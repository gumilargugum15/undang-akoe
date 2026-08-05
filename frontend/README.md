# VowStyle Digital Invitations

Buatkan website undangan digital (untuk pernikahan/acara) menggunakan React + Tailwind CSS dengan fitur UTAMA: sistem tema yang bisa diganti-ganti secara dinamis oleh pengguna.

## KONSEP UTAMA

Website ini adalah undangan digital yang bisa dibagikan via link. Pengguna (pemilik acara) bisa memilih tema visual dari beberapa pilihan yang tersedia, dan seluruh tampilan (warna, font, ornamen, animasi) berubah mengikuti tema yang dipilih — tanpa mengubah struktur konten.

## STRUKTUR HALAMAN

1. **Cover/Landing** - Nama pengantin/tuan rumah, tanggal acara, tombol "Buka Undangan"

2. **Home** - Sambutan singkat, quote/ayat

3. **Mempelai/Profil** - Foto & bio pihak yang terlibat

4. **Acara** - Tanggal, waktu, lokasi (dengan embed Google Maps), countdown timer

5. **Galeri** - Grid foto/gallery

6. **RSVP** - Form konfirmasi kehadiran + jumlah tamu

7. **Ucapan & Doa** - Wall komentar tamu (nama, pesan, timestamp)

8. **Amplop Digital** (opsional) - Info rekening/e-wallet dengan tombol salin

9. **Footer** - Ucapan terima kasih

## SISTEM TEMA DINAMIS

Buat minimal 4 preset tema yang bisa dipilih lewat theme switcher (dropdown/gallery pilihan di awal atau di settings):

1. **Elegant Classic** - Palet gold/cream/maroon, font serif (Playfair Display), ornamen floral klasik

2. **Modern Minimalist** - Palet monokrom/pastel, font sans-serif (Inter/Poppins), garis clean, banyak whitespace

3. **Rustic Garden** - Palet hijau sage/terracotta, font handwritten untuk judul, ornamen daun/bunga liar

4. **Dark Luxury** - Palet hitam/emas/navy, font serif tebal, efek glow/shimmer halus

Setiap tema harus mengubah:

- Color palette (primary, secondary, accent, background)

- Font pairing (heading & body)

- Ornamen/ikon dekoratif (bunga, garis, bingkai)

- Gaya tombol & kartu (rounded vs sharp, shadow, border)

- Animasi transisi (fade, slide, atau reveal saat scroll)

## IMPLEMENTASI TEKNIS

- Gunakan struktur theme object/config (bukan hardcode warna di tiap komponen), misal `themes = { elegant: {...}, minimalist: {...} }`, lalu semua komponen membaca dari context/state tema aktif

- Simpan pilihan tema di state (React Context atau Zustand) agar konsisten di semua halaman

- Sediakan halaman/panel "Pilih Tema" dengan preview kecil tiap tema sebelum pengguna memilih

- Responsive penuh untuk mobile (karena mayoritas dibuka lewat WhatsApp di HP)

- Tambahkan smooth scroll dan animasi ringan saat elemen muncul (misal pakai Framer Motion)

- Musik latar opsional dengan tombol play/pause mengambang

## GAYA VISUAL UMUM

Desain harus terasa premium dan personal, bukan template generik — perhatikan detail typography, spacing, dan micro-interaction di setiap tema agar masing-masing terasa punya karakter berbeda, bukan cuma ganti warna saja.

This project was built with [Lovable](https://lovable.dev).

**Live app**: https://undangakoe.lovable.app

## Build with Lovable

Continue developing this project in the [Lovable editor](https://lovable.dev/projects/ce623c85-0936-4a82-a325-ef347439645a).

- **Ship faster**: describe what you want to build and Lovable handles the code.
- **Stay in sync**: every change made in Lovable is committed straight to this repository.
- **Full ownership**: this code is yours. Push to `main` on GitHub and your changes sync back into Lovable, ready for your next prompt.

## Development

Prefer working locally? You need Node.js and npm — [install with nvm](https://github.com/nvm-sh/nvm#installing-and-updating).

```sh
git clone <this-repository-url>
cd <repository-name>
npm i
npm run dev
```
