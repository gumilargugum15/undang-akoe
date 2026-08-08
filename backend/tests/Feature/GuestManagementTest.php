<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_owner_can_add_list_and_remove_a_guest(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $store = $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/guests", [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'category' => 'VIP',
        ]);

        $store->assertCreated()->assertJsonPath('data.name', 'Budi Santoso');
        $token = $store->json('data.slug_token');
        $this->assertNotEmpty($token);

        $list = $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/guests");
        $list->assertOk()->assertJsonCount(1, 'data');

        $guestId = $store->json('data.id');
        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitation->id}/guests/{$guestId}")->assertOk();
        $this->assertSoftDeleted('guests', ['id' => $guestId]);
    }

    #[Test]
    public function a_guest_name_is_required(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/guests", [])
            ->assertUnprocessable()->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function a_stranger_cannot_manage_someone_elses_guests(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->postJson("/api/invitations/{$invitation->id}/guests", ['name' => 'Hijack'])
            ->assertForbidden();
        $this->apiAs($stranger)->getJson("/api/invitations/{$invitation->id}/guests")->assertForbidden();
    }

    #[Test]
    public function a_guest_cannot_be_added_past_the_packages_max_guests_limit(): void
    {
        $owner = User::factory()->create();
        $package = Package::factory()->create(['max_guests' => 1]);
        $invitation = Invitation::factory()->for($owner, 'user')->create(['package_id' => $package->id]);
        $invitation->guests()->create(['name' => 'Tamu Pertama']);

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/guests", ['name' => 'Tamu Kedua'])
            ->assertUnprocessable()->assertJsonValidationErrors(['guests']);

        $this->assertSame(1, $invitation->guests()->count());
    }

    #[Test]
    public function a_package_with_unlimited_guests_has_no_cap(): void
    {
        $owner = User::factory()->create();
        $package = Package::factory()->create(['max_guests' => null]);
        $invitation = Invitation::factory()->for($owner, 'user')->create(['package_id' => $package->id]);
        $invitation->guests()->create(['name' => 'Tamu Pertama']);

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/guests", ['name' => 'Tamu Kedua'])
            ->assertCreated();
    }

    #[Test]
    public function a_guest_cannot_be_added_to_an_invitation_past_its_active_period(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create(['expires_at' => now()->subDay()]);

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/guests", ['name' => 'Tamu Telat'])
            ->assertUnprocessable()->assertJsonValidationErrors(['invitation']);
    }

    #[Test]
    public function a_guest_from_another_invitation_cannot_be_deleted_through_this_invitation(): void
    {
        $owner = User::factory()->create();
        $invitationA = Invitation::factory()->for($owner, 'user')->create();
        $invitationB = Invitation::factory()->for($owner, 'user')->create();
        $guest = $invitationB->guests()->create(['name' => 'Someone Else']);

        $this->apiAs($owner)->deleteJson("/api/invitations/{$invitationA->id}/guests/{$guest->id}")
            ->assertNotFound();
    }
}
