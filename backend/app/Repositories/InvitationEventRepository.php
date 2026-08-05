<?php

namespace App\Repositories;

use App\Models\Invitation;
use App\Models\InvitationEvent;
use App\Repositories\Interfaces\InvitationEventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InvitationEventRepository implements InvitationEventRepositoryInterface
{
    public function forInvitation(Invitation $invitation): Collection
    {
        return $invitation->events()->orderBy('sort_order')->orderBy('event_date')->get();
    }

    public function create(Invitation $invitation, array $data): InvitationEvent
    {
        return $invitation->events()->create($data);
    }

    public function update(InvitationEvent $event, array $data): InvitationEvent
    {
        $event->update($data);

        return $event->fresh();
    }

    public function delete(InvitationEvent $event): void
    {
        $event->delete();
    }
}
