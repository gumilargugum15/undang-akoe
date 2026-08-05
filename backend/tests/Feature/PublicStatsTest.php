<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublicStatsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_platform_wide_counts_without_auth(): void
    {
        [$owner1, $owner2] = User::factory()->count(2)->create();
        Invitation::factory()->published()->for($owner1, 'user')->create()->increment('view_count', 10);
        Invitation::factory()->published()->for($owner2, 'user')->create()->increment('view_count', 5);
        // A draft's views still count toward the platform's all-time visitor total — status only
        // scopes the "currently live invitations" count, not historical traffic.
        Invitation::factory()->for($owner1, 'user')->create(['status' => 'draft'])->increment('view_count', 100);

        $response = $this->getJson('/api/public/stats');

        $response->assertOk()
            ->assertJsonPath('data.total_customers', 2)
            ->assertJsonPath('data.total_invitations', 2)
            ->assertJsonPath('data.total_visitors', 115);
    }
}
