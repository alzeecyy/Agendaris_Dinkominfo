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
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk mengedit notulensi ini.');
        }

        $notulensi = $agenda->notulensi;
        if ($notulensi && $notulensi->status === 'disahkan') {
            return redirect()->route('notulensi.review', $agenda->id)
                ->with('warning', 'Notulensi telah disahkan dan tidak dapat diubah lagi.');
        }

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
            'audio' => 'required|file|mimes:mp3,wav,m4a,ogg,webm,aac,flac|max:40960',
        ], [
            'audio.required' => 'Silakan pilih berkas audio rapat terlebih dahulu.',
            'audio.mimes' => 'Format berkas audio harus berupa MP3, WAV, M4A, OGG, WEBM, AAC, atau FLAC.',
            'audio.max' => 'Ukuran berkas audio maksimal adalah 40 MB per berkas.',
        ]);

        $notulensi = $agenda->notulensi;
        if ($notulensi && $notulensi->status === 'disahkan') {
            return back()->with('error', 'Akses ditolak. Notulensi telah disahkan dan tidak dapat diubah lagi.');
        }

        if (!$notulensi) {
            $notulensi = Notulensi::create([
                'agenda_id' => $agenda->id,
                'status' => 'draft',
            ]);
        }

        // Block upload during active transcription
        if ($notulensi->is_transcribing) {
            return back()->with('error', 'Tidak dapat mengunggah berkas saat proses transkripsi AI sedang berjalan.');
        }

        // Block upload if notulensi is under review
        if ($notulensi->status === 'menunggu_review') {
            return back()->with('error', 'Tidak dapat mengunggah berkas saat notulensi sedang dalam proses review pimpinan.');
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
     * Uses atomic locking to prevent race condition from double-click or concurrent requests.
     */
    public function processAudio(Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk memproses audio.');
        }

        $notulensi = $agenda->notulensi;
        if ($notulensi && $notulensi->status === 'disahkan') {
            return back()->with('error', 'Akses ditolak. Notulensi telah disahkan dan tidak dapat diubah lagi.');
        }

        if (!$notulensi || empty($notulensi->audio_files)) {
            return back()->with('error', 'Silakan unggah minimal 1 berkas audio rapat terlebih dahulu.');
        }

        // --- Fix #3: Atomic locking to prevent race condition (double-click / concurrent requests) ---
        $dispatched = \Illuminate\Support\Facades\DB::transaction(function () use ($notulensi, $user) {
            // Lock the notulensi row for update to prevent concurrent reads
            $locked = Notulensi::where('id', $notulensi->id)->lockForUpdate()->first();

            if (!$locked) {
                return false;
            }

            // If already transcribing, reject duplicate dispatch
            if ($locked->is_transcribing) {
                return false;
            }

            // Validate audio files still exist on disk before dispatching
            $audioFiles = $locked->audio_files ?? [];
            $hasValidAudio = false;
            foreach ($audioFiles as $audioItem) {
                $audioPath = $audioItem['path'] ?? null;
                if ($audioPath && Storage::disk('public')->exists($audioPath)) {
                    $hasValidAudio = true;
                    break;
                }
            }

            if (!$hasValidAudio) {
                return 'no_audio';
            }

            // Atomically set is_transcribing = true within the transaction
            $locked->update([
                'is_transcribing' => true,
                'transkrip_error' => null,
            ]);

            return true;
        });

        if ($dispatched === false) {
            return back()->with('error', 'Proses transkripsi AI sedang berjalan. Mohon tunggu sejenak...');
        }

        if ($dispatched === 'no_audio') {
            return back()->with('error', 'Berkas audio tidak ditemukan di server. Silakan unggah ulang berkas audio rapat.');
        }

        @set_time_limit(0);

        // Dispatch background job for AI transcription (outside transaction to avoid long lock)
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

        if ($notulensi->status === 'disahkan') {
            return back()->with('error', 'Akses ditolak. Notulensi telah disahkan dan tidak dapat diubah lagi.');
        }

        // Block delete during active transcription to prevent missing files mid-job
        if ($notulensi->is_transcribing) {
            return back()->with('error', 'Tidak dapat menghapus berkas saat proses transkripsi AI sedang berjalan.');
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

        if (in_array($notulensi->status, ['menunggu_review', 'disahkan'])) {
            return back()->with('error', 'Akses ditolak. Notulensi sedang dalam proses review atau telah disahkan dan tidak dapat diubah.');
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
     * Uses atomic state transition to prevent double-submit or submit from invalid state.
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

        // Validate state: only draft can be submitted for review
        if ($notulensi->status !== 'draft') {
            if ($notulensi->status === 'menunggu_review') {
                return back()->with('error', 'Notulensi sudah diajukan untuk review. Menunggu keputusan pimpinan.');
            }
            return back()->with('error', 'Akses ditolak. Notulensi telah disahkan dan tidak dapat diubah lagi.');
        }

        // Block submit while AI is still transcribing
        if ($notulensi->is_transcribing) {
            return back()->with('error', 'Tidak dapat mengajukan notulensi saat proses transkripsi AI masih berjalan.');
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

        // Atomic state transition with DB transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($agenda, $notulensi, $validated, $user) {
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
        });

        return redirect()->route('agenda.show', $agenda->id)
            ->with('success', 'Notulensi berhasil diajukan untuk persetujuan pimpinan.');
    }

    /**
     * Show review page for Ketua.
     */
    public function review(Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->hasAccessToAgenda($agenda)) {
            $prevUrl = url()->previous();
            if (empty($prevUrl) || $prevUrl === url()->current()) {
                return redirect()->route('agenda.today')->with('warning', 'Akses ditolak. Anda tidak terdaftar sebagai peserta dalam rapat ini.');
            }
            return redirect()->back()->with('warning', 'Akses ditolak. Anda tidak terdaftar sebagai peserta dalam rapat ini.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi || !in_array($notulensi->status, ['menunggu_review', 'disahkan'])) {
            return redirect()->route('agenda.show', $agenda->id)
                ->with('error', 'Notulensi belum tersedia.');
        }

        // Verify if user is the authorized secretary
        $isSecretaryOfAgenda = $user->isSecretaryOfAgenda($agenda);
        // Verify that user is the authorized Ketua (Master or Bidang)
        $isApprover = $user->isApproverOfAgenda($agenda);

        if ($notulensi->status === 'menunggu_review' && !$isApprover && !$isSecretaryOfAgenda) {
            abort(403, 'Akses ditolak. Notulensi sedang dalam proses peninjauan oleh pimpinan.');
        }

        $approverInfo = $this->getApproverSignatureInfo($agenda, $notulensi);

        return view('notulensi.review', compact('agenda', 'notulensi', 'isApprover', 'approverInfo'));
    }

    /**
     * Approve and sign off minutes (status = disahkan).
     * Uses lockForUpdate to prevent concurrent approval by two tabs/users.
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

        // Atomic approval with row lock to prevent concurrent approval race condition
        $approved = \Illuminate\Support\Facades\DB::transaction(function () use ($agenda, $user, $tandaTangan) {
            $notulensi = Notulensi::where('agenda_id', $agenda->id)->lockForUpdate()->first();

            if (!$notulensi || $notulensi->status !== 'menunggu_review') {
                return false;
            }

            $notulensi->update([
                'status' => 'disahkan',
                'catatan_revisi' => null,
                'approver_id' => $user->id,
                'tanda_tangan_approver' => $tandaTangan,
            ]);

            return true;
        });

        if (!$approved) {
            return back()->with('error', 'Notulensi tidak dapat disahkan. Status notulensi mungkin sudah berubah.');
        }

        return redirect()->route('notulensi.review', $agenda->id)
            ->with('success', 'Notulensi rapat berhasil disahkan dengan tanda tangan digital Pimpinan.');
    }

    /**
     * Reject and request revision for minutes.
     * Uses lockForUpdate to prevent concurrent revision/approval race condition.
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

        // Atomic revision with row lock to prevent concurrent approval/revision race condition
        $revised = \Illuminate\Support\Facades\DB::transaction(function () use ($agenda, $validated) {
            $notulensi = Notulensi::where('agenda_id', $agenda->id)->lockForUpdate()->first();

            if (!$notulensi || $notulensi->status !== 'menunggu_review') {
                return false;
            }

            $notulensi->update([
                'status' => 'draft',
                'catatan_revisi' => $validated['catatan_revisi'],
                'approver_id' => null,
                'tanda_tangan_approver' => null,
            ]);

            return true;
        });

        if (!$revised) {
            return back()->with('error', 'Notulensi tidak dapat dikembalikan. Status notulensi mungkin sudah berubah.');
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

        $notulensi = $agenda->notulensi;
        if ($notulensi && in_array($notulensi->status, ['menunggu_review', 'disahkan'])) {
            return back()->with('error', 'Akses ditolak. Notulensi sedang dalam proses review atau telah disahkan dan data peserta eksternal tidak dapat diubah.');
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

        $notulensi = $agenda->notulensi;
        if ($notulensi && in_array($notulensi->status, ['menunggu_review', 'disahkan'])) {
            return back()->with('error', 'Akses ditolak. Notulensi sedang dalam proses review atau telah disahkan dan data peserta eksternal tidak dapat dihapus.');
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

        if (!$user->hasAccessToAgenda($agenda)) {
            abort(403);
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi || $notulensi->status !== 'disahkan') {
            abort(400, 'Dokumen belum disahkan.');
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
                'jabatan' => $emp->jabatan,
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
            $belum = $total - ($hadir + $izin + $sakit);

            $recap[] = (object) [
                'bidang_nama' => $bid->nama,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
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

        if (!$user->hasAccessToAgenda($agenda)) {
            abort(403);
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi || $notulensi->status !== 'disahkan') {
            abort(400, 'Dokumen belum disahkan.');
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
                'jabatan' => $emp->jabatan,
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
     * Get signature block info (Title, Name, NIP) according to scope.
     */
    private function getApproverSignatureInfo(Agenda $agenda, Notulensi $notulensi)
    {
        $hakAkses = $agenda->hak_akses ?? [];
        $isLintasDinas = in_array('semua_orang', $hakAkses) || count($hakAkses) > 1 || count($hakAkses) === 0;

        $actualApprover = $notulensi->approver;

        if ($isLintasDinas) {
            $jabatan = "Kepala Dinas Komunikasi dan Informatika";
            $subJabatan = "Kabupaten Banyumas";
            if ($actualApprover && $actualApprover->isKetuaMaster()) {
                $name = $actualApprover->name;
                $nip = $actualApprover->nip;
            } else {
                $ketuaMaster = \App\Models\User::where('role', 'ketua_master')->first();
                $name = $ketuaMaster ? $ketuaMaster->name : ($actualApprover ? $actualApprover->name : 'Kepala Dinas');
                $nip = $ketuaMaster ? $ketuaMaster->nip : ($actualApprover ? $actualApprover->nip : '-');
            }
        } else {
            $singleBidangId = $hakAkses[0] ?? null;
            $bidang = $singleBidangId ? \App\Models\Bidang::find($singleBidangId) : null;
            $bidangNama = $bidang ? $bidang->nama : 'Bidang';
            $jabatan = "Kepala " . $bidangNama;
            $subJabatan = "";

            if ($actualApprover && $actualApprover->isKetuaBidang()) {
                $name = $actualApprover->name;
                $nip = $actualApprover->nip;
            } else {
                $ketuaBidangUser = \App\Models\User::where('role', 'ketua_bidang')->where('bidang_id', $singleBidangId)->first();
                $name = $ketuaBidangUser ? $ketuaBidangUser->name : ($actualApprover ? $actualApprover->name : "Kepala " . $bidangNama);
                $nip = $ketuaBidangUser ? $ketuaBidangUser->nip : ($actualApprover ? $actualApprover->nip : '-');
            }
        }

        return (object) [
            'jabatan' => $jabatan,
            'sub_jabatan' => $subJabatan,
            'name' => $name,
            'nip' => $nip,
            'is_lintas_dinas' => $isLintasDinas,
        ];
    }

    /**
     * Regenerate ringkasan and points from raw transcript text via Gemini AI.
     */
    public function regenerate(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $notulensi = $agenda->notulensi;
        if ($notulensi && $notulensi->status === 'disahkan') {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak. Notulensi telah disahkan dan tidak dapat diubah lagi.'], 403);
        }

        $request->validate([
            'transkrip_raw' => 'required|string',
        ]);

        $transcript = $request->input('transkrip_raw');

        // Return early if the transcript is too short to analyze
        if (strlen(trim($transcript)) < 10) {
            return response()->json([
                'status' => 'success',
                'data' => "Catatan / transkrip rapat terlalu singkat untuk dianalisis."
            ]);
        }

        $apiKey = env('GEMINI_API_KEY');
        $llmApiBase = env('LLM_API_BASE');
        $llmModel = env('LLM_MODEL', 'qwen2.5:1.5b');
        $llmApiKey = env('LLM_API_KEY', 'none');

        $promptText = "Role & Task:\n" .
                      "Kamu adalah asisten eksekutif profesional yang bertugas mengolah, merapikan, dan menyusun ulang dokumen/teks mentah dari pengguna menjadi notulensi formal.\n\n" .
                      "Strict Guardrails (Anti-Halusinasi):\n" .
                      "1. Faktual & Setia pada Teks: Hanya gunakan informasi yang secara eksplisit tertulis pada teks sumber. DILARANG MENAMBAHKAN asumsi, inferensi berlebihan, lokasi, nama platform, atau fakta baru yang tidak ada di teks.\n" .
                      "2. Handling Ambiguitas: Jika ada informasi yang ambigu, membingungkan, atau tidak logis pada teks sumber, tuliskan apa adanya atau kategorikan sebagai 'Perlu Klarifikasi'. JANGAN memperbaikinya dengan asumsi sendiri.\n" .
                      "3. Eliminasi OOT: Buang percakapan santai, bercandaan, atau typo tanpa mengubah fakta inti dari poin utama.\n" .
                      "4. No Speculation: Jika sebuah data tidak disebutkan (seperti waktu pasti, nama PIC, atau link), biarkan kosong atau tulis 'Tidak disebutkan'. Jangan menebak.\n" .
                      "5. Verifikasi Istilah Teknis: Jika ada istilah teknis, nama perintah, atau kode khusus, pertahankan sesuai teks asli.\n" .
                      "6. Khusus Transkrip Audio (STT):\n" .
                      "   - Diizinkan memperbaiki kata yang jelas merupakan kesalahan dengar/fonetik (contoh: 'kelala' -> 'kelola', 'tangga' -> 'tanggal').\n" .
                      "   - Namun, jika istilah/nama peran tetap meragukan dan tidak ada padanan konteksnya yang pasti, pertahankan kata aslinya dan masukkan ke dalam 'CATATAN & PERLU KLARIFIKASI'.\n\n" .
                      "Output Formatting Rules:\n" .
                      "1. No Conversational Filler: LANGSUNG tampilkan hasil olahan teks. DILARANG menggunakan kalimat pengantar/pembuka (misal: 'Berikut adalah hasil...') dan DILARANG menggunakan kalimat penutup.\n" .
                      "2. No Emojis: DILARANG menggunakan emoji atau karakter emotikon apa pun di seluruh dokumen demi kebutuhan ekspor PDF.\n\n" .
                      "STRUKTUR OUTPUT MARKDOWN MANDATORI (TANPA EMOJI):\n\n" .
                      "### RINGKASAN EKSEKUTIF RAPAT\n" .
                      "[Tuliskan 1-2 paragraf ringkasan eksekutif yang merangkum keseluruhan isi pembicaraan rapat secara padat, jelas, faktual, tanpa asumsi]\n\n" .
                      "### POIN-POIN PEMBAHASAN UTAMA\n" .
                      "1. **[Judul Topik/Bahasan Utama]**\n" .
                      "   - Penjelasan dan rincian pembahasan yang disampaikan narasumber/peserta.\n" .
                      "2. **[Judul Topik/Bahasan Selanjutnya]**\n" .
                      "   - Penjelasan dan rincian pembahasan lanjutan.\n\n" .
                      "### KEPUTUSAN & TINDAK LANJUT\n" .
                      "1. **[Keputusan/Kesepakatan Pertama]**: Penjelasan rincian keputusan atau langkah konkret yang disepakati.\n" .
                      "2. **[Tindak Lanjut]**: Rencana penanganan atau tugas kelanjutan setelah rapat (jika PIC/waktu tidak disebutkan, tulis 'Tidak disebutkan').\n\n" .
                      "### CATATAN & PERLU KLARIFIKASI\n" .
                      "- [Cantumkan HANYA jika terdapat poin yang ambigu, kontradiktif, atau belum jelas di teks sumber. Jika tidak ada, hilangkan bagian ini]\n\n" .
                      "Berikut teks transkrip percakapan rapat:\n\n" . $transcript;

        // 1. Try Gemini API first (Super Fast 1-2s response, cloud-ready for hosting)
        if ($apiKey) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(25)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey, [
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
                        'temperature' => 0.1,
                        'topP' => 0.2
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text) {
                        return response()->json([
                            'status' => 'success',
                            'data' => trim($text)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini regenerateSummary Exception: ' . $e->getMessage());
            }
        }

        // 2. Fallback to local Qwen AI / Ollama if offline or Gemini fails
        if ($llmApiBase) {
            try {
                $url = rtrim($llmApiBase, '/') . '/chat/completions';
                $llmResponse = \Illuminate\Support\Facades\Http::timeout(60)->withHeaders([
                    'Authorization' => 'Bearer ' . $llmApiKey,
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'model' => $llmModel,
                    'temperature' => 0.1,
                    'top_p' => 0.2,
                    'max_tokens' => 1200,
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
                        return response()->json([
                            'status' => 'success',
                            'data' => trim($text)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Qwen AI regenerateSummary Exception: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Proses analisis AI gagal. Pastikan koneksi internet atau server AI lokal Anda aktif.'
        ], 503);
    }

    /**
     * Refine manual typed text into structured meeting minutes.
     */
    public function refineText(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki wewenang.'], 403);
        }

        $notulensi = $agenda->notulensi;
        if ($notulensi && $notulensi->status === 'disahkan') {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak. Notulensi telah disahkan dan tidak dapat diubah lagi.'], 403);
        }

        $request->validate([
            'teks_raw' => 'required|string',
        ]);

        $textRaw = trim($request->input('teks_raw'));

        if (strlen($textRaw) < 10) {
            return response()->json([
                'status' => 'error',
                'message' => 'Teks catatan mentah terlalu pendek untuk dirapikan.'
            ], 422);
        }

        // Save raw text to notulensi record if exists
        if (!$notulensi) {
            $notulensi = Notulensi::create([
                'agenda_id' => $agenda->id,
                'created_by_id' => $user->id,
                'last_edited_by_id' => $user->id,
                'status' => 'draft',
                'transkrip_raw' => $textRaw,
            ]);
        } else {
            $notulensi->update(['transkrip_raw' => $textRaw]);
        }

        $apiKey = env('GEMINI_API_KEY');
        $llmApiBase = env('LLM_API_BASE', 'http://localhost:11434/v1');
        $llmModel = env('LLM_MODEL', 'qwen2.5:1.5b');
        $llmApiKey = env('LLM_API_KEY', 'none');

        $promptText = "Role & Task:\n" .
                      "Kamu adalah asisten eksekutif profesional yang bertugas mengolah, merapikan, dan menyusun ulang dokumen/teks mentah dari pengguna menjadi notulensi formal.\n\n" .
                      "Strict Guardrails (Anti-Halusinasi):\n" .
                      "1. Faktual & Setia pada Teks: Hanya gunakan informasi yang secara eksplisit tertulis pada teks sumber. DILARANG MENAMBAHKAN asumsi, inferensi berlebihan, lokasi, nama platform, atau fakta baru yang tidak ada di teks.\n" .
                      "2. Handling Ambiguitas: Jika ada informasi yang ambigu, membingungkan, atau tidak logis pada teks sumber, tuliskan apa adanya atau kategorikan sebagai 'Perlu Klarifikasi'. JANGAN memperbaikinya dengan asumsi sendiri.\n" .
                      "3. Eliminasi OOT: Buang percakapan santai, bercandaan, atau typo tanpa mengubah fakta inti dari poin utama.\n" .
                      "4. No Speculation: Jika sebuah data tidak disebutkan (seperti waktu pasti, nama PIC, atau link), biarkan kosong atau tulis 'Tidak disebutkan'. Jangan menebak.\n" .
                      "5. Verifikasi Istilah Teknis: Jika ada istilah teknis, nama perintah, atau kode khusus, pertahankan sesuai teks asli.\n" .
                      "6. Khusus Transkrip Audio (STT):\n" .
                      "   - Diizinkan memperbaiki kata yang jelas merupakan kesalahan dengar/fonetik (contoh: 'kelala' -> 'kelola', 'tangga' -> 'tanggal').\n" .
                      "   - Namun, jika istilah/nama peran tetap meragukan dan tidak ada padanan konteksnya yang pasti, pertahankan kata aslinya dan masukkan ke dalam 'CATATAN & PERLU KLARIFIKASI'.\n\n" .
                      "Output Formatting Rules:\n" .
                      "1. No Conversational Filler: LANGSUNG tampilkan hasil olahan teks. DILARANG menggunakan kalimat pengantar/pembuka (misal: 'Berikut adalah hasil...') dan DILARANG menggunakan kalimat penutup.\n" .
                      "2. No Emojis: DILARANG menggunakan emoji atau karakter emotikon apa pun di seluruh dokumen demi kebutuhan ekspor PDF.\n\n" .
                      "STRUKTUR OUTPUT MARKDOWN MANDATORI (TANPA EMOJI):\n\n" .
                      "### RINGKASAN EKSEKUTIF RAPAT\n" .
                      "[Tuliskan 1-2 paragraf ringkasan eksekutif yang merangkum keseluruhan isi pembicaraan rapat secara padat, jelas, faktual, tanpa asumsi]\n\n" .
                      "### POIN-POIN PEMBAHASAN UTAMA\n" .
                      "1. **[Judul Topik/Bahasan Utama]**\n" .
                      "   - Penjelasan dan rincian pembahasan.\n\n" .
                      "### KEPUTUSAN & TINDAK LANJUT\n" .
                      "1. **[Keputusan/Tindak Lanjut]**: Rincian langkah konkret yang disepakati (jika PIC/waktu tidak disebutkan, tulis 'Tidak disebutkan').\n\n" .
                      "### CATATAN & PERLU KLARIFIKASI\n" .
                      "- [Cantumkan HANYA jika terdapat poin yang ambigu, kontradiktif, atau belum jelas di teks sumber. Jika tidak ada, hilangkan bagian ini]\n\n" .
                      "Berikut catatan mentah rapat:\n\n" . $textRaw;

        // 1. Try Gemini 1.5 Flash API first (Super Fast 1-2s response)
        if ($apiKey) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(25)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey, [
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
                        'temperature' => 0.1,
                        'topP' => 0.2
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text) {
                        return response()->json([
                            'status' => 'success',
                            'data' => trim($text)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini refineText Exception: ' . $e->getMessage());
            }
        }

        // 2. Fallback to local Qwen AI / Ollama if offline or Gemini fails
        if ($llmApiBase) {
            try {
                $url = rtrim($llmApiBase, '/') . '/chat/completions';
                $llmResponse = \Illuminate\Support\Facades\Http::timeout(60)->withHeaders([
                    'Authorization' => 'Bearer ' . $llmApiKey,
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'model' => $llmModel,
                    'temperature' => 0.1,
                    'top_p' => 0.2,
                    'max_tokens' => 1200,
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
                        return response()->json([
                            'status' => 'success',
                            'data' => trim($text)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Qwen AI refineText Exception: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Proses merapikan teks tidak dapat diselesaikan. Pastikan koneksi internet atau server AI lokal Anda aktif.'
        ], 503);
    }
}
