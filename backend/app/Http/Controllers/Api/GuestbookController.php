<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guestbook\PublicRsvpRequest;
use App\Http\Resources\GuestbookResource;
use App\Models\Guestbook;
use App\Models\Invitation;
use App\Services\GuestbookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestbookController extends Controller
{
    public function __construct(
        private readonly GuestbookService $guestbook,
    ) {}

    /**
     * Public — no auth. A guest browsing the published invitation submits
     * their RSVP + ucapan here. 404s (not 422) for a draft/suspended
     * invitation so its unpublished existence isn't leaked to the public.
     */
    public function submit(PublicRsvpRequest $request, Invitation $invitation): JsonResponse
    {
        abort_unless($invitation->isLive(), 404);

        $entry = $this->guestbook->submitRsvp($invitation, [
            ...$request->validated(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => $entry->attendance === 'hadir'
                ? "Terima kasih {$entry->guest_name}, sampai jumpa di acara kami!"
                : "Terima kasih {$entry->guest_name} atas doanya.",
            'data' => new GuestbookResource($entry),
        ], 201);
    }

    public function index(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        $entries = $this->guestbook->list($invitation, $request->only(['attendance', 'is_approved', 'per_page']));

        return response()->json([
            'data' => GuestbookResource::collection($entries),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
            ],
        ]);
    }

    /**
     * Public — no auth. "Wall of Love": approved ucapan only, shown on the
     * published invitation page. Same 404-not-422 rule as submit().
     */
    public function wall(Request $request, Invitation $invitation): JsonResponse
    {
        abort_unless($invitation->isLive(), 404);

        $entries = $this->guestbook->getPublicWall($invitation, (int) $request->input('per_page', 15));

        return response()->json([
            'data' => GuestbookResource::collection($entries),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
            ],
        ]);
    }

    public function approve(Invitation $invitation, Guestbook $rsvp): JsonResponse
    {
        $this->authorize('update', $invitation);
        abort_unless($rsvp->invitation_id === $invitation->id, 404);

        $rsvp = $this->guestbook->setApproval($rsvp, true);

        return response()->json([
            'message' => 'Ucapan berhasil disetujui dan tampil di halaman undangan.',
            'data' => new GuestbookResource($rsvp),
        ]);
    }

    public function reject(Invitation $invitation, Guestbook $rsvp): JsonResponse
    {
        $this->authorize('update', $invitation);
        abort_unless($rsvp->invitation_id === $invitation->id, 404);

        $rsvp = $this->guestbook->setApproval($rsvp, false);

        return response()->json([
            'message' => 'Ucapan disembunyikan dari halaman undangan.',
            'data' => new GuestbookResource($rsvp),
        ]);
    }

    public function summary(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json([
            'data' => $this->guestbook->summary($invitation),
        ]);
    }

    public function destroy(Invitation $invitation, Guestbook $rsvp): JsonResponse
    {
        $this->authorize('update', $invitation);
        abort_unless($rsvp->invitation_id === $invitation->id, 404);

        $this->guestbook->delete($rsvp);

        return response()->json([
            'message' => 'Data RSVP berhasil dihapus.',
        ]);
    }
}
