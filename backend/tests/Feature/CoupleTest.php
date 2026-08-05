<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CoupleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_owner_can_upsert_groom_and_bride_data(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/couples/groom", [
            'nickname' => 'Andi',
            'full_name' => 'Andi Wijaya',
        ])->assertOk()->assertJsonPath('data.role', 'groom');

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/couples/bride", [
            'nickname' => 'Sari',
            'full_name' => 'Sari Puspita',
        ])->assertOk()->assertJsonPath('data.role', 'bride');

        $response = $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/couples");
        $response->assertOk()->assertJsonCount(2, 'data');
    }

    #[Test]
    public function upserting_the_same_role_twice_updates_rather_than_duplicates(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/couples/groom", [
            'nickname' => 'Andi', 'full_name' => 'Andi Wijaya',
        ])->assertOk();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/couples/groom", [
            'nickname' => 'Andi', 'full_name' => 'Andi Wijaya Kusuma',
        ])->assertOk()->assertJsonPath('data.full_name', 'Andi Wijaya Kusuma');

        $this->assertDatabaseCount('couples', 1);
    }

    #[Test]
    public function an_invalid_role_is_rejected_at_the_route_level(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}/couples/uncle", [
            'nickname' => 'x', 'full_name' => 'y',
        ])->assertNotFound();
    }

    #[Test]
    public function uploading_a_photo_replaces_and_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $first = $this->apiAs($owner)->put("/api/invitations/{$invitation->id}/couples/groom", [
            'nickname' => 'Andi',
            'full_name' => 'Andi Wijaya',
            'photo' => UploadedFile::fake()->image('first.jpg', 800, 800),
        ]);
        $first->assertOk();
        $firstPath = parse_url($first->json('data.photo'), PHP_URL_PATH);
        $firstPath = str_replace('/storage/', '', $firstPath);
        Storage::disk('public')->assertExists($firstPath);

        $second = $this->apiAs($owner)->put("/api/invitations/{$invitation->id}/couples/groom", [
            'nickname' => 'Andi',
            'full_name' => 'Andi Wijaya',
            'photo' => UploadedFile::fake()->image('second.jpg', 800, 800),
        ]);
        $second->assertOk();

        Storage::disk('public')->assertMissing($firstPath);
    }

    #[Test]
    public function a_stranger_cannot_manage_someone_elses_couples(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->putJson("/api/invitations/{$invitation->id}/couples/groom", [
            'nickname' => 'Hijack', 'full_name' => 'Hijack Attempt',
        ])->assertForbidden();
    }
}
