@extends('layouts.app')

@section('title', 'Dashboard Utama')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Grid (Greeting & Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
        
        <!-- Welcome Card -->
        <div class="md:col-span-1 bg-[#2e2552] text-white rounded-[32px] p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
            <!-- Decorative circle overlay -->
            <div class="absolute -top-12 -right-12 w-28 h-28 bg-white/5 rounded-full"></div>
            
            <div class="space-y-2 z-10">
                <span class="text-[10px] font-bold uppercase tracking-widest text-[#8e88dd]">Ringkasan Hari Ini</span>
                <h3 class="text-xl font-black leading-tight">Pantau Agenda Rapat & Notulensi Kerja</h3>
                <p class="text-xs text-[#bda6ff] leading-relaxed">Kelola dan hadiri koordinasi kedinasan Dinkominfo Banyumas secara terpadu.</p>
            </div>
            
            <div class="mt-6 z-10">
                <a href="{{ route('calendar') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-[#2e2552] hover:bg-[#ebe9fe] text-xs font-bold rounded-xl shadow-sm transition-all duration-200">
                    <span>Lihat Kalender Rinci</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Role-Specific KPI Cards -->
        @if(Auth::user()->role === 'staff')
            <!-- Card 1: Week Agendas -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#5a508f] uppercase">Agenda Minggu Ini</span>
                    <div class="p-2 bg-[#8ba0f2]/10 text-[#8ba0f2] rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-4xl font-black text-[#2e2552]">{{ $kpi['week_agendas'] ?? 0 }}</h2>
                    <p class="text-xs text-[#5a508f] mt-1 font-medium">Agenda kegiatan terjadwal yang dapat Anda ikuti</p>
                </div>
            </div>

            <!-- Card 2: Unfilled Presence -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#5a508f] uppercase">Belum Presensi</span>
                    <div class="p-2 bg-rose-50 text-rose-500 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-4xl font-black text-rose-600">{{ $kpi['pending_presence'] ?? 0 }}</h2>
                    <p class="text-xs text-[#5a508f] mt-1 font-medium">Kehadiran rapat yang belum Anda konfirmasi</p>
                </div>
            </div>

        @elseif(Auth::user()->role === 'sekretaris_bidang')
            <!-- Card 1: Bidang Agendas -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#5a508f] uppercase">Agenda Bidang Bulan Ini</span>
                    <div class="p-2 bg-[#bc8bf2]/10 text-[#bc8bf2] rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-4xl font-black text-[#2e2552]">{{ $kpi['bidang_month_agendas'] ?? 0 }}</h2>
                    <p class="text-xs text-[#5a508f] mt-1 font-medium">Agenda yang dikelola oleh Sekretaris Bidang</p>
                </div>
            </div>

            <!-- Card 2: Waiting Review -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#5a508f] uppercase">Menunggu Review Ketua</span>
                    <div class="p-2 bg-amber-50 text-amber-500 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-4xl font-black text-amber-600">{{ $kpi['bidang_pending_reviews'] ?? 0 }}</h2>
                    <p class="text-xs text-[#5a508f] mt-1 font-medium">Notulensi yang diajukan kepada Kepala Bidang</p>
                </div>
            </div>

        @elseif(Auth::user()->role === 'sekretaris_master')
            <!-- Card 1: Master Total Month -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#5a508f] uppercase">Agenda Dinas Bulan Ini</span>
                    <div class="p-2 bg-[#8ba0f2]/10 text-[#8ba0f2] rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-4xl font-black text-[#2e2552]">{{ $kpi['master_month_agendas'] ?? 0 }}</h2>
                    <p class="text-xs text-[#5a508f] mt-1 font-medium">Total seluruh agenda Dinas Kominfo pada bulan ini</p>
                </div>
            </div>

            <!-- Card 2: Master Overdue Alerts -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#5a508f] uppercase">Notulensi Overdue (>3 Hari)</span>
                    <div class="p-2 bg-rose-50 text-rose-500 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-4xl font-black text-rose-600">{{ $kpi['master_overdue_reviews'] ?? 0 }}</h2>
                    <p class="text-xs text-[#5a508f] mt-1 font-medium">Draf notulensi peninjauan pimpinan yang belum disahkan</p>
                </div>
            </div>

        @elseif(Auth::user()->isKetua())
            <!-- Card 1: Ketua Pending Approvals -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#5a508f] uppercase">Notulensi Butuh Pengesahan</span>
                    <div class="p-2 bg-amber-50 text-amber-500 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-4xl font-black text-amber-600">{{ $kpi['ketua_pending_reviews'] ?? 0 }}</h2>
                    <p class="text-xs text-[#5a508f] mt-1 font-medium">Menunggu tanda tangan dan persetujuan dari Anda</p>
                </div>
            </div>

            <!-- Card 2: Master Info / Status -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#5a508f] uppercase">Cakupan Pengawasan</span>
                    <div class="p-2 bg-[#8ba0f2]/10 text-[#8ba0f2] rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-base font-black text-[#2e2552] truncate">
                        {{ Auth::user()->role === 'ketua_master' ? 'Seluruh Kominfo' : (Auth::user()->bidang->singkatan ?? 'Bidang Dinas') }}
                    </h4>
                    <p class="text-xs text-[#5a508f] mt-1 font-medium">Berdasarkan wewenang pengawasan Kepala Dinas / Kepala Bidang</p>
                </div>
            </div>
        @endif

    </div>

    <!-- MAIN TWO COLUMN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- LEFT/MID COLUMN: MONTHLY CALENDAR CARD -->
        <div class="lg:col-span-2 bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm flex flex-col">
            
            <!-- Month selector header -->
            <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-4 mb-6">
                <div>
                    <h2 class="text-lg font-black text-[#2e2552] tracking-wide">{{ $selectedMonth->translatedFormat('F Y') }}</h2>
                    <p class="text-xs text-[#5a508f]">Agenda Kerja Bulanan Dinkominfo</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard', ['month' => $selectedMonth->copy()->subMonth()->format('Y-m')]) }}" 
                       class="p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-[#2e2552] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <a href="{{ route('dashboard', ['month' => now()->format('Y-m')]) }}" 
                       class="px-3.5 py-1.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-xs font-bold text-[#2e2552] transition-colors">
                        Bulan Ini
                    </a>
                    <a href="{{ route('dashboard', ['month' => $selectedMonth->copy()->addMonth()->format('Y-m')]) }}" 
                       class="p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-[#2e2552] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Month grid header -->
            <div class="grid grid-cols-7 gap-2 text-center text-[10px] font-bold text-[#8e88dd] uppercase tracking-wider mb-2">
                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
            </div>

            <!-- Month grid days -->
            <div class="grid grid-cols-7 gap-2 text-xs">
                @foreach($gridDates as $date)
                    @php
                        $dateStr = $date->toDateString();
                        $isCurrentMonth = $date->month === $selectedMonth->month;
                        $isToday = $date->isToday();
                        $dayEvents = $agendasByDate[$dateStr] ?? [];
                    @endphp
                    
                    <!-- Calendar Day Cell with AlpineJS hover popover -->
                    <div x-data="{ open: false }" 
                         @mouseenter="open = true" 
                         @mouseleave="open = false"
                         class="relative min-h-[75px] p-2 bg-[#fcfbff] border border-[#d4d1f5]/30 rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-[#8e88dd]/50 hover:bg-[#f8f7ff]">
                        
                        <!-- Day Number Header -->
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[11px] 
                                {{ $isToday ? 'bg-[#2e2552] text-white px-2 py-0.5 rounded-lg shadow-sm' : ($isCurrentMonth ? 'text-[#2e2552]' : 'text-[#d4d1f5]') }}">
                                {{ $date->day }}
                            </span>
                            @if(count($dayEvents) > 0)
                                <span class="text-[9px] font-black text-[#8e88dd]">({{ count($dayEvents) }})</span>
                            @endif
                        </div>

                        <!-- Dots color wrapper -->
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($dayEvents as $evt)
                                @php
                                    // Categories colors:
                                    // Rapat: Amethyst Purple, Sosialisasi: Periwinkle Blue, Pelatihan: Lime Green, Kegiatan Lainnya: Lavender Gray
                                    $dotColors = [
                                        'rapat' => 'bg-[#bc8bf2]',
                                        'sosialisasi' => 'bg-[#8ba0f2]',
                                        'pelatihan' => 'bg-[#c2f73b]',
                                        'kegiatan_lainnya' => 'bg-[#9f95d9]',
                                    ];
                                    $dotColor = $dotColors[$evt->kategori ?? ''] ?? 'bg-[#9f95d9]';
                                @endphp
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}" title="{{ $evt->judul }}"></span>
                            @endforeach
                        </div>

                        <!-- Click Link overlays whole cell (redirects to weekly grid page) -->
                        <a href="{{ route('calendar', ['date' => $dateStr]) }}" class="absolute inset-0 z-10 rounded-2xl"></a>

                        <!-- AlpineJS Hover Card Popover -->
                        @if(count($dayEvents) > 0)
                            <div x-show="open" 
                                 x-cloak 
                                 x-transition
                                 class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 w-60 bg-[#2e2552] text-white p-3 rounded-2xl shadow-2xl z-30 text-[10px] space-y-2 pointer-events-none border border-white/10">
                                <div class="font-bold border-b border-white/10 pb-1 flex justify-between">
                                    <span>Agenda Rapat</span>
                                    <span>{{ $date->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                                    @foreach($dayEvents as $evt)
                                        <div class="leading-tight">
                                            <span class="text-[#bda6ff] font-bold">[{{ $evt->jam_mulai }}]</span>
                                            <span class="font-semibold ml-0.5">{{ $evt->judul }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            <!-- Color code legend -->
            <div class="flex flex-wrap items-center gap-4 mt-6 border-t border-[#d4d1f5]/40 pt-4 text-[10px] font-bold uppercase tracking-wider text-[#5a508f]">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#bc8bf2]"></span> Rapat</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#8ba0f2]"></span> Sosialisasi</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#c2f73b]"></span> Pelatihan</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#9f95d9]"></span> Kegiatan Lainnya</span>
            </div>

        </div>

        <!-- RIGHT COLUMN: ACTIONABLE HIGHLIGHTS & HISTORY -->
        <div class="space-y-6">
            
            <!-- Highlights Panel -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Perhatian Khusus</h3>
                
                <div class="space-y-3">
                    @forelse($highlights as $hl)
                        <div class="p-3 bg-[#f8f7ff] border border-amber-300/30 rounded-2xl flex flex-col gap-2">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-[11px] text-[#5a508f] leading-relaxed font-semibold">{{ $hl['text'] }}</p>
                            </div>
                            @if(isset($hl['url']))
                                <a href="{{ $hl['url'] }}" 
                                   class="self-end px-3 py-1 bg-[#2e2552] hover:bg-[#3d326a] text-white text-[9px] font-bold rounded-lg transition-colors">
                                    {{ $hl['action_text'] }}
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6 italic">Tidak ada tindakan mendesak hari ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity History Card -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Riwayat Kegiatan</h3>
                    <a href="{{ route('riwayat') }}" class="text-[10px] text-[#8e88dd] hover:text-[#2e2552] font-bold transition-colors">Lihat Semua</a>
                </div>

                <div class="space-y-3">
                    @forelse($riwayatRingkas as $rw)
                        <div class="p-3 bg-[#fcfbff] border border-[#d4d1f5]/20 rounded-2xl flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-[#2e2552] truncate">{{ $rw->judul }}</h4>
                                <p class="text-[9px] text-[#5a508f] mt-0.5">
                                    {{ $rw->tanggal->translatedFormat('d M Y') }} &bull; {{ substr($rw->jam_mulai, 0, 5) }}
                                </p>
                            </div>
                            <!-- Status Presence badge -->
                            <div>
                                @if($rw->status_kehadiran === 'hadir')
                                    <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100 font-semibold">Hadir</span>
                                @elseif($rw->status_kehadiran === 'izin')
                                    <span class="text-[9px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-100 font-semibold">Izin</span>
                                @elseif($rw->status_kehadiran === 'sakit')
                                    <span class="text-[9px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-100 font-semibold">Sakit</span>
                                @else
                                    <span class="text-[9px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100 font-semibold">-</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6 italic">Belum ada riwayat kegiatan.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
