<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\Bidang;
use App\Models\Presensi;
use App\Models\Notulensi;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AgendaController extends Controller
{
    /**
     * Display today's activities & agendas page.
     */
    public function today(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.users.index');
        }

        $todayDate = Carbon::today()->format('Y-m-d');

        $query = Agenda::with(['sekretaris', 'notulensi', 'presensis.user'])
            ->whereDate('tanggal', $todayDate);

        if (!$user->isSekretarisMaster() && !$user->isKetuaMaster() && !$user->isSekretariat()) {
            $query->where(function ($q) use ($user) {
                $q->whereJsonContains('hak_akses', 'semua_orang')
                  ->orWhereJsonContains('hak_akses', (string)$user->bidang_id);
            });
        }

        $agendas = $query->orderBy('jam_mulai', 'asc')->get();

        $nowTime = Carbon::now()->format('H:i:s');

        $ongoingAgendas = $agendas->filter(function($a) use ($nowTime) {
            $start = Carbon::parse($a->jam_mulai)->format('H:i:s');
            $end = Carbon::parse($a->jam_selesai)->format('H:i:s');
            return $nowTime >= $start && $nowTime <= $end;
        });

        $upcomingAgendas = $agendas->filter(function($a) use ($nowTime) {
            $start = Carbon::parse($a->jam_mulai)->format('H:i:s');
            return $nowTime < $start;
        });

        $completedAgendas = $agendas->filter(function($a) use ($nowTime) {
            $end = Carbon::parse($a->jam_selesai)->format('H:i:s');
            return $nowTime > $end;
        });

        return view('agenda.today', compact(
            'agendas',
            'ongoingAgendas',
            'upcomingAgendas',
            'completedAgendas',
            'todayDate'
        ));
    }

    /**
     * Store a newly created agenda in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'judul' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:rapat,sosialisasi,pelatihan,kegiatan_lainnya',
            'nomor_surat_dasar' => 'nullable|string|max:255',
        ];

        // Validate hak_akses depending on role
        if ($user->isSekretarisBidang()) {
            $rules['semua_orang'] = 'nullable|prohibited'; // Bidang Secretary cannot check semua_orang
        } else {
            $rules['semua_orang'] = 'nullable|boolean';
        }
        $rules['bidangs'] = 'required_without:semua_orang|array';
        $rules['bidangs.*'] = 'exists:bidangs,id';

        $validated = $request->validate($rules, [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal agenda wajib diisi.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'lokasi.required' => 'Lokasi kegiatan wajib diisi.',
            'kategori.required' => 'Kategori agenda wajib diisi.',
            'bidangs.required_without' => 'Pilih minimal satu bidang atau centang Semua Orang.',
            'semua_orang.prohibited' => 'Admin Bidang tidak diperbolehkan membuat rapat Lintas Dinas (Semua Orang).',
        ]);

        // Determine hak_akses
        if ($user->isSekretarisBidang()) {
            $bidangs = $request->input('bidangs', []);
            // Enforce own bidang is checked
            if (!in_array((string)$user->bidang_id, array_map('strval', $bidangs))) {
                $bidangs[] = (string)$user->bidang_id;
            }
            // Max 2 bidangs allowed
            if (count($bidangs) > 2) {
                return back()->withErrors(['bidangs' => 'Admin Bidang hanya diperbolehkan memilih bidangnya sendiri dan maksimal 1 bidang tambahan.'])->withInput();
            }
            $hakAkses = $bidangs;
        } else {
            if ($request->has('semua_orang')) {
                $hakAkses = ['semua_orang'];
            } else {
                $hakAkses = $request->input('bidangs', []);
            }
        }

        $butuhPresensi = $request->has('butuh_presensi');

        // Create the agenda
        $agenda = Agenda::create([
            'judul' => $validated['judul'],
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'lokasi' => $validated['lokasi'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'kategori' => $validated['kategori'],
            'hak_akses' => $hakAkses,
            'butuh_presensi' => $butuhPresensi,
            'nomor_surat_dasar' => $validated['nomor_surat_dasar'] ?? null,
            'sekretaris_id' => $user->id,
        ]);

        // Determine participants to attach
        $allowedUsersQuery = \App\Models\User::where('role', '!=', 'admin')->where('active', true);
        if (!in_array('semua_orang', $hakAkses)) {
            $allowedUsersQuery->whereIn('bidang_id', $hakAkses);
        }
        $allowedUserIds = $allowedUsersQuery->pluck('id')->toArray();

        if ($request->has('participants')) {
            $submittedParticipants = array_map('intval', (array) $request->input('participants', []));
            $targetUserIds = array_values(array_intersect($submittedParticipants, $allowedUserIds));
            if (count($targetUserIds) === 0) {
                return back()->withErrors(['bidangs' => 'Pilih minimal 1 peserta rapat yang diundang.'])->withInput();
            }
        } else {
            $targetUserIds = $allowedUserIds;
            if (count($targetUserIds) === 0) {
                return back()->withErrors(['bidangs' => 'Bidang yang dipilih tidak memiliki anggota aktif.'])->withInput();
            }
        }

        $agenda->participants()->sync($targetUserIds);

        // If it needs presensi, automatically initialize an empty notulensi record
        if ($butuhPresensi && $agenda->kategori === 'rapat') {
            Notulensi::create([
                'agenda_id' => $agenda->id,
                'status' => 'draft',
            ]);
        }

        return redirect()->route('dashboard', ['date' => $agenda->tanggal->toDateString()])
            ->with('success', 'Agenda berhasil ditambahkan.');
    }

    /**
     * Display the specified agenda details.
     */
    public function show(Agenda $agenda)
    {
        $user = Auth::user();

        // Check if user has access to this agenda
        if (!$user->hasAccessToAgenda($agenda)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk membuka agenda ini.');
        }

        // Eager load relations for high performance
        $agenda->load(['sekretaris.bidang', 'notulensi', 'externalParticipants', 'participants']);

        // Auto-heal stale is_transcribing status if no audio file exists
        if ($agenda->notulensi && $agenda->notulensi->is_transcribing) {
            $notulensi = $agenda->notulensi;
            $hasAudio = !empty($notulensi->audio_path) || (!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0);
            if (!$hasAudio) {
                $notulensi->update(['is_transcribing' => false]);
            }
        }

        // Get own presensi status
        $ownPresensi = Presensi::where('agenda_id', $agenda->id)
            ->where('user_id', $user->id)
            ->first();

        // Get internal participants using helper (only invited meeting_participants)
        $internalUsers = $agenda->getInternalParticipants();

        // Get actual attendance records
        $attendanceRecords = Presensi::where('agenda_id', $agenda->id)
            ->get()
            ->keyBy('user_id');

        $isExpired = $agenda->isPresensiExpired();

        // Combine user list with their attendance records
        $participants = $internalUsers->map(function ($employee) use ($attendanceRecords, $isExpired) {
            $record = $attendanceRecords->get($employee->id);
            
            $status = $record ? $record->status : 'Belum Absen';
            if ($isExpired && ($status === 'Belum Absen' || !$record)) {
                $status = 'alfa';
            }

            $employee->status_presensi = $status;
            $employee->tanda_tangan = $record ? $record->tanda_tangan : null;
            $employee->keterangan = $record ? $record->keterangan : null;
            return $employee;
        });

        // Calculate attendance recap per bidang
        $recap = [];
        $hakAkses = $agenda->hak_akses;
        $allowedBidangs = [];
        
        if (in_array('semua_orang', $hakAkses)) {
            $allowedBidangs = Bidang::orderBy('nama')->get();
        } else {
            $allowedBidangs = Bidang::whereIn('id', $hakAkses)->orderBy('nama')->get();
        }

        foreach ($allowedBidangs as $bid) {
            $bidangUsers = $participants->filter(fn($p) => $p->bidang_id === $bid->id);
            $total = $bidangUsers->count();
            $hadir = $bidangUsers->filter(fn($p) => $p->status_presensi === 'hadir')->count();
            $izin = $bidangUsers->filter(fn($p) => $p->status_presensi === 'izin')->count();
            $sakit = $bidangUsers->filter(fn($p) => $p->status_presensi === 'sakit')->count();
            $alfa = $bidangUsers->filter(fn($p) => $p->status_presensi === 'alfa')->count();
            $belum = $bidangUsers->filter(fn($p) => $p->status_presensi === 'Belum Absen')->count();

            $recap[] = (object) [
                'bidang_id' => $bid->id,
                'bidang_nama' => $bid->nama,
                'total' => $total,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alfa' => $alfa,
                'belum' => $belum,
            ];
        }

        // Get external participants
        $externalParticipants = $agenda->externalParticipants;

        // Check if user has secretary access to this agenda
        $isSecretaryOfAgenda = $user->isSecretaryOfAgenda($agenda);

        // Check if user is the approver of this agenda's notulensi
        $isApproverOfAgenda = false;
        if ($agenda->notulensi && $agenda->notulensi->status === 'menunggu_review') {
            $isApproverOfAgenda = $user->isApproverOfAgenda($agenda);
        }
        
        $stdLocations = ['Aula Rapat Dinkominfo', 'Ruang Pelatihan', 'Smart Room Graha Satria'];
        $initialTempat = in_array($agenda->lokasi, $stdLocations) ? $agenda->lokasi : 'Lainnya';
        $initialTempatLainnya = $initialTempat === 'Lainnya' ? $agenda->lokasi : '';

        // Load bidangs with active non-admin users for edit modal participant management
        $bidangsWithUsers = Bidang::with(['users' => function($q) {
            $q->where('role', '!=', 'admin')->where('active', true)->orderBy('name');
        }])->orderBy('nama')->get();

        return view('agenda.show', compact(
            'agenda', 
            'ownPresensi', 
            'participants', 
            'recap', 
            'externalParticipants', 
            'isSecretaryOfAgenda',
            'isApproverOfAgenda',
            'initialTempat',
            'initialTempatLainnya',
            'bidangsWithUsers'
        ));
    }

    /**
     * Update the specified agenda in storage.
     */
    public function update(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk mengubah agenda ini.');
        }

        $rules = [
            'judul' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:rapat,sosialisasi,pelatihan,kegiatan_lainnya',
            'nomor_surat_dasar' => 'nullable|string|max:255',
        ];

        // Validate hak_akses depending on role
        if ($user->isSekretarisBidang()) {
            $rules['semua_orang'] = 'nullable|prohibited';
        } else {
            $rules['semua_orang'] = 'nullable|boolean';
        }
        $rules['bidangs'] = 'required_without:semua_orang|array';
        $rules['bidangs.*'] = 'exists:bidangs,id';

        $validated = $request->validate($rules, [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal agenda wajib diisi.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'lokasi.required' => 'Lokasi kegiatan wajib diisi.',
            'kategori.required' => 'Kategori agenda wajib diisi.',
            'bidangs.required_without' => 'Pilih minimal satu bidang atau centang Semua Orang.',
            'semua_orang.prohibited' => 'Admin Bidang tidak diperbolehkan membuat rapat Lintas Dinas (Semua Orang).',
        ]);

        // Determine hak_akses
        if ($user->isSekretarisBidang()) {
            $bidangs = $request->input('bidangs', []);
            // Enforce own bidang is checked
            if (!in_array((string)$user->bidang_id, array_map('strval', $bidangs))) {
                $bidangs[] = (string)$user->bidang_id;
            }
            // Max 2 bidangs allowed
            if (count($bidangs) > 2) {
                return back()->withErrors(['bidangs' => 'Admin Bidang hanya diperbolehkan memilih bidangnya sendiri dan maksimal 1 bidang tambahan.'])->withInput();
            }
            $newHakAkses = $bidangs;
        } else {
            if ($request->has('semua_orang')) {
                $newHakAkses = ['semua_orang'];
            } else {
                $newHakAkses = $request->input('bidangs', []);
            }
        }

        $butuhPresensi = $request->has('butuh_presensi');

        // Update the agenda
        $agenda->update([
            'judul' => $validated['judul'],
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'lokasi' => $validated['lokasi'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'kategori' => $validated['kategori'],
            'hak_akses' => $newHakAkses,
            'butuh_presensi' => $butuhPresensi,
            'nomor_surat_dasar' => $validated['nomor_surat_dasar'] ?? null,
        ]);

        // Sync participants
        $allowedUsersQuery = \App\Models\User::where('role', '!=', 'admin')->where('active', true);
        if (!in_array('semua_orang', $newHakAkses)) {
            $allowedUsersQuery->whereIn('bidang_id', $newHakAkses);
        }
        $allowedUserIds = $allowedUsersQuery->pluck('id')->toArray();

        if ($request->has('participants')) {
            $submittedParticipants = array_map('intval', (array) $request->input('participants', []));
            $targetUserIds = array_values(array_intersect($submittedParticipants, $allowedUserIds));
            if (count($targetUserIds) === 0) {
                return back()->withErrors(['bidangs' => 'Pilih minimal 1 peserta rapat yang diundang.'])->withInput();
            }
        } else {
            $targetUserIds = $allowedUserIds;
            if (count($targetUserIds) === 0) {
                return back()->withErrors(['bidangs' => 'Bidang yang dipilih tidak memiliki anggota aktif.'])->withInput();
            }
        }

        $agenda->participants()->sync($targetUserIds);

        // Cleanup presensi of uninvited users if any
        Presensi::where('agenda_id', $agenda->id)
            ->whereNotIn('user_id', $targetUserIds)
            ->delete();

        // Manage notulensi instantiation based on updated toggles
        if ($butuhPresensi && $agenda->kategori === 'rapat') {
            if (!$agenda->notulensi) {
                Notulensi::create([
                    'agenda_id' => $agenda->id,
                    'status' => 'draft',
                ]);
            }
        } else {
            if ($agenda->notulensi && $agenda->notulensi->status === 'draft') {
                $agenda->notulensi->delete();
            }
        }

        return redirect()->route('agenda.show', $agenda->id)
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    /**
     * Quick update for agenda's nomor_surat_dasar.
     */
    public function updateNomorSurat(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda) && !$user->isAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk mengubah nomor surat agenda ini.');
        }

        $validated = $request->validate([
            'nomor_surat_dasar' => 'nullable|string|max:255',
        ], [
            'nomor_surat_dasar.max' => 'Nomor surat dasar maksimal 255 karakter.',
        ]);

        $agenda->update([
            'nomor_surat_dasar' => $validated['nomor_surat_dasar'],
        ]);

        return back()->with('success', 'Nomor surat dasar berhasil diperbarui.');
    }

    /**
     * Remove the specified agenda from storage.
     */
    public function destroy(Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk menghapus agenda ini.');
        }

        $dateStr = $agenda->tanggal->toDateString();
        $agenda->delete();

        return redirect()->route('dashboard', ['date' => $dateStr])
            ->with('success', 'Agenda berhasil dihapus.');
    }
}
