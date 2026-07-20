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

    <div x-data="notulenEditor" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- LEFT/MID COLUMN: EDIT FIELDS FORM -->
        <div class="lg:col-span-2 space-y-6">
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
                                <svg class="w-3.5 h-3.5" id="refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2M7 9a7 7 0 1111.468-4.686L18 9"/>
                                </svg>
                                <span>Perbarui &amp; Cek Hasil Transkrip</span>
                            </button>
                        </div>
                        @endif
                    @endif
                @endif

                <!-- Edit Draft Form -->
                <form id="notulen-form" class="space-y-6">
                    @csrf
                    
                    <!-- Judul & Nomor Surat Rapat -->
                    <div class="p-4 bg-[#8e88dd]/10 border border-[#8e88dd]/20 rounded-2xl space-y-4">
                        <div class="space-y-1">
                            <label for="judul" class="block text-xs font-bold uppercase tracking-wider text-[#2e2552]">Nama / Judul Kegiatan Rapat <span class="text-rose-500">*</span></label>
                            <input type="text" name="judul" id="judul" required value="{{ old('judul', $agenda->judul) }}" placeholder="Contoh: Rapat Evaluasi SPBE..."
                                   class="w-full mt-1 px-4 py-2.5 bg-white border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                        </div>

                        <div class="space-y-1">
                            <label for="nomor_surat_dasar" class="block text-xs font-bold uppercase tracking-wider text-[#2e2552]">Nomor Surat <span class="text-rose-500">*</span></label>
                            <input type="text" name="nomor_surat_dasar" id="nomor_surat_dasar" value="{{ old('nomor_surat_dasar', $agenda->nomor_surat_dasar) }}" placeholder="Contoh: 005/123/2026 Perihal Undangan Rapat Evaluasi SPBE"
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



                    <!-- Submit Draft buttons -->
                    <div class="flex items-center justify-end gap-3 border-t border-[#d4d1f5]/40 pt-6">
                        <button type="submit" formaction="{{ route('notulensi.save', $agenda->id) }}" formmethod="POST"
                                class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-xl transition-all">
                            Simpan Progress Draft
                        </button>
                        <button type="submit" formaction="{{ route('notulensi.submit', $agenda->id) }}" formmethod="POST"
                                class="px-6 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-xl shadow-md shadow-[#2e2552]/10 transition-all">
                            Ajukan untuk Persetujuan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN: PESERTA EKSTERNAL MANAGEMENT -->
        <div class="space-y-6">
            
            <!-- External Guests Card -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-6">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Daftar Hadir Tamu Eksternal</h3>
                    <p class="text-[10px] text-[#5a508f] mt-1.5 leading-relaxed font-medium">Gunakan ini untuk memasukkan nama undangan dari luar Dinkominfo (OPD lain, dll).</p>
                </div>

                <!-- Add Guest Form -->
                <form action="{{ route('notulensi.external.add', $agenda->id) }}" method="POST" class="space-y-3 p-4 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl text-[#2e2552]">
                    @csrf
                    <div class="space-y-1">
                        <label for="ext_nama" class="block text-[9px] font-bold text-[#5a508f] uppercase">Nama Tamu</label>
                        <input type="text" name="nama" id="ext_nama" required placeholder="Contoh: Budi Santoso, S.Kom"
                               class="w-full px-3 py-2 bg-white border border-[#d4d1f5] rounded-xl text-xs text-[#2e2552] placeholder-slate-400 focus:outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="ext_jabatan" class="block text-[9px] font-bold text-[#5a508f] uppercase">Jabatan</label>
                        <input type="text" name="jabatan" id="ext_jabatan" required placeholder="Contoh: Analis Infrastruktur"
                               class="w-full px-3 py-2 bg-white border border-[#d4d1f5] rounded-xl text-xs text-[#2e2552] placeholder-slate-400 focus:outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="ext_instansi" class="block text-[9px] font-bold text-[#5a508f] uppercase">Instansi Asal</label>
                        <input type="text" name="instansi" id="ext_instansi" required placeholder="Contoh: Bappeda Litbang"
                               class="w-full px-3 py-2 bg-white border border-[#d4d1f5] rounded-xl text-xs text-[#2e2552] placeholder-slate-400 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                        + Tambah Tamu
                    </button>
                </form>

                <!-- List Guests table -->
                <div class="space-y-2">
                    <h4 class="text-[10px] font-bold text-[#5a508f] uppercase">Tamu Terdaftar:</h4>
                    
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        @forelse($externalParticipants as $guest)
                            <div class="flex items-center justify-between p-3 bg-[#f8f7ff] border border-[#d4d1f5]/20 rounded-2xl text-xs">
                                <div class="min-w-0">
                                    <div class="font-bold text-[#2e2552] truncate">{{ $guest->nama }}</div>
                                    <div class="text-[9px] text-[#5a508f] truncate font-medium">{{ $guest->jabatan }} - <strong class="text-[#2e2552]">{{ $guest->instansi }}</strong></div>
                                </div>
                                <form action="{{ route('notulensi.external.delete', $guest->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-500 p-1.5 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus Tamu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4 italic font-medium">Belum ada tamu eksternal.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>
    <script>
    function registerNotulenEditor() {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('notulenEditor', () => ({
                loading: false,
                init() {},
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
    }

    if (typeof Alpine !== 'undefined') {
        registerNotulenEditor();
    } else {
        document.addEventListener('alpine:init', registerNotulenEditor);
    }
    </script>
</div>
@endsection
