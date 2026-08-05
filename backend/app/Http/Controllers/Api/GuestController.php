<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreGuestRequest;
use App\Http\Resources\GuestResource;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\GuestService;
use Illuminate\Http\JsonResponse;

class GuestController extends Controller
{
    public function __construct(
        private readonly GuestService $guests,
    ) {}

    public function index(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json([
            'data' => GuestResource::collection($this->guests->getForInvitation($invitation)),
        ]);
    }

    public function store(StoreGuestRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $guest = $this->guests->create($invitation, $request->validated());

        return response()->json([
            'message' => 'Tamu berhasil ditambahkan.',
            'data' => new GuestResource($guest),
        ], 201);
    }

    public function destroy(Invitation $invitation, Guest $guest): JsonResponse
    {
        $this->authorize('update', $invitation);
        abort_unless($guest->invitation_id === $invitation->id, 404);

        $this->guests->delete($guest);

        return response()->json([
            'message' => 'Tamu berhasil dihapus.',
        ]);
    }
}
