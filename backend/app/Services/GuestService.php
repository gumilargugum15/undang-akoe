<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Repositories\Interfaces\GuestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GuestService
{
    public function __construct(
        private readonly GuestRepositoryInterface $guests,
    ) {}

    /**
     * @return Collection<int, Guest>
     */
    public function getForInvitation(Invitation $invitation): Collection
    {
        return $this->guests->forInvitation($invitation);
    }

    public function create(Invitation $invitation, array $data): Guest
    {
        return $this->guests->create($invitation, $data);
    }

    public function delete(Guest $guest): void
    {
        $this->guests->delete($guest);
    }

    public function findByToken(Invitation $invitation, string $token): ?Guest
    {
        return $this->guests->findByToken($invitation, $token);
    }
}
