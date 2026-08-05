<?php

namespace Tests\Feature;

use App\Models\DigitalEnvelope;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DigitalEnvelopeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_owner_can_add_a_bank_envelope(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $response = $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/envelopes", [
            'type' => 'bank', 'provider_name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'Sari Puspita',
        ]);

        $response->assertCreated()->assertJsonPath('data.provider_name', 'BCA');
    }

    #[Test]
    public function an_ewallet_provider_must_be_one_of_the_fixed_list(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/envelopes", [
            'type' => 'ewallet', 'provider_name' => 'PayPal', 'account_number' => '0812', 'account_holder' => 'Andi',
        ])->assertUnprocessable()->assertJsonValidationErrors(['provider_name']);

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/envelopes", [
            'type' => 'ewallet', 'provider_name' => 'GoPay', 'account_number' => '0812', 'account_holder' => 'Andi',
        ])->assertCreated();
    }

    #[Test]
    public function qris_requires_an_image_and_defaults_its_provider_name(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($owner)->postJson("/api/invitations/{$invitation->id}/envelopes", [
            'type' => 'qris',
        ])->assertUnprocessable()->assertJsonValidationErrors(['qr_image']);

        $response = $this->apiAs($owner)->post("/api/invitations/{$invitation->id}/envelopes", [
            'type' => 'qris',
            'qr_image' => UploadedFile::fake()->image('qr.png', 400, 400),
        ]);

        $response->assertCreated()->assertJsonPath('data.provider_name', 'QRIS');
    }

    #[Test]
    public function the_public_endpoint_only_shows_active_envelopes_on_a_live_invitation(): void
    {
        $invitation = Invitation::factory()->published()->create();
        DigitalEnvelope::factory()->for($invitation, 'invitation')->create(['is_active' => true]);
        DigitalEnvelope::factory()->inactive()->for($invitation, 'invitation')->create();

        $this->getJson("/api/public/invitations/{$invitation->slug}/envelopes")
            ->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function the_public_endpoint_404s_for_a_draft_invitation(): void
    {
        $invitation = Invitation::factory()->create(['status' => 'draft']);
        DigitalEnvelope::factory()->for($invitation, 'invitation')->create();

        $this->getJson("/api/public/invitations/{$invitation->slug}/envelopes")->assertNotFound();
    }

    #[Test]
    public function an_owner_sees_inactive_envelopes_too(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();
        DigitalEnvelope::factory()->inactive()->for($invitation, 'invitation')->create();

        $this->apiAs($owner)->getJson("/api/invitations/{$invitation->id}/envelopes")
            ->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function a_stranger_cannot_manage_someone_elses_envelopes(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();

        $this->apiAs($stranger)->postJson("/api/invitations/{$invitation->id}/envelopes", [
            'type' => 'bank', 'provider_name' => 'Mandiri', 'account_number' => '111', 'account_holder' => 'Hack',
        ])->assertForbidden();
    }
}
