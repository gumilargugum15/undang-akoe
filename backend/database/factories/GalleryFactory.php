<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'type' => 'photo',
            'file_path' => 'gallery/photos/1/'.fake()->uuid().'.jpg',
            'caption' => fake()->sentence(3),
            'category' => fake()->randomElement(['prewedding', 'venue', null]),
        ];
    }
}
