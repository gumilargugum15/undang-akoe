# PROMPT CLAUDE AI

Anda adalah seorang **Senior Software Architect**, **Senior Laravel Developer**, **Senior MySQL Database Designer**, dan **Senior Frontend Developer** yang berpengalaman membangun aplikasi production-ready.

## Latar Belakang

Saya sudah memiliki project frontend hasil export dari **Lovable** (React + Vite). Tugas Anda adalah membantu saya membangun aplikasi **Undangan Digital Berbasis Web** dengan:

* Frontend : Project Lovable yang sudah tersedia (jangan membuat ulang desain)
* Backend : Laravel 12
* Database : MySQL 8
* Authentication : Laravel Sanctum
* API : REST API
* Storage : Laravel Storage
* Upload File : Image
* Web Server : Apache / Nginx
* Bahasa : Indonesia

Aplikasi harus dibuat dengan struktur kode yang rapi, scalable, clean architecture, dan mudah dikembangkan.

---

# Tujuan Aplikasi

Membuat aplikasi undangan digital yang memungkinkan pengguna membuat website undangan sendiri hanya dengan mengisi data.

Setiap undangan memiliki URL sendiri.

Contoh:

```
https://domain.com/andi-sari
```

atau

```
https://domain.com/invitation/andi-sari
```

---

# Role User

## Admin

Memiliki akses penuh

Menu:

* Dashboard
* Kelola User
* Kelola Paket
* Kelola Template
* Kelola Undangan
* Kelola Pembayaran
* Kelola Testimoni
* Kelola Banner
* Kelola FAQ
* Kelola Setting Website
* Kelola Domain
* Kelola Media
* Laporan

---

## Customer

Menu:

Dashboard

Kelola Undangan

Tema

Galeri

Cerita

Acara

Mempelai

Ucapan

Amplop Digital

Musik

QR Code

Pengaturan

Preview

Publish

Statistik Pengunjung

---

# Fitur Utama

## 1. Authentication

Login

Register

Forgot Password

Reset Password

Email Verification

Remember Login

Logout

Profile

Ganti Password

---

## 2. Dashboard

Menampilkan

Jumlah Undangan

Jumlah Pengunjung

Jumlah Ucapan

Jumlah Kehadiran

Jumlah Amplop Masuk

Grafik Pengunjung

---

## 3. Kelola Undangan

CRUD

Judul Undangan

Slug URL

Status Draft

Status Publish

Tanggal Publish

Template

Kategori

Aktif / Nonaktif

---

## 4. Data Mempelai

Mempelai Pria

Nama

Nama Lengkap

Nama Orang Tua

Instagram

Foto

Deskripsi

Mempelai Wanita

Nama

Nama Lengkap

Nama Orang Tua

Instagram

Foto

Deskripsi

---

## 5. Cerita

Love Story

Timeline

Tanggal

Judul

Deskripsi

Foto

Urutan

---

## 6. Acara

Akad

Resepsi

Ngunduh Mantu

Acara Tambahan

Field

Nama Acara

Tanggal

Jam

Lokasi

Google Maps

Latitude

Longitude

Catatan

---

## 7. Countdown

Hitung mundur otomatis

---

## 8. Galeri

Upload banyak foto

Kategori

Video Youtube

Video MP4

Sorting

---

## 9. Musik

Upload MP3

atau

Spotify

YouTube Music

Auto Play

Loop

---

## 10. RSVP

Nama

Jumlah Tamu

Konfirmasi Hadir

Pesan

Tanggal

---

## 11. Buku Tamu

Nama

Ucapan

Status Hadir

Moderasi Admin

---

## 12. Amplop Digital

Bank

Nomor Rekening

Atas Nama

QRIS

Dana

OVO

GoPay

ShopeePay

---

## 13. QR Code

Generate QR

Scan Kehadiran

---

## 14. Statistik

Jumlah View

Unique Visitor

Device

Browser

Lokasi

Referrer

---

## 15. Tema

Customer dapat memilih template.

Admin dapat menambah template baru.

---

## 16. SEO

Meta Title

Meta Description

Open Graph

Favicon

Google Analytics

Facebook Pixel

---

## 17. Share

WhatsApp

Facebook

Instagram

Telegram

Twitter/X

Copy Link

---

## 18. Domain Custom

Subdomain

Domain Sendiri

---

# Database

Gunakan MySQL.

Buat desain database yang sudah dinormalisasi.

Gunakan Foreign Key.

Gunakan Index.

Gunakan Soft Delete.

Gunakan Timestamp.

Gunakan UUID bila diperlukan.

---

# Backend Laravel

Gunakan:

Laravel 12

Repository Pattern

Service Pattern

Form Request Validation

API Resource

Policy

Middleware

Sanctum

Queue

Jobs

Notification

Mail

Storage

Migration

Seeder

Factory

Observer

Event

Exception Handler

Logging

Caching

Pagination

Rate Limit

---

# Frontend

Frontend berasal dari project Lovable.

Jangan mengubah desain.

Gunakan API Laravel.

Jika ada data dummy, ubah menjadi API.

Jika ada hardcode, ubah menjadi dynamic.

Pastikan semua halaman Lovable terhubung dengan backend.

---

# REST API

Buat endpoint lengkap.

Contoh:

POST /login

POST /register

GET /profile

PUT /profile

GET /invitations

POST /invitations

PUT /invitations/{id}

DELETE /invitations/{id}

GET /guestbook

POST /guestbook

GET /gallery

POST /gallery

dan endpoint lain yang diperlukan.

---

# Struktur Folder Laravel

Gunakan struktur yang rapi.

Contoh

```
app/

    Http/

    Models/

    Repositories/

    Services/

    Interfaces/

    Helpers/

    Actions/

    DTO/

    Events/

    Listeners/

    Jobs/

    Notifications/

    Policies/

    Traits/

database/

routes/

resources/

storage/
```

---

# Struktur Database

Claude harus membuat:

* ERD
* Flow Database
* Migration
* Seeder
* Factory
* Relasi
* Foreign Key

---

# Validasi

Gunakan Form Request.

Semua input harus divalidasi.

Sanitasi input.

Cegah SQL Injection.

Cegah XSS.

Cegah CSRF.

---

# Upload

Upload Foto

Upload Musik

Upload Video

Resize Image

Compress Image

Validasi ukuran file

---

# Dokumentasi

Untuk setiap fitur yang dibuat, sertakan:

1. Penjelasan fitur
2. Flow sistem
3. Struktur database
4. Migration
5. Model
6. Controller
7. Service
8. Repository
9. Validation
10. API Endpoint
11. Response JSON
12. Contoh Request
13. Unit Test
14. Feature Test

---

# Tahapan Pengerjaan

Jangan langsung membuat semua kode sekaligus.

Kerjakan secara bertahap.

Tahapan yang diinginkan:

Phase 1

* Analisis Project
* Analisis Frontend Lovable
* Menentukan struktur backend
* Menentukan struktur database

Phase 2

* Desain Database
* ERD
* Migration

Phase 3

* Authentication
* Sanctum

Phase 4

* CRUD Undangan

Phase 5

* CRUD Mempelai

Phase 6

* CRUD Acara

Phase 7

* CRUD Galeri

Phase 8

* RSVP

Phase 9

* Buku Tamu

Phase 10

* Amplop Digital

Phase 11

* Statistik

Phase 12

* Dashboard

Phase 13

* Integrasi Frontend Lovable

Phase 14

* Optimasi

Phase 15

* Testing

Phase 16

* Deployment

---

## 17. Tema / Desain Dinamis

Aplikasi harus mendukung sistem **Multi Theme** sehingga tampilan undangan dapat diganti kapan saja tanpa mengubah data undangan.

### Ketentuan

* Setiap tema memiliki layout, warna, font, animasi, dan komponen yang berbeda.
* Customer dapat mengganti tema kapan saja melalui Dashboard.
* Seluruh data undangan tetap sama, hanya tampilan yang berubah.
* Admin dapat menambah, mengubah, mengaktifkan, menonaktifkan, dan menghapus tema.
* Tema baru harus dapat ditambahkan tanpa perlu mengubah kode utama aplikasi (plug-and-play sejauh memungkinkan).
* Setiap tema memiliki preview (thumbnail) sebelum dipilih.
* Tema memiliki kategori, misalnya:

  * Wedding
  * Birthday
  * Khitan
  * Aqiqah
  * Anniversary
  * Corporate
  * Graduation
  * Custom
* Tema dapat memiliki status:

  * Draft
  * Published
  * Premium
  * Free
* Mendukung pengaturan warna utama (Primary Color), warna sekunder (Secondary Color), font, ikon, dan animasi.
* Mendukung mode Light dan Dark apabila tema menyediakannya.
* Setiap tema dapat memiliki konfigurasi (Theme Settings) sendiri yang disimpan di database.

### Struktur Data Tema

Minimal informasi tema meliputi:

* Nama Tema
* Slug
* Deskripsi
* Thumbnail
* Banner Preview
* Screenshot
* Versi
* Penulis
* Status
* Kategori
* Tipe (Free/Premium)
* Warna Default
* Font Default
* Animasi Default

### Integrasi Frontend Lovable

Frontend berasal dari project Lovable yang sudah tersedia.

Claude harus merancang arsitektur agar:

* Frontend dapat memuat tema secara dinamis.
* Setiap tema menggunakan komponen yang dapat digunakan kembali (reusable).
* Seluruh data berasal dari API Laravel.
* Tidak ada data yang di-hardcode pada frontend.
* Perpindahan tema tidak memerlukan perubahan backend maupun migrasi data.

### Theme Engine

Buat sistem Theme Engine yang mampu:

* Memuat tema berdasarkan konfigurasi database.
* Mengubah layout sesuai tema yang dipilih.
* Menggunakan konfigurasi JSON untuk warna, font, ikon, dan komponen.
* Mendukung penambahan tema baru tanpa mengubah struktur aplikasi utama.
* Menggunakan pola Factory atau Strategy Pattern untuk pemilihan tema agar mudah dikembangkan.

### Dashboard Customer

Tambahkan menu:

* Pilih Tema
* Preview Tema
* Ganti Tema
* Kustomisasi Warna
* Kustomisasi Font
* Kustomisasi Musik
* Simpan Pengaturan Tema

### Dashboard Admin

Tambahkan menu:

* Kelola Tema
* Tambah Tema
* Edit Tema
* Upload Thumbnail
* Upload Asset Tema
* Publish / Unpublish Tema
* Duplikasi Tema
* Preview Tema
* Pengaturan Tema

### API

Sediakan endpoint REST API untuk pengelolaan tema, seperti:

* GET /themes
* GET /themes/{id}
* POST /themes
* PUT /themes/{id}
* DELETE /themes/{id}
* GET /themes/preview/{slug}
* POST /invitations/{id}/change-theme

### Tujuan Arsitektur

Rancang sistem agar di masa depan dapat memiliki puluhan hingga ratusan tema tanpa perlu melakukan perubahan besar pada kode aplikasi. Prioritaskan arsitektur yang modular, reusable, scalable, dan mudah dipelihara.


# Aturan

* Jangan mengubah desain Lovable.
* Fokus menghubungkan frontend dengan backend Laravel.
* Gunakan best practice Laravel.
* Semua kode harus production-ready.
* Selalu jelaskan alasan pemilihan arsitektur.
* Jika ada beberapa pilihan implementasi, pilih yang paling scalable dan mudah dipelihara.
* Berikan hasil secara bertahap dan tunggu konfirmasi sebelum melanjutkan ke fase berikutnya.
