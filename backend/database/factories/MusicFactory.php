<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Music;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Music>
 */
class MusicFactory extends Factory
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
            'source' => 'upload',
            'title' => fake()->words(3, true),
            'artist' => fake()->name(),
            'file_path' => 'music/1/'.fake()->uuid().'.mp3',
        ];
    }

    public function spotify(): static
    {
        return $this->state([
            'source' => 'spotify',
            'file_path' => null,
            'external_url' => 'https://open.spotify.com/track/'.fake()->regexify('[A-Za-z0-9]{22}'),
        ]);
    }
}
