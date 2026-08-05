<?php

namespace Database\Factories;

use App\Models\DigitalEnvelope;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalEnvelope>
 */
class DigitalEnvelopeFactory extends Factory
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
            'type' => 'bank',
            'provider_name' => 'BCA',
            'account_number' => fake()->numerify('##########'),
            'account_holder' => fake()->name(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
