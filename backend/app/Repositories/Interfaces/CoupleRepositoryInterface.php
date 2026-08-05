<?php

namespace App\Repositories\Interfaces;

use App\Models\Couple;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Collection;

interface CoupleRepositoryInterface
{
    /**
     * @return Collection<int, Couple>
     */
    public function forInvitation(Invitation $invitation): Collection;

    public function upsert(Invitation $invitation, string $role, array $data): Couple;

    public function delete(Invitation $invitation, string $role): void;
}
