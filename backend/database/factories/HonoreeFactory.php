<?php

namespace Database\Factories;

use App\Models\Honoree;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Honoree>
 */
class HonoreeFactory extends Factory
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
            'role_label' => fake()->randomElement(['Yang Berulang Tahun', 'Yang Dikhitan']),
            'nickname' => fake()->firstName(),
            'full_name' => fake()->name(),
            'parent_name' => 'Bpk. '.fake()->lastName().' & Ibu '.fake()->lastName(),
            'instagram_handle' => '@'.fake()->userName(),
            'meta' => [],
        ];
    }
}
