<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Backs the admin "Pengaturan Pembayaran" page and the instructions embedded in
 * TransactionResource (via Transaction::paymentInstructionsFor). Every setting falls back to
 * config/payment.php's env-driven defaults until an admin actually sets it here, so the
 * checkout flow keeps working before anyone has touched the admin page.
 */
class PaymentSettingService
{
    public function __construct(
        private readonly ImageProcessingService $images,
    ) {}

    /**
     * Full current settings — used by the admin settings page.
     *
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return [
            'banks' => $this->banks(),
            'dana' => [
                'number' => SiteSetting::get('payment.dana_number', config('payment.ewallets.dana.number')),
                'account_name' => SiteSetting::get('payment.dana_name', config('payment.ewallets.dana.account_name')),
            ],
            'gopay' => [
                'number' => SiteSetting::get('payment.gopay_number', config('payment.ewallets.gopay.number')),
                'account_name' => SiteSetting::get('payment.gopay_name', config('payment.ewallets.gopay.account_name')),
            ],
            'qris' => [
                'image_url' => $this->qrisImageUrl(),
                'merchant_name' => SiteSetting::get('payment.qris_merchant_name', config('payment.qris.merchant_name')),
            ],
        ];
    }

    /**
     * What a customer sees for the one method they picked — embedded in TransactionResource.
     *
     * @return array<string, mixed>
     */
    public function instructionsFor(string $method): array
    {
        return match ($method) {
            'bank_transfer' => ['banks' => $this->banks()],
            'qris' => [
                'image_url' => $this->qrisImageUrl(),
                'merchant_name' => SiteSetting::get('payment.qris_merchant_name', config('payment.qris.merchant_name')),
            ],
            'dana' => [
                'provider' => 'DANA',
                'number' => SiteSetting::get('payment.dana_number', config('payment.ewallets.dana.number')),
                'account_name' => SiteSetting::get('payment.dana_name', config('payment.ewallets.dana.account_name')),
            ],
            'gopay' => [
                'provider' => 'GoPay',
                'number' => SiteSetting::get('payment.gopay_number', config('payment.ewallets.gopay.number')),
                'account_name' => SiteSetting::get('payment.gopay_name', config('payment.ewallets.gopay.account_name')),
            ],
            default => [],
        };
    }

    /**
     * @return array<int, array{bank: string, account_number: string, account_name: string}>
     */
    public function banks(): array
    {
        $raw = SiteSetting::get('payment.banks');

        return $raw ? (json_decode($raw, true) ?: []) : config('payment.bank_transfer');
    }

    /**
     * @param  array<int, array{bank: string, account_number: string, account_name: string}>  $banks
     */
    public function updateBanks(array $banks): void
    {
        SiteSetting::set('payment.banks', json_encode(array_values($banks)), 'payment');
    }

    public function updateDana(?string $number, ?string $accountName): void
    {
        SiteSetting::set('payment.dana_number', $number, 'payment');
        SiteSetting::set('payment.dana_name', $accountName, 'payment');
    }

    public function updateGopay(?string $number, ?string $accountName): void
    {
        SiteSetting::set('payment.gopay_number', $number, 'payment');
        SiteSetting::set('payment.gopay_name', $accountName, 'payment');
    }

    public function updateQrisMerchantName(?string $name): void
    {
        SiteSetting::set('payment.qris_merchant_name', $name, 'payment');
    }

    public function uploadQris(UploadedFile $file): void
    {
        $existing = SiteSetting::get('payment.qris_image');

        if ($existing) {
            Storage::disk('public')->delete($existing);
        }

        $path = $this->images->storeLosslessImage($file, 'payment/qris', maxWidth: 1000);
        SiteSetting::set('payment.qris_image', $path, 'payment');
    }

    public function removeQris(): void
    {
        $existing = SiteSetting::get('payment.qris_image');

        if (! $existing) {
            return;
        }

        Storage::disk('public')->delete($existing);
        SiteSetting::set('payment.qris_image', null, 'payment');
    }

    private function qrisImageUrl(): ?string
    {
        $path = SiteSetting::get('payment.qris_image');

        return $path ? Storage::disk('public')->url($path) : config('payment.qris.image_url');
    }
}
