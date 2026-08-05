<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Music;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MusicManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_invitation_with_no_music_returns_null(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/music")
            ->assertOk()->assertJsonPath('data', null);
    }

    #[Test]
    public function an_owner_can_upload_a_music_file(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $response = $this->apiAs($owner)->put("/api/invitations/{$invitation->id}/music", [
            'source' => 'upload',
            'title' => 'Lagu Kami',
            'artist' => 'Penyanyi',
            'file' => UploadedFile::fake()->create('song.mp3', 500, 'audio/mpeg'),
        ]);

        $response->assertOk()->assertJsonPath('data.source', 'upload');
        $this->assertNotNull($response->json('data.url'));
        $this->assertDatabaseHas('musics', ['invitation_id' => $invitation->id, 'title' => 'Lagu Kami']);
    }

    #[Test]
    public function upload_source_without_any_file_and_no_existing_track_fails(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/music", [
            'source' => 'upload',
        ])->assertUnprocessable()->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function an_owner_can_set_a_spotify_link_without_uploading_a_file(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $response = $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/music", [
            'source' => 'spotify',
            'external_url' => 'https://open.spotify.com/track/abc123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.source', 'spotify')
            ->assertJsonPath('data.url', 'https://open.spotify.com/track/abc123');
    }

    #[Test]
    public function an_external_url_is_required_for_non_upload_sources(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/music", [
            'source' => 'youtube_music',
        ])->assertUnprocessable()->assertJsonValidationErrors(['external_url']);
    }

    #[Test]
    public function switching_source_replaces_the_previous_track_instead_of_creating_a_second_row(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->put("/api/invitations/{$invitation->id}/music", [
            'source' => 'upload',
            'file' => UploadedFile::fake()->create('song.mp3', 500, 'audio/mpeg'),
        ])->assertOk();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/music", [
            'source' => 'spotify',
            'external_url' => 'https://open.spotify.com/track/abc123',
        ])->assertOk()->assertJsonPath('data.source', 'spotify');

        $this->assertDatabaseCount('musics', 1);
    }

    #[Test]
    public function an_owner_can_delete_their_music(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();
        Music::factory()->for($invitation, 'invitation')->create();

        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}/music")->assertOk();
        $this->assertDatabaseCount('musics', 0);
    }

    #[Test]
    public function a_stranger_cannot_manage_someone_elses_music(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->putJson("/api/invitations/{$invitation->id}/music", [
            'source' => 'spotify',
            'external_url' => 'https://open.spotify.com/track/abc123',
        ])->assertForbidden();
    }

    #[Test]
    public function the_public_page_only_exposes_active_music(): void
    {
        $invitation = Invitation::factory()->published()->create();
        Music::factory()->spotify()->create(['invitation_id' => $invitation->id, 'is_active' => false]);

        $response = $this->getJson("/api/public/invitations/{$invitation->slug}");

        $response->assertOk()->assertJsonPath('data.music', null);
    }
}
