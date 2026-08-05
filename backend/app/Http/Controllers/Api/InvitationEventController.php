<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvitationEvent\StoreInvitationEventRequest;
use App\Http\Requests\InvitationEvent\UpdateInvitationEventRequest;
use App\Http\Resources\InvitationEventResource;
use App\Models\Invitation;
use App\Models\InvitationEvent;
use App\Services\InvitationEventService;
use Illuminate\Http\JsonResponse;

class InvitationEventController extends Controller
{
    public function __construct(
        private readonly InvitationEventService $events,
    ) {}

    public function index(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json([
            'data' => InvitationEventResource::collection($this->events->getForInvitation($invitation)),
        ]);
    }

    public function store(StoreInvitationEventRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $event = $this->events->create($invitation, $request->validated());

        return response()->json([
            'message' => 'Acara berhasil ditambahkan.',
            'data' => new InvitationEventResource($event),
        ], 201);
    }

    public function update(UpdateInvitationEventRequest $request, Invitation $invitation, InvitationEvent $event): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $event);

        $event = $this->events->update($event, $request->validated());

        return response()->json([
            'message' => 'Acara berhasil diperbarui.',
            'data' => new InvitationEventResource($event),
        ]);
    }

    public function destroy(Invitation $invitation, InvitationEvent $event): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->assertBelongsToInvitation($invitation, $event);

        $this->events->delete($event);

        return response()->json([
            'message' => 'Acara berhasil dihapus.',
        ]);
    }

    private function assertBelongsToInvitation(Invitation $invitation, InvitationEvent $event): void
    {
        abort_unless($event->invitation_id === $invitation->id, 404);
    }
}
