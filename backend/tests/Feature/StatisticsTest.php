<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function tracking_a_visit_on_a_draft_invitation_404s(): void
    {
        $invitation = Invitation::factory()->create(['status' => 'draft']);

        $this->postJson("/api/public/invitations/{$invitation->slug}/visit", [
            'session_id' => 'sess-1',
        ])->assertNotFound();
    }

    #[Test]
    public function tracking_a_visit_increments_the_invitations_view_count(): void
    {
        $invitation = Invitation::factory()->published()->create();

        $this->postJson("/api/public/invitations/{$invitation->slug}/visit", [
            'session_id' => 'sess-1',
        ], ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'])
            ->assertCreated();

        $this->assertEquals(1, $invitation->fresh()->view_count);
        $this->assertDatabaseHas('invitation_visits', [
            'invitation_id' => $invitation->id,
            'session_id' => 'sess-1',
            'device' => 'desktop',
            'browser' => 'Chrome',
        ]);
    }

    #[Test]
    public function repeat_visits_from_the_same_session_do_not_count_as_new_unique_visitors(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();

        $this->postJson("/api/public/invitations/{$invitation->slug}/visit", ['session_id' => 'sess-A']);
        $this->postJson("/api/public/invitations/{$invitation->slug}/visit", ['session_id' => 'sess-A']);
        $this->postJson("/api/public/invitations/{$invitation->slug}/visit", ['session_id' => 'sess-B']);

        $summary = $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/statistics");

        $summary->assertOk()
            ->assertJsonPath('data.total_views', 3)
            ->assertJsonPath('data.unique_visitors', 2);
    }

    #[Test]
    public function a_stranger_cannot_view_someone_elses_statistics(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();

        $this->apiAs($stranger)->getJson("/api/invitations/{$invitation->id}/statistics")->assertForbidden();
    }
}
