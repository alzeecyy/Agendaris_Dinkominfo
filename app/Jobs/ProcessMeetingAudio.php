<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Notulensi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessMeetingAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notulensi;
    protected $secretaryId;
    protected $specificAudioPath;

    /**
     * Create a new job instance.
     */
    public function __construct(Notulensi $notulensi, $secretaryId, $specificAudioPath = null)
    {
        $this->notulensi = $notulensi;
        $this->secretaryId = $secretaryId;
        $this->specificAudioPath = $specificAudioPath;
    }

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 1;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public $timeout = 300;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        try {
            Log::info("ProcessMeetingAudio job started for notulensi ID: " . $this->notulensi->id);
            
            // Refresh model from DB to get the latest state
            $this->notulensi->refresh();

            // Idempotency guard: if is_transcribing was already reset (e.g., by a concurrent job),
            // or if the notulensi has been deleted, bail out gracefully
            if (!$this->notulensi->is_transcribing) {
                Log::warning("ProcessMeetingAudio: is_transcribing is already false. Skipping duplicate job for notulensi ID: " . $this->notulensi->id);
                return;
            }

            // Guard: if notulensi has been approved/signed off since job was dispatched, don't overwrite
            if ($this->notulensi->status === 'disahkan') {
                Log::warning("ProcessMeetingAudio: Notulensi ID " . $this->notulensi->id . " was already disahkan. Skipping.");
                $this->notulensi->update(['is_transcribing' => false]);
                return;
            }
            
            $agenda = $this->notulensi->agenda;
            $apiKey = env('GEMINI_API_KEY');

            $pythonPath = env('PYTHON_PATH', 'python');
            $scriptPath = base_path('transcribe_whisper_cpp.py');

            // Retrieve list of audio files to process
            $audioFiles = $this->notulensi->audio_files;
            if (empty($audioFiles) && $this->notulensi->audio_path) {
                $audioFiles = [
                    [
                        'name' => $this->notulensi->audio_name ?? 'Rekaman Audio',
                        'path' => $this->notulensi->audio_path
                    ]
                ];
            }

            if (empty($audioFiles)) {
                Log::warning("ProcessMeetingAudio: No audio files to process.");
                $this->notulensi->update(['is_transcribing' => false]);
                return;
            }

            $transcriptBlocks = [];

            foreach ($audioFiles as $index => $audioItem) {
                $audioPath = $audioItem['path'] ?? null;
                $audioName = $audioItem['name'] ?? ('Rekaman #' . ($index + 1));
                
                if (!$audioPath) continue;

                $audioFile = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, Storage::disk('public')->path($audioPath));
                $scriptPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, base_path('transcribe_whisper_cpp.py'));

                if (!file_exists($audioFile)) {
                    Log::error("ProcessMeetingAudio: Audio file not found at " . $audioFile);
                    continue;
                }

                try {
                    $transcribedText = null;

                    // 1. Try Gemini Multimodal STT first (Super fast 3-5s response, zero RAM paging crash)
                    if ($apiKey && filesize($audioFile) <= 20 * 1024 * 1024) {
                        try {
                            Log::info("ProcessMeetingAudio: Transcribing {$audioName} via Gemini Multimodal STT...");
                            $mimeType = 'audio/mp3';
                            $ext = strtolower(pathinfo($audioFile, PATHINFO_EXTENSION));
                            if ($ext === 'wav') $mimeType = 'audio/wav';
                            if ($ext === 'm4a') $mimeType = 'audio/m4a';
                            if ($ext === 'ogg') $mimeType = 'audio/ogg';
                            if ($ext === 'flac') $mimeType = 'audio/flac';
                            if ($ext === 'aac') $mimeType = 'audio/aac';
                            if ($ext === 'webm') $mimeType = 'audio/webm';

                            $audioBase64 = base64_encode(file_get_contents($audioFile));

                            $sttResponse = Http::withoutVerifying()->timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey, [
                                'contents' => [
                                    [
                                        'parts' => [
                                            [
                                                'inlineData' => [
                                                    'mimeType' => $mimeType,
                                                    'data' => $audioBase64
                                                ]
                                            ],
                                            [
                                                'text' => "Transkripsikan percakapan suara dari audio rapat ini secara lengkap, rinci, dan akurat ke dalam Bahasa Indonesia baku. Tuliskan HANYA teks transkrip percakapan suara tanpa kata pengantar atau penjelasan tambahan."
                                            ]
                                        ]
                                    ]
                                ]
                            ]);

                            if ($sttResponse->successful()) {
                                $sttResult = $sttResponse->json();
                                $sttText = $sttResult['candidates'][0]['content']['parts'][0]['text'] ?? null;
                                if (!empty($sttText)) {
                                    $transcribedText = trim($sttText);
                                    Log::info("ProcessMeetingAudio: Gemini Multimodal STT successful for {$audioName}.");
                                }
                            } else {
                                Log::error("ProcessMeetingAudio: Gemini Multimodal STT failed with status " . $sttResponse->status() . ": " . $sttResponse->body());
                            }
                        } catch (\Exception $e) {
                            Log::error("ProcessMeetingAudio: Gemini Multimodal STT exception: " . $e->getMessage());
                        }
                    }

                    // 2. Fallback to local Whisper CPP if Gemini STT failed or offline
                    if (empty($transcribedText)) {
                        Log::info("ProcessMeetingAudio: Transcribing audio file #" . ($index + 1) . " ({$audioName}) via Local Whisper...");
                        
                        $cmd = '"' . $pythonPath . '" "' . $scriptPath . '" "' . $audioFile . '"';
                        $descriptors = [
                            0 => ["pipe", "r"],
                            1 => ["pipe", "w"],
                            2 => ["pipe", "w"],
                        ];

                        $process = proc_open($cmd, $descriptors, $pipes, base_path());
                        $output = '';
                        $stderr = '';

                        if (is_resource($process)) {
                            fclose($pipes[0]);
                            $output = stream_get_contents($pipes[1]);
                            $stderr = stream_get_contents($pipes[2]);
                            fclose($pipes[1]);
                            fclose($pipes[2]);
                            $returnCode = proc_close($process);
                        }

                        if ($output) {
                            $data = json_decode($output, true);
                            if (json_last_error() === JSON_ERROR_NONE && isset($data['status']) && $data['status'] === 'success') {
                                $transcribedText = self::cleanTranscriptText($data['text'] ?? '');
                            } else {
                                Log::error("ProcessMeetingAudio: Whisper error on file " . $audioName . ": Output: " . $output . " Stderr: " . $stderr);
                            }
                        } else {
                            Log::error("ProcessMeetingAudio: Empty output from Python process. Stderr: " . $stderr);
                        }
                    }

                    if (!empty($transcribedText)) {
                        $cleanedText = self::cleanTranscriptText($transcribedText);
                        if (!empty($cleanedText)) {
                            if (count($audioFiles) > 1) {
                                $transcriptBlocks[] = "📌 BAGIAN REKAMAN " . ($index + 1) . ": " . mb_strtoupper($audioName) . "\n" . str_repeat("—", 50) . "\n" . $cleanedText;
                            } else {
                                $transcriptBlocks[] = $cleanedText;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("ProcessMeetingAudio exception for " . $audioName . ": " . $e->getMessage());
                }
            }

            if (empty($transcriptBlocks)) {
                Log::error("ProcessMeetingAudio: All audio transcriptions failed or returned empty.");
                $this->notulensi->update([
                    'is_transcribing' => false,
                    'transkrip_error' => 'Transkripsi gagal: Whisper tidak dapat memproses berkas audio. Pastikan berkas audio valid dan memuat suara percakapan.',
                ]);
                return;
            }

            // Combine formatted transcriptions
            $combinedTranscript = implode("\n\n\n", $transcriptBlocks);

            $summarized = false;

            // Check if combined transcript is too short to summarize
            $textLen = strlen(trim($combinedTranscript));
            if ($textLen < 150) {
                $this->notulensi->update([
                    'transkrip_raw' => $combinedTranscript,
                    'ringkasan' => "Transkripsi selesai. Rekaman audio terlalu singkat/pendek untuk dianalisis secara lengkap oleh AI.",
                    'pembahasan' => null,
                    'keputusan' => null,
                    'kesimpulan' => null,
                    'last_edited_by_id' => $this->secretaryId,
                    'status' => 'draft'
                ]);
                $summarized = true;
                Log::info("ProcessMeetingAudio: Transcript too short ({$textLen} chars). Skipped LLM summarization.");
            }

            $prompt = "Role & Task:\n" .
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
                      "Berikut teks transkrip percakapan rapat:\n\n" . $combinedTranscript;

            // 1. Try Gemini API first (Super fast 1-2s response)
            if ($apiKey) {
                try {
                    Log::info("ProcessMeetingAudio: calling Gemini API for summarization...");
                    $response = Http::withoutVerifying()->timeout(25)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey, [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $prompt
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
                        $sumText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if ($sumText) {
                            $sumText = trim(preg_replace('/```(?:markdown)?/i', '', $sumText));
                            $this->notulensi->update([
                                'transkrip_raw' => $combinedTranscript,
                                'ringkasan' => $sumText,
                                'pembahasan' => null,
                                'keputusan' => null,
                                'kesimpulan' => null,
                                'transkrip_error' => null,
                                'last_edited_by_id' => $this->secretaryId,
                                'status' => 'draft'
                            ]);
                            $summarized = true;
                            Log::info("ProcessMeetingAudio completed and summarized via Gemini API successfully.");
                        }
                    } else {
                        Log::error("ProcessMeetingAudio: Gemini API request failed with status: " . $response->status() . " Body: " . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error("ProcessMeetingAudio exception during Gemini API call: " . $e->getMessage());
                }
            }

            // 2. Fallback to custom OpenAI-compatible API / Qwen if offline or Gemini fails
            $llmApiBase = env('LLM_API_BASE');
            $llmApiKey = env('LLM_API_KEY', 'none');
            $llmModel = env('LLM_MODEL', 'qwen2.5:1.5b');

            if (!$summarized && $llmApiBase) {
                try {
                    Log::info("ProcessMeetingAudio: calling custom OpenAI-compatible API ({$llmApiBase}) with model: {$llmModel}...");
                    $url = rtrim($llmApiBase, '/') . '/chat/completions';
                    $response = Http::timeout(45)->withHeaders([
                        'Authorization' => 'Bearer ' . $llmApiKey,
                        'Content-Type' => 'application/json'
                    ])->post($url, [
                        'model' => $llmModel,
                        'temperature' => 0.1,
                        'top_p' => 0.2,
                        'max_tokens' => 1200,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ]
                    ]);

                    if ($response->successful()) {
                        $resJson = $response->json();
                        $sumText = $resJson['choices'][0]['message']['content'] ?? null;
                        if ($sumText) {
                            $sumText = trim(preg_replace('/```(?:markdown)?/i', '', $sumText));
                            $this->notulensi->update([
                                'transkrip_raw' => $combinedTranscript,
                                'ringkasan' => $sumText,
                                'pembahasan' => null,
                                'keputusan' => null,
                                'kesimpulan' => null,
                                'transkrip_error' => null,
                                'last_edited_by_id' => $this->secretaryId,
                                'status' => 'draft'
                            ]);
                            $summarized = true;
                            Log::info("ProcessMeetingAudio completed and summarized via custom LLM API successfully.");
                        }
                    } else {
                        Log::error("ProcessMeetingAudio: Custom LLM API request failed with status: " . $response->status() . " Body: " . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error("ProcessMeetingAudio exception during custom LLM API call: " . $e->getMessage());
                }
            }

            // If offline or Gemini failed, run smart local heuristic summarizer
            if (!$summarized) {
                Log::info("ProcessMeetingAudio: Generating local heuristic summary...");
                
                $lines = preg_split('/[.!?\n]+/', $combinedTranscript);
                $lines = array_map('trim', $lines);
                $lines = array_filter($lines, function($l) { 
                    return strlen($l) > 3; 
                });
                
                $textLen = strlen(trim($combinedTranscript));
                
                if ($textLen < 120) {
                    // Audio transcript is too short or simple greetings
                    $ringkasan = trim($combinedTranscript);
                    if (empty($ringkasan)) {
                        $ringkasan = "Belum ada ringkasan hasil rapat.";
                    }
                    
                    $pembahasan = "1. Rekaman audio terlalu singkat untuk diidentifikasi poin pembahasannya.";
                    $keputusan = "1. Tidak ada keputusan yang dapat diidentifikasi dari rekaman singkat ini.";
                    $kesimpulan = "Transkripsi selesai namun audio terlalu pendek untuk dianalisis secara lengkap.";
                } else {
                    // Transcript has actual content. Build a comprehensive summary paragraph
                    // covering the beginning, middle, and end of the transcript.
                    $cleanLines = array_values(array_filter($lines, function($l) {
                        return strlen($l) > 10;
                    }));
                    
                    $totalLines = count($cleanLines);
                    $summarySentences = [];
                    
                    if ($totalLines <= 5) {
                        $summarySentences = $cleanLines;
                    } else {
                        $summarySentences[] = $cleanLines[0];
                        $summarySentences[] = $cleanLines[1];
                        
                        $mid = floor($totalLines / 2);
                        $summarySentences[] = $cleanLines[$mid];
                        if ($totalLines > 6) {
                            $summarySentences[] = $cleanLines[$mid + 1];
                        }
                        
                        $summarySentences[] = $cleanLines[$totalLines - 1];
                    }
                    
                    $summarySentences = array_unique($summarySentences);
                    $ringkasan = "Rapat ini membahas jalannya agenda koordinasi berdasarkan rekaman audio rapat yang berhasil ditranskrip secara offline. " . implode(". ", $summarySentences) . ".";
                    $ringkasan = preg_replace('/\.+/', '.', $ringkasan);
                    $ringkasan = str_replace('..', '.', $ringkasan);
                    
                    $pembahasanLines = array_slice($lines, 0, 4);
                    if (empty($pembahasanLines)) {
                        $pembahasanLines[] = "Pembahasan sesuai dengan rekaman suara yang diunggah.";
                    }
                    
                    $keputusanLines = [];
                    foreach ($lines as $line) {
                        if (preg_match('/(setuju|sepakat|putus|pilih|mulai|harus|tindak|akan|laku|evaluasi|bahas|rapat)/i', $line)) {
                            $keputusanLines[] = $line;
                        }
                    }
                    $keputusanLines = array_slice($keputusanLines, 0, 3);
                    if (empty($keputusanLines)) {
                        $keputusanLines[] = "Menyetujui penyelarasan progres koordinasi yang telah dicatat.";
                        $keputusanLines[] = "Menyepakati tindak lanjut penyelesaian poin pembahasan.";
                    }
                    
                    $pembahasan = "";
                    foreach ($pembahasanLines as $idx => $line) {
                        $pembahasan .= ($idx + 1) . ". " . $line . "\n";
                    }
                    $pembahasan = rtrim($pembahasan);
                    
                    $keputusan = "";
                    foreach ($keputusanLines as $idx => $line) {
                        $keputusan .= ($idx + 1) . ". " . $line . "\n";
                    }
                    $keputusan = rtrim($keputusan);
                    
                    $kesimpulan = "Poin utama rapat telah dicatat berdasarkan transkrip suara.";
                }
                
                $this->notulensi->update([
                    'transkrip_raw' => $combinedTranscript,
                    'ringkasan' => $ringkasan,
                    'pembahasan' => $pembahasan,
                    'keputusan' => $keputusan,
                    'kesimpulan' => $kesimpulan,
                    'transkrip_error' => null,
                    'last_edited_by_id' => $this->secretaryId,
                    'status' => 'draft'
                ]);
                Log::info("ProcessMeetingAudio completed and summarized via Local Heuristic successfully.");
            }
        } finally {
            $this->notulensi->update([
                'is_transcribing' => false
            ]);
            Log::info("ProcessMeetingAudio finally: set is_transcribing to false.");
        }
    }

    public static function cleanTranscriptText(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $fillers = [
            '/\bah\b/i', '/\beh\b/i', '/\boh\b/i', '/\bsih\b/i', '/\bkan\b/i', 
            '/\bgitu\b/i', '/\bkayak\b/i', '/\bdong\b/i', '/\bdeh\b/i', 
            '/\bkok\b/i', '/\byah\b/i', '/\bhehe\b/i', '/\bhihi\b/i', 
            '/\buh\b/i', '/\bum\b/i', '/\beh-eh\b/i', '/\bya\b/i'
        ];
        $text = preg_replace($fillers, '', $text);
        $text = preg_replace('/\b(\w+)(?:\s*,\s*\1\b)+/i', '$1', $text);
        $text = preg_replace('/\b(\w+)(?:\s+\1\b)+/i', '$1', $text);
        $text = preg_replace('/\b[b-hj-zB-HJ-Z]\b\s*/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\s*,\s*/', ', ', $text);
        $text = preg_replace('/\s*\.\s*/', '. ', $text);
        $text = preg_replace('/,\s*\./', '.', $text);
        $text = preg_replace('/\.{2,}/', '.', $text);
        $text = preg_replace('/,{2,}/', ',', $text);
        
        $sentences = preg_split('/([.!?]\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $text = '';
        foreach ($sentences as $i => $s) {
            if ($i % 2 === 0) {
                $text .= ucfirst(trim($s));
            } else {
                $text .= $s;
            }
        }

        return trim($text);
    }

    /**
     * Handle a job failure.
     * Safety net to ensure is_transcribing is always reset even if the job fails
     * outside the try/finally block (e.g., serialization errors, timeout kills).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessMeetingAudio FAILED for notulensi ID: " . $this->notulensi->id . " - " . $exception->getMessage());

        try {
            $this->notulensi->update([
                'is_transcribing' => false,
                'transkrip_error' => 'Proses transkripsi gagal secara tidak terduga. Silakan coba lagi.',
            ]);
        } catch (\Exception $e) {
            Log::error("ProcessMeetingAudio failed() cleanup error: " . $e->getMessage());
        }
    }
}
