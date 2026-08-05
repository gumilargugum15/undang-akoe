<?php

namespace App\Services;

use App\Models\Honoree;
use App\Models\Invitation;
use App\Repositories\Interfaces\HonoreeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class HonoreeService
{
    public function __construct(
        private readonly HonoreeRepositoryInterface $honorees,
    ) {}

    /**
     * @return Collection<int, Honoree>
     */
    public function getForInvitation(Invitation $invitation): Collection
    {
        return $this->honorees->forInvitation($invitation);
    }

    public function create(Invitation $invitation, array $data): Honoree
    {
        return $this->honorees->create($invitation, $data);
    }

    public function update(Honoree $honoree, array $data): Honoree
    {
        return $this->honorees->update($honoree, $data);
    }

    public function delete(Honoree $honoree): void
    {
        $this->honorees->delete($honoree);
    }
}
