<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\LoveStory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoveStoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_owner_can_create_list_update_and_delete_a_love_story(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $create = $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/love-stories", [
            'title' => 'Pertama Bertemu',
            'story_date' => '2022-01-14',
            'description' => 'Bertemu pertama kali di kampus.',
        ]);
        $create->assertCreated()->assertJsonPath('data.title', 'Pertama Bertemu');
        $storyId = $create->json('data.id');

        $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/love-stories")
            ->assertOk()->assertJsonCount(1, 'data');

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/love-stories/{$storyId}", [
            'title' => 'Pertama Bertemu (Updated)',
        ])->assertOk()->assertJsonPath('data.title', 'Pertama Bertemu (Updated)');

        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}/love-stories/{$storyId}")->assertOk();
        $this->assertSoftDeleted('love_stories', ['id' => $storyId]);
    }

    #[Test]
    public function a_love_story_can_be_created_with_a_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $response = $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/love-stories", [
            'title' => 'Lamaran',
            'photo' => UploadedFile::fake()->image('lamaran.jpg', 800, 800),
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.photo'));
    }

    #[Test]
    public function title_is_required(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/love-stories", [
            'description' => 'Tanpa judul',
        ])->assertUnprocessable()->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function a_story_belonging_to_another_invitation_cannot_be_accessed_via_a_different_invitation_id(): void
    {
        $owner = User::factory()->create();
        $invitationA = Invitation::factory()->for($owner, 'user')->create();
        $invitationB = Invitation::factory()->for($owner, 'user')->create();
        $story = LoveStory::factory()->for($invitationA, 'invitation')->create();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitationB->id}/love-stories/{$story->id}", [
            'title' => 'Hijack',
        ])->assertNotFound();
    }

    #[Test]
    public function a_stranger_cannot_manage_someone_elses_love_stories(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->postJson("/api/invitations/{$invitation->id}/love-stories", [
            'title' => 'Hijack',
        ])->assertForbidden();
    }
}
