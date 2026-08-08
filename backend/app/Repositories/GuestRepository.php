<?php

namespace App\Repositories;

use App\Models\Guest;
use App\Models\Invitation;
use App\Repositories\Interfaces\GuestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GuestRepository implements GuestRepositoryInterface
{
    public function forInvitation(Invitation $invitation): Collection
    {
        return $invitation->guests()->orderBy('created_at')->get();
    }

    public function countForInvitation(Invitation $invitation): int
    {
        return $invitation->guests()->count();
    }

    public function create(Invitation $invitation, array $data): Guest
    {
        return $invitation->guests()->create($data);
    }

    public function delete(Guest $guest): void
    {
        $guest->delete();
    }

    public function findByToken(Invitation $invitation, string $token): ?Guest
    {
        return $invitation->guests()->where('slug_token', $token)->first();
    }
}
