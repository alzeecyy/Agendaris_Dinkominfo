@extends('layouts.app')

@section('title', 'Kalender Rinci')

@section('content')
<div x-data="{ 
    openAddModal: false, 
    selectedDate: '{{ $selectedDate->toDateString() }}', 
    selectedTime: '07:15', 
    kategori: 'rapat',
    tempat: 'Ruang Rapat Kartini',
    tempatLainnya: '',
    get combinedLokasi() {
        return this.tempat === 'Lainnya' ? this.tempatLainnya : this.tempat;
    }
}" class="h-full flex flex-col xl:flex-row gap-6">
    
    <!-- LEFT PANEL: Mini Calendar & Quick Add -->
    <div class="w-full xl:w-80 space-y-6 shrink-0">
        
        <!-- Mini Calendar Card -->
        <div class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4 border-b border-[#d4d1f5]/30 pb-2">
                <a href="{{ route('calendar', ['date' => $selectedDate->copy()->subMonth()->startOfMonth()->toDateString()]) }}" 
                   class="p-1.5 hover:bg-[#8e88dd]/20 rounded-xl text-[#2e2552] transition-colors"
                   title="Bulan Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h3 class="text-xs font-black uppercase tracking-wider text-[#2e2552]">
                    {{ $selectedDate->translatedFormat('F Y') }}
                </h3>
                <a href="{{ route('calendar', ['date' => $selectedDate->copy()->addMonth()->startOfMonth()->toDateString()]) }}" 
                   class="p-1.5 hover:bg-[#8e88dd]/20 rounded-xl text-[#2e2552] transition-colors"
                   title="Bulan Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            
            <!-- Calendar Days Header -->
            <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-[#5a508f] mb-2">
                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span class="text-indigo-500 font-extrabold">Sab</span><span class="text-rose-500 font-extrabold">Min</span>
            </div>
            
            <!-- Calendar Grid Calculations -->
            @php
                $startOfMonth = $selectedDate->copy()->startOfMonth();
                $endOfMonth = $selectedDate->copy()->endOfMonth();
                
                // Adjust start of month to Monday
                $startDayOfWeek = $startOfMonth->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
                $calendarStart = $startOfMonth->copy()->subDays($startDayOfWeek - 1);
                
                // Adjust end of month to Sunday
                $endDayOfWeek = $endOfMonth->dayOfWeekIso;
                $calendarEnd = $endOfMonth->copy()->addDays(7 - $endDayOfWeek);
                
                // Fetch dates with events to display dots
                $datesWithEvents = [];
                foreach ($agendasByDate as $dateStr => $events) {
                    if (count($events) > 0) {
                        $datesWithEvents[] = $dateStr;
                    }
                }
            @endphp
            
            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                @php
                    $currentDay = $calendarStart->copy();
                @endphp
                @while ($currentDay->lte($calendarEnd))
                    @php
                        $isCurrentMonth = $currentDay->month === $selectedDate->month;
                        $isToday = $currentDay->isToday();
                        $isSelected = $currentDay->isSameDay($selectedDate);
                        $hasEvent = in_array($currentDay->toDateString(), $datesWithEvents);
                        $isSunday = $currentDay->isSunday();
                        $isSaturday = $currentDay->isSaturday();
                    @endphp
                    <a href="{{ route('calendar', ['date' => $currentDay->toDateString()]) }}" 
                       class="relative p-2 rounded-xl flex items-center justify-center font-medium transition-all duration-150 hover:bg-[#8e88dd]/20
                       {{ $isSelected ? 'bg-[#2e2552] text-white font-bold shadow-md shadow-[#2e2552]/20' : '' }}
                       {{ !$isSelected && $isToday ? 'border border-[#8e88dd]/50 text-[#2e2552] font-semibold' : '' }}
                       {{ !$isSelected && !$isToday && $isCurrentMonth ? ($isSunday ? 'text-rose-600 font-bold' : ($isSaturday ? 'text-indigo-600 font-bold' : 'text-[#5a508f]')) : '' }}
                       {{ !$isSelected && !$isToday && !$isCurrentMonth ? ($isSunday ? 'text-rose-300' : ($isSaturday ? 'text-indigo-300' : 'text-[#d4d1f5]')) : '' }}">
                        <span>{{ $currentDay->day }}</span>
                        @if($hasEvent && !$isSelected)
                            <span class="absolute bottom-1 w-1 h-1 bg-[#8e88dd] rounded-full"></span>
                        @endif
                    </a>
                    @php
                        $currentDay->addDay();
                    @endphp
                @endwhile
            </div>
        </div>

        <!-- Today's Highlights Panel -->
        <div class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Kegiatan Hari Ini</h3>
                <span class="text-[10px] bg-[#2e2552]/10 text-[#2e2552] px-2.5 py-0.5 rounded-full border border-[#2e2552]/20 font-bold">
                    {{ count($todayAgendas) }} Agenda
                </span>
            </div>
            
            <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                @forelse($todayAgendas as $ta)
                    <div class="p-3 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl hover:border-[#8e88dd]/40 transition-all duration-200">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] text-[#5a508f] font-bold">{{ substr($ta->jam_mulai, 0, 5) }} - {{ substr($ta->jam_selesai, 0, 5) }}</span>
                            @if($ta->singkatan_bidang === 'Semua')
                                <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold">Semua</span>
                            @else
                                <span class="text-[9px] px-2 py-0.5 rounded bg-[#2e2552]/10 text-[#2e2552] font-semibold">{{ $ta->singkatan_bidang }}</span>
                            @endif
                        </div>
                        <h4 class="text-xs font-bold text-[#2e2552] mt-1.5 line-clamp-1">{{ $ta->judul }}</h4>
                        <p class="text-[10px] text-[#5a508f] mt-0.5 truncate flex items-center gap-1">
                            <svg class="w-3 h-3 text-[#8e88dd]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>{{ $ta->lokasi }}</span>
                        </p>
                        @if($ta->has_access)
                            <a href="{{ route('agenda.show', $ta->id) }}" class="inline-flex items-center gap-1 text-[9px] text-[#8e88dd] hover:text-[#2e2552] font-semibold mt-2 group">
                                <span>Buka Detail</span>
                                <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6 italic">Tidak ada agenda untuk hari ini.</p>
                @endforelse
            </div>

            <!-- Quick Add Agenda Button (Secretaries only) -->
            @if(Auth::user()->isSekretarisMaster() || Auth::user()->isSekretarisBidang())
                <button @click="openAddModal = true; selectedDate = '{{ $selectedDate->toDateString() }}'; selectedTime = '07:15'" 
                        class="w-full py-3 bg-[#2e2552] hover:bg-[#3d326a] active:scale-[0.98] text-white font-bold rounded-2xl text-xs transition-all duration-200 shadow-md shadow-[#2e2552]/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Agenda Baru</span>
                </button>
            @endif
        </div>
    </div>

    <!-- RIGHT PANEL: 7-Day Weekly Agenda Grid -->
    <div class="flex-1 bg-white border border-[#d4d1f5]/60 rounded-3xl p-6 shadow-sm flex flex-col overflow-x-auto">
        <!-- Calendar Navigation Header -->
        <div class="flex items-center justify-between mb-6 gap-4 border-b border-[#d4d1f5]/40 pb-4">
            <div>
                <h2 class="text-lg font-bold text-[#2e2552] tracking-wide">Kalender Rinci Mingguan</h2>
                <p class="text-xs text-[#5a508f] mt-0.5">Menampilkan jam kerja resmi dinas (07:15 - 15:30 WIB)</p>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('calendar', ['date' => $selectedDate->copy()->subWeek()->toDateString()]) }}" 
                   class="p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-[#2e2552] transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <a href="{{ route('calendar', ['date' => now()->toDateString()]) }}" 
                   class="px-4 py-2 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-xs font-semibold text-[#2e2552] transition-all duration-200">
                    Minggu Ini
                </a>
                <a href="{{ route('calendar', ['date' => $selectedDate->copy()->addWeek()->toDateString()]) }}" 
                   class="p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-[#2e2552] transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Weekly Grid Layout -->
        <div class="flex-1 min-w-[800px] flex flex-col relative" style="height: 660px;">
            <!-- Dates columns header -->
            <div class="grid grid-cols-8 border-b border-[#d4d1f5]/40 pb-3 relative z-0">
                <!-- Time axes column -->
                <div class="text-center text-xs font-bold text-[#5a508f] flex items-center justify-center">Waktu</div>
                <!-- 7 days columns -->
                @foreach($dates as $date)
                    @php
                        $isDateSelected = $date->isSameDay($selectedDate);
                        $isDateToday = $date->isToday();
                        $isSunday = $date->isSunday();
                        $isSaturday = $date->isSaturday();
                    @endphp
                    <div class="text-center flex flex-col items-center justify-center">
                        <span class="text-[9px] uppercase font-bold {{ $isSunday ? 'text-rose-500 font-extrabold' : ($isSaturday ? 'text-indigo-500 font-extrabold' : 'text-[#8e88dd]') }}">{{ $date->translatedFormat('D') }}</span>
                        <span class="text-xs font-bold mt-0.5 px-3 py-1 rounded-xl transition-all duration-200 
                            {{ $isDateToday ? 'bg-[#2e2552] text-white shadow-sm' : ($isDateSelected ? 'bg-[#8e88dd]/20 text-[#2e2552]' : ($isSunday ? 'text-rose-600 font-black' : ($isSaturday ? 'text-indigo-600 font-black' : 'text-[#5a508f]'))) }}">
                            {{ $date->day }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Grid container with time axes rows & events overlay -->
            <div class="h-[600px] grid grid-cols-8 relative z-10 select-none pb-3">
                @php
                    $labelTimes = [
                        '07:15' => 0.0,
                        '08:00' => 9.09,
                        '09:00' => 21.21,
                        '10:00' => 33.33,
                        '11:00' => 45.45,
                        '12:00' => 57.57,
                        '13:00' => 69.69,
                        '14:00' => 81.81,
                        '15:00' => 93.93,
                        '15:30' => 100.0
                    ];
                    $timeSlotsData = [
                        ['start' => '07:15', 'top' => 0.0, 'height' => 9.09],
                        ['start' => '08:00', 'top' => 9.09, 'height' => 12.12],
                        ['start' => '09:00', 'top' => 21.21, 'height' => 12.12],
                        ['start' => '10:00', 'top' => 33.33, 'height' => 12.12],
                        ['start' => '11:00', 'top' => 45.45, 'height' => 12.12],
                        ['start' => '12:00', 'top' => 57.57, 'height' => 12.12],
                        ['start' => '13:00', 'top' => 69.69, 'height' => 12.12],
                        ['start' => '14:00', 'top' => 81.81, 'height' => 12.12],
                        ['start' => '15:00', 'top' => 93.93, 'height' => 6.07],
                    ];
                @endphp
                
                <!-- 1. Y-Axis Time Labels Column -->
                <div class="border-r border-[#d4d1f5]/40 h-full relative z-10 select-none pointer-events-none">
                    @foreach($labelTimes as $timeStr => $topPct)
                        <span class="absolute left-1/2 flex items-center justify-center bg-white px-1.5 z-20 text-[10px] font-extrabold text-[#5a508f] pointer-events-none" style="top: {{ number_format($topPct, 2, '.', '') }}%; transform: translate(-50%, {{ $topPct == 0 ? '10px' : ($topPct == 100 ? '-100%' : '-50%') }});">
                            {{ $timeStr }}
                        </span>
                    @endforeach
                </div>

                <!-- 2. Grid Columns for 7 Days -->
                @foreach($dates as $date)
                    @php
                        $dateStr = $date->toDateString();
                        $events = $agendasByDate[$dateStr] ?? [];
                        $isSunday = $date->isSunday();
                        $isSaturday = $date->isSaturday();
                    @endphp
                    <!-- Column relative container -->
                     <div class="h-full border-r border-[#d4d1f5]/40 last:border-0 relative {{ ($isSunday || $isSaturday) ? 'bg-rose-50/40' : 'bg-[#fcfbff]' }} group/col">
                        
                        <!-- Clickable background slots to quickly create events (Secretaries only) -->
                        @if(Auth::user()->isSekretarisMaster() || Auth::user()->isSekretarisBidang())
                            <div class="absolute inset-0 z-0 opacity-0 group-hover/col:opacity-100 transition-opacity duration-150 pointer-events-none">
                                @foreach($timeSlotsData as $slot)
                                    <button @click="openAddModal = true; selectedDate = '{{ $dateStr }}'; selectedTime = '{{ $slot['start'] }}'" 
                                            class="absolute w-full text-[9px] text-[#1b3bbb] hover:bg-[#1b3bbb]/5 pointer-events-auto flex items-center justify-center border-t border-dashed border-[#d4d1f5]/30 transition-colors"
                                            style="top: {{ number_format($slot['top'], 2, '.', '') }}%; height: {{ number_format($slot['height'], 2, '.', '') }}%;">
                                        <span class="bg-white/95 px-2 py-0.5 rounded-md shadow-sm border border-slate-100/50 text-[8px] font-bold text-[#1b3bbb]">+ Tambah {{ $slot['start'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <!-- Render Events inside this day's column -->
                        @foreach($events as $event)
                            @php
                                // Total grid range: 07:15 (435 min) to 15:30 (930 min) -> 495 min
                                $gridStartMin = 7 * 60 + 15;
                                $gridEndMin = 15 * 60 + 30;
                                $gridTotalMin = $gridEndMin - $gridStartMin;

                                // Event Start Time
                                $startParts = explode(':', $event->jam_mulai);
                                $eventStartMin = $startParts[0] * 60 + $startParts[1];
                                
                                // Event End Time
                                $endParts = explode(':', $event->jam_selesai);
                                $eventEndMin = $endParts[0] * 60 + $endParts[1];

                                // Constrain coordinate visually
                                $clampedStart = max($eventStartMin, $gridStartMin);
                                $clampedEnd = min($eventEndMin, $gridEndMin);
                                $duration = max($clampedEnd - $clampedStart, 30); // minimum height

                                $topPct = (($clampedStart - $gridStartMin) / $gridTotalMin) * 100;
                                $heightPct = ($duration / $gridTotalMin) * 100;
                                
                                // Column division positioning for overlaps
                                $colWidth = 100 / $event->total_cols;
                                $leftPos = $event->col_index * $colWidth;
                                
                                // Beautiful Lavender-Theme Category Colors
                                // Rapat: Amethyst Purple, Sosialisasi: Periwinkle Blue, Pelatihan: Lime Green, Kegiatan Lainnya: Lavender Gray
                                $categoryColorClasses = [
                                    'rapat' => 'bg-[#bc8bf2]/95 border-[#9a5fd9] text-white',
                                    'sosialisasi' => 'bg-[#8ba0f2]/95 border-[#5b73d9] text-white',
                                    'pelatihan' => 'bg-[#c2f73b]/95 border-[#9dd413] text-[#2e2552]',
                                    'kegiatan_lainnya' => 'bg-[#9f95d9]/95 border-[#786eb8] text-white',
                                ];
                                
                                $cardColorClass = $event->has_access 
                                    ? ($categoryColorClasses[$event->kategori] ?? 'bg-[#9f95d9]/95 border-[#786eb8] text-white')
                                    : 'bg-slate-200 border-slate-300 text-slate-500 cursor-not-allowed';
                            @endphp
                            
                            <!-- Event Card Container -->
                            @if($event->has_access)
                                <a href="{{ route('agenda.show', $event->id) }}" 
                                   class="absolute p-2 border rounded-2xl text-left shadow-sm z-10 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md flex flex-col justify-between overflow-hidden {{ $cardColorClass }}"
                                   style="top: {{ number_format($topPct, 2, '.', '') }}%; height: {{ number_format($heightPct, 2, '.', '') }}%; left: {{ number_format($leftPos, 2, '.', '') }}%; width: {{ number_format($colWidth, 2, '.', '') }}%;">
                                    <div class="min-w-0">
                                        <div class="flex items-center justify-between text-[8px] font-bold opacity-80 gap-1 uppercase truncate">
                                            <span>{{ substr($event->jam_mulai, 0, 5) }} - {{ substr($event->jam_selesai, 0, 5) }}</span>
                                            <span class="px-1 py-0.5 rounded bg-black/10 text-[8px] font-semibold">{{ $event->singkatan_bidang }}</span>
                                        </div>
                                        <h4 class="text-[10px] font-bold mt-0.5 leading-tight line-clamp-2">{{ $event->judul }}</h4>
                                    </div>
                                    <div class="text-[8px] opacity-80 truncate flex items-center gap-0.5 font-medium">
                                        <svg class="w-2.5 h-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        <span>{{ $event->lokasi }}</span>
                                    </div>
                                </a>
                             @else
                                <div class="absolute p-2 border rounded-2xl text-left shadow-sm z-10 overflow-hidden {{ $cardColorClass }}"
                                     title="Agenda ini terbatas untuk bidang {{ $event->singkatan_bidang }}"
                                     style="top: {{ number_format($topPct, 2, '.', '') }}%; height: {{ number_format($heightPct, 2, '.', '') }}%; left: {{ number_format($leftPos, 2, '.', '') }}%; width: {{ number_format($colWidth, 2, '.', '') }}%;">
                                    <div class="flex items-center justify-between text-[8px] font-bold opacity-60 gap-1 uppercase">
                                        <span>{{ substr($event->jam_mulai, 0, 5) }} - {{ substr($event->jam_selesai, 0, 5) }}</span>
                                        <span class="px-1 py-0.5 rounded bg-black/5">{{ $event->singkatan_bidang }}</span>
                                    </div>
                                    <h4 class="text-[10px] font-bold text-slate-500 mt-1 italic flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        <span>Rapat Terbatas</span>
                                    </h4>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
                
                 <!-- 3. Horizontal Grid Lines (visual representation spanning all columns) -->
                 <div class="absolute inset-0 pointer-events-none opacity-40 z-0">
                     @foreach($labelTimes as $timeStr => $topPct)
                         @if($topPct > 0 && $topPct < 100)
                             <div class="absolute w-full border-b border-[#d4d1f5]/30 h-0" style="top: {{ number_format($topPct, 2, '.', '') }}%;"></div>
                         @endif
                     @endforeach
                 </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD AGENDA FORM -->
    <div x-show="openAddModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        
        <div @click.away="openAddModal = false" 
             class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden relative"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-[#2e2552] to-[#8e88dd]"></div>

            <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#2e2552]">Buat Agenda Kegiatan Baru</h3>
                    <p class="text-xs text-[#5a508f]">Jadwalkan rapat atau kegiatan Dinkominfo</p>
                </div>
                <button @click="openAddModal = false" class="text-[#5a508f] hover:text-[#2e2552] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('agenda.store') }}" method="POST" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-[#2e2552]">
                @csrf

                <!-- Title Input -->
                <div class="space-y-1">
                    <label for="judul" class="block text-xs font-bold text-[#5a508f] uppercase">Judul Kegiatan / Rapat <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" id="judul" required placeholder="Contoh: Rapat Koordinasi Layanan SPBE"
                           class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>

                <!-- Date & Hours Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label for="tanggal" class="block text-xs font-bold text-[#5a508f] uppercase">Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" required x-model="selectedDate"
                               min="{{ now()->subMonths(6)->toDateString() }}"
                               max="{{ now()->addMonths(6)->toDateString() }}"
                               class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                    </div>
                    <div class="space-y-1">
                        <label for="jam_mulai" class="block text-xs font-bold text-[#5a508f] uppercase">Jam Mulai <span class="text-rose-500">*</span></label>
                        <input type="time" name="jam_mulai" id="jam_mulai" required x-model="selectedTime"
                               class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                    </div>
                    <div class="space-y-1">
                        <label for="jam_selesai" class="block text-xs font-bold text-[#5a508f] uppercase">Jam Selesai <span class="text-rose-500">*</span></label>
                        <input type="time" name="jam_selesai" id="jam_selesai" required value="15:30"
                               class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                    </div>
                </div>

                <!-- Tempat / Ruangan Dropdown & Kategori Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="tempat" class="block text-xs font-bold text-[#5a508f] uppercase">Tempat / Ruangan <span class="text-rose-500">*</span></label>
                        <select id="tempat" x-model="tempat"
                                class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                            <option value="Ruang Rapat Kartini">Ruang Rapat Kartini (Gedung A)</option>
                            <option value="Aula Utama Kominfo">Aula Utama Kominfo (Gedung A)</option>
                            <option value="Ruang Rapat Kepala Dinas">Ruang Rapat Kepala Dinas (Gedung A)</option>
                            <option value="Ruang PPID">Ruang PPID (Gedung B)</option>
                            <option value="Ruang Bidang IKP">Ruang Bidang IKP (Gedung B)</option>
                            <option value="Ruang Server TIK">Ruang Server TIK (Gedung B)</option>
                            <option value="Ruang Bidang Aptika">Ruang Bidang Aptika (Gedung B)</option>
                            <option value="Ruang Bidang Statistik & Persandian">Ruang Bidang Statistik & Persandian (Gedung B)</option>
                            <option value="Lainnya">Lainnya (Isi Kustom)...</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="kategori" class="block text-xs font-bold text-[#5a508f] uppercase">Kategori <span class="text-rose-500">*</span></label>
                        <select name="kategori" id="kategori" required x-model="kategori"
                                class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                            <option value="rapat">Rapat</option>
                            <option value="sosialisasi">Sosialisasi</option>
                            <option value="pelatihan">Pelatihan</option>
                            <option value="kegiatan_lainnya">Kegiatan Lainnya</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Tempat text field (show if Lainnya is selected) -->
                <div x-show="tempat === 'Lainnya'" x-transition class="space-y-1">
                    <label for="tempat_lainnya" class="block text-xs font-bold text-[#5a508f] uppercase">Nama Tempat Baru <span class="text-rose-500">*</span></label>
                    <input type="text" id="tempat_lainnya" x-model="tempatLainnya" placeholder="Contoh: Ruang Tamu Sekretariat"
                           class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>

                <!-- Hidden input to submit combined lokasi -->
                <input type="hidden" name="lokasi" :value="combinedLokasi">

                <!-- Description -->
                <div class="space-y-1">
                    <label for="deskripsi" class="block text-xs font-bold text-[#5a508f] uppercase">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" placeholder="Masukkan rincian singkat agenda kegiatan..."
                              class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]"></textarea>
                </div>

                <!-- Special Rapat Field: Dasar Pelaksanaan (Nomor Surat) -->
                <div x-show="kategori === 'rapat'" class="space-y-1 bg-[#8e88dd]/10 p-4 border border-[#8e88dd]/20 rounded-2xl">
                    <label for="nomor_surat_dasar" class="block text-xs font-bold text-[#2e2552] uppercase">Nomor Surat Dasar Pelaksanaan (Opsional Saat Pembuatan)</label>
                    <input type="text" name="nomor_surat_dasar" id="nomor_surat_dasar" placeholder="Contoh: 005/123/2026 Perihal Undangan Rapat Evaluasi SPBE"
                           class="w-full mt-1.5 px-4 py-2.5 bg-white border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                    <p class="text-[10px] text-[#5a508f] mt-1.5 font-medium">Catatan: Kolom ini wajib dilengkapi sekretaris sebelum mengajukan notulen hasil rapat untuk ditandatangani/disetujui oleh Ketua.</p>
                </div>

                <!-- Hak Akses (Audience) -->
                <div x-data="{
                    semuaOrang: false,
                    @if(Auth::user()->isSekretarisBidang())
                        bidangs: ['{{ Auth::user()->bidang_id }}'],
                        isSekBid: true,
                        ownBidangId: '{{ Auth::user()->bidang_id }}',
                    @else
                        bidangs: [],
                        isSekBid: false,
                        ownBidangId: null,
                    @endif
                    toggleSemua() {
                        if (this.semuaOrang) {
                            this.bidangs = ['1', '2', '3'];
                        } else {
                            this.bidangs = [];
                        }
                    },
                    checkBidang(id) {
                        if (this.isSekBid) {
                            if (!this.bidangs.includes(this.ownBidangId)) {
                                this.bidangs.push(this.ownBidangId);
                            }
                            if (3 <= this.bidangs.length) {
                                alert('Sekretaris Bidang hanya dapat memilih maksimal 1 bidang tambahan.');
                                this.bidangs = [this.ownBidangId, id];
                            }
                        }
                        if (this.bidangs.length === 3) {
                            this.semuaOrang = true;
                        } else {
                            this.semuaOrang = false;
                        }
                    }
                }" class="space-y-2 border-t border-[#d4d1f5]/40 pt-3">
                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Hak Akses / Peserta Rapat <span class="text-rose-500">*</span></label>
                    
                    @if(Auth::user()->isSekretarisBidang())
                        <!-- Enforce own bidang submission -->
                        <input type="hidden" name="bidangs[]" value="{{ Auth::user()->bidang_id }}">
                    @else
                        <!-- Semua orang checkbox -->
                        <label class="flex items-center text-xs text-[#2e2552] cursor-pointer select-none font-bold">
                            <input type="checkbox" name="semua_orang" value="1" x-model="semuaOrang" @change="toggleSemua()"
                                   class="w-4 h-4 rounded border-[#d4d1f5] bg-[#f3f2fe] text-[#8e88dd] focus:ring-[#8e88dd] mr-2">
                            <span>Semua Orang (LINTAS DINAS)</span>
                        </label>
                    @endif
                    
                    <!-- Individual Bidangs -->
                    <div class="grid grid-cols-1 gap-2 {{ Auth::user()->isSekretarisBidang() ? '' : 'pl-6' }} mt-1">
                        @foreach($bidangs as $bid)
                            <label class="flex items-center text-xs text-[#5a508f] cursor-pointer select-none font-medium">
                                <input type="checkbox" name="bidangs[]" value="{{ $bid->id }}" x-model="bidangs" @change="checkBidang('{{ $bid->id }}')"
                                       @if(Auth::user()->isSekretarisBidang() && Auth::user()->bidang_id == $bid->id) disabled @endif
                                       class="w-4 h-4 rounded border-[#d4d1f5] bg-[#f3f2fe] text-[#8e88dd] focus:ring-[#8e88dd] mr-2">
                                <span class="{{ Auth::user()->isSekretarisBidang() && Auth::user()->bidang_id == $bid->id ? 'font-bold text-[#2e2552]' : '' }}">
                                    {{ $bid->nama }} ({{ $bid->singkatan }})
                                    @if(Auth::user()->isSekretarisBidang() && Auth::user()->bidang_id == $bid->id)
                                        <span class="text-[9px] text-[#5a508f] lowercase ml-1">(Wajib hadir / Tidak dapat dibatalkan)</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Toggle Butuh Presensi -->
                <div class="flex items-center justify-between p-3 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl">
                    <div class="space-y-0.5">
                        <label for="butuh_presensi" class="block text-xs font-bold text-[#2e2552]">Memerlukan Presensi Digital?</label>
                        <p class="text-[10px] text-[#5a508f]">Mengaktifkan pencatatan kehadiran mandiri pegawai serta pembuatan dokumen notulensi rapat.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="butuh_presensi" id="butuh_presensi" checked value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#2e2552] peer-checked:after:bg-white"></div>
                    </label>
                </div>

                <!-- Footer buttons -->
                <div class="flex items-center justify-end gap-2 border-t border-[#d4d1f5]/40 pt-4">
                    <button type="button" @click="openAddModal = false"
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-2xl transition-colors">
                        Batalkan
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl shadow-lg shadow-[#2e2552]/25 transition-all">
                        Simpan Agenda
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
