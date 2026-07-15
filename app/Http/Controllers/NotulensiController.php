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
        $hakAkses = $agenda->hak_akses;

        // Check if user has secretary access to this agenda
        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk mengedit notulensi ini.');
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
        $hakAkses = $agenda->hak_akses;

        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk mengunggah berkas.');
        }

        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a,mpga|max:20480', // max 20MB
        ], [
            'audio.required' => 'File audio wajib diunggah.',
            'audio.mimes' => 'Format file harus berupa mp3, wav, atau m4a.',
            'audio.max' => 'Ukuran file audio maksimal adalah 20MB.',
        ]);

        $notulensi = $agenda->notulensi;
        if (!$notulensi) {
            $notulensi = Notulensi::create([
                'agenda_id' => $agenda->id,
                'status' => 'draft',
                'audio_files' => [],
            ]);
        }

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
            'is_transcribing' => true,
            'transkrip_error' => null, // Reset any previous error
        ]);

        // Dispatch background job for AI transcription for the specific file
        ProcessMeetingAudio::dispatch($notulensi, $user->id, $path);

        return back()->with('success', 'Berkas audio berhasil diunggah. AI sedang melakukan analisis transkripsi rapat di latar belakang.');
    }

    /**
     * Delete a specific meeting audio recording.
     */
    public function deleteAudio(Agenda $agenda, $index)
    {
        $user = Auth::user();
        $hakAkses = $agenda->hak_akses;

        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
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
        $hakAkses = $agenda->hak_akses;

        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
            return back()->with('error', 'Akses ditolak.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi) {
            abort(404, 'Notulensi tidak ditemukan.');
        }

        $validated = $request->validate([
            'transkrip_raw' => 'nullable|string',
            'ringkasan' => 'nullable|string',
            'pembahasan' => 'nullable|string',
            'keputusan' => 'nullable|string',
            'kesimpulan' => 'nullable|string',
            'pembahasan_title' => 'nullable|string',
            'keputusan_title' => 'nullable|string',
        ]);

        $notulensi->update([
            'transkrip_raw' => $validated['transkrip_raw'] ?? null,
            'ringkasan' => $validated['ringkasan'] ?? null,
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
        $hakAkses = $agenda->hak_akses;

        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
            return back()->with('error', 'Akses ditolak.');
        }

        // Validate Dasar Surat
        if (empty($agenda->nomor_surat_dasar)) {
            return redirect()->route('agenda.show', $agenda->id)
                ->with('error', 'Gagal mengajukan. Nomor Surat Dasar Pelaksanaan wajib diisi terlebih dahulu pada edit agenda.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi) {
            return back()->with('error', 'Notulensi belum dibuat.');
        }

        // Save current inputs first
        $validated = $request->validate([
            'transkrip_raw' => 'nullable|string',
            'ringkasan' => 'nullable|string',
            'pembahasan' => 'nullable|string',
            'keputusan' => 'nullable|string',
            'kesimpulan' => 'nullable|string',
            'pembahasan_title' => 'nullable|string',
            'keputusan_title' => 'nullable|string',
        ]);

        $notulensi->update([
            'transkrip_raw' => $validated['transkrip_raw'] ?? null,
            'ringkasan' => $validated['ringkasan'] ?? null,
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
        $hakAkses = $agenda->hak_akses;

        // Verify if user is the authorized secretary
        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        // Verify that user is the authorized Ketua (Master or Bidang)
        $isApprover = false;
        if (count($hakAkses) === 1 && !in_array('semua_orang', $hakAkses)) {
            $isApprover = $user->isKetuaBidang() && $user->bidang_id == $hakAkses[0];
        } else {
            $isApprover = $user->isKetuaMaster();
        }

        if (!$isApprover && !$isSecretaryOfAgenda) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk meninjau notulensi ini.');
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi || $notulensi->status !== 'menunggu_review') {
            return redirect()->route('agenda.show', $agenda->id)
                ->with('error', 'Notulensi tidak dalam status menunggu review.');
        }

        return view('notulensi.review', compact('agenda', 'notulensi', 'isApprover'));
    }

    /**
     * Approve and sign off minutes (status = disahkan).
     */
    public function approve(Request $request, Agenda $agenda)
    {
        $user = Auth::user();
        $hakAkses = $agenda->hak_akses;

        $isApprover = false;
        if (count($hakAkses) === 1 && !in_array('semua_orang', $hakAkses)) {
            $isApprover = $user->isKetuaBidang() && $user->bidang_id == $hakAkses[0];
        } else {
            $isApprover = $user->isKetuaMaster();
        }

        if (!$isApprover) {
            return back()->with('error', 'Akses ditolak.');
        }

        $notulensi = $agenda->notulensi;
        if ($notulensi) {
            $notulensi->update([
                'status' => 'disahkan',
                'catatan_revisi' => null,
                'approver_id' => $user->id,
            ]);
        }

        return redirect()->route('agenda.show', $agenda->id)
            ->with('success', 'Notulensi rapat berhasil disahkan.');
    }

    /**
     * Reject and request revision for minutes.
     */
    public function requestRevision(Request $request, Agenda $agenda)
    {
        $user = Auth::user();
        $hakAkses = $agenda->hak_akses;

        $isApprover = false;
        if (count($hakAkses) === 1 && !in_array('semua_orang', $hakAkses)) {
            $isApprover = $user->isKetuaBidang() && $user->bidang_id == $hakAkses[0];
        } else {
            $isApprover = $user->isKetuaMaster();
        }

        if (!$isApprover) {
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
        $hakAkses = $agenda->hak_akses;

        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
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
        $hakAkses = $agenda->hak_akses;

        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
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

        if (!$user->hasAccessToAgenda($agenda)) {
            abort(403);
        }

        $notulensi = $agenda->notulensi;
        if (!$notulensi || $notulensi->status !== 'disahkan') {
            abort(400, 'Dokumen belum disahkan.');
        }

        // Get internal attendees
        $hakAkses = $agenda->hak_akses;
        $participantsQuery = \App\Models\User::where('role', '!=', 'admin')->where('active', true);
        if (!in_array('semua_orang', $hakAkses)) {
            $participantsQuery->whereIn('bidang_id', $hakAkses);
        }
        $internalUsers = $participantsQuery->orderBy('name')->get();
        $attendanceRecords = Presensi::where('agenda_id', $agenda->id)->get()->keyBy('user_id');

        $attendees = [];
        
        // Add internal users
        foreach ($internalUsers as $emp) {
            $record = $attendanceRecords->get($emp->id);
            $status = $record ? $record->status : 'Belum Absen';
            
            $statusLabel = 'Belum Absen';
            if ($status === 'hadir') $statusLabel = 'Hadir';
            if ($status === 'izin') $statusLabel = 'Izin';
            if ($status === 'sakit') $statusLabel = 'Sakit';

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
                'nama' => $ext->name,
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

        $pdf = Pdf::loadView('notulensi.export_pdf', compact('agenda', 'notulensi', 'attendees', 'recap', 'logoBase64'));
        
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

        // Get internal attendees
        $hakAkses = $agenda->hak_akses;
        $participantsQuery = \App\Models\User::where('role', '!=', 'admin')->where('active', true);
        if (!in_array('semua_orang', $hakAkses)) {
            $participantsQuery->whereIn('bidang_id', $hakAkses);
        }
        $internalUsers = $participantsQuery->orderBy('name')->get();
        $attendanceRecords = Presensi::where('agenda_id', $agenda->id)->get()->keyBy('user_id');

        $attendees = [];
        
        foreach ($internalUsers as $emp) {
            $record = $attendanceRecords->get($emp->id);
            $status = $record ? $record->status : 'Belum Absen';
            $statusLabel = $status === 'hadir' ? 'Hadir' : ($status === 'izin' ? 'Izin' : ($status === 'sakit' ? 'Sakit' : 'Belum Absen'));
            
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
                'nama' => $ext->name,
                'nip' => '-',
                'jabatan' => $ext->jabatan,
                'bidang' => $ext->instansi . ' (Eksternal)',
                'status' => 'Hadir',
                'tanda_tangan' => null,
                'keterangan' => null,
            ];
        }

        // Generate clean document layout
        $viewContent = view('notulensi.export_docx', compact('agenda', 'notulensi', 'attendees'))->render();
        
        $filename = 'notulensi-rapat-' . $agenda->id . '.doc';

        return response($viewContent)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Regenerate ringkasan and points from raw transcript text via Gemini AI.
     */
    public function regenerate(Request $request, Agenda $agenda)
    {
        $user = Auth::user();
        $hakAkses = $agenda->hak_akses;

        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'transkrip_raw' => 'required|string',
        ]);

        $transcript = $request->input('transkrip_raw');
        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(45)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                     'text' => "Anda adalah notulis profesional rapat pemerintahan Dinkominfo Banyumas.\n\n" .
                                               "Berikut adalah transkrip rapat:\n\n{$transcript}\n\n" .
                                               "Berdasarkan transkrip di atas, buat 4 elemen notulensi:\n\n" .
                                               "1. RINGKASAN (ringkasan): Tulis 2-4 paragraf executive summary. Berisi tujuan/latar belakang rapat, topik utama, dan hasil akhir. JANGAN menyalin ulang percakapan. Maksimal 10% dari panjang transkrip.\n" .
                                               "2. POIN PEMBAHASAN (pembahasan): Daftar topik yang dibahas, pisahkan tiap poin dengan baris baru, tanpa nomor.\n" .
                                               "3. KEPUTUSAN (keputusan): Daftar keputusan/tindak lanjut yang disepakati, pisahkan dengan baris baru, tanpa nomor.\n" .
                                               "4. KESIMPULAN (kesimpulan): Satu paragraf singkat tentang hasil dan langkah selanjutnya.\n\n" .
                                               "Output HARUS berupa JSON murni tanpa markdown: " .
                                               '{"ringkasan": "...", "pembahasan": "poin 1\npoin 2", "keputusan": "keputusan 1\nkeputusan 2", "kesimpulan": "..."}'
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text) {
                        $data = json_decode($text, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            return response()->json([
                                'status' => 'success',
                                'data' => $data
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // fall through to mock on exception
            }
        }

        // Fallback: try local Ollama/OpenAI-compatible LLM API
        $llmApiBase = env('LLM_API_BASE');
        $llmModel = env('LLM_MODEL', 'qwen2.5:1.5b');
        $llmApiKey = env('LLM_API_KEY', 'none');

        if ($llmApiBase) {
            try {
                $url = rtrim($llmApiBase, '/') . '/chat/completions';
                $llmResponse = \Illuminate\Support\Facades\Http::timeout(90)->withHeaders([
                    'Authorization' => 'Bearer ' . $llmApiKey,
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'model' => $llmModel,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Anda adalah notulis profesional rapat pemerintahan Dinkominfo Banyumas.\n\n" .
                                         "Berikut adalah transkrip rapat:\n\n{$transcript}\n\n" .
                                         "Berdasarkan transkrip di atas, buat 4 elemen notulensi:\n\n" .
                                         "1. RINGKASAN (ringkasan): Tulis 2-4 paragraf executive summary. Berisi tujuan/latar belakang rapat, topik utama, dan hasil akhir. JANGAN menyalin ulang percakapan. Maksimal 10% dari panjang transkrip.\n" .
                                         "2. POIN PEMBAHASAN (pembahasan): Daftar topik yang dibahas, pisahkan tiap poin dengan baris baru, tanpa nomor.\n" .
                                         "3. KEPUTUSAN (keputusan): Daftar keputusan/tindak lanjut yang disepakati, pisahkan dengan baris baru, tanpa nomor.\n" .
                                         "4. KESIMPULAN (kesimpulan): Satu paragraf singkat tentang hasil dan langkah selanjutnya.\n\n" .
                                         "Output HARUS berupa JSON murni tanpa markdown: " .
                                         '{"ringkasan": "...", "pembahasan": "poin 1\npoin 2", "keputusan": "keputusan 1\nkeputusan 2", "kesimpulan": "..."}'
                        ]
                    ],
                ]);

                if ($llmResponse->successful()) {
                    $resJson = $llmResponse->json();
                    $text = $resJson['choices'][0]['message']['content'] ?? null;
                    if ($text) {
                        $text = trim($text);
                        // Strip markdown code fences if present
                        if (str_starts_with($text, '```')) {
                            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
                            $text = preg_replace('/\s*```$/', '', $text);
                            $text = trim($text);
                        }
                        $data = json_decode($text, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            return response()->json([
                                'status' => 'success',
                                'data' => $data
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // fall through to error
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Analisis AI gagal. Tidak ada API key yang dikonfigurasi (GEMINI_API_KEY) dan server Ollama lokal tidak dapat dijangkau atau tidak merespons. Pastikan Ollama berjalan di ' . ($llmApiBase ?? 'localhost:11434') . '.'
        ], 503);
    }
}
