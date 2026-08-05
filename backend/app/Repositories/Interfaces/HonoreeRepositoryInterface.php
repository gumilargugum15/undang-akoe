<?php

namespace App\Repositories\Interfaces;

use App\Models\Honoree;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Collection;

interface HonoreeRepositoryInterface
{
    /**
     * @return Collection<int, Honoree>
     */
    public function forInvitation(Invitation $invitation): Collection;

    public function create(Invitation $invitation, array $data): Honoree;

    public function update(Honoree $honoree, array $data): Honoree;

    public function delete(Honoree $honoree): void;
}
