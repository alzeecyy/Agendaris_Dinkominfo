@extends('layouts.app')

@section('title', 'Kelola Bidang')

@section('content')
<div x-data="{ openAddModal: false, openEditModal: false, editBidang: {} }" class="space-y-6">
    
    <!-- Title & Add Trigger -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-base sm:text-xl font-black text-[#2e2552] tracking-wide">Kelola Master Bidang</h1>
            <p class="text-[11px] sm:text-xs text-[#5a508f] mt-0.5">Tambahkan atau perbarui data bidang di lingkungan Dinkominfo</p>
        </div>
        <button @click="openAddModal = true"
                class="w-9 h-9 sm:w-auto sm:h-auto sm:px-4 sm:py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-xl shadow-md shadow-[#2e2552]/10 transition-all inline-flex items-center justify-center gap-1.5 shrink-0"
                title="Tambah Bidang Baru"
                aria-label="Tambah Bidang Baru">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="hidden sm:inline">Tambah Bidang Baru</span>
        </button>
    </div>

    <!-- Bidang Table Card -->
    <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm overflow-hidden text-[#2e2552]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2e2552]">
                <thead style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="bg-[#ebf2ff] text-[#1b3bbb] border-y border-[#bfd5ff] select-none">
                    <tr style="background-color: #ebf2ff !important;">
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider">Nama Bidang / Subbagian</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider text-left">Singkatan</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider text-center">Jumlah Pegawai</th>
                        <th style="background-color: #ebf2ff !important; color: #1b3bbb !important;" class="py-3 sm:py-3.5 px-4 text-xs font-black uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#d4d1f5]/30">
                    @forelse($bidangs as $bid)
                        @php
                            $isChildSubbag = (str_contains(strtolower($bid->nama), 'subbag') || str_contains(strtolower($bid->singkatan), 'subbag')) && strcasecmp($bid->singkatan, 'Sekretariat') !== 0;
                        @endphp
                        <tr class="hover:bg-[#f8f7ff] transition-colors {{ $isChildSubbag ? 'bg-slate-50/40' : '' }}">
                            <td class="py-4 px-4 font-bold text-[#2e2552] {{ $isChildSubbag ? 'pl-10' : '' }}">
                                @if($isChildSubbag)
                                    <span class="text-[#b0aad8] font-bold mr-1 text-sm">└</span>
                                @endif
                                <span>{{ $bid->nama }}</span>
                            </td>

                            <td class="py-4 px-4 text-left font-black text-[#8e88dd] {{ $isChildSubbag ? 'pl-7' : '' }}">
                                {{ $bid->singkatan }}
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-slate-700">{{ $bid->users_count }}</td>
                            <td class="py-4 px-4 text-center text-xs">
                                <!-- Edit Trigger -->
                                <button @click="openEditModal = true; editBidang = { id: {{ $bid->id }}, nama: '{{ addslashes($bid->nama) }}', singkatan: '{{ addslashes($bid->singkatan) }}' }" 
                                        class="text-[#8e88dd] hover:text-[#2e2552] font-bold transition-colors">
                                    Edit Bidang
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 px-4 text-center text-[#8e88dd] italic font-medium">Tidak terdapat data bidang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: ADD BIDANG -->
    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-md overflow-y-auto transition-all duration-300">
        <div @click.away="openAddModal = false" 
             class="bg-white border border-[#d4d1f5] rounded-3xl w-full max-w-md shadow-2xl overflow-hidden relative text-[#2e2552] my-auto flex flex-col"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Tambah Bidang Baru</h3>
                        <p class="text-[11px] text-indigo-100 font-medium">Buat master bidang atau subbagian baru</p>
                    </div>
                </div>
                <button @click="openAddModal = false" type="button" class="p-1.5 bg-white/10 hover:bg-rose-500/80 rounded-xl text-white transition-all cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form Content -->
            <form action="{{ route('admin.bidang.store') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="space-y-1">
                    <label for="nama" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Nama Bidang Lengkap <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="nama" id="nama" required placeholder="Contoh: Bidang Aplikasi Informatika" class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm placeholder-[#5a508f]/50 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="space-y-1">
                    <label for="singkatan" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Singkatan / Label <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="singkatan" id="singkatan" required placeholder="Contoh: Aptika" class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm placeholder-[#5a508f]/50 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-[#d4d1f5]/40 pt-4">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-xl transition-all cursor-pointer">Batalkan</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#1b3bbb] hover:bg-[#152e96] text-white text-xs font-bold rounded-xl shadow-md shadow-[#1b3bbb]/20 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                        <span>Simpan Bidang</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT BIDANG -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-md overflow-y-auto transition-all duration-300">
        <div @click.away="openEditModal = false" 
             class="bg-white border border-[#d4d1f5] rounded-3xl w-full max-w-md shadow-2xl overflow-hidden relative text-[#2e2552] my-auto flex flex-col"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Edit Master Bidang</h3>
                        <p class="text-[11px] text-indigo-100 font-medium">Perbarui data nama atau singkatan bidang</p>
                    </div>
                </div>
                <button @click="openEditModal = false" type="button" class="p-1.5 bg-white/10 hover:bg-rose-500/80 rounded-xl text-white transition-all cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form Content -->
            <form :action="'/admin/bidang/' + editBidang.id" method="POST" class="p-5 space-y-4">
                @csrf
                @method('PUT')
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Nama Bidang Lengkap <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="nama" required x-model="editBidang.nama" class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm placeholder-[#5a508f]/50 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
                </div>
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Singkatan / Label <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="singkatan" required x-model="editBidang.singkatan" class="w-full px-3.5 py-2.5 bg-[#f4f6fc] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-sm placeholder-[#5a508f]/50 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] transition-all">
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
