<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Undang Akoe',
            'email' => 'admin@undangakoe.test',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Customer Demo',
            'email' => 'customer@undangakoe.test',
            'password' => bcrypt('password'),
        ]);

        $this->call(ThemeSeeder::class);
        $this->call(PackageSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(DemoInvitationSeeder::class);
    }
}
