@extends('layouts.app')

@section('title', 'Kalender Rinci')

@section('content')
<div x-data="{ 
    openAddModal: {{ ($errors->any() || request()->has('open_add')) ? 'true' : 'false' }}, 
    selectedDate: '{{ $selectedDate->toDateString() }}', 
    selectedTime: '07:15', 
    selectedEndTime: '{{ $selectedDate->isFriday() ? "15:15" : "15:30" }}',
    kategori: '',
    showMonthPicker: false,
    pickerYear: {{ $selectedDate->year }},

    updateEndTimeForDate(dateStr) {
        if (!dateStr) return;
        let parts = dateStr.split('-');
        if (parts.length === 3) {
            let d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            this.selectedEndTime = (d.getDay() === 5) ? '15:15' : '15:30';
        }
    },

    init() {
        this.updateEndTimeForDate(this.selectedDate);
        this.$watch('selectedDate', (val) => this.updateEndTimeForDate(val));
    }
}" class="h-full flex flex-col lg:flex-row gap-4 sm:gap-6">
    
    <!-- LEFT PANEL: Mini Calendar & Quick Add -->
    <div class="w-full lg:w-72 xl:w-80 flex flex-col gap-4 sm:gap-6 shrink-0 min-h-0">
        
        <!-- Mini Calendar Card -->
        <div class="bg-slate-50/70 border border-[#d4d1f5]/60 rounded-3xl p-4 sm:p-5 shadow-xs shrink-0">
            <div class="flex items-center justify-between mb-4 border-b border-[#d4d1f5]/30 pb-2">
                <!-- Prev Button -->
                <button type="button" 
                        @click="if (showMonthPicker) { pickerYear-- } else { window.location.href = '{{ route('calendar', ['date' => $selectedDate->copy()->subMonth()->startOfMonth()->toDateString()]) }}' }"
                        class="p-1.5 hover:bg-[#8e88dd]/20 rounded-xl text-[#2e2552] transition-colors"
                        title="Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <!-- Month/Year Header (Click to Toggle Month Picker) -->
                <h3 @click="showMonthPicker = !showMonthPicker" 
                    class="text-xs font-black uppercase tracking-wider text-[#2e2552] cursor-pointer hover:bg-[#8e88dd]/10 px-3 py-1 rounded-xl transition-colors select-none"
                    title="Klik untuk memilih bulan">
                    <span x-show="!showMonthPicker">{{ $selectedDate->translatedFormat('F Y') }}</span>
                    <span x-show="showMonthPicker" x-text="pickerYear"></span>
                </h3>
                
                <!-- Next Button -->
                <button type="button" 
                        @click="if (showMonthPicker) { pickerYear++ } else { window.location.href = '{{ route('calendar', ['date' => $selectedDate->copy()->addMonth()->startOfMonth()->toDateString()]) }}' }"
                        class="p-1.5 hover:bg-[#8e88dd]/20 rounded-xl text-[#2e2552] transition-colors"
                        title="Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Calendar Days Header -->
            <div x-show="!showMonthPicker" class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-[#5a508f] mb-2">
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
                $datesWithEvents = $miniCalendarDatesWithEvents ?? [];
            @endphp
            
            <!-- Days Grid view -->
            <div x-show="!showMonthPicker" class="grid grid-cols-7 gap-1 text-center text-xs">
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

            <!-- Month Picker Grid view -->
            <div x-show="showMonthPicker" x-cloak class="grid grid-cols-3 gap-2 text-center text-xs py-1">
                <template x-for="(mName, mIdx) in ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']" :key="mIdx">
                    <button type="button" 
                            @click="window.location.href = '/calendar?date=' + pickerYear + '-' + String(mIdx + 1).padStart(2, '0') + '-01'"
                            class="py-3 rounded-xl font-bold border transition-all duration-200"
                            :class="pickerYear === {{ $selectedDate->year }} && mIdx === {{ $selectedDate->month - 1 }} 
                                ? 'bg-[#2e2552] text-white border-[#2e2552] shadow-sm' 
                                : 'border-[#d4d1f5]/60 text-[#5a508f] hover:bg-[#8e88dd]/10 hover:text-[#2e2552] bg-white'">
                        <span x-text="mName"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Today's Highlights Panel -->
        <div class="bg-slate-50/70 border border-[#d4d1f5]/60 rounded-3xl p-4 sm:p-5 shadow-xs flex-1 flex flex-col min-h-0">
            <div class="flex items-center justify-between shrink-0 mb-3 sm:mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Kegiatan Hari Ini</h3>
                <span class="text-[10px] bg-[#2e2552]/10 text-[#2e2552] px-2.5 py-0.5 rounded-full border border-[#2e2552]/20 font-bold">
                    {{ count($todayAgendas) }} Agenda
                </span>
            </div>
            
            <div class="space-y-3 flex-1 min-h-0 overflow-y-auto pr-1">
                @forelse($todayAgendas as $ta)
                    <div class="p-3 bg-white border border-[#d4d1f5]/40 rounded-2xl hover:border-[#8e88dd]/40 transition-all duration-200 shadow-2xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] text-[#5a508f] font-bold">{{ substr($ta->jam_mulai, 0, 5) }} - {{ substr($ta->jam_selesai, 0, 5) }}</span>
                            @if($ta->singkatan_bidang === 'Semua')
                                <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold">Semua</span>
                            @else
                                <span class="text-[9px] px-2 py-0.5 rounded bg-[#2e2552]/10 text-[#2e2552] font-semibold max-w-[130px] truncate inline-block align-middle" title="{{ $ta->singkatan_bidang }}">{{ \Illuminate\Support\Str::limit($ta->singkatan_bidang, 16, '...') }}</span>
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
                    <p class="text-xs text-slate-400 text-center py-4 italic">Tidak ada agenda untuk hari ini.</p>
                @endforelse
            </div>

            <!-- Quick Add Agenda Button (Secretaries & Admin Subbag/Bidang Only) -->
            @if(Auth::user()->isSekretarisMaster() || Auth::user()->isSekretarisBidang())
                <div class="shrink-0 pt-3">
                    <button @click="openAddModal = true; selectedDate = '{{ $selectedDate->toDateString() }}'; selectedTime = '07:15'" 
                            class="w-full py-2.5 bg-[#2e2552] hover:bg-[#3d326a] active:scale-[0.98] text-white font-bold rounded-2xl text-xs transition-all duration-200 shadow-md shadow-[#2e2552]/20 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tambah Agenda Baru</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- RIGHT PANEL: 7-Day Weekly Agenda Grid -->
    <div class="flex-1 min-w-0 bg-slate-50/70 border border-[#d4d1f5]/60 rounded-xl md:rounded-3xl p-3 sm:p-5 shadow-xs flex flex-col">
        <!-- Calendar Navigation Header -->
        <div class="flex items-center justify-between mb-3 sm:mb-4 gap-2 sm:gap-4 border-b border-[#d4d1f5]/40 pb-2 sm:pb-3 shrink-0">
            <div class="min-w-0">
                <h2 class="text-sm sm:text-base font-extrabold text-[#2e2552] tracking-wide truncate">Kalender Rinci Mingguan</h2>
                <p class="text-[9.5px] sm:text-xs text-[#5a508f] mt-0.5 hidden sm:block">Jam kerja resmi (07:15 - {{ now()->isFriday() ? '15:15' : '15:30' }} WIB)</p>
            </div>
            
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <a href="{{ route('calendar', ['date' => $selectedDate->copy()->subWeek()->toDateString()]) }}" 
                   class="p-1.5 sm:p-2 bg-[#f3f2fe] border border-[#d4d1f5] rounded-lg sm:rounded-xl hover:bg-[#8e88dd]/20 text-[#2e2552] transition-all duration-200">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                @php
                    $startOfMonthRef = $selectedDate->copy()->startOfMonth();
                    $startDayOfWeekRef = $startOfMonthRef->dayOfWeekIso;
                    $calendarStartRef = $startOfMonthRef->copy()->subDays($startDayOfWeekRef - 1);
                    $diffDays = $calendarStartRef->diffInDays($dates[0], false);
                    $weekNum = (int) ($diffDays / 7) + 1;
                @endphp
                <a href="{{ route('calendar', ['date' => now()->toDateString()]) }}" 
                   class="px-2.5 py-1 sm:px-3.5 sm:py-1.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-lg sm:rounded-xl hover:bg-[#8e88dd]/20 text-[10px] sm:text-xs font-semibold text-[#2e2552] transition-all duration-200 whitespace-nowrap"
                   title="Kembali ke Minggu Ini">
                    Minggu ke-{{ $weekNum }}
                </a>
                <a href="{{ route('calendar', ['date' => $selectedDate->copy()->addWeek()->toDateString()]) }}" 
                   class="p-1.5 sm:p-2 bg-[#f3f2fe] border border-[#d4d1f5] rounded-lg sm:rounded-xl hover:bg-[#8e88dd]/20 text-[#2e2552] transition-all duration-200">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Weekly Grid Layout -->
        <div class="flex-1 w-full flex flex-col overflow-hidden">
            <div class="flex-1 min-w-0 flex flex-col relative h-full overflow-x-auto overflow-y-hidden">
                <div class="min-w-[540px] sm:min-w-full flex-1 flex flex-col h-full min-h-[660px] sm:min-h-[720px]">
            <!-- Dates columns header -->
            <div class="grid grid-cols-8 border-b border-[#d4d1f5]/40 pb-1.5 sm:pb-2 relative z-0 shrink-0">
                <!-- Time axes column -->
                <div class="text-center text-[10px] sm:text-xs font-bold text-[#5a508f] flex items-center justify-center">Waktu</div>
                <!-- 7 days columns -->
                @foreach($dates as $date)
                    @php
                        $isDateSelected = $date->isSameDay($selectedDate);
                        $isDateToday = $date->isToday();
                        $isSunday = $date->isSunday();
                        $isSaturday = $date->isSaturday();
                    @endphp
                    <div class="text-center flex flex-col items-center justify-center">
                        <span class="text-[8.5px] sm:text-[9px] uppercase font-bold {{ $isSunday ? 'text-rose-500 font-extrabold' : ($isSaturday ? 'text-indigo-500 font-extrabold' : 'text-[#8e88dd]') }}">{{ $date->translatedFormat('D') }}</span>
                        <span class="text-[10px] sm:text-xs font-bold mt-0.5 px-2 py-0.5 rounded-lg sm:rounded-xl transition-all duration-200 
                            {{ $isDateToday ? 'bg-[#2e2552] text-white shadow-sm' : ($isDateSelected ? 'bg-[#8e88dd]/20 text-[#2e2552]' : ($isSunday ? 'text-rose-600 font-black' : ($isSaturday ? 'text-indigo-600 font-black' : 'text-[#5a508f]'))) }}">
                            {{ $date->day }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Grid container with time axes rows & events overlay -->
            <div class="min-h-[660px] sm:min-h-[720px] flex-1 grid grid-cols-8 relative z-10 select-none border-b border-[#d4d1f5]/40">
                @php
                    $labelTimes = [
                        '07:15' => ['top' => 0.0, 'transform' => 'translate(-50%, 2px)'],
                        '08:00' => ['top' => 9.09, 'transform' => 'translate(-50%, -30%)'],
                        '09:00' => ['top' => 21.21, 'transform' => 'translate(-50%, -50%)'],
                        '10:00' => ['top' => 33.33, 'transform' => 'translate(-50%, -50%)'],
                        '11:00' => ['top' => 45.45, 'transform' => 'translate(-50%, -50%)'],
                        '12:00' => ['top' => 57.57, 'transform' => 'translate(-50%, -50%)'],
                        '13:00' => ['top' => 69.69, 'transform' => 'translate(-50%, -50%)'],
                        '14:00' => ['top' => 81.81, 'transform' => 'translate(-50%, -50%)'],
                        '15:00' => ['top' => 93.93, 'transform' => 'translate(-50%, -90%)'],
                        '15:30' => ['top' => 100.0, 'transform' => 'translate(-50%, -100%)'],
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
                    @foreach($labelTimes as $timeStr => $item)
                        <span class="absolute left-1/2 flex items-center justify-center bg-white px-1 z-20 text-[10px] font-extrabold text-[#5a508f] pointer-events-none" style="top: {{ number_format($item['top'], 2, '.', '') }}%; transform: {{ $item['transform'] }};">
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
                                $rawDuration = max($clampedEnd - $clampedStart, 15);
                                $maxAllowedDuration = max($gridEndMin - $clampedStart, 1);
                                $duration = min($rawDuration, $maxAllowedDuration);

                                $topPct = (($clampedStart - $gridStartMin) / $gridTotalMin) * 100;
                                $heightPct = ($duration / $gridTotalMin) * 100;
                                
                                // Column division positioning for overlaps
                                $colWidth = 100 / $event->total_cols;
                                $leftPos = $event->col_index * $colWidth;
                                
                                // Beautiful Lavender-Theme Category Colors
                                // Rapat: Amethyst Purple, Sosialisasi: Periwinkle Blue, Pelatihan: Lime Green, Kegiatan Lainnya: Lavender Gray
                                $categoryColorClasses = [
                                    'rapat' => 'bg-[#ef4444]/95 border-[#dc2626] text-white',
                                    'sosialisasi' => 'bg-[#3b82f6]/95 border-[#2563eb] text-white',
                                    'pelatihan' => 'bg-[#10b981]/95 border-[#059669] text-white',
                                    'kegiatan_lainnya' => 'bg-[#94a3b8]/95 border-[#475569] text-white',
                                ];
                                
                                $cardColorClass = $event->has_access 
                                    ? ($categoryColorClasses[$event->kategori] ?? 'bg-[#9f95d9]/95 border-[#786eb8] text-white')
                                    : 'bg-slate-200 border-slate-300 text-slate-500 cursor-not-allowed';
                            @endphp
                            
                            <!-- Event Card Container -->
                            @if($event->has_access)
                                  @php
                                      $dayIso = $date->dayOfWeekIso;
                                      if ($dayIso === 1) {
                                          $alignClass = 'left-0 translate-x-0';
                                          $arrowAlignClass = 'after:left-6 after:translate-x-0';
                                      } elseif ($dayIso >= 6) {
                                          $alignClass = 'right-0 left-auto translate-x-0';
                                          $arrowAlignClass = 'after:right-6 after:left-auto after:translate-x-0';
                                      } else {
                                          $alignClass = 'left-1/2 -translate-x-1/2';
                                          $arrowAlignClass = 'after:left-1/2 after:-translate-x-1/2';
                                      }

                                      if ($topPct < 25) {
                                          $tooltipPosition = 'top-full mt-2 ' . $alignClass;
                                          $arrowClass = 'after:bottom-full ' . $arrowAlignClass . ' after:border-b-' . ($event->kategori === 'rapat' ? '[#ffe4e6]' : ($event->kategori === 'sosialisasi' ? '[#dbeafe]' : ($event->kategori === 'pelatihan' ? '[#d1fae5]' : '[#f1f5f9]')));
                                      } else {
                                          $tooltipPosition = 'bottom-full mb-2 ' . $alignClass;
                                          $arrowClass = 'after:top-full ' . $arrowAlignClass . ' after:border-t-' . ($event->kategori === 'rapat' ? '[#ffe4e6]' : ($event->kategori === 'sosialisasi' ? '[#dbeafe]' : ($event->kategori === 'pelatihan' ? '[#d1fae5]' : '[#f1f5f9]')));
                                      }
                                      
                                      $tooltipStyles = [
                                          'rapat' => [
                                              'bg' => 'bg-[#ffe4e6]',
                                              'border' => 'border-[#fda4af]',
                                              'text' => 'text-[#881337]',
                                              'subtext' => 'text-[#b91c1c]',
                                              'header_text' => 'text-[#be123c]',
                                          ],
                                          'sosialisasi' => [
                                              'bg' => 'bg-[#dbeafe]',
                                              'border' => 'border-[#bfdbfe]',
                                              'text' => 'text-[#1e3a8a]',
                                              'subtext' => 'text-[#1d4ed8]',
                                              'header_text' => 'text-[#1d4ed8]',
                                          ],
                                          'pelatihan' => [
                                              'bg' => 'bg-[#d1fae5]',
                                              'border' => 'border-[#a7f3d0]',
                                              'text' => 'text-[#064e3b]',
                                              'subtext' => 'text-[#047857]',
                                              'header_text' => 'text-[#047857]',
                                          ],
                                          'kegiatan_lainnya' => [
                                              'bg' => 'bg-[#f1f5f9]',
                                              'border' => 'border-[#cbd5e1]',
                                              'text' => 'text-[#0f172a]',
                                              'subtext' => 'text-[#475569]',
                                              'header_text' => 'text-[#475569]',
                                          ],
                                      ];
                                      $tStyle = $tooltipStyles[$event->kategori] ?? $tooltipStyles['kegiatan_lainnya'];
                                  @endphp
                                  <a href="{{ route('agenda.show', $event->id) }}" 
                                     class="absolute p-1.5 sm:p-2 border rounded-xl sm:rounded-2xl text-left shadow-sm z-10 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:z-50 flex flex-col justify-between group {{ $cardColorClass }}"
                                     style="top: calc({{ number_format($topPct, 2, '.', '') }}% + 2px); height: calc({{ number_format($heightPct, 2, '.', '') }}% - 4px); left: calc({{ number_format($leftPos, 2, '.', '') }}% + 2px); width: calc({{ number_format($colWidth, 2, '.', '') }}% - 4px);">
                                      <div class="min-w-0 w-full overflow-hidden shrink-0">
                                          <div class="flex items-center justify-between text-[8px] font-bold opacity-90 gap-1 uppercase min-w-0">
                                              <span class="whitespace-nowrap shrink-0">{{ substr($event->jam_mulai, 0, 5) }} - {{ substr($event->jam_selesai, 0, 5) }}</span>
                                              <span class="px-1 py-0.5 rounded bg-black/10 text-[8px] font-semibold truncate">{{ $event->singkatan_bidang }}</span>
                                          </div>
                                          <h4 class="text-[10px] font-bold mt-0.5 leading-tight line-clamp-2 break-all">{{ $event->judul }}</h4>
                                      </div>
                                      <div class="text-[8px] opacity-90 truncate flex items-center gap-0.5 font-semibold w-full overflow-hidden shrink-0 mt-0.5">
                                          <svg class="w-2.5 h-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                          </svg>
                                          <span class="truncate">{{ $event->lokasi }}</span>
                                      </div>

                                      <!-- Floating Tooltip on Hover -->
                                      <div class="absolute {{ $tooltipPosition }} {{ $arrowClass }} {{ $tStyle['bg'] }} {{ $tStyle['border'] }} {{ $tStyle['text'] }} w-56 sm:w-60 p-3 rounded-2xl shadow-2xl z-50 text-[10px] pointer-events-none opacity-0 group-hover:opacity-100 transition-all duration-200 border after:content-[''] after:absolute after:border-4 after:border-transparent">
                                         <div class="font-bold border-b border-black/10 pb-1 flex justify-between items-start gap-2">
                                             <span class="{{ $tStyle['header_text'] }} font-extrabold uppercase leading-tight mr-2">{{ $event->singkatan_bidang }}</span>
                                             <span class="{{ $tStyle['subtext'] }} whitespace-nowrap shrink-0 text-right mt-0.5">{{ substr($event->jam_mulai, 0, 5) }} - {{ substr($event->jam_selesai, 0, 5) }}</span>
                                         </div>
                                         <div class="mt-1.5 font-bold leading-tight">
                                             {{ $event->judul }}
                                         </div>
                                         <div class="mt-1.5 text-[9px] {{ $tStyle['subtext'] }} flex items-center gap-1">
                                             <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                             </svg>
                                             <span class="truncate font-semibold">{{ $event->lokasi }}</span>
                                         </div>
                                     </div>
                                 </a>
                             @else
                                <div class="absolute p-2 border rounded-2xl text-left shadow-sm z-10 overflow-hidden {{ $cardColorClass }}"
                                     title="Agenda ini terbatas untuk bidang {{ $event->singkatan_bidang }}"
                                     style="top: calc({{ number_format($topPct, 2, '.', '') }}% + 2px); height: calc({{ number_format($heightPct, 2, '.', '') }}% - 4px); left: calc({{ number_format($leftPos, 2, '.', '') }}% + 2px); width: calc({{ number_format($colWidth, 2, '.', '') }}% - 4px);">
                                    <div class="flex items-center justify-between text-[8px] font-bold opacity-60 gap-1 uppercase min-w-0">
                                        <span class="whitespace-nowrap shrink-0">{{ substr($event->jam_mulai, 0, 5) }} - {{ substr($event->jam_selesai, 0, 5) }}</span>
                                        <span class="px-1 py-0.5 rounded bg-black/5 truncate">{{ $event->singkatan_bidang }}</span>
                                    </div>
                                    <h4 class="text-[10px] font-bold text-slate-500 mt-1 italic flex items-center gap-1 min-w-0 overflow-hidden">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        <span class="truncate">Rapat Terbatas</span>
                                    </h4>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
                
                 <!-- 3. Horizontal Grid Lines (visual representation spanning all columns) -->
                 <div class="absolute inset-0 pointer-events-none opacity-40 z-0">
                     @foreach($labelTimes as $timeStr => $topPct)
                         @if($topPct > 0 && $topPct <= 100)
                             <div class="absolute w-full border-b border-[#d4d1f5]/30 h-0" style="top: {{ number_format($topPct, 2, '.', '') }}%;"></div>
                         @endif
                     @endforeach
                 </div>
             </div>
         </div>
     </div>
 </div>
</div>

    <!-- MODAL: ADD AGENDA FORM -->
    <div x-show="openAddModal" x-cloak 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-md overflow-y-auto transition-all duration-300">
        
        <div @click.away="openAddModal = false" 
             class="bg-white border border-[#d4d1f5] rounded-3xl w-full max-w-xl shadow-2xl overflow-hidden relative text-[#2e2552] my-auto flex flex-col max-h-[85vh] sm:max-h-[88vh]"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">
            
            <!-- Modal Header -->
            <div class="px-5 py-3.5 sm:py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-xl border border-white/15 shrink-0">
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Buat Agenda Kegiatan Baru</h3>
                        <p class="text-[11px] text-indigo-100 font-medium">Jadwalkan rapat atau kegiatan Dinkominfo</p>
                    </div>
                </div>
                <button @click="openAddModal = false" type="button" class="p-1.5 bg-white/10 hover:bg-rose-500/80 rounded-xl text-white transition-all cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form Content -->
            <form action="{{ route('agenda.store') }}" method="POST" class="p-4 sm:p-5 space-y-2.5 flex-1 min-h-0 overflow-y-auto">
                @csrf

                <!-- Title & Category Row -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 space-y-1">
                        <label for="judul" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Judul Kegiatan / Rapat <span class="text-rose-500 font-bold">*</span></label>
                        <input type="text" name="judul" id="judul" required placeholder="Contoh: Rapat Koordinasi Layanan SPBE"
                               class="w-full px-3.5 py-2 bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-xs placeholder-slate-400 focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 transition-all font-semibold">
                    </div>
                    <div class="space-y-1 relative" x-data="{ openKategori: false }" @click.outside="openKategori = false">
                        <label for="kategori" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Kategori <span class="text-rose-500 font-bold">*</span></label>
                        <input type="hidden" name="kategori" id="kategori" x-model="kategori" required>
                        <button type="button" @click="openKategori = !openKategori" 
                                class="w-full px-3.5 py-2 bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#09103c] text-xs font-semibold flex items-center justify-between transition-all cursor-pointer focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]">
                            <span class="truncate" x-text="kategori ? (kategori === 'rapat' ? 'Rapat' : (kategori === 'sosialisasi' ? 'Sosialisasi' : (kategori === 'pelatihan' ? 'Pelatihan' : 'Kegiatan Lainnya'))) : 'Pilih Kategori'"></span>
                            <svg class="w-3.5 h-3.5 text-[#1b3bbb] transition-transform duration-200" :class="openKategori ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="openKategori" x-cloak 
                             x-transition:enter="transition ease-out duration-150 transform" 
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-100 transform" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1" 
                             class="absolute left-0 top-full mt-1 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                            <div class="space-y-0.5">
                                <template x-for="opt in [
                                    { value: 'rapat', label: 'Rapat' },
                                    { value: 'sosialisasi', label: 'Sosialisasi' },
                                    { value: 'pelatihan', label: 'Pelatihan' },
                                    { value: 'kegiatan_lainnya', label: 'Kegiatan Lainnya' }
                                ]" :key="opt.value">
                                    <button type="button" @click="kategori = opt.value; openKategori = false" 
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                            :class="kategori === opt.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                        <span class="text-left leading-snug" x-text="opt.label"></span>
                                        <svg x-show="kategori === opt.value" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date & Hours Row -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="col-span-2 sm:col-span-1 space-y-1">
                        <label for="tanggal" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Tanggal <span class="text-rose-500 font-bold">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" required x-model="selectedDate"
                               min="{{ now()->subMonths(6)->toDateString() }}"
                               max="{{ now()->addMonths(6)->toDateString() }}"
                               class="w-full px-3 py-2 bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-xs font-semibold focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 transition-all">
                    </div>
                    <div class="space-y-1">
                        <label for="jam_mulai" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Jam Mulai <span class="text-rose-500 font-bold">*</span></label>
                        <input type="time" name="jam_mulai" id="jam_mulai" required x-model="selectedTime"
                               class="w-full px-2.5 py-2 bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-xs font-semibold focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 transition-all">
                    </div>
                    <div class="space-y-1">
                        <label for="jam_selesai" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Jam Selesai <span class="text-rose-500 font-bold">*</span></label>
                        <input type="time" name="jam_selesai" id="jam_selesai" required x-model="selectedEndTime"
                               class="w-full px-2.5 py-2 bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-xs font-semibold focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 transition-all">
                    </div>
                </div>

                <!-- Location & Description Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1 relative" x-data="{ lokasiVal: '', openLokasi: false }" @click.outside="openLokasi = false">
                        <label for="tempat" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Tempat / Ruangan <span class="text-rose-500 font-bold">*</span></label>
                        <input type="hidden" id="tempat" name="lokasi" :value="lokasiVal" required>
                        <button type="button" @click="openLokasi = !openLokasi" 
                                class="w-full px-3.5 py-2 bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#09103c] text-xs font-semibold flex items-center justify-between transition-all cursor-pointer focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]">
                            <span class="truncate" x-text="lokasiVal || 'Pilih Lokasi / Ruangan'"></span>
                            <svg class="w-3.5 h-3.5 text-[#1b3bbb] transition-transform duration-200" :class="openLokasi ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="openLokasi" x-cloak 
                             x-transition:enter="transition ease-out duration-150 transform" 
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-100 transform" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1" 
                             class="absolute left-0 bottom-full mb-1 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                            <div class="space-y-0.5">
                                <template x-for="loc in ['Aula Rapat Dinkominfo', 'Ruang Pelatihan', 'Smart Room Graha Satria']" :key="loc">
                                    <button type="button" @click="lokasiVal = loc; openLokasi = false" 
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                            :class="lokasiVal === loc ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                        <span class="text-left leading-snug" x-text="loc"></span>
                                        <svg x-show="lokasiVal === loc" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="deskripsi" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Deskripsi (Opsional)</label>
                        <input type="text" name="deskripsi" id="deskripsi" placeholder="Masukkan rincian singkat agenda..."
                               class="w-full px-3.5 py-2 bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-xs placeholder-slate-400 focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 transition-all font-semibold">
                    </div>
                </div>

                <!-- Hak Akses & Kelola Peserta -->
                @php
                    $allBidangIds = $bidangs->pluck('id')->map(fn($id) => (string)$id)->toArray();
                    $totalBidangCount = count($allBidangIds);
                    $sekretariatBid = $bidangs->first(fn($b) => strcasecmp($b->singkatan, 'Sekretariat') === 0 || strcasecmp($b->nama, 'Sekretariat') === 0);
                    $sekretariatId = $sekretariatBid ? (string)$sekretariatBid->id : null;
                    $sekretariatSubbagIds = $bidangs->filter(function($b) {
                        return strcasecmp($b->singkatan, 'Sekretariat') === 0 || 
                               strcasecmp($b->nama, 'Sekretariat') === 0 || 
                               str_contains(strtolower($b->nama), 'subbag') || 
                               str_contains(strtolower($b->singkatan), 'subbag');
                    })->pluck('id')->map(fn($id) => (string)$id)->values()->toArray();

                    $kadinUser = \App\Models\User::where('role', 'ketua_master')->first();
                    $kadinUserId = $kadinUser ? (string)$kadinUser->id : '';
                    $kadinUserData = $kadinUser ? [
                        'id' => (string)$kadinUser->id,
                        'name' => $kadinUser->name,
                        'nip' => $kadinUser->nip ?? '-',
                        'jabatan' => $kadinUser->jabatan ?? 'Kepala Dinas / Kadin',
                        'role' => $kadinUser->role,
                    ] : null;
                    
                    $defaultInitialBidangs = [];
                    if (Auth::user()->isSekretarisBidang() || Auth::user()->isSekretariatScope()) {
                        if (Auth::user()->bidang_id) {
                            $defaultInitialBidangs[] = (string)Auth::user()->bidang_id;
                        }
                        if (Auth::user()->isSekretariatScope() && $sekretariatId) {
                            $defaultInitialBidangs[] = (string)$sekretariatId;
                        }
                    }

                    $bidangsUserData = $bidangs->map(function($b) {
                        return [
                            'id' => (string)$b->id,
                            'nama' => $b->nama,
                            'singkatan' => $b->singkatan,
                            'users' => $b->users->map(function($u) {
                                return [
                                    'id' => (string)$u->id,
                                    'name' => $u->name,
                                    'nip' => $u->nip ?? '-',
                                    'jabatan' => $u->jabatan ?? '-',
                                    'role' => $u->role,
                                ];
                            })->values()->toArray(),
                        ];
                    })->values()->toArray();
                @endphp

                <div x-data='{
                    semuaOrang: false,
                    semuaSekretariat: false,
                    sekretariatSubbagIds: {{ json_encode(array_values($sekretariatSubbagIds)) }},
                    allBidangIds: {{ json_encode(array_values($allBidangIds)) }},
                    totalCount: {{ $totalBidangCount }},
                    bidangs: {{ json_encode(array_values(array_unique($defaultInitialBidangs))) }},
                    isSekBid: {{ Auth::user()->isSekretarisBidang() ? "true" : "false" }},
                    isSekretariatScope: {{ Auth::user()->isSekretariatScope() ? "true" : "false" }},
                    ownBidangId: "{{ Auth::user()->bidang_id }}",
                    sekId: "{{ $sekretariatId }}",
                    kadinUserId: "{{ $kadinUserId }}",
                    kadinUser: {{ json_encode($kadinUserData) }},
                    kadinTarget: false,
                    showBidangLimitWarning: false,
                    bidangsUserData: {{ json_encode($bidangsUserData) }},
                    currentUserId: "{{ Auth::id() }}",
                    selectedParticipants: [],
                    participantModalOpen: false,
                    searchParticipant: "",
                    adminValidationErrorMessage: "",
                    isDirty: false,

                    init() {
                        this.syncParticipants();
                    },

                    isAdminUser(user) {
                        if (!user) return false;
                        let r = user.role;
                        return r === "sekretaris_bidang" || r === "sekretaris_master";
                    },

                    isKetuaUser(user) {
                        if (!user) return false;
                        let r = user.role;
                        let j = (user.jabatan || "").toLowerCase();
                        if (this.isAdminUser(user)) return false;
                        return r === "ketua_bidang" || r === "ketua_master" || j.includes("kepala") || j.includes("kabid") || j.includes("kasubbag") || j.includes("kadin") || j.includes("sekdin");
                    },

                    isPimpinan(user) {
                        return this.isKetuaUser(user);
                    },

                    isNotulis(user) {
                        if (!user) return false;
                        return String(user.id) === String(this.currentUserId);
                    },

                    isMandatoryUser(user) {
                        if (!user) return false;
                        let uId = String(user.id);
                        let isCreator = (uId === String(this.currentUserId));
                        let isKetua = this.isKetuaUser(user);
                        return isCreator || isKetua;
                    },

                    toggleUserParticipant(user) {
                        this.isDirty = true;
                        let uId = String(user.id);
                        let curParts = (this.selectedParticipants || []).map(String);

                        if (this.isAdminUser(user)) {
                            let unitId = String(user.bidang_id);
                            let b = (this.bidangsUserData || []).find(item => String(item.id) === unitId);
                            if (b) {
                                let unitAdminIds = b.users.filter(u => this.isAdminUser(u)).map(u => String(u.id));
                                if (!curParts.includes(uId)) {
                                    // Switch to this admin (max 1 admin selected per unit)
                                    curParts = curParts.filter(id => !unitAdminIds.includes(id));
                                    curParts.push(uId);
                                }
                            }
                            this.selectedParticipants = curParts;
                        }
                    },

                    validateAdminSelection() {
                        let selectedBidangIds = (this.bidangs || []).map(String);
                        let curSelected = (this.selectedParticipants || []).map(String);
                        let missingAdminBidangNames = [];

                        (this.bidangsUserData || []).forEach(b => {
                            if (selectedBidangIds.includes(String(b.id))) {
                                let unitAdmins = (b.users || []).filter(u => this.isAdminUser(u));
                                if (unitAdmins.length > 0) {
                                    let selectedCount = unitAdmins.filter(u => curSelected.includes(String(u.id))).length;
                                    if (selectedCount !== 1) {
                                        missingAdminBidangNames.push(b.nama || b.singkatan);
                                    }
                                }
                            }
                        });

                        if (missingAdminBidangNames.length > 0) {
                            this.adminValidationErrorMessage = "Pilih 1 Admin dari unit yang diundang (" + missingAdminBidangNames.join(", ") + ").";
                            this.$nextTick(() => {
                                if (this.$refs.modalBody) {
                                    this.$refs.modalBody.scrollTo({ top: 0, behavior: "smooth" });
                                }
                            });
                            return false;
                        }
                        this.adminValidationErrorMessage = "";
                        return true;
                    },

                    toggleKadinTarget() {
                        this.isDirty = true;
                        let kId = String(this.kadinUserId);
                        let curParts = (this.selectedParticipants || []).map(String);
                        let curBids = (this.bidangs || []).map(String);

                        if (this.kadinTarget) {
                            if (!curBids.includes("kadin")) curBids.push("kadin");
                            if (kId && !curParts.includes(kId)) curParts.push(kId);
                        } else {
                            curBids = curBids.filter(b => b !== "kadin");
                            if (kId) curParts = curParts.filter(p => p !== kId);
                        }
                        this.bidangs = curBids;
                        this.syncParticipants();
                    },

                    toggleSemua() {
                        this.isDirty = true;
                        if (this.semuaOrang) {
                            this.bidangs = Array.from(this.allBidangIds);
                        } else {
                            this.bidangs = [];
                        }
                        this.syncParticipants();
                    },

                    toggleSemuaSekretariat() {
                        this.isDirty = true;
                        let sekSubIds = (this.sekretariatSubbagIds || []).map(String);
                        let curBids = (this.bidangs || []).map(String);

                        if (this.semuaSekretariat) {
                            sekSubIds.forEach(id => {
                                if (!curBids.includes(id)) {
                                    curBids.push(id);
                                }
                            });
                            if (!this.isSekretariatScope && curBids.length > 3) {
                                curBids = curBids.slice(0, 3);
                                this.showBidangLimitWarning = true;
                            } else {
                                this.showBidangLimitWarning = false;
                            }
                        } else {
                            curBids = curBids.filter(id => !sekSubIds.includes(id));
                            if (this.ownBidangId && !curBids.includes(String(this.ownBidangId))) {
                                curBids.push(String(this.ownBidangId));
                            }
                            if (this.sekId && !curBids.includes(String(this.sekId))) {
                                curBids.push(String(this.sekId));
                            }
                            this.showBidangLimitWarning = false;
                        }
                        this.bidangs = curBids;
                        this.semuaSekretariat = sekSubIds.length > 0 && sekSubIds.every(id => curBids.includes(id));
                        this.syncParticipants();
                    },

                    checkBidang(id) {
                        this.isDirty = true;
                        this.$nextTick(() => {
                            let strId = String(id);
                            let curBids = (this.bidangs || []).map(String);
                            let curParts = (this.selectedParticipants || []).map(String);

                            if (curBids.includes(strId)) {
                                // Bidang checked: Auto-check ALL users in this bidang
                                let b = (this.bidangsUserData || []).find(item => String(item.id) === strId);
                                if (b && Array.isArray(b.users)) {
                                    b.users.forEach(u => {
                                        let uId = String(u.id);
                                        if (!curParts.includes(uId)) {
                                            curParts.push(uId);
                                        }
                                    });
                                }
                            } else {
                                // Bidang unchecked: Remove users belonging to this bidang
                                let b = (this.bidangsUserData || []).find(item => String(item.id) === strId);
                                if (b && Array.isArray(b.users)) {
                                    let unitUserIds = b.users.map(u => String(u.id));
                                    curParts = curParts.filter(uId => !unitUserIds.includes(uId) || uId === String(this.currentUserId));
                                }
                            }
                            this.selectedParticipants = curParts;

                            if (this.isSekBid || this.isSekretariatScope) {
                                if (this.ownBidangId && !curBids.includes(String(this.ownBidangId))) {
                                    curBids.push(String(this.ownBidangId));
                                }
                                if (this.isSekretariatScope && this.sekId && !curBids.includes(String(this.sekId))) {
                                    curBids.push(String(this.sekId));
                                }
                                let numericBids = curBids.filter(b => b !== "kadin" && b !== String(this.sekId));
                                if (!this.isSekretariatScope && numericBids.length > 3) {
                                    this.showBidangLimitWarning = true;
                                    curBids = curBids.filter(bId => bId !== strId);
                                } else {
                                    this.showBidangLimitWarning = false;
                                }
                            }
                            this.bidangs = curBids;
                            let sekSubIds = (this.sekretariatSubbagIds || []).map(String);
                            this.semuaSekretariat = sekSubIds.length > 0 && sekSubIds.every(id => curBids.includes(id));
                            this.semuaOrang = (this.bidangs.length === this.totalCount);
                            this.syncParticipants();
                        });
                    },

                    filteredUsers(users) {
                        if (!users || !Array.isArray(users)) return [];
                        if (!this.searchParticipant || !this.searchParticipant.trim()) return users;
                        let q = this.searchParticipant.toLowerCase().trim();
                        return users.filter(u => 
                            (u.name && String(u.name).toLowerCase().includes(q)) || 
                            (u.jabatan && String(u.jabatan).toLowerCase().includes(q)) ||
                            (u.nip && String(u.nip).toLowerCase().includes(q))
                        );
                    },

                    get visibleBidangs() {
                        let selectedBidangIds = (this.bidangs || []).map(String);
                        return (this.bidangsUserData || []).filter(b => {
                            let isSelected = selectedBidangIds.includes(String(b.id));
                            if (!isSelected) return false;
                            if (this.searchParticipant && this.searchParticipant.trim()) {
                                return this.filteredUsers(b.users).length > 0;
                            }
                            return true;
                        });
                    },

                    get totalFilteredUsersCount() {
                        let count = 0;
                        this.visibleBidangs.forEach(b => {
                            count += this.filteredUsers(b.users).length;
                        });
                        return count;
                    },

                    syncParticipants() {
                        let selectedBidangIds = (this.bidangs || []).map(String);
                        let activeUserIds = [];
                        let mandatoryUserIds = [];

                        // Always enforce logged-in creator (Notulis)
                        if (this.currentUserId) {
                            mandatoryUserIds.push(String(this.currentUserId));
                        }

                        let currentSelected = (this.selectedParticipants || []).map(String);

                        (this.bidangsUserData || []).forEach(b => {
                            if (selectedBidangIds.includes(String(b.id))) {
                                let unitAdmins = (b.users || []).filter(u => this.isAdminUser(u));
                                let hasAdminSelected = unitAdmins.some(u => currentSelected.includes(String(u.id)));

                                (b.users || []).forEach(u => {
                                    let uId = String(u.id);
                                    activeUserIds.push(uId);
                                    if (this.isMandatoryUser(u)) {
                                        mandatoryUserIds.push(uId);
                                    }
                                });

                                // Auto-pick 1st admin if no admin is currently selected for this invited unit
                                if (!hasAdminSelected && unitAdmins.length > 0) {
                                    mandatoryUserIds.push(String(unitAdmins[0].id));
                                }
                            }
                        });

                        if (this.kadinTarget && this.kadinUserId) {
                            let kId = String(this.kadinUserId);
                            activeUserIds.push(kId);
                            mandatoryUserIds.push(kId);
                        }

                        if (this.sekdinTarget && this.sekdinUserId) {
                            let sId = String(this.sekdinUserId);
                            activeUserIds.push(sId);
                            mandatoryUserIds.push(sId);
                        }

                        let newSelection = currentSelected.filter(id => activeUserIds.includes(id));

                        // Auto-check all users of newly invited bidangs if none were selected yet for that unit
                        selectedBidangIds.forEach(bidId => {
                            let b = (this.bidangsUserData || []).find(item => String(item.id) === bidId);
                            if (b && Array.isArray(b.users)) {
                                let hasAnySelected = b.users.some(u => currentSelected.includes(String(u.id)));
                                if (!hasAnySelected) {
                                    b.users.forEach(u => {
                                        let uId = String(u.id);
                                        if (!newSelection.includes(uId)) {
                                            newSelection.push(uId);
                                        }
                                    });
                                }
                            }
                        });

                        // Mandatory users (Creator + Ketua + 1 Admin) MUST always be checked
                        mandatoryUserIds.forEach(id => {
                            if (!newSelection.includes(id)) {
                                newSelection.push(id);
                            }
                        });

                        this.selectedParticipants = newSelection;
                        if (this.kadinUserId) {
                            this.kadinTarget = this.selectedParticipants.map(String).includes(String(this.kadinUserId));
                        }
                        if (this.sekdinUserId) {
                            this.sekdinTarget = this.selectedParticipants.map(String).includes(String(this.sekdinUserId));
                        }
                    },

                    toggleBidangUsers(bidangId) {
                        this.isDirty = true;
                        let b = this.bidangsUserData.find(item => String(item.id) === String(bidangId));
                        if (!b) return;
                        let bUserIds = b.users.map(u => String(u.id));
                        let currentSelected = this.selectedParticipants.map(String);
                        let allChecked = bUserIds.every(id => currentSelected.includes(id));

                        if (!allChecked) {
                            bUserIds.forEach(id => {
                                if (!currentSelected.includes(id)) {
                                    currentSelected.push(id);
                                }
                            });
                        } else {
                            // Uncheck only non-mandatory users; keep mandatory leaders and notulis checked
                            currentSelected = currentSelected.filter(id => {
                                let u = b.users.find(usr => String(usr.id) === String(id));
                                if (u && this.isMandatoryUser(u)) return true;
                                return !bUserIds.includes(id);
                            });
                        }
                        this.selectedParticipants = currentSelected;
                    },

                    isBidangAllChecked(bidangId) {
                        let b = this.bidangsUserData.find(item => String(item.id) === String(bidangId));
                        if (!b || !b.users || b.users.length === 0) return false;
                        let currentSelected = this.selectedParticipants.map(String);
                        return b.users.every(u => currentSelected.includes(String(u.id)));
                    }
                }' class="space-y-2 border-t border-[#d4d1f5]/60 pt-2.5">
                    
                    <!-- Hidden Payload Inputs -->
                    <template x-for="bidangId in bidangs" :key="'bidang-' + bidangId">
                        <input type="hidden" name="bidangs[]" :value="bidangId">
                    </template>
                    <template x-for="userId in selectedParticipants" :key="'participant-' + userId">
                        <input type="hidden" name="participants[]" :value="userId">
                    </template>
                    <template x-if="semuaOrang">
                        <input type="hidden" name="semua_orang" value="1">
                    </template>

                    <!-- Header & Manage Participants Button Row -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">
                                Sasaran Bidang & Peserta <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <p class="text-[10.5px] text-slate-500 font-medium">Pilihan pimpinan ACC dan notulis otomatis tercentang wajib</p>
                        </div>
                        <button type="button" 
                                @click="participantModalOpen = true"
                                :class="selectedParticipants.length === 0 ? 'bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-100' : 'bg-[#f8f7ff] text-[#1b3bbb] border-[#d4d1f5] hover:border-[#1b3bbb] hover:bg-[#f3f2fe]'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-xl text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Kelola Peserta</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold" 
                                  :class="selectedParticipants.length === 0 ? 'bg-rose-600 text-white animate-pulse' : 'bg-[#1b3bbb] text-white'" 
                                  x-text="selectedParticipants.length"></span>
                        </button>
                    </div>

                    <!-- Bidang Selection List Card -->
                    <div class="bg-[#f8f7ff] border border-[#d4d1f5] rounded-2xl p-3 space-y-2">
                        <!-- Inline Alert for 3-Bidang Limit -->
                        <div x-show="showBidangLimitWarning" x-cloak class="p-2.5 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-[11px] font-semibold flex items-center gap-2 animate-in fade-in duration-200 shadow-2xs">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Admin Bidang hanya dapat memilih maksimal 3 bidang (bidang Anda + maksimal 2 bidang tambahan).</span>
                        </div>

                        @if(Auth::user()->isSekretarisMaster())
                            <label class="flex items-center gap-2 px-2.5 py-1.5 bg-white rounded-xl border border-[#d4d1f5] hover:border-[#1b3bbb] transition-all cursor-pointer select-none">
                                <input type="checkbox" x-model="semuaOrang" @change="toggleSemua()" 
                                       class="w-4 h-4 rounded border-[#d4d1f5] text-[#1b3bbb] focus:ring-[#1b3bbb] transition-all">
                                <span class="text-xs text-[#2e2552] font-extrabold">Semua Bidang / Rapat Lintas Dinas (Semua Orang)</span>
                            </label>
                        @endif

                        <div class="grid grid-cols-1 gap-1 max-h-[140px] overflow-y-auto pr-1">
                            <!-- Checkbox Kepala Dinas (Kadin) -->
                            <label class="flex items-center justify-between px-2.5 py-1.5 rounded-xl border border-transparent hover:border-[#d4d1f5] hover:bg-white transition-all cursor-pointer select-none">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" value="kadin" x-model="kadinTarget" @change="toggleKadinTarget()"
                                           class="w-4 h-4 rounded border-[#d4d1f5] text-[#1b3bbb] focus:ring-[#1b3bbb] focus:ring-offset-0 transition-all shrink-0">
                                    <span class="text-xs text-[#2e2552] font-semibold">
                                        Kepala Dinas <span class="text-[#5a508f] font-normal">(Kadin)</span>
                                    </span>
                                </div>
                            </label>
                            @foreach($bidangs as $bid)
                                @php
                                    $isSekretariatItem = (strcasecmp($bid->singkatan, 'Sekretariat') === 0 || strcasecmp($bid->nama, 'Sekretariat') === 0);
                                    $isSubbag = (str_contains(strtolower($bid->nama), 'subbag') || str_contains(strtolower($bid->singkatan), 'subbag')) && !$isSekretariatItem;
                                    $isUserBidang = Auth::user()->isSekretarisBidang() && Auth::user()->bidang_id == $bid->id;
                                @endphp
                                @if($isSekretariatItem)
                                    <!-- Checkbox Lingkup Sekretariat (Right above Sekretariat) -->
                                    <label class="flex items-center gap-2 px-2.5 py-1.5 bg-indigo-50/80 rounded-xl border border-indigo-200/80 hover:border-[#1b3bbb] transition-all cursor-pointer select-none my-0.5">
                                        <input type="checkbox" x-model="semuaSekretariat" @change="toggleSemuaSekretariat()" 
                                               class="w-4 h-4 rounded border-[#d4d1f5] text-[#1b3bbb] focus:ring-[#1b3bbb] transition-all shrink-0">
                                        <span class="text-xs text-[#1b3bbb] font-extrabold">Lingkup Sekretariat</span>
                                    </label>
                                @endif
                                <label class="flex items-center justify-between px-2.5 py-1.5 rounded-xl border border-transparent hover:border-[#d4d1f5] hover:bg-white transition-all cursor-pointer select-none {{ $isSubbag ? 'pl-6' : '' }}">
                                    <div class="flex items-center gap-2">
                                        @if($isSubbag)
                                            <span class="text-[#8e88dd] text-xs font-bold shrink-0 -mr-1">└</span>
                                        @endif
                                        <input type="checkbox" value="{{ $bid->id }}" x-model="bidangs" @change="checkBidang('{{ $bid->id }}')"
                                               @if($isUserBidang) disabled @endif
                                               class="w-4 h-4 rounded border-[#d4d1f5] text-[#1b3bbb] focus:ring-[#1b3bbb] focus:ring-offset-0 transition-all shrink-0">
                                        <span class="text-xs text-[#2e2552] font-semibold {{ $isUserBidang ? 'font-extrabold text-[#1b3bbb]' : '' }}">
                                            {{ $bid->nama }} <span class="text-[#5a508f] font-normal">({{ $bid->singkatan }})</span>
                                        </span>
                                    </div>
                                    @if($isUserBidang)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200/80 shrink-0 ml-2">
                                            Wajib Hadir
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- KELOLA PESERTA MODAL (POPOVER OVERLAY) -->
                    <div x-show="participantModalOpen" x-cloak 
                         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 bg-slate-950/70 backdrop-blur-sm select-none">
                        <div @click.away="participantModalOpen = false" 
                             class="bg-white rounded-3xl shadow-2xl border border-[#d4d1f5] w-full max-w-xl flex flex-col max-h-[85vh] overflow-hidden animate-in fade-in zoom-in duration-200">
                            
                            <!-- Header Modal Kelola Peserta -->
                            <div class="px-5 py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-white/10 rounded-xl border border-white/15 shrink-0">
                                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-extrabold text-white">Kelola Peserta Rapat & Kewenangan</h3>
                                        <p class="text-[11px] text-indigo-100 font-medium">Pilih tepat 1 Admin dari setiap unit yang diundang</p>
                                    </div>
                                </div>
                                <button @click="participantModalOpen = false" type="button" class="p-1.5 bg-white/10 hover:bg-rose-500/80 rounded-xl text-white transition-all cursor-pointer shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Body Modal Kelola Peserta -->
                            <div x-ref="modalBody" class="p-4 sm:p-5 overflow-y-auto no-scrollbar space-y-4 flex-1 bg-slate-50/50">
                                 <!-- Search Bar -->
                                <div class="relative">
                                    <input type="text" x-model="searchParticipant" placeholder="Cari nama, NIP, atau jabatan peserta..." 
                                           class="w-full pl-9 pr-8 py-2 bg-white border border-[#d4d1f5] rounded-xl text-xs text-[#2e2552] placeholder-slate-400 focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 transition-all font-semibold shadow-2xs">
                                    <svg class="w-4 h-4 text-[#1b3bbb] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <button type="button" x-show="searchParticipant.length > 0" @click="searchParticipant = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                <!-- Inline Validation Alert Banner inside Kelola Peserta Modal -->
                                <template x-if="adminValidationErrorMessage">
                                    <div class="p-3 bg-amber-50 border-2 border-amber-400 text-amber-900 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm animate-in fade-in duration-200">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="p-1 bg-amber-200/80 rounded-lg shrink-0">
                                                <svg class="w-4 h-4 text-amber-800 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                            </div>
                                            <span x-text="adminValidationErrorMessage" class="leading-tight"></span>
                                        </div>
                                        <button type="button" @click="adminValidationErrorMessage = ''" class="p-1 text-amber-700 hover:text-amber-950 hover:bg-amber-200/60 rounded-lg transition-all cursor-pointer shrink-0 ml-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </template>

                                <template x-if="bidangs.length === 0">
                                    <div class="p-8 text-center bg-white rounded-2xl border border-dashed border-[#d4d1f5] shadow-2xs">
                                        <p class="text-xs text-slate-500 font-bold">Pilih minimal satu bidang di atas terlebih dahulu untuk mengelola peserta.</p>
                                    </div>
                                </template>

                                <!-- Group Card Khusus Kepala Dinas (Kadin) -->
                                <template x-if="kadinUser && kadinTarget && (!searchParticipant || kadinUser.name.toLowerCase().includes(searchParticipant.toLowerCase()) || kadinUser.jabatan.toLowerCase().includes(searchParticipant.toLowerCase()))">
                                    <div class="bg-gradient-to-r from-purple-100 via-purple-100/90 to-purple-200/70 border border-purple-400 rounded-2xl p-3.5 space-y-2.5 shadow-2xs">
                                        <div class="flex items-center justify-between pb-2 border-b border-purple-200">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                                                <span class="text-xs font-extrabold text-purple-950">Kepala Dinas (Kadin)</span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                            <label class="flex items-start gap-2.5 p-2 bg-white/90 rounded-xl border border-purple-300 cursor-pointer select-none transition-all">
                                                <input type="checkbox" :value="kadinUser.id" x-model="selectedParticipants" :disabled="true" class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 mt-0.5 shrink-0 opacity-80 cursor-not-allowed">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-purple-950 leading-tight truncate">
                                                        <span x-text="kadinUser.name"></span>
                                                    </div>
                                                    <div class="text-[10px] text-purple-700 font-medium truncate" x-text="kadinUser.jabatan || 'Kepala Dinas / Kadin'"></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </template>

                                <template x-for="bidang in visibleBidangs" :key="bidang.id">
                                    <div class="bg-white border border-[#d4d1f5]/80 rounded-2xl p-3.5 space-y-2.5 shadow-2xs">
                                        <div class="flex items-center justify-between pb-2 border-b border-[#d4d1f5]/40">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full bg-[#1b3bbb]"></span>
                                                <span class="text-xs font-extrabold text-[#2e2552]" x-text="bidang.nama + ' (' + bidang.singkatan + ')'"></span>
                                            </div>
                                            <button type="button" @click="toggleBidangUsers(bidang.id)" class="text-[11px] font-extrabold text-[#1b3bbb] hover:underline cursor-pointer">
                                                <span x-text="isBidangAllChecked(bidang.id) ? 'Hapus Centang Staf' : 'Centang Semua Staf'"></span>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                            <template x-for="user in filteredUsers(bidang.users)" :key="user.id">
                                                <label class="flex items-start gap-2.5 p-2.5 rounded-xl border select-none transition-all cursor-pointer"
                                                       :class="isAdminUser(user) 
                                                               ? 'bg-gradient-to-r from-amber-50/90 to-amber-100/60 border-amber-300 hover:border-amber-400' 
                                                               : (isKetuaUser(user)
                                                                   ? 'bg-gradient-to-r from-purple-100 via-purple-100/90 to-purple-200/70 border-purple-400 hover:border-purple-500' 
                                                                   : 'bg-[#f8f7ff] hover:bg-indigo-50/50 border-[#d4d1f5]/60 hover:border-[#1b3bbb]')">
                                                    
                                                    <input type="checkbox" 
                                                           :value="user.id" 
                                                           x-model="selectedParticipants" 
                                                           :disabled="isMandatoryUser(user)"
                                                           @change="toggleUserParticipant(user)" 
                                                           class="w-4 h-4 rounded border-slate-300 mt-0.5 shrink-0"
                                                           :class="isAdminUser(user) ? 'text-amber-600 focus:ring-amber-500' : (isKetuaUser(user) ? 'text-purple-600 focus:ring-purple-500' : 'text-[#1b3bbb] focus:ring-[#1b3bbb]')"
                                                           :class="isMandatoryUser(user) ? 'opacity-80 cursor-not-allowed' : ''">
                                                    
                                                    <div class="min-w-0 flex-1">
                                                        <div class="text-xs font-bold leading-tight truncate" :class="isAdminUser(user) ? 'text-amber-950' : (isKetuaUser(user) ? 'text-purple-950' : 'text-[#2e2552]')">
                                                            <span x-text="user.name" class="truncate"></span>
                                                        </div>
                                                        <div class="text-[10px] font-medium truncate mt-0.5" :class="isAdminUser(user) ? 'text-amber-800' : (isKetuaUser(user) ? 'text-purple-800' : 'text-[#5a508f]')" x-text="user.jabatan"></div>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Footer Modal Kelola Peserta -->
                            <div class="px-5 py-3.5 bg-[#f8f7ff] border-t border-[#d4d1f5] flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                                <div class="text-xs font-bold text-[#5a508f] flex items-center gap-1">
                                    <template x-if="selectedParticipants.length === 0">
                                        <span class="text-rose-600 font-black flex items-center gap-1">Pilih minimal 1 peserta!</span>
                                    </template>
                                    <template x-if="selectedParticipants.length > 0">
                                        <span>Total Terpilih: <span class="text-[#1b3bbb] font-extrabold" x-text="selectedParticipants.length"></span> Peserta</span>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                    <button type="button" @click="participantModalOpen = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-[#2e2552] text-xs font-bold rounded-xl transition-all cursor-pointer">
                                        Tutup
                                    </button>
                                    <button type="button" 
                                            @click="if(validateAdminSelection()) { participantModalOpen = false; }" 
                                            :class="selectedParticipants.length === 0 ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-[#1b3bbb] hover:bg-[#09103c] text-white shadow-md shadow-[#1b3bbb]/20 cursor-pointer'"
                                            class="px-5 py-2 text-xs font-extrabold rounded-xl transition-all">
                                        Simpan Peserta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- Presensi Toggle & Action Footer -->
                <div class="flex items-center justify-between gap-2 border-t border-[#d4d1f5]/60 pt-3 shrink-0">
                    <div class="flex items-center justify-between p-2 px-3 bg-gradient-to-r from-[#f8f7ff] to-[#f3f2fe] border border-[#d4d1f5] rounded-xl flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-3.5 h-3.5 text-[#1b3bbb] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <label for="butuh_presensi" class="text-[11px] sm:text-xs font-bold text-[#2e2552] cursor-pointer whitespace-nowrap truncate">Memerlukan Presensi Digital?</label>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer select-none ml-1.5 shrink-0" x-data="{ checked: true }">
                            <input type="checkbox" name="butuh_presensi" id="butuh_presensi" :checked="checked" value="1" class="sr-only">
                            <div @click="checked = !checked" 
                                 :style="checked ? 'background-color: #1b3bbb !important;' : 'background-color: #cbd5e1 !important;'"
                                 class="w-9 h-5 rounded-full p-0.5 transition-all duration-200 relative flex items-center cursor-pointer shadow-inner shrink-0">
                                <div :style="checked ? 'transform: translateX(16px) !important; background-color: #ffffff !important;' : 'transform: translateX(0px) !important; background-color: #ffffff !important;'"
                                     class="w-4 h-4 rounded-full shadow-md transition-transform duration-200 border border-slate-200"></div>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-1.5 shrink-0">
                        <button type="button" @click="openAddModal = false"
                                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-[#2e2552] text-xs font-bold rounded-xl transition-all active:scale-[0.98] whitespace-nowrap">
                            Batalkan
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-[#1b3bbb] hover:bg-[#09103c] text-white text-xs font-extrabold rounded-xl shadow-md shadow-[#1b3bbb]/20 transition-all active:scale-[0.98] flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                            <span>Simpan Agenda</span>
                            <svg class="w-3.5 h-3.5 text-indigo-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection

