@extends('layouts.app')

@section('title', 'Kegiatan Hari Ini')

@section('content')
<div x-data="{ 
    activeTab: 'semua', 
    tvMode: false,
    tvTheme: 'cerah',
    currentTime: '',
    currentDate: '',
    toggleTvMode() {
        this.tvMode = !this.tvMode;
        if (this.tvMode) {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen().catch(() => {});
            }
        } else {
            if (document.fullscreenElement && document.exitFullscreen) {
                document.exitFullscreen().catch(() => {});
            }
        }
    },
    updateClock() {
        const now = new Date();
        this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
}" 
x-init="
    updateClock(); 
    setInterval(() => updateClock(), 1000);
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            tvMode = false;
        }
    });
"
class="w-full flex flex-col gap-5 select-none">
    
    <!-- Hero Header Card -->
    <div class="bg-gradient-to-br from-[#09103c] via-[#1b3bbb] to-[#0b1554] rounded-2xl md:rounded-[32px] p-5 sm:p-7 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <!-- Glow Overlay -->
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-2 max-w-xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-[10px] font-bold tracking-wider uppercase text-emerald-300 border border-white/15">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Realtime Monitoring</span>
                </span>

                <!-- TV DISPLAY BUTTON -->
                <button @click="toggleTvMode()" 
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black rounded-full text-[10px] tracking-wider uppercase transition-all cursor-pointer shadow-md hover:scale-105 active:scale-95 border border-amber-300">
                    <svg class="w-3.5 h-3.5 text-slate-950 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>📺 Mode Papan TV (Full Screen)</span>
                </button>
            </div>

            <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight leading-tight">AGENDA HARI INI</h1>
            <p class="text-xs sm:text-sm text-indigo-100 font-medium leading-relaxed">
                Pantau seluruh kegiatan dinas & rapat yang dijadwalkan hari ini ({{ \Carbon\Carbon::today()->locale('id')->translatedFormat('l, d F Y') }}).
            </p>
        </div>

        <!-- Realtime Live Status Stats -->
        <div class="relative z-10 flex flex-wrap items-center gap-2.5 sm:gap-3 shrink-0">
            <div class="bg-emerald-500/20 backdrop-blur-md border border-emerald-400/30 rounded-2xl px-4 py-2.5 text-center min-w-[95px]">
                <div class="text-xs font-bold text-emerald-300 uppercase tracking-wider">Berlangsung</div>
                <div class="text-lg sm:text-xl font-black text-white">{{ $ongoingAgendas->count() }}</div>
            </div>
            <div class="bg-indigo-500/20 backdrop-blur-md border border-indigo-400/30 rounded-2xl px-4 py-2.5 text-center min-w-[95px]">
                <div class="text-xs font-bold text-indigo-200 uppercase tracking-wider">Mendatang</div>
                <div class="text-lg sm:text-xl font-black text-white">{{ $upcomingAgendas->count() }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-2.5 text-center min-w-[95px]">
                <div class="text-xs font-bold text-slate-300 uppercase tracking-wider">Selesai</div>
                <div class="text-lg sm:text-xl font-black text-white">{{ $completedAgendas->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <button @click="activeTab = 'semua'"
                :class="activeTab === 'semua' ? 'bg-[#1b3bbb] text-white shadow-md shadow-[#1b3bbb]/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
            <span>Semua Kegiatan</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/20 font-black">{{ $agendas->count() }}</span>
        </button>

        <button @click="activeTab = 'ongoing'"
                :class="activeTab === 'ongoing' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-emerald-50 border border-slate-200/80'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            <span>Sedang Berlangsung</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-black">{{ $ongoingAgendas->count() }}</span>
        </button>

        <button @click="activeTab = 'upcoming'"
                :class="activeTab === 'upcoming' ? 'bg-[#1b3bbb] text-white shadow-md shadow-[#1b3bbb]/20' : 'bg-white text-slate-600 hover:bg-indigo-50 border border-slate-200/80'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
            <span>Mendatang</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-800 font-black">{{ $upcomingAgendas->count() }}</span>
        </button>

        <button @click="activeTab = 'completed'"
                :class="activeTab === 'completed' ? 'bg-slate-700 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
            <span>Selesai</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-700 font-black">{{ $completedAgendas->count() }}</span>
        </button>
    </div>

    <!-- Agenda Cards List -->
    @if($agendas->isEmpty())
        <div class="bg-white rounded-2xl md:rounded-[32px] p-8 sm:p-12 text-center border border-slate-200/80 space-y-3">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-base font-extrabold text-[#09103c]">Tidak Ada Agenda Hari Ini</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">Belum ada agenda rapat atau kegiatan yang dijadwalkan untuk hari ini ({{ \Carbon\Carbon::today()->locale('id')->translatedFormat('l, d F Y') }}).</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($agendas as $agenda)
                @php
                    $nowTime = \Carbon\Carbon::now()->format('H:i:s');
                    $startTime = \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i:s');
                    $endTime = \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i:s');
                    
                    $isOngoing = $nowTime >= $startTime && $nowTime <= $endTime;
                    $isUpcoming = $nowTime < $startTime;
                    $isCompleted = $nowTime > $endTime;

                    $tabCategory = $isOngoing ? 'ongoing' : ($isUpcoming ? 'upcoming' : 'completed');
                    
                    // Ongoing = GREEN (Emerald), Upcoming = BLUE (Indigo), Completed = SLATE (Grey)
                    $statusColor = $isOngoing ? 'border-emerald-500 bg-emerald-50/20' : ($isUpcoming ? 'border-[#1b3bbb] bg-indigo-50/10' : 'border-slate-300 bg-white');
                    $statusBadge = $isOngoing 
                        ? 'bg-emerald-100 text-emerald-800 border-emerald-300 font-extrabold' 
                        : ($isUpcoming ? 'bg-indigo-100 text-indigo-800 border-indigo-200 font-extrabold' : 'bg-slate-100 text-slate-600 border-slate-200 font-semibold');
                    $statusText = $isOngoing ? '🟢 SEDANG BERLANGSUNG' : ($isUpcoming ? '🔵 MENDATANG' : '⚪ SELESAI');

                    $isAbsen = $agenda->presensis->contains('user_id', Auth::id());
                @endphp

                <div x-show="activeTab === 'semua' || activeTab === '{{ $tabCategory }}'" x-transition
                     class="bg-white rounded-2xl md:rounded-[28px] p-5 sm:p-6 border-l-4 {{ $statusColor }} border-slate-200/80 shadow-xs hover:shadow-md transition-all space-y-4">
                    
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Time Badge -->
                            <span class="px-3 py-1 rounded-xl bg-slate-100 text-[#09103c] font-mono text-xs font-bold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#1b3bbb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i') }} WIB</span>
                            </span>

                            <!-- Status Badge -->
                            <span class="px-3 py-1 rounded-xl text-xs border {{ $statusBadge }}">
                                {{ $statusText }}
                            </span>

                            <!-- Kategori Badge -->
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ strtoupper($agenda->kategori) }}
                            </span>
                        </div>

                        <!-- Scope Access Badge -->
                        @if(in_array('semua_orang', $agenda->hak_akses))
                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-full px-2.5 py-0.5">
                                🌐 Lintas Dinas (Semua)
                            </span>
                        @else
                            <span class="text-[10px] font-bold text-purple-600 bg-purple-50 border border-purple-100 rounded-full px-2.5 py-0.5">
                                🏢 Bidang Khusus
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-base sm:text-lg font-black text-[#09103c] hover:text-[#1b3bbb] transition-colors leading-snug">
                            <a href="{{ route('agenda.show', $agenda->id) }}">{{ $agenda->judul }}</a>
                        </h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium text-slate-600">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>Lokasi: <strong class="text-slate-800">{{ $agenda->lokasi }}</strong></span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#1b3bbb] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span>Peserta Hadir: <strong class="text-emerald-600 font-bold">{{ $agenda->presensis->count() }} Orang</strong></span>
                            </div>
                        </div>

                        @if(!empty($agenda->deskripsi))
                            <p class="text-xs text-slate-500 line-clamp-2 pt-1 leading-relaxed">
                                {{ $agenda->deskripsi }}
                            </p>
                        @endif
                    </div>

                    <!-- Action Footer Links -->
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            @if($isAbsen)
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold flex items-center gap-1">
                                    ✓ Sudah Absen
                                </span>
                            @else
                                <a href="{{ route('agenda.show', $agenda->id) }}" class="px-3 py-1.5 bg-[#1b3bbb] hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1">
                                    <span>Presensi Mandiri</span>
                                </a>
                            @endif

                            @if($agenda->notulensi)
                                <a href="{{ route('notulensi.review', $agenda->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                                    <span>📄 Notulensi ({{ ucfirst($agenda->notulensi->status) }})</span>
                                </a>
                            @endif
                        </div>

                        <a href="{{ route('agenda.show', $agenda->id) }}" class="text-xs font-bold text-[#1b3bbb] hover:underline flex items-center gap-1">
                            <span>Detail Lengkap Agenda &rarr;</span>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <!-- ========================================== -->
    <!-- 📺 FULL SCREEN TV DISPLAY MODE OVERLAY    -->
    <!-- ========================================== -->
    <div x-show="tvMode" x-cloak 
         :class="tvTheme === 'cerah' 
            ? 'bg-gradient-to-br from-[#eef2ff] via-[#f8fafc] to-[#e0e7ff] text-[#09103c]' 
            : 'bg-gradient-to-br from-[#090d1a] via-[#0f172a] to-[#1e1b4b] text-white'"
         class="fixed inset-0 z-[9999] flex flex-col p-6 lg:p-10 overflow-hidden select-none transition-colors duration-300">
        
        <!-- TV Top Header Bar -->
        <div :class="tvTheme === 'cerah' 
                ? 'bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white border-white/20' 
                : 'bg-[#131b2e] text-white border-slate-800'"
             class="flex items-center justify-between rounded-3xl p-5 lg:p-6 shadow-xl border shrink-0 transition-all">
            
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Banyumas" class="h-14 lg:h-16 w-auto drop-shadow-md">
                <div>
                    <h2 class="text-base lg:text-xl font-extrabold text-amber-300 tracking-wider uppercase">PEMERINTAH KABUPATEN BANYUMAS</h2>
                    <h1 class="text-lg lg:text-2xl font-black text-white tracking-wide">DINAS KOMUNIKASI DAN INFORMATIKA</h1>
                </div>
            </div>

            <!-- TV Center Title & Theme Switcher -->
            <div class="hidden lg:flex flex-col items-center">
                <div class="px-4 py-1 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-xs font-extrabold text-emerald-300 tracking-widest uppercase flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    PAPAN INFORMASI DIGITAL REALTIME
                </div>
                <div class="text-2xl lg:text-3xl font-black tracking-tight text-white uppercase drop-shadow-md">AGENDA HARI INI</div>
            </div>

            <!-- TV Right Controls: Theme Toggle, Clock, Exit -->
            <div class="flex items-center gap-3">
                <!-- Theme Switcher Button -->
                <button @click="tvTheme = (tvTheme === 'cerah' ? 'gelap' : 'cerah')" 
                        type="button" 
                        class="px-3.5 py-2 bg-white/10 hover:bg-white/20 rounded-2xl border border-white/20 text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                    <span x-text="tvTheme === 'cerah' ? '🌙 Mode Gelap' : '☀️ Mode Cerah'"></span>
                </button>

                <!-- Clock Badge -->
                <div class="text-right bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-4 py-2 shadow-lg">
                    <div class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider" x-text="currentDate"></div>
                    <div class="text-xl lg:text-2xl font-black text-amber-300 font-mono tracking-tight" x-text="currentTime"></div>
                </div>

                <!-- Exit Button -->
                <button @click="toggleTvMode()" type="button" 
                        class="p-3 bg-white/10 hover:bg-rose-600 text-white rounded-2xl transition-all border border-white/20 hover:border-rose-500 cursor-pointer shadow-lg active:scale-95">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- TV Main Content Display Grid -->
        <div class="flex-1 min-h-0 py-6 overflow-y-auto space-y-6 scrollbar-none">
            @if($agendas->isEmpty())
                <div class="h-full flex flex-col items-center justify-center text-center space-y-4">
                    <div :class="tvTheme === 'cerah' ? 'bg-white text-slate-400 border-slate-200' : 'bg-white/5 text-slate-400 border-white/10'" 
                         class="w-24 h-24 rounded-full flex items-center justify-center border shadow-lg">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 :class="tvTheme === 'cerah' ? 'text-[#09103c]' : 'text-white'" class="text-2xl lg:text-3xl font-black">TIDAK ADA AGENDA RAPAT HARI INI</h3>
                    <p :class="tvTheme === 'cerah' ? 'text-slate-500' : 'text-indigo-200'" class="text-base max-w-lg font-medium">Seluruh kegiatan dinas berjalan lancar. Belum ada jadwal rapat baru untuk hari ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($agendas as $agenda)
                        @php
                            $nowTime = \Carbon\Carbon::now()->format('H:i:s');
                            $startTime = \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i:s');
                            $endTime = \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i:s');
                            
                            $isOngoing = $nowTime >= $startTime && $nowTime <= $endTime;
                            $isUpcoming = $nowTime < $startTime;

                            // Ongoing = GREEN (Emerald), Upcoming = ROYAL BLUE (Indigo), Completed = SLATE (Grey)
                            $tvCardStyleCerah = $isOngoing 
                                ? 'bg-white border-4 border-emerald-500 ring-4 ring-emerald-500/20 shadow-[0_10px_35px_rgba(16,185,129,0.25)] text-[#09103c]' 
                                : ($isUpcoming ? 'bg-white border-2 border-[#1b3bbb] shadow-xl text-[#09103c]' : 'bg-white/80 border-2 border-slate-300 opacity-85 shadow-sm text-[#09103c]');
                            
                            $tvCardStyleGelap = $isOngoing 
                                ? 'bg-[#131c2e] border-4 border-emerald-500 ring-4 ring-emerald-500/30 shadow-[0_10px_35px_rgba(16,185,129,0.35)] text-white' 
                                : ($isUpcoming ? 'bg-[#131c2e] border-2 border-[#1b3bbb] shadow-xl text-white' : 'bg-[#131c2e]/70 border-2 border-slate-800 opacity-75 text-slate-300');

                            $tvStatusBadge = $isOngoing
                                ? 'bg-emerald-600 text-white animate-pulse font-black'
                                : ($isUpcoming ? 'bg-[#1b3bbb] text-white font-black' : 'bg-slate-200 text-slate-800 font-bold');

                            $tvStatusLabel = $isOngoing ? '🟢 SEDANG BERLANGSUNG' : ($isUpcoming ? '🔵 MENDATANG' : '⚪ SELESAI');
                            $timeBadgeBg = $isOngoing 
                                ? 'bg-emerald-50 text-emerald-900 border-emerald-200' 
                                : ($isUpcoming ? 'bg-indigo-50 text-[#1b3bbb] border-indigo-200' : 'bg-slate-100 text-slate-700 border-slate-200');
                        @endphp

                        <div :class="tvTheme === 'cerah' ? '{{ $tvCardStyleCerah }}' : '{{ $tvCardStyleGelap }}'" 
                             class="rounded-3xl p-6 lg:p-8 flex flex-col justify-between gap-6 transition-all">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <!-- Time Range Badge -->
                                    <div class="px-4 py-2 rounded-2xl {{ $timeBadgeBg }} font-mono text-base lg:text-lg font-black tracking-wide border flex items-center gap-2">
                                        <svg class="w-5 h-5 text-[#1b3bbb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i') }} WIB</span>
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="px-4 py-1.5 rounded-2xl text-xs lg:text-sm font-extrabold tracking-wider uppercase {{ $tvStatusBadge }}">
                                        {{ $tvStatusLabel }}
                                    </div>
                                </div>

                                <!-- Agenda Title -->
                                <h3 :class="tvTheme === 'cerah' ? 'text-[#09103c]' : 'text-white'" class="text-xl lg:text-2xl font-black leading-snug tracking-tight">
                                    {{ $agenda->judul }}
                                </h3>

                                <!-- Location & Attendance -->
                                <div class="space-y-2 pt-2 text-sm lg:text-base font-bold" :class="tvTheme === 'cerah' ? 'text-slate-600' : 'text-slate-300'">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>Lokasi / Ruangan: <strong :class="tvTheme === 'cerah' ? 'text-[#09103c]' : 'text-white'" class="font-extrabold">{{ $agenda->lokasi }}</strong></span>
                                    </div>

                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span>Kehadiran Peserta: <strong class="text-emerald-600 font-extrabold">{{ $agenda->presensis->count() }} Terpresensi</strong></span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-200 flex items-center justify-between text-xs lg:text-sm font-bold text-slate-500">
                                <span>Kategori: <strong class="text-[#1b3bbb] uppercase font-extrabold">{{ $agenda->kategori }}</strong></span>
                                <span>Akses: <strong class="text-purple-700 font-extrabold">{{ in_array('semua_orang', $agenda->hak_akses) ? 'Lintas Dinas' : 'Internal Bidang' }}</strong></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- TV Bottom Running Text Marquee -->
        <div :class="tvTheme === 'cerah' ? 'bg-[#09103c] text-white shadow-xl' : 'bg-[#131b2e] text-white border-t border-slate-800'"
             class="shrink-0 -mx-6 lg:-mx-10 -mb-6 lg:-mb-10 px-6 py-3.5 flex items-center gap-4 overflow-hidden rounded-t-2xl transition-all">
            <div class="px-3.5 py-1 rounded-xl bg-emerald-600 text-white font-black text-xs uppercase tracking-wider shrink-0 flex items-center gap-1.5 shadow-md">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>PENGUMUMAN</span>
            </div>
            <div class="overflow-hidden whitespace-nowrap w-full">
                <p class="inline-block animate-marquee text-sm lg:text-base font-bold text-white tracking-wide">
                    📢 Selamat Datang di Dinas Komunikasi dan Informatika Kabupaten Banyumas &nbsp;•&nbsp; Harap melakukan Presensi Mandiri pada aplikasi Agendaris sebelum rapat dimulai &nbsp;•&nbsp; Jagalah ketertiban dan kebersihan ruang rapat demi kenyamanan bersama &nbsp;•&nbsp; Terima kasih.
                </p>
            </div>
        </div>

    </div>

</div>

<!-- Marquee Animation Style -->
<style>
    @keyframes marquee {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
    .animate-marquee {
        animation: marquee 25s linear infinite;
    }
</style>
@endsection
