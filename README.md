<p align="center">
  <h1 align="center">📅 SIRENA — Agendaris Dinkominfo</h1>
  <p align="center"><b>Sistem Informasi Rekapitulasi & Agenda Dinas Komunikasi dan Informatika</b></p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/PostgreSQL-15.x-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/Google%20Gemini%20AI-Integration-4285F4?style=for-the-badge&logo=google&logoColor=white" alt="Google Gemini AI">
  <img src="https://img.shields.io/badge/Whisper.cpp-Offline%20STT-000000?style=for-the-badge&logo=openai&logoColor=white" alt="Whisper.cpp Engine">
</p>

---

## 📌 Tentang Aplikasi

**SIRENA (Sistem Informasi Rekapitulasi & Agenda Dinkominfo)** adalah platform web terpadu yang dirancang khusus untuk mengelola seluruh agenda kegiatan dinas, presensi digital mandiri, serta penyusunan dan pengesahan notulensi berbasis **Kecerdasan Buatan (AI Hybrid: Google Gemini 1.5 & Whisper.cpp)** di lingkungan Dinas Komunikasi dan Informatika Kabupaten Banyumas.

Sistem ini memfasilitasi koordinasi antar-bidang, pencatatan kehadiran pegawai dengan tanda tangan digital canvas, otomatisasi transkripsi audio rapat menggunakan AI, serta alur pengesahan digital resmi oleh Pimpinan/Kepala Dinas.

---

## ✨ Fitur Utama

### 🗓️ 1. Manajemen Agenda & Kalender Kegiatan
- **Kalender Grid Interaktif**: Tampilan agenda mingguan, mini calendar, dan filter kegiatan hari ini.
- **Kategori Kegiatan**: Rapat, Sosialisasi, Pelatihan, dan Kegiatan Lainnya.
- **Pengaturan Disposisi & Hak Akses**: Pembatasan akses agenda per bidang atau lintas dinas (*semua orang*).
- **Manajemen Nomor Surat**: Pencatatan dan pembaharuan Nomor Surat Dasar pelaksanaan agenda rapat.

### ✍️ 2. Absensi Digital & Tanda Tangan Mandiri
- **Presensi Digital Canvas**: Pegawai melakukan absensi mandiri lengkap dengan Tanda Tangan Digital pada layar sentuh/mouse.
- **Penguncian Jendela Waktu (Auto-Alfa)**: Batas waktu presensi mandiri dibuka tepat saat rapat mulai dan otomatis terkunci 1 jam setelah rapat selesai (status belum absen berubah menjadi **ALFA**).
- **Anti-Kecurangan & Koreksi Manual**: Pegawai hanya bisa presensi untuk akun NIP sendiri. Fitur Koreksi Presensi Manual tersedia khusus untuk Notulis/Admin.

### 🤖 3. Notulensi AI Hybrid (Gemini 1.5 & Whisper.cpp Fallback)
- **Primary Cloud STT (Google Gemini 1.5 Flash)**: Memproses rekaman suara rapat di cloud untuk transkripsi dan rangkuman poin rapat terstruktur.
- **Local Fallback STT (Whisper.cpp CLI)**: Mesin cadangan lokal berbasis Python & Whisper.cpp jika koneksi internet terputus.
- **Background Queue Processing**: Pemrosesan audio berjalan di background queue (`queue:work`) sehingga sistem tidak mengalami timeout.
- **Interactive Notulensi Editor**: Editor interaktif untuk merapikan poin ringkasan, pembahasan, keputusan, dan kesimpulan rapat.

### ✒️ 4. Verifikasi & Pengesahan Digital Pimpinan
- **Alur Persetujuan Dokumen**: `Draft` ➔ `Menunggu Review` ➔ `Perlu Revisi` / `Telah Disahkan`.
- **Privasi Staff**: Notulensi yang sedang dalam proses perbaikan/revisi akan ditampilkan dengan status netral **"Sedang Ditinjau"** pada layar Staff umum. Catatan revisi hanya dapat dibaca oleh Notulis dan Pimpinan.
- **Export Dokumen Resmi**: Unduh hasil notulensi ke format **PDF (Dompdf)** dan **Word (PHPWord)** berformat Tata Naskah Dinas Pemkab Banyumas lengkap dengan Tanda Tangan Digital.

### 🛡️ 5. Manajemen Pengguna & Keamanan (RBAC)
- **Multi-Role RBAC (Role-Based Access Control)**:
  - `admin`: Administrator Sistem (Kelola User, Bidang, TV Board).
  - `ketua_master`: Kepala Dinas (Pengesahan Notulensi Seluruh Dinas).
  - `sekretaris_master`: Sekretaris Dinas (Kelola Agenda & Notulensi Dinas).
  - `ketua_bidang`: Kepala Bidang / Kasubag (Pengesahan Notulensi Bidang).
  - `sekretaris_bidang`: Sekretaris Bidang (Kelola Agenda & Notulensi Bidang).
  - `staff`: Staff / Pegawai (Kalender, Presensi Mandiri, Download Notulen Sah).
- **Keamanan Login**: Protection *Force Password Change* pada login pertama & hashing BCRYPT.

---

## 🛠️ Teknologi & Dependensi

- **Backend**: PHP 8.2+, Laravel 11.x / 12.x (Eloquent ORM, Blade Templating)
- **Database Server**: **PostgreSQL v14+ / v15+ / v16+** (Native JSONB & Full-Text Search) / MySQL
- **Engine AI**: Google Gemini 1.5 Flash API Key (`GEMINI_API_KEY`) & Local Whisper.cpp CLI (Python 3.10+)
- **Audio Processing**: FFmpeg
- **Frontend**: TailwindCSS, Alpine.js, HTML5 Canvas Signature
- **Dokumen Generator**: `barryvdh/laravel-dompdf` & `phpoffice/phpword`

---

## 🚀 Panduan Instalasi & Konfigurasi

### 1. Prasyarat Sistem
- PHP >= 8.2 (dengan ekstensi `pdo_pgsql` dan `pgsql`)
- PostgreSQL 14 / 15 / 16 (atau MySQL)
- Composer >= 2.x
- FFmpeg Media Engine
- Python 3.10+ & package `openai-whisper`

### 2. Clone Repository
```bash
git clone https://github.com/alzeecyy/Agendaris_Dinkominfo.git sirena
cd sirena
```

### 3. Instalasi Dependensi PHP
```bash
composer install
```

### 4. Konfigurasi Environment (`.env`)
Salin berkas contoh `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Atur konfigurasi database PostgreSQL dan API Key Google Gemini:
```ini
APP_NAME="SIRENA - Agendaris Dinkominfo"
APP_URL=http://sirena.test

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sirena_db
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password

# API Key Google Gemini untuk fitur Notulensi AI
GEMINI_API_KEY=your_google_gemini_api_key_here
```

### 5. Generate Application Key, Migration & Link Storage
```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### 6. Jalankan Background Queue Worker
```bash
php artisan queue:work
```
*(Wajib berjalan agar fitur transkripsi AI audio bekerja di latar belakang).*

---

## 🔑 Akun Default (Seeder)

Setelah menjalankan `php artisan migrate --seed`, Anda dapat menguji aplikasi menggunakan akun default berikut (Password default: `password`):

| Role | NIP | Nama / Jabatan | Bidang |
| :--- | :--- | :--- | :--- |
| **Admin System** | `199001012015011000` | Administrator System | - |
| **Kepala Dinas (Kadin)** | `199001012015011001` | Ir. Purwadi Santoso, M.Hum. | - |
| **Sekretaris Dinas (Sekdin)** | `199001012015011002` | Drs. H. Mulyono, M.Si. | Sekretariat |
| **Kabid Aptika** | `199001012015011010` | Hendra Wijaya, S.Kom. | Bidang Aptika |
| **Sekretaris Bidang Aptika** | `199001012015011011` | Dewi Lestari, S.T. | Bidang Aptika |
| **Staff Aptika (13 Orang)** | `199001012015011012` s.d. `199001012015011024` | Staff Aplikasi Informatika | Bidang Aptika |
| **Kabid IKP** | `199001012015011030` | Drs. Bambang Sutejo | Bidang IKP |
| **Sekretaris Bidang IKP** | `199001012015011031` | Rini Handayani, S.Sos. | Bidang IKP |
| **Staff IKP (13 Orang)** | `199001012015011032` s.d. `199001012015011044` | Staff IKP | Bidang IKP |
| **Kabid Statistik** | `199001012015011050` | Sigit Pramono, S.Si., M.Si. | Bidang Statistik & Persandian |
| **Sekretaris Bidang Statistik** | `199001012015011051` | Agus Setiawan, S.Stat. | Bidang Statistik & Persandian |
| **Staff Statistik (13 Orang)** | `199001012015011052` s.d. `199001012015011064` | Staff Statistik | Bidang Statistik & Persandian |
| **Kasubag Umum** | `199001012015011070` | Tri Cahyono, S.H. | Subbag Umum & Kepegawaian |
| **Admin Subbag Umum** | `199001012015011071` | Ahmad Rizky, A.Md. | Subbag Umum & Kepegawaian |
| **Staff Subbag Umum (4 Orang)** | `199001012015011072` s.d. `199001012015011075` | Staff Subbag Umum | Subbag Umum & Kepegawaian |
| **Kasubag Keuangan** | `199001012015011075` | Sri Wahyuni, S.E. | Subbag Keuangan |
| **Admin Subbag Keuangan** | `199001012015011076` | Ratna Juwita, A.Md. | Subbag Keuangan |
| **Staff Subbag Keuangan (3 Orang)**| `199001012015011077` s.d. `199001012015011079` | Staff Subbag Keuangan | Subbag Keuangan |
| **Kasubag Perencanaan** | `199001012015011080` | Drs. Hendro Wibowo | Subbag Perencanaan |
| **Admin Subbag Perencanaan** | `199001012015011081` | Budi Hartono, S.E. | Subbag Perencanaan |
| **Staff Subbag Perencanaan (3 Orang)**| `199001012015011082` s.d. `199001012015011084` | Staff Subbag Perencanaan | Subbag Perencanaan |

---

## 📄 Lisensi

Pengembangan aplikasi ini dikelola secara internal untuk Dinas Komunikasi dan Informatika Kabupaten Banyumas.

---
<p align="center">
  <b>SIRENA &copy; 2026 Dinas Komunikasi dan Informatika</b>
</p>
