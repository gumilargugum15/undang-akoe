<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Invitation;
use App\Models\Package;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
    ) {}

    /**
     * Starts (or restarts, for "Ganti Paket") a manual-payment checkout for a paid package.
     * A FREE/no-payment package never reaches this endpoint — it's published directly via
     * InvitationController::publish().
     */
    public function store(CheckoutRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $package = Package::findOrFail($request->validated('package_id'));

        $transaction = $this->checkout->checkout(
            $invitation,
            $package,
            $request->user(),
            $request->validated('payment_method'),
        );

        return response()->json([
            'message' => 'Checkout berhasil dibuat. Selesaikan pembayaran sesuai instruksi, lalu unggah bukti pembayaran.',
            'data' => new TransactionResource($transaction->load(['invitation', 'package'])),
        ], 201);
    }
}
