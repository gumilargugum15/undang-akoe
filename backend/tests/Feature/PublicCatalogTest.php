<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_guest_can_browse_published_themes_without_logging_in(): void
    {
        Theme::factory()->create(['status' => 'published']);
        Theme::factory()->draft()->create();

        $response = $this->getJson('/api/public/themes');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function a_guest_can_browse_active_packages_without_logging_in(): void
    {
        Package::factory()->create();
        Package::factory()->inactive()->create();

        $response = $this->getJson('/api/public/packages');

        $response->assertOk()->assertJsonCount(1, 'data');
    }
}
