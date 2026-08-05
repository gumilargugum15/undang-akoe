<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    /**
     * Same "Dashboard" menu for both roles, different content: a customer
     * sees their own invitations' numbers, an admin sees platform-wide ones.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $user->isAdmin()
                ? $this->dashboard->forAdmin()
                : $this->dashboard->forCustomer($user),
        ]);
    }
}
