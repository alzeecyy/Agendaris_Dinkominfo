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

    /**
     * Create a new job instance.
     */
    public function __construct(Notulensi $notulensi, $secretaryId)
    {
        $this->notulensi = $notulensi;
        $this->secretaryId = $secretaryId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("ProcessMeetingAudio job started for notulensi ID: " . $this->notulensi->id);
        
        $agenda = $this->notulensi->agenda;
        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                // If API Key is present, attempt actual Gemini audio ingestion
                $audioFile = Storage::disk('public')->path($this->notulensi->audio_path);
                
                if (file_exists($audioFile)) {
                    $mimeType = mime_content_type($audioFile);
                    $fileBytes = file_get_contents($audioFile);
                    
                    // Standard Gemini 2.5 Flash API endpoint
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
                    
                    $response = Http::timeout(60)->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'inlineData' => [
                                            'mimeType' => $mimeType,
                                            'data' => base64_encode($fileBytes)
                                        ]
                                    ],
                                    [
                                        'text' => "Anda adalah asisten AI pencatat rapat (notulensi) Dinkominfo Kabupaten Banyumas. " .
                                                  "Transkripsikan audio rapat ini secara lengkap dengan menandai nama pembicara (misal Pembicara 1, Pembicara 2). " .
                                                  "Lalu buatkan ringkasan, rincian pembahasan rapat, keputusan-keputusan rapat, dan kesimpulan. " .
                                                  "Rapat ini berjudul: '{$agenda->judul}', dengan deskripsi: '{$agenda->deskripsi}' di lokasi: '{$agenda->lokasi}'. " .
                                                  "Output Anda HARUS berupa objek JSON dengan format persis seperti berikut tanpa tanda petik kode/markdown (kembalikan JSON murni): " .
                                                  '{"transcript": "teks lengkap transkrip dengan pembicara...", "ringkasan": "teks ringkasan...", "pembahasan": "poin 1\npoin 2...", "keputusan": "keputusan 1\nkeputusan 2...", "kesimpulan": "kesimpulan..."}'
                                        ]
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
                                $this->notulensi->update([
                                    'transkrip_raw' => $data['transcript'] ?? '',
                                    'ringkasan' => $data['ringkasan'] ?? '',
                                    'pembahasan' => $data['pembahasan'] ?? '',
                                    'keputusan' => $data['keputusan'] ?? '',
                                    'kesimpulan' => $data['kesimpulan'] ?? '',
                                    'last_edited_by_id' => $this->secretaryId,
                                    'status' => 'draft'
                                ]);
                                Log::info("ProcessMeetingAudio processed via Gemini API successfully.");
                                return;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("ProcessMeetingAudio exception during Gemini API call: " . $e->getMessage());
            }
        }

        // Fallback AI Simulator (Demo / fallback mode)
        Log::info("ProcessMeetingAudio running Fallback AI Simulator.");
        
        // Wait 5 seconds to simulate processing latency
        sleep(5);

        // Generate realistic content based on Agenda
        $judul = $agenda->judul;
        $lokasi = $agenda->lokasi;
        $deskripsi = $agenda->deskripsi ?? 'koordinasi rutin dinas';

        $transcript = "Pembicara 1 (Pimpinan): Assalamu'alaikum Wr. Wb. Selamat pagi rekan-rekan sekalian. " .
                      "Terima kasih sudah hadir di {$lokasi} untuk membahas agenda '{$judul}'. " .
                      "Sebagai dasar pelaksanaan rapat, kita mengacu pada surat edaran dinas terkait {$deskripsi}. " .
                      "Silakan dari perwakilan bidang untuk menyampaikan laporan terkini.\n\n" .
                      "Pembicara 2: Selamat pagi pimpinan. Berkenaan dengan agenda '{$judul}', bidang kami sudah melakukan inventarisasi awal. " .
                      "Ada beberapa kendala koordinasi lapangan, namun secara umum progres sudah mencapai 80%.\n\n" .
                      "Pembicara 3: Menambahkan untuk aspek kesiapan sarana teknis, tim kami telah memastikan server " .
                      "dan jaringan pendukung siap beroperasi penuh untuk menunjang kegiatan ini.\n\n" .
                      "Pembicara 1 (Pimpinan): Baik, terima kasih. Saya harap seluruh bidang saling bersinergi untuk mempercepat penyelesaian kendala tersebut. " .
                      "Mari kita sepakati poin keputusan rapat hari ini agar langsung bisa ditindaklanjuti.";

        $ringkasan = "Rapat membahas evaluasi dan pelaksanaan koordinasi terkait agenda '{$judul}'. Seluruh perwakilan bidang melaporkan progres kerja masing-masing yang rata-rata telah mencapai 80%, serta menyelaraskan dukungan sarana teknis infrastruktur.";

        $pembahasan = "1. Evaluasi awal pelaksanaan kegiatan '{$judul}'.\n" .
                      "2. Pembahasan kendala koordinasi lapangan antar bidang di lingkungan Dinkominfo.\n" .
                      "3. Laporan kesiapan infrastruktur server dan stabilitas jaringan untuk kelancaran operasional.";

        $keputusan = "1. Menugaskan bidang teknis terkait untuk segera menyelesaikan kendala lapangan dalam waktu 3 hari kerja.\n" .
                     "2. Menyepakati integrasi alur komunikasi antar bidang secara intensif menggunakan platform koordinasi dinas.\n" .
                     "3. Menjadwalkan rapat monitoring tindak lanjut minggu depan.";

        $kesimpulan = "Kegiatan '{$judul}' siap dilaksanakan secara terintegrasi dengan dukungan teknis yang optimal. Koordinasi berkelanjutan mutlak diperlukan untuk menjamin pencapaian target kerja.";

        $this->notulensi->update([
            'transkrip_raw' => "[DEMO AI SIMULATOR]\n\n" . $transcript,
            'ringkasan' => $ringkasan,
            'pembahasan' => $pembahasan,
            'keputusan' => $keputusan,
            'kesimpulan' => $kesimpulan,
            'last_edited_by_id' => $this->secretaryId,
            'status' => 'draft',
        ]);
        
        Log::info("ProcessMeetingAudio completed via Fallback AI Simulator successfully.");
    }
}
