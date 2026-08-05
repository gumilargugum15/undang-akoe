<?php

namespace Tests\Feature;

use App\Models\Honoree;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HonoreeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_owner_can_create_list_update_and_delete_a_honoree(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create(['event_category' => 'birthday']);

        $create = $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/honorees", [
            'role_label' => 'Yang Berulang Tahun',
            'nickname' => 'Kayla',
            'full_name' => 'Kayla Anindya',
        ]);
        $create->assertCreated()->assertJsonPath('data.nickname', 'Kayla');
        $honoreeId = $create->json('data.id');

        $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/honorees")
            ->assertOk()->assertJsonCount(1, 'data');

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/honorees/{$honoreeId}", [
            'nickname' => 'Kayla (Updated)',
        ])->assertOk()->assertJsonPath('data.nickname', 'Kayla (Updated)');

        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}/honorees/{$honoreeId}")->assertOk();
        $this->assertSoftDeleted('honorees', ['id' => $honoreeId]);
    }

    #[Test]
    public function a_honoree_can_be_created_with_a_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create(['event_category' => 'khitan']);

        $response = $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/honorees", [
            'role_label' => 'Yang Dikhitan',
            'nickname' => 'Rafi',
            'full_name' => 'Muhammad Rafi',
            'photo' => UploadedFile::fake()->image('rafi.jpg', 800, 800),
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.photo'));
    }

    #[Test]
    public function nickname_and_full_name_are_required(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create(['event_category' => 'birthday']);

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/honorees", [
            'role_label' => 'Yang Berulang Tahun',
        ])->assertUnprocessable()->assertJsonValidationErrors(['nickname', 'full_name']);
    }

    #[Test]
    public function a_honoree_belonging_to_another_invitation_cannot_be_accessed_via_a_different_invitation_id(): void
    {
        $owner = User::factory()->create();
        $invitationA = Invitation::factory()->for($owner, 'user')->create();
        $invitationB = Invitation::factory()->for($owner, 'user')->create();
        $honoree = Honoree::factory()->for($invitationA, 'invitation')->create();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitationB->id}/honorees/{$honoree->id}", [
            'nickname' => 'Hijack',
        ])->assertNotFound();
    }

    #[Test]
    public function a_stranger_cannot_manage_someone_elses_honorees(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->postJson("/api/invitations/{$invitation->id}/honorees", [
            'role_label' => 'Hijack',
            'nickname' => 'Hijack',
            'full_name' => 'Hijack',
        ])->assertForbidden();
    }
}
