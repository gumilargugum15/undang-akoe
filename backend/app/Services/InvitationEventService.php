<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\InvitationEvent;
use App\Repositories\Interfaces\InvitationEventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InvitationEventService
{
    public function __construct(
        private readonly InvitationEventRepositoryInterface $events,
    ) {}

    /**
     * @return Collection<int, InvitationEvent>
     */
    public function getForInvitation(Invitation $invitation): Collection
    {
        return $this->events->forInvitation($invitation);
    }

    public function create(Invitation $invitation, array $data): InvitationEvent
    {
        return $this->events->create($invitation, $data);
    }

    public function update(InvitationEvent $event, array $data): InvitationEvent
    {
        return $this->events->update($event, $data);
    }

    public function delete(InvitationEvent $event): void
    {
        $this->events->delete($event);
    }
}
