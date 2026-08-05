<?php

namespace App\Repositories;

use App\Models\Couple;
use App\Models\Invitation;
use App\Repositories\Interfaces\CoupleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CoupleRepository implements CoupleRepositoryInterface
{
    public function forInvitation(Invitation $invitation): Collection
    {
        return $invitation->couples()->orderBy('sort_order')->get();
    }

    public function upsert(Invitation $invitation, string $role, array $data): Couple
    {
        return $invitation->couples()->updateOrCreate(['role' => $role], $data);
    }

    public function delete(Invitation $invitation, string $role): void
    {
        $invitation->couples()->where('role', $role)->delete();
    }
}
