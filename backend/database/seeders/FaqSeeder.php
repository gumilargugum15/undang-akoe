<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Apa itu Undangan Digital?',
                'answer' => 'Undangan Digital adalah layanan pembuatan undangan pernikahan (dan acara lainnya) secara online. Anda bisa membuat, mempercantik dengan tema pilihan, dan membagikan undangan hanya lewat satu tautan.',
                'category' => 'umum',
                'sort_order' => 1,
            ],
            [
                'question' => 'Apakah saya perlu kemampuan desain untuk membuat undangan?',
                'answer' => 'Tidak. Anda cukup memilih salah satu tema yang tersedia, lalu mengisi data acara dan mempelai. Semua tampilan sudah dirancang siap pakai.',
                'category' => 'umum',
                'sort_order' => 2,
            ],
            [
                'question' => 'Apa perbedaan tema gratis dan premium?',
                'answer' => 'Tema gratis dapat langsung digunakan tanpa biaya. Tema premium biasanya menawarkan desain yang lebih eksklusif dan detail tambahan, dengan biaya satu kali sesuai harga tema tersebut.',
                'category' => 'pembayaran',
                'sort_order' => 3,
            ],
            [
                'question' => 'Bisakah saya mengganti tema setelah undangan dibuat?',
                'answer' => 'Bisa. Anda dapat mengganti tema kapan saja dari dashboard tanpa perlu mengisi ulang data mempelai, acara, atau galeri — hanya tampilannya yang berubah.',
                'category' => 'tema',
                'sort_order' => 4,
            ],
            [
                'question' => 'Apakah tamu perlu membuat akun untuk mengisi RSVP?',
                'answer' => 'Tidak. Tamu cukup membuka tautan undangan, lalu mengisi form RSVP dan buku tamu tanpa perlu login atau mendaftar.',
                'category' => 'umum',
                'sort_order' => 5,
            ],
            [
                'question' => 'Apakah ada fitur amplop digital?',
                'answer' => 'Ada. Anda dapat menambahkan rekening bank, e-wallet, atau QRIS di undangan sehingga tamu dapat memberikan hadiah secara digital tanpa perlu hadir langsung.',
                'category' => 'umum',
                'sort_order' => 6,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq);
        }
    }
}
