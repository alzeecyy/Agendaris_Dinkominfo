<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\Notulensi;
use App\Models\AgendaExternalParticipant;
use App\Models\Presensi;
use App\Jobs\ProcessMeetingAudio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class NotulensiController extends Controller
{
    /**
     * Show the editor page for a meeting minutes.
     */
    public function edit(Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            if ($agenda->notulensi) {
                return redirect()->route('notulensi.review', $agenda->id)->with('info', 'Anda hanya memiliki akses Mode Baca (View Only) untuk notulensi bidang ini.');
            }
            return redirect()->route('agenda.show', $agenda->id)->with('error', 'Akses ditolak. Notulensi rapat bidang ini hanya dapat dikelola oleh sekretaris bidang penyelenggara.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi) {
            $notulensi = Notulensi::create([
                'agenda_id' => $agenda->id,
                'status' => 'draft',
            ]);
        }

        // Get external guests
        $externalParticipants = $agenda->externalParticipants;

        return view('notulensi.edit', compact('agenda', 'notulensi', 'externalParticipants'));
    }

    /**
     * Upload meeting audio recording.
     */
    public function uploadAudio(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk mengunggah berkas.');
        }

        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a,ogg,webm,aac,flac|max:102400',
        ], [
            'audio.required' => 'Silakan pilih berkas audio rapat terlebih dahulu.',
            'audio.mimes' => 'Format berkas audio harus berupa MP3, WAV, M4A, OGG, WEBM, AAC, atau FLAC.',
            'audio.max' => 'Ukuran berkas audio maksimal adalah 100 MB.',
        ]);

        $notulensi = $agenda->notulensi;
        if (!$notulensi) {
            $notulensi = Notulensi::create([
                'agenda_id' => $agenda->id,
                'status' => 'draft',
            ]);
        }

        // Handle up to 3 audio files
        $audioFiles = $notulensi->audio_files ?? [];
        if (count($audioFiles) >= 3) {
            return back()->with('error', 'Gagal mengunggah. Maksimal 3 berkas audio rapat tercapai.');
        }

        // Save new audio file
        $file = $request->file('audio');
        $path = $file->store('audio', 'public');

        // Add to array
        $audioFiles[] = [
            'name' => $file->getClientOriginalName(),
            'path' => $path,
        ];

        // Also set legacy fields to the most recent one for compatibility
        $notulensi->update([
            'audio_path' => $path,
            'audio_name' => $file->getClientOriginalName(),
            'audio_files' => $audioFiles,
            'status' => 'draft',
            'is_transcribing' => false, // Do not auto-transcribe immediately so user can upload more files
            'transkrip_error' => null,
        ]);

        return back()->with('success', 'Berkas audio berhasil diunggah (' . count($audioFiles) . '/3). Silakan tambah berkas audio lain atau tekan tombol "Proses Transkripsi AI" saat siap.');
    }

    /**
     * Trigger AI audio transcription process manually.
     */
    public function processAudio(Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk memproses audio.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi || empty($notulensi->audio_files)) {
            return back()->with('error', 'Silakan unggah minimal 1 berkas audio rapat terlebih dahulu.');
        }

        @set_time_limit(0);

        // Set is_transcribing to true
        $notulensi->update([
            'is_transcribing' => true,
            'transkrip_error' => null,
        ]);

        // Dispatch background job for AI transcription
        $audioFiles = $notulensi->audio_files ?? [];
        $lastFile = !empty($audioFiles) ? end($audioFiles) : null;
        $path = is_array($lastFile) ? ($lastFile['path'] ?? $notulensi->audio_path) : $notulensi->audio_path;

        ProcessMeetingAudio::dispatch($notulensi, $user->id, $path);

        return back()->with('success', 'Proses transkripsi AI telah dimulai. Mohon tunggu sejenak...');
    }

    /**
     * Check current transcription status via AJAX (no page refresh lag).
     */
    public function checkStatus(Agenda $agenda)
    {
        $notulensi = $agenda->notulensi;
        return response()->json([
            'is_transcribing' => $notulensi ? (bool)$notulensi->is_transcribing : false,
            'transkrip_error' => $notulensi->transkrip_error ?? null,
            'has_transcript' => !empty($notulensi->transkrip_raw),
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Delete a specific meeting audio recording.
     */
    public function deleteAudio(Agenda $agenda, $index)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk menghapus berkas.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi) {
            return back()->with('error', 'Notulensi tidak ditemukan.');
        }

        $audioFiles = $notulensi->audio_files ?? [];
        if (!isset($audioFiles[$index])) {
            return back()->with('error', 'Berkas audio tidak ditemukan.');
        }

        // Delete from storage
        $deletedFile = $audioFiles[$index];
        Storage::disk('public')->delete($deletedFile['path']);

        // Remove from array and rekey
        unset($audioFiles[$index]);
        $audioFiles = array_values($audioFiles);

        // Update model (and clear legacy fields if list is empty)
        $updateData = [
            'audio_files' => $audioFiles,
        ];

        if (empty($audioFiles)) {
            $updateData['audio_path'] = null;
            $updateData['audio_name'] = null;
            $updateData['transkrip_raw'] = null;
            $updateData['ringkasan'] = null;
            $updateData['pembahasan'] = null;
            $updateData['keputusan'] = null;
            $updateData['kesimpulan'] = null;
        } else {
            // Point legacy fields to the last remaining file
            $lastFile = end($audioFiles);
            $updateData['audio_path'] = $lastFile['path'];
            $updateData['audio_name'] = $lastFile['name'];
        }

        $notulensi->update($updateData);

        return back()->with('success', 'Berkas audio berhasil dihapus.');
    }

    /**
     * Save draft of minutes contents (transkrip, ringkasan, dll).
     */
    public function saveDraft(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Akses ditolak.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi) {
            abort(404, 'Notulensi tidak ditemukan.');
        }

        $validated = $request->validate([
            'judul' => 'nullable|string|max:255',
            'nomor_surat_dasar' => 'nullable|string|max:255',
            'transkrip_raw' => 'nullable|string',
            'ringkasan' => 'nullable|string',
            'pembahasan' => 'nullable|string',
            'keputusan' => 'nullable|string',
            'kesimpulan' => 'nullable|string',
            'pembahasan_title' => 'nullable|string',
            'keputusan_title' => 'nullable|string',
        ]);

        $agendaUpdates = [];
        if (!empty($validated['judul'])) {
            $agendaUpdates['judul'] = $validated['judul'];
        }
        if (isset($validated['nomor_surat_dasar'])) {
            $agendaUpdates['nomor_surat_dasar'] = $validated['nomor_surat_dasar'];
        }
        if (!empty($agendaUpdates)) {
            $agenda->update($agendaUpdates);
        }

        $ringkasanClean = isset($validated['ringkasan']) ? trim(preg_replace('/```(?:markdown)?/i', '', $validated['ringkasan'])) : null;

        $notulensi->update([
            'transkrip_raw' => $validated['transkrip_raw'] ?? null,
            'ringkasan' => $ringkasanClean,
            'pembahasan' => $validated['pembahasan'] ?? null,
            'keputusan' => $validated['keputusan'] ?? null,
            'kesimpulan' => $validated['kesimpulan'] ?? null,
            'pembahasan_title' => $validated['pembahasan_title'] ?? null,
            'keputusan_title' => $validated['keputusan_title'] ?? null,
            'last_edited_by_id' => $user->id,
        ]);

        return back()->with('success', 'Draft notulensi berhasil disimpan.');
    }

    /**
     * Submit minutes draft for Ketua's review and approval.
     */
    public function submitForReview(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Akses ditolak.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi) {
            return back()->with('error', 'Notulensi belum dibuat.');
        }

        // Save current inputs first & validate judul & nomor_surat_dasar
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'nomor_surat_dasar' => 'required|string|max:255',
            'transkrip_raw' => 'nullable|string',
            'ringkasan' => 'nullable|string',
            'pembahasan' => 'nullable|string',
            'keputusan' => 'nullable|string',
            'kesimpulan' => 'nullable|string',
            'pembahasan_title' => 'nullable|string',
            'keputusan_title' => 'nullable|string',
        ], [
            'judul.required' => 'Nama / Judul Kegiatan Rapat wajib diisi.',
            'nomor_surat_dasar.required' => 'Nomor Surat Pelaksanaan wajib diisi sebelum mengajukan notulensi.',
        ]);

        $agenda->update([
            'judul' => $validated['judul'],
            'nomor_surat_dasar' => $validated['nomor_surat_dasar'],
        ]);

        $ringkasanClean = isset($validated['ringkasan']) ? trim(preg_replace('/```(?:markdown)?/i', '', $validated['ringkasan'])) : null;

        $notulensi->update([
            'transkrip_raw' => $validated['transkrip_raw'] ?? null,
            'ringkasan' => $ringkasanClean,
            'pembahasan' => $validated['pembahasan'] ?? null,
            'keputusan' => $validated['keputusan'] ?? null,
            'kesimpulan' => $validated['kesimpulan'] ?? null,
            'pembahasan_title' => $validated['pembahasan_title'] ?? null,
            'keputusan_title' => $validated['keputusan_title'] ?? null,
            'status' => 'menunggu_review',
            'last_edited_by_id' => $user->id,
        ]);

        return redirect()->route('agenda.show', $agenda->id)
            ->with('success', 'Notulensi berhasil diajukan untuk persetujuan pimpinan.');
    }

    /**
     * Show review page for Ketua.
     */
    public function review(Agenda $agenda)
    {
        $user = Auth::user();

        $notulensi = $agenda->notulensi;
        if (!$notulensi || !in_array($notulensi->status, ['menunggu_review', 'disahkan'])) {
            return redirect()->route('agenda.show', $agenda->id)
                ->with('error', 'Notulensi belum tersedia.');
        }

        $canView = $user->isKetuaMaster() 
            || $user->isSekretarisMaster() 
            || $notulensi->status === 'disahkan' 
            || $user->hasAccessToAgenda($agenda);

        if (!$canView) {
            $prevUrl = url()->previous();
            if (empty($prevUrl) || $prevUrl === url()->current()) {
                return redirect()->route('agenda.today')->with('warning', 'Akses ditolak. Anda tidak memiliki wewenang untuk membaca notulensi ini.');
            }
            return redirect()->back()->with('warning', 'Akses ditolak. Anda tidak memiliki wewenang untuk membaca notulensi ini.');
        }

        // Verify if user is the authorized secretary
        $isSecretaryOfAgenda = $user->isSecretaryOfAgenda($agenda);
        // Verify that user is the authorized Ketua (Master or Bidang)
        $isApprover = $user->isApproverOfAgenda($agenda);

        $approverInfo = $this->getApproverSignatureInfo($agenda, $notulensi);

        return view('notulensi.review', compact('agenda', 'notulensi', 'isApprover', 'approverInfo'));
    }

    /**
     * Approve and sign off minutes (status = disahkan).
     */
    public function approve(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isApproverOfAgenda($agenda)) {
            return back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'tanda_tangan_approver' => 'nullable|string',
        ]);

        $tandaTangan = $request->input('tanda_tangan_approver');

        $notulensi = $agenda->notulensi;
        if ($notulensi) {
            $notulensi->update([
                'status' => 'disahkan',
                'catatan_revisi' => null,
                'approver_id' => $user->id,
                'tanda_tangan_approver' => $tandaTangan,
            ]);
        }

        return redirect()->route('notulensi.review', $agenda->id)
            ->with('success', 'Notulensi rapat berhasil disahkan dengan tanda tangan digital Pimpinan.');
    }

    /**
     * Reject and request revision for minutes.
     */
    public function requestRevision(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isApproverOfAgenda($agenda)) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'catatan_revisi' => 'required|string',
        ], [
            'catatan_revisi.required' => 'Catatan revisi wajib diisi jika Anda menolak draf.',
        ]);

        $notulensi = $agenda->notulensi;
        if ($notulensi) {
            $notulensi->update([
                'status' => 'draft',
                'catatan_revisi' => $validated['catatan_revisi'],
            ]);
        }

        return redirect()->route('agenda.show', $agenda->id)
            ->with('warning', 'Notulensi dikembalikan ke sekretaris untuk direvisi.');
    }

    /**
     * Add external guest participant.
     */
    public function addExternal(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
        ]);

        AgendaExternalParticipant::create([
            'agenda_id' => $agenda->id,
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'instansi' => $validated['instansi'],
        ]);

        return back()->with('success', 'Peserta eksternal berhasil ditambahkan.');
    }

    /**
     * Delete external guest participant.
     */
    public function deleteExternal(AgendaExternalParticipant $participant)
    {
        $user = Auth::user();
        $agenda = $participant->agenda;

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Akses ditolak.');
        }

        $participant->delete();

        return back()->with('success', 'Peserta eksternal berhasil dihapus.');
    }

    /**
     * Export minutes document to PDF format using Dompdf.
     */
    public function exportPdf(Agenda $agenda)
    {
        $user = Auth::user();

        $notulensi = $agenda->notulensi;
        if (!$notulensi || $notulensi->status !== 'disahkan') {
            abort(400, 'Dokumen belum disahkan.');
        }

        $canView = $user->isKetuaMaster() 
            || $user->isSekretarisMaster() 
            || $notulensi->status === 'disahkan' 
            || $user->hasAccessToAgenda($agenda);

        if (!$canView) {
            abort(403, 'Akses ditolak.');
        }

        // Get internal attendees (only invited meeting_participants)
        $hakAkses = $agenda->hak_akses;
        $internalUsers = $agenda->getInternalParticipants();
        $attendanceRecords = Presensi::where('agenda_id', $agenda->id)->get()->keyBy('user_id');

        $attendees = [];
        
        $isExpired = $agenda->isPresensiExpired();
        foreach ($internalUsers as $emp) {
            $record = $attendanceRecords->get($emp->id);
            $status = $record ? $record->status : 'Belum Absen';
            if ($isExpired && ($status === 'Belum Absen' || !$record)) {
                $status = 'alfa';
            }
            
            $statusLabel = 'Belum Absen';
            if ($status === 'hadir') $statusLabel = 'Hadir';
            if ($status === 'izin') $statusLabel = 'Izin';
            if ($status === 'sakit') $statusLabel = 'Sakit';
            if ($status === 'alfa') $statusLabel = 'Alfa';

            $attendees[] = (object) [
                'nama' => $emp->name,
                'nip' => $emp->nip,
                'jabatan' => $this->formatCleanJabatan($emp->jabatan),
                'bidang' => $emp->bidang->singkatan ?? 'Dinas',
                'status' => $statusLabel,
                'tanda_tangan' => $record ? $record->tanda_tangan : null,
                'keterangan' => $record ? $record->keterangan : null,
            ];
        }

        // Add external participants
        foreach ($agenda->externalParticipants as $ext) {
            $attendees[] = (object) [
                'nama' => $ext->nama,
                'nip' => '-',
                'jabatan' => $ext->jabatan,
                'bidang' => $ext->instansi . ' (Eksternal)',
                'status' => 'Hadir',
                'tanda_tangan' => null,
                'keterangan' => null,
            ];
        }

        // Attendance recap per bidang
        $recap = [];
        $allowedBidangs = in_array('semua_orang', $hakAkses) 
            ? \App\Models\Bidang::orderBy('nama')->get()
            : \App\Models\Bidang::whereIn('id', $hakAkses)->orderBy('nama')->get();

        foreach ($allowedBidangs as $bid) {
            $total = $internalUsers->filter(fn($p) => $p->bidang_id === $bid->id)->count();
            $hadir = $internalUsers->filter(fn($p) => $p->bidang_id === $bid->id && ($attendanceRecords->has($p->id) && $attendanceRecords[$p->id]->status === 'hadir'))->count();
            $izin = $internalUsers->filter(fn($p) => $p->bidang_id === $bid->id && ($attendanceRecords->has($p->id) && $attendanceRecords[$p->id]->status === 'izin'))->count();
            $sakit = $internalUsers->filter(fn($p) => $p->bidang_id === $bid->id && ($attendanceRecords->has($p->id) && $attendanceRecords[$p->id]->status === 'sakit'))->count();
            $belum = $internalUsers->filter(fn($p) => $p->bidang_id === $bid->id && (!$attendanceRecords->has($p->id) || !in_array($attendanceRecords[$p->id]->status, ['hadir', 'izin', 'sakit'])))->count();

            $recap[] = (object) [
                'bidang_nama' => $bid->nama,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alfa' => $belum,
                'belum' => $belum,
            ];
        }

        // Convert Banyumas logo to base64 for PDF rendering compatibility
        $logoPath = public_path('images/logo-banyumas.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        // Get designated approver signature info according to scope rule
        $approverInfo = $this->getApproverSignatureInfo($agenda, $notulensi);

        $pdf = Pdf::loadView('notulensi.export_pdf', compact('agenda', 'notulensi', 'attendees', 'recap', 'logoBase64', 'approverInfo'));
        
        return $pdf->download('notulensi-rapat-' . $agenda->id . '.pdf');
    }

    /**
     * Export minutes document to Word (DOCX / HTML compat) format.
     */
    public function exportDocx(Agenda $agenda)
    {
        $user = Auth::user();

        $notulensi = $agenda->notulensi;
        if (!$notulensi || $notulensi->status !== 'disahkan') {
            abort(400, 'Dokumen belum disahkan.');
        }

        $canView = $user->isKetuaMaster() 
            || $user->isSekretarisMaster() 
            || $notulensi->status === 'disahkan' 
            || $user->hasAccessToAgenda($agenda);

        if (!$canView) {
            abort(403, 'Akses ditolak.');
        }

        // Get internal attendees (only invited meeting_participants)
        $hakAkses = $agenda->hak_akses;
        $internalUsers = $agenda->getInternalParticipants();
        $attendanceRecords = Presensi::where('agenda_id', $agenda->id)->get()->keyBy('user_id');

        $attendees = [];
        $isExpired = $agenda->isPresensiExpired();
        
        foreach ($internalUsers as $emp) {
            $record = $attendanceRecords->get($emp->id);
            $status = $record ? $record->status : 'Belum Absen';
            if ($isExpired && ($status === 'Belum Absen' || !$record)) {
                $status = 'alfa';
            }
            
            $statusLabel = 'Belum Absen';
            if ($status === 'hadir') $statusLabel = 'Hadir';
            if ($status === 'izin') $statusLabel = 'Izin';
            if ($status === 'sakit') $statusLabel = 'Sakit';
            if ($status === 'alfa') $statusLabel = 'Alfa';
            
            $attendees[] = (object) [
                'nama' => $emp->name,
                'nip' => $emp->nip,
                'jabatan' => $this->formatCleanJabatan($emp->jabatan),
                'bidang' => $emp->bidang->singkatan ?? 'Dinas',
                'status' => $statusLabel,
                'tanda_tangan' => $record ? $record->tanda_tangan : null,
                'keterangan' => $record ? $record->keterangan : null,
            ];
        }

        foreach ($agenda->externalParticipants as $ext) {
            $attendees[] = (object) [
                'nama' => $ext->nama,
                'nip' => '-',
                'jabatan' => $ext->jabatan,
                'bidang' => $ext->instansi . ' (Eksternal)',
                'status' => 'Hadir',
                'tanda_tangan' => null,
                'keterangan' => null,
            ];
        }

        // Get designated approver signature info according to scope rule
        $approverInfo = $this->getApproverSignatureInfo($agenda, $notulensi);

        // Generate clean document layout
        $viewContent = view('notulensi.export_docx', compact('agenda', 'notulensi', 'attendees', 'approverInfo'))->render();
        
        $filename = 'notulensi-rapat-' . $agenda->id . '.doc';

        return response($viewContent)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Get signature block info (Title, Name, NIP) according to scope & actual approver.
     */
    private function getApproverSignatureInfo(Agenda $agenda, Notulensi $notulensi)
    {
        $actualApprover = $notulensi->approver;

        if ($actualApprover) {
            return (object) [
                'jabatan' => $actualApprover->jabatan ?? 'Pejabat Pengesah',
                'sub_jabatan' => '',
                'name' => $actualApprover->name,
                'nip' => $actualApprover->nip,
                'is_lintas_dinas' => false,
            ];
        }

        // Preview before approval: Resolve expected Kasubag / Kabid / Sekdin
        $creator = $agenda->sekretaris;
        $creatorBidangId = $creator?->bidang_id;

        if ($creatorBidangId) {
            $ketuaUser = \App\Models\User::where('role', 'ketua_bidang')
                ->where('bidang_id', $creatorBidangId)
                ->first();

            if ($ketuaUser) {
                return (object) [
                    'jabatan' => $ketuaUser->jabatan,
                    'sub_jabatan' => '',
                    'name' => $ketuaUser->name,
                    'nip' => $ketuaUser->nip,
                    'is_lintas_dinas' => false,
                ];
            }
        }

        // Fallback to Sekdin / Kadis
        $sekdin = \App\Models\User::where('role', 'sekretaris_master')->first();
        if ($sekdin) {
            return (object) [
                'jabatan' => 'Sekretaris Dinas',
                'sub_jabatan' => 'Sekretariat Dinkominfo',
                'name' => $sekdin->name,
                'nip' => $sekdin->nip,
                'is_lintas_dinas' => false,
            ];
        }

        $kadis = \App\Models\User::where('role', 'ketua_master')->first();
        return (object) [
            'jabatan' => 'Kepala Dinas Komunikasi dan Informatika',
            'sub_jabatan' => 'Kabupaten Banyumas',
            'name' => $kadis ? $kadis->name : 'Kepala Dinas',
            'nip' => $kadis ? $kadis->nip : '-',
            'is_lintas_dinas' => true,
        ];
    }

    /**
     * Regenerate ringkasan and points from raw transcript text via Gemini AI.
     */
    /**
     * Regenerate ringkasan and points from raw transcript text via Gemini AI.
     */
    public function regenerate(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'transkrip_raw' => 'required|string',
        ]);

        $transcript = trim($request->input('transkrip_raw'));

        // Return early if transcript is empty or too short to analyze
        if (strlen($transcript) < 15) {
            return response()->json([
                'status' => 'error',
                'message' => 'Teks catatan rapat terlalu singkat (minimal 15 karakter) untuk dianalisis.'
            ], 422);
        }

        $apiKey = env('GEMINI_API_KEY');

        $promptText = "Role & Task:\n" .
                      "Kamu adalah asisten eksekutif profesional yang bertugas mengolah, merapikan, dan menyusun ulang catatan/transkrip mentah dari pengguna menjadi dokumen notulensi rapat formal.\n\n" .
                      "Strict Guardrails (Aturan Anti-Halusinasi & Faktual):\n" .
                      "1. Faktual & Setia pada Teks: Hanya gunakan informasi yang secara eksplisit tertulis pada teks sumber. DILARANG MENAMBAHKAN asumsi, inferensi berlebihan, lokasi, nama platform, atau fakta baru yang tidak ada di teks.\n" .
                      "2. Penanganan Istilah & Ambiguitas:\n" .
                      "   - Jika ada informasi yang ambigu, membingungkan, atau tidak logis pada teks sumber (misal: \"rapat via gdrive\"), tuliskan apa adanya di bagian khusus atau kategorikan sebagai \"CATATAN & PERLU KLARIFIKASI\". JANGAN mencoba memperbaikinya dengan asumsi sendiri.\n" .
                      "   - Khusus Transkrip Audio / Speech-to-Text (STT): Kamu diizinkan membetulkan kata-kata salah dengar/typo fonetik yang jelas dan berisiko rendah (contoh: \"kelala\" menjadi \"kelola\", \"tangga\" menjadi \"tanggal\"). Namun, jika istilah teknis atau nama peran tetap meragukan, pertahankan kata aslinya dan masukkan ke bagian clarification.\n" .
                      "3. Eliminasi OOT: Buang percakapan santai, bercandaan, atau typo yang tidak relevan tanpa mengubah fakta inti dari poin utama.\n" .
                      "4. Handling Data Kosong: Jika data seperti PIC, tenggat waktu, atau tanggal tidak disebutkan di teks sumber, tuliskan \"Tidak disebutkan\" secara eksplisit. Jangan menebak.\n\n" .
                      "Output Formatting Guidelines (Standar Ekspor PDF):\n" .
                      "1. NO CONVERSATIONAL PREFACE/OUTRO: Langsung berikan hasil akhir berupa dokumen Markdown. DILARANG menggunakan kalimat pembuka/pengantar (seperti \"Berikut adalah hasil...\", \"Ini notulensinya...\") maupun kalimat penutup/salam.\n" .
                      "2. NO EMOJIS: Dilarang keras menggunakan emoji atau simbol emotikon apa pun dalam seluruh isi teks demi kebutuhan ekspor PDF.\n" .
                      "3. Struktur Dokumen: Gunakan hirarki heading Markdown berikut secara konsisten:\n" .
                      "   # RINGKASAN EKSEKUTIF RAPAT\n" .
                      "   [Tuliskan 1-2 paragraf ringkasan eksekutif secara padat, faktual, dan profesional]\n\n" .
                      "   # POIN-POIN PEMBAHASAN UTAMA\n" .
                      "   1. **[Judul Topik/Bahasan Utama]**\n" .
                      "      - Rincian pembahasan dan penjelasan yang disampaikan narasumber/peserta.\n\n" .
                      "   # KEPUTUSAN & TINDAK LANJUT\n" .
                      "   1. **[Keputusan/Kesepakatan Pertama]**: Penjelasan rincian keputusan atau langkah konkret yang disepakati. PIC: [Nama/Tidak disebutkan], Tenggat Waktu: [Tanggal/Tidak disebutkan].\n\n" .
                      "   # CATATAN & PERLU KLARIFIKASI\n" .
                      "   - [Tuliskan istilah ambigu/meragukan atau ketik 'Tidak ada' jika semua data sudah jelas].\n\n" .
                      "Berikut teks sumber (transkrip / catatan mentah rapat):\n\n" . $transcript;

        if ($apiKey) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(45)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                     'text' => $promptText
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.0
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text) {
                        $cleanText = trim(preg_replace('/```(?:markdown)?/i', '', $text));
                        return response()->json([
                            'status' => 'success',
                            'data' => $cleanText
                        ]);
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error('Gemini API HTTP error (' . $response->status() . '): ' . $response->body());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini Exception: ' . $e->getMessage());
            }
        }

        // Fallback 1: try local Ollama/OpenAI-compatible LLM API
        $llmApiBase = env('LLM_API_BASE');
        $llmModel = env('LLM_MODEL', 'qwen2.5:1.5b');
        $llmApiKey = env('LLM_API_KEY', 'none');

        if ($llmApiBase) {
            try {
                $url = rtrim($llmApiBase, '/') . '/chat/completions';
                $llmResponse = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(15)->withHeaders([
                    'Authorization' => 'Bearer ' . $llmApiKey,
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'model' => $llmModel,
                    'temperature' => 0.0,
                    'max_tokens' => 3000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $promptText
                        ]
                    ],
                ]);

                if ($llmResponse->successful()) {
                    $resJson = $llmResponse->json();
                    $text = $resJson['choices'][0]['message']['content'] ?? null;
                    if ($text) {
                        $cleanText = trim(preg_replace('/```(?:markdown)?/i', '', $text));
                        return response()->json([
                            'status' => 'success',
                            'data' => $cleanText
                        ]);
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error('Ollama HTTP error: ' . $llmResponse->status() . ' - ' . $llmResponse->body());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ollama Exception: ' . $e->getMessage());
            }
        }

        // Fallback 2: Local Heuristic Markdown Generator if AI APIs are unavailable
        $lines = array_values(array_filter(array_map('trim', explode("\n", $transcript))));
        $pembahasanItems = [];
        foreach ($lines as $line) {
            if (!empty($line)) {
                $pembahasanItems[] = "- " . $line;
            }
        }

        $fallbackMarkdown = "# RINGKASAN EKSEKUTIF RAPAT\n" .
                            "Notulensi disusun dan dirapikan secara terstruktur dari catatan mentah hasil rapat.\n\n" .
                            "# POIN-POIN PEMBAHASAN UTAMA\n" .
                            (count($pembahasanItems) > 0 ? implode("\n", $pembahasanItems) : "- " . $transcript) . "\n\n" .
                            "# KEPUTUSAN & TINDAK LANJUT\n" .
                            "1. **Tindak Lanjut**: Menindaklanjuti poin-poin pembahasan rapat di atas. PIC: Tidak disebutkan, Tenggat Waktu: Tidak disebutkan.\n\n" .
                            "# CATATAN & PERLU KLARIFIKASI\n" .
                            "- Tidak ada.";

        return response()->json([
            'status' => 'success',
            'data' => $fallbackMarkdown
        ]);
    }

    /**
     * Show official archive of approved notulensi for all bidangs & subbags.
     */
    public function arsipDinas(Request $request)
    {
        $user = Auth::user();
        if ($user->isAdmin() || (!$user->isKetuaMaster() && !$user->isSekretarisMaster())) {
            return redirect()->route('dashboard')->with('warning', 'Akses ditolak. Halaman Arsip Notulensi Dinas hanya dapat diakses oleh Kepala Dinas dan Sekretaris Dinas.');
        }

        $selectedBidangId = $request->query('bidang_id', 'semua');
        $searchQuery = trim($request->query('search', ''));

        $query = Notulensi::where('status', 'disahkan')
            ->with(['agenda.sekretaris.bidang', 'approver', 'lastEditedBy']);

        // Search filter
        if (!empty($searchQuery)) {
            $query->whereHas('agenda', function($q) use ($searchQuery) {
                $q->where('judul', 'like', "%{$searchQuery}%")
                  ->orWhere('nomor_surat_dasar', 'like', "%{$searchQuery}%")
                  ->orWhere('lokasi', 'like', "%{$searchQuery}%");
            });
        }

        // Bidang filter: Categorized strictly by organizer bidang (sekretaris.bidang_id)
        if ($selectedBidangId === 'lintas_dinas') {
            $query->whereHas('agenda', function($q) {
                $q->whereJsonContains('hak_akses', 'semua_orang');
            });
        } elseif ($selectedBidangId !== 'semua' && is_numeric($selectedBidangId)) {
            $query->whereHas('agenda', function($q) use ($selectedBidangId) {
                $q->whereHas('sekretaris', function($sq) use ($selectedBidangId) {
                    $sq->where('bidang_id', $selectedBidangId);
                });
            });
        }

        $notulensiList = $query->orderBy('updated_at', 'desc')->get();

        // Get all active Bidangs for filter tabs
        $bidangs = \App\Models\Bidang::orderBy('nama', 'asc')->get();

        // Calculate exact counts for each Bidang tab based on organizer bidang
        $allApproved = Notulensi::where('status', 'disahkan')->with('agenda.sekretaris')->get();
        $bidangCounts = [
            'semua' => $allApproved->count(),
            'lintas_dinas' => $allApproved->filter(fn($n) => $n->agenda && in_array('semua_orang', (array)($n->agenda->hak_akses ?? [])))->count(),
        ];

        foreach ($bidangs as $b) {
            $bidangCounts[$b->id] = $allApproved->filter(function($n) use ($b) {
                if (!$n->agenda) return false;
                $creatorBidangId = $n->agenda->sekretaris?->bidang_id;
                return (string)$creatorBidangId === (string)$b->id;
            })->count();
        }

        return view('notulensi.arsip', compact('notulensiList', 'bidangs', 'selectedBidangId', 'bidangCounts', 'searchQuery'));
    }

    /**
     * Clean redundant "Bidang ..." suffix from employee position title.
     */
    private function formatCleanJabatan(?string $rawJabatan): string
    {
        if (empty($rawJabatan)) return '-';
        if (preg_match('/^Kepala\s+Bidang\b/i', $rawJabatan)) {
            return 'Kepala Bidang';
        }
        if (preg_match('/^Kepala\s+Dinas\b/i', $rawJabatan)) {
            return 'Kepala Dinas';
        }
        return trim(preg_replace('/\s+(Bidang|Dinas)\s+.*$/i', '', $rawJabatan));
    }
}
