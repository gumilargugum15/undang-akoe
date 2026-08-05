<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_customer_can_create_an_invitation_with_an_auto_generated_slug(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $theme = Theme::factory()->create(['status' => 'published']);

        $response = $this->apiAs($customer)->postJson('/api/invitations', [
            'title' => 'Andi & Sari',
            'theme_id' => $theme->id,
            'event_category' => 'wedding',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'andi-sari')
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('invitations', ['title' => 'Andi & Sari', 'user_id' => $customer->id]);
    }

    #[Test]
    public function an_unverified_customer_cannot_create_an_invitation(): void
    {
        $customer = User::factory()->unverified()->create(['role' => 'customer']);
        $theme = Theme::factory()->create(['status' => 'published']);

        $this->apiAs($customer)->postJson('/api/invitations', [
            'title' => 'Andi & Sari',
            'theme_id' => $theme->id,
            'event_category' => 'wedding',
        ])->assertForbidden();
    }

    #[Test]
    public function an_unverified_admin_can_still_create_an_invitation(): void
    {
        $admin = User::factory()->unverified()->create(['role' => 'admin']);
        $theme = Theme::factory()->create(['status' => 'published']);

        $this->apiAs($admin)->postJson('/api/invitations', [
            'title' => 'Andi & Sari',
            'theme_id' => $theme->id,
            'event_category' => 'wedding',
        ])->assertCreated();
    }

    #[Test]
    public function a_duplicate_title_gets_an_auto_suffixed_slug(): void
    {
        $customer = User::factory()->create();
        $theme = Theme::factory()->create(['status' => 'published']);

        $this->apiAs($customer)->postJson('/api/invitations', [
            'title' => 'Andi & Sari', 'theme_id' => $theme->id, 'event_category' => 'wedding',
        ])->assertCreated();

        $second = $this->apiAs($customer)->postJson('/api/invitations', [
            'title' => 'Andi & Sari', 'theme_id' => $theme->id, 'event_category' => 'wedding',
        ]);

        $second->assertCreated()->assertJsonPath('data.slug', 'andi-sari-2');
    }

    #[Test]
    public function a_customer_cannot_select_a_draft_theme_but_an_admin_can(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $draftTheme = Theme::factory()->draft()->create();

        $this->apiAs($customer)->postJson('/api/invitations', [
            'title' => 'Test', 'theme_id' => $draftTheme->id, 'event_category' => 'wedding',
        ])->assertUnprocessable()->assertJsonValidationErrors(['theme_id']);

        $this->apiAs($admin)->postJson('/api/invitations', [
            'title' => 'Test', 'theme_id' => $draftTheme->id, 'event_category' => 'wedding',
        ])->assertCreated();
    }

    #[Test]
    public function a_customer_only_sees_their_own_invitations_while_an_admin_sees_all(): void
    {
        $customerA = User::factory()->create();
        $customerB = User::factory()->create();
        Invitation::factory()->for($customerA, 'user')->create();
        Invitation::factory()->for($customerB, 'user')->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->apiAs($customerA)->getJson('/api/invitations')->assertOk()->assertJsonCount(1, 'data');
        $this->apiAs($admin)->getJson('/api/invitations')->assertOk()->assertJsonCount(2, 'data');
    }

    #[Test]
    public function a_customer_cannot_view_another_customers_invitation(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->getJson("/api/invitations/{$invitation->id}")->assertForbidden();
        $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}")->assertOk();
    }

    #[Test]
    public function an_owner_can_update_and_delete_their_invitation(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}", ['title' => 'Updated Title'])
            ->assertOk()->assertJsonPath('data.title', 'Updated Title');

        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}")->assertOk();
        $this->assertSoftDeleted('invitations', ['id' => $invitation->id]);
    }

    #[Test]
    public function a_stranger_cannot_update_or_delete_someone_elses_invitation(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->putJson("/api/invitations/{$invitation->id}", ['title' => 'Hijack'])->assertForbidden();
        $this->apiAs($stranger)->deleteJson("/api/invitations/{$invitation->id}")->assertForbidden();
    }

    #[Test]
    public function an_owner_can_publish_and_unpublish_their_invitation(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create(['status' => 'draft']);

        $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/publish")
            ->assertOk()->assertJsonPath('data.status', 'published');

        $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/unpublish")
            ->assertOk()->assertJsonPath('data.status', 'draft');
    }

    #[Test]
    public function only_an_admin_can_suspend_an_invitation(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();

        $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/suspend")->assertForbidden();

        $this->apiAs($admin)->patchJson("/api/invitations/{$invitation->id}/suspend")
            ->assertOk()->assertJsonPath('data.status', 'suspended');
    }

    #[Test]
    public function an_owner_cannot_self_publish_a_suspended_invitation(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->suspended()->create();

        $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/publish")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function an_admin_can_reactivate_a_suspended_invitation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $invitation = Invitation::factory()->suspended()->create(['published_at' => now()]);

        $this->apiAs($admin)->patchJson("/api/invitations/{$invitation->id}/reactivate")
            ->assertOk()->assertJsonPath('data.status', 'published');
    }

    #[Test]
    public function an_owner_can_upload_and_remove_a_cover_photo_regardless_of_category(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create(['event_category' => 'khitan']);

        $upload = $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/cover-photo", [
            'photo' => UploadedFile::fake()->image('cover.jpg', 1600, 1600),
        ]);

        $upload->assertOk();
        $this->assertNotNull($upload->json('data.cover_photo'));
        $invitation->refresh();
        $this->assertNotNull($invitation->cover_photo);
        Storage::disk('public')->assertExists($invitation->cover_photo);

        $remove = $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}/cover-photo");

        $remove->assertOk()->assertJsonPath('data.cover_photo', null);
        $this->assertNull($invitation->refresh()->cover_photo);
    }

    #[Test]
    public function a_stranger_cannot_upload_a_cover_photo_to_someone_elses_invitation(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->post("/api/invitations/{$invitation->id}/cover-photo", [
            'photo' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertForbidden();
    }

    #[Test]
    public function a_non_image_file_is_rejected_as_a_cover_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/cover-photo", [
            'photo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['photo']);
    }
}
