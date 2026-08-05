<?php

namespace Tests\Feature;

use App\Models\Couple;
use App\Models\Honoree;
use App\Models\Invitation;
use App\Models\InvitationEvent;
use App\Models\LoveStory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublicInvitationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_draft_invitation_404s_and_does_not_leak_its_existence(): void
    {
        $invitation = Invitation::factory()->create(['status' => 'draft']);

        $this->getJson("/api/public/invitations/{$invitation->slug}")->assertNotFound();
    }

    #[Test]
    public function a_published_invitation_returns_everything_needed_to_render_the_page(): void
    {
        $invitation = Invitation::factory()->published()->create(['title' => 'Andi & Sari']);
        Couple::factory()->groom()->for($invitation, 'invitation')->create(['nickname' => 'Andi']);
        Couple::factory()->bride()->for($invitation, 'invitation')->create(['nickname' => 'Sari']);
        InvitationEvent::factory()->for($invitation, 'invitation')->create(['name' => 'Akad Nikah']);
        LoveStory::factory()->for($invitation, 'invitation')->create(['title' => 'Pertama Bertemu']);

        $response = $this->getJson("/api/public/invitations/{$invitation->slug}");

        $response->assertOk()
            ->assertJsonPath('data.title', 'Andi & Sari')
            ->assertJsonPath('data.couples.groom.nickname', 'Andi')
            ->assertJsonPath('data.couples.bride.nickname', 'Sari')
            ->assertJsonPath('data.events.0.name', 'Akad Nikah')
            ->assertJsonPath('data.love_stories.0.title', 'Pertama Bertemu')
            ->assertJsonStructure(['data' => ['theme' => ['id', 'tokens', 'fonts'], 'seo']]);
    }

    #[Test]
    public function missing_couples_resolve_to_null_not_an_empty_broken_object(): void
    {
        $invitation = Invitation::factory()->published()->create();

        $response = $this->getJson("/api/public/invitations/{$invitation->slug}");

        $response->assertOk()
            ->assertJsonPath('data.couples.groom', null)
            ->assertJsonPath('data.couples.bride', null);
    }

    #[Test]
    public function a_birthday_invitation_returns_honorees_as_a_list_and_couples_stay_null(): void
    {
        $invitation = Invitation::factory()->published()->create(['event_category' => 'birthday']);
        Honoree::factory()->for($invitation, 'invitation')->create([
            'role_label' => 'Yang Berulang Tahun',
            'nickname' => 'Kayla',
        ]);

        $response = $this->getJson("/api/public/invitations/{$invitation->slug}");

        $response->assertOk()
            ->assertJsonPath('data.event_category', 'birthday')
            ->assertJsonPath('data.honorees.0.nickname', 'Kayla')
            ->assertJsonPath('data.couples.groom', null)
            ->assertJsonPath('data.couples.bride', null);
    }

    #[Test]
    public function theme_settings_overrides_are_merged_into_the_base_theme_config(): void
    {
        $invitation = Invitation::factory()->published()->create([
            'theme_settings' => ['tokens' => ['primary' => '#ff0000']],
        ]);
        $baseBg = $invitation->theme->config['tokens']['bg'];

        $response = $this->getJson("/api/public/invitations/{$invitation->slug}");

        $response->assertOk()
            ->assertJsonPath('data.theme.tokens.primary', '#ff0000')
            ->assertJsonPath('data.theme.tokens.bg', $baseBg);
    }

    #[Test]
    public function a_valid_guest_token_resolves_to_that_guests_name(): void
    {
        $invitation = Invitation::factory()->published()->create();
        $guest = $invitation->guests()->create(['name' => 'Budi Santoso']);

        $response = $this->getJson("/api/public/invitations/{$invitation->slug}?to={$guest->slug_token}");

        $response->assertOk()->assertJsonPath('data.guest_name', 'Budi Santoso');
    }

    #[Test]
    public function a_missing_or_invalid_guest_token_resolves_to_null_without_erroring(): void
    {
        $invitation = Invitation::factory()->published()->create();

        $this->getJson("/api/public/invitations/{$invitation->slug}")
            ->assertOk()->assertJsonPath('data.guest_name', null);

        $this->getJson("/api/public/invitations/{$invitation->slug}?to=not-a-real-token")
            ->assertOk()->assertJsonPath('data.guest_name', null);
    }

    #[Test]
    public function a_guest_token_belonging_to_another_invitation_does_not_resolve(): void
    {
        $invitationA = Invitation::factory()->published()->create();
        $invitationB = Invitation::factory()->published()->create();
        $foreignGuest = $invitationB->guests()->create(['name' => 'Someone Else']);

        $this->getJson("/api/public/invitations/{$invitationA->slug}?to={$foreignGuest->slug_token}")
            ->assertOk()->assertJsonPath('data.guest_name', null);
    }
}
