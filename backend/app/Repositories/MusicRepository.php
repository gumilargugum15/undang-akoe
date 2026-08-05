<?php

namespace App\Repositories;

use App\Models\Invitation;
use App\Models\Music;
use App\Repositories\Interfaces\MusicRepositoryInterface;

class MusicRepository implements MusicRepositoryInterface
{
    public function forInvitation(Invitation $invitation): ?Music
    {
        return $invitation->music()->first();
    }

    public function upsert(Invitation $invitation, array $data): Music
    {
        $existing = $invitation->music()->first();

        if ($existing) {
            $existing->update($data);

            return $existing->fresh();
        }

        return $invitation->music()->create($data);
    }

    public function delete(Invitation $invitation): void
    {
        $invitation->music()->delete();
    }
}
