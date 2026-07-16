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

            $pythonPath = env('PYTHON_PATH', 'python');
            $scriptPath = base_path('transcribe_whisper_cpp.py');
            $audioFile = Storage::disk('public')->path($audioPath);
            Log::info("ProcessMeetingAudio: absolute path: " . $audioFile);
            Log::info("ProcessMeetingAudio: file_exists? " . (file_exists($audioFile) ? "YES" : "NO"));

            $newTranscript = "";
            if (file_exists($audioFile)) {
                try {
                    Log::info("ProcessMeetingAudio: Transcribing audio via local whisper.cpp...");
                    $cmd = (str_contains($pythonPath, ' ') ? '"' . $pythonPath . '"' : $pythonPath) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($audioFile) . " 2>&1";
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

            $prompt = "Anda adalah editor profesional yang bertugas merapikan hasil transkrip rapat menjadi dokumen yang mudah dibaca.\n\n" .
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
                      "- Sub-bagian (misal: Informasi Rapat, Daftar Hadir, Pembahasan, Kesimpulan): gunakan ##\n" .
                      "- Nama pembicara (jika eksplisit disebutkan): tebalkan dengan **Nama:** diikuti isi ucapan\n" .
                      "- Poin-poin penting/daftar: gunakan bullet (-) atau angka (1.)\n" .
                      "- Jangan gunakan format lain di luar markdown standar (tanpa HTML, tanpa tabel kompleks kecuali diminta)\n\n" .
                      "OUTPUT\n" .
                      "Berikan hanya hasil transkrip yang sudah dirapikan dalam format markdown, tanpa penjelasan tambahan.\n\n" .
                      "Sebelum menghasilkan jawaban akhir, lakukan validasi akhir terhadap seluruh dokumen:\n" .
                      "1. Pastikan tidak ada nama, gelar, atau struktur field yang dikarang dan tidak ada di transkrip asli.\n" .
                      "2. Pastikan tidak ada lagi istilah yang memiliki lebih dari satu variasi penulisan apabila sebenarnya mengacu pada entitas yang sama.\n" .
                      "3. Pastikan struktur markdown (judul, sub-judul, bold) sudah konsisten dari awal hingga akhir dokumen.\n\n" .
                      "Berikut transkrip:\n\n" . $combinedTranscript;

            $llmApiBase = env('LLM_API_BASE');
            $llmApiKey = env('LLM_API_KEY') ?: $apiKey;
            $llmModel = env('LLM_MODEL') ?: 'gemini-3.5-flash';

            // Try custom OpenAI-compatible API first (e.g. local Qwen, Ollama, LM Studio)
            if ($llmApiBase) {
                try {
                    Log::info("ProcessMeetingAudio: calling custom OpenAI-compatible API ({$llmApiBase}) with model: {$llmModel}...");
                    $url = rtrim($llmApiBase, '/') . '/chat/completions';
                    $response = Http::timeout(480)->withHeaders([
                        'Authorization' => 'Bearer ' . $llmApiKey,
                        'Content-Type' => 'application/json'
                    ])->post($url, [
                        'model' => $llmModel,
                        'temperature' => 0.0,
                        'max_tokens' => 3000,
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
                            $this->notulensi->update([
                                'transkrip_raw' => $combinedTranscript,
                                'ringkasan' => $sumText,
                                'pembahasan' => null,
                                'keputusan' => null,
                                'kesimpulan' => null,
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

            // Fallback to Google Gemini API if custom API is not used but key is present
            if (!$summarized && $apiKey) {
                try {
                    Log::info("ProcessMeetingAudio: calling Gemini API to summarize...");
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey;
                    $responseSummary = Http::timeout(45)->post($url, [
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
                            'temperature' => 0.0
                        ]
                    ]);
                    
                    if ($responseSummary->successful()) {
                        $sumResult = $responseSummary->json();
                        $sumText = $sumResult['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if ($sumText) {
                            $sumText = trim($sumText);
                            $this->notulensi->update([
                                'transkrip_raw' => $combinedTranscript,
                                'ringkasan' => $sumText,
                                'pembahasan' => null,
                                'keputusan' => null,
                                'kesimpulan' => null,
                                'last_edited_by_id' => $this->secretaryId,
                                'status' => 'draft'
                            ]);
                            $summarized = true;
                            Log::info("ProcessMeetingAudio completed and summarized via Gemini API successfully.");
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
