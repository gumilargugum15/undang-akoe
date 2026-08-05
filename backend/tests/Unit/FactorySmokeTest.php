<?php

namespace Tests\Unit;

use App\Models\Couple;
use App\Models\DigitalEnvelope;
use App\Models\Gallery;
use App\Models\Guestbook;
use App\Models\Honoree;
use App\Models\Invitation;
use App\Models\InvitationEvent;
use App\Models\Package;
use App\Models\Theme;
use App\Models\ThemeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FactorySmokeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function every_factory_creates_a_valid_row(): void
    {
        $this->assertNotNull(User::factory()->create()->id);
        $this->assertNotNull(ThemeCategory::factory()->create()->id);
        $this->assertNotNull(Theme::factory()->create()->id);
        $this->assertNotNull(Package::factory()->create()->id);
        $this->assertNotNull(Invitation::factory()->create()->id);
        $this->assertNotNull(Couple::factory()->create()->id);
        $this->assertNotNull(Honoree::factory()->create()->id);
        $this->assertNotNull(InvitationEvent::factory()->create()->id);
        $this->assertNotNull(Gallery::factory()->create()->id);
        $this->assertNotNull(Guestbook::factory()->create()->id);
        $this->assertNotNull(DigitalEnvelope::factory()->create()->id);
    }
}
