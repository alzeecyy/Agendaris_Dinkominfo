@extends('layouts.app')

@section('title', 'Kelola Notulensi')

@section('content')
<div class="space-y-6">
    <!-- Header/Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('agenda.show', $agenda->id) }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-[#5a508f] hover:text-[#2e2552] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l-7-7m7-7v14"></path>
            </svg>
            <span>Kembali ke Detail Agenda</span>
        </a>
        <div>
            <h1 class="text-lg font-black text-[#2e2552]">Kelola Notulensi Rapat</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- LEFT/MID COLUMN: EDIT FIELDS FORM -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 md:p-8 shadow-sm space-y-6">
                
                <!-- Audio Status Block -->
                @if($notulensi->audio_path)
                    <div class="p-4 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl space-y-3 text-[#2e2552]">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-[#5a508f] font-semibold">Berkas Rekaman Rapat:</span>
                            <span class="text-[9px] bg-[#2e2552]/10 text-[#2e2552] border border-[#2e2552]/20 px-2.5 py-0.5 rounded-full font-bold uppercase">Tercatat</span>
                        </div>
                        <p class="text-xs font-bold text-[#2e2552] truncate">{{ $notulensi->audio_name }}</p>
                        
                        <audio controls class="w-full h-8 mt-2 bg-white border border-[#d4d1f5]/60 rounded-xl">
                            <source src="{{ asset('storage/' . $notulensi->audio_path) }}" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>

                        @if(empty($notulensi->transkrip_raw))
                            <!-- Loading processing state -->
                            <div class="p-3 bg-[#8e88dd]/10 border border-[#8e88dd]/20 rounded-xl flex items-center gap-3 animate-pulse">
                                <div class="w-2 h-2 bg-[#8e88dd] rounded-full animate-ping"></div>
                                <span class="text-xs text-[#2e2552] font-semibold">AI sedang memproses berkas audio. Silakan tunggu dan lakukan pembaruan (refresh) pada halaman ini dalam beberapa saat.</span>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Audio upload dropzone -->
                    <div class="p-6 bg-[#f8f7ff] border border-[#d4d1f5]/60 border-dashed rounded-[24px] text-center space-y-4 text-[#2e2552]">
                        <div class="mx-auto w-12 h-12 bg-[#8e88dd]/10 rounded-2xl flex items-center justify-center text-[#2e2552] shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold text-[#2e2552]">Unggah Rekaman Suara Rapat</h4>
                            <p class="text-[10px] text-[#5a508f] font-medium">Maksimal 60 menit, 20MB. Format: .mp3, .wav, .m4a</p>
                        </div>
                        
                        <form action="{{ route('notulensi.upload', $agenda->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="audio" required accept="audio/*" onchange="this.form.submit()" class="hidden" id="audio-input">
                            <label for="audio-input" class="inline-flex px-4 py-2 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-xl cursor-pointer shadow-md shadow-[#2e2552]/10 transition-all">
                                Pilih Berkas Audio
                            </label>
                        </form>
                    </div>
                @endif

                <!-- Edit Draft Form -->
                <form id="notulen-form" class="space-y-6">
                    @csrf
                    
                    <!-- Transkrip Raw -->
                    <div class="space-y-1.5">
                        <label for="transkrip_raw" class="block text-xs font-bold uppercase tracking-wider text-[#5a508f]">Transkrip Percakapan Rapat (Hasil AI/Edit)</label>
                        <textarea name="transkrip_raw" id="transkrip_raw" rows="8" placeholder="Transkrip lengkap percakapan rapat..."
                                  class="w-full px-4 py-3 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">{{ $notulensi->transkrip_raw }}</textarea>
                    </div>

                    <!-- Ringkasan -->
                    <div class="space-y-1.5">
                        <label for="ringkasan" class="block text-xs font-bold uppercase tracking-wider text-[#5a508f]">Ringkasan Hasil Rapat</label>
                        <textarea name="ringkasan" id="ringkasan" rows="4" placeholder="Ringkasan singkat hasil keputusan rapat..."
                                  class="w-full px-4 py-3 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">{{ $notulensi->ringkasan }}</textarea>
                    </div>

                    <!-- Pembahasan -->
                    <div class="space-y-1.5">
                        <label for="pembahasan" class="block text-xs font-bold uppercase tracking-wider text-[#5a508f]">Poin Pembahasan Rapat</label>
                        <textarea name="pembahasan" id="pembahasan" rows="6" placeholder="Tuliskan butir-butir penting yang dibahas..."
                                  class="w-full px-4 py-3 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">{{ $notulensi->pembahasan }}</textarea>
                    </div>

                    <!-- Keputusan -->
                    <div class="space-y-1.5">
                        <label for="keputusan" class="block text-xs font-bold uppercase tracking-wider text-[#5a508f]">Daftar Keputusan Rapat</label>
                        <textarea name="keputusan" id="keputusan" rows="4" placeholder="Tuliskan kesepakatan atau keputusan hasil rapat..."
                                  class="w-full px-4 py-3 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">{{ $notulensi->keputusan }}</textarea>
                    </div>

                    <!-- Kesimpulan -->
                    <div class="space-y-1.5">
                        <label for="kesimpulan" class="block text-xs font-bold uppercase tracking-wider text-[#5a508f]">Kesimpulan Akhir</label>
                        <textarea name="kesimpulan" id="kesimpulan" rows="3" placeholder="Kesimpulan penutup rapat..."
                                  class="w-full px-4 py-3 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">{{ $notulensi->kesimpulan }}</textarea>
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
</div>
@endsection
