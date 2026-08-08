<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentSetting\UpdatePaymentSettingRequest;
use App\Http\Requests\PaymentSetting\UploadQrisRequest;
use App\Services\PaymentSettingService;
use Illuminate\Http\JsonResponse;

/**
 * Admin-only (see routes/api.php) — lets an admin configure manual payment details (bank
 * accounts, DANA/GoPay numbers, QRIS image) without a code deploy. Read by customers
 * indirectly through Transaction::paymentInstructionsFor(), not through this controller.
 */
class PaymentSettingController extends Controller
{
    public function __construct(
        private readonly PaymentSettingService $settings,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json(['data' => $this->settings->get()]);
    }

    public function update(UpdatePaymentSettingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->settings->updateBanks($data['banks'] ?? []);
        $this->settings->updateDana($data['dana']['number'] ?? null, $data['dana']['account_name'] ?? null);
        $this->settings->updateGopay($data['gopay']['number'] ?? null, $data['gopay']['account_name'] ?? null);
        $this->settings->updateQrisMerchantName($data['qris_merchant_name'] ?? null);

        return response()->json([
            'message' => 'Pengaturan pembayaran berhasil disimpan.',
            'data' => $this->settings->get(),
        ]);
    }

    public function uploadQris(UploadQrisRequest $request): JsonResponse
    {
        $this->settings->uploadQris($request->file('qris'));

        return response()->json([
            'message' => 'Gambar QRIS berhasil diunggah.',
            'data' => $this->settings->get(),
        ]);
    }

    public function removeQris(): JsonResponse
    {
        $this->settings->removeQris();

        return response()->json([
            'message' => 'Gambar QRIS berhasil dihapus.',
            'data' => $this->settings->get(),
        ]);
    }
}
