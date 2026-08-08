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
                'is_free' => true,
                'requires_payment' => false,
                'auto_publish' => true,
                'duration_days' => 7,
                'max_photos' => 3,
                'max_guests' => 5,
                'features' => ['tema_gratis', 'rsvp', 'buku_tamu'],
                'limits' => [
                    'max_active_invitations' => 1,
                    'max_visitors' => 100,
                    'watermark' => true,
                    'music' => false,
                    'video' => false,
                    'qr_gift' => false,
                    'template_scope' => 'free',
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Premium',
                'description' => 'Untuk yang ingin tampil lebih personal dengan tema premium dan tanpa batas tamu.',
                'price' => 99000,
                'is_free' => false,
                'requires_payment' => true,
                'auto_publish' => false,
                'duration_days' => 90,
                'max_photos' => 50,
                'max_guests' => null,
                // Only capabilities that actually exist — no "custom_domain"/"remove_watermark"
                // claims, since neither is a built feature.
                'features' => ['tema_premium', 'rsvp', 'buku_tamu', 'amplop_digital'],
                'limits' => [
                    'max_active_invitations' => 3,
                    'max_visitors' => null,
                    'watermark' => false,
                    'music' => true,
                    'video' => false,
                    'qr_gift' => true,
                    'template_scope' => 'all',
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Lifetime',
                'description' => 'Akses seumur hidup, semua fitur premium tanpa batas waktu maupun tamu.',
                'price' => 249000,
                'is_free' => false,
                'requires_payment' => true,
                'auto_publish' => false,
                'duration_days' => null,
                'max_photos' => null,
                'max_guests' => null,
                'features' => ['tema_premium', 'rsvp', 'buku_tamu', 'amplop_digital'],
                'limits' => [
                    'max_active_invitations' => null,
                    'max_visitors' => null,
                    'watermark' => false,
                    'music' => true,
                    'video' => true,
                    'qr_gift' => true,
                    'template_scope' => 'all',
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(['name' => $package['name']], $package);
        }
    }
}
