# 📋 RINCIAN LENGKAP IMPLEMENTASI KERJA PRAKTIK (KP) - AGENDARIS DINKOMINFO

**Sistem**: Agendaris Dinkominfo (Sistem Informasi Manajemen Agenda, Presensi Digital, Notulensi AI, & Pengesahan Rapat OPD)  
**Teknologi**: Laravel 11, PostgreSQL, TailwindCSS, AlpineJS, Dompdf, PHPWord, Gemini 1.5 Flash API, Whisper.cpp.

---

## 👤 MAHASISWA 1: CORE ARCHITECTURE, AUTH/RBAC, MASTER DATA, AGENDA, & CALENDAR ENGINE

### 1. Fokus Utama Implementasi
Berfokus pada **Arsitektur Dasar Sistem, Autentikasi & Keamanan Sesi, Manajemen Akses Berbasis Role (RBAC 6 Role + Dynamic Delegation), Master Data OPD, Penjadwalan Agenda Rapat, Data Masking Privasi, serta Engine Visualisasi Kalender Rinci Grid Mingguan**.

### 2. Fitur & Tanggung Jawab Utama
- **Autentikasi & Keamanan Akses**:
  - Login NIP, Logout, Ubah Kata Sandi, dan Mekanisme *Force Password Change* (`must_change_password`) pada login pertama.
  - *Mekanisme Auto-Logout & Session Invalidation*: Pada `RoleMiddleware`, jika pegawai dinonaktifkan (`!$user->active`), sistem otomatis memicu `Auth::logout()`, menginvalidasi sesi aktif (`$request->session()->invalidate()`), dan me-regenerasi token CSRF.
- **Manajemen User & Bidang OPD (Role Admin & Dynamic Delegation)**:
  - CRUD Data Pegawai, NIP Unique Validation, Assign Bidang, Reset Password Default, Toggle Status Aktif/Nonaktif Pegawai, dan Master Bidang OPD.
  - *Delegasi Wewenang Bidang Sekretariat*: Staff pada Bidang Sekretariat (`$user->isSekretariat()`) secara dinamis diberikan wewenang untuk melakukan aksi pembukuan/backup yang biasanya khusus untuk Sekretaris Master / Sekretaris Bidang.
- **Form Penjadwalan Agenda Rapat**:
  - Penjadwalan rapat/kegiatan, kategori rapat, penentuan lokasi/jam, nomor surat dasar, dan pembatasan batas maksimal 3 bidang untuk Sekretaris Bidang.
- **Matrix Hak Akses Rapat (`hak_akses`) & Data Masking**:
  - Penentuan cakupan rapat (*Semua Orang*, *Lintas Dinas*, atau *Bidang Spesifik*).
  - *Data Masking Privasi*: Agenda yang tidak memiliki hak akses bagi pegawai biasa (`staff`) disamarkan judul dan lokasinya menjadi `"Rapat Terbatas (Dinkominfo)"` pada tampilan visual kalender.
- **Engine Kalender Rinci Grid Mingguan (`/calendar`)**:
  - Algoritma kalkulasi *overlapping events* (`calculateOverlaps()`) untuk mengatur posisi offset kolom berdampingan pada jam bentrok.
  - Navigasi mingguan (Senin–Minggu) dan indikator event bulanan pada mini-calendar.
- **Dashboard Overview & Optimasi Performansi**:
  - KPI Statistik Utama Admin/Sekretaris, Ringkasan Agenda Bulanan, Caching data pegawai per-bidang (`Cache::remember('active_bidangs_users', 300)`), serta indeksi komposit database `(tanggal, jam_mulai)`.

### 3. Alur Kerja Sistem yang Ditangani
```text
Login NIP ➔ Check Must Change Password ➔ Dashboard / Admin Management 
➔ Form Tambah Agenda ➔ Penentuan Hak Akses Multi-Bidang 
➔ Simpan Agenda ➔ Kalkulasi Grid Overlap Jam ➔ Visualisasi Kalender Rinci (Disamarkan jika Terbatas)
```

### 4. Komponen Source Code Terkait
- **Controllers**: `AuthController.php`, `AdminUserController.php`, `AgendaController.php` (Store, Update, Destroy), `DashboardController.php` (Index, Calendar, GetEvents).
- **Middleware**: `RoleMiddleware.php` (RBAC & Dynamic Sekretariat Backup), `ForcePasswordChange.php`.
- **Models**: `User.php` (Role helpers & `hasAccessToAgenda`), `Bidang.php`, `Agenda.php`.
- **Migrations**: `0001_01_01_000000_create_users_table.php`, `2026_07_09_000002_create_agendas_table.php`, `2026_07_24_150000_add_performance_indices_to_agendas_table.php`.
- **Views**: `auth/login.blade.php`, `auth/change-password.blade.php`, `profile.blade.php`, `admin/users.blade.php`, `admin/bidang.blade.php`, `calendar.blade.php`, `dashboard.blade.php`.

### 5. Kompleksitas & Beban Pengerjaan
- **Skor Kompleksitas**: **8.8 / 10**
- **Tantangan Teknis**: Algoritma penghitungan offset posisi *overlapping events* di kalender grid jam-demi-jam, penyaringan query JSON `hak_akses`, penegakan RBAC 6 role di level middleware backend, serta proteksi data masking privasi rapat terbatas.

### 6. Batas Tanggung Jawab (No-Overlap Boundary)
- Mahasiswa 1 **TIDAK MENANGANI** proses presensi digital mandiri, tanda tangan canvas, upload audio, proses AI, notulensi, pengesahan pimpinan, maupun ekspor PDF/DOCX.

### 7. Kontribusi Teknis Laporan KP
- Implementasi sistem otorisasi multi-level (RBAC 6 role) dengan delegasi dinamis.
- Algoritma pemisahan kolom visual event yang bentrok jam di kalender grid.
- Optimasi kueri SQL scoping berbasis JSON array dan indeksi komposit `(tanggal, jam_mulai)`.

### 8. Rekomendasi Judul Laporan Kerja Praktik
> **"Rancang Bangun Sistem Penjadwalan Agenda Rapat dan Engine Kalender Grid Berbasis Role-Based Access Control (RBAC) pada Dinas Komunikasi dan Informatika"**

---

## 👤 MAHASISWA 2: DIGITAL ATTENDANCE, SIGNATURE CANVAS, EXECUTIVE APPROVAL, & ANALYTICS

### 1. Fokus Utama Implementasi
Berfokus pada **Sub-sistem Presensi Digital Mandiri Pegawai, Integrasi HTML5 Canvas Tanda Tangan Elektronik, Workflow Pengesahan Pimpinan (Executive Approval Routing), Proteksi Integritas Data Presensi Historis, dan Analytics Kehadiran**.

### 2. Fitur & Tanggung Jawab Utama
- **Peserta Internal & Tamu Eksternal**:
  - Pemilihan peserta rapat internal (`meeting_participants`) dan manajemen tamu eksternal (`agenda_external_participants`).
- **Absen Digital Mandiri Pegawai & Canvas Signature**:
  - Modal presensi digital cepat, integrasi HTML5 Canvas Tanda Tangan Digital (`signature_pad` Base64), dan pembatasan presensi ganda (`composite unique index`).
- **Validasi Jendela Waktu Presensi (Grace Period & Expiry)**:
  - Pembukaan & penutupan presensi (`canPresensiBeFilled()`), penanganan *grace period* 1 jam setelah rapat selesai (`isPresensiInGracePeriod()`), penanganan expired (`isPresensiExpired()`), serta status `hadir`, `izin`, `sakit`, `alfa`.
- **Koreksi Manual Presensi**:
  - Fitur koreksi status presensi oleh Sekretaris Rapat untuk penyesuaian pegawai yang tidak melakukan absen mandiri.
- **Workflow Review & Executive Approval Routing**:
  - Pratinjau dokumen notulensi oleh Pimpinan.
  - *Executive Approval Routing Rules* (`isApproverOfAgenda`):
    - Agenda Lintas Dinas / Semua Orang $\rightarrow$ Disahkan oleh Kepala Dinas (`ketua_master`).
    - Agenda Spesifik Internal Bidang $\rightarrow$ Disahkan oleh Kepala Bidang (`ketua_bidang`) terkait.
  - Pembacaan tanda tangan digital canvas Pimpinan (`tanda_tangan_approver`), aksi pengesahan (`disahkan`), atau pengajuan revisi dengan catatan revisi (`catatan_revisi`).
- **Proteksi Integritas Presensi Historis (*Orphan Presence Preservation*)**:
  - Algoritma `Agenda::getInternalParticipants()` menggabungkan peserta undangan aktif dengan record presensi terdaftar (`concat` & `unique('id')`) agar data presensi lama **tidak hilang/tersembunyi** jika pegawai di-uncheck dari daftar undangan.
- **Hak Akses TV Monitoring Presensi Hari Ini (`canViewAgendaToday`)**:
  - Otorisasi khusus untuk menampilkan layar monitoring rapat real-time (`/agenda/today`) bagi Pimpinan, Sekretaris, dan Staff Sekretariat.
- **Rekapitulasi Kehadiran & Riwayat Rapat (`/riwayat`)**:
  - Rekapitulasi per-bidang (`getAttendanceRecapByBidang()`) di Detail Agenda, Rekap Rapat, dan riwayat rapat pegawai berbasis SQL scoping.

### 3. Alur Kerja Sistem yang Ditangani
```text
Buka Detail Agenda ➔ Presensi Digital Mandiri (Canvas Tanda Tangan) ➔ Rekap Kehadiran Multi-Bidang 
➔ Review Notulensi Pimpinan (Routing Ketua Master vs Ketua Bidang) 
➔ Pengesahan Tanda Tangan Canvas Pimpinan / Catatan Revisi ➔ Riwayat Rapat Pegawai
```

### 4. Komponen Source Code Terkait
- **Controllers**: `PresensiController.php`, `NotulensiController.php` (Review, Approve, RequestRevision), `AgendaController.php` (Show, Today), `DashboardController.php` (Riwayat).
- **Models**: `Presensi.php`, `AgendaExternalParticipant.php`, `Agenda.php` (Presensi helpers & `getInternalParticipants`), `User.php` (`isApproverOfAgenda` & `canViewAgendaToday`).
- **Migrations**: `2026_07_09_000003_create_presensis_table.php`, `2026_07_09_000004_create_agenda_external_participants_table.php`, `2026_07_14_065903_add_signature_and_keterangan_to_presensis_table.php`, `2026_07_23_081801_add_tanda_tangan_approver_to_notulensis_table.php`, `2026_07_23_100000_create_meeting_participants_table.php`.
- **Views**: `agenda/show.blade.php`, `agenda/today.blade.php`, `riwayat/index.blade.php`, `notulensi/review.blade.php`.

### 5. Kompleksitas & Beban Pengerjaan
- **Skor Kompleksitas**: **8.8 / 10**
- **Tantangan Teknis**: Pemrosesan gambar Base64 Tanda Tangan Canvas (pegawai dan Pimpinan), pencegahan race condition presensi ganda (`composite unique index`), aturan hirarki pengesahan Pimpinan, serta preservasi orphan presensi historis.

### 6. Batas Tanggung Jawab (No-Overlap Boundary)
- Mahasiswa 2 **TIDAK MENANGANI** pemrosesan audio, transkripsi AI, editor isi notulensi, generator PDF/DOCX, maupun pembuatan master user/bidang.

### 7. Kontribusi Teknis Laporan KP
- Integrasi penangkapan dan validasi data tanda tangan digital berbasis HTML5 Canvas.
- Penerapan state machine pengesahan pimpinan berhirarki dengan penguncian dokumen final.
- Algoritma penggabungan data historis presensi terpisah (*orphan presence preservation*).

### 8. Rekomendasi Judul Laporan Kerja Praktik
> **"Penerapan Presensi Digital dan Workflow Pengesahan Pimpinan Berbasis Tanda Tangan Elektronik Canvas pada Aplikasi Agendaris Dinkominfo"**

---

## 👤 MAHASISWA 3: AUDIO AI TRANSCRIPTION, NOTULENSI ENGINE, & DOCUMENT EXPORTER

### 1. Fokus Utama Implementasi
Berfokus pada **Arsitektur Pemrosesan Audio, Transkripsi AI Asinkron (Queue Worker), Dual-Engine AI Failover, Editor Notulensi Interaktif, dan Generator Ekspor Dokumen Resmi (PDF & DOCX)**.

### 2. Fitur & Tanggung Jawab Utama
- **Upload Multi-Audio Rekaman**:
  - Unggah hingga 3 berkas audio rapat (MP3, WAV, M4A, OGG max 40MB per berkas) dan penyimpanan disk storage `public`.
- **Asynchronous Queue Processing & Thread Safety**:
  - Pengolahan transkripsi di latar belakang menggunakan Laravel Queue Worker (`database` driver) dan Job `ProcessMeetingAudio`.
  - *Idempotency Guard & Limits*: Pemeriksaan `$this->notulensi->refresh()`, flag `is_transcribing`, batasan memori `512M`, dan timeout job 300 detik.
- **Dual-Engine AI Transcription & Fallback**:
  - Integrasi Cloud AI (Google Gemini 1.5 Flash API) sebagai engine utama.
  - Fallback otomatis ke Subprocess CLI Whisper.cpp lokal (`transcribe_whisper_cpp.py`) apabila koneksi internet terputus atau quota habis.
- **Mekanisme Auto-Heal Crash Recovery**:
  - Deteksi otomatis antrean queue mati/crash pada `AgendaController@show` / `NotulensiController@checkStatus` untuk mengembalikan flag `is_transcribing` ke `false` dan menampilkan pesan kesalahan (`transkrip_error`).
- **Editor Notulensi Interaktif**:
  - Pengeditan poin Ringkasan, Pembahasan, Keputusan, Kesimpulan, dan Judul Kustom.
- **Pengajuan Review Notulensi (`submitForReview`)**:
  - Pengubahan status notulensi dari `draft` menjadi `menunggu_review` untuk dikirimkan ke Pimpinan.
- **Engine Generator Ekspor Dokumen Resmi**:
  - Pembuatan dokumen PDF resmi via Dompdf dan dokumen Word `.docx` via PHPWord lengkap dengan Kop Surat Dinkominfo, Poin Rapat, dan Rendering Tanda Tangan Digital Pimpinan (`tanda_tangan_approver`).

### 3. Alur Kerja Sistem yang Ditangani
```text
Upload Audio Rapat ➔ Queue Worker Asinkron ➔ Dual-Engine AI (Gemini Cloud / Whisper.cpp Fallback) 
➔ Hasil Transkripsi & Perapihan ➔ Editor Poin Notulensi ➔ Submit for Review 
➔ Ekspor PDF & DOCX Resmi dengan Kop Surat & Tanda Tangan Canvas
```

### 4. Komponen Source Code Terkait
- **Controllers**: `NotulensiController.php` (UploadAudio, DeleteAudio, ProcessAudio, Edit, SaveDraft, SubmitForReview, ExportPdf, ExportDocx, CheckStatus).
- **Jobs**: `App\Jobs\ProcessMeetingAudio.php` (Queue Job & Subprocess CLI).
- **Models**: `Notulensi.php`.
- **Migrations**: `2026_07_09_000005_create_notulensis_table.php`, `2026_07_15_002548_add_audio_files_to_notulensis_table.php`, `2026_07_15_011446_add_custom_titles_to_notulensis_table.php`, `2026_07_15_012239_add_is_transcribing_to_notulensis_table.php`, `2026_07_15_032529_add_transkrip_error_to_notulensis_table.php`.
- **Views**: `notulensi/edit.blade.php`, `notulensi/export_pdf.blade.php`, `notulensi/export_docx.blade.php`.

### 5. Kompleksitas & Beban Pengerjaan
- **Skor Kompleksitas**: **8.8 / 10**
- **Tantangan Teknis**: Manajemen background process queue, integrasi API AI external & subprocess CLI executables, penanganan recovery crash queue (Auto-Heal), serta layouting dokumen PDF/DOCX dinamis ber-kop surat.

### 6. Batas Tanggung Jawab (No-Overlap Boundary)
- Mahasiswa 3 **TIDAK MENANGANI** form pembuat agenda, pengisian presensi pegawai, halaman review tanda tangan pimpinan, maupun visualisasi kalender grid.

### 7. Kontribusi Teknis Laporan KP
- Implementasi antrean pemrosesan audio asinkron berbasis Laravel Queue.
- Integrasi Dual-Engine AI (Gemini Cloud API + Whisper.cpp Local Executable) dengan fail-safe.
- Perancangan engine pembuat dokumen resmi ber-kop surat otomatis berbasis Dompdf dan PHPWord.

### 8. Rekomendasi Judul Laporan Kerja Praktik
> **"Otomatisasi Penyusunan Notulensi Rapat Menggunakan Transkripsi AI Asinkron Dual-Engine dan Generator Dokumen Resmi pada Aplikasi Agendaris Dinkominfo"**

---

## 🔗 SHARED COMPONENTS (KOMPONEN BERSAMA)

Komponen berikut digunakan secara bersama-sama oleh ketiga mahasiswa, dengan penanggung jawab utama sebagai berikut:

| Shared Component | Fungsi Sistem | Penanggung Jawab Utama | Konsumen / Pengguna Lain |
| :--- | :--- | :--- | :--- |
| **Model `Agenda.php`** | Entitas Data Utama Rapat | **Mahasiswa 1** (Core Structure & Event CRUD) | Mahasiswa 2 (Presensi Helpers & Orphan Preservation), Mahasiswa 3 (Notulensi Relation) |
| **Model `User.php`** | Entitas Pegawai & Authorization | **Mahasiswa 1** (Auth, RBAC 6 Role, User Management) | Mahasiswa 2 (Approver Routing & Participant Relation), Mahasiswa 3 (Approver Relation) |
| **Layout `app.blade.php`** | Template Utama UI, Header, & Sidebar | **Mahasiswa 1** (Navigation Structure & Design Tokens) | Mahasiswa 2 & 3 (View Integration) |
| **View `agenda/show.blade.php`** | Halaman Detail Utama Agenda Rapat | **Mahasiswa 2** (Presensi & Rekap Kehadiran Layout) | Mahasiswa 1 (Info Agenda & Hak Akses), Mahasiswa 3 (Pemicu Process Audio & Status Transkripsi) |

---

## 📊 TABEL RINGKASAN PEMBAGIAN KERJA PRAKTIK

| Mahasiswa | Fokus Utama Implementasi | Fitur & Tanggung Jawab Utama | Kompleksitas | Rekomendasi Judul Laporan Kerja Praktik (KP) |
| :--- | :--- | :--- | :---: | :--- |
| **Mahasiswa 1** | *Core System, Auth/RBAC, User OPD, Agenda, & Calendar Engine* | Autentikasi, Force Password Change, RBAC 6 Role + Dynamic Sekretariat Backup, Master Data Pegawai & Bidang OPD, Data Masking Rapat Terbatas, Cache Layer, Indeksi Komposit SQL, & Engine Overlapping Calendar Mingguan. | **8.8 / 10** | *Rancang Bangun Sistem Penjadwalan Agenda Rapat dan Engine Kalender Grid Berbasis Role-Based Access Control (RBAC) pada Dinas Komunikasi dan Informatika* |
| **Mahasiswa 2** | *Digital Attendance, Signature Canvas, Executive Approval, & Analytics* | Management Peserta Undangan (Internal/Eksternal), Absen Digital Canvas Signature Base64, Jendela Waktu Presensi (Grace Period 1 Jam), Executive Approval Routing (Ketua Master vs Ketua Bidang), Orphan Presence Preservation, & Live TV Board Monitoring. | **8.8 / 10** | *Penerapan Presensi Digital dan Workflow Pengesahan Pimpinan Berbasis Tanda Tangan Elektronik Canvas pada Aplikasi Agendaris Dinkominfo* |
| **Mahasiswa 3** | *Asynchronous AI Transcription, Notulensi Engine, & Document Exporter* | Upload Multi-Audio (Max 3 Files), Laravel Queue Worker Asinkron + Idempotency Guard, Dual-Engine AI (Gemini 1.5 Flash Cloud API + Whisper.cpp Fallback), Auto-Heal Crash Recovery, & Generator Dokumen Resmi Ber-Kop Surat (Dompdf & PHPWord). | **8.8 / 10** | *Otomatisasi Penyusunan Notulensi Rapat Menggunakan Transkripsi AI Asinkron Dual-Engine dan Generator Dokumen Resmi pada Aplikasi Agendaris Dinkominfo* |
