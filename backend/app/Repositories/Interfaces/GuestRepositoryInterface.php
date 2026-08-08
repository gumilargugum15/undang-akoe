<?php

namespace App\Repositories\Interfaces;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Collection;

interface GuestRepositoryInterface
{
    /**
     * @return Collection<int, Guest>
     */
    public function forInvitation(Invitation $invitation): Collection;

    public function countForInvitation(Invitation $invitation): int;

    public function create(Invitation $invitation, array $data): Guest;

    public function delete(Guest $guest): void;

    public function findByToken(Invitation $invitation, string $token): ?Guest;
}
