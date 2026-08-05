<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BannerManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_customer_cannot_manage_banners(): void
    {
        $customer = User::factory()->create();

        $this->apiAs($customer)->postJson('/api/banners', ['title' => 'Promo'])
            ->assertForbidden();
    }

    #[Test]
    public function an_admin_can_create_a_banner_with_an_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->apiAs($admin)->post('/api/banners', [
            'title' => 'Promo Akhir Tahun',
            'image' => UploadedFile::fake()->image('promo.jpg', 1200, 600),
            'link_url' => 'https://example.com/promo',
        ]);

        $response->assertCreated()->assertJsonPath('data.title', 'Promo Akhir Tahun');
        $this->assertNotNull($response->json('data.image'));
        $this->assertDatabaseHas('banners', ['title' => 'Promo Akhir Tahun']);
    }

    #[Test]
    public function title_and_image_are_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->apiAs($admin)->postJson('/api/banners', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['title', 'image']);
    }

    #[Test]
    public function the_public_endpoint_only_returns_live_banners(): void
    {
        Banner::factory()->create(['title' => 'Live']);
        Banner::factory()->inactive()->create(['title' => 'Inactive']);
        Banner::factory()->expired()->create(['title' => 'Expired']);

        $response = $this->getJson('/api/public/banners');

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Live');
    }

    #[Test]
    public function an_admin_index_sees_every_banner_including_inactive(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Banner::factory()->create();
        Banner::factory()->inactive()->create();

        $this->apiAs($admin)->getJson('/api/banners')->assertOk()->assertJsonCount(2, 'data');
    }

    #[Test]
    public function an_admin_can_update_and_deactivate_a_banner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $banner = Banner::factory()->create();

        $this->apiAs($admin)->putJson("/api/banners/{$banner->id}", ['is_active' => false])
            ->assertOk()->assertJsonPath('data.is_active', false);
    }

    #[Test]
    public function an_admin_can_delete_a_banner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $banner = Banner::factory()->create();

        $this->apiAs($admin)->deleteJson("/api/banners/{$banner->id}")->assertOk();
        $this->assertSoftDeleted('banners', ['id' => $banner->id]);
    }
}
