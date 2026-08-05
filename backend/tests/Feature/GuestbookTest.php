<?php

namespace Tests\Feature;

use App\Models\Guestbook;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestbookTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_guest_cannot_rsvp_to_a_draft_invitation(): void
    {
        $invitation = Invitation::factory()->create(['status' => 'draft']);

        $this->postJson("/api/public/invitations/{$invitation->slug}/rsvp", [
            'guest_name' => 'Dinda', 'attendance' => 'hadir', 'guest_count' => 2, 'message' => 'Selamat!',
        ])->assertNotFound();
    }

    #[Test]
    public function a_guest_can_rsvp_to_a_published_invitation_without_authentication(): void
    {
        $invitation = Invitation::factory()->published()->create();

        $response = $this->postJson("/api/public/invitations/{$invitation->slug}/rsvp", [
            'guest_name' => 'Dinda', 'attendance' => 'hadir', 'guest_count' => 2, 'message' => 'Selamat menempuh hidup baru!',
        ]);

        $response->assertCreated()->assertJsonPath('data.guest_name', 'Dinda');
        $this->assertDatabaseHas('guestbooks', ['guest_name' => 'Dinda', 'invitation_id' => $invitation->id]);
    }

    #[Test]
    public function guest_count_is_forced_to_one_when_not_attending_even_if_spoofed(): void
    {
        $invitation = Invitation::factory()->published()->create();

        $response = $this->postJson("/api/public/invitations/{$invitation->slug}/rsvp", [
            'guest_name' => 'Bagas', 'attendance' => 'tidak_hadir', 'guest_count' => 5, 'message' => 'Maaf tidak bisa hadir.',
        ]);

        $response->assertCreated()->assertJsonPath('data.guest_count', 1);
    }

    #[Test]
    public function a_message_is_required_to_submit_rsvp(): void
    {
        $invitation = Invitation::factory()->published()->create();

        $this->postJson("/api/public/invitations/{$invitation->slug}/rsvp", [
            'guest_name' => 'Tanpa Pesan', 'attendance' => 'ragu',
        ])->assertUnprocessable()->assertJsonValidationErrors(['message']);
    }

    #[Test]
    public function the_public_wall_only_shows_approved_entries(): void
    {
        $invitation = Invitation::factory()->published()->create();
        Guestbook::factory()->for($invitation, 'invitation')->create(['guest_name' => 'Approved']);
        Guestbook::factory()->pending()->for($invitation, 'invitation')->create(['guest_name' => 'Hidden']);

        $response = $this->getJson("/api/public/invitations/{$invitation->slug}/guestbook");

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.guest_name', 'Approved');
    }

    #[Test]
    public function an_owner_can_see_a_summary_of_attendance(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();
        Guestbook::factory()->for($invitation, 'invitation')->create(['attendance' => 'hadir', 'guest_count' => 2]);
        Guestbook::factory()->for($invitation, 'invitation')->create(['attendance' => 'hadir', 'guest_count' => 3]);
        Guestbook::factory()->for($invitation, 'invitation')->create(['attendance' => 'tidak_hadir', 'guest_count' => 1]);

        $response = $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/rsvp/summary");

        $response->assertOk()
            ->assertJsonPath('data.total_submissions', 3)
            ->assertJsonPath('data.hadir.submissions', 2)
            ->assertJsonPath('data.hadir.guests', 5)
            ->assertJsonPath('data.tidak_hadir.submissions', 1);
    }

    #[Test]
    public function an_owner_can_reject_an_entry_and_it_disappears_from_the_public_wall(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();
        $entry = Guestbook::factory()->for($invitation, 'invitation')->create();

        $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/rsvp/{$entry->id}/reject")
            ->assertOk()->assertJsonPath('data.is_approved', false);

        $this->getJson("/api/public/invitations/{$invitation->slug}/guestbook")
            ->assertOk()->assertJsonCount(0, 'data');
    }

    #[Test]
    public function an_owner_can_approve_a_previously_rejected_entry(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();
        $entry = Guestbook::factory()->pending()->for($invitation, 'invitation')->create();

        $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/rsvp/{$entry->id}/approve")
            ->assertOk()->assertJsonPath('data.is_approved', true);
    }

    #[Test]
    public function a_stranger_cannot_moderate_someone_elses_guestbook(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();
        $entry = Guestbook::factory()->for($invitation, 'invitation')->create();

        $this->apiAs($stranger)->patchJson("/api/invitations/{$invitation->id}/rsvp/{$entry->id}/reject")->assertForbidden();
    }

    #[Test]
    public function an_owner_can_delete_a_guestbook_entry(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();
        $entry = Guestbook::factory()->for($invitation, 'invitation')->create();

        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}/rsvp/{$entry->id}")->assertOk();
        $this->assertSoftDeleted('guestbooks', ['id' => $entry->id]);
    }
}
