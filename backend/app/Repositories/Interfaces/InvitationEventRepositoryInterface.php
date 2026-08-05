<?php

namespace App\Repositories\Interfaces;

use App\Models\Invitation;
use App\Models\InvitationEvent;
use Illuminate\Database\Eloquent\Collection;

interface InvitationEventRepositoryInterface
{
    /**
     * @return Collection<int, InvitationEvent>
     */
    public function forInvitation(Invitation $invitation): Collection;

    public function create(Invitation $invitation, array $data): InvitationEvent;

    public function update(InvitationEvent $event, array $data): InvitationEvent;

    public function delete(InvitationEvent $event): void;
}
