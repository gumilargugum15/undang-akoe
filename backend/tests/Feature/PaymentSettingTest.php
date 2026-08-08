<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use App\Models\Invitation;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentSettingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_customer_cannot_view_or_manage_payment_settings(): void
    {
        $customer = User::factory()->create();

        $this->apiAs($customer)->getJson('/api/payment-settings')->assertForbidden();
        $this->apiAs($customer)->putJson('/api/payment-settings', [])->assertForbidden();
    }

    #[Test]
    public function an_admin_sees_config_fallbacks_before_anything_is_configured(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->apiAs($admin)->getJson('/api/payment-settings');

        $response->assertOk()
            ->assertJsonPath('data.banks.0.bank', 'BCA')
            ->assertJsonPath('data.qris.image_url', null);
    }

    #[Test]
    public function an_admin_can_update_banks_and_ewallet_numbers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->apiAs($admin)->putJson('/api/payment-settings', [
            'banks' => [
                ['bank' => 'Mandiri', 'account_number' => '111222333', 'account_name' => 'Undang Akoe'],
            ],
            'dana' => ['number' => '081234567890', 'account_name' => 'Undang Akoe'],
            'gopay' => ['number' => '089876543210', 'account_name' => 'Undang Akoe'],
            'qris_merchant_name' => 'Toko Undang Akoe',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.banks.0.bank', 'Mandiri')
            ->assertJsonPath('data.dana.number', '081234567890')
            ->assertJsonPath('data.gopay.number', '089876543210')
            ->assertJsonPath('data.qris.merchant_name', 'Toko Undang Akoe');

        // persisted, not just echoed back
        $this->apiAs($admin)->getJson('/api/payment-settings')
            ->assertJsonPath('data.dana.number', '081234567890');
    }

    #[Test]
    public function an_admin_can_upload_and_remove_the_qris_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $upload = $this->apiAs($admin)->post('/api/payment-settings/qris', [
            'qris' => UploadedFile::fake()->image('qris.png', 400, 400),
        ]);
        $upload->assertOk();
        $imageUrl = $upload->json('data.qris.image_url');
        $this->assertNotEmpty($imageUrl);

        $this->apiAs($admin)->deleteJson('/api/payment-settings/qris')
            ->assertOk()->assertJsonPath('data.qris.image_url', null);
    }

    #[Test]
    public function a_customer_can_checkout_with_dana_or_gopay_and_gets_the_configured_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->apiAs($admin)->putJson('/api/payment-settings', [
            'dana' => ['number' => '081234567890', 'account_name' => 'Undang Akoe'],
        ])->assertOk();

        $customer = User::factory()->create();
        $theme = Theme::factory()->create();
        $package = Package::factory()->create(['requires_payment' => true, 'is_free' => false]);
        $invitation = Invitation::factory()->for($customer, 'user')->for($theme)->create(['status' => 'draft']);

        $response = $this->apiAs($customer)->postJson("/api/invitations/{$invitation->id}/checkout", [
            'package_id' => $package->id,
            'payment_method' => 'dana',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.payment_method', 'dana')
            ->assertJsonPath('data.payment_channel', 'DANA')
            ->assertJsonPath('data.instructions.number', '081234567890');
    }
}
