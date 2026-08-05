<?php

namespace Tests\Feature;

use App\Models\Guestbook;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_customer_sees_only_their_own_aggregate_numbers(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $mine = Invitation::factory()->for($customer, 'user')->create(['view_count' => 10]);
        Guestbook::factory()->for($mine, 'invitation')->create(['attendance' => 'hadir', 'guest_count' => 2]);

        $notMine = Invitation::factory()->for($otherCustomer, 'user')->create(['view_count' => 999]);
        Guestbook::factory()->for($notMine, 'invitation')->create(['attendance' => 'hadir', 'guest_count' => 50]);

        $response = $this->apiAs($customer)->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.total_invitations', 1)
            ->assertJsonPath('data.total_visitors', 10)
            ->assertJsonPath('data.total_guestbook_messages', 1)
            ->assertJsonPath('data.total_attendance', 2)
            ->assertJsonPath('data.total_gifts_received', null);
    }

    #[Test]
    public function a_new_customer_with_no_invitations_sees_clean_zeros_not_an_error(): void
    {
        $customer = User::factory()->create();

        $this->apiAs($customer)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.total_invitations', 0)
            ->assertJsonPath('data.total_visitors', 0);
    }

    #[Test]
    public function an_admin_sees_platform_wide_numbers_across_all_customers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Invitation::factory()->count(2)->create();

        $response = $this->apiAs($admin)->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.total_invitations', 2)
            ->assertJsonPath('data.total_revenue', null)
            ->assertJsonStructure(['data' => ['total_users', 'total_customers', 'visitor_chart']]);
    }
}
