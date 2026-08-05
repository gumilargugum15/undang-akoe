<?php

namespace App\Repositories;

use App\Models\Honoree;
use App\Models\Invitation;
use App\Repositories\Interfaces\HonoreeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class HonoreeRepository implements HonoreeRepositoryInterface
{
    public function forInvitation(Invitation $invitation): Collection
    {
        return $invitation->honorees()->orderBy('sort_order')->get();
    }

    public function create(Invitation $invitation, array $data): Honoree
    {
        return $invitation->honorees()->create($data);
    }

    public function update(Honoree $honoree, array $data): Honoree
    {
        $honoree->update($data);

        return $honoree->fresh();
    }

    public function delete(Honoree $honoree): void
    {
        $honoree->delete();
    }
}
