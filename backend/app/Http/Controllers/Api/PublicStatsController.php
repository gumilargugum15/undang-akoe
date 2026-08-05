<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Read-only aggregate counts for the marketing landing page's "Trusted By"
 * section — public, no auth, nothing here is sensitive (just totals).
 */
class PublicStatsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_customers' => User::where('role', 'customer')->count(),
                'total_invitations' => Invitation::where('status', 'published')->count(),
                // Not scoped to status — a suspended/unpublished invitation's past visits are
                // still real historical traffic the platform drove, unlike the "live now" count above.
                'total_visitors' => (int) Invitation::sum('view_count'),
            ],
        ]);
    }
}
