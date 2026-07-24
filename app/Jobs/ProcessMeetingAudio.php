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
     * Execute the job.
     */
    public function handle(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        try {
            Log::info("ProcessMeetingAudio job started for notulensi ID: " . $this->notulensi->id);
            
            // Refresh model from DB to get the latest audio_files array
            $this->notulensi->refresh();
            
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
                    Log::info("ProcessMeetingAudio: Transcribing audio file #" . ($index + 1) . " ({$audioName})...");
                    
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
                            $cleanedText = self::cleanTranscriptText($data['text'] ?? '');
                            if (!empty($cleanedText)) {
                                if (count($audioFiles) > 1) {
                                    $transcriptBlocks[] = "📌 BAGIAN REKAMAN " . ($index + 1) . ": " . mb_strtoupper($audioName) . "\n" . str_repeat("—", 50) . "\n" . $cleanedText;
                                } else {
                                    $transcriptBlocks[] = $cleanedText;
                                }
                            }
                        } else {
                            Log::error("ProcessMeetingAudio: Whisper error on file " . $audioName . ": Output: " . $output . " Stderr: " . $stderr);
                        }
                    } else {
                        Log::error("ProcessMeetingAudio: Empty output from Python process. Stderr: " . $stderr);
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

            $prompt = "Anda adalah Sekretaris Profesional & Notulis Rapat Senior. Tugas Anda adalah menganalisis teks transkrip percakapan rapat berikut dan menyusun RINGKASAN & NOTULENSI RAPAT yang sangat rapi, terstruktur, profesional, dan mudah dipahami.\n\n" .
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
                      "- Ekstrak seluruh poin penting dari SELURUH bagian transkrip (termasuk jika terdiri dari beberapa bagian audio/rekaman).\n" .
                      "- Jangan membuat informasi fiktif di luar transkrip asli.\n" .
                      "- Tuliskan jawaban LANGSUNG dalam format markdown sesuai struktur di atas tanpa kata pengantar tambahan.\n\n" .
                      "Berikut teks transkrip percakapan rapat:\n\n" . $combinedTranscript;

            $llmApiBase = env('LLM_API_BASE');
            $llmApiKey = env('LLM_API_KEY') ?: $apiKey;
            $llmModel = env('LLM_MODEL') ?: 'qwen2.5:1.5b';

            // Try custom OpenAI-compatible API first (e.g. local Qwen, Ollama, LM Studio)
            if ($llmApiBase) {
                try {
                    Log::info("ProcessMeetingAudio: calling custom OpenAI-compatible API ({$llmApiBase}) with model: {$llmModel}...");
                    $url = rtrim($llmApiBase, '/') . '/chat/completions';
                    $response = Http::timeout(45)->withHeaders([
                        'Authorization' => 'Bearer ' . $llmApiKey,
                        'Content-Type' => 'application/json'
                    ])->post($url, [
                        'model' => $llmModel,
                        'temperature' => 0.1,
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
}
