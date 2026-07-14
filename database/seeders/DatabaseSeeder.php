<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bidang;
use App\Models\User;
use App\Models\Agenda;
use App\Models\Notulensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Bidang
        $aptika = Bidang::create([
            'nama' => 'Bidang Aplikasi Informatika',
            'singkatan' => 'Aptika',
        ]);

        $ikp = Bidang::create([
            'nama' => 'Bidang Informasi dan Komunikasi Publik',
            'singkatan' => 'IKP',
        ]);

        $statistik = Bidang::create([
            'nama' => 'Bidang Statistik, Persandian, dan Infrastruktur Teknologi Informasi dan Komunikasi',
            'singkatan' => 'Statistik & Persandian',
        ]);

        $bidangList = [
            'aptika' => $aptika,
            'ikp' => $ikp,
            'statistik' => $statistik,
        ];

        // Shared password hash
        $hashedPassword = Hash::make('password');

        // Common NIP prefix (15 digits)
        $nipPrefix = "199001012015011";

        // 2. Seed Master Level Accounts
        // Admin
        $admin = User::create([
            'name' => 'Administrator Dinkominfo',
            'nip' => $nipPrefix . '000', // 199001012015011000
            'jabatan' => 'Sistem Administrator',
            'bidang_id' => null,
            'role' => 'admin',
            'password' => $hashedPassword,
            'must_change_password' => false,
            'active' => true,
        ]);

        // Ketua Master (Kepala Dinas)
        $ketuaMaster = User::create([
            'name' => 'Ir. Purwadi Santoso, M.Hum.',
            'nip' => $nipPrefix . '001', // 199001012015011001
            'jabatan' => 'Kepala Dinas / Ketua Master',
            'bidang_id' => null,
            'role' => 'ketua_master',
            'password' => $hashedPassword,
            'must_change_password' => true,
            'active' => true,
        ]);

        // Sekretaris Master
        $sekretarisMaster = User::create([
            'name' => 'Drs. H. Mulyono, M.Si.',
            'nip' => $nipPrefix . '002', // 199001012015011002
            'jabatan' => 'Sekretaris Master Dinas',
            'bidang_id' => null,
            'role' => 'sekretaris_master',
            'password' => $hashedPassword,
            'must_change_password' => true,
            'active' => true,
        ]);

        // Dummy Names Configuration
        $names = [
            'aptika' => [
                'ketua' => 'Hendra Wijaya, S.Kom.',
                'sekretaris' => 'Dewi Lestari, S.T.',
                'bendahara' => 'Siti Aminah, A.Md.Ak.',
                'staff' => [
                    'Budi Santoso, S.Kom.', 'Ahmad Fauzi, S.Tr.Kom.', 'Eko Prasetyo, A.Md.T.',
                    'Rina Wati, S.T.', 'Sri Rahayu, S.Kom.', 'Tri Utami, A.Md.',
                    'Heri Susanto, S.Kom.', 'Andi Wijaya, S.T.', 'Yudi Hermawan, A.Md.T.',
                    'Diana Putri, S.Kom.', 'Rian Hidayat, S.Tr.Kom.', 'Mega Utami, S.Kom.'
                ]
            ],
            'ikp' => [
                'ketua' => 'Drs. Bambang Sutejo',
                'sekretaris' => 'Rini Handayani, S.Sos.',
                'bendahara' => 'Kartika Sari, A.Md.Sos.',
                'staff' => [
                    'Joko Susilo, S.I.Kom.', 'Sri Wahyuni, A.Md.', 'Fajar Nugroho, S.Sos.',
                    'Indah Permata, S.I.Kom.', 'Arief Rahman, S.Sos.', 'Fitriani, A.Md.Kom.',
                    'Dedi Kurniawan, S.I.Kom.', 'Novianti, S.Sos.', 'Rudi Hartono, A.Md.',
                    'Larasati, S.I.Kom.', 'Taufik Hidayat, S.Sos.', 'Wulan Dari, S.I.Kom.'
                ]
            ],
            'statistik' => [
                'ketua' => 'Sigit Pramono, S.Si., M.Si.',
                'sekretaris' => 'Agus Setiawan, S.Stat.',
                'bendahara' => 'Retno Wulandari, A.Md.Stat.',
                'staff' => [
                    'Aditya Putra, S.Stat.', 'Anisa Fitri, A.Md.', 'Bambang Hermawan, S.Si.',
                    'Citra Lestari, S.Stat.', 'Denny Hidayat, S.Si.', 'Elisa Putri, A.Md.Stat.',
                    'Farhan Bashir, S.Si.', 'Gita Savitri, S.Stat.', 'Hafiz Rizky, S.Si.',
                    'Irma Suryani, A.Md.', 'Junaedi, S.Stat.', 'Kurniawati, S.Si.'
                ]
            ]
        ];

        // Suffix mapping per division (Aptika: 10-24, IKP: 30-44, Statistik: 50-64)
        $divisionConfig = [
            'aptika' => [
                'start' => 10,
                'bidang' => $aptika
            ],
            'ikp' => [
                'start' => 30,
                'bidang' => $ikp
            ],
            'statistik' => [
                'start' => 50,
                'bidang' => $statistik
            ]
        ];

        $usersMap = [];

        foreach ($divisionConfig as $key => $config) {
            $bidang = $config['bidang'];
            $startSuffix = $config['start'];
            $bidName = $bidang->nama;
            $data = $names[$key];

            // 1. Ketua Bidang (ends in 10, 30, 50)
            $suffixStr = str_pad($startSuffix, 3, '0', STR_PAD_LEFT);
            $usersMap[$key . '_ketua'] = User::create([
                'name' => $data['ketua'],
                'nip' => $nipPrefix . $suffixStr,
                'jabatan' => 'Kepala ' . $bidName,
                'bidang_id' => $bidang->id,
                'role' => 'ketua_bidang',
                'password' => $hashedPassword,
                'must_change_password' => true,
                'active' => true,
            ]);

            // 2. Sekretaris Bidang (ends in 11, 31, 51)
            $suffixStr = str_pad($startSuffix + 1, 3, '0', STR_PAD_LEFT);
            $usersMap[$key . '_sekretaris'] = User::create([
                'name' => $data['sekretaris'],
                'nip' => $nipPrefix . $suffixStr,
                'jabatan' => 'Sekretaris ' . $bidName,
                'bidang_id' => $bidang->id,
                'role' => 'sekretaris_bidang',
                'password' => $hashedPassword,
                'must_change_password' => true,
                'active' => true,
            ]);

            // 3. Bendahara Bidang (Staff Role) (ends in 12, 32, 52)
            $suffixStr = str_pad($startSuffix + 2, 3, '0', STR_PAD_LEFT);
            $usersMap[$key . '_bendahara'] = User::create([
                'name' => $data['bendahara'],
                'nip' => $nipPrefix . $suffixStr,
                'jabatan' => 'Bendahara ' . $bidName,
                'bidang_id' => $bidang->id,
                'role' => 'staff',
                'password' => $hashedPassword,
                'must_change_password' => true,
                'active' => true,
            ]);

            // 4. Staff 1 - 12 (Staff Role)
            for ($i = 0; $i < 12; $i++) {
                $suffixStr = str_pad($startSuffix + 3 + $i, 3, '0', STR_PAD_LEFT);
                $usersMap[$key . '_staff_' . $i] = User::create([
                    'name' => $data['staff'][$i],
                    'nip' => $nipPrefix . $suffixStr,
                    'jabatan' => 'Staff ' . $bidName,
                    'bidang_id' => $bidang->id,
                    'role' => 'staff',
                    'password' => $hashedPassword,
                    'must_change_password' => true,
                    'active' => true,
                ]);
            }
        }

        // 3. Seed Dummy Agendas (relative to today)
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $yesterday = Carbon::yesterday();

        // Rapat 1: Rapat Evaluasi SPBE (Today, overlaps to test split-column)
        // Bidang Aptika (ID: 1)
        $agenda1 = Agenda::create([
            'judul' => 'Rapat Evaluasi Indeks Layanan SPBE',
            'tanggal' => $today->toDateString(),
            'jam_mulai' => '08:30:00',
            'jam_selesai' => '10:00:00',
            'lokasi' => 'Ruang Rapat Kartini',
            'deskripsi' => 'Rapat koordinasi membahas pencapaian nilai indeks Sistem Pemerintahan Berbasis Elektronik (SPBE) tahun lalu serta penyusunan strategi perbaikan.',
            'kategori' => 'rapat',
            'hak_akses' => [(string)$aptika->id],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => '005/214/2026 Perihal Evaluasi Layanan IT',
            'sekretaris_id' => $usersMap['aptika_sekretaris']->id,
        ]);
        Notulensi::create([
            'agenda_id' => $agenda1->id,
            'status' => 'draft',
        ]);

        // Rapat 2: Rapat Integrasi API Layanan Publik (Today, overlaps with Rapat 1)
        // Bidang Aptika (ID: 1)
        $agenda2 = Agenda::create([
            'judul' => 'Rapat Penyelarasan API Portal Banyumas',
            'tanggal' => $today->toDateString(),
            'jam_mulai' => '09:30:00',
            'jam_selesai' => '11:00:00',
            'lokasi' => 'Ruang Server TIK',
            'deskripsi' => 'Penyelarasan antarmuka pemograman aplikasi (API) untuk mendukung integrasi single-sign-on (SSO) pada Portal Layanan Publik Banyumas.',
            'kategori' => 'rapat',
            'hak_akses' => [(string)$aptika->id],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => null, // Optional at start
            'sekretaris_id' => $usersMap['aptika_sekretaris']->id,
        ]);
        Notulensi::create([
            'agenda_id' => $agenda2->id,
            'status' => 'draft',
        ]);

        // Rapat 3: Rapat Lintas Bidang Kehumasan (Tomorrow)
        // Semua Orang
        $agenda3 = Agenda::create([
            'judul' => 'Sosialisasi & Koordinasi Media Publikasi Pemkab',
            'tanggal' => $tomorrow->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'lokasi' => 'Aula Kominfo Utama',
            'deskripsi' => 'Rapat koordinasi lintas bidang guna penyelarasan konten publikasi media sosial dinas dan penanganan disinformasi publik.',
            'kategori' => 'rapat',
            'hak_akses' => ['semua_orang'],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => '005/012/2026 Undangan Rilis Publikasi Media',
            'sekretaris_id' => $sekretarisMaster->id,
        ]);
        Notulensi::create([
            'agenda_id' => $agenda3->id,
            'status' => 'draft',
        ]);

        // Rapat 4: Rapat Kemarin (Awaiting Review for Ketua Bidang IKP to test approval flow)
        // Bidang IKP (ID: 2)
        $agenda4 = Agenda::create([
            'judul' => 'Koordinasi Layanan Informasi Publik PPID',
            'tanggal' => $yesterday->toDateString(),
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '14:30:00',
            'lokasi' => 'Ruang PPID Kominfo',
            'deskripsi' => 'Rapat rutin membahas pembaruan data berkala pada portal PPID Dinkominfo Kabupaten Banyumas.',
            'kategori' => 'rapat',
            'hak_akses' => [(string)$ikp->id],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => '005/982/2026 Perihal Pembaruan Data PPID',
            'sekretaris_id' => $usersMap['ikp_sekretaris']->id,
        ]);
        
        // Seed completed draft awaiting review
        Notulensi::create([
            'agenda_id' => $agenda4->id,
            'transkrip_raw' => "[DEMO AI TRANSCRIPTION]\n\nPembicara 1 (Sekretaris IKP): Selamat siang rekan-rekan. Rapat PPID hari ini kita akan membahas mengenai update informasi berkala di website.\nPembicara 2 (Staff IKP): Kami sudah mengumpulkan data statistik kunjungan dan update dokumen regulasi terbaru.\nPembicara 3 (Ketua IKP): Pastikan semua dokumen diunggah tepat waktu agar kepatuhan keterbukaan informasi publik kita tetap prima.",
            'ringkasan' => 'Rapat koordinasi rutin PPID Dinkominfo menghasilkan kesepakatan pembaruan data berkala pada portal informasi publik.',
            'pembahasan' => "1. Pembahasan statistik kunjungan portal PPID.\n2. Inventarisasi dokumen regulasi terbaru yang wajib dipublikasikan secara berkala.",
            'keputusan' => "1. Menyetujui pengunggahan data statistik kunjungan triwulan ke-2.\n2. Menetapkan tenggat waktu pembaruan website maksimal hari jumat ini.",
            'kesimpulan' => 'Pembaruan portal PPID berjalan sesuai jadwal untuk mempertahankan predikat keterbukaan informasi utama.',
            'status' => 'menunggu_review',
            'last_edited_by_id' => $usersMap['ikp_sekretaris']->id,
        ]);

        // Rapat 5: Sosialisasi Keamanan Informasi (Today, Lintas Dinas)
        $agenda5 = Agenda::create([
            'judul' => 'Sosialisasi Keamanan Informasi & Anti-Phishing',
            'tanggal' => $today->toDateString(),
            'jam_mulai' => '11:15:00',
            'jam_selesai' => '12:30:00',
            'lokasi' => 'Gedung A (Induk) - Aula Utama Kominfo',
            'deskripsi' => 'Sosialisasi pentingnya kesadaran keamanan informasi siber bagi seluruh ASN lingkungan Pemerintah Kabupaten Banyumas.',
            'kategori' => 'sosialisasi',
            'hak_akses' => ['semua_orang'],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => '005/228/2026 Perihal Sosialisasi Keamanan Informasi',
            'sekretaris_id' => $usersMap['aptika_sekretaris']->id,
        ]);
        Notulensi::create([
            'agenda_id' => $agenda5->id,
            'status' => 'draft',
        ]);

        // Rapat 6: Pelatihan Jurnalistik (Today, Bidang IKP)
        $agenda6 = Agenda::create([
            'judul' => 'Pelatihan Jurnalistik & Penulisan Rilis Berita',
            'tanggal' => $today->toDateString(),
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '14:30:00',
            'lokasi' => 'Gedung B (Pelayanan) - Ruang Bidang IKP',
            'deskripsi' => 'Pelatihan teknis penulisan artikel berita rilis publikasi Pemkab bagi staff humas.',
            'kategori' => 'pelatihan',
            'hak_akses' => [(string)$ikp->id],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => null,
            'sekretaris_id' => $usersMap['ikp_sekretaris']->id,
        ]);
        Notulensi::create([
            'agenda_id' => $agenda6->id,
            'status' => 'draft',
        ]);

        // Rapat 7: Bimtek Metadata Statistik (Tomorrow, Bidang Statistik)
        $agenda7 = Agenda::create([
            'judul' => 'Bimbingan Teknis Metadata Statistik Sektoral',
            'tanggal' => $tomorrow->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'lokasi' => 'Gedung A (Induk) - Ruang Rapat Kartini',
            'deskripsi' => 'Pengisian metadata statistik sektoral daerah terintegrasi portal satu data Indonesia.',
            'kategori' => 'pelatihan',
            'hak_akses' => [(string)$statistik->id],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => '005/765/2026 Undangan Bimtek Metadata',
            'sekretaris_id' => $usersMap['statistik_sekretaris']->id,
        ]);
        Notulensi::create([
            'agenda_id' => $agenda7->id,
            'status' => 'draft',
        ]);

        // Rapat 8: Rapat Pengamanan Siber SPBE (Tomorrow, Bidang Aptika)
        $agenda8 = Agenda::create([
            'judul' => 'Koordinasi Rutin Pengamanan Siber SPBE',
            'tanggal' => $tomorrow->toDateString(),
            'jam_mulai' => '13:30:00',
            'jam_selesai' => '15:00:00',
            'lokasi' => 'Gedung B (Pelayanan) - Ruang Server TIK',
            'deskripsi' => 'Evaluasi celah keamanan sistem informasi SPBE Pemkab Banyumas triwulan pertama.',
            'kategori' => 'rapat',
            'hak_akses' => [(string)$aptika->id],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => null,
            'sekretaris_id' => $usersMap['aptika_sekretaris']->id,
        ]);
        Notulensi::create([
            'agenda_id' => $agenda8->id,
            'status' => 'draft',
        ]);

        // Rapat 9: Kegiatan Lainnya Infografis (Yesterday, Bidang Statistik)
        $agenda9 = Agenda::create([
            'judul' => 'Penyusunan Publikasi Infografis Banyumas Dalam Angka',
            'tanggal' => $yesterday->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'lokasi' => 'Gedung A (Induk) - Ruang Rapat Kepala Dinas',
            'deskripsi' => 'Rapat finalisasi data statistik sektoral dan perancangan desain visual infografis Banyumas.',
            'kategori' => 'kegiatan_lainnya',
            'hak_akses' => [(string)$statistik->id],
            'butuh_presensi' => true,
            'nomor_surat_dasar' => null,
            'sekretaris_id' => $usersMap['statistik_sekretaris']->id,
        ]);
        Notulensi::create([
            'agenda_id' => $agenda9->id,
            'status' => 'draft',
        ]);
    }
}
