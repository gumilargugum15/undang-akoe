<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\LoveStory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoveStory>
 */
class LoveStoryFactory extends Factory
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
            'title' => fake()->randomElement(['Pertama Bertemu', 'Jadian', 'Lamaran']),
            'story_date' => fake()->dateTimeBetween('-3 years', '-1 month')->format('Y-m-d'),
            'description' => fake()->paragraph(),
        ];
    }
}
