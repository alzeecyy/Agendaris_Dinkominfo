@extends('layouts.app')

@section('title', 'Dashboard Utama')

@section('content')
<div class="space-y-2.5 sm:space-y-6 pb-12 sm:pb-16">

    @php
        $hasOneKpiCard = Auth::user()->isKetuaMaster();
        $hasThreeKpiCards = Auth::user()->role === 'sekretaris_bidang';
    @endphp
    <!-- KPI Summary Grid (Greeting & Cards) -->
    <div class="grid grid-cols-2 {{ $hasThreeKpiCards ? 'lg:grid-cols-4' : 'md:grid-cols-3' }} gap-2 sm:gap-6 items-stretch">
        
        <!-- Welcome Card -->
        <div class="col-span-2 {{ $hasOneKpiCard ? 'md:col-span-2' : 'lg:col-span-1' }} bg-gradient-to-br from-[#1b3bbb] via-[#102480] to-[#0b1554] text-white rounded-xl md:rounded-[32px] p-3 sm:p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
            <!-- Decorative circle overlay -->
            <div class="absolute -top-12 -right-12 w-28 h-28 bg-white/10 rounded-full"></div>
            
            <div class="space-y-1 sm:space-y-2 z-10">
                <span class="text-[8.5px] sm:text-[10px] font-bold uppercase tracking-widest text-blue-200">Ringkasan Hari Ini</span>
                <h3 class="text-sm sm:text-xl font-black leading-tight text-white">Pantau Agenda Rapat & Notulensi Kerja</h3>
                <p class="text-[10px] sm:text-xs text-blue-100/90 leading-relaxed">Kelola dan hadiri koordinasi kedinasan Dinkominfo Banyumas secara terpadu.</p>
            </div>
            
            <div class="mt-2.5 sm:mt-6 z-10">
                <a href="{{ route('calendar') }}" 
                   class="inline-flex items-center gap-1 px-2.5 py-1 sm:px-4 sm:py-2 bg-white text-[#1b3bbb] hover:bg-blue-50 text-[10px] sm:text-xs font-bold rounded-lg sm:rounded-xl shadow-sm transition-all duration-200">
                    <span>Lihat Kalender Rinci</span>
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Role-Specific KPI Cards -->
        @if(Auth::user()->role === 'staff')
            <!-- Card 1: Week Agendas -->
            <a href="{{ $links['week_agendas'] ?? route('calendar') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-[#1b3bbb] rounded-xl md:rounded-[32px] p-2.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] sm:text-xs font-bold text-[#5a508f] group-hover:text-[#1b3bbb] transition-colors uppercase truncate">Agenda Minggu Ini</span>
                    <div class="kpi-icon kpi-icon-navy p-1 sm:p-2 bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-lg sm:rounded-2xl group-hover:!bg-[#1b3bbb] group-hover:!text-white transition-all duration-200 shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5 md:w-6 md:h-6 text-[#1b3bbb] group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5 sm:mt-4">
                    <h2 class="text-xl sm:text-3xl md:text-4xl font-black text-[#2e2552] group-hover:scale-105 origin-left transition-transform duration-200">{{ $kpi['week_agendas'] ?? 0 }}</h2>
                    <p class="text-[9px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium truncate">Agenda kegiatan terjadwal &rarr;</p>
                </div>
            </a>

            <!-- Card 2: Unfilled Presence -->
            @php
                $pendingPresenceCount = $kpi['pending_presence'] ?? 0;
            @endphp
            @if($pendingPresenceCount > 0)
                <a href="{{ $links['pending_presence'] ?? route('agenda.today') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-rose-400 rounded-xl md:rounded-[32px] p-2.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] sm:text-xs font-bold text-[#5a508f] group-hover:text-rose-600 transition-colors uppercase truncate">Belum Presensi</span>
                        <div class="kpi-icon kpi-icon-rose p-1 sm:p-2 bg-rose-50 text-rose-500 rounded-lg sm:rounded-2xl group-hover:!bg-[#f43f5e] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5 md:w-6 md:h-6 text-rose-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-1.5 sm:mt-4">
                        <h2 class="text-xl sm:text-3xl md:text-4xl font-black text-rose-600 group-hover:scale-105 origin-left transition-transform duration-200">{{ $pendingPresenceCount }}</h2>
                        <p class="text-[9px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium truncate">Konfirmasi kehadiran &rarr;</p>
                    </div>
                </a>
            @else
                <button type="button" onclick="Swal.fire({
                    title: 'Tidak Ada Presensi Aktif',
                    text: 'Saat ini tidak ada agenda rapat yang memerlukan pengisian presensi Anda. Seluruh presensi telah selesai diisi.',
                    icon: 'success',
                    confirmButtonColor: '#1b3bbb',
                    confirmButtonText: 'Selesai'
                })" class="text-left kpi-card bg-white border border-[#d4d1f5]/60 hover:border-rose-400 rounded-xl md:rounded-[32px] p-2.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer w-full">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] sm:text-xs font-bold text-[#5a508f] group-hover:text-rose-600 transition-colors uppercase truncate">Belum Presensi</span>
                        <div class="kpi-icon kpi-icon-rose p-1 sm:p-2 bg-rose-50 text-rose-500 rounded-lg sm:rounded-2xl group-hover:!bg-[#f43f5e] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5 md:w-6 md:h-6 text-rose-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-1.5 sm:mt-4">
                        <h2 class="text-xl sm:text-3xl md:text-4xl font-black text-rose-600 group-hover:scale-105 origin-left transition-transform duration-200">0</h2>
                        <p class="text-[9px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium truncate">Tidak ada presensi aktif &rarr;</p>
                    </div>
                </button>
            @endif

        @elseif(Auth::user()->role === 'sekretaris_bidang')
            <!-- Card 1: Bidang Agendas -->
            <a href="{{ $links['bidang_month_agendas'] ?? route('calendar') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-[#1b3bbb] rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-[#1b3bbb] transition-colors uppercase">Agenda Bidang Bulan Ini</span>
                    <div class="kpi-icon kpi-icon-navy p-1.5 sm:p-2 bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-xl sm:rounded-2xl group-hover:!bg-[#1b3bbb] group-hover:!text-white transition-all duration-200 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-[#1b3bbb] group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-2.5 sm:mt-4">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#2e2552] group-hover:scale-105 origin-left transition-transform duration-200">{{ $kpi['bidang_month_agendas'] ?? 0 }}</h2>
                    <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Kelola jadwal rapat bidang &rarr;</p>
                </div>
            </a>

            <!-- Card 2: Waiting Review -->
            @php
                $bidangPendingCount = $kpi['bidang_pending_reviews'] ?? 0;
            @endphp
            @if($bidangPendingCount > 0)
                <a href="{{ $links['bidang_pending_reviews'] ?? route('riwayat') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-amber-400 rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-amber-600 transition-colors uppercase">Menunggu Review Ketua</span>
                        <div class="kpi-icon kpi-icon-amber p-1.5 sm:p-2 bg-amber-50 text-amber-500 rounded-xl sm:rounded-2xl group-hover:!bg-[#f59e0b] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-amber-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 sm:mt-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-amber-600 group-hover:scale-105 origin-left transition-transform duration-200">{{ $bidangPendingCount }}</h2>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Diajukan ke Kepala Bidang &rarr;</p>
                    </div>
                </a>
            @else
                <button type="button" onclick="Swal.fire({
                    title: 'Tidak Ada Notulensi Pending',
                    text: 'Saat ini tidak ada draf notulensi rapat yang sedang menunggu review dari Kepala Bidang.',
                    icon: 'info',
                    confirmButtonColor: '#1b3bbb',
                    confirmButtonText: 'Mengerti'
                })" class="text-left kpi-card bg-white border border-[#d4d1f5]/60 hover:border-amber-400 rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer w-full">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-amber-600 transition-colors uppercase">Menunggu Review Ketua</span>
                        <div class="kpi-icon kpi-icon-amber p-1.5 sm:p-2 bg-amber-50 text-amber-500 rounded-xl sm:rounded-2xl group-hover:!bg-[#f59e0b] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-amber-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 sm:mt-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-amber-600 group-hover:scale-105 origin-left transition-transform duration-200">0</h2>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Belum ada draf pending &rarr;</p>
                    </div>
                </button>
            @endif

            <!-- Card 3: Need Revision -->
            @php
                $bidangRevisedCount = $kpi['bidang_revised_notulensi'] ?? 0;
            @endphp
            @if($bidangRevisedCount > 0)
                <a href="{{ $links['bidang_revised_notulensi'] ?? route('riwayat') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-rose-400 rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-rose-600 transition-colors uppercase">Notulensi Perlu Revisi</span>
                        <div class="kpi-icon kpi-icon-rose p-1.5 sm:p-2 bg-rose-50 text-rose-500 rounded-xl sm:rounded-2xl group-hover:!bg-[#f43f5e] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-rose-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 sm:mt-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-rose-600 group-hover:scale-105 origin-left transition-transform duration-200">{{ $bidangRevisedCount }}</h2>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Perbaiki &amp; ajukan ulang &rarr;</p>
                    </div>
                </a>
            @else
                <button type="button" onclick="Swal.fire({
                    title: 'Tidak Ada Catatan Revisi',
                    text: 'Saat ini tidak ada notulensi rapat yang dikembalikan pimpinan untuk direvisi.',
                    icon: 'success',
                    confirmButtonColor: '#1b3bbb',
                    confirmButtonText: 'Selesai'
                })" class="text-left kpi-card bg-white border border-[#d4d1f5]/60 hover:border-rose-400 rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer w-full">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-rose-600 transition-colors uppercase">Notulensi Perlu Revisi</span>
                        <div class="kpi-icon kpi-icon-rose p-1.5 sm:p-2 bg-rose-50 text-rose-500 rounded-xl sm:rounded-2xl group-hover:!bg-[#f43f5e] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-rose-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 sm:mt-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-rose-600 group-hover:scale-105 origin-left transition-transform duration-200">0</h2>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Tidak ada catatan revisi &rarr;</p>
                    </div>
                </button>
            @endif

        @elseif(Auth::user()->role === 'sekretaris_master')
            <!-- Card 1: Sekdin Pending Approvals -->
            @php
                $sekdinPendingCount = $kpi['sekdin_pending_reviews'] ?? 0;
            @endphp
            @if($sekdinPendingCount > 0)
                <a href="{{ $links['sekdin_pending_reviews'] ?? route('riwayat') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-amber-400 rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-amber-600 transition-colors uppercase">Notulensi Butuh Pengesahan</span>
                        <div class="kpi-icon kpi-icon-amber p-1.5 sm:p-2 bg-amber-50 text-amber-500 rounded-xl sm:rounded-2xl group-hover:!bg-[#f59e0b] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-amber-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 sm:mt-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-amber-600 group-hover:scale-105 origin-left transition-transform duration-200">{{ $sekdinPendingCount }}</h2>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Tinjau &amp; sahkan notulensi &rarr;</p>
                    </div>
                </a>
            @else
                <button type="button" onclick="Swal.fire({
                    title: 'Tidak Ada Notulensi Pending',
                    text: 'Saat ini tidak ada draf notulensi rapat yang membutuhkan pengesahan Anda.',
                    icon: 'info',
                    confirmButtonColor: '#1b3bbb',
                    confirmButtonText: 'Mengerti'
                })" class="text-left kpi-card bg-white border border-[#d4d1f5]/60 hover:border-amber-400 rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer w-full">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-amber-600 transition-colors uppercase">Notulensi Butuh Pengesahan</span>
                        <div class="kpi-icon kpi-icon-amber p-1.5 sm:p-2 bg-amber-50 text-amber-500 rounded-xl sm:rounded-2xl group-hover:!bg-[#f59e0b] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-amber-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 sm:mt-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-amber-600 group-hover:scale-105 origin-left transition-transform duration-200">0</h2>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Belum ada notulensi pending &rarr;</p>
                    </div>
                </button>
            @endif

            <!-- Card 2: Master Total Month -->
            <a href="{{ $links['master_month_agendas'] ?? route('calendar') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-[#1b3bbb] rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-[#1b3bbb] transition-colors uppercase">Agenda Dinas Bulan Ini</span>
                    <div class="kpi-icon kpi-icon-navy p-1.5 sm:p-2 bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-xl sm:rounded-2xl group-hover:!bg-[#1b3bbb] group-hover:!text-white transition-all duration-200 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-[#1b3bbb] group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-2.5 sm:mt-4">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#2e2552] group-hover:scale-105 origin-left transition-transform duration-200">{{ $kpi['master_month_agendas'] ?? 0 }}</h2>
                    <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Kalender agenda dinas &rarr;</p>
                </div>
            </a>

        @elseif(Auth::user()->isKetuaMaster())
            <!-- Card Agenda Minggu Ini (Untuk Kadis) -->
            <a href="{{ $links['ketua_week_agendas'] ?? route('calendar') }}" class="kpi-card col-span-2 md:col-span-1 bg-white border border-[#d4d1f5]/60 hover:border-[#1b3bbb] rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-[#1b3bbb] transition-colors uppercase">Agenda Minggu Ini</span>
                    <div class="kpi-icon kpi-icon-navy p-1.5 sm:p-2 bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-xl sm:rounded-2xl group-hover:!bg-[#1b3bbb] group-hover:!text-white transition-all duration-200 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-[#1b3bbb] group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-2.5 sm:mt-4">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1b3bbb] group-hover:scale-105 origin-left transition-transform duration-200">{{ $kpi['ketua_week_agendas'] ?? 0 }}</h2>
                    <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Lihat kalender &rarr;</p>
                </div>
            </a>

        @elseif(Auth::user()->isKetuaBidang())
            @php
                $pendingCountKb = $kpi['ketua_pending_reviews'] ?? 0;
            @endphp
            @if($pendingCountKb > 0)
                <!-- Card 1: Ketua Bidang Pending Approvals (Ada Data Pending) -->
                <a href="{{ $links['ketua_pending_reviews'] ?? route('riwayat') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-amber-400 rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-amber-600 transition-colors uppercase">Notulensi Butuh Pengesahan</span>
                        <div class="kpi-icon kpi-icon-amber p-1.5 sm:p-2 bg-amber-50 text-amber-500 rounded-xl sm:rounded-2xl group-hover:!bg-[#f59e0b] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-amber-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 sm:mt-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-amber-600 group-hover:scale-105 origin-left transition-transform duration-200">{{ $pendingCountKb }}</h2>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Tinjau &amp; sahkan notulensi &rarr;</p>
                    </div>
                </a>
            @else
                <!-- Card 1: Ketua Bidang Pending Approvals (Kosong 0 Data -> Pop Up SweetAlert2) -->
                <button type="button" onclick="Swal.fire({
                    title: 'Tidak Ada Notulensi Pending',
                    text: 'Saat ini tidak ada notulensi rapat yang membutuhkan pengesahan Anda.',
                    icon: 'info',
                    confirmButtonColor: '#1b3bbb',
                    confirmButtonText: 'Mengerti'
                })" class="text-left kpi-card bg-white border border-[#d4d1f5]/60 hover:border-amber-400 rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer w-full">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-amber-600 transition-colors uppercase">Notulensi Butuh Pengesahan</span>
                        <div class="kpi-icon kpi-icon-amber p-1.5 sm:p-2 bg-amber-50 text-amber-500 rounded-xl sm:rounded-2xl group-hover:!bg-[#f59e0b] group-hover:!text-white transition-all duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-amber-500 group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 sm:mt-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-amber-600 group-hover:scale-105 origin-left transition-transform duration-200">0</h2>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Tinjau &amp; sahkan notulensi &rarr;</p>
                    </div>
                </button>
            @endif

            <!-- Card 2: Agenda Minggu Ini (Untuk Kabid) -->
            <a href="{{ route('calendar') }}" class="kpi-card bg-white border border-[#d4d1f5]/60 hover:border-[#1b3bbb] rounded-2xl md:rounded-[32px] p-3.5 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 group cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-[#5a508f] group-hover:text-[#1b3bbb] transition-colors uppercase">Agenda Minggu Ini</span>
                    <div class="kpi-icon kpi-icon-navy p-1.5 sm:p-2 bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-xl sm:rounded-2xl group-hover:!bg-[#1b3bbb] group-hover:!text-white transition-all duration-200 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-[#1b3bbb] group-hover:!text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-2.5 sm:mt-4">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1b3bbb] group-hover:scale-105 origin-left transition-transform duration-200">{{ $kpi['ketua_week_agendas'] ?? 0 }}</h2>
                    <p class="text-[10px] sm:text-xs text-[#5a508f] mt-0.5 sm:mt-1 font-medium">Lihat kalender &rarr;</p>
                </div>
            </a>
        @endif

        <style>
            .kpi-card:hover .kpi-icon-navy {
                background-color: #1b3bbb !important;
                color: #ffffff !important;
            }
            .kpi-card:hover .kpi-icon-rose {
                background-color: #f43f5e !important;
                color: #ffffff !important;
            }
            .kpi-card:hover .kpi-icon-amber {
                background-color: #f59e0b !important;
                color: #ffffff !important;
            }
        </style>

    </div>

    <!-- MAIN TWO COLUMN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
        
        <!-- LEFT/MID COLUMN: MONTHLY CALENDAR CARD -->
        <div x-data="{ showMonthPicker: false, pickerYear: {{ $selectedMonth->year }} }" class="lg:col-span-2 bg-white border border-[#d4d1f5]/60 rounded-2xl md:rounded-[32px] p-3.5 sm:p-6 shadow-sm flex flex-col">
            
            <!-- Month selector header -->
            <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-3 mb-4 sm:mb-6">
                <!-- Month/Year Header (Click to Toggle Month Picker) -->
                <div @click="showMonthPicker = !showMonthPicker" 
                     class="cursor-pointer hover:bg-[#8e88dd]/10 px-2.5 py-1 -ml-2 rounded-xl transition-all duration-150 inline-block select-none"
                     title="Klik untuk memilih bulan">
                    <h2 class="text-base sm:text-lg font-black text-[#2e2552] tracking-wide inline-flex items-center gap-1.5">
                        <span x-show="!showMonthPicker">{{ $selectedMonth->translatedFormat('F Y') }}</span>
                        <span x-show="showMonthPicker" x-text="pickerYear"></span>
                        <!-- Dropdown Arrow Icon -->
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#8e88dd] transition-transform duration-200" :class="showMonthPicker ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </h2>
                    <p class="text-[10px] sm:text-xs text-[#5a508f]">Agenda Kerja Bulanan Dinkominfo</p>
                </div>
                
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button type="button" 
                            @click="if (showMonthPicker) { pickerYear-- } else { window.location.href = '{{ route('dashboard', ['month' => $selectedMonth->copy()->subMonth()->format('Y-m')]) }}' }"
                            class="p-1.5 sm:p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-[#2e2552] transition-colors">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <a href="{{ route('dashboard', ['month' => now()->format('Y-m')]) }}" 
                       class="px-2.5 py-1.5 sm:px-3.5 sm:py-1.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-[11px] sm:text-xs font-bold text-[#2e2552] transition-colors"
                       title="Kembali ke Bulan Ini">
                        Bulan ke-{{ $selectedMonth->month }}
                    </a>
                    <button type="button" 
                            @click="if (showMonthPicker) { pickerYear++ } else { window.location.href = '{{ route('dashboard', ['month' => $selectedMonth->copy()->addMonth()->format('Y-m')]) }}' }"
                            class="p-1.5 sm:p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl hover:bg-[#8e88dd]/20 text-[#2e2552] transition-colors">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Month grid header -->
            <div x-show="!showMonthPicker" class="grid grid-cols-7 gap-1 sm:gap-2 text-center text-[9px] sm:text-[10px] font-bold text-[#8e88dd] uppercase tracking-wider mb-1.5">
                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span class="text-indigo-500 font-extrabold">Sab</span><span class="text-rose-500 font-extrabold">Min</span>
            </div>

            <!-- Month grid days -->
            <div x-show="!showMonthPicker" class="grid grid-cols-7 gap-1 sm:gap-2 text-xs">
                @foreach($gridDates as $idx => $date)
                    @php
                        $dateStr = $date->toDateString();
                        $isCurrentMonth = $date->month === $selectedMonth->month;
                        $isToday = $date->isToday();
                        $dayEvents = $agendasByDate[$dateStr] ?? [];
                        
                        // Tooltip positioning based on column index
                        $colPosition = $idx % 7;
                        if ($colPosition === 0) {
                            $tooltipClass = "left-0 translate-x-0";
                        } elseif ($colPosition === 6) {
                            $tooltipClass = "right-0 left-auto translate-x-0";
                        } else {
                            $tooltipClass = "left-1/2 -translate-x-1/2";
                        }
                        
                        $isSunday = $date->isSunday();
                        $isSaturday = $date->isSaturday();
                    @endphp
                    
                    <!-- Calendar Day Cell with AlpineJS hover popover -->
                    <div x-data="{ open: false }" 
                          @mouseenter="open = true" 
                          @mouseleave="open = false"
                          class="relative min-h-[36px] sm:min-h-[60px] md:min-h-[75px] p-0.5 sm:p-2 {{ ($isSunday || $isSaturday) ? 'bg-rose-50' : 'bg-[#fcfbff]' }} border border-[#d4d1f5]/30 rounded-lg sm:rounded-2xl flex flex-col justify-between transition-all duration-200 hover:border-[#8e88dd]/50 hover:bg-[#f8f7ff]">
                        
                        <!-- Day Number Header -->
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[10px] sm:text-[11px] 
                                {{ $isToday ? 'bg-[#2e2552] text-white px-1.5 sm:px-2 py-0.5 rounded-md sm:rounded-lg shadow-sm' : ($isCurrentMonth ? ($isSunday ? 'text-rose-500 font-black' : ($isSaturday ? 'text-indigo-500 font-black' : 'text-[#2e2552]')) : ($isSunday ? 'text-rose-300' : ($isSaturday ? 'text-indigo-300' : 'text-[#d4d1f5]'))) }}">
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
                                        'rapat' => 'bg-[#ef4444]',
                                        'sosialisasi' => 'bg-[#3b82f6]',
                                        'pelatihan' => 'bg-[#10b981]',
                                        'kegiatan_lainnya' => 'bg-[#94a3b8]',
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
                                 class="absolute bottom-full {{ $tooltipClass }} mb-2.5 w-64 bg-white text-[#2e2552] p-4 rounded-2xl shadow-2xl z-30 text-[10px] space-y-3 pointer-events-none border border-[#d4d1f5]/60 overflow-hidden">
                                <div class="font-bold border-b border-[#d4d1f5]/40 pb-1.5 flex justify-between text-xs">
                                    <span class="text-[#2e2552]">Agenda Kegiatan</span>
                                    <span class="text-[#8e88dd]">{{ $date->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="space-y-2 max-h-48 overflow-y-auto no-scrollbar pr-1">
                                    @foreach($dayEvents as $evt)
                                        @php
                                            $badgeColors = [
                                                'rapat' => 'bg-rose-500',
                                                'sosialisasi' => 'bg-blue-500',
                                                'pelatihan' => 'bg-emerald-500',
                                                'kegiatan_lainnya' => 'bg-slate-400',
                                            ];
                                            $badgeColor = $badgeColors[$evt->kategori ?? ''] ?? 'bg-[#8e88dd]';
                                        @endphp
                                        <div class="flex items-start gap-2 pb-2 border-b border-dashed border-[#d4d1f5]/20 last:border-0 last:pb-0">
                                            <span class="inline-block text-[9px] font-black text-white {{ $badgeColor }} px-1.5 py-0.5 rounded-md shrink-0">
                                                {{ $evt->jam_mulai }}
                                            </span>
                                            <span class="font-semibold text-[10px] leading-tight text-[#2e2552]">
                                                {{ $evt->judul }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            <!-- Month Picker Grid view -->
            <div x-show="showMonthPicker" x-cloak class="grid grid-cols-3 gap-3 text-center text-xs py-4">
                <template x-for="(mName, mIdx) in ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']" :key="mIdx">
                    <button type="button" 
                            @click="window.location.href = '/dashboard?month=' + pickerYear + '-' + String(mIdx + 1).padStart(2, '0')"
                            class="py-4 rounded-2xl font-bold border transition-all duration-200"
                            :class="pickerYear === {{ $selectedMonth->year }} && mIdx === {{ $selectedMonth->month - 1 }} 
                                ? 'bg-[#2e2552] text-white border-[#2e2552] shadow-md shadow-[#2e2552]/10' 
                                : 'border-[#d4d1f5]/60 text-[#5a508f] hover:bg-[#8e88dd]/10 hover:text-[#2e2552] bg-white'">
                        <span x-text="mName"></span>
                    </button>
                </template>
            </div>

            <!-- Color code legend -->
            <div x-show="!showMonthPicker" class="flex flex-wrap items-center gap-4 mt-6 border-t border-[#d4d1f5]/40 pt-4 text-[10px] font-bold uppercase tracking-wider text-[#5a508f]">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span> Rapat</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></span> Sosialisasi</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span> Pelatihan</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#94a3b8]"></span> Kegiatan Lainnya</span>
            </div>

        </div>

        <!-- RIGHT COLUMN: ACTIONABLE HIGHLIGHTS & HISTORY -->
        <div class="space-y-4 sm:space-y-6 flex flex-col h-full justify-between">
            
            <!-- Highlights Panel -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-2xl md:rounded-[32px] p-3.5 sm:p-6 shadow-sm flex flex-col min-h-[250px] flex-1">
                <h3 class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-[#2e2552] mb-3 sm:mb-4">Perhatian Khusus</h3>
                
                <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1 flex-1">
                    @forelse($highlights as $hl)
                        <div class="p-2.5 {{ ($hl['type'] ?? '') === 'revision' ? 'bg-rose-50/90 border-rose-300' : 'bg-[#f8f7ff] border-amber-300/30' }} border rounded-xl flex flex-col gap-1.5 shadow-2xs">
                            <div class="flex items-start gap-1.5">
                                <svg class="w-3.5 h-3.5 {{ ($hl['type'] ?? '') === 'revision' ? 'text-rose-600' : 'text-amber-500' }} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-[9.5px] {{ ($hl['type'] ?? '') === 'revision' ? 'text-rose-950 font-bold' : 'text-[#5a508f] font-semibold' }} leading-snug">{{ $hl['text'] }}</p>
                            </div>
                            @if(isset($hl['url']))
                                <a href="{{ $hl['url'] }}" 
                                   class="self-end px-3 py-1.5 {{ ($hl['type'] ?? '') === 'revision' ? 'bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 shadow-rose-600/20' : 'bg-gradient-to-r from-[#1b3bbb] to-[#0b1554] hover:from-[#0b1554] hover:to-[#1b3bbb] shadow-[#1b3bbb]/20' }} text-white text-[9px] sm:text-[10px] font-bold rounded-xl transition-all duration-300 shadow-md active:scale-95">
                                    {{ $hl['action_text'] }}
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="py-6 text-center">
                            <p class="text-xs text-slate-400 italic">Tidak ada tindakan mendesak hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity History Card -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-2xl md:rounded-[32px] p-3.5 sm:p-6 shadow-sm flex flex-col min-h-[250px] flex-1">
                <div class="flex items-center justify-between mb-3.5 sm:mb-4">
                    <h3 class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-[#2e2552]">Riwayat Kegiatan</h3>
                    <a href="{{ route('riwayat') }}" class="text-[10px] text-[#8e88dd] hover:text-[#2e2552] font-bold transition-colors">Lihat Semua</a>
                </div>

                <div class="space-y-2.5 max-h-[220px] overflow-y-auto pr-1 flex-1">
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
                                @elseif($rw->status_kehadiran === 'alfa')
                                    <span class="text-[9px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-lg border border-red-100 font-semibold">Alfa</span>
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
