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

        if (!$user->hasAccessToAgenda($agenda)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk membuka agenda ini.');
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

        return view('notulensi.review', compact('agenda', 'notulensi', 'isApprover'));
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

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'transkrip_raw' => 'required|string',
        ]);

        $transcript = $request->input('transkrip_raw');

        // Return early if the transcript is too short to analyze
        if (strlen(trim($transcript)) < 150) {
            return response()->json([
                'status' => 'success',
                'data' => "Transkripsi selesai. Rekaman audio terlalu singkat/pendek untuk dianalisis secara lengkap oleh AI."
            ]);
        }

        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(45)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                     'text' => "Anda adalah Sekretaris Profesional & Notulis Rapat Senior. Tugas Anda adalah menganalisis teks transkrip percakapan rapat berikut dan menyusun RINGKASAN & NOTULENSI RAPAT yang sangat rapi, terstruktur, profesional, dan mudah dipahami.\n\n" .
                                               "STRUKTUR OUTPUT MARKDOWN MANDATORI:\n\n" .
                                               "### 📌 RINGKASAN EKSEKUTIF RAPAT\n" .
                                               "[Tuliskan 1-2 paragraf ringkasan eksekutif yang merangkum keseluruhan isi pembicaraan rapat secara padat, jelas, dan profesional]\n\n" .
                                               "### 💡 POIN-POIN PEMBAHASAN UTAMA\n" .
                                               "1. **[Judul Topik/Bahasan Utama]**\n" .
                                               "   - Rincian pembahasan dan penjelasan yang disampaikan narasumber/peserta.\n" .
                                               "2. **[Judul Topik/Bahasan Selanjutnya]**\n" .
                                               "   - Rincian pembahasan dan penjelasan lanjutan.\n\n" .
                                               "### 📝 KEPUTUSAN & TINDAK LANJUT\n" .
                                               "1. **[Keputusan/Kesepakatan Pertama]**: Penjelasan rincian keputusan atau langkah konkret yang disepakati.\n" .
                                               "2. **[Tindak Lanjut]**: Rencana penanganan atau tugas kelanjutan setelah rapat.\n\n" .
                                               "ATURAN PENULISAN:\n" .
                                               "- Gunakan bahasa Indonesia baku yang formal dan mudah dipahami.\n" .
                                               "- Ekstrak seluruh poin penting dari SELURUH bagian transkrip.\n" .
                                               "- Jangan membuat informasi fiktif di luar transkrip asli.\n" .
                                               "- Tuliskan jawaban LANGSUNG dalam format markdown sesuai struktur di atas tanpa kata pengantar tambahan.\n\n" .
                                               "Berikut teks transkrip percakapan rapat:\n\n" . $transcript
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
                        return response()->json([
                            'status' => 'success',
                            'data' => trim($text)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini Exception: ' . $e->getMessage());
            }
        }

        // Fallback: try local Ollama/OpenAI-compatible LLM API
        $llmApiBase = env('LLM_API_BASE');
        $llmModel = env('LLM_MODEL', 'qwen2.5:1.5b');
        $llmApiKey = env('LLM_API_KEY', 'none');

        if ($llmApiBase) {
            try {
                $url = rtrim($llmApiBase, '/') . '/chat/completions';
                $llmResponse = \Illuminate\Support\Facades\Http::timeout(480)->withHeaders([
                    'Authorization' => 'Bearer ' . $llmApiKey,
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'model' => $llmModel,
                    'temperature' => 0.0,
                    'max_tokens' => 3000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Anda adalah editor profesional yang bertugas merapikan hasil transkrip rapat menjadi dokumen yang mudah dibaca.\n\n" .
                                         "Ikuti seluruh instruksi berikut tanpa terkecuali.\n\n" .
                                         "TUJUAN\n" .
                                         "Menghasilkan transkrip rapat yang rapi, akurat, dan mempertahankan seluruh informasi yang disampaikan narasumber.\n\n" .
                                         "PRIORITAS UTAMA\n" .
                                         "Jika terjadi konflik antara \"membuat kalimat lebih natural\" dan \"akurasi terhadap isi asli\", akurasi harus selalu diutamakan. Lebih baik menandai [tidak jelas] daripada mengarang atau memaksakan kalimat yang tidak sesuai dengan apa yang sebenarnya diucapkan.\n\n" .
                                         "ATURAN\n" .
                                         "1. Jangan menambahkan informasi, opini, atau kesimpulan yang tidak terdapat pada transkrip.\n" .
                                         "2. Jangan menghapus informasi penting.\n" .
                                         "3. Hilangkan kata, frasa, atau kalimat yang berulang akibat kesalahan transkrip.\n" .
                                         "4. Perbaiki ejaan, tata bahasa, tanda baca, serta susunan kalimat agar lebih natural.\n" .
                                         "5. Pertahankan makna asli dari setiap pembicara.\n" .
                                         "6. Jika terdapat bagian yang benar-benar tidak dapat dipahami, tuliskan [tidak jelas].\n" .
                                         "7. Pertahankan nama orang, nama organisasi, nama program kerja, jabatan, lokasi, tanggal, angka, dan istilah penting.\n" .
                                         "8. Hilangkan filler words (eee, anu, kayak, jadi gini, dsb.) yang tidak mengandung informasi.\n" .
                                         "9. Jika kalimat pembicara terpotong/menggantung, rapikan menjadi kalimat utuh selama maknanya tidak berubah; jika maknanya tidak bisa disimpulkan, biarkan apa adanya.\n\n" .
                                         "LARANGAN TAMBAHAN\n" .
                                         "- Dilarang keras mengarang nama, gelar, jabatan, atau struktur field (misalnya label \"Tugas Pertama\", \"Sebelum Sekolah\", dsb.) yang tidak secara eksplisit disebutkan dalam transkrip asli.\n" .
                                         "- Jika transkrip tidak menyebutkan nama pembicara secara eksplisit, gunakan deskripsi peran (misal \"Narasumber\", \"Pewawancara\") — jangan mengarang nama.\n" .
                                         "- Sebelum memformat sebagai dialog berlabel banyak pembicara, identifikasi dulu apakah transkrip ini benar-benar multi-speaker atau hanya satu narasumber yang diwawancarai/ditanya beberapa pertanyaan.\n" .
                                         "- Dilarang membuat kalimat yang secara gramatikal maupun logis tidak masuk akal hanya demi merapikan format.\n" .
                                         "- Jika satu bagian transkrip terlalu rusak/tidak jelas untuk direkonstruksi dengan akurat, tandai bagian tersebut dengan [tidak jelas] daripada menciptakan kalimat baru.\n\n" .
                                         "LARANGAN FORMAT\n" .
                                         "- Dilarang mengubah transkrip naratif/monolog menjadi format tanya-jawab buatan (misal \"Apakah Anda tahu apa itu X?\") jika format tersebut tidak eksplisit ada dalam transkrip asli.\n" .
                                         "- Ikuti struktur asli transkrip: jika berupa narasi/penjelasan mengalir dari satu narasumber, sajikan sebagai narasi terstruktur per topik (bukan Q&A buatan).\n" .
                                         "- Jika transkrip memang berbentuk tanya-jawab (ada pewawancara bertanya secara eksplisit), gunakan Q&A HANYA untuk pertanyaan yang benar-benar diajukan, satu kali per pertanyaan — jangan mengulang entri yang sama.\n" .
                                         "- Dilarang mengulang paragraf, poin, atau entri yang identik lebih dari satu kali dalam output akhir.\n\n" .
                                         "PEMERIKSAAN KONSISTENSI\n" .
                                         "Setelah seluruh transkrip selesai dirapikan, lakukan pemeriksaan ulang terhadap seluruh dokumen dari awal hingga akhir.\n\n" .
                                         "- Identifikasi seluruh nama orang.\n" .
                                         "- Identifikasi seluruh nama organisasi.\n" .
                                         "- Identifikasi seluruh nama divisi.\n" .
                                         "- Identifikasi seluruh nama program kerja.\n" .
                                         "- Identifikasi seluruh singkatan.\n" .
                                         "- Identifikasi seluruh istilah khusus.\n\n" .
                                         "Apabila ditemukan beberapa penulisan berbeda yang mengacu pada entitas yang sama (typo, salah eja, hasil speech-to-text), ubah SEMUA kemunculannya menjadi SATU bentuk penulisan yang konsisten. Gunakan versi yang paling sering muncul atau versi baku/resmi jika diketahui. Jangan hanya memperbaiki kemunculan pertama — pastikan seluruh kemunculan telah diperbaiki.\n\n" .
                                         "FORMAT PENULISAN (Markdown)\n" .
                                         "Gunakan format markdown berikut agar struktur dokumen terbaca jelas saat dikonversi ke PDF:\n" .
                                         "- Judul dokumen: gunakan # (contoh: # Notulensi Rapat [Nama Rapat])\n" .
                                         "- Sub-bagian (misal: Informasi Rapat, Pembahasan, Kesimpulan): gunakan ##\n" .
                                         "- Penomoran poin: gunakan angka langsung tanpa tanda strip di depannya (contoh: 1. Perencanaan Aplikasi, 2. Rapat Koordinasi)\n" .
                                         "- Sub-detail (Isi, Penjelasan, Catatan): tulis langsung nama label diikuti titik dua tanpa tanda strip di depannya (contoh: Isi: ..., Penjelasan: ...)\n" .
                                         "- Jangan gunakan format lain di luar markdown standar (tanpa HTML, tanpa tabel kompleks kecuali diminta)\n\n" .
                                         "OUTPUT\n" .
                                         "Berikan hanya hasil transkrip yang sudah dirapikan dalam format markdown, tanpa penjelasan tambahan.\n\n" .
                                         "Sebelum menghasilkan jawaban akhir, lakukan validasi akhir terhadap seluruh dokumen:\n" .
                                         "1. Pastikan tidak ada nama, gelar, atau struktur field yang dikarang dan tidak ada di transkrip asli.\n" .
                                         "2. Pastikan tidak ada lagi istilah yang memiliki lebih dari satu variasi penulisan apabila sebenarnya mengacu pada entitas yang sama.\n" .
                                         "3. Pastikan struktur markdown (judul, sub-judul, bold) sudah konsisten dari awal hingga akhir dokumen.\n\n" .
                                         "Berikut transkrip:\n\n" . $transcript
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
                    } else {
                        \Illuminate\Support\Facades\Log::error('Ollama empty content in response choice.');
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error('Ollama HTTP error: ' . $llmResponse->status() . ' - ' . $llmResponse->body());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ollama Exception: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Analisis AI gagal. Tidak ada API key yang dikonfigurasi (GEMINI_API_KEY) dan server Ollama lokal tidak dapat dijangkau atau tidak merespons. Pastikan Ollama berjalan di ' . ($llmApiBase ?? 'localhost:11434') . '.'
        ], 503);
    }
}
