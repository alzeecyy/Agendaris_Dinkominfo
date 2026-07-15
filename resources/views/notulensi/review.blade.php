@extends('layouts.app')

@section('title', 'Tinjau Notulensi')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs / Back button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('agenda.show', $agenda->id) }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-[#5a508f] hover:text-[#2e2552] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l-7-7m7-7v14"></path>
            </svg>
            <span>Kembali ke Detail Agenda</span>
        </a>
        <div>
            <h1 class="text-lg font-black text-[#2e2552]">Tinjau Draf Notulensi</h1>
        </div>
    </div>

    <div x-data="{ openRevisiPanel: false }" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- LEFT PANEL: READ ONLY MINUTES DETAILS -->
        <div class="lg:col-span-2 space-y-6 bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 md:p-8 shadow-sm">
            <div>
                <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-600 border border-amber-200 text-[10px] font-bold uppercase tracking-wide">Menunggu Review</span>
                <h2 class="text-lg font-black text-[#2e2552] mt-2 leading-tight">{{ $agenda->judul }}</h2>
                <p class="text-xs text-[#5a508f] mt-1">Dasar Pelaksanaan: <strong class="text-[#2e2552]">{{ $agenda->nomor_surat_dasar }}</strong></p>
            </div>

            <!-- Read only fields -->
            <div class="space-y-5 text-sm text-[#2e2552] border-t border-[#d4d1f5]/40 pt-4">
                <div class="space-y-1.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Ringkasan Rapat</h4>
                    <p class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed whitespace-pre-line font-medium">{{ $notulensi->ringkasan }}</p>
                </div>
                
                <div class="space-y-1.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">{{ $notulensi->pembahasan_title }}</h4>
                    <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed whitespace-pre-line font-medium text-slate-700">{{ $notulensi->pembahasan }}</div>
                </div>

                <div class="space-y-1.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">{{ $notulensi->keputusan_title }}</h4>
                    <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 text-emerald-600 font-bold rounded-2xl leading-relaxed whitespace-pre-line">{{ $notulensi->keputusan }}</div>
                </div>

                <div class="space-y-1.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Kesimpulan</h4>
                    <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed whitespace-pre-line font-medium text-slate-700">{{ $notulensi->kesimpulan }}</div>
                </div>

                <div class="space-y-1.5" x-data="{ showTranscript: false }">
                    <button @click="showTranscript = !showTranscript" type="button" class="flex items-center gap-1.5 text-xs text-[#8e88dd] hover:text-[#2e2552] font-bold focus:outline-none">
                        <span x-text="showTranscript ? 'Sembunyikan Transkrip Lengkap' : 'Lihat Transkrip Percakapan Lengkap (AI)'"></span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': showTranscript}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="showTranscript" x-cloak class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed text-xs text-slate-600 whitespace-pre-line mt-2">
                        {{ $notulensi->transkrip_raw }}
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: DECISION ACTIONS -->
        <div class="space-y-6">
            
            @if($isApprover)
                <!-- Approval Control Card -->
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Persetujuan Dokumen</h3>
                        <p class="text-[10px] text-[#5a508f] mt-1.5 leading-relaxed font-medium">Harap tinjau draf di sebelah kiri secara saksama sebelum mengambil keputusan.</p>
                    </div>

                    <div class="space-y-3">
                        <!-- 1. APPROVE ACTION -->
                        <form action="{{ route('notulensi.review.approve', $agenda->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengesahkan notulensi rapat ini?')">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.98] text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-emerald-600/10 transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Sahkan & Setujui Dokumen</span>
                            </button>
                        </form>

                        <!-- 2. REVISE SWITCH BUTTON -->
                        <button @click="openRevisiPanel = !openRevisiPanel" 
                                class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-[#5a508f] font-bold text-xs rounded-xl border border-[#d4d1f5] transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Tolak & Minta Perbaikan</span>
                        </button>
                    </div>

                    <!-- 3. REVISION INPUT PANEL -->
                    <div x-show="openRevisiPanel" x-cloak x-transition
                         class="p-4 bg-rose-50/50 border border-rose-200 rounded-2xl space-y-3">
                        <form action="{{ route('notulensi.review.revision', $agenda->id) }}" method="POST">
                            @csrf
                            <label for="catatan_revisi" class="block text-[10px] font-bold text-rose-600 uppercase">Catatan Revisi untuk Sekretaris</label>
                            <textarea name="catatan_revisi" id="catatan_revisi" rows="4" required placeholder="Tuliskan bagian mana yang perlu diperbaiki (misal: koreksi redaksi keputusan rapat poin kedua)..."
                                      class="w-full mt-1.5 px-3 py-2 bg-white border border-[#d4d1f5] rounded-xl text-xs text-[#2e2552] placeholder-slate-400 focus:outline-none"></textarea>
                            
                            <button type="submit" 
                                    class="w-full mt-3 py-2.5 bg-rose-600 hover:bg-rose-500 active:scale-[0.98] text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-rose-600/20 transition-all">
                                Kirim Catatan Perbaikan
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- Read Only Info Card for Secretary -->
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Status Dokumen</h3>
                    <div class="p-4 bg-amber-50/50 border border-amber-200 text-amber-800 rounded-2xl text-xs space-y-2 leading-relaxed">
                        <p class="font-bold flex items-center gap-1">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Mode Pratinjau (Mode Baca)</span>
                        </p>
                        <p>Anda sedang meninjau draf notulensi ini. Pengesahan hanya dapat dilakukan oleh Pimpinan/Ketua Rapat yang berwenang.</p>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
