@extends('layouts.app')

@section('title', 'Kelola Notulensi')

@section('content')
<div class="space-y-6">
    <!-- Header/Back & Title -->
    <div class="space-y-1">
        <a href="{{ route('agenda.show', $agenda->id) }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-[#5a508f] hover:text-[#2e2552] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path>
            </svg>
            <span>Kembali ke Detail Agenda</span>
        </a>
        <h1 class="text-lg font-black text-[#2e2552]">Kelola Notulensi Rapat</h1>
    </div>

<script>
function showUploadLoading(type) {
    if (type === 'compact') {
        document.getElementById('compact-upload-btn').style.display = 'none';
        document.getElementById('compact-upload-loading').style.display = 'inline-flex';
    } else {
        document.getElementById('large-upload-btn').style.display = 'none';
        document.getElementById('large-upload-loading').style.display = 'flex';
    }
}
</script>

    <div x-data="{
        loading: false,
        regenerateSummary() {
            const transcript = document.getElementById('transkrip_raw').value;
            if (!transcript.trim()) {
                Swal.fire({
                    text: 'Teks transkrip rapat masih kosong!',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                text: 'Apakah Anda yakin ingin menganalisis ulang transkrip? Tindakan ini akan menimpa isi Ringkasan saat ini.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                this.loading = true;

                fetch('{{ route("notulensi.regenerate", $agenda->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        transkrip_raw: transcript
                    })
                })
                .then(res => res.json())
                .then(res => {
                    this.loading = false;
                    if (res.status === 'success') {
                        document.getElementById('ringkasan').value = res.data.trim();
                        Swal.fire({
                            text: 'Analisis transkrip berhasil! Ringkasan telah diperbarui. Silakan edit dan rapikan sesuai kebutuhan.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            text: res.message || 'Gagal memproses analisis transkrip.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(err => {
                    this.loading = false;
                    Swal.fire({
                        text: 'Terjadi kesalahan koneksi saat memproses analisis.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        }
    }" class="space-y-6">
        
        <!-- EDIT FIELDS FORM -->
        <div class="space-y-6">
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 md:p-8 shadow-sm space-y-6">
                
                <!-- Audio List -->
                @if(!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Berkas Rekaman Rapat ({{ count($notulensi->audio_files) }}/3):</span>
                        </div>
                        
                        <div class="space-y-3">
                            @foreach($notulensi->audio_files as $index => $audio)
                                <div class="p-4 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-xs font-bold text-[#2e2552] truncate flex-1" title="{{ $audio['name'] }}">
                                            {{ $index + 1 }}. {{ $audio['name'] }}
                                        </p>
                                        
                                        <!-- Delete Audio Form -->
                                        <form action="{{ route('notulensi.audio.delete', [$agenda->id, $index]) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus rekaman audio ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-500 p-1 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Rekaman">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <audio controls class="w-full h-8 bg-white border border-[#d4d1f5]/60 rounded-xl">
                                        <source src="{{ asset('storage/' . $audio['path']) }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Audio Upload Box (Max 3) -->
                @if(empty($notulensi->audio_files) || count($notulensi->audio_files) < 3)
                    @if(!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0)
                        <!-- Compact Upload Box (when audio already exists) -->
                        <div class="p-4 bg-[#f8f7ff] border border-[#d4d1f5]/60 rounded-2xl flex items-center justify-between gap-4 text-[#2e2552] mt-3">
                            <div class="min-w-0 flex-1 text-left">
                                <h4 class="text-xs font-bold text-[#2e2552]">Tambah Berkas Audio Lainnya</h4>
                                <p class="text-[9px] text-[#5a508f] truncate font-medium">Format: .mp3, .wav, .m4a (Maks 20MB)</p>
                            </div>
                            <form action="{{ route('notulensi.upload', $agenda->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="audio" required accept="audio/*" onchange="showUploadLoading('compact'); this.form.submit()" class="hidden" id="audio-input-compact">
                                <label id="compact-upload-btn" for="audio-input-compact" class="inline-flex px-3.5 py-2 bg-[#2e2552] hover:bg-[#3d326a] text-white text-[10px] font-bold rounded-xl cursor-pointer shadow-sm transition-all whitespace-nowrap">
                                    + Pilih Berkas
                                </label>
                                <div id="compact-upload-loading" style="display: none;" class="items-center gap-1.5 text-[10px] font-bold text-[#5a508f]">
                                    <svg class="w-3.5 h-3.5 animate-spin text-[#8e88dd]" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Mengunggah...</span>
                                </div>
                            </form>
                        </div>
                    @else
                        <!-- Large Upload Box (first upload) -->
                        <div class="p-6 bg-[#f8f7ff] border border-[#d4d1f5]/60 border-dashed rounded-[24px] text-center space-y-4 text-[#2e2552]">
                            <div class="mx-auto w-12 h-12 bg-[#8e88dd]/10 rounded-2xl flex items-center justify-center text-[#2e2552] shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-[#2e2552]">Unggah Rekaman Suara Rapat</h4>
                                <p class="text-[10px] text-[#5a508f] font-medium">Maksimal 3 berkas audio. Maksimal 60 menit & 20MB per file. (.mp3, .wav, .m4a)</p>
                            </div>
                            
                            <form action="{{ route('notulensi.upload', $agenda->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="audio" required accept="audio/*" onchange="showUploadLoading('large'); this.form.submit()" class="hidden" id="audio-input-large">
                                <label id="large-upload-btn" for="audio-input-large" class="inline-flex px-4 py-2 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-xl cursor-pointer shadow-md shadow-[#2e2552]/10 transition-all">
                                    Pilih Berkas Audio
                                </label>
                                <div id="large-upload-loading" style="display: none;" class="flex items-center justify-center gap-2 text-xs font-bold text-[#5a508f] py-2">
                                    <svg class="w-4 h-4 animate-spin text-[#8e88dd]" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Mengunggah audio ke server...</span>
                                </div>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-xs font-bold text-center mt-3">
                        Batas maksimal 3 rekaman audio rapat telah tercapai. Hapus salah satu jika ingin mengunggah file baru.
                    </div>
                @endif

                <!-- Next Steps / Live Transcribe Progress Status -->
                @if(!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0)
                    @if($notulensi->is_transcribing)
                        <!-- AI active transcribing state with auto polling -->
                        <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-[20px] space-y-2.5 mt-3 shadow-sm animate-pulse">
                            <div class="flex items-center justify-between text-amber-800 text-[10px] font-black uppercase tracking-wider">
                                <span>Status Pemrosesan:</span>
                                <span class="px-2 py-0.5 bg-amber-100 rounded-full text-[9px] font-black tracking-widest animate-bounce">AI Transkripsi Aktif</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-amber-600/10 rounded-xl flex items-center justify-center text-amber-700 flex-shrink-0 mt-0.5">
                                    <svg class="w-4.5 h-4.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0 text-left">
                                    <h4 class="text-xs font-bold text-amber-900">Menganalisis Suara Rapat...</h4>
                                    <p class="text-[10px] text-amber-700 leading-normal font-medium mt-0.5">
                                        AI sedang mengubah rekaman suara menjadi teks secara offline. Halaman ini akan diperbarui otomatis setelah selesai.
                                    </p>
                                </div>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.reload();
                                }, 3000);
                            </script>
                        </div>
                    @else
                        @if($notulensi->transkrip_error)
                        {{-- Error state: transcription failed --}}
                        <div class="p-4 bg-gradient-to-r from-rose-50 to-red-50 border border-rose-200 rounded-[20px] space-y-2.5 mt-3">
                            <div class="flex items-center justify-between text-rose-800 text-[10px] font-black uppercase tracking-wider">
                                <span>Status Transkripsi:</span>
                                <span class="px-2 py-0.5 bg-rose-100 rounded-full text-[9px] font-black tracking-widest text-rose-700">Gagal</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-rose-600/10 rounded-xl flex items-center justify-center text-rose-700 flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.96-.833-2.73 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0 text-left">
                                    <h4 class="text-xs font-bold text-rose-900">Transkripsi Audio Gagal</h4>
                                    <p class="text-[10px] text-rose-700 leading-normal font-medium mt-0.5">{{ $notulensi->transkrip_error }}</p>
                                </div>
                            </div>
                        </div>
                        @else
                        {{-- Ready state --}}
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-[20px] space-y-2.5 mt-3">
                            <div class="flex items-center justify-between text-blue-800 text-[10px] font-black uppercase tracking-wider">
                                <span>Langkah Selanjutnya:</span>
                                <span class="px-2 py-0.5 bg-blue-100 rounded-full text-[9px] font-bold">Proses AI Selesai</span>
                            </div>
                            <p class="text-xs text-blue-700 leading-relaxed font-medium">
                                Transkripsi otomatis selesai! Anda dapat memeriksa hasil transkrip dan ringkasan di bawah.
                            </p>
                            <button type="button" onclick="window.location.reload()" 
                                    class="w-full py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/10 transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" id="refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                <span>Perbarui &amp; Cek Hasil Transkrip</span>
                            </button>
                        </div>
                        @endif
                    @endif
                @endif

                <!-- Edit Draft Form -->
                <form id="notulen-form" class="space-y-6" action="{{ route('notulensi.save', $agenda->id) }}" method="POST">
                    @csrf
                    
                    @if($errors->any())
                        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs space-y-1">
                            <p class="font-bold flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Pengajuan Gagal. Silakan periksa kembali kelengkapan data:</span>
                            </p>
                            <ul class="list-disc list-inside pl-2 space-y-0.5 font-medium">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Judul & Nomor Surat Rapat -->
                    <div class="p-4 bg-[#8e88dd]/10 border border-[#8e88dd]/20 rounded-2xl space-y-4">
                        <div class="space-y-1">
                            <label for="judul" class="block text-xs font-bold uppercase tracking-wider text-[#2e2552]">Nama / Judul Kegiatan Rapat <span class="text-rose-500">*</span></label>
                            <input type="text" name="judul" id="judul" required value="{{ old('judul', $agenda->judul) }}" placeholder="Contoh: Rapat Evaluasi SPBE..."
                                   class="w-full mt-1 px-4 py-2.5 bg-white border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                        </div>

                        <div class="space-y-1">
                            <label for="nomor_surat_dasar" class="block text-xs font-bold uppercase tracking-wider text-[#2e2552]">Nomor Surat <span class="text-rose-500">*</span></label>
                            <input type="text" name="nomor_surat_dasar" id="nomor_surat_dasar" required value="{{ old('nomor_surat_dasar', $agenda->nomor_surat_dasar) }}" placeholder="Contoh: 005/123/2026 Perihal Undangan Rapat Evaluasi SPBE"
                                   class="w-full mt-1 px-4 py-2.5 bg-white border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                            <p class="text-[10px] text-[#5a508f] mt-1 font-medium">Judul rapat dan nomor surat wajib diisi oleh sekretaris sebelum notulensi diajukan untuk disahkan pimpinan.</p>
                        </div>
                    </div>

                    <!-- Transkrip Raw -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="transkrip_raw" class="block text-xs font-bold uppercase tracking-wider text-[#5a508f]">Transkrip Percakapan Rapat (Hasil AI/Edit)</label>
                            <button type="button" @click="regenerateSummary" :disabled="loading"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-[#1b3bbb] to-[#3b82f6] hover:from-[#09103c] hover:to-[#1b3bbb] text-white text-[9px] font-bold uppercase tracking-wider rounded-lg shadow-sm transition-all disabled:opacity-50">
                                <svg class="w-3 h-3 animate-spin" x-show="loading" fill="none" viewBox="0 0 24 24" style="display: none;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg class="w-3 h-3" x-show="!loading" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span x-text="loading ? 'Memproses...' : 'Analisis Ulang via AI'"></span>
                            </button>
                        </div>
                        <textarea name="transkrip_raw" id="transkrip_raw" rows="8" placeholder="Transkrip lengkap percakapan rapat..."
                                  class="w-full px-4 py-3 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">{{ $notulensi->transkrip_raw }}</textarea>
                    </div>

                    <!-- Ringkasan / Notulensi Utama -->
                    <div class="space-y-1.5">
                        <label for="ringkasan" class="block text-xs font-bold uppercase tracking-wider text-[#5a508f]">Ringkasan &amp; Notulensi Rapat</label>
                        <p class="text-[10px] text-[#5a508f]/70 font-medium">Edit dan rapikan hasil notulensi di sini. Kamu bisa menulis dengan format bebas — poin, paragraf, atau struktur apapun.</p>
                        <textarea name="ringkasan" id="ringkasan" rows="20" placeholder="Tulis ringkasan dan notulensi rapat di sini..."
                                  class="w-full px-4 py-3 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd] font-mono leading-relaxed">{{ trim(preg_replace('/```(?:markdown)?/i', '', $notulensi->ringkasan)) }}</textarea>
                    </div>



                    <!-- Submit Draft buttons & Unsaved Warning -->
                    <div class="space-y-4 pt-6 border-t border-[#d4d1f5]/40">
                        <div x-show="isDirty" x-cloak class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl flex items-center justify-between text-xs text-amber-800 animate-pulse">
                            <span class="flex items-center gap-2 font-bold">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Ada perubahan draf yang belum disimpan! Pastikan menekan tombol <strong>Simpan Progress Draft</strong>.</span>
                            </span>
                            <span class="text-[10px] font-extrabold uppercase bg-amber-200 text-amber-900 px-2 py-0.5 rounded-md shrink-0">Belum Disimpan</span>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <p class="text-[11px] text-[#5a508f] font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Ingat untuk menekan <strong>Simpan Progress Draft</strong> jika melakukan perubahan.</span>
                            </p>

                            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto justify-end">
                                <button type="submit" @click="isDirty = false" formaction="{{ route('notulensi.save', $agenda->id) }}" formmethod="POST"
                                        class="w-full sm:w-auto px-5 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-300 text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    <span>Simpan Progress Draft</span>
                                </button>
                                <button type="submit" @click="isDirty = false" formaction="{{ route('notulensi.submit', $agenda->id) }}" formmethod="POST"
                                        class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-[#2e2552] to-[#4338ca] hover:from-[#211a3d] hover:to-[#3730a3] text-white text-xs font-bold rounded-xl shadow-md shadow-[#2e2552]/20 transition-all flex items-center justify-center gap-2">
                                    <span>Ajukan untuk Persetujuan</span>
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <script>
    function registerNotulenEditor() {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('notulenEditor', () => ({
                loading: false,
                isDirty: false,
                init() {
                    this.$nextTick(() => {
                        const form = document.getElementById('notulen-form');
                        if (form) {
                            form.querySelectorAll('input, textarea, select').forEach(el => {
                                el.addEventListener('input', () => { this.isDirty = true; });
                                el.addEventListener('change', () => { this.isDirty = true; });
                            });
                            form.addEventListener('submit', () => { this.isDirty = false; });
                        }
                    });

                    window.addEventListener('beforeunload', (e) => {
                        if (this.isDirty) {
                            e.preventDefault();
                            e.returnValue = 'Ada perubahan draf notulensi yang belum disimpan!';
                        }
                    });
                },
                regenerateSummary() {
                    const transcript = document.getElementById('transkrip_raw').value;
                    if (!transcript.trim()) {
                        Swal.fire({
                            text: 'Teks transkrip rapat masih kosong!',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    Swal.fire({
                        text: 'Apakah Anda yakin ingin menganalisis ulang transkrip? Tindakan ini akan menimpa isi Ringkasan saat ini.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        this.loading = true;
                        if (typeof window.showHeavyLoading === 'function') {
                            window.showHeavyLoading('Menganalisis Transkrip AI', 'AI sedang menganalisis ulang isi transkrip rapat. Mohon tunggu...');
                        }

                        fetch('{{ route("notulensi.regenerate", $agenda->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                transkrip_raw: transcript
                            })
                        })
                        .then(res => res.json())
                        .then(res => {
                            this.loading = false;
                            if (typeof window.hideHeavyLoading === 'function') window.hideHeavyLoading();
                            if (res.status === 'success') {
                                document.getElementById('ringkasan').value = res.data.trim();
                                this.isDirty = true;
                                Swal.fire({
                                    text: 'Analisis transkrip berhasil! Ringkasan telah diperbarui. Silakan edit dan rapikan sesuai kebutuhan.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                Swal.fire({
                                    text: res.message || 'Gagal memproses analisis transkrip.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(err => {
                            this.loading = false;
                            if (typeof window.hideHeavyLoading === 'function') window.hideHeavyLoading();
                            Swal.fire({
                                text: 'Terjadi kesalahan koneksi saat memproses analisis.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                    });
                }
            }));
        }
    }

    function showUploadLoading(type) {
        if (type === 'compact') {
            document.getElementById('compact-upload-btn').style.display = 'none';
            document.getElementById('compact-upload-loading').style.display = 'inline-flex';
        } else {
            document.getElementById('large-upload-btn').style.display = 'none';
            document.getElementById('large-upload-loading').style.display = 'flex';
        }
        if (typeof window.showHeavyLoading === 'function') {
            window.showHeavyLoading('Transkripsi Audio Rapat', 'Berkas audio sedang diunggah dan diproses secara otomatis oleh Whisper.cpp & AI. Mohon tunggu sejenak dan jangan menutup halaman ini...');
        }
    }

    if (typeof Alpine !== 'undefined') {
        registerNotulenEditor();
    } else {
        document.addEventListener('alpine:init', registerNotulenEditor);
    }
    </script>
</div>
@endsection
