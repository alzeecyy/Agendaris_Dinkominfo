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
        try {
            Log::info("ProcessMeetingAudio job started for notulensi ID: " . $this->notulensi->id);
            
            $agenda = $this->notulensi->agenda;
            $apiKey = env('GEMINI_API_KEY');

            // Determine specific audio file path to process
            $audioPath = $this->specificAudioPath;
            if (!$audioPath) {
                if ($this->notulensi->audio_path) {
                    $audioPath = $this->notulensi->audio_path;
                } elseif (!empty($this->notulensi->audio_files) && is_array($this->notulensi->audio_files)) {
                    $audioPath = $this->notulensi->audio_files[0]['path'] ?? null;
                }
            }

            if (!$audioPath) {
                Log::warning("ProcessMeetingAudio: No audio path to process.");
                return;
            }

            // Determine the audio index in the files list
            $audioIndex = 1;
            if (is_array($this->notulensi->audio_files)) {
                foreach ($this->notulensi->audio_files as $idx => $f) {
                    if ($f['path'] === $audioPath) {
                        $audioIndex = $idx + 1;
                        break;
                    }
                }
            }

            $pythonPath = 'python';
            $scriptPath = base_path('transcribe_whisper_cpp.py');
            $audioFile = Storage::disk('public')->path($audioPath);
            Log::info("ProcessMeetingAudio: absolute path: " . $audioFile);
            Log::info("ProcessMeetingAudio: file_exists? " . (file_exists($audioFile) ? "YES" : "NO"));

            $newTranscript = "";
            if (file_exists($audioFile)) {
                try {
                    Log::info("ProcessMeetingAudio: Transcribing audio via local whisper.cpp...");
                    $cmd = "python " . escapeshellarg($scriptPath) . " " . escapeshellarg($audioFile) . " 2>&1";
                    Log::info("ProcessMeetingAudio running: " . $cmd);
                    $output = shell_exec($cmd);
                    Log::info("ProcessMeetingAudio raw output: " . $output);
                    
                    if ($output) {
                        $data = json_decode($output, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($data['status']) && $data['status'] === 'success') {
                            $newTranscript = self::cleanTranscriptText($data['text'] ?? '');
                            $modelUsed = $data['model'] ?? 'unknown';
                            Log::info("ProcessMeetingAudio: transcription succeeded using model: {$modelUsed}");
                            // Clear any previous error
                            $this->notulensi->update(['transkrip_error' => null]);
                        } else {
                            Log::error("ProcessMeetingAudio local transcription error or raw output: " . $output);
                        }
                    } else {
                        Log::error("ProcessMeetingAudio: shell_exec returned no output.");
                    }
                } catch (\Exception $e) {
                    Log::error("ProcessMeetingAudio exception during local transcription: " . $e->getMessage());
                }
            } else {
                Log::error("ProcessMeetingAudio error: audio file does not exist at " . $audioFile);
            }

            if (empty($newTranscript)) {
                Log::error("ProcessMeetingAudio: local transcription returned empty or failed. Kemungkinan RAM tidak cukup atau whisper.cpp error.");
                $this->notulensi->update([
                    'is_transcribing' => false,
                    'transkrip_error' => 'Transkripsi gagal: Whisper tidak dapat dijalankan. Kemungkinan RAM komputer tidak mencukupi. Tutup aplikasi lain (browser, LM Studio, dll) lalu coba upload ulang berkas audio.',
                ]);
                return;
            }


            // Combine transcriptions
            $isFirstAudio = true;
            if (is_array($this->notulensi->audio_files) && count($this->notulensi->audio_files) > 1) {
                $isFirstAudio = false;
            }

            if ($isFirstAudio) {
                Log::info("ProcessMeetingAudio: Overwriting pre-existing transcript for the first audio upload.");
                $combinedTranscript = $newTranscript;
            } else {
                $existingTranscript = $this->notulensi->transkrip_raw;
                $separator = "\n\n=== [BAGIAN REKAMAN #{$audioIndex}] ===\n\n";
                $combinedTranscript = $existingTranscript . $separator . $newTranscript;
            }

            $summarized = false;

            $prompt = "Anda adalah notulis profesional rapat pemerintahan Dinkominfo Banyumas.\n\n" .
                      "Anda diberikan transkrip mentah hasil speech-to-text dari rekaman audio rapat.\n\n" .
                      "TUGAS ANDA ADA DUA BAGIAN:\n\n" .
                      "== BAGIAN 1: RAPIKAN TRANSKRIP ==\n" .
                      "Rapikan transkrip berikut menjadi teks yang mudah dibaca dengan aturan:\n" .
                      "1. Hapus filler words (ah, eh, oh, ya, sih, kan, gitu, kayak, anu, dll).\n" .
                      "2. Hapus pengulangan kata/kalimat akibat kesalahan transkrip.\n" .
                      "3. Perbaiki ejaan, tanda baca, dan tata bahasa.\n" .
                      "4. Pertahankan SEMUA informasi penting: nama orang, jabatan, angka, tanggal, program kerja, keputusan.\n" .
                      "5. Jika ada bagian tidak jelas, tulis [tidak jelas].\n" .
                      "6. Standarkan penulisan nama/istilah yang sama di seluruh dokumen.\n\n" .
                      "== BAGIAN 2: BUAT DOKUMEN NOTULENSI ==\n" .
                      "Berdasarkan transkrip yang sudah dirapikan, buat 4 elemen notulensi:\n\n" .
                      "A. RINGKASAN (ringkasan):\n" .
                      "   - Tulis dalam 2-4 paragraf singkat dan padat.\n" .
                      "   - Berisi: latar belakang/tujuan rapat, topik utama yang dibahas, dan hasil akhir.\n" .
                      "   - JANGAN menyalin ulang percakapan. Ini adalah EXECUTIVE SUMMARY, bukan transkrip.\n" .
                      "   - Gunakan kalimat aktif dan bahasa formal.\n\n" .
                      "B. POIN PEMBAHASAN (pembahasan):\n" .
                      "   - Daftar topik/agenda yang dibahas dalam rapat (bukan kalimat panjang).\n" .
                      "   - Pisahkan tiap poin dengan baris baru. Tanpa angka/nomor.\n\n" .
                      "C. KEPUTUSAN (keputusan):\n" .
                      "   - Daftar keputusan, kesepakatan, atau tindak lanjut yang disetujui dalam rapat.\n" .
                      "   - Pisahkan tiap poin dengan baris baru. Tanpa angka/nomor.\n\n" .
                      "D. KESIMPULAN (kesimpulan):\n" .
                      "   - Satu paragraf singkat yang merangkum hasil dan langkah selanjutnya setelah rapat.\n\n" .
                      "Berikut transkrip mentah yang perlu dirapikan:\n\n" .
                      $combinedTranscript . "\n\n" .
                      "Output Anda HARUS berupa objek JSON murni (tanpa markdown/backtick) dengan format PERSIS:\n" .
                      '{"ringkasan": "...", "pembahasan": "poin 1\npoin 2\npoin 3", "keputusan": "keputusan 1\nkeputusan 2", "kesimpulan": "..."}' . "\n" .
                      "PENTING: Nilai 'ringkasan' harus jauh lebih singkat dari transkrip asli (maksimal 10% dari panjang transkrip).";

            $llmApiBase = env('LLM_API_BASE');
            $llmApiKey = env('LLM_API_KEY') ?: $apiKey;
            $llmModel = env('LLM_MODEL') ?: 'gemini-2.5-flash';

            // Try custom OpenAI-compatible API first (e.g. local Qwen, Ollama, LM Studio)
            if ($llmApiBase) {
                try {
                    Log::info("ProcessMeetingAudio: calling custom OpenAI-compatible API ({$llmApiBase}) with model: {$llmModel}...");
                    $url = rtrim($llmApiBase, '/') . '/chat/completions';
                    $response = Http::timeout(60)->withHeaders([
                        'Authorization' => 'Bearer ' . $llmApiKey,
                        'Content-Type' => 'application/json'
                    ])->post($url, [
                        'model' => $llmModel,
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
                            $sumText = trim($sumText);
                            if (str_starts_with($sumText, '```')) {
                                $sumText = preg_replace('/^```(?:json)?\s*/i', '', $sumText);
                                $sumText = preg_replace('/\s*```$/', '', $sumText);
                                $sumText = trim($sumText);
                            }
                            $data = json_decode($sumText, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $this->notulensi->update([
                                    'transkrip_raw' => $combinedTranscript,
                                    'ringkasan' => $data['ringkasan'] ?? '',
                                    'pembahasan' => $data['pembahasan'] ?? '',
                                    'keputusan' => $data['keputusan'] ?? '',
                                    'kesimpulan' => $data['kesimpulan'] ?? '',
                                    'last_edited_by_id' => $this->secretaryId,
                                    'status' => 'draft'
                                ]);
                                $summarized = true;
                                Log::info("ProcessMeetingAudio completed and summarized via custom LLM API successfully.");
                            }
                        }
                    } else {
                        Log::error("ProcessMeetingAudio: Custom LLM API request failed with status: " . $response->status() . " Body: " . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error("ProcessMeetingAudio exception during custom LLM API call: " . $e->getMessage());
                }
            }

            // Fallback to Google Gemini API if custom API is not used but key is present
            if (!$summarized && $apiKey) {
                try {
                    Log::info("ProcessMeetingAudio: calling Gemini API to summarize...");
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
                    $responseSummary = Http::timeout(45)->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $prompt
                                    ]
                                ]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json'
                        ]
                    ]);
                    
                    if ($responseSummary->successful()) {
                        $sumResult = $responseSummary->json();
                        $sumText = $sumResult['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if ($sumText) {
                            $sumText = trim($sumText);
                            if (str_starts_with($sumText, '```')) {
                                $sumText = preg_replace('/^```(?:json)?\s*/i', '', $sumText);
                                $sumText = preg_replace('/\s*```$/', '', $sumText);
                                $sumText = trim($sumText);
                            }
                            $data = json_decode($sumText, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $this->notulensi->update([
                                    'transkrip_raw' => $combinedTranscript,
                                    'ringkasan' => $data['ringkasan'] ?? '',
                                    'pembahasan' => $data['pembahasan'] ?? '',
                                    'keputusan' => $data['keputusan'] ?? '',
                                    'kesimpulan' => $data['kesimpulan'] ?? '',
                                    'last_edited_by_id' => $this->secretaryId,
                                    'status' => 'draft'
                                ]);
                                $summarized = true;
                                Log::info("ProcessMeetingAudio completed and summarized via Gemini API successfully.");
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("ProcessMeetingAudio exception during Gemini API call: " . $e->getMessage());
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
