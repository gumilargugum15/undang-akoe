<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrackVisitRequest;
use App\Models\Invitation;
use App\Services\InvitationVisitService;
use Illuminate\Http\JsonResponse;

class InvitationStatisticsController extends Controller
{
    public function __construct(
        private readonly InvitationVisitService $visits,
    ) {}

    /**
     * Public — no auth. Called once by the frontend when the published
     * invitation page loads. Same 404-not-422 rule as the other public
     * endpoints for a draft/suspended undangan.
     */
    public function track(TrackVisitRequest $request, Invitation $invitation): JsonResponse
    {
        abort_unless($invitation->isLive(), 404);

        $this->visits->track($invitation, [
            'session_id' => $request->validated('session_id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->validated('referrer') ?? $request->header('referer'),
        ]);

        return response()->json(['message' => 'Kunjungan tercatat.'], 201);
    }

    public function summary(Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json([
            'data' => $this->visits->summary($invitation),
        ]);
    }
}
