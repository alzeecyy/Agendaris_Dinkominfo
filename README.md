<p align="center">
  <h1 align="center">📅 SIRENA — Agendaris Dinkominfo</h1>
  <p align="center"><b>Sistem Informasi Rekapitulasi & Agenda Dinas Komunikasi dan Informatika</b></p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/TailwindCSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Google%20Gemini%20AI-Integration-4285F4?style=for-the-badge&logo=google&logoColor=white" alt="Google Gemini AI">
</p>

---

## 📌 Tentang Aplikasi

**SIRENA (Agendaris Dinkominfo)** adalah sistem informasi berbasis web yang dirancang khusus untuk mengelola seluruh agenda kegiatan, rapat dinas, presensi digital mandiri, serta penyusunan dan pengesahan notulensi berbasis **Kecerdasan Buatan (AI)** di lingkungan Dinas Komunikasi dan Informatika.

Sistem ini memudahkan koordinasi antar-bidang, pencatatan kehadiran pegawai dengan tanda tangan digital, transkripsi & perapihan notulensi otomatis menggunakan **Google Gemini AI**, serta pengesahan dokumen notulensi resmi secara digital oleh Pimpinan/Kepala Dinas.

---

## ✨ Fitur Utama

### 🗓️ 1. Manajemen Agenda & Kalender Kegiatan
- **Kalender Interaktif**: Tampilan agenda bulanan, mini calendar, dan filter kegiatan hari ini.
- **Kategori Kegiatan**: Mendukung berbagai jenis kegiatan (Rapat, Sosialisasi, Pelatihan, Bimtek, Workshop, Seminar, Webinar, Koordinasi).
- **Pengaturan Disposisi / Hak Akses**: Pembatasan akses agenda (Semua Pegawai, Bidang Tertentu, atau Spesifik Pegawai).
- **Manajemen Nomor Surat**: Pencatatan dan pembaharuan Nomor Surat Dasar pelaksanaan agenda rapat.

### ✍️ 2. Absensi Digital & Tanda Tangan Mandiri
- **Presensi Digital Canvas**: Pegawai melakukan absensi mandiri lengkap dengan tanda tangan digital pada layar sentuh/mouse.
- **Masa Toleransi Absensi (Auto-Alfa)**: Sistem secara otomatis mendeteksi batas waktu presensi (1 jam setelah rapat berakhir) dan mengubah status pegawai yang belum absen menjadi **Alfa**.
- **Koreksi Presensi Manual**: Fitur bagi Sekretaris/Admin untuk melakukan perbaikan status presensi pegawai.
- **Rekap Kehadiran Bidang**: Pemantauan statistik kehadiran pegawai per bidang/unit kerja secara *real-time*.

### 🤖 3. Notulensi Berbasis AI (Google Gemini AI)
- **Audio Processing & Transkripsi**: Unggah rekaman suara rapat untuk diproses dan dirapikan otomatis menjadi draf notulensi resmi.
- **Refine & Analysis Catatan**: AI merapikan rangkuman poin penting rapat, keputusan, dan tindak lanjut secara terstruktur.
- **Draf Peserta Eksternal**: Pencatatan peserta tamu/luar dinas secara rinci.

### ✒️ 4. Verifikasi & Pengesahan Digital Pimpinan
- **Alur Persetujuan Dokumen**: `Draf` ➔ `Menunggu Review` ➔ `Perlu Revisi` / `Telah Disahkan`.
- **Tanda Tangan Digital Pimpinan**: Pengesahan notulensi oleh Kepala Dinas/Kepala Bidang secara digital melalui modal Canvas Signature.
- **Export Dokumen Resmi**: Unduh hasil notulensi ke format **PDF (DomPDF)** dan **DOCX (Microsoft Word)** yang sudah dilengkapi Kop Dinas dan Tanda Tangan Digital.
- **Arsip Notulensi Dinas**: Pencarian dan pengarsipan notulensi resmi yang dapat diakses antar-bidang.

### 🛡️ 5. Manajemen Pengguna & Keamanan
- **Multi-Role RBAC (Role-Based Access Control)**:
  - `admin`: Administrator Sistem (Manajemen User & Bidang).
  - `ketua_master`: Kepala Dinas (Kadin).
  - `sekretaris_master`: Sekretaris Dinas (Sekdin).
  - `ketua_bidang`: Kepala Bidang (Kabid).
  - `sekretaris_bidang`: Sekretaris/Admin Bidang.
  - `staff`: Staff / Pegawai / Kasi.
- **Keamanan Login**: Fitur *Force Change Password* pada login pertama & proteksi hashing BCRYPT.

---

## 🛠️ Teknologi & Dependensi

- **Backend**: PHP 8.3+, Laravel 12.x / 13.x (Eloquent ORM, Blade Templating)
- **Frontend**: TailwindCSS 4.0, Alpine.js, HTML5 Canvas
- **Kecerdasan Buatan (AI)**: Google Gemini API (`GEMINI_API_KEY`)
- **PDF & Document Engine**: `barryvdh/laravel-dompdf`
- **Database**: MySQL / MariaDB / SQLite
- **Build Tool**: Vite 8.0, Node.js

---

## 🚀 Panduan Instalasi Lokal

### 1. Prasyarat Sistem
- PHP >= 8.3
- Composer >= 2.x
- Node.js >= 18.x & NPM
- Database Server (MySQL/MariaDB via Laragon/XAMPP atau SQLite)

### 2. Clone Repository
```bash
git clone https://github.com/alzeecyy/Agendaris_Dinkominfo.git
cd Agendaris_Dinkominfo
```

### 3. Instalasi Dependensi PHP & Node.js
```bash
composer install
npm install
```

### 4. Konfigurasi Environment (`.env`)
Salin berkas contoh `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Buka berkas `.env` dan atur konfigurasi database serta API Key Google Gemini:
```ini
APP_NAME="SIRENA - Agendaris Dinkominfo"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agendaris_dinkominfo
DB_USERNAME=root
DB_PASSWORD=

# API Key Google Gemini untuk fitur Notulensi AI
GEMINI_API_KEY=your_google_gemini_api_key_here
```

### 5. Generate Application Key & Migration
```bash
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```

### 6. Jalankan Server Pengembangan
Jalankan dev server Laravel dan Vite secara bersamaan:
```bash
# Opsi 1: Menggunakan command dev bawaan
npm run dev

# Atau Opsi 2: Menjalankan secara terpisah di 2 terminal
php artisan serve
npm run dev
```

Aplikasi dapat diakses melalui browser di: `http://127.0.0.1:8000`

---

## 🔑 Akun Default (Seeder)

Setelah menjalankan `php artisan db:seed`, Anda dapat menguji aplikasi menggunakan akun default berikut (Password default: `password`):

| Role | NIP | Nama / Jabatan | Bidang |
| :--- | :--- | :--- | :--- |
| **Admin System** | `199001012015011000` | Administrator Dinkominfo | - |
| **Kepala Dinas (Kadin)** | `199001012015011001` | Ir. Purwadi Santoso, M.Hum. | - |
| **Sekretaris Dinas (Sekdin)**| `199001012015011002` | Drs. Bambang Wijaya, M.Si. | Sekretariat |
| **Kepala Bidang (Aptika)** | `199001012015011003` | Eko Prasetyo, S.Kom., M.T. | Bidang Aptika |
| **Sekretaris Bidang (Aptika)**| `199001012015011004` | Siti Rahmawati, S.STP. | Bidang Aptika |
| **Staff (Aptika)** | `199001012015011005` | Budi Santoso, A.Md. | Bidang Aptika |

---

## 📁 Struktur Direktori Utama

```
Agendaris_Dinkominfo/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminUserController.php   # Manajemen Akun Pegawai & Bidang
│   │   ├── AgendaController.php      # Manajemen Agenda & Kegiatan
│   │   ├── AuthController.php        # Autentikasi & Profil
│   │   ├── DashboardController.php   # Dashboard, Kalender & Riwayat
│   │   ├── NotulensiController.php   # Pengolahan AI Notulensi, Export & Pengesahan
│   │   └── PresensiController.php    # Absensi Mandiri & Koreksi
│   └── Models/
│       ├── Agenda.php
│       ├── Bidang.php
│       ├── Notulensi.php
│       ├── Presensi.php
│       └── User.php
├── database/
│   ├── migrations/                  # Skema Database System
│   └── seeders/                     # Seeder Data Awal & Dummy Accounts
├── resources/
│   ├── views/                       # Blade Views & UI Components
│   │   ├── admin/                   # Panel Admin System
│   │   ├── agenda/                  # View Agenda & Absensi
│   │   ├── notulensi/               # View Edit, Review, & Export Notulensi
│   │   ├── riwayat/                 # View Riwayat & Filternya
│   │   ├── dashboard.blade.php      # Dashboard Utama Multi-Role
│   │   └── calendar.blade.php       # Kalender Interaktif
├── routes/
│   └── web.php                      # Endpoint Routing & Middleware RBAC
└── storage/
    └── app/public/presensi/         # Simpan Tanda Tangan Digital & File Audio
```

---

## 📄 Lisensi

Pengembangan aplikasi ini dikelola secara internal untuk Dinas Komunikasi dan Informatika.

---
<p align="center">
  <b>SIRENA &copy; 2026 Dinas Komunikasi dan Informatika</b>
</p>
