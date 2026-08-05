<?php

namespace Tests\Unit;

use App\Models\Invitation;
use App\Models\Theme;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvitationThemeMergeTest extends TestCase
{
    /**
     * Pure unit test: the theme relation is injected in-memory via
     * setRelation() instead of touching the database, since this is testing
     * pure merge logic, not persistence.
     */
    #[Test]
    public function theme_settings_overrides_only_the_keys_it_specifies(): void
    {
        $theme = new Theme([
            'config' => [
                'ornament' => 'floral',
                'fonts' => ['head' => 'serif', 'body' => 'sans-serif'],
                'tokens' => ['bg' => '#ffffff', 'primary' => '#333333', 'text' => '#222222'],
            ],
        ]);

        $invitation = new Invitation([
            'theme_settings' => ['tokens' => ['primary' => '#ff0000']],
        ]);
        $invitation->setRelation('theme', $theme);

        $resolved = $invitation->resolvedThemeConfig();

        $this->assertSame('#ff0000', $resolved['tokens']['primary']);
        $this->assertSame('#ffffff', $resolved['tokens']['bg'], 'untouched sibling token must survive the merge');
        $this->assertSame('#222222', $resolved['tokens']['text'], 'untouched sibling token must survive the merge');
        $this->assertSame('floral', $resolved['ornament'], 'keys with no override at all must survive the merge');
        $this->assertSame('serif', $resolved['fonts']['head']);
    }

    #[Test]
    public function no_theme_settings_returns_the_base_config_unchanged(): void
    {
        $theme = new Theme(['config' => ['ornament' => 'floral', 'tokens' => ['bg' => '#ffffff']]]);
        $invitation = new Invitation(['theme_settings' => null]);
        $invitation->setRelation('theme', $theme);

        $this->assertSame($theme->config, $invitation->resolvedThemeConfig());
    }
}
