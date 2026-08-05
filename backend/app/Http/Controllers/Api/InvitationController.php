<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\ChangeInvitationThemeRequest;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Http\Requests\Invitation\UpdateInvitationRequest;
use App\Http\Requests\Invitation\UploadCoverPhotoRequest;
use App\Http\Resources\InvitationResource;
use App\Http\Resources\PublicInvitationResource;
use App\Models\Invitation;
use App\Services\GuestService;
use App\Services\ImageProcessingService;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitations,
        private readonly ImageProcessingService $images,
        private readonly GuestService $guests,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Invitation::class);

        $filters = $request->only(['status', 'event_category', 'search', 'per_page']);
        $invitations = $this->invitations->list($request->user(), $filters);

        return response()->json([
            'data' => InvitationResource::collection($invitations),
            'meta' => [
                'current_page' => $invitations->currentPage(),
                'last_page' => $invitations->lastPage(),
                'per_page' => $invitations->perPage(),
                'total' => $invitations->total(),
            ],
        ]);
    }

    public function store(StoreInvitationRequest $request): JsonResponse
    {
        $this->authorize('create', Invitation::class);

        $invitation = $this->invitations->create($request->user(), $request->validated());
        $invitation->load(['theme', 'package', 'user']);

        return response()->json([
            'message' => 'Undangan berhasil dibuat.',
            'data' => new InvitationResource($invitation),
        ], 201);
    }

    public function show(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        $invitation->load(['theme.category', 'package', 'user']);

        return response()->json([
            'data' => new InvitationResource($invitation),
        ]);
    }

    /**
     * Public — no auth. Everything the guest-facing invitation page needs
     * for its first render (SSR loader), in one call. Same 404-not-422 rule
     * as the other public endpoints for a draft/suspended undangan.
     */
    public function publicShow(Request $request, Invitation $invitation): JsonResponse
    {
        abort_unless($invitation->isLive(), 404);

        $invitation->load(['theme', 'seo', 'couples', 'honorees', 'events', 'gallery', 'loveStories', 'music']);

        $data = (new PublicInvitationResource($invitation))->resolve($request);

        // Personalized `?to=<slug_token>` link — resolves to the invited guest's own name so
        // the cover greets them by name instead of the generic "Bapak/Ibu/Saudara/i". Silently
        // stays null for a missing/invalid/foreign token rather than erroring the whole page.
        if ($token = $request->query('to')) {
            $data['guest_name'] = $this->guests->findByToken($invitation, $token)?->name;
        } else {
            $data['guest_name'] = null;
        }

        return response()->json(['data' => $data]);
    }

    public function update(UpdateInvitationRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $invitation = $this->invitations->update($invitation, $request->validated());

        return response()->json([
            'message' => 'Undangan berhasil diperbarui.',
            'data' => new InvitationResource($invitation),
        ]);
    }

    public function destroy(Invitation $invitation): JsonResponse
    {
        $this->authorize('delete', $invitation);

        $this->invitations->delete($invitation);

        return response()->json([
            'message' => 'Undangan berhasil dihapus.',
        ]);
    }

    public function publish(Invitation $invitation): JsonResponse
    {
        $this->authorize('publish', $invitation);

        $invitation = $this->invitations->publish($invitation);

        return response()->json([
            'message' => 'Undangan berhasil dipublikasikan.',
            'data' => new InvitationResource($invitation),
        ]);
    }

    public function unpublish(Invitation $invitation): JsonResponse
    {
        $this->authorize('publish', $invitation);

        $invitation = $this->invitations->unpublish($invitation);

        return response()->json([
            'message' => 'Undangan dikembalikan ke status draft.',
            'data' => new InvitationResource($invitation),
        ]);
    }

    public function changeTheme(ChangeInvitationThemeRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $invitation = $this->invitations->changeTheme($invitation, $request->validated('theme_id'));

        return response()->json([
            'message' => 'Tema undangan berhasil diganti.',
            'data' => new InvitationResource($invitation->load('theme')),
        ]);
    }

    public function uploadCoverPhoto(UploadCoverPhotoRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        if ($invitation->cover_photo) {
            Storage::disk('public')->delete($invitation->cover_photo);
        }

        $path = $this->images->storePhoto($request->file('photo'), "invitations/{$invitation->id}/cover", maxWidth: 1600);
        $invitation = $this->invitations->update($invitation, ['cover_photo' => $path]);

        return response()->json([
            'message' => 'Foto sampul berhasil diunggah.',
            'data' => new InvitationResource($invitation),
        ]);
    }

    public function removeCoverPhoto(Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        if ($invitation->cover_photo) {
            Storage::disk('public')->delete($invitation->cover_photo);
        }

        $invitation = $this->invitations->update($invitation, ['cover_photo' => null]);

        return response()->json([
            'message' => 'Foto sampul berhasil dihapus.',
            'data' => new InvitationResource($invitation),
        ]);
    }

    public function suspend(Invitation $invitation): JsonResponse
    {
        $this->authorize('moderate', Invitation::class);

        $invitation = $this->invitations->suspend($invitation);

        return response()->json([
            'message' => 'Undangan berhasil ditangguhkan.',
            'data' => new InvitationResource($invitation),
        ]);
    }

    public function reactivate(Invitation $invitation): JsonResponse
    {
        $this->authorize('moderate', Invitation::class);

        $invitation = $this->invitations->reactivate($invitation);

        return response()->json([
            'message' => 'Penangguhan undangan berhasil dicabut.',
            'data' => new InvitationResource($invitation),
        ]);
    }
}
