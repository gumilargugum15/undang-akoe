<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvitationEventTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_owner_can_create_list_update_and_delete_an_event(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $create = $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/events", [
            'name' => 'Akad Nikah',
            'event_date' => '2026-11-14',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'location_name' => 'Masjid Agung',
        ]);
        $create->assertCreated()->assertJsonPath('data.start_time', '09:00');
        $eventId = $create->json('data.id');

        $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/events")
            ->assertOk()->assertJsonCount(1, 'data');

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/events/{$eventId}", [
            'name' => 'Akad Nikah (Updated)',
        ])->assertOk()->assertJsonPath('data.name', 'Akad Nikah (Updated)');

        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}/events/{$eventId}")->assertOk();
        $this->assertSoftDeleted('invitation_events', ['id' => $eventId]);
    }

    #[Test]
    public function latitude_and_longitude_must_be_provided_together(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/events", [
            'name' => 'Resepsi',
            'event_date' => '2026-11-14',
            'latitude' => -6.9,
        ])->assertUnprocessable()->assertJsonValidationErrors(['longitude']);
    }

    #[Test]
    public function an_invalid_time_format_is_rejected(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/events", [
            'name' => 'Resepsi',
            'event_date' => '2026-11-14',
            'start_time' => '9am',
        ])->assertUnprocessable()->assertJsonValidationErrors(['start_time']);
    }

    #[Test]
    public function an_event_belonging_to_another_invitation_cannot_be_accessed_via_a_different_invitation_id(): void
    {
        $owner = User::factory()->create();
        $invitationA = Invitation::factory()->for($owner, 'user')->create();
        $invitationB = Invitation::factory()->for($owner, 'user')->create();
        $event = InvitationEvent::factory()->for($invitationA, 'invitation')->create();

        // IDOR check: owner owns both invitations, but the event belongs to A, not B.
        $this->apiAs($owner)->putJson("/api/invitations/{$invitationB->id}/events/{$event->id}", [
            'name' => 'Hijack',
        ])->assertNotFound();
    }

    #[Test]
    public function a_stranger_cannot_manage_someone_elses_events(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->postJson("/api/invitations/{$invitation->id}/events", [
            'name' => 'Hijack', 'event_date' => '2026-11-14',
        ])->assertForbidden();
    }
}
