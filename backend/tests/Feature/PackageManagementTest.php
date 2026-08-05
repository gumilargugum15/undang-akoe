<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_customer_cannot_manage_packages(): void
    {
        $customer = User::factory()->create();

        $this->apiAs($customer)->postJson('/api/packages', [
            'name' => 'Premium', 'price' => 99000,
        ])->assertForbidden();
    }

    #[Test]
    public function an_admin_can_create_a_package_with_an_auto_generated_slug(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->apiAs($admin)->postJson('/api/packages', [
            'name' => 'Paket Hemat',
            'price' => 49000,
            'max_guests' => 100,
            'features' => ['rsvp', 'buku_tamu'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Paket Hemat')
            ->assertJsonPath('data.slug', 'paket-hemat')
            ->assertJsonPath('data.price', 49000)
            ->assertJsonPath('data.is_active', true);
        $this->assertDatabaseHas('packages', ['name' => 'Paket Hemat', 'slug' => 'paket-hemat']);
    }

    #[Test]
    public function name_and_price_are_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->apiAs($admin)->postJson('/api/packages', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'price']);
    }

    #[Test]
    public function package_names_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Package::factory()->create(['name' => 'Premium']);

        $this->apiAs($admin)->postJson('/api/packages', [
            'name' => 'Premium', 'price' => 1000,
        ])->assertUnprocessable()->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function an_admin_can_update_and_deactivate_a_package(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $package = Package::factory()->create();

        $this->apiAs($admin)->putJson("/api/packages/{$package->id}", [
            'price' => 150000, 'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.price', 150000)
            ->assertJsonPath('data.is_active', false);
    }

    #[Test]
    public function admin_sees_inactive_packages_but_a_customer_only_sees_active_ones(): void
    {
        Package::factory()->create(['name' => 'Active One']);
        Package::factory()->inactive()->create(['name' => 'Hidden One']);

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();

        $this->apiAs($admin)->getJson('/api/packages')->assertOk()->assertJsonCount(2, 'data');
        $this->apiAs($customer)->getJson('/api/packages')->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function a_package_still_used_by_an_invitation_cannot_be_deleted_but_can_be_deactivated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $package = Package::factory()->create();
        Invitation::factory()->create(['package_id' => $package->id]);

        $this->apiAs($admin)->deleteJson("/api/packages/{$package->id}")
            ->assertUnprocessable()->assertJsonValidationErrors(['package']);

        $this->apiAs($admin)->putJson("/api/packages/{$package->id}", ['is_active' => false])
            ->assertOk()->assertJsonPath('data.is_active', false);
    }

    #[Test]
    public function an_unused_package_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $package = Package::factory()->create();

        $this->apiAs($admin)->deleteJson("/api/packages/{$package->id}")->assertOk();
        $this->assertSoftDeleted('packages', ['id' => $package->id]);
    }
}
