@extends('layouts.app')

@section('title', 'Riwayat Kegiatan')

@section('content')
<div x-data="{ 
    searchQuery: '{{ addslashes($searchQuery ?? "") }}',
    filterKategori: '',
    filterTanggal: '',
    filterStatus: '',
    filterNotulensiStatus: '{{ $selectedNotulensiStatus ?? "" }}',
    currentPage: 1,
    isPageChanging: false,
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
    checkSearch(title, location, query) {
        if (!query) return true;
        const q = query.toLowerCase().trim();
        const t = (title || '').toLowerCase();
        const l = (location || '').toLowerCase();
        return t.includes(q) || l.includes(q);
    },
    matchesFilter(judul, lokasi, kategori, tanggalStr, statusKehadiran, notulensiStatus) {
        const matchesSearch = this.checkSearch(judul, lokasi, this.searchQuery);
        const matchesKategori = !this.filterKategori || kategori === this.filterKategori;
        const matchesTanggal = !this.filterTanggal || tanggalStr === this.filterTanggal;
        const matchesStatus = !this.filterStatus || statusKehadiran === this.filterStatus;
        const matchesNotulensi = !this.filterNotulensiStatus || notulensiStatus === this.filterNotulensiStatus;
        
        return matchesSearch && matchesKategori && matchesTanggal && matchesStatus && matchesNotulensi;
    },
    get filteredAgendas() {
        return this.agendas.filter(a => {
            const matchesSearch = this.checkSearch(a.judul, a.lokasi, this.searchQuery);
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
        
        let start = Math.max(1, Math.min(current - (maxVisible - 1), total - maxVisible + 1));
        let end = start + maxVisible - 1;
        
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
        if (this.currentPage < this.totalPages && !this.isPageChanging) {
            this.setPage(this.currentPage + 1);
        }
    },
    prevPage() {
        if (this.currentPage > 1 && !this.isPageChanging) {
            this.setPage(this.currentPage - 1);
        }
    },
    setPage(page) {
        const targetPage = Math.max(1, Math.min(page, this.totalPages));
        if (targetPage === this.currentPage || this.isPageChanging) return;
        
        this.isPageChanging = true;
        setTimeout(() => {
            this.currentPage = targetPage;
            this.stripeRows();
            setTimeout(() => {
                this.isPageChanging = false;
            }, 40);
        }, 120);
    },
    resetPagination() {
        this.currentPage = 1;
        this.stripeRows();
    },
    init() {
        this.$watch('searchQuery', () => this.resetPagination());
        this.$watch('filterKategori', () => this.resetPagination());
        this.$watch('filterTanggal', () => this.resetPagination());
        this.$watch('filterStatus', () => this.resetPagination());
        this.$watch('filterNotulensiStatus', () => this.resetPagination());
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
}" 
class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-[#09103c] tracking-wide">Riwayat Kegiatan & Rapat</h1>
        <p class="text-xs sm:text-sm font-medium text-[#5a508f] mt-1">Arsip seluruh kegiatan dan status kehadiran Anda di Dinkominfo</p>
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
                <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                    <button type="button" @click="open = !open" 
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 hover:border-[#1b3bbb] rounded-xl text-xs text-[#09103c] font-semibold flex items-center justify-between transition-all cursor-pointer shadow-2xs truncate focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]/20">
                        <span class="truncate" x-text="filterKategori ? (filterKategori === 'rapat' ? 'Rapat' : (filterKategori === 'sosialisasi' ? 'Sosialisasi' : (filterKategori === 'pelatihan' ? 'Pelatihan' : 'Kegiatan Lainnya'))) : 'Semua Kategori'"></span>
                    </button>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10"></path>
                        </svg>
                    </div>
                    <!-- Custom Arrow Right -->
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                         class="absolute left-0 top-full mt-1.5 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                        <div class="space-y-0.5">
                            <template x-for="opt in [
                                { value: '', label: 'Semua Kategori' },
                                { value: 'rapat', label: 'Rapat' },
                                { value: 'sosialisasi', label: 'Sosialisasi' },
                                { value: 'pelatihan', label: 'Pelatihan' },
                                { value: 'kegiatan_lainnya', label: 'Kegiatan Lainnya' }
                            ]" :key="opt.value">
                                <button type="button" @click="filterKategori = opt.value; open = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                        :class="filterKategori === opt.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                    <span class="text-left leading-snug" x-text="opt.label"></span>
                                    <svg x-show="filterKategori === opt.value" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </template>
                        </div>
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
                <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                    <button type="button" @click="open = !open" 
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 hover:border-[#1b3bbb] rounded-xl text-xs text-[#09103c] font-semibold flex items-center justify-between transition-all cursor-pointer shadow-2xs truncate focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]/20">
                        <span class="truncate" x-text="filterStatus ? (filterStatus === 'hadir' ? 'Hadir' : (filterStatus === 'izin' ? 'Izin' : (filterStatus === 'sakit' ? 'Sakit' : 'Alfa'))) : 'Semua Kehadiran'"></span>
                    </button>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <!-- Custom Arrow Right -->
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                         class="absolute left-0 top-full mt-1.5 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                        <div class="space-y-0.5">
                            <template x-for="opt in [
                                { value: '', label: 'Semua Kehadiran' },
                                { value: 'hadir', label: 'Hadir' },
                                { value: 'izin', label: 'Izin' },
                                { value: 'sakit', label: 'Sakit' },
                                { value: 'alfa', label: 'Alfa' }
                            ]" :key="opt.value">
                                <button type="button" @click="filterStatus = opt.value; open = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                        :class="filterStatus === opt.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                    <span class="text-left leading-snug" x-text="opt.label"></span>
                                    <svg x-show="filterStatus === opt.value" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Status Notulensi Filter -->
                <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                    <button type="button" @click="open = !open" 
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 hover:border-[#1b3bbb] rounded-xl text-xs text-[#09103c] font-semibold flex items-center justify-between transition-all cursor-pointer shadow-2xs truncate focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]/20">
                        <span class="truncate" x-text="filterNotulensiStatus ? (filterNotulensiStatus === 'draft' ? 'Belum Ada Draft' : (filterNotulensiStatus === 'menunggu_review' ? 'Menunggu Review' : (filterNotulensiStatus === 'revisi' ? 'Perlu Revisi' : 'Telah Disahkan'))) : 'Semua Status Notulen'"></span>
                    </button>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <!-- Custom Arrow Right -->
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                         class="absolute left-0 top-full mt-1.5 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                        <div class="space-y-0.5">
                            <template x-for="opt in [
                                { value: '', label: 'Semua Status Notulen' },
                                { value: 'draft', label: 'Belum Ada Draft' },
                                { value: 'menunggu_review', label: 'Menunggu Review' },
                                { value: 'revisi', label: 'Perlu Revisi' },
                                { value: 'disahkan', label: 'Telah Disahkan' }
                            ]" :key="opt.value">
                                <button type="button" @click="filterNotulensiStatus = opt.value; open = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                        :class="filterNotulensiStatus === opt.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                    <span class="text-left leading-snug" x-text="opt.label"></span>
                                    <svg x-show="filterNotulensiStatus === opt.value" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </template>
                        </div>
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
                <tbody class="divide-y divide-[#d4d1f5]/30 transition-all duration-200 ease-out transform" :class="isPageChanging ? 'opacity-0 -translate-y-1 scale-[0.995]' : 'opacity-100 translate-y-0 scale-100'">
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
                            x-show="matchesFilter('{{ addslashes($item->judul) }}', '{{ addslashes($item->lokasi) }}', '{{ $item->kategori }}', '{{ $item->tanggal->toDateString() }}', '{{ $item->status_kehadiran }}', '{{ $item->notulensi_status }}') && isAgendaVisible({{ $item->id }})">
                            <td class="py-2 sm:py-4 px-2 sm:px-4 font-bold text-[#2e2552]">
                                <a href="{{ route('agenda.show', $item->id) }}" class="hover:text-[#8e88dd] transition-colors leading-snug">
                                    {{ $item->judul }}
                                </a>
                                @php
                                    $itemHak = (array)($item->hak_akses ?? []);
                                @endphp
                                @if(!empty($itemHak))
                                    <div class="flex items-center gap-1 flex-wrap mt-1">
                                        @if(in_array('semua_orang', $itemHak))
                                            <span class="inline-block text-[8.5px] px-1.5 py-0.5 font-bold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Semua Bidang & Subbag
                                            </span>
                                        @else
                                            @php
                                                 $numericItemHak = array_values(array_filter($itemHak, 'is_numeric'));
                                                 $bRecords = \App\Models\Bidang::whereIn('id', $numericItemHak)->get();
                                                 $bNames = [];
                                                 if (in_array('kadin', $itemHak)) {
                                                     $bNames[] = 'Kadin';
                                                 }
                                                 foreach ($bRecords as $bRec) {
                                                     $bNames[] = $bRec->singkatan ?? $bRec->nama;
                                                 }
                                                 $maxRShow = 2;
                                                 $totalRCount = count($bNames);
                                                 $visibleRNames = array_slice($bNames, 0, $maxRShow);
                                                 $remRCount = $totalRCount - $maxRShow;
                                                 $remRNames = $remRCount > 0 ? implode(', ', array_slice($bNames, $maxRShow)) : '';
                                             @endphp
                                            @foreach($visibleRNames as $bNm)
                                                <span class="inline-block text-[8.5px] px-1.5 py-0.5 font-bold rounded-md bg-indigo-50 text-[#1b3bbb] border border-indigo-200">
                                                    {{ $bNm }}
                                                </span>
                                            @endforeach
                                            @if($remRCount > 0)
                                                <span class="inline-block text-[8.5px] px-1.5 py-0.5 font-extrabold rounded-md bg-indigo-50 text-[#1b3bbb] border border-indigo-200 cursor-help" title="{{ $remRNames }}">
                                                    +{{ $remRCount }} lainnya
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                @endif
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
                                    <span class="inline-block px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 font-bold">Hadir</span>
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
                                    @if(!empty($item->is_approver) || !empty($item->is_secretary))
                                        <a href="{{ route('notulensi.review', $item->id) }}" title="Tinjau Notulensi" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Tinjau Notulensi</span>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50/80 text-amber-700 border border-amber-200/80 rounded-xl text-xs font-bold shadow-2xs select-none">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Sedang Ditinjau</span>
                                        </span>
                                    @endif
                                @elseif($item->notulensi_status === 'revisi')
                                    @if(!empty($item->is_secretary))
                                        <a href="{{ route('notulensi.edit', $item->id) }}" title="Perbaiki Notulensi" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            <span>Perbaiki Notulensi &rarr;</span>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold shadow-2xs select-none">
                                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span>Perlu Revisi</span>
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
                                        <p class="text-xs font-bold text-[#09103c]">Belum Ada Data Riwayat Kegiatan</p>
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
                <!-- First Page Button -->
                <button @click="setPage(1)" :disabled="currentPage === 1"
                        class="w-9 h-9 flex items-center justify-center rounded-xl border border-[#d4d1f5] hover:bg-[#1b3bbb]/10 hover:border-[#1b3bbb] hover:text-[#1b3bbb] hover:scale-110 active:scale-90 disabled:opacity-30 disabled:hover:scale-100 disabled:hover:bg-transparent disabled:hover:border-[#d4d1f5] disabled:hover:text-inherit transition-all duration-200 cursor-pointer shadow-2xs"
                        title="Halaman Pertama">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M20 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <!-- Previous Button -->
                <button @click="prevPage()" :disabled="currentPage === 1"
                        class="w-9 h-9 flex items-center justify-center rounded-xl border border-[#d4d1f5] hover:bg-[#1b3bbb]/10 hover:border-[#1b3bbb] hover:text-[#1b3bbb] hover:scale-110 active:scale-90 disabled:opacity-30 disabled:hover:scale-100 disabled:hover:bg-transparent disabled:hover:border-[#d4d1f5] disabled:hover:text-inherit transition-all duration-200 cursor-pointer shadow-2xs"
                        title="Halaman Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <!-- Page numbers -->
                <template x-for="p in displayedPages" :key="p">
                    <button @click="setPage(p)"
                            x-text="p"
                            class="w-9 h-9 flex items-center justify-center rounded-xl border font-extrabold text-xs transition-all duration-300 cursor-pointer transform"
                            :class="currentPage === p ? 'bg-[#1b3bbb] text-white border-[#1b3bbb] shadow-md shadow-[#1b3bbb]/30 scale-110' : 'border-[#d4d1f5] hover:border-[#1b3bbb] hover:bg-[#1b3bbb]/10 text-[#5a508f] hover:text-[#1b3bbb] hover:scale-105 active:scale-95'">
                    </button>
                </template>
                
                <!-- Next Button -->
                <button @click="nextPage()" :disabled="currentPage === totalPages"
                        class="w-9 h-9 flex items-center justify-center rounded-xl border border-[#d4d1f5] hover:bg-[#1b3bbb]/10 hover:border-[#1b3bbb] hover:text-[#1b3bbb] hover:scale-110 active:scale-90 disabled:opacity-30 disabled:hover:scale-100 disabled:hover:bg-transparent disabled:hover:border-[#d4d1f5] disabled:hover:text-inherit transition-all duration-200 cursor-pointer shadow-2xs"
                        title="Halaman Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Last Page Button -->
                <button @click="setPage(totalPages)" :disabled="currentPage === totalPages"
                        class="w-9 h-9 flex items-center justify-center rounded-xl border border-[#d4d1f5] hover:bg-[#1b3bbb]/10 hover:border-[#1b3bbb] hover:text-[#1b3bbb] hover:scale-110 active:scale-90 disabled:opacity-30 disabled:hover:scale-100 disabled:hover:bg-transparent disabled:hover:border-[#d4d1f5] disabled:hover:text-inherit transition-all duration-200 cursor-pointer shadow-2xs"
                        title="Halaman Terakhir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M4 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
