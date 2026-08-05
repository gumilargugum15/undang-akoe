<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\Invitation;
use App\Repositories\Interfaces\CoupleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CoupleService
{
    public function __construct(
        private readonly CoupleRepositoryInterface $couples,
    ) {}

    /**
     * @return Collection<int, Couple>
     */
    public function getForInvitation(Invitation $invitation): Collection
    {
        return $this->couples->forInvitation($invitation);
    }

    public function upsert(Invitation $invitation, string $role, array $data): Couple
    {
        return $this->couples->upsert($invitation, $role, $data);
    }

    public function remove(Invitation $invitation, string $role): void
    {
        $this->couples->delete($invitation, $role);
    }
}
