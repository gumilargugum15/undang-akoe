<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_customer_cannot_manage_users(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();

        $this->apiAs($customer)->getJson('/api/users')->assertForbidden();
        $this->apiAs($customer)->patchJson("/api/users/{$other->id}/suspend")->assertForbidden();
    }

    #[Test]
    public function an_admin_can_list_search_and_filter_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);
        User::factory()->create(['name' => 'Siti Aminah', 'email' => 'siti@example.com']);

        $this->apiAs($admin)->getJson('/api/users')->assertOk()->assertJsonCount(3, 'data');

        $this->apiAs($admin)->getJson('/api/users?search=budi')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budi Santoso');

        $this->apiAs($admin)->getJson('/api/users?role=admin')
            ->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function an_admin_can_suspend_and_activate_a_customer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();

        $this->apiAs($admin)->patchJson("/api/users/{$customer->id}/suspend")
            ->assertOk()->assertJsonPath('data.is_active', false);

        $this->apiAs($admin)->patchJson("/api/users/{$customer->id}/activate")
            ->assertOk()->assertJsonPath('data.is_active', true);
    }

    #[Test]
    public function an_admin_cannot_suspend_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->apiAs($admin)->patchJson("/api/users/{$admin->id}/suspend")
            ->assertUnprocessable()->assertJsonValidationErrors(['user']);
    }

    #[Test]
    public function an_admin_can_change_a_users_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();

        $this->apiAs($admin)->putJson("/api/users/{$customer->id}/role", ['role' => 'admin'])
            ->assertOk()->assertJsonPath('data.role', 'admin');
    }

    #[Test]
    public function an_admin_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->apiAs($admin)->putJson("/api/users/{$admin->id}/role", ['role' => 'customer'])
            ->assertUnprocessable()->assertJsonValidationErrors(['user']);
    }

    #[Test]
    public function a_user_with_invitations_cannot_be_deleted_but_can_be_suspended(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        Invitation::factory()->for($customer, 'user')->create();

        $this->apiAs($admin)->deleteJson("/api/users/{$customer->id}")
            ->assertUnprocessable()->assertJsonValidationErrors(['user']);

        $this->apiAs($admin)->patchJson("/api/users/{$customer->id}/suspend")->assertOk();
    }

    #[Test]
    public function a_user_without_invitations_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();

        $this->apiAs($admin)->deleteJson("/api/users/{$customer->id}")->assertOk();
        $this->assertSoftDeleted('users', ['id' => $customer->id]);
    }

    #[Test]
    public function an_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->apiAs($admin)->deleteJson("/api/users/{$admin->id}")
            ->assertUnprocessable()->assertJsonValidationErrors(['user']);
    }
}
