@extends('layouts.app')

@section('title', 'Arsip Notulensi Dinas')

@section('content')
<div class="w-full space-y-6 max-w-7xl mx-auto pb-20 md:pb-10">
    <!-- Page Header Title -->
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-[#09103c] tracking-wide">Arsip Notulensi Resmi Dinas</h1>
        <p class="text-xs sm:text-sm font-medium text-[#5a508f] mt-1">Kumpulan dokumen notulensi rapat seluruh bidang & unit kerja yang telah disahkan oleh Pimpinan Dinkominfo</p>
    </div>

    <!-- Main Card Container (Unified System Card Layout - Fixed Full Height & Width Container) -->
    <div class="w-full bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-3.5 sm:p-6 shadow-sm flex flex-col space-y-4 sm:space-y-5" style="min-height: calc(100vh - 225px);">
        
        <!-- Filter Toolbar Box with Modern Dropdown -->
        <div class="bg-[#f8f7ff] border border-[#d4d1f5]/60 rounded-2xl p-3.5 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="p-2.5 bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-xl shrink-0 border border-[#1b3bbb]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <label for="bidang-filter-select" class="block text-[11px] font-extrabold text-[#5a508f] uppercase tracking-wider">Filter Bidang / Unit Kerja</label>
                    <p class="text-[10.5px] text-slate-500 font-medium truncate">Pilih unit kerja untuk memfilter arsip notulensi</p>
                </div>
            </div>

@php
                $selectedLabelName = 'Semua Bidang';
                $selectedCount = $bidangCounts['semua'] ?? 0;
                if ($selectedBidangId === 'lintas_dinas') {
                    $selectedLabelName = 'Rapat Lintas Dinas';
                    $selectedCount = $bidangCounts['lintas_dinas'] ?? 0;
                } else {
                    foreach($bidangs as $b) {
                        if ((string)$selectedBidangId === (string)$b->id) {
                            $selectedCount = $bidangCounts[$b->id] ?? 0;
                            $selectedLabelName = $b->nama ?: $b->singkatan;
                            break;
                        }
                    }
                }
            @endphp

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Modern Custom Dropdown -->
                <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full sm:w-80">
                    <button type="button" 
                            @click="open = !open" 
                            class="w-full pl-8 pr-8 py-2.5 bg-white border border-[#d4d1f5] hover:border-[#1b3bbb] rounded-xl text-xs font-bold text-[#09103c] shadow-xs flex items-center justify-between gap-2 transition-all cursor-pointer truncate focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]/20">
                        <span class="truncate">{{ $selectedLabelName }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold shrink-0 {{ $selectedCount > 0 ? 'bg-[#1b3bbb]/10 text-[#1b3bbb] border border-[#1b3bbb]/20' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                            {{ $selectedCount }} Notulensi
                        </span>
                    </button>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <!-- Custom Arrow Right -->
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>

                    <!-- Dropdown Menu -->
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150 transform" 
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1" 
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                         x-transition:leave="transition ease-in duration-100 transform" 
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1" 
                         class="absolute left-0 top-full mt-1.5 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-60 overflow-y-auto">
                        <div class="space-y-0.5">
                            @php $semuaCount = $bidangCounts['semua'] ?? 0; @endphp
                            <a href="{{ route('notulensi.arsip', ['bidang_id' => 'semua']) }}" 
                               class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left {{ $selectedBidangId === 'semua' ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]' }}">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    @if($selectedBidangId === 'semua')
                                        <svg class="w-3.5 h-3.5 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                    <span class="truncate">Semua Bidang</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold shrink-0 {{ $selectedBidangId === 'semua' ? ($semuaCount > 0 ? 'bg-white/20 text-white border border-white/30' : 'bg-white/10 text-white/80 border border-white/20') : ($semuaCount > 0 ? 'bg-[#1b3bbb]/10 text-[#1b3bbb] border border-[#1b3bbb]/20' : 'bg-slate-100 text-slate-500 border border-slate-200') }}">
                                    {{ $semuaCount }} Notulensi
                                </span>
                            </a>

                            @php $lintasCount = $bidangCounts['lintas_dinas'] ?? 0; @endphp
                            <a href="{{ route('notulensi.arsip', ['bidang_id' => 'lintas_dinas']) }}" 
                               class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left {{ $selectedBidangId === 'lintas_dinas' ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]' }}">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    @if($selectedBidangId === 'lintas_dinas')
                                        <svg class="w-3.5 h-3.5 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                    <span class="truncate">Rapat Lintas Dinas</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold shrink-0 {{ $selectedBidangId === 'lintas_dinas' ? ($lintasCount > 0 ? 'bg-white/20 text-white border border-white/30' : 'bg-white/10 text-white/80 border border-white/20') : ($lintasCount > 0 ? 'bg-[#1b3bbb]/10 text-[#1b3bbb] border border-[#1b3bbb]/20' : 'bg-slate-100 text-slate-500 border border-slate-200') }}">
                                    {{ $lintasCount }} Notulensi
                                </span>
                            </a>

                            @foreach($bidangs as $b)
                                @php
                                    $count = $bidangCounts[$b->id] ?? 0;
                                    $labelName = $b->nama ?: $b->singkatan;
                                    $isSelected = (string)$selectedBidangId === (string)$b->id;
                                @endphp
                                <a href="{{ route('notulensi.arsip', ['bidang_id' => $b->id]) }}" 
                                   class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left {{ $isSelected ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]' }}">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        @if($isSelected)
                                            <svg class="w-3.5 h-3.5 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                        <span class="truncate" title="{{ $labelName }}">{{ $labelName }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold shrink-0 {{ $isSelected ? ($count > 0 ? 'bg-white/20 text-white border border-white/30' : 'bg-white/10 text-white/80 border border-white/20') : ($count > 0 ? 'bg-[#1b3bbb]/10 text-[#1b3bbb] border border-[#1b3bbb]/20' : 'bg-slate-100 text-slate-500 border border-slate-200') }}">
                                        {{ $count }} Notulensi
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <span class="hidden md:inline-flex items-center text-xs font-extrabold text-[#1b3bbb] bg-[#1b3bbb]/10 border border-[#1b3bbb]/20 px-3 py-2.5 rounded-xl shrink-0">
                    Total: {{ $bidangCounts['semua'] ?? 0 }}
                </span>
            </div>
        </div>

        <!-- Cards Grid -->
        @if($notulensiList->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @foreach($notulensiList as $notulensi)
                    @php
                        $agenda = $notulensi->agenda;
                        if (!$agenda) continue;

                        $creatorBidang = $agenda->sekretaris?->bidang?->singkatan ?? $agenda->sekretaris?->bidang?->nama;
                        $bidangBadgeStr = $creatorBidang ?: 'Dinkominfo';
                        
                        $notulisName = $notulensi->lastEditedBy?->name ?? $agenda->sekretaris?->name ?? 'Admin Rapat';
                        $approverName = $notulensi->approver?->name ?? 'Pimpinan';
                        $approverJabatan = $notulensi->approver?->jabatan ?? 'Pengesah';
                    @endphp

                    <div class="bg-white border border-[#d4d1f5]/60 hover:border-[#1b3bbb] rounded-2xl md:rounded-[24px] p-4 sm:p-5 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <!-- Card Header Badges (Stacked Vertically) -->
                            <div class="flex flex-col items-start gap-1.5">
                                <span class="px-2.5 py-1 rounded-xl bg-[#1b3bbb]/10 text-[#1b3bbb] border border-[#1b3bbb]/20 text-[10.5px] font-extrabold flex items-center gap-1.5 max-w-full truncate">
                                    <svg class="w-3.5 h-3.5 text-[#1b3bbb] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V5"></path>
                                    </svg>
                                    <span class="truncate">{{ $bidangBadgeStr }}</span>
                                </span>

                                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10.5px] font-extrabold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>TELAH DISAHKAN</span>
                                </span>
                            </div>

                            <!-- Agenda Title -->
                            <div>
                                <h3 class="text-sm sm:text-base font-black text-[#2e2552] leading-snug line-clamp-2">
                                    {{ $agenda->judul }}
                                </h3>
                                @if($agenda->nomor_surat_dasar)
                                    <p class="text-[11px] text-[#5a508f] mt-1 font-medium truncate">
                                        No. Surat: <span class="font-bold text-[#2e2552]">{{ $agenda->nomor_surat_dasar }}</span>
                                    </p>
                                @endif
                            </div>

                            <!-- Date & Location Metadata -->
                            <div class="space-y-1.5 p-3 bg-[#f8f7ff] rounded-xl text-xs border border-[#d4d1f5]/60">
                                <div class="flex items-center gap-2 text-[#2e2552] font-bold text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-[#1b3bbb] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ $agenda->tanggal->translatedFormat('l, d F Y') }}</span>
                                    <span class="text-slate-400 font-normal">&bull;</span>
                                    <span>{{ substr($agenda->jam_mulai, 0, 5) }} WIB</span>
                                </div>
                                <div class="flex items-center gap-2 text-[#5a508f] text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-[#8e88dd] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="truncate">{{ $agenda->lokasi }}</span>
                                </div>
                            </div>

                            <!-- Author & Approver Info -->
                            <div class="grid grid-cols-2 gap-2 text-[10.5px] pt-1.5 border-t border-[#d4d1f5]/40">
                                <div>
                                    <span class="text-[9.5px] font-bold text-[#8e88dd] uppercase tracking-wider block">Penyusun</span>
                                    <span class="font-bold text-[#2e2552] truncate block">{{ $notulisName }}</span>
                                </div>
                                <div>
                                    <span class="text-[9.5px] font-bold text-emerald-600 uppercase tracking-wider block">Pengesah</span>
                                    <span class="font-bold text-[#2e2552] truncate block" title="{{ $approverName }} ({{ $approverJabatan }})">
                                        {{ $approverName }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-3 border-t border-[#d4d1f5]/50 flex items-center justify-between gap-2">
                            <a href="{{ route('notulensi.review', $agenda->id) }}" 
                               class="flex-1 py-2 px-3 bg-[#1b3bbb] hover:bg-[#2e2552] text-white rounded-xl text-xs font-bold transition-all text-center flex items-center justify-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span>Lihat Notulensi</span>
                            </a>

                            <div class="flex items-center gap-1">
                                <a href="{{ route('notulensi.export.pdf', $agenda->id) }}" target="_blank" data-no-pjax title="Unduh PDF Resmi" 
                                   class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition-all">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('notulensi.export.docx', $agenda->id) }}" target="_blank" data-no-pjax title="Unduh Word (DOCX)" 
                                   class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition-all">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State (Centered in Full-Width & Height Stretched Container) -->
            <div class="w-full flex-1 flex flex-col items-center justify-center py-12 px-4 text-center space-y-3 my-auto">
                <div class="w-14 h-14 bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-2xl border border-[#1b3bbb]/20 flex items-center justify-center mx-auto shadow-2xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-[#09103c] tracking-wide">Belum Ada Dokumen Notulensi Disahkan</h3>
                <p class="text-xs text-[#5a508f] max-w-md mx-auto font-medium leading-relaxed">
                    Dokumen notulensi rapat yang telah disahkan oleh Pimpinan akan otomatis tersimpan dan dapat diakses di sini.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
