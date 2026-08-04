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

        if (!$user->canViewAgendaToday()) {
            return redirect()->route('dashboard');
        }

        $todayDate = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $nowTime = Carbon::now('Asia/Jakarta')->format('H:i:s');

        $rawAgendas = Agenda::with(['sekretaris.bidang', 'notulensi', 'presensis.user'])
            ->whereDate('tanggal', $todayDate)
            ->get();

        // Rule: On Card Agenda Hari Ini, ALL ROLES ONLY see agendas where they are explicitly invited, creator, or target bidang.
        $agendas = $rawAgendas->filter(fn($a) => $user->isInvitedToAgenda($a))
        ->sortBy(function($a) use ($nowTime) {
                $start = Carbon::parse($a->jam_mulai)->format('H:i:s');
                $end = Carbon::parse($a->jam_selesai)->format('H:i:s');

                if ($nowTime >= $start && $nowTime <= $end) {
                    $priority = 1; // Ongoing (Berlangsung)
                } elseif ($nowTime < $start) {
                    $priority = 2; // Upcoming (Mendatang)
                } else {
                    $priority = 3; // Completed (Selesai)
                }

                return sprintf('%d_%s_%d', $priority, $start, $a->id);
            })
            ->values();

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

        // Auto-merge mandatory bidangs into request if NOT semua_orang
        if (!$request->has('semua_orang')) {
            $reqBidangs = array_map('strval', (array)$request->input('bidangs', []));
            if ($user->bidang_id && !in_array((string)$user->bidang_id, $reqBidangs)) {
                $reqBidangs[] = (string)$user->bidang_id;
            }
            if ($user->isSekretariatScope()) {
                $sekId = \App\Models\Bidang::where('singkatan', 'Sekretariat')->orWhere('nama', 'Sekretariat')->value('id');
                if ($sekId && !in_array((string)$sekId, $reqBidangs)) {
                    $reqBidangs[] = (string)$sekId;
                }
            }
            $request->merge(['bidangs' => array_values(array_unique($reqBidangs))]);
        }

        $validated = $request->validate($rules, [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal agenda wajib diisi.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'lokasi.required' => 'Lokasi kegiatan wajib diisi.',
            'kategori.required' => 'Kategori agenda wajib diisi.',
            'bidangs.required_without' => 'Pilih minimal satu bidang atau centang Semua Orang.',
        ]);

        // Determine hak_akses
        if ($request->has('semua_orang')) {
            $hakAkses = ['semua_orang'];
        } else if ($user->isSekretarisBidang() && !$user->isSekretariatScope()) {
            $bidangs = array_map('strval', $request->input('bidangs', []));
            // Enforce own bidang is checked
            if (!in_array((string)$user->bidang_id, $bidangs)) {
                $bidangs[] = (string)$user->bidang_id;
            }
            // Max 3 bidangs allowed for standard Bidangs (excluding Kadin & Bidang Sekretariat) when NOT semua_orang
            $sekretariatId = \App\Models\Bidang::where('singkatan', 'Sekretariat')->orWhere('nama', 'Sekretariat')->value('id');

            $actualBidangCount = count(array_filter($bidangs, function($b) use ($sekretariatId) {
                return is_numeric($b) && (string)$b !== (string)$sekretariatId;
            }));

            if ($actualBidangCount > 3) {
                return back()->withErrors(['bidangs' => 'Admin Bidang hanya diperbolehkan memilih maksimal 3 bidang/subbag (di luar Sekretariat & Kadin).'])->withInput();
            }
            $hakAkses = array_values(array_unique($bidangs));
        } else {
            $bidangs = array_map('strval', (array)$request->input('bidangs', []));
            $hakAkses = array_values(array_unique($bidangs));
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
        $numericHakAkses = array_values(array_filter($hakAkses, 'is_numeric'));
        $hasKadin = in_array('kadin', $hakAkses);

        $allowedUsersQuery = \App\Models\User::where('role', '!=', 'admin')->where('active', true);
        if (!in_array('semua_orang', $hakAkses)) {
            $allowedUsersQuery->where(function($q) use ($numericHakAkses, $hasKadin) {
                if (!empty($numericHakAkses)) {
                    $q->whereIn('bidang_id', $numericHakAkses);
                }
                if ($hasKadin) {
                    $q->orWhere('role', 'ketua_master');
                }
            });
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

        // Enforce mandatory creator (Notulis)
        $creatorId = Auth::id();
        if ($creatorId && !in_array($creatorId, $targetUserIds)) {
            $targetUserIds[] = $creatorId;
        }

        // Validate exactly 1 Admin per invited bidang/subbag
        if (!in_array('semua_orang', $hakAkses)) {
            $invitedNumericBidangs = array_values(array_filter($hakAkses, 'is_numeric'));
            foreach ($invitedNumericBidangs as $bidId) {
                $unitAdminUsers = \App\Models\User::where('active', true)
                    ->where('bidang_id', $bidId)
                    ->whereIn('role', ['sekretaris_bidang', 'sekretaris_master'])
                    ->pluck('id')
                    ->toArray();

                if (!empty($unitAdminUsers)) {
                    $selectedAdminsInUnit = array_intersect($unitAdminUsers, $targetUserIds);
                    $adminCount = count($selectedAdminsInUnit);
                    if ($adminCount < 1) {
                        $bidName = \App\Models\Bidang::where('id', $bidId)->value('nama');
                        return back()->withErrors(['participants' => 'Pilih minimal 1 Admin dari unit yang diundang (' . ($bidName ?: 'Unit Kerja') . ').'])->withInput();
                    }
                }
            }
        }

        $agenda->participants()->sync($targetUserIds);

        // Automatically initialize an empty notulensi record for all rapat agendas
        if ($agenda->kategori === 'rapat') {
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
            abort(403, 'Akses ditolak. Anda tidak terdaftar sebagai peserta dalam rapat ini.');
        }

        // Eager load relations for high performance
        $agenda->load(['sekretaris.bidang', 'notulensi', 'externalParticipants', 'participants']);

        // Ensure rapat category always has an initialized notulensi record
        if ($agenda->kategori === 'rapat' && !$agenda->notulensi) {
            $notulensi = Notulensi::create([
                'agenda_id' => $agenda->id,
                'status' => 'draft',
            ]);
            $agenda->setRelation('notulensi', $notulensi);
        }

        // Auto-heal stale is_transcribing status based on queue job lifecycle and audio availability
        if ($agenda->notulensi && $agenda->notulensi->is_transcribing) {
            $notulensi = $agenda->notulensi;
            $hasAudio = !empty($notulensi->audio_path) || (!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0);
            
            // Check if a queue job is currently pending in the jobs table for this notulensi
            $jobPendingInQueue = false;
            try {
                $jobPendingInQueue = \Illuminate\Support\Facades\DB::table('jobs')
                    ->where('payload', 'like', '%ProcessMeetingAudio%')
                    ->where(function ($q) use ($notulensi) {
                        $q->where('payload', 'like', '%"id";i:' . $notulensi->id . '%')
                          ->orWhere('payload', 'like', '%"id";s:' . strlen((string)$notulensi->id) . ':"' . $notulensi->id . '"%');
                    })
                    ->exists();
            } catch (\Exception $e) {
                $jobPendingInQueue = false;
            }

            // Only heal if no job is pending in queue
            if (!$jobPendingInQueue) {
                // Refresh to get latest data — job may have already saved results
                $notulensi->refresh();
                $alreadyHasResult = !empty(trim($notulensi->transkrip_raw ?? ''));

                if ($alreadyHasResult) {
                    // Job succeeded — clear flag, NO error message
                    $notulensi->update([
                        'is_transcribing' => false,
                        'transkrip_error' => null,
                    ]);
                } else if (!$hasAudio) {
                    $notulensi->update([
                        'is_transcribing' => false,
                        'transkrip_error' => null,
                    ]);
                } else {
                    // No result and no job in queue — genuinely failed
                    $notulensi->update([
                        'is_transcribing' => false,
                        'transkrip_error' => $notulensi->transkrip_error ?: 'Proses transkripsi terhenti. Silakan coba lagi.',
                    ]);
                }
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
        $hakAkses = (array) ($agenda->hak_akses ?? []);
        $allowedBidangs = [];
        
        if (in_array('semua_orang', $hakAkses)) {
            $allowedBidangs = Bidang::orderBy('nama')->get();
        } else {
            $numericHakAkses = array_values(array_filter($hakAkses, 'is_numeric'));
            $allowedBidangs = Bidang::whereIn('id', $numericHakAkses)->orderBy('nama')->get();
        }

        // 1. Include Kepala Dinas card if invited or in hak_akses
        $kadinUsers = $participants->filter(fn($p) => $p->role === 'ketua_master' || !$p->bidang_id);
        if ($kadinUsers->isNotEmpty() || in_array('kadin', $hakAkses)) {
            $total = $kadinUsers->count();
            $hadir = $kadinUsers->filter(fn($p) => $p->status_presensi === 'hadir')->count();
            $izin = $kadinUsers->filter(fn($p) => $p->status_presensi === 'izin')->count();
            $sakit = $kadinUsers->filter(fn($p) => $p->status_presensi === 'sakit')->count();
            $alfa = $kadinUsers->filter(fn($p) => $p->status_presensi === 'alfa')->count();
            $belum = $kadinUsers->filter(fn($p) => $p->status_presensi === 'Belum Absen' || !$p->status_presensi)->count();

            $recap[] = (object) [
                'bidang_id' => 0,
                'bidang_nama' => 'Kepala Dinas',
                'total' => $total,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alfa' => $alfa,
                'belum' => $belum,
            ];
        }

        // 2. Include each allowed Bidang card
        foreach ($allowedBidangs as $bid) {
            $bidangUsers = $participants->filter(fn($p) => (int)$p->bidang_id === (int)$bid->id && $p->role !== 'ketua_master');
            $total = $bidangUsers->count();
            $hadir = $bidangUsers->filter(fn($p) => $p->status_presensi === 'hadir')->count();
            $izin = $bidangUsers->filter(fn($p) => $p->status_presensi === 'izin')->count();
            $sakit = $bidangUsers->filter(fn($p) => $p->status_presensi === 'sakit')->count();
            $alfa = $bidangUsers->filter(fn($p) => $p->status_presensi === 'alfa')->count();
            $belum = $bidangUsers->filter(fn($p) => $p->status_presensi === 'Belum Absen' || !$p->status_presensi)->count();

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

        $sekretariatId = Bidang::where('singkatan', 'Sekretariat')->orWhere('nama', 'Sekretariat')->value('id');
        $sekretariatSubbagIds = Bidang::whereIn('singkatan', ['Subbag Umum & Kepegawaian', 'Subbag Keuangan', 'Subbag Perencanaan'])
            ->orWhereIn('nama', ['Subbag Umum dan Kepegawaian', 'Subbag Keuangan', 'Subbag Perencanaan'])
            ->pluck('id')->toArray();
        $kadinUser = \App\Models\User::where('role', 'ketua_master')->first();
        $kadinUserId = $kadinUser ? $kadinUser->id : null;
        $kadinUserData = $kadinUser ? [
            'id' => $kadinUser->id,
            'name' => $kadinUser->name,
            'nip' => $kadinUser->nip,
            'jabatan' => $kadinUser->jabatan,
            'role' => $kadinUser->role,
        ] : null;

        $bidangsUserData = $bidangsWithUsers->map(function($b) {
            return [
                'id' => $b->id,
                'nama' => $b->nama,
                'singkatan' => $b->singkatan,
                'users' => $b->users->map(function($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'nip' => $u->nip,
                        'jabatan' => $u->jabatan,
                        'role' => $u->role,
                        'bidang_id' => $u->bidang_id,
                    ];
                })
            ];
        });

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
            'bidangsWithUsers',
            'sekretariatId',
            'sekretariatSubbagIds',
            'kadinUserId',
            'kadinUserData',
            'bidangsUserData'
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

        // --- Fix #2: Backend validation - lock butuh_presensi & kategori when notulensi is beyond draft ---
        $notulensi = $agenda->notulensi;
        $notulensiLocked = $notulensi && in_array($notulensi->status, ['menunggu_review', 'disahkan']);

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
        if (!$request->has('semua_orang')) {
            $reqBidangs = array_map('strval', (array)$request->input('bidangs', []));
            if ($user->isSekretarisBidang() && !$user->isSekretariatScope()) {
                if ($user->bidang_id && !in_array((string)$user->bidang_id, $reqBidangs)) {
                    $reqBidangs[] = (string)$user->bidang_id;
                }
            }
            $request->merge(['bidangs' => array_values(array_unique($reqBidangs))]);
        }

        $validated = $request->validate($rules, [
            'judul.required' => 'Judul agenda wajib diisi.',
            'tanggal.required' => 'Tanggal agenda wajib diisi.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'lokasi.required' => 'Lokasi kegiatan wajib diisi.',
            'kategori.required' => 'Kategori agenda wajib diisi.',
            'bidangs.required_without' => 'Pilih minimal satu bidang atau centang Semua Orang.',
        ]);

        // Reject changes to kategori/butuh_presensi if notulensi workflow is locked
        if ($notulensiLocked) {
            $requestedKategori = $validated['kategori'];
            $requestedButuhPresensi = $request->has('butuh_presensi');

            if ($requestedKategori !== $agenda->kategori) {
                return back()->withErrors(['kategori' => 'Kategori agenda tidak dapat diubah karena notulensi sudah dalam proses review atau telah disahkan.'])->withInput();
            }
            if ($requestedButuhPresensi !== (bool) $agenda->butuh_presensi) {
                return back()->withErrors(['butuh_presensi' => 'Pengaturan presensi tidak dapat diubah karena notulensi sudah dalam proses review atau telah disahkan.'])->withInput();
            }
        }

        // Determine hak_akses
        if ($request->has('semua_orang')) {
            $newHakAkses = ['semua_orang'];
        } else if ($user->isSekretarisBidang() && !$user->isSekretariatScope()) {
            $bidangs = array_map('strval', (array)$request->input('bidangs', []));
            if (!in_array((string)$user->bidang_id, $bidangs)) {
                $bidangs[] = (string)$user->bidang_id;
            }
            $sekretariatId = \App\Models\Bidang::where('singkatan', 'Sekretariat')->orWhere('nama', 'Sekretariat')->value('id');

            $actualBidangCount = count(array_filter($bidangs, function($b) use ($sekretariatId) {
                return is_numeric($b) && (string)$b !== (string)$sekretariatId;
            }));

            if ($actualBidangCount > 3) {
                return back()->withErrors(['bidangs' => 'Admin Bidang hanya diperbolehkan memilih maksimal 3 bidang/subbag (di luar Sekretariat & Kadin).'])->withInput();
            }
            $newHakAkses = array_values(array_unique($bidangs));
        } else {
            $bidangs = array_map('strval', (array)$request->input('bidangs', []));
            $newHakAkses = array_values(array_unique($bidangs));
        }

        $butuhPresensi = $request->has('butuh_presensi');

        // Pre-compute target participant list for validation before entering transaction
        $numericNewHakAkses = array_values(array_filter($newHakAkses, 'is_numeric'));
        $hasKadinNew = in_array('kadin', $newHakAkses);

        $allowedUsersQuery = \App\Models\User::where('role', '!=', 'admin')->where('active', true);
        if (!in_array('semua_orang', $newHakAkses)) {
            $allowedUsersQuery->where(function($q) use ($numericNewHakAkses, $hasKadinNew) {
                if (!empty($numericNewHakAkses)) {
                    $q->whereIn('bidang_id', $numericNewHakAkses);
                }
                if ($hasKadinNew) {
                    $q->orWhere('role', 'ketua_master');
                }
            });
        }
        $allowedUserIds = $allowedUsersQuery->pluck('id')->toArray();

        if ($request->has('participants')) {
            $submittedParticipants = array_map('intval', (array) $request->input('participants', []));
            $targetUserIds = array_values(array_intersect($submittedParticipants, $allowedUserIds));
            if (count($targetUserIds) === 0) {
                return back()->withErrors(['participants' => 'Pilih minimal 1 peserta rapat yang diundang.'])->withInput();
            }
        } else {
            $targetUserIds = $allowedUserIds;
            if (count($targetUserIds) === 0) {
                return back()->withErrors(['bidangs' => 'Bidang yang dipilih tidak memiliki anggota aktif.'])->withInput();
            }
        }

        // Validate at least 1 Admin per invited bidang/subbag
        if (!in_array('semua_orang', $newHakAkses)) {
            $invitedNumericBidangs = array_values(array_filter($newHakAkses, 'is_numeric'));
            foreach ($invitedNumericBidangs as $bidId) {
                $unitAdminUsers = \App\Models\User::where('active', true)
                    ->where('bidang_id', $bidId)
                    ->whereIn('role', ['sekretaris_bidang', 'sekretaris_master'])
                    ->pluck('id')
                    ->toArray();

                if (!empty($unitAdminUsers)) {
                    $selectedAdminsInUnit = array_intersect($unitAdminUsers, $targetUserIds);
                    $adminCount = count($selectedAdminsInUnit);
                    if ($adminCount < 1) {
                        $bidName = \App\Models\Bidang::where('id', $bidId)->value('nama');
                        return back()->withErrors(['participants' => 'Pilih minimal 1 Admin dari unit yang diundang (' . ($bidName ?: 'Unit Kerja') . ').'])->withInput();
                    }
                }
            }
        }

        // Wrap the entire update in a transaction for atomicity
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $agenda, $validated, $newHakAkses, $butuhPresensi, $notulensiLocked, $targetUserIds) {
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

            $agenda->participants()->sync($targetUserIds);

            // --- Fix #1: Only delete presensi records for uninvited users that have NO recorded attendance ---
            // Preserve presensi records with status hadir/izin/sakit to protect attendance history
            Presensi::where('agenda_id', $agenda->id)
                ->whereNotIn('user_id', $targetUserIds)
                ->whereNull('status')
                ->delete();

            // For users removed from invite list who DO have attendance records,
            // keep their presensi data intact for historical integrity.
            // Only remove records that are completely empty (no status recorded yet).
            $emptyPresensiIds = Presensi::where('agenda_id', $agenda->id)
                ->whereNotIn('user_id', $targetUserIds)
                ->whereNotIn('status', ['hadir', 'izin', 'sakit', 'alfa'])
                ->pluck('id');
            if ($emptyPresensiIds->isNotEmpty()) {
                Presensi::whereIn('id', $emptyPresensiIds)->delete();
            }

            // Manage notulensi instantiation based on category
            // Only allow notulensi creation/deletion when workflow is not locked
            if (!$notulensiLocked) {
                if ($agenda->kategori === 'rapat') {
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
            }
        });

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

        // Clean up physical audio files from storage
        if ($agenda->notulensi) {
            $audioFiles = $agenda->notulensi->audio_files ?? [];
            foreach ($audioFiles as $file) {
                if (!empty($file['path'])) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file['path']);
                }
            }
            if (!empty($agenda->notulensi->audio_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($agenda->notulensi->audio_path);
            }
        }

        // Clean up physical signature files from storage
        foreach ($agenda->presensis as $presensi) {
            if (!empty($presensi->tanda_tangan)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($presensi->tanda_tangan);
            }
        }

        $agenda->delete();

        return redirect()->route('dashboard', ['date' => $dateStr])
            ->with('success', 'Agenda berhasil dihapus.');
    }
}
