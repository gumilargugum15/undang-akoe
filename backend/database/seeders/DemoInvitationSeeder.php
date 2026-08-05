<?php

namespace Database\Seeders;

use App\Models\Couple;
use App\Models\DigitalEnvelope;
use App\Models\Invitation;
use App\Models\InvitationEvent;
use App\Models\LoveStory;
use App\Models\Music;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A single complete, published invitation ("alya-raka") that the frontend's root
 * route ('/') redirects to as a live showcase — replaces the old hardcoded demo
 * data that used to live in the frontend's lib/invitation-data.ts.
 */
class DemoInvitationSeeder extends Seeder
{
    public function run(): void
    {
        if (Invitation::where('slug', 'alya-raka')->exists()) {
            return;
        }

        $owner = User::where('email', 'customer@undangakoe.test')->firstOrFail();
        $theme = Theme::where('slug', 'elegant')->firstOrFail();

        $invitation = Invitation::create([
            'user_id' => $owner->id,
            'theme_id' => $theme->id,
            'event_category' => 'wedding',
            'title' => 'Alya & Raka',
            'slug' => 'alya-raka',
            'status' => 'published',
            'is_active' => true,
            'published_at' => now(),
        ]);

        Couple::create([
            'invitation_id' => $invitation->id,
            'role' => 'bride',
            'nickname' => 'Alya',
            'full_name' => 'Alya Nurwahida',
            'parent_name' => 'Bapak H. Suryanto & Ibu Hj. Ratna Dewi',
            'instagram_handle' => '@alyanwhd',
            'description' => 'Putri kedua dari Bapak H. Suryanto & Ibu Hj. Ratna Dewi',
            'sort_order' => 0,
        ]);

        Couple::create([
            'invitation_id' => $invitation->id,
            'role' => 'groom',
            'nickname' => 'Raka',
            'full_name' => 'Raka Prasetyo',
            'parent_name' => 'Bapak Ir. Bambang Wijaya & Ibu Sri Lestari',
            'instagram_handle' => '@rakaprst',
            'description' => 'Putra pertama dari Bapak Ir. Bambang Wijaya & Ibu Sri Lestari',
            'sort_order' => 1,
        ]);

        InvitationEvent::create([
            'invitation_id' => $invitation->id,
            'name' => 'Akad Nikah',
            'event_date' => '2026-11-14',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'location_name' => 'Masjid Agung Al-Ikhlas',
            'address' => 'Jl. Diponegoro No. 12, Bandung, Jawa Barat',
            'gmaps_url' => 'https://www.google.com/maps?q=Gedung%20Sate%20Bandung&output=embed',
            'sort_order' => 0,
        ]);

        InvitationEvent::create([
            'invitation_id' => $invitation->id,
            'name' => 'Resepsi',
            'event_date' => '2026-11-14',
            'start_time' => '18:00',
            'end_time' => '21:00',
            'location_name' => 'Grand Padma Ballroom',
            'address' => 'Jl. Asia Afrika No. 88, Bandung, Jawa Barat',
            'gmaps_url' => 'https://www.google.com/maps?q=Gedung%20Sate%20Bandung&output=embed',
            'sort_order' => 1,
        ]);

        LoveStory::create([
            'invitation_id' => $invitation->id,
            'title' => 'Pertama Bertemu',
            'story_date' => '2022-02-14',
            'description' => 'Alya dan Raka pertama kali bertemu di sebuah acara kampus, berawal dari obrolan ringan yang berlanjut menjadi kedekatan yang tulus.',
            'sort_order' => 0,
        ]);

        LoveStory::create([
            'invitation_id' => $invitation->id,
            'title' => 'Menjalin Hubungan',
            'story_date' => '2022-08-20',
            'description' => 'Setelah beberapa bulan saling mengenal lebih dekat, Raka memberanikan diri mengungkapkan perasaannya dan Alya menerimanya dengan bahagia.',
            'sort_order' => 1,
        ]);

        LoveStory::create([
            'invitation_id' => $invitation->id,
            'title' => 'Lamaran',
            'story_date' => '2025-12-10',
            'description' => 'Raka melamar Alya disaksikan oleh kedua keluarga besar, menandai dimulainya perjalanan menuju hari pernikahan.',
            'sort_order' => 2,
        ]);

        Music::create([
            'invitation_id' => $invitation->id,
            'source' => 'upload',
            'title' => 'Nada Bahagia',
            'artist' => 'Undang Akoe',
            'file_path' => 'music/demo/demo-tune.mp3',
            'autoplay' => true,
            'is_loop' => true,
        ]);

        DigitalEnvelope::create([
            'invitation_id' => $invitation->id,
            'type' => 'bank',
            'provider_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Alya Nurwahida',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        DigitalEnvelope::create([
            'invitation_id' => $invitation->id,
            'type' => 'bank',
            'provider_name' => 'Mandiri',
            'account_number' => '9876543210',
            'account_holder' => 'Raka Prasetyo',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        DigitalEnvelope::create([
            'invitation_id' => $invitation->id,
            'type' => 'ewallet',
            'provider_name' => 'GoPay',
            'account_number' => '081234567890',
            'account_holder' => 'Raka Prasetyo',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}
