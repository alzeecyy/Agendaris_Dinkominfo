@extends('layouts.app')

@section('title', 'Riwayat Kegiatan')

@section('content')
<div x-data="{ 
    searchQuery: '',
    filterKategori: '',
    filterTanggal: '',
    filterStatus: '',
    filterNotulensiStatus: '{{ $selectedNotulensiStatus ?? "" }}',
    currentPage: 1,
    itemsPerPage: 10,
    agendas: [
        @foreach($riwayatData as $item)
        {
            id: {{ $item->id }},
            judul: '{{ addslashes($item->judul) }}',
            kategori: '{{ $item->kategori }}',
            tanggal: '{{ $item->tanggal->toDateString() }}',
            status_kehadiran: '{{ $item->status_kehadiran ?? '' }}',
            notulensi_status: '{{ $item->notulensi_status ?? '' }}',
            lokasi: '{{ addslashes($item->lokasi) }}'
        },
        @endforeach
    ],
    checkSearch(title, query) {
        if (!query) return true;
        const q = query.toLowerCase().trim();
        const t = title.toLowerCase();
        return t.includes(q);
    },
    matchesFilter(judul, kategori, tanggalStr, statusKehadiran, notulensiStatus) {
        const matchesSearch = this.checkSearch(judul, this.searchQuery);
        const matchesKategori = !this.filterKategori || kategori === this.filterKategori;
        const matchesTanggal = !this.filterTanggal || tanggalStr === this.filterTanggal;
        const matchesStatus = !this.filterStatus || statusKehadiran === this.filterStatus;
        const matchesNotulensi = !this.filterNotulensiStatus || notulensiStatus === this.filterNotulensiStatus;
        
        return matchesSearch && matchesKategori && matchesTanggal && matchesStatus && matchesNotulensi;
    },
    get filteredAgendas() {
        return this.agendas.filter(a => {
            const matchesSearch = this.checkSearch(a.judul, this.searchQuery);
            const matchesKategori = !this.filterKategori || a.kategori === this.filterKategori;
            const matchesTanggal = !this.filterTanggal || a.tanggal === this.filterTanggal;
            const matchesStatus = !this.filterStatus || a.status_kehadiran === this.filterStatus;
            const matchesNotulensi = !this.filterNotulensiStatus || a.notulensi_status === this.filterNotulensiStatus;
            
            return matchesSearch && matchesKategori && matchesTanggal && matchesStatus && matchesNotulensi;
        });
    },
    get totalPages() {
        return Math.ceil(this.filteredAgendas.length / this.itemsPerPage) || 1;
    },
    get displayedPages() {
        const total = this.totalPages;
        const current = this.currentPage;
        const maxVisible = 3;
        
        if (total <= maxVisible) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }
        
        let start = Math.max(1, current - 1);
        let end = start + maxVisible - 1;
        
        if (end > total) {
            end = total;
            start = Math.max(1, end - maxVisible + 1);
        }
        
        const pages = [];
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    },
    isAgendaVisible(agendaId) {
        const index = this.filteredAgendas.findIndex(a => a.id === agendaId);
        if (index === -1) return false;
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return index >= start && index < end;
    },
    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.stripeRows();
        }
    },
    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.stripeRows();
        }
    },
    setPage(page) {
        this.currentPage = page;
        this.stripeRows();
    },
    resetPagination() {
        this.currentPage = 1;
        this.stripeRows();
    },
    stripeRows() {
        this.$nextTick(() => {
            let visibleIndex = 0;
            document.querySelectorAll('.agenda-row').forEach(row => {
                if (row.style.display !== 'none') {
                    if (visibleIndex % 2 === 0) {
                        row.style.backgroundColor = '#ffffff';
                    } else {
                        row.style.backgroundColor = '#f4f7ff';
                    }
                    visibleIndex++;
                }
            });
        });
    }
} 
x-init="
    $watch('searchQuery', () => resetPagination());
    $watch('filterKategori', () => resetPagination());
    $watch('filterTanggal', () => resetPagination());
    $watch('filterStatus', () => resetPagination());
    stripeRows();
"
class="space-y-6">
    <div>
        <h1 class="text-xl font-black text-[#2e2552] tracking-wide">Riwayat Kegiatan & Rapat</h1>
        <p class="text-xs text-[#5a508f] mt-0.5">Arsip seluruh kegiatan dan status kehadiran Anda di Dinkominfo</p>
    </div>

    <!-- History Table Card -->
    <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-2.5 sm:p-6 shadow-sm overflow-hidden">
        
        <!-- Searchbar & Filter Toolbar -->
        <div class="bg-[#f8f7ff] border border-[#d4d1f5]/60 rounded-2xl p-3 sm:p-4 space-y-2.5 sm:space-y-3 mb-4 sm:mb-6">
            <!-- Row 1: Searchbar + Reset Filter -->
            <div class="flex items-center gap-2.5 w-full">
                <div class="relative flex-1">
                    <input type="text" x-model="searchQuery" placeholder="Cari nama agenda kegiatan atau lokasi..."
                           class="w-full pl-9 pr-8 py-2 sm:py-2.5 bg-white border border-[#d4d1f5]/80 rounded-xl text-xs text-[#2e2552] placeholder-[#5a508f]/50 font-medium focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all shadow-2xs">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-[#1b3bbb]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <button type="button" x-show="filterKategori || filterTanggal || filterStatus || searchQuery" x-cloak
                        @click="filterKategori = ''; filterTanggal = ''; filterStatus = ''; searchQuery = '';" 
                        class="py-2 sm:py-2.5 px-3.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Reset Filter</span>
                </button>
            </div>
            
            <!-- Row 2: 4 Equal Width Filter Inputs (Exact 100% Width Match) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 items-center w-full">
                <!-- Kategori Filter -->
                <div class="relative w-full">
                    <select x-model="filterKategori" 
                            style="background-image: none !important; -webkit-appearance: none !important; -moz-appearance: none !important; appearance: none !important;"
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 rounded-xl text-xs text-[#2e2552] font-semibold cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all shadow-2xs truncate">
                        <option value="">Semua Kategori</option>
                        <option value="rapat">Rapat</option>
                        <option value="sosialisasi">Sosialisasi</option>
                        <option value="pelatihan">Pelatihan</option>
                        <option value="kegiatan_lainnya">Kegiatan Lainnya</option>
                    </select>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10"></path>
                        </svg>
                    </div>
                    <!-- Custom Arrow Right -->
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#5a508f] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Tanggal Filter -->
                <div class="relative w-full">
                    <input type="date" x-model="filterTanggal" 
                           class="w-full pl-8 pr-3 py-2 bg-white border border-[#d4d1f5]/80 rounded-xl text-xs text-[#2e2552] font-semibold focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all shadow-2xs cursor-pointer">
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Status Kehadiran Filter -->
                <div class="relative w-full">
                    <select x-model="filterStatus" 
                            style="background-image: none !important; -webkit-appearance: none !important; -moz-appearance: none !important; appearance: none !important;"
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 rounded-xl text-xs text-[#2e2552] font-semibold cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all shadow-2xs truncate">
                        <option value="">Semua Kehadiran</option>
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alfa">Alfa</option>
                    </select>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <!-- Custom Arrow Right -->
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#5a508f] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Status Notulensi Filter -->
                <div class="relative w-full">
                    <select x-model="filterNotulensiStatus" 
                            style="background-image: none !important; -webkit-appearance: none !important; -moz-appearance: none !important; appearance: none !important;"
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 rounded-xl text-xs text-[#2e2552] font-semibold cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all shadow-2xs truncate">
                        <option value="">Semua Status Notulen</option>
                        <option value="draft">Belum Ada Draft</option>
                        <option value="menunggu_review">Menunggu Review</option>
                        <option value="revisi">Perlu Revisi</option>
                        <option value="disahkan">Telah Disahkan</option>
                    </select>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <!-- Custom Arrow Right -->
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#5a508f] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[10.5px] sm:text-sm text-[#2e2552]">
                <thead style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="bg-[#ebf2ff] text-[#1b3bbb] border-y border-[#bfd5ff] select-none">
                    <tr style="background-color: #ebf2ff !important;">
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 px-3 sm:px-4 text-[10px] sm:text-xs font-black uppercase tracking-wider">Nama Agenda Kegiatan</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 px-3 sm:px-4 text-[10px] sm:text-xs font-black uppercase tracking-wider text-center">Kategori</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 px-3 sm:px-4 text-[10px] sm:text-xs font-black uppercase tracking-wider whitespace-nowrap">Tanggal & Jam</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 px-3 sm:px-4 text-[10px] sm:text-xs font-black uppercase tracking-wider">Lokasi</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 px-3 sm:px-4 text-[10px] sm:text-xs font-black uppercase tracking-wider text-center leading-tight">Status<br class="hidden sm:inline"> Kehadiran</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 px-3 sm:px-4 text-[10px] sm:text-xs font-black uppercase tracking-wider text-center whitespace-nowrap">Notulensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#d4d1f5]/30">
                    <!-- Client-side Empty State for filters -->
                    <tr x-show="filteredAgendas.length === 0" class="hover:bg-transparent">
                        <td colspan="6" class="py-8 px-4 text-center">
                            <div class="space-y-2">
                                <p class="text-xs text-slate-500 font-medium">Tidak ada riwayat kegiatan yang cocok dengan kriteria filter.</p>
                                <button type="button" @click="searchQuery = ''; filterKategori = ''; filterTanggal = ''; filterStatus = ''; filterNotulensiStatus = '';" 
                                        class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#1b3bbb] text-xs font-bold rounded-xl border border-indigo-200 transition-all inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span>Reset Filter (Tampilkan Semua)</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @forelse($riwayatData as $item)
                        <tr class="agenda-row hover:bg-[#f8f7ff] cursor-pointer transition-colors"
                            onclick="if (!event.target.closest('a')) { window.loadPage('{{ route('agenda.show', $item->id) }}', this) }"
                            x-show="matchesFilter('{{ addslashes($item->judul) }}', '{{ $item->kategori }}', '{{ $item->tanggal->toDateString() }}', '{{ $item->status_kehadiran }}', '{{ $item->notulensi_status }}') && isAgendaVisible({{ $item->id }})">
                            <td class="py-2 sm:py-4 px-2 sm:px-4 font-bold text-[#2e2552]">
                                <a href="{{ route('agenda.show', $item->id) }}" class="hover:text-[#8e88dd] transition-colors leading-snug">
                                    {{ $item->judul }}
                                </a>
                            </td>
                            <td class="py-2 sm:py-4 px-2 sm:px-4 text-center whitespace-nowrap">
                                @php
                                    $badgeStyles = [
                                        'rapat' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'sosialisasi' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'pelatihan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'kegiatan_lainnya' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    ];
                                    $kategoriLabels = [
                                        'rapat' => 'Rapat',
                                        'sosialisasi' => 'Sosialisasi',
                                        'pelatihan' => 'Pelatihan',
                                        'kegiatan_lainnya' => 'Kegiatan Lainnya',
                                    ];
                                @endphp
                                <span class="inline-block text-[8.5px] sm:text-[10px] px-2 py-0.5 font-bold uppercase rounded-md sm:rounded-lg border 
                                    {{ $badgeStyles[$item->kategori] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                    {{ $kategoriLabels[$item->kategori] ?? $item->kategori }}
                                </span>
                            </td>
                            <td class="py-2 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs font-semibold">
                                <div>{{ $item->tanggal->translatedFormat('d M Y') }}</div>
                                <div class="text-[#8e88dd] mt-0.5 font-bold whitespace-nowrap">{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</div>
                            </td>
                            <td class="py-2 sm:py-4 px-2 sm:px-4 text-[10px] sm:text-xs text-[#5a508f] font-medium truncate max-w-[120px] sm:max-w-[150px]" title="{{ $item->lokasi }}">
                                {{ $item->lokasi }}
                            </td>
                            <td class="py-2 sm:py-4 px-2 sm:px-4 text-center text-[10px] sm:text-xs">
                                @if($item->status_kehadiran === 'hadir')
                                    <span class="inline-block px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 font-bold">Hadir ✓</span>
                                @elseif($item->status_kehadiran === 'izin')
                                    <span class="inline-block px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg bg-amber-50 text-amber-600 border border-amber-200 font-bold">Izin</span>
                                @elseif($item->status_kehadiran === 'sakit')
                                    <span class="inline-block px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg bg-rose-50 text-rose-600 border border-rose-200 font-bold">Sakit</span>
                                @elseif($item->status_kehadiran === 'alfa')
                                    <span class="inline-block px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg bg-red-50 text-red-600 border border-red-200 font-extrabold">Alfa</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg bg-slate-100 text-slate-400 border border-slate-200 font-semibold">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center text-xs whitespace-nowrap">
                                @if($item->kategori !== 'rapat')
                                    <span class="text-slate-400 font-medium">-</span>
                                @elseif($item->notulensi_status === 'disahkan')
                                    <div class="flex items-center justify-center gap-1.5 font-bold">
                                        <a href="{{ route('notulensi.export.pdf', $item->id) }}" target="_blank" data-no-pjax title="Unduh Notulensi PDF" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition-all shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <span>PDF</span>
                                        </a>
                                        <a href="{{ route('notulensi.export.docx', $item->id) }}" target="_blank" data-no-pjax title="Unduh Notulensi Word" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition-all shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>Word</span>
                                        </a>
                                    </div>
                                @elseif($item->notulensi_status === 'menunggu_review')
                                    @if(!empty($item->is_approver))
                                        <a href="{{ route('notulensi.review', $item->id) }}" title="Tinjau & Sahkan Notulensi" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Tinjau Notulensi &rarr;</span>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50/80 text-amber-700 border border-amber-200/80 rounded-xl text-xs font-bold shadow-2xs select-none">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Sedang Ditinjau</span>
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100/70 text-slate-400 border border-slate-200/50 text-[9.5px] font-semibold italic">Belum Disahkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="w-12 h-12 bg-[#8e88dd]/10 text-[#5a508f] rounded-2xl flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="text-xs font-bold text-[#2e2552]">Belum Ada Data Riwayat Kegiatan</p>
                                        <p class="text-[11px] text-[#5a508f] font-medium">Riwayat rapat dan presensi yang diikuti akan tercatat secara otomatis di sini.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination controls -->
        <div x-show="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between border-t border-[#d4d1f5]/40 pt-4 mt-4 text-xs font-bold text-[#5a508f] gap-4">
            <!-- Showing x to y of z entries -->
            <div>
                Menampilkan 
                <span x-text="Math.min((currentPage - 1) * itemsPerPage + 1, filteredAgendas.length)"></span>
                sampai
                <span x-text="Math.min(currentPage * itemsPerPage, filteredAgendas.length)"></span>
                dari
                <span x-text="filteredAgendas.length"></span>
                kegiatan
            </div>
            
            <!-- Page buttons -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <!-- Previous Button -->
                <button @click="prevPage()" :disabled="currentPage === 1"
                        class="p-2 rounded-xl border border-[#d4d1f5] hover:bg-[#8e88dd]/10 disabled:opacity-40 disabled:hover:bg-transparent transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <!-- Page numbers -->
                <template x-for="p in displayedPages" :key="p">
                    <button @click="setPage(p)"
                            x-text="p"
                            class="px-3.5 py-2 rounded-xl border transition-all duration-200"
                            :class="currentPage === p ? 'bg-[#2e2552] text-white border-[#2e2552] shadow-sm' : 'border-[#d4d1f5] hover:bg-[#8e88dd]/10'">
                    </button>
                </template>
                
                <!-- Next Button -->
                <button @click="nextPage()" :disabled="currentPage === totalPages"
                        class="p-2 rounded-xl border border-[#d4d1f5] hover:bg-[#8e88dd]/10 disabled:opacity-40 disabled:hover:bg-transparent transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
