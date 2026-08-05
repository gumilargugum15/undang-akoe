<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Package',
            'slug' => fn (array $attrs) => Str::slug($attrs['name']),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement([0, 49000, 99000, 199000]),
            'duration_days' => fake()->randomElement([null, 30, 365]),
            'max_photos' => fake()->randomElement([null, 20, 50]),
            'max_guests' => fake()->randomElement([null, 100, 500]),
            'features' => ['custom_domain' => false, 'remove_watermark' => false],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
