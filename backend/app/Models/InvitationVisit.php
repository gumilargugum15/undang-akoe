<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationVisit extends Model
{
    /**
     * Append-only analytics log using `visited_at` instead of Laravel's
     * created_at/updated_at (see Phase 2 ERD notes) — no soft deletes either.
     * Retention/pruning of old rows is an Optimasi-phase concern.
     */
    public $timestamps = false;

    protected $fillable = [
        'invitation_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'platform',
        'country',
        'city',
        'referrer',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Invitation, $this>
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
