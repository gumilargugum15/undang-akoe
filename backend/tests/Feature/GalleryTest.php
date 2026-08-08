<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_owner_can_upload_a_single_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $response = $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'photo',
            'caption' => 'Foto prewedding',
            'file' => UploadedFile::fake()->image('photo.jpg', 1000, 1000),
        ]);

        $response->assertCreated()->assertJsonPath('data.type', 'photo');
        $this->assertDatabaseCount('galleries', 1);
    }

    #[Test]
    public function a_non_image_file_is_rejected_for_type_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function a_youtube_video_requires_a_recognizable_youtube_url(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'video_youtube',
            'external_url' => 'https://vimeo.com/12345',
        ])->assertUnprocessable()->assertJsonValidationErrors(['external_url']);

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'video_youtube',
            'external_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertCreated();
    }

    #[Test]
    public function bulk_upload_creates_one_row_per_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $response = $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery/bulk", [
            'category' => 'venue',
            'photos' => [
                UploadedFile::fake()->image('a.jpg', 500, 500),
                UploadedFile::fake()->image('b.jpg', 500, 500),
                UploadedFile::fake()->image('c.jpg', 500, 500),
            ],
        ]);

        $response->assertCreated()->assertJsonCount(3, 'data');
        $this->assertDatabaseCount('galleries', 3);
    }

    #[Test]
    public function a_photo_upload_is_rejected_past_the_packages_max_photos_limit(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $package = Package::factory()->create(['max_photos' => 1]);
        $invitation = Invitation::factory()->for($owner, 'user')->create(['package_id' => $package->id]);

        $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->image('a.jpg'),
        ])->assertCreated();

        $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->image('b.jpg'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['gallery']);

        $this->assertDatabaseCount('galleries', 1);
    }

    #[Test]
    public function a_bulk_upload_is_rejected_as_a_whole_if_it_would_exceed_max_photos(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $package = Package::factory()->create(['max_photos' => 2]);
        $invitation = Invitation::factory()->for($owner, 'user')->create(['package_id' => $package->id]);

        $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery/bulk", [
            'photos' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
                UploadedFile::fake()->image('c.jpg'),
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors(['gallery']);

        $this->assertDatabaseCount('galleries', 0);
    }

    #[Test]
    public function a_video_upload_does_not_count_against_max_photos(): void
    {
        $owner = User::factory()->create();
        $package = Package::factory()->create(['max_photos' => 1]);
        $invitation = Invitation::factory()->for($owner, 'user')->create(['package_id' => $package->id]);

        Storage::fake('public');
        $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->image('a.jpg'),
        ])->assertCreated();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'video_youtube',
            'external_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertCreated();
    }

    #[Test]
    public function a_photo_cannot_be_added_to_an_invitation_past_its_active_period(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create(['expires_at' => now()->subDay()]);

        $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->image('a.jpg'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['invitation']);
    }

    #[Test]
    public function deleting_a_gallery_item_removes_its_stored_file(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $create = $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'photo',
            'file' => UploadedFile::fake()->image('photo.jpg', 600, 600),
        ]);
        $itemId = $create->json('data.id');
        $path = str_replace('/storage/', '', parse_url($create->json('data.url'), PHP_URL_PATH));
        Storage::disk('public')->assertExists($path);

        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}/gallery/{$itemId}")->assertOk();

        Storage::disk('public')->assertMissing($path);
    }

    #[Test]
    public function filtering_by_type_and_category_works(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'video_youtube', 'external_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertCreated();

        Storage::fake('public');
        $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/gallery", [
            'type' => 'photo', 'category' => 'venue', 'file' => UploadedFile::fake()->image('a.jpg'),
        ])->assertCreated();

        $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/gallery?type=video_youtube")
            ->assertOk()->assertJsonCount(1, 'data');
        $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/gallery?category=venue")
            ->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function a_stranger_cannot_manage_someone_elses_gallery(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->getJson("/api/invitations/{$invitation->id}/gallery")->assertForbidden();
    }
}
