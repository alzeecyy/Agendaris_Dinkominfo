@extends('layouts.app')

@section('title', 'Kelola Pegawai')

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
    itemsPerPage: 10,
    users: [
        @foreach($users as $user)
        {
            id: {{ $user->id }},
            name: '{{ addslashes($user->name) }}',
            nip: '{{ $user->nip }}',
            bidang_id: '{{ $user->bidang_id }}',
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
    isUserVisible(userId) {
        return this.visibleUserIds.has(userId);
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
x-init="
    $watch('searchQuery', () => resetPagination());
    $watch('filterBidang', () => resetPagination());
    $watch('filterRole', () => resetPagination());
    $watch('filterStatus', () => resetPagination());
    stripeRows();
"
class="space-y-6">
    
    <!-- Title & Add Trigger -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-[#2e2552] tracking-wide">Kelola Akun Pegawai</h1>
            <p class="text-xs text-[#5a508f] mt-0.5">Tambah akun, reset password, dan kelola peran/role pegawai</p>
        </div>
        <button @click="openAddModal = true"
                class="px-4 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-xl shadow-md shadow-[#2e2552]/10 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            <span>Tambah Pegawai Baru</span>
        </button>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm overflow-hidden text-[#2e2552]">
        
        <!-- Filter Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Search Input -->
            <div class="relative">
                <input type="text" x-model="searchQuery" placeholder="Cari nama atau NIP..."
                       class="w-full pl-10 pr-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-xs text-[#2e2552] focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#5a508f]/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Bidang Filter -->
            <div>
                <select x-model="filterBidang" 
                        class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-xs text-[#2e2552] focus:outline-none">
                    <option value="">Semua Bidang</option>
                    <option value="master">Dinkominfo (Master)</option>
                    @foreach($bidangs as $bid)
                        <option value="{{ $bid->id }}">{{ $bid->singkatan }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Role Filter -->
            <div>
                <select x-model="filterRole" 
                        class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-xs text-[#2e2552] focus:outline-none">
                    <option value="">Semua Role</option>
                    <option value="sekretaris_master">Sekretaris Dinas</option>
                    <option value="sekretaris_bidang">Admin Bidang</option>
                    <option value="ketua_master">Kepala Dinas</option>
                    <option value="ketua_bidang">Ketua Bidang</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            
            <!-- Status Filter -->
            <div>
                <select x-model="filterStatus" 
                        class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-xs text-[#2e2552] focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2e2552]">
                <thead class="text-xs font-bold uppercase tracking-wider text-[#5a508f] border-b border-[#d4d1f5]/40">
                    <tr>
                        <th class="py-4 px-4 whitespace-nowrap" style="min-width: 200px;">Nama Pegawai</th>
                        <th class="py-4 px-4 text-center whitespace-nowrap">NIP</th>
                        <th class="py-4 px-4 whitespace-nowrap">Bidang</th>
                        <th class="py-4 px-4 text-center whitespace-nowrap" style="min-width: 150px; white-space: nowrap;">Role Sistem</th>
                        <th class="py-4 px-4 text-center whitespace-nowrap">Status</th>
                        <th class="py-4 px-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#d4d1f5]/30">
                    <!-- Client-side Empty State for filters -->
                    <tr x-show="filteredUsers.length === 0" class="hover:bg-transparent">
                        <td colspan="6" class="py-8 px-4 text-center text-[#8e88dd] italic font-medium">Tidak ada data pegawai yang cocok dengan kriteria filter.</td>
                    </tr>
                    @forelse($users as $user)
                        <tr class="user-row hover:bg-[#f8f7ff] transition-colors"
                            x-show="isUserVisible({{ $user->id }})"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100">
                            <td class="py-4 px-4 font-bold whitespace-nowrap">{{ $user->name }}</td>
                            <td class="py-4 px-4 text-center font-mono text-xs text-[#5a508f]">{{ $user->nip }}</td>
                            <td class="py-4 px-4 text-xs font-semibold text-[#5a508f]">{{ $user->bidang->singkatan ?? 'Dinkominfo (Master)' }}</td>
                            <td class="py-4 px-4 text-center text-xs" style="white-space: nowrap;">
                                @php
                                    $roleBadge = [
                                        'sekretaris_master' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'ketua_master' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                        'sekretaris_bidang' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'ketua_bidang' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'staff' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    ];
                                    $bidName = $user->bidang ? ($user->bidang->singkatan ?? $user->bidang->nama) : '';
                                    $roleLabel = [
                                        'sekretaris_master' => 'Sekretaris Dinas',
                                        'ketua_master' => 'Kepala Dinas',
                                        'sekretaris_bidang' => $bidName ? "Admin Bidang {$bidName}" : 'Admin Bidang',
                                        'ketua_bidang' => $bidName ? "Ketua Bidang {$bidName}" : 'Ketua Bidang',
                                        'staff' => 'Staff',
                                    ];
                                @endphp
                                <span class="inline-block whitespace-nowrap text-[10px] px-2.5 py-0.5 rounded-full border font-bold {{ $roleBadge[$user->role] ?? 'bg-slate-100 text-slate-500' }}">
                                    {{ $roleLabel[$user->role] ?? $user->role }}
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
                                    <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin me-reset password pegawai ini ke default: password?">
                                        @csrf
                                        <button type="submit" class="text-amber-600 hover:text-amber-700 transition-colors">Reset</button>
                                    </form>
                                    <span class="text-[#d4d1f5]">|</span>
                                    <!-- Toggle status -->
                                    <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST"
                                          data-confirm="{{ $user->active ? 'Apakah Anda yakin ingin menonaktifkan akun pegawai ini? Pegawai tersebut tidak akan bisa masuk ke sistem.' : 'Apakah Anda yakin ingin mengaktifkan kembali akun pegawai ini?' }}">
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
                        class="p-2 rounded-xl border border-[#d4d1f5] hover:bg-[#8e88dd]/10 disabled:opacity-40 disabled:hover:bg-transparent transition-colors"
                        title="Halaman Pertama">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M20 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <!-- Previous Button -->
                <button @click="prevPage()" :disabled="currentPage === 1"
                        class="p-2 rounded-xl border border-[#d4d1f5] hover:bg-[#8e88dd]/10 disabled:opacity-40 disabled:hover:bg-transparent transition-colors"
                        title="Halaman Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <!-- Page numbers -->
                <template x-for="p in totalPages" :key="p">
                    <button @click="setPage(p)"
                            x-text="p"
                            class="px-3.5 py-2 rounded-xl border transition-all duration-200"
                            :class="currentPage === p ? 'bg-[#2e2552] text-white border-[#2e2552] shadow-sm' : 'border-[#d4d1f5] hover:bg-[#8e88dd]/10'">
                    </button>
                </template>
                
                <!-- Next Button -->
                <button @click="nextPage()" :disabled="currentPage === totalPages"
                        class="p-2 rounded-xl border border-[#d4d1f5] hover:bg-[#8e88dd]/10 disabled:opacity-40 disabled:hover:bg-transparent transition-colors"
                        title="Halaman Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Last Page Button -->
                <button @click="setPage(totalPages)" :disabled="currentPage === totalPages"
                        class="p-2 rounded-xl border border-[#d4d1f5] hover:bg-[#8e88dd]/10 disabled:opacity-40 disabled:hover:bg-transparent transition-colors"
                        title="Halaman Terakhir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M4 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD PEGAWAI -->
    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="openAddModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden relative text-[#2e2552]">
            <div class="absolute top-0 left-0 w-full h-[2px] bg-[#2e2552]"></div>
            <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                <h3 class="text-base font-bold text-[#2e2552]">Tambah Pegawai Baru</h3>
                <button @click="openAddModal = false" class="text-[#5a508f] hover:text-[#2e2552]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="space-y-1">
                    <label for="name" class="block text-xs font-bold text-[#5a508f] uppercase">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" required placeholder="Contoh: Dr. Budi Setiawan" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>
                <div class="space-y-1">
                    <label for="nip" class="block text-xs font-bold text-[#5a508f] uppercase">Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" id="nip" required placeholder="Contoh: 199001012015011013" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>
                <div class="space-y-1">
                    <label for="jabatan" class="block text-xs font-bold text-[#5a508f] uppercase">Jabatan Pegawai <span class="text-rose-500">*</span></label>
                    <input type="text" name="jabatan" id="jabatan" required placeholder="Contoh: Pengelola Integrasi Aplikasi" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="bidang_id" class="block text-xs font-bold text-[#5a508f] uppercase">Bidang Dinas</label>
                        <select name="bidang_id" id="bidang_id" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                            <option value="" disabled selected>-- Pilih Bidang --</option>
                            @foreach($bidangs as $bid)
                                <option value="{{ $bid->id }}">{{ $bid->singkatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label for="role" class="block text-xs font-bold text-[#5a508f] uppercase">Role Sistem <span class="text-rose-500">*</span></label>
                        <select name="role" id="role" required class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                            <option value="" disabled selected>-- Pilih Role --</option>
                            <option value="staff">Staff</option>
                            <option value="sekretaris_bidang">Admin Bidang</option>
                            <option value="ketua_bidang">Ketua Bidang</option>
                            <option value="sekretaris_master">Sekretaris Dinas</option>
                            <option value="ketua_master">Kepala Dinas</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-[#d4d1f5]/40 pt-4">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-2xl">Batalkan</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT PEGAWAI -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="openEditModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden relative text-[#2e2552]">
            <div class="absolute top-0 left-0 w-full h-[2px] bg-[#2e2552]"></div>
            <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                <h3 class="text-base font-bold text-[#2e2552]">Edit Data Pegawai</h3>
                <button @click="openEditModal = false" class="text-[#5a508f] hover:text-[#2e2552]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form :action="'/admin/users/' + editUser.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required x-model="editUser.name" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" required x-model="editUser.nip" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Jabatan Pegawai <span class="text-rose-500">*</span></label>
                    <input type="text" name="jabatan" required x-model="editUser.jabatan" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-[#5a508f] uppercase">Bidang Dinas</label>
                        <select name="bidang_id" x-model="editUser.bidang_id" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm">
                            <option value="" disabled>-- Pilih Bidang --</option>
                            @foreach($bidangs as $bid)
                                <option value="{{ $bid->id }}">{{ $bid->singkatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-[#5a508f] uppercase">Role Sistem <span class="text-rose-500">*</span></label>
                        <select name="role" x-model="editUser.role" required class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm">
                            <option value="" disabled>-- Pilih Role --</option>
                            <option value="staff">Staff</option>
                            <option value="sekretaris_bidang">Admin Bidang</option>
                            <option value="ketua_bidang">Ketua Bidang</option>
                            <option value="sekretaris_master">Sekretaris Dinas</option>
                            <option value="ketua_master">Kepala Dinas</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-[#d4d1f5]/40 pt-4">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-2xl">Batalkan</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
