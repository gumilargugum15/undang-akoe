<?php

namespace Database\Seeders;

use App\Models\Theme;
use App\Models\ThemeCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds real, working themes for every category the Theme Engine currently
 * supports templates for. `config` mirrors the frontend's InvitationTheme
 * shape 1:1 (see Phase 2 ERD note #3 on why it's one JSON blob) — pure design
 * tokens, no structural/section info (that's decided per-category on the
 * frontend template registry, not here).
 *
 * Font families are intentionally kept to the set already loaded by the
 * frontend's <head> (Playfair Display, Cormorant Garamond, Plus Jakarta Sans,
 * Inter, Caveat, Marcellus) so new themes never need a new Google Fonts link.
 */
class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'wedding',
                'attrs' => ['name' => 'Wedding', 'icon' => 'heart', 'sort_order' => 1],
                'themes' => $this->weddingThemes(),
            ],
            [
                'slug' => 'birthday',
                'attrs' => ['name' => 'Birthday', 'icon' => 'cake', 'sort_order' => 2],
                'themes' => $this->birthdayThemes(),
            ],
            [
                'slug' => 'khitan',
                'attrs' => ['name' => 'Khitan', 'icon' => 'moon', 'sort_order' => 3],
                'themes' => $this->khitanThemes(),
            ],
            [
                'slug' => 'aqiqah',
                'attrs' => ['name' => 'Aqiqah', 'icon' => 'baby', 'sort_order' => 4],
                'themes' => $this->aqiqahThemes(),
            ],
            [
                'slug' => 'anniversary',
                'attrs' => ['name' => 'Anniversary', 'icon' => 'gem', 'sort_order' => 5],
                'themes' => $this->anniversaryThemes(),
            ],
            [
                'slug' => 'corporate',
                'attrs' => ['name' => 'Corporate', 'icon' => 'briefcase', 'sort_order' => 6],
                'themes' => $this->corporateThemes(),
            ],
            [
                'slug' => 'graduation',
                'attrs' => ['name' => 'Graduation', 'icon' => 'graduation-cap', 'sort_order' => 7],
                'themes' => $this->graduationThemes(),
            ],
        ];

        foreach ($categories as $categoryDef) {
            $category = ThemeCategory::firstOrCreate(['slug' => $categoryDef['slug']], $categoryDef['attrs']);

            foreach ($categoryDef['themes'] as $index => $theme) {
                Theme::updateOrCreate(
                    ['slug' => $theme['slug']],
                    [
                        'theme_category_id' => $category->id,
                        'name' => $theme['name'],
                        'description' => $theme['description'],
                        'version' => '1.0.0',
                        'author' => 'Undang Akoe',
                        'status' => 'published',
                        'type' => 'free',
                        'price' => 0,
                        'supports_dark_mode' => $theme['supports_dark_mode'] ?? false,
                        'config' => $theme['config'],
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function weddingThemes(): array
    {
        return [
            [
                'name' => 'Elegant Classic',
                'slug' => 'elegant',
                'description' => 'Gold, krem & maroon dengan ornamen floral klasik',
                'config' => [
                    'ornament' => 'floral',
                    'reveal' => 'fade',
                    'radius' => '0.5rem',
                    'cardRadius' => '1.75rem 1.75rem 1.75rem 1.75rem',
                    'shadow' => '0 24px 60px -35px rgba(88, 34, 44, 0.45)',
                    'buttonShadow' => '0 14px 30px -14px rgba(123, 45, 59, 0.65)',
                    'letterSpacing' => '0.06em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Playfair Display", serif',
                        'body' => '"Cormorant Garamond", serif',
                        'script' => '"Playfair Display", serif',
                    ],
                    'tokens' => [
                        'bg' => '#fbf6ec', 'bgAlt' => '#f5ead7', 'surface' => '#fffdf8',
                        'primary' => '#7b2d3b', 'primaryFg' => '#fff8ee', 'secondary' => '#b08d57',
                        'accent' => '#c9a227', 'text' => '#3a2429', 'muted' => '#8a6f6a', 'border' => '#e2cfae',
                    ],
                    'swatch' => ['#fbf6ec', '#e2cfae', '#b08d57', '#7b2d3b'],
                    'texture' => 'radial-gradient(circle at 15% 10%, rgba(201,162,39,0.10), transparent 45%), radial-gradient(circle at 85% 80%, rgba(123,45,59,0.08), transparent 50%)',
                ],
            ],
            [
                'name' => 'Modern Minimalist',
                'slug' => 'minimalist',
                'description' => 'Monokrom pastel, garis bersih, banyak ruang kosong',
                'config' => [
                    'ornament' => 'line',
                    'reveal' => 'slide',
                    'radius' => '0.125rem',
                    'cardRadius' => '0.125rem',
                    'shadow' => '0 1px 0 0 rgba(20,20,20,0.08)',
                    'buttonShadow' => 'none',
                    'letterSpacing' => '0.18em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Plus Jakarta Sans", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#fafaf9', 'bgAlt' => '#f1f0ee', 'surface' => '#ffffff',
                        'primary' => '#1c1c1c', 'primaryFg' => '#ffffff', 'secondary' => '#9a9490',
                        'accent' => '#c9b8a8', 'text' => '#1c1c1c', 'muted' => '#7d7873', 'border' => '#e3e1de',
                    ],
                    'swatch' => ['#fafaf9', '#e3e1de', '#c9b8a8', '#1c1c1c'],
                    'texture' => 'none',
                ],
            ],
            [
                'name' => 'Rustic Garden',
                'slug' => 'rustic',
                'description' => 'Sage & terracotta, judul tulisan tangan, dedaunan liar',
                'config' => [
                    'ornament' => 'leaf',
                    'reveal' => 'zoom',
                    'radius' => '1.5rem',
                    'cardRadius' => '2.5rem 0.75rem 2.5rem 0.75rem',
                    'shadow' => '0 26px 50px -30px rgba(60,80,52,0.5)',
                    'buttonShadow' => '0 12px 26px -12px rgba(192,113,75,0.6)',
                    'letterSpacing' => '0.02em',
                    'headWeight' => '400',
                    'fonts' => [
                        'head' => '"Caveat", cursive',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Caveat", cursive',
                    ],
                    'tokens' => [
                        'bg' => '#f6f3e9', 'bgAlt' => '#e9e6d5', 'surface' => '#fffdf6',
                        'primary' => '#4b6043', 'primaryFg' => '#f8f6ec', 'secondary' => '#c0714b',
                        'accent' => '#8fa37f', 'text' => '#33402f', 'muted' => '#77836d', 'border' => '#d7d5bf',
                    ],
                    'swatch' => ['#f6f3e9', '#8fa37f', '#c0714b', '#4b6043'],
                    'texture' => 'radial-gradient(circle at 80% 12%, rgba(143,163,127,0.20), transparent 42%), radial-gradient(circle at 8% 85%, rgba(192,113,75,0.14), transparent 45%)',
                ],
            ],
            [
                'name' => 'Dark Luxury',
                'slug' => 'luxury',
                'description' => 'Hitam, navy & emas dengan kilau shimmer halus',
                'config' => [
                    'ornament' => 'shimmer',
                    'reveal' => 'blur',
                    'radius' => '0.25rem',
                    'cardRadius' => '0.25rem',
                    'shadow' => '0 30px 70px -40px rgba(212,175,55,0.55)',
                    'buttonShadow' => '0 0 28px -6px rgba(212,175,55,0.55)',
                    'letterSpacing' => '0.24em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Marcellus", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Marcellus", serif',
                    ],
                    'tokens' => [
                        'bg' => '#080b13', 'bgAlt' => '#0f1626', 'surface' => '#111a2b',
                        'primary' => '#d4af37', 'primaryFg' => '#0a0d16', 'secondary' => '#1e2c4a',
                        'accent' => '#e7d08a', 'text' => '#f0ece2', 'muted' => '#9aa2b4', 'border' => '#2a3450',
                    ],
                    'swatch' => ['#080b13', '#1e2c4a', '#d4af37', '#e7d08a'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(212,175,55,0.16), transparent 55%), radial-gradient(circle at 10% 90%, rgba(30,44,74,0.6), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
            ...$this->weddingDarkThemes(),
        ];
    }

    /**
     * Dark-elegant wedding variants alongside "Dark Luxury" — each a distinct color story
     * rather than palette swaps of the same idea, split across two methods (+ two batches of
     * `reveal`/transition variety) to keep each one a reasonable size.
     *
     * @return array<int, array<string, mixed>>
     */
    private function weddingDarkThemes(): array
    {
        return [...$this->weddingDarkThemesClassic(), ...$this->weddingDarkThemesExtra()];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function weddingDarkThemesClassic(): array
    {
        return [
            [
                'name' => 'Midnight Emerald',
                'slug' => 'midnight-emerald',
                'description' => 'Hijau zamrud gelap & emas antik, botanical dan mewah',
                'config' => [
                    'ornament' => 'floral',
                    'reveal' => 'fade',
                    'radius' => '0.5rem',
                    'cardRadius' => '1.75rem',
                    'shadow' => '0 30px 70px -40px rgba(205, 165, 69, 0.35)',
                    'buttonShadow' => '0 0 26px -6px rgba(205, 165, 69, 0.5)',
                    'letterSpacing' => '0.05em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Playfair Display", serif',
                        'body' => '"Cormorant Garamond", serif',
                        'script' => '"Playfair Display", serif',
                    ],
                    'tokens' => [
                        'bg' => '#0a1712', 'bgAlt' => '#102019', 'surface' => '#142a21',
                        'primary' => '#cda545', 'primaryFg' => '#0a1712', 'secondary' => '#1f4a38',
                        'accent' => '#e6d3a0', 'text' => '#eef0ea', 'muted' => '#8fa89a', 'border' => '#24473a',
                    ],
                    'swatch' => ['#0a1712', '#1f4a38', '#cda545', '#e6d3a0'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(205,165,69,0.14), transparent 55%), radial-gradient(circle at 10% 90%, rgba(31,74,56,0.5), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
            [
                'name' => 'Onyx Rose',
                'slug' => 'onyx-rose',
                'description' => 'Hitam onyx dengan dusty rose & kilau perak yang romantis',
                'config' => [
                    'ornament' => 'shimmer',
                    'reveal' => 'blur',
                    'radius' => '0.375rem',
                    'cardRadius' => '1rem',
                    'shadow' => '0 30px 70px -40px rgba(201, 139, 160, 0.4)',
                    'buttonShadow' => '0 0 26px -6px rgba(201, 139, 160, 0.55)',
                    'letterSpacing' => '0.1em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Cormorant Garamond", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Cormorant Garamond", serif',
                    ],
                    'tokens' => [
                        'bg' => '#120c10', 'bgAlt' => '#1c1216', 'surface' => '#221720',
                        'primary' => '#c98ba0', 'primaryFg' => '#120c10', 'secondary' => '#3a2430',
                        'accent' => '#e3c3ce', 'text' => '#f3e8ec', 'muted' => '#a0868f', 'border' => '#35242c',
                    ],
                    'swatch' => ['#120c10', '#3a2430', '#c98ba0', '#e3c3ce'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(201,139,160,0.16), transparent 55%), radial-gradient(circle at 90% 90%, rgba(58,36,48,0.6), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
            [
                'name' => 'Velvet Wine',
                'slug' => 'velvet-wine',
                'description' => 'Merah anggur pekat & emas champagne, mewah bak beludru',
                'config' => [
                    'ornament' => 'floral',
                    'reveal' => 'fade',
                    'radius' => '0.5rem',
                    'cardRadius' => '1.75rem',
                    'shadow' => '0 30px 70px -40px rgba(201, 162, 74, 0.35)',
                    'buttonShadow' => '0 0 26px -6px rgba(201, 162, 74, 0.5)',
                    'letterSpacing' => '0.08em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Marcellus", serif',
                        'body' => '"Cormorant Garamond", serif',
                        'script' => '"Playfair Display", serif',
                    ],
                    'tokens' => [
                        'bg' => '#140a0d', 'bgAlt' => '#1f1014', 'surface' => '#251218',
                        'primary' => '#c9a24a', 'primaryFg' => '#140a0d', 'secondary' => '#4a1620',
                        'accent' => '#e0c68a', 'text' => '#f2e9e6', 'muted' => '#a3888c', 'border' => '#3a1a20',
                    ],
                    'swatch' => ['#140a0d', '#4a1620', '#c9a24a', '#e0c68a'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(201,162,74,0.15), transparent 55%), radial-gradient(circle at 15% 90%, rgba(74,22,32,0.55), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
            [
                'name' => 'Starlit Sapphire',
                'slug' => 'starlit-sapphire',
                'description' => 'Navy gelap & perak platinum, elegan bernuansa langit malam',
                'config' => [
                    'ornament' => 'shimmer',
                    'reveal' => 'blur',
                    'radius' => '0.25rem',
                    'cardRadius' => '0.25rem',
                    'shadow' => '0 30px 70px -40px rgba(170, 184, 217, 0.4)',
                    'buttonShadow' => '0 0 28px -6px rgba(215, 220, 230, 0.5)',
                    'letterSpacing' => '0.22em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Marcellus", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Marcellus", serif',
                    ],
                    'tokens' => [
                        'bg' => '#070c18', 'bgAlt' => '#0c1526', 'surface' => '#101c33',
                        'primary' => '#d7dce6', 'primaryFg' => '#070c18', 'secondary' => '#2a3b63',
                        'accent' => '#aab8d9', 'text' => '#eef1f7', 'muted' => '#8b96ad', 'border' => '#263756',
                    ],
                    'swatch' => ['#070c18', '#2a3b63', '#d7dce6', '#aab8d9'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(215,220,230,0.12), transparent 55%), radial-gradient(circle at 85% 85%, rgba(42,59,99,0.6), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
        ];
    }

    /**
     * Second batch of dark wedding variants — also introduces three new `reveal` transition
     * kinds (flip, curtain, bounce) on top of the original fade/slide/zoom/blur, so the
     * catalog's motion feels as varied as its palettes (see reveal.tsx/cover.tsx).
     *
     * @return array<int, array<string, mixed>>
     */
    private function weddingDarkThemesExtra(): array
    {
        return [
            [
                'name' => 'Obsidian Plum',
                'slug' => 'obsidian-plum',
                'description' => 'Ungu plum gelap & rose gold, romantis dan misterius',
                'config' => [
                    'ornament' => 'leaf',
                    'reveal' => 'curtain',
                    'radius' => '1.25rem',
                    'cardRadius' => '2rem',
                    'shadow' => '0 30px 70px -40px rgba(202, 160, 107, 0.35)',
                    'buttonShadow' => '0 0 26px -6px rgba(202, 160, 107, 0.5)',
                    'letterSpacing' => '0.06em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Playfair Display", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Playfair Display", serif',
                    ],
                    'tokens' => [
                        'bg' => '#150c17', 'bgAlt' => '#1f1220', 'surface' => '#251629',
                        'primary' => '#caa06b', 'primaryFg' => '#150c17', 'secondary' => '#3d2142',
                        'accent' => '#e0c9a8', 'text' => '#f1e8f0', 'muted' => '#a189a8', 'border' => '#3a2440',
                    ],
                    'swatch' => ['#150c17', '#3d2142', '#caa06b', '#e0c9a8'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(202,160,107,0.14), transparent 55%), radial-gradient(circle at 12% 90%, rgba(61,33,66,0.6), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
            [
                'name' => 'Noir Blanc',
                'slug' => 'noir-blanc',
                'description' => 'Hitam pekat & putih mutiara, kontras tegas nan mewah',
                'config' => [
                    'ornament' => 'line',
                    'reveal' => 'flip',
                    'radius' => '0.125rem',
                    'cardRadius' => '0.125rem',
                    'shadow' => '0 1px 0 0 rgba(245,243,238,0.12)',
                    'buttonShadow' => 'none',
                    'letterSpacing' => '0.2em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Plus Jakarta Sans", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#0a0a0a', 'bgAlt' => '#141414', 'surface' => '#1a1a1a',
                        'primary' => '#f2f0ec', 'primaryFg' => '#0a0a0a', 'secondary' => '#3d3d3d',
                        'accent' => '#d9d4c9', 'text' => '#f5f3ee', 'muted' => '#9c9992', 'border' => '#2c2c2c',
                    ],
                    'swatch' => ['#0a0a0a', '#3d3d3d', '#f2f0ec', '#d9d4c9'],
                    'texture' => 'none',
                ],
                'supports_dark_mode' => true,
            ],
            [
                'name' => 'Copper Nights',
                'slug' => 'copper-nights',
                'description' => 'Charcoal gelap & tembaga metalik, hangat dan industrial-elegan',
                'config' => [
                    'ornament' => 'shimmer',
                    'reveal' => 'bounce',
                    'radius' => '0.375rem',
                    'cardRadius' => '0.5rem',
                    'shadow' => '0 30px 70px -40px rgba(201, 123, 69, 0.4)',
                    'buttonShadow' => '0 0 26px -6px rgba(201, 123, 69, 0.55)',
                    'letterSpacing' => '0.15em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Marcellus", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Marcellus", serif',
                    ],
                    'tokens' => [
                        'bg' => '#140f0c', 'bgAlt' => '#1d1611', 'surface' => '#241b15',
                        'primary' => '#c97b45', 'primaryFg' => '#140f0c', 'secondary' => '#3a2a1e',
                        'accent' => '#e0a578', 'text' => '#f2ece4', 'muted' => '#a3948a', 'border' => '#3d2c20',
                    ],
                    'swatch' => ['#140f0c', '#3a2a1e', '#c97b45', '#e0a578'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(201,123,69,0.16), transparent 55%), radial-gradient(circle at 90% 90%, rgba(58,42,30,0.6), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
            [
                'name' => 'Amethyst Dusk',
                'slug' => 'amethyst-dusk',
                'description' => 'Ungu ametis senja & perak lembut, misterius dan mempesona',
                'config' => [
                    'ornament' => 'floral',
                    'reveal' => 'blur',
                    'radius' => '0.5rem',
                    'cardRadius' => '1.75rem',
                    'shadow' => '0 30px 70px -40px rgba(167, 139, 201, 0.35)',
                    'buttonShadow' => '0 0 26px -6px rgba(167, 139, 201, 0.5)',
                    'letterSpacing' => '0.06em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Playfair Display", serif',
                        'body' => '"Cormorant Garamond", serif',
                        'script' => '"Playfair Display", serif',
                    ],
                    'tokens' => [
                        'bg' => '#100b1a', 'bgAlt' => '#171025', 'surface' => '#1d1530',
                        'primary' => '#a78bc9', 'primaryFg' => '#100b1a', 'secondary' => '#2e2350',
                        'accent' => '#cdb8e8', 'text' => '#ece8f5', 'muted' => '#9990ad', 'border' => '#332a52',
                    ],
                    'swatch' => ['#100b1a', '#2e2350', '#a78bc9', '#cdb8e8'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(167,139,201,0.14), transparent 55%), radial-gradient(circle at 15% 90%, rgba(46,35,80,0.55), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function birthdayThemes(): array
    {
        return [
            [
                // Fixes the pre-existing placeholder row (dummy #f00/#0f0/#00f tokens,
                // is_active false) in place rather than leaving it broken or duplicating it.
                'name' => 'Confetti Pop',
                'slug' => 'confetti-pop',
                'description' => 'Warna-warni ceria, confetti melayang & balon pesta',
                'config' => [
                    'ornament' => 'confetti',
                    'reveal' => 'zoom',
                    'radius' => '1.5rem',
                    'cardRadius' => '1.5rem',
                    'shadow' => '0 20px 45px -25px rgba(255, 92, 138, 0.45)',
                    'buttonShadow' => '0 12px 26px -10px rgba(255, 92, 138, 0.55)',
                    'letterSpacing' => '0.04em',
                    'headWeight' => '700',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#fff9f0', 'bgAlt' => '#ffedd5', 'surface' => '#ffffff',
                        'primary' => '#ff5c8a', 'primaryFg' => '#ffffff', 'secondary' => '#06b6d4',
                        'accent' => '#ffd60a', 'text' => '#2b2130', 'muted' => '#8a7a8f', 'border' => '#ffd9e6',
                    ],
                    'swatch' => ['#fff9f0', '#ffd9e6', '#06b6d4', '#ff5c8a'],
                    'texture' => 'radial-gradient(circle at 12% 15%, rgba(255,214,10,0.18), transparent 40%), radial-gradient(circle at 88% 80%, rgba(6,182,212,0.16), transparent 45%)',
                ],
            ],
            [
                'name' => 'Pastel Balloon Party',
                'slug' => 'pastel-balloon',
                'description' => 'Lavender, peach & mint lembut dengan balon melayang',
                'config' => [
                    'ornament' => 'balloon',
                    'reveal' => 'slide',
                    'radius' => '1.25rem',
                    'cardRadius' => '2rem',
                    'shadow' => '0 22px 50px -30px rgba(124, 108, 240, 0.35)',
                    'buttonShadow' => '0 10px 22px -10px rgba(124, 108, 240, 0.45)',
                    'letterSpacing' => '0.05em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#fdfbff', 'bgAlt' => '#f2eefd', 'surface' => '#ffffff',
                        'primary' => '#7c6cf0', 'primaryFg' => '#ffffff', 'secondary' => '#ffb4a2',
                        'accent' => '#6ee7c8', 'text' => '#2a2440', 'muted' => '#8b84a3', 'border' => '#e6e0fb',
                    ],
                    'swatch' => ['#fdfbff', '#e6e0fb', '#ffb4a2', '#7c6cf0'],
                    'texture' => 'radial-gradient(circle at 85% 10%, rgba(110,231,200,0.18), transparent 42%), radial-gradient(circle at 10% 90%, rgba(255,180,162,0.18), transparent 45%)',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function khitanThemes(): array
    {
        return [
            [
                'name' => 'Zamrud Emas',
                'slug' => 'zamrud-emas',
                'description' => 'Hijau zamrud & emas dengan ornamen geometris islami',
                'config' => [
                    'ornament' => 'geometric',
                    'reveal' => 'fade',
                    'radius' => '0.75rem',
                    'cardRadius' => '1rem 2rem 1rem 2rem',
                    'shadow' => '0 24px 55px -32px rgba(15, 81, 50, 0.45)',
                    'buttonShadow' => '0 14px 28px -12px rgba(176, 141, 87, 0.5)',
                    'letterSpacing' => '0.06em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Marcellus", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Playfair Display", serif',
                    ],
                    'tokens' => [
                        'bg' => '#f7faf7', 'bgAlt' => '#eaf3ea', 'surface' => '#ffffff',
                        'primary' => '#0f5132', 'primaryFg' => '#fdf6e3', 'secondary' => '#b08d57',
                        'accent' => '#d4af37', 'text' => '#1c2b22', 'muted' => '#6b7d70', 'border' => '#cfe0d0',
                    ],
                    'swatch' => ['#f7faf7', '#cfe0d0', '#b08d57', '#0f5132'],
                    'texture' => 'radial-gradient(circle at 15% 12%, rgba(212,175,55,0.14), transparent 42%), radial-gradient(circle at 85% 85%, rgba(15,81,50,0.10), transparent 48%)',
                ],
            ],
            [
                'name' => 'Nur Minimalis',
                'slug' => 'nur-minimalis',
                'description' => 'Putih & hijau sage minimalis dengan sentuhan emas halus',
                'config' => [
                    'ornament' => 'crescent',
                    'reveal' => 'slide',
                    'radius' => '0.25rem',
                    'cardRadius' => '0.25rem',
                    'shadow' => '0 1px 0 0 rgba(20,40,30,0.08)',
                    'buttonShadow' => 'none',
                    'letterSpacing' => '0.16em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#fbfdfb', 'bgAlt' => '#f1f6f2', 'surface' => '#ffffff',
                        'primary' => '#14532d', 'primaryFg' => '#ffffff', 'secondary' => '#94a3a1',
                        'accent' => '#c9a227', 'text' => '#16231c', 'muted' => '#6e7d73', 'border' => '#dfe7e1',
                    ],
                    'swatch' => ['#fbfdfb', '#dfe7e1', '#94a3a1', '#14532d'],
                    'texture' => 'none',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function aqiqahThemes(): array
    {
        return [
            [
                'name' => 'Kapas Lembut',
                'slug' => 'kapas-lembut',
                'description' => 'Biru & krem lembut dengan sentuhan awan dan bintang',
                'config' => [
                    'ornament' => 'cloud',
                    'reveal' => 'fade',
                    'radius' => '1.25rem',
                    'cardRadius' => '2rem',
                    'shadow' => '0 22px 48px -30px rgba(91, 133, 173, 0.4)',
                    'buttonShadow' => '0 10px 22px -10px rgba(91, 133, 173, 0.45)',
                    'letterSpacing' => '0.04em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#f6fafd', 'bgAlt' => '#e9f2fa', 'surface' => '#ffffff',
                        'primary' => '#5b85ad', 'primaryFg' => '#ffffff', 'secondary' => '#f4c9c9',
                        'accent' => '#f7d774', 'text' => '#22303d', 'muted' => '#7d8ea0', 'border' => '#dbe8f4',
                    ],
                    'swatch' => ['#f6fafd', '#dbe8f4', '#f4c9c9', '#5b85ad'],
                    'texture' => 'radial-gradient(circle at 85% 10%, rgba(247,215,116,0.16), transparent 42%), radial-gradient(circle at 10% 90%, rgba(244,201,201,0.16), transparent 45%)',
                ],
            ],
            [
                'name' => 'Bunga Melati',
                'slug' => 'bunga-melati',
                'description' => 'Putih & emas bersih dengan ornamen bunga melati',
                'config' => [
                    'ornament' => 'floral',
                    'reveal' => 'zoom',
                    'radius' => '1rem',
                    'cardRadius' => '1.5rem',
                    'shadow' => '0 22px 48px -30px rgba(201, 162, 39, 0.3)',
                    'buttonShadow' => '0 10px 22px -10px rgba(201, 162, 39, 0.4)',
                    'letterSpacing' => '0.05em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Playfair Display", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Playfair Display", serif',
                    ],
                    'tokens' => [
                        'bg' => '#fffdf9', 'bgAlt' => '#faf3e3', 'surface' => '#ffffff',
                        'primary' => '#8a9a5b', 'primaryFg' => '#ffffff', 'secondary' => '#c9a227',
                        'accent' => '#f4e2b8', 'text' => '#2c2b21', 'muted' => '#83806f', 'border' => '#ecdfc0',
                    ],
                    'swatch' => ['#fffdf9', '#ecdfc0', '#c9a227', '#8a9a5b'],
                    'texture' => 'radial-gradient(circle at 12% 12%, rgba(138,154,91,0.12), transparent 42%), radial-gradient(circle at 88% 85%, rgba(201,162,39,0.14), transparent 48%)',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function anniversaryThemes(): array
    {
        return [
            [
                'name' => 'Emas Pernikahan',
                'slug' => 'emas-pernikahan',
                'description' => 'Burgundy & emas hangat merayakan tahun-tahun kebersamaan',
                'config' => [
                    'ornament' => 'floral',
                    'reveal' => 'fade',
                    'radius' => '0.5rem',
                    'cardRadius' => '1.5rem',
                    'shadow' => '0 24px 55px -32px rgba(122, 32, 46, 0.4)',
                    'buttonShadow' => '0 14px 28px -12px rgba(201, 162, 39, 0.5)',
                    'letterSpacing' => '0.05em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Playfair Display", serif',
                        'body' => '"Cormorant Garamond", serif',
                        'script' => '"Playfair Display", serif',
                    ],
                    'tokens' => [
                        'bg' => '#fdf7f2', 'bgAlt' => '#f6e9df', 'surface' => '#ffffff',
                        'primary' => '#7a202e', 'primaryFg' => '#fff8ee', 'secondary' => '#c9a227',
                        'accent' => '#e3c774', 'text' => '#3a2124', 'muted' => '#8a6f6a', 'border' => '#ecd8c2',
                    ],
                    'swatch' => ['#fdf7f2', '#ecd8c2', '#c9a227', '#7a202e'],
                    'texture' => 'radial-gradient(circle at 15% 10%, rgba(201,162,39,0.12), transparent 45%), radial-gradient(circle at 85% 80%, rgba(122,32,46,0.10), transparent 50%)',
                ],
            ],
            [
                'name' => 'Momen Abadi',
                'slug' => 'momen-abadi',
                'description' => 'Rose gold & blush modern untuk perayaan cinta masa kini',
                'config' => [
                    'ornament' => 'shimmer',
                    'reveal' => 'slide',
                    'radius' => '1rem',
                    'cardRadius' => '1.25rem',
                    'shadow' => '0 22px 48px -30px rgba(183, 110, 121, 0.35)',
                    'buttonShadow' => '0 12px 24px -10px rgba(183, 110, 121, 0.45)',
                    'letterSpacing' => '0.08em',
                    'headWeight' => '500',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#fdf6f6', 'bgAlt' => '#f7e8e9', 'surface' => '#ffffff',
                        'primary' => '#b76e79', 'primaryFg' => '#ffffff', 'secondary' => '#d4af8c',
                        'accent' => '#e9c6b8', 'text' => '#3a2a2c', 'muted' => '#8a7574', 'border' => '#f0dad9',
                    ],
                    'swatch' => ['#fdf6f6', '#f0dad9', '#d4af8c', '#b76e79'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(212,175,140,0.14), transparent 55%), radial-gradient(circle at 10% 90%, rgba(183,110,121,0.10), transparent 50%)',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function corporateThemes(): array
    {
        return [
            [
                'name' => 'Korporat Modern',
                'slug' => 'korporat-modern',
                'description' => 'Navy & putih bersih, profesional dan modern',
                'config' => [
                    'ornament' => 'line',
                    'reveal' => 'slide',
                    'radius' => '0.375rem',
                    'cardRadius' => '0.5rem',
                    'shadow' => '0 12px 30px -20px rgba(15, 30, 60, 0.35)',
                    'buttonShadow' => '0 10px 20px -10px rgba(15, 30, 60, 0.4)',
                    'letterSpacing' => '0.14em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#f7f9fc', 'bgAlt' => '#eaeff6', 'surface' => '#ffffff',
                        'primary' => '#0f1e3c', 'primaryFg' => '#ffffff', 'secondary' => '#3a5a8c',
                        'accent' => '#c9a227', 'text' => '#101827', 'muted' => '#6b7686', 'border' => '#dde4ee',
                    ],
                    'swatch' => ['#f7f9fc', '#dde4ee', '#3a5a8c', '#0f1e3c'],
                    'texture' => 'none',
                ],
            ],
            [
                'name' => 'Executive Slate',
                'slug' => 'executive-slate',
                'description' => 'Charcoal & aksen emas untuk acara perusahaan kelas atas',
                'config' => [
                    'ornament' => 'shimmer',
                    'reveal' => 'blur',
                    'radius' => '0.25rem',
                    'cardRadius' => '0.25rem',
                    'shadow' => '0 26px 60px -35px rgba(20, 22, 26, 0.55)',
                    'buttonShadow' => '0 0 24px -6px rgba(201, 162, 39, 0.5)',
                    'letterSpacing' => '0.2em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Marcellus", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Marcellus", serif',
                    ],
                    'tokens' => [
                        'bg' => '#14161a', 'bgAlt' => '#1c1f26', 'surface' => '#20242c',
                        'primary' => '#c9a227', 'primaryFg' => '#14161a', 'secondary' => '#3a3f4a',
                        'accent' => '#e3c774', 'text' => '#eceef0', 'muted' => '#9aa0aa', 'border' => '#2e323b',
                    ],
                    'swatch' => ['#14161a', '#2e323b', '#3a3f4a', '#c9a227'],
                    'texture' => 'radial-gradient(circle at 50% -10%, rgba(201,162,39,0.14), transparent 55%)',
                ],
                'supports_dark_mode' => true,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function graduationThemes(): array
    {
        return [
            [
                'name' => 'Wisuda Prestasi',
                'slug' => 'wisuda-prestasi',
                'description' => 'Navy & emas klasik terinspirasi toga wisuda',
                'config' => [
                    'ornament' => 'line',
                    'reveal' => 'fade',
                    'radius' => '0.375rem',
                    'cardRadius' => '0.75rem',
                    'shadow' => '0 22px 50px -30px rgba(20, 35, 70, 0.4)',
                    'buttonShadow' => '0 12px 24px -10px rgba(201, 162, 39, 0.45)',
                    'letterSpacing' => '0.1em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Marcellus", serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Marcellus", serif',
                    ],
                    'tokens' => [
                        'bg' => '#f7f8fb', 'bgAlt' => '#e9ecf3', 'surface' => '#ffffff',
                        'primary' => '#14235a', 'primaryFg' => '#ffffff', 'secondary' => '#c9a227',
                        'accent' => '#e3c774', 'text' => '#151a2c', 'muted' => '#6d7286', 'border' => '#dde1ec',
                    ],
                    'swatch' => ['#f7f8fb', '#dde1ec', '#c9a227', '#14235a'],
                    'texture' => 'radial-gradient(circle at 85% 10%, rgba(201,162,39,0.12), transparent 42%)',
                ],
            ],
            [
                'name' => 'Cerah Masa Depan',
                'slug' => 'cerah-masa-depan',
                'description' => 'Teal & coral modern, ceria menyambut masa depan',
                'config' => [
                    'ornament' => 'confetti',
                    'reveal' => 'zoom',
                    'radius' => '1rem',
                    'cardRadius' => '1.25rem',
                    'shadow' => '0 22px 48px -30px rgba(13, 122, 121, 0.35)',
                    'buttonShadow' => '0 12px 24px -10px rgba(255, 111, 97, 0.45)',
                    'letterSpacing' => '0.04em',
                    'headWeight' => '600',
                    'fonts' => [
                        'head' => '"Plus Jakarta Sans", sans-serif',
                        'body' => '"Inter", sans-serif',
                        'script' => '"Plus Jakarta Sans", sans-serif',
                    ],
                    'tokens' => [
                        'bg' => '#f5fcfb', 'bgAlt' => '#e3f5f3', 'surface' => '#ffffff',
                        'primary' => '#0d7a79', 'primaryFg' => '#ffffff', 'secondary' => '#ff6f61',
                        'accent' => '#ffd166', 'text' => '#122b2a', 'muted' => '#6a8382', 'border' => '#d3ede9',
                    ],
                    'swatch' => ['#f5fcfb', '#d3ede9', '#ff6f61', '#0d7a79'],
                    'texture' => 'radial-gradient(circle at 12% 15%, rgba(255,209,102,0.14), transparent 40%), radial-gradient(circle at 88% 85%, rgba(13,122,121,0.12), transparent 45%)',
                ],
            ],
        ];
    }
}
