@extends('layouts.app')

@section('title', 'Kegiatan Hari Ini')

@section('content')
<div x-data="{ 
    activeTab: 'semua', 
    tvMode: false,
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
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-400/20 hover:bg-amber-400/30 text-amber-300 border border-amber-400/40 rounded-full text-[10px] font-extrabold tracking-wider uppercase transition-all cursor-pointer shadow-xs active:scale-95">
                    <svg class="w-3.5 h-3.5 text-amber-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
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
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-2.5 text-center min-w-[90px]">
                <div class="text-xs font-bold text-rose-300 uppercase tracking-wider">Berlangsung</div>
                <div class="text-lg sm:text-xl font-black text-white">{{ $ongoingAgendas->count() }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-2.5 text-center min-w-[90px]">
                <div class="text-xs font-bold text-amber-300 uppercase tracking-wider">Mendatang</div>
                <div class="text-lg sm:text-xl font-black text-white">{{ $upcomingAgendas->count() }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-4 py-2.5 text-center min-w-[90px]">
                <div class="text-xs font-bold text-emerald-300 uppercase tracking-wider">Selesai</div>
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
                :class="activeTab === 'ongoing' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-white text-slate-600 hover:bg-rose-50 border border-slate-200/80'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            <span>Sedang Berlangsung</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-800 font-black">{{ $ongoingAgendas->count() }}</span>
        </button>

        <button @click="activeTab = 'upcoming'"
                :class="activeTab === 'upcoming' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-white text-slate-600 hover:bg-amber-50 border border-slate-200/80'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
            <span>Mendatang</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800 font-black">{{ $upcomingAgendas->count() }}</span>
        </button>

        <button @click="activeTab = 'completed'"
                :class="activeTab === 'completed' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-emerald-50 border border-slate-200/80'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
            <span>Selesai</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-black">{{ $completedAgendas->count() }}</span>
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
                    
                    $statusColor = $isOngoing ? 'border-rose-500 bg-rose-50/30' : ($isUpcoming ? 'border-amber-400 bg-amber-50/20' : 'border-emerald-400 bg-white');
                    $statusBadge = $isOngoing 
                        ? 'bg-rose-100 text-rose-700 border-rose-200' 
                        : ($isUpcoming ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200');
                    $statusText = $isOngoing ? '🔴 Sedang Berlangsung' : ($isUpcoming ? '🟡 Mendatang' : '🟢 Selesai');

                    $isAbsen = $agenda->presensis->contains('user_id', Auth::id());
                @endphp

                <div x-show="activeTab === 'semua' || activeTab === '{{ $tabCategory }}'" x-transition
                     class="bg-white rounded-2xl md:rounded-[28px] p-5 sm:p-6 border-l-4 {{ $statusColor }} border-slate-200/80 shadow-xs hover:shadow-md transition-all space-y-4">
                    
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Time Badge -->
                            <span class="px-3 py-1 rounded-xl bg-slate-100 text-[#09103c] font-mono text-xs font-bold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i') }} WIB</span>
                            </span>

                            <!-- Status Badge -->
                            <span class="px-3 py-1 rounded-xl text-xs font-extrabold border {{ $statusBadge }}">
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
                                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
         class="fixed inset-0 z-[9999] bg-[#050817] text-white flex flex-col p-6 lg:p-10 overflow-hidden select-none">
        
        <!-- TV Top Header -->
        <div class="flex items-center justify-between border-b border-white/10 pb-6 shrink-0">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Banyumas" class="h-14 lg:h-16 w-auto">
                <div>
                    <h2 class="text-lg lg:text-2xl font-black text-white tracking-widest uppercase">PEMERINTAH KABUPATEN BANYUMAS</h2>
                    <h1 class="text-sm lg:text-lg font-bold text-amber-400 tracking-wider">DINAS KOMUNIKASI DAN INFORMATIKA</h1>
                </div>
            </div>

            <!-- TV Center Title -->
            <div class="hidden lg:flex flex-col items-center">
                <div class="px-4 py-1 bg-indigo-600/30 border border-indigo-400/30 rounded-full text-xs font-bold text-indigo-300 tracking-widest uppercase flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    PAPAN INFORMASI RITME AGENDAPARAT
                </div>
                <div class="text-2xl font-black tracking-tight text-white uppercase">AGENDA HARI INI</div>
            </div>

            <!-- TV Right Clock & Exit -->
            <div class="flex items-center gap-4">
                <div class="text-right bg-white/5 border border-white/10 rounded-2xl px-5 py-2.5 backdrop-blur-md">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="currentDate"></div>
                    <div class="text-2xl lg:text-3xl font-black text-amber-400 font-mono tracking-tight" x-text="currentTime"></div>
                </div>

                <button @click="toggleTvMode()" type="button" 
                        class="p-3 bg-white/10 hover:bg-rose-600 text-white rounded-2xl transition-all border border-white/20 hover:border-rose-500 cursor-pointer shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- TV Main Content Display Grid -->
        <div class="flex-1 min-h-0 py-6 overflow-y-auto space-y-5 scrollbar-none">
            @if($agendas->isEmpty())
                <div class="h-full flex flex-col items-center justify-center text-center space-y-4">
                    <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center border border-white/10 text-slate-500">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-white">TIDAK ADA AGENDA RAPAT HARI INI</h3>
                    <p class="text-base text-slate-400 max-w-lg">Seluruh kegiatan dinas berjalan lancar. Belum ada jadwal rapat baru untuk hari ini.</p>
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

                            $tvCardBorder = $isOngoing 
                                ? 'border-rose-500 bg-rose-950/30 shadow-[0_0_30px_rgba(244,63,94,0.25)]' 
                                : ($isUpcoming ? 'border-amber-500/60 bg-amber-950/20' : 'border-emerald-500/40 bg-emerald-950/10');
                            
                            $tvStatusBadge = $isOngoing
                                ? 'bg-rose-500 text-white animate-pulse'
                                : ($isUpcoming ? 'bg-amber-500 text-slate-950' : 'bg-emerald-500 text-slate-950');

                            $tvStatusLabel = $isOngoing ? '🔴 SEDANG BERLANGSUNG' : ($isUpcoming ? '🟡 MENDATANG' : '🟢 SELESAI');
                        @endphp

                        <div class="bg-white/5 backdrop-blur-xl border-2 {{ $tvCardBorder }} rounded-3xl p-6 lg:p-8 flex flex-col justify-between gap-6 transition-all">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <!-- Time Range Badge -->
                                    <div class="px-4 py-2 rounded-2xl bg-white/10 text-amber-300 font-mono text-base lg:text-lg font-black tracking-wide border border-white/10 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <h3 class="text-xl lg:text-2xl font-black text-white leading-snug tracking-tight">
                                    {{ $agenda->judul }}
                                </h3>

                                <!-- Location & Attendance -->
                                <div class="space-y-2 pt-2 text-sm lg:text-base font-semibold text-slate-300">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>Lokasi / Ruangan: <strong class="text-white font-bold">{{ $agenda->lokasi }}</strong></span>
                                    </div>

                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span>Kehadiran Peserta: <strong class="text-emerald-400 font-bold">{{ $agenda->presensis->count() }} Terpresensi</strong></span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs lg:text-sm font-bold text-slate-400">
                                <span>Kategori: <strong class="text-indigo-300 uppercase">{{ $agenda->kategori }}</strong></span>
                                <span>Akses: <strong class="text-purple-300">{{ in_array('semua_orang', $agenda->hak_akses) ? 'Lintas Dinas' : 'Internal Bidang' }}</strong></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- TV Bottom Running Text Marquee -->
        <div class="shrink-0 bg-white/5 border-t border-white/10 -mx-6 lg:-mx-10 -mb-6 lg:-mb-10 px-6 py-3 flex items-center gap-4 overflow-hidden">
            <div class="px-3 py-1 rounded-lg bg-rose-600 text-white font-extrabold text-xs uppercase tracking-wider shrink-0 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>PENGUMUMAN</span>
            </div>
            <div class="overflow-hidden whitespace-nowrap w-full">
                <p class="inline-block animate-marquee text-sm font-bold text-slate-300 tracking-wide">
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
