<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Illuminate\Console\Command;

/**
 * Moves Published invitations whose expires_at has passed to Expired. A lifetime package
 * (duration_days=null) never sets expires_at, so it's naturally excluded and never expires.
 */
class ExpireInvitations extends Command
{
    protected $signature = 'invitations:expire';

    protected $description = 'Move published invitations past their expires_at to the expired status';

    public function handle(): int
    {
        $count = Invitation::query()
            ->where('status', Invitation::STATUS_PUBLISHED)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => Invitation::STATUS_EXPIRED]);

        $this->info("{$count} undangan dipindahkan ke status expired.");

        return self::SUCCESS;
    }
}
