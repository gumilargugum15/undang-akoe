<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Theme;
use App\Models\ThemeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ThemeManagementTest extends TestCase
{
    use RefreshDatabase;

    private function validConfig(): array
    {
        return [
            'ornament' => 'floral',
            'reveal' => 'fade',
            'radius' => '0.5rem',
            'cardRadius' => '1rem',
            'shadow' => 'none',
            'buttonShadow' => 'none',
            'letterSpacing' => '0.02em',
            'headWeight' => '500',
            'fonts' => ['head' => 'serif', 'body' => 'sans-serif', 'script' => 'cursive'],
            'tokens' => [
                'bg' => '#fff', 'bgAlt' => '#eee', 'surface' => '#fff', 'primary' => '#333',
                'primaryFg' => '#fff', 'secondary' => '#999', 'accent' => '#ccc',
                'text' => '#222', 'muted' => '#777', 'border' => '#ddd',
            ],
            'swatch' => ['#fff', '#333'],
            'texture' => 'none',
        ];
    }

    #[Test]
    public function a_customer_cannot_manage_theme_categories_or_themes(): void
    {
        $customer = User::factory()->create();

        $this->apiAs($customer)->postJson('/api/theme-categories', ['name' => 'Birthday'])->assertForbidden();

        $category = ThemeCategory::factory()->create();
        $this->apiAs($customer)->postJson('/api/themes', [
            'theme_category_id' => $category->id, 'name' => 'Test', 'type' => 'free', 'config' => $this->validConfig(),
        ])->assertForbidden();
    }

    #[Test]
    public function an_admin_can_create_a_theme_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->apiAs($admin)->postJson('/api/theme-categories', ['name' => 'Birthday']);

        $response->assertCreated();
        $this->assertDatabaseHas('theme_categories', ['name' => 'Birthday', 'slug' => 'birthday']);
    }

    #[Test]
    public function a_category_still_in_use_by_a_theme_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ThemeCategory::factory()->create();
        Theme::factory()->create(['theme_category_id' => $category->id]);

        $this->apiAs($admin)->deleteJson("/api/theme-categories/{$category->id}")
            ->assertUnprocessable()->assertJsonValidationErrors(['category']);
    }

    #[Test]
    public function an_admin_can_create_a_theme_with_a_thumbnail(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ThemeCategory::factory()->create();

        $response = $this->apiAs($admin)->post('/api/themes', [
            'theme_category_id' => $category->id,
            'name' => 'Garden Party',
            'type' => 'free',
            'config' => $this->validConfig(),
            'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 800, 800),
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Garden Party');
        $this->assertNotNull($response->json('data.thumbnail'));
        $this->assertDatabaseHas('themes', ['name' => 'Garden Party', 'slug' => 'garden-party']);
    }

    #[Test]
    public function creating_a_theme_requires_a_complete_config_shape(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ThemeCategory::factory()->create();

        $this->apiAs($admin)->postJson('/api/themes', [
            'theme_category_id' => $category->id,
            'name' => 'Incomplete',
            'type' => 'free',
            'config' => ['ornament' => 'floral'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['config.fonts', 'config.tokens', 'config.swatch']);
    }

    #[Test]
    public function a_premium_theme_requires_a_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ThemeCategory::factory()->create();

        $this->apiAs($admin)->postJson('/api/themes', [
            'theme_category_id' => $category->id, 'name' => 'Luxury', 'type' => 'premium', 'config' => $this->validConfig(),
        ])->assertUnprocessable()->assertJsonValidationErrors(['price']);
    }

    #[Test]
    public function an_admin_can_publish_unpublish_and_duplicate_a_theme(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $theme = Theme::factory()->draft()->create();

        $this->apiAs($admin)->patchJson("/api/themes/{$theme->id}/publish")
            ->assertOk()->assertJsonPath('data.status', 'published');

        $this->apiAs($admin)->patchJson("/api/themes/{$theme->id}/unpublish")
            ->assertOk()->assertJsonPath('data.status', 'draft');

        $duplicate = $this->apiAs($admin)->postJson("/api/themes/{$theme->id}/duplicate");
        $duplicate->assertCreated()->assertJsonPath('data.status', 'draft');
        $this->assertDatabaseCount('themes', 2);
        $this->assertNotEquals($theme->slug, $duplicate->json('data.slug'));
    }

    #[Test]
    public function a_theme_still_used_by_an_invitation_cannot_be_deleted_but_can_be_deactivated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $theme = Theme::factory()->create();
        Invitation::factory()->create(['theme_id' => $theme->id]);

        $this->apiAs($admin)->deleteJson("/api/themes/{$theme->id}")
            ->assertUnprocessable()->assertJsonValidationErrors(['theme']);

        $this->apiAs($admin)->putJson("/api/themes/{$theme->id}", ['is_active' => false])
            ->assertOk()->assertJsonPath('data.is_active', false);
    }

    #[Test]
    public function an_unused_theme_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $theme = Theme::factory()->create();

        $this->apiAs($admin)->deleteJson("/api/themes/{$theme->id}")->assertOk();
        $this->assertSoftDeleted('themes', ['id' => $theme->id]);
    }

    #[Test]
    public function the_admin_theme_list_is_paginated_and_includes_drafts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Theme::factory()->count(2)->create(['status' => 'published']);
        Theme::factory()->draft()->create();

        $response = $this->apiAs($admin)->getJson('/api/themes');

        $response->assertOk()->assertJsonCount(3, 'data')->assertJsonStructure(['meta' => ['total']]);
    }

    #[Test]
    public function the_customer_theme_list_only_shows_active_published_themes(): void
    {
        $customer = User::factory()->create();
        Theme::factory()->create(['status' => 'published']);
        Theme::factory()->draft()->create();
        Theme::factory()->inactive()->create(['status' => 'published']);

        $this->apiAs($customer)->getJson('/api/themes')->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function preview_by_slug_returns_the_same_theme_as_by_id(): void
    {
        $customer = User::factory()->create();
        $theme = Theme::factory()->create();

        $this->apiAs($customer)->getJson("/api/themes/preview/{$theme->slug}")
            ->assertOk()->assertJsonPath('data.slug', $theme->slug);
    }

    #[Test]
    public function an_owner_can_change_their_invitations_theme_and_it_resets_customizations(): void
    {
        $owner = User::factory()->create();
        $oldTheme = Theme::factory()->create();
        $newTheme = Theme::factory()->create(['status' => 'published']);
        $invitation = Invitation::factory()->for($owner, 'user')->create([
            'theme_id' => $oldTheme->id,
            'theme_settings' => ['tokens' => ['primary' => '#ff0000']],
        ]);

        $response = $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/change-theme", [
            'theme_id' => $newTheme->id,
        ]);

        $response->assertOk();
        $invitation->refresh();
        $this->assertEquals($newTheme->id, $invitation->theme_id);
        $this->assertNull($invitation->theme_settings);
    }

    #[Test]
    public function a_customer_cannot_change_theme_to_a_draft_theme(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();
        $draftTheme = Theme::factory()->draft()->create();

        $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/change-theme", [
            'theme_id' => $draftTheme->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['theme_id']);
    }

    #[Test]
    public function a_customer_can_change_the_theme_of_a_published_invitation(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create([
            'theme_settings' => ['tokens' => ['primary' => '#ff0000']],
        ]);
        $newTheme = Theme::factory()->create(['status' => 'published']);

        // Cosmetic-only change — allowed at any status so a customer can keep trying themes
        // until it matches what they pictured, even after the invitation is already live.
        $this->apiAs($owner)->patchJson("/api/invitations/{$invitation->id}/change-theme", [
            'theme_id' => $newTheme->id,
        ])->assertOk();

        $invitation->refresh();
        $this->assertSame($newTheme->id, $invitation->theme_id);
        $this->assertNull($invitation->theme_settings);
        $this->assertSame('published', $invitation->status);
    }

    #[Test]
    public function the_general_update_endpoint_also_allows_changing_the_theme_on_a_published_invitation(): void
    {
        $owner = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->published()->create();
        $newTheme = Theme::factory()->create(['status' => 'published']);

        $this->apiAs($owner)->putJson("/api/invitations/{$invitation->id}", [
            'theme_id' => $newTheme->id,
        ])->assertOk();

        $this->assertSame($newTheme->id, $invitation->fresh()->theme_id);
    }

    #[Test]
    public function a_stranger_cannot_change_someone_elses_invitation_theme(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $invitation = Invitation::factory()->for($owner, 'user')->create();
        $newTheme = Theme::factory()->create(['status' => 'published']);

        $this->apiAs($stranger)->patchJson("/api/invitations/{$invitation->id}/change-theme", [
            'theme_id' => $newTheme->id,
        ])->assertForbidden();
    }
}
