<?php

namespace App\Repositories\Interfaces;

use App\Models\Invitation;
use App\Models\Music;

interface MusicRepositoryInterface
{
    public function forInvitation(Invitation $invitation): ?Music;

    public function upsert(Invitation $invitation, array $data): Music;

    public function delete(Invitation $invitation): void;
}
