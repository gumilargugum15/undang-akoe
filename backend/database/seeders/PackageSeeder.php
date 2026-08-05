<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Gratis',
                'description' => 'Cukup untuk mencoba membuat undangan sederhana tanpa biaya.',
                'price' => 0,
                'duration_days' => 30,
                'max_photos' => 10,
                'max_guests' => 100,
                'features' => ['tema_gratis', 'rsvp', 'buku_tamu'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Premium',
                'description' => 'Untuk yang ingin tampil lebih personal dengan tema premium dan tanpa batas tamu.',
                'price' => 99000,
                'duration_days' => 90,
                'max_photos' => 50,
                'max_guests' => null,
                // Only capabilities that actually exist — no "custom_domain"/"remove_watermark"
                // claims, since neither is a built feature.
                'features' => ['tema_premium', 'rsvp', 'buku_tamu', 'amplop_digital'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Lifetime',
                'description' => 'Akses seumur hidup, semua fitur premium tanpa batas waktu maupun tamu.',
                'price' => 249000,
                'duration_days' => null,
                'max_photos' => null,
                'max_guests' => null,
                'features' => ['tema_premium', 'rsvp', 'buku_tamu', 'amplop_digital'],
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(['name' => $package['name']], $package);
        }
    }
}
