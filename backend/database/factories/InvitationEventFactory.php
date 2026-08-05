<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\InvitationEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvitationEvent>
 */
class InvitationEventFactory extends Factory
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
            'name' => fake()->randomElement(['Akad Nikah', 'Resepsi']),
            'event_date' => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'location_name' => fake()->company(),
            'address' => fake()->address(),
        ];
    }
}
