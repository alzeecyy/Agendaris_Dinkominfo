@extends('layouts.app')

@section('title', 'Kelola Pegawai')

@php
    $sekretariatId = \App\Models\Bidang::where('singkatan', 'Sekretariat')->orWhere('nama', 'Sekretariat')->value('id');
@endphp

@section('content')
<div x-data="{ 
    openAddModal: false, 
    openEditModal: false, 
    editUser: {},
    searchQuery: '',
    filterBidang: '',
    filterRole: '',
    filterStatus: '',
    currentPage: 1,
    isPageChanging: false,
    itemsPerPage: 10,
    bidangsMap: {
        @foreach($bidangs as $bid)
            '{{ $bid->id }}': '{{ addslashes($bid->singkatan) }}',
        @endforeach
    },
    roleMap: {
        'ketua_master': 'Kepala Dinas (Kadin)',
        'sekretaris_master': 'Sekretaris Dinas (Sekdin)',
        'ketua_bidang': 'Ketua Bidang / Kasubag',
        'sekretaris_bidang': 'Admin Bidang / Admin Subbag',
        'staff': 'Staff'
    },
    getBidangLabel(id) {
        return this.bidangsMap[id] || 'Bidang';
    },
    getRoleLabel(role) {
        return this.roleMap[role] || role;
    },
    users: [
        @foreach($users as $user)
        {
            id: {{ $user->id }},
            name: '{{ addslashes($user->name) }}',
            nip: '{{ $user->nip }}',
            bidang_id: '{{ $user->bidang_id }}',
            is_sekretariat: {{ $user->isSekretariat() ? 'true' : 'false' }},
            role: '{{ $user->role }}',
            active: {{ $user->active ? 'true' : 'false' }}
        },
        @endforeach
    ],
    get filteredUsers() {
        return this.users.filter(u => {
            const matchesSearch = !this.searchQuery || 
                u.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                u.nip.toLowerCase().includes(this.searchQuery.toLowerCase());
                
            let matchesBidang = true;
            if (this.filterBidang) {
                if (this.filterBidang === 'master') {
                    matchesBidang = !u.bidang_id;
                } else if (this.filterBidang == '{{ $sekretariatId }}') {
                    matchesBidang = u.bidang_id == '{{ $sekretariatId }}' || u.is_sekretariat === true;
                } else {
                    matchesBidang = u.bidang_id == this.filterBidang;
                }
            }
            
            let matchesRole = true;
            if (this.filterRole) {
                if (this.filterRole === 'sekretaris') {
                    matchesRole = u.role === 'sekretaris_master' || u.role === 'sekretaris_bidang';
                } else if (this.filterRole === 'ketua') {
                    matchesRole = u.role === 'ketua_master' || u.role === 'ketua_bidang';
                } else {
                    matchesRole = u.role === this.filterRole;
                }
            }
            
            const matchesStatus = !this.filterStatus || 
                (this.filterStatus === 'aktif' && u.active) || 
                (this.filterStatus === 'nonaktif' && !u.active);
                
            return matchesSearch && matchesBidang && matchesRole && matchesStatus;
        });
    },
    get visibleUserIds() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return new Set(this.filteredUsers.slice(start, end).map(u => u.id));
    },
    get totalPages() {
        return Math.ceil(this.filteredUsers.length / this.itemsPerPage) || 1;
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
    isUserVisible(userId) {
        return this.visibleUserIds.has(userId);
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
        this.$watch('filterBidang', () => this.resetPagination());
        this.$watch('filterRole', () => this.resetPagination());
        this.$watch('filterStatus', () => this.resetPagination());
        this.stripeRows();
    },
    stripeRows() {
        this.$nextTick(() => {
            let visibleIndex = 0;
            document.querySelectorAll('.user-row').forEach(row => {
                if (row.style.display !== 'none') {
                    if (visibleIndex % 2 === 0) {
                        row.classList.remove('bg-[#fcfbff]');
                    } else {
                        row.classList.add('bg-[#fcfbff]');
                    }
                    visibleIndex++;
                }
            });
        });
    }
}" 
class="space-y-6">
    
    <!-- Title & Add Trigger -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-base sm:text-xl font-black text-[#2e2552] tracking-wide">Kelola Akun Pegawai</h1>
            <p class="text-[11px] sm:text-xs text-[#5a508f] mt-0.5">Tambah akun, reset password, dan kelola peran/role pegawai</p>
        </div>
        <button @click="openAddModal = true"
                class="w-9 h-9 sm:w-auto sm:h-auto sm:px-4 sm:py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-xl shadow-md shadow-[#2e2552]/10 transition-all inline-flex items-center justify-center gap-1.5 shrink-0"
                title="Tambah Pegawai Baru"
                aria-label="Tambah Pegawai Baru">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            <span class="hidden sm:inline">Tambah Pegawai Baru</span>
        </button>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white border border-[#d4d1f5]/60 rounded-2xl md:rounded-[32px] p-3.5 sm:p-6 shadow-sm overflow-hidden text-[#2e2552]">
        
        <!-- Searchbar & Filter Toolbar -->
        <div class="bg-[#f8f7ff] border border-[#d4d1f5]/60 rounded-2xl p-3 sm:p-4 space-y-2.5 sm:space-y-3 mb-4 sm:mb-6">
            <!-- Row 1: Searchbar + Reset Filter -->
            <div class="flex items-center gap-2.5 w-full">
                <div class="relative flex-1">
                    <input type="text" x-model="searchQuery" placeholder="Cari nama atau NIP pegawai..."
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
                <button type="button" x-show="filterBidang || filterRole || filterStatus || searchQuery" x-cloak
                        @click="filterBidang = ''; filterRole = ''; filterStatus = ''; searchQuery = '';" 
                        class="py-2 sm:py-2.5 px-3.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Reset Filter</span>
                </button>
            </div>

            <!-- Row 2: 3 Equal Width Filter Inputs (Exact 100% Width Match) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 items-center w-full">
                <!-- Bidang Filter -->
                <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                    <button type="button" @click="open = !open" 
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 hover:border-[#1b3bbb] rounded-xl text-xs text-[#09103c] font-semibold flex items-center justify-between transition-all cursor-pointer shadow-2xs truncate focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]/20">
                        <span class="truncate" x-text="filterBidang ? getBidangLabel(filterBidang) : 'Semua Bidang'"></span>
                    </button>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
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
                            <button type="button" @click="filterBidang = ''; open = false" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                    :class="filterBidang === '' ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                <span class="text-left leading-snug">Semua Bidang</span>
                                <svg x-show="filterBidang === ''" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            @foreach($bidangs as $bid)
                                <button type="button" @click="filterBidang = '{{ $bid->id }}'; open = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                        :class="String(filterBidang) === '{{ $bid->id }}' ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                    <span class="text-left leading-snug">{{ $bid->nama }} ({{ $bid->singkatan }})</span>
                                    <svg x-show="String(filterBidang) === '{{ $bid->id }}'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Role Filter -->
                <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                    <button type="button" @click="open = !open" 
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 hover:border-[#1b3bbb] rounded-xl text-xs text-[#09103c] font-semibold flex items-center justify-between transition-all cursor-pointer shadow-2xs truncate focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]/20">
                        <span class="truncate" x-text="filterRole ? getRoleLabel(filterRole) : 'Semua Peran/Role'"></span>
                    </button>
                    <!-- Icon Left -->
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#1b3bbb] pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
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
                                { value: '', label: 'Semua Peran/Role' },
                                { value: 'ketua_master', label: 'Kepala Dinas (Kadin)' },
                                { value: 'sekretaris_master', label: 'Sekretaris Dinas (Sekdin)' },
                                { value: 'ketua_bidang', label: 'Ketua Bidang / Kasubag' },
                                { value: 'sekretaris_bidang', label: 'Admin Bidang / Admin Subbag' },
                                { value: 'staff', label: 'Staff' }
                            ]" :key="opt.value">
                                <button type="button" @click="filterRole = opt.value; open = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                        :class="filterRole === opt.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                    <span class="text-left leading-snug" x-text="opt.label"></span>
                                    <svg x-show="filterRole === opt.value" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                    <button type="button" @click="open = !open" 
                            class="w-full pl-8 pr-8 py-2 bg-white border border-[#d4d1f5]/80 hover:border-[#1b3bbb] rounded-xl text-xs text-[#09103c] font-semibold flex items-center justify-between transition-all cursor-pointer shadow-2xs truncate focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]/20">
                        <span class="truncate" x-text="filterStatus ? (filterStatus === 'aktif' ? 'Aktif' : 'Nonaktif') : 'Semua Status'"></span>
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
                                { value: '', label: 'Semua Status' },
                                { value: 'aktif', label: 'Aktif' },
                                { value: 'nonaktif', label: 'Nonaktif' }
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
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2e2552]">
                <thead style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="bg-[#ebf2ff] text-[#1b3bbb] border-y border-[#bfd5ff] select-none">
                    <tr style="background-color: #ebf2ff !important;">
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important; min-width: 200px;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider whitespace-nowrap">Nama Pegawai</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider text-center whitespace-nowrap">NIP</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider whitespace-nowrap">Bidang</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important; min-width: 150px;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider text-center whitespace-nowrap">Role Sistem</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider text-center whitespace-nowrap">Status</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#d4d1f5]/30 transition-all duration-200 ease-out transform" :class="isPageChanging ? 'opacity-0 -translate-y-1 scale-[0.995]' : 'opacity-100 translate-y-0 scale-100'">
                    <!-- Client-side Empty State for filters -->
                    <tr x-show="filteredUsers.length === 0" class="hover:bg-transparent">
                        <td colspan="6" class="py-8 px-4 text-center">
                            <div class="space-y-2">
                                <p class="text-xs text-slate-500 font-medium">Tidak ada data pegawai yang cocok dengan kriteria filter.</p>
                                <button type="button" @click="searchQuery = ''; filterBidang = ''; filterRole = ''; filterStatus = '';" 
                                        class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#1b3bbb] text-xs font-bold rounded-xl border border-indigo-200 transition-all inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span>Reset Filter (Tampilkan Semua)</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @forelse($users as $user)
                        <tr class="user-row hover:bg-[#f8f7ff] transition-colors"
                            x-show="isUserVisible({{ $user->id }})"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100">
                            <td class="py-4 px-4 font-bold whitespace-nowrap">{{ $user->name }}</td>
                            <td class="py-4 px-4 text-center font-mono text-xs text-[#5a508f]">{{ $user->nip }}</td>
                            <td class="py-4 px-4 text-xs font-semibold text-[#5a508f]">
                                {{ $user->bidang ? $user->bidang->singkatan : 'Dinkominfo' }}
                            </td>
                            <td class="py-4 px-4 text-center text-xs" style="white-space: nowrap;">
                                @php
                                    $roleBadge = [
                                        'sekretaris_master' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'ketua_master' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                        'sekretaris_bidang' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'ketua_bidang' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'staff' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    ];

                                    $displayText = $user->role_label;
                                @endphp
                                <span class="inline-block whitespace-nowrap text-[10px] px-2.5 py-0.5 rounded-full border font-bold {{ $roleBadge[$user->role] ?? 'bg-slate-100 text-slate-500' }}">
                                    {{ $displayText }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($user->active)
                                    <span class="inline-block text-[10px] px-2.5 py-0.5 font-bold uppercase rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100">Aktif</span>
                                @else
                                    <span class="inline-block text-[10px] px-2.5 py-0.5 font-bold uppercase rounded-lg bg-slate-100 text-slate-400 border border-slate-200">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center text-xs align-middle">
                                <div class="flex items-center justify-center gap-3 font-bold">
                                    <!-- Edit Trigger -->
                                    <button @click="openEditModal = true; editUser = { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', nip: '{{ $user->nip }}', jabatan: '{{ addslashes($user->jabatan) }}', bidang_id: '{{ $user->bidang_id }}', role: '{{ $user->role }}' }" 
                                            class="text-[#8e88dd] hover:text-[#2e2552] transition-colors">
                                        Edit
                                    </button>
                                    <span class="text-[#d4d1f5]">|</span>
                                    <!-- Reset password -->
                                    <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" data-title="Reset Kata Sandi Pegawai?" data-confirm="Kata sandi pegawai ini akan dikembalikan ke default: password." data-confirm-btn="Reset Sandi">
                                        @csrf
                                        <button type="submit" class="text-amber-600 hover:text-amber-700 transition-colors">Reset</button>
                                    </form>
                                    <span class="text-[#d4d1f5]">|</span>
                                    <!-- Toggle status -->
                                    <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST"
                                          data-title="{{ $user->active ? 'Nonaktifkan Akun Pegawai?' : 'Aktifkan Akun Pegawai?' }}"
                                          data-confirm="{{ $user->active ? 'Pegawai ini tidak akan bisa masuk ke sistem sampai diaktifkan kembali.' : 'Akun pegawai ini akan diaktifkan kembali agar bisa masuk ke sistem.' }}"
                                          data-confirm-btn="{{ $user->active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                        @csrf
                                        @if($user->active)
                                            <button type="submit" class="text-rose-600 hover:text-rose-500 transition-colors">Nonaktifkan</button>
                                        @else
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-500 transition-colors">Aktifkan</button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                        <td colspan="6" class="py-8 px-4 text-center text-[#8e88dd] italic font-medium">Tidak terdapat data pegawai yang terdaftar.</td>
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
                <span x-text="Math.min((currentPage - 1) * itemsPerPage + 1, filteredUsers.length)"></span>
                sampai
                <span x-text="Math.min(currentPage * itemsPerPage, filteredUsers.length)"></span>
                dari
                <span x-text="filteredUsers.length"></span>
                pegawai
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

    <!-- MODAL: ADD PEGAWAI -->
    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-md overflow-y-auto transition-all duration-300">
        <div @click.away="openAddModal = false" 
             class="bg-white border border-[#d4d1f5] rounded-3xl w-full max-w-md shadow-2xl relative text-[#2e2552] my-auto flex flex-col"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">

            <!-- Modal Header -->
            <div class="px-5 py-3.5 sm:py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-xl border border-white/15 shrink-0">
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Tambah Pegawai Baru</h3>
                        <p class="text-[11px] text-indigo-100 font-medium">Daftarkan akun pengguna baru ke sistem</p>
                    </div>
                </div>
                <button @click="openAddModal = false" type="button" class="p-1.5 bg-white/10 hover:bg-rose-500/80 rounded-xl text-white transition-all cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="p-5 space-y-3.5">
                @csrf
                <div class="space-y-1">
                    <label for="name" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Nama Lengkap <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="name" id="name" required placeholder="Contoh: Dr. Budi Setiawan" 
                           class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm placeholder-[#5a508f]/50 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="space-y-1">
                    <label for="nip" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Nomor Induk Pegawai (NIP) <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="nip" id="nip" required placeholder="Contoh: 199001012015011013" 
                           class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm placeholder-[#5a508f]/50 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="space-y-1">
                    <label for="jabatan" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Jabatan Pegawai <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="jabatan" id="jabatan" required placeholder="Contoh: Pengelola Integrasi Aplikasi" 
                           class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm placeholder-[#5a508f]/50 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ addBidangId: '', addRole: '', openBidang: false, openRole: false }">
                    <div class="space-y-1 relative" @click.outside="openBidang = false">
                        <label for="bidang_id" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Bidang / Subbagian</label>
                        <input type="hidden" name="bidang_id" :value="addBidangId">
                        <button type="button" @click="openBidang = !openBidang" 
                                class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] hover:border-[#1b3bbb] rounded-xl text-[#09103c] text-xs font-semibold flex items-center justify-between transition-all cursor-pointer focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]">
                            <span class="truncate" x-text="addBidangId ? getBidangLabel(addBidangId) : 'Pilih Bidang / Subbag'"></span>
                            <svg class="w-4 h-4 text-[#1b3bbb] transition-transform duration-200" :class="openBidang ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openBidang" x-cloak 
                             x-transition:enter="transition ease-out duration-150 transform" 
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-100 transform" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1" 
                             class="absolute left-0 bottom-full mb-1 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                            <div class="space-y-0.5">
                                <button type="button" @click="addBidangId = ''; openBidang = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                        :class="addBidangId === '' ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                    <span class="text-left leading-snug">Pilih Bidang / Subbag</span>
                                </button>
                                @foreach($bidangs as $bid)
                                    <button type="button" @click="addBidangId = '{{ $bid->id }}'; openBidang = false" 
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                            :class="String(addBidangId) === '{{ $bid->id }}' ? 'bg-[#1b3bbb] text-[#1b3bbb] font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                        <span class="text-left leading-snug">{{ $bid->nama }} ({{ $bid->singkatan }})</span>
                                        <svg x-show="String(addBidangId) === '{{ $bid->id }}'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1 relative" @click.outside="openRole = false">
                        <label for="role" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Role Sistem <span class="text-rose-500 font-bold">*</span></label>
                        <input type="hidden" name="role" :value="addRole" required>
                        <button type="button" @click="openRole = !openRole" 
                                class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] hover:border-[#1b3bbb] rounded-xl text-[#09103c] text-xs font-semibold flex items-center justify-between transition-all cursor-pointer focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]">
                            <span class="truncate" x-text="addRole ? getRoleLabel(addRole) : 'Pilih Role'"></span>
                            <svg class="w-4 h-4 text-[#1b3bbb] transition-transform duration-200" :class="openRole ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openRole" x-cloak 
                             x-transition:enter="transition ease-out duration-150 transform" 
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-100 transform" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1" 
                             class="absolute left-0 bottom-full mb-1 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                            <div class="space-y-0.5">
                                <template x-for="opt in [
                                    { value: 'staff', label: 'Staff' },
                                    { value: 'sekretaris_bidang', label: 'Admin Bidang / Subbag' },
                                    { value: 'ketua_bidang', label: 'Kepala Bidang / Kasubag' },
                                    { value: 'sekretaris_master', label: 'Sekretaris Dinas (Sekdin)' },
                                    { value: 'ketua_master', label: 'Kepala Dinas (Kadin)' }
                                ]" :key="opt.value">
                                    <button type="button" @click="addRole = opt.value; openRole = false" 
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                            :class="addRole === opt.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                        <span class="text-left leading-snug" x-text="opt.label"></span>
                                        <svg x-show="addRole === opt.value" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-[#d4d1f5]/40 pt-4">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-xl transition-all cursor-pointer">Batalkan</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#1b3bbb] hover:bg-[#152e96] text-white text-xs font-bold rounded-xl shadow-md shadow-[#1b3bbb]/20 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                        <span>Simpan Akun</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT PEGAWAI -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-md overflow-y-auto transition-all duration-300">
        <div @click.away="openEditModal = false" 
             class="bg-white border border-[#d4d1f5] rounded-3xl w-full max-w-md shadow-2xl relative text-[#2e2552] my-auto flex flex-col"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">

            <!-- Modal Header -->
            <div class="px-5 py-3.5 sm:py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-xl border border-white/15 shrink-0">
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Edit Data Pegawai</h3>
                        <p class="text-[11px] text-indigo-100 font-medium">Perbarui informasi profil atau wewenang akun</p>
                    </div>
                </div>
                <button @click="openEditModal = false" type="button" class="p-1.5 bg-white/10 hover:bg-rose-500/80 rounded-xl text-white transition-all cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form :action="'/admin/users/' + editUser.id" method="POST" class="p-5 space-y-3.5">
                @csrf
                @method('PUT')
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Nama Lengkap <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="name" required x-model="editUser.name" class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Nomor Induk Pegawai (NIP) <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="nip" required x-model="editUser.nip" class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Jabatan Pegawai <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="jabatan" required x-model="editUser.jabatan" class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ openEditBidang: false, openEditRole: false }">
                    <div class="space-y-1 relative" @click.outside="openEditBidang = false">
                        <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Bidang / Subbagian</label>
                        <input type="hidden" name="bidang_id" :value="editUser.bidang_id">
                        <button type="button" @click="openEditBidang = !openEditBidang" 
                                class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] hover:border-[#1b3bbb] rounded-xl text-[#09103c] text-xs font-semibold flex items-center justify-between transition-all cursor-pointer focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]">
                            <span class="truncate" x-text="editUser.bidang_id ? getBidangLabel(editUser.bidang_id) : 'Pilih Bidang / Subbag'"></span>
                            <svg class="w-4 h-4 text-[#1b3bbb] transition-transform duration-200" :class="openEditBidang ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openEditBidang" x-cloak 
                             x-transition:enter="transition ease-out duration-150 transform" 
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-100 transform" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1" 
                             class="absolute left-0 bottom-full mb-1 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                            <div class="space-y-0.5">
                                <button type="button" @click="editUser.bidang_id = ''; openEditBidang = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                        :class="!editUser.bidang_id ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                    <span class="text-left leading-snug">Pilih Bidang / Subbag</span>
                                </button>
                                @foreach($bidangs as $bid)
                                    <button type="button" @click="editUser.bidang_id = '{{ $bid->id }}'; openEditBidang = false" 
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                            :class="String(editUser.bidang_id) === '{{ $bid->id }}' ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                        <span class="text-left leading-snug">{{ $bid->nama }} ({{ $bid->singkatan }})</span>
                                        <svg x-show="String(editUser.bidang_id) === '{{ $bid->id }}'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1 relative" @click.outside="openEditRole = false">
                        <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Role Sistem <span class="text-rose-500 font-bold">*</span></label>
                        <input type="hidden" name="role" :value="editUser.role" required>
                        <button type="button" @click="openEditRole = !openEditRole" 
                                class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] hover:border-[#1b3bbb] rounded-xl text-[#09103c] text-xs font-semibold flex items-center justify-between transition-all cursor-pointer focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb]">
                            <span class="truncate" x-text="editUser.role ? getRoleLabel(editUser.role) : 'Pilih Role'"></span>
                            <svg class="w-4 h-4 text-[#1b3bbb] transition-transform duration-200" :class="openEditRole ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openEditRole" x-cloak 
                             x-transition:enter="transition ease-out duration-150 transform" 
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-100 transform" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1" 
                             class="absolute left-0 bottom-full mb-1 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                            <div class="space-y-0.5">
                                <template x-for="opt in [
                                    { value: 'staff', label: 'Staff' },
                                    { value: 'sekretaris_bidang', label: 'Admin Bidang / Subbag' },
                                    { value: 'ketua_bidang', label: 'Kepala Bidang / Kasubag' },
                                    { value: 'sekretaris_master', label: 'Sekretaris Dinas (Sekdin)' },
                                    { value: 'ketua_master', label: 'Kepala Dinas (Kadin)' }
                                ]" :key="opt.value">
                                    <button type="button" @click="editUser.role = opt.value; openEditRole = false" 
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                            :class="editUser.role === opt.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                        <span class="text-left leading-snug" x-text="opt.label"></span>
                                        <svg x-show="editUser.role === opt.value" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-[#d4d1f5]/40 pt-4">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-xl transition-all cursor-pointer">Batalkan</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#1b3bbb] hover:bg-[#152e96] text-white text-xs font-bold rounded-xl shadow-md shadow-[#1b3bbb]/20 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                        <span>Simpan Perubahan</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
