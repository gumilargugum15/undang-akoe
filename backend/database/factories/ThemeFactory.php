<?php

namespace Database\Factories;

use App\Models\Theme;
use App\Models\ThemeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Theme>
 */
class ThemeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'theme_category_id' => ThemeCategory::factory(),
            'name' => fake()->unique()->words(2, true).' Theme',
            'description' => fake()->sentence(),
            'version' => '1.0.0',
            'author' => 'Undang Akoe',
            'status' => 'published',
            'type' => 'free',
            'price' => 0,
            'supports_dark_mode' => false,
            'config' => [
                'ornament' => 'floral',
                'reveal' => 'fade',
                'radius' => '0.5rem',
                'cardRadius' => '1rem',
                'shadow' => '0 10px 20px rgba(0,0,0,0.1)',
                'buttonShadow' => 'none',
                'letterSpacing' => '0.02em',
                'headWeight' => '500',
                'fonts' => ['head' => 'serif', 'body' => 'sans-serif', 'script' => 'cursive'],
                'tokens' => [
                    'bg' => '#ffffff', 'bgAlt' => '#f5f5f5', 'surface' => '#ffffff',
                    'primary' => '#333333', 'primaryFg' => '#ffffff', 'secondary' => '#999999',
                    'accent' => '#cccccc', 'text' => '#222222', 'muted' => '#777777', 'border' => '#dddddd',
                ],
                'swatch' => ['#ffffff', '#dddddd', '#999999', '#333333'],
                'texture' => 'none',
            ],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
