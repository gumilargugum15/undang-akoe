<?php

namespace Database\Factories;

use App\Models\Couple;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Couple>
 */
class CoupleFactory extends Factory
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
            'role' => fake()->randomElement(Couple::ROLES),
            'nickname' => fake()->firstName(),
            'full_name' => fake()->name(),
            'parent_name' => 'Bpk. '.fake()->lastName().' & Ibu '.fake()->lastName(),
            'instagram_handle' => '@'.fake()->userName(),
        ];
    }

    public function groom(): static
    {
        return $this->state(fn () => ['role' => 'groom']);
    }

    public function bride(): static
    {
        return $this->state(fn () => ['role' => 'bride']);
    }
}
