@extends('layouts.app')

@section('title', 'Kelola Bidang')

@section('content')
<div x-data="{ openAddModal: false, openEditModal: false, editBidang: {} }" class="space-y-6">
    
    <!-- Title & Add Trigger -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-[#2e2552] tracking-wide">Kelola Master Bidang</h1>
            <p class="text-xs text-[#5a508f] mt-0.5">Tambahkan atau perbarui data bidang di lingkungan Dinkominfo</p>
        </div>
        <button @click="openAddModal = true"
                class="px-4 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-xl shadow-md shadow-[#2e2552]/10 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Bidang Baru</span>
        </button>
    </div>

    <!-- Bidang Table Card -->
    <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm overflow-hidden text-[#2e2552]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2e2552]">
                <thead class="text-xs font-bold uppercase tracking-wider text-[#5a508f] border-b border-[#d4d1f5]/40">
                    <tr>
                        <th class="py-4 px-4">Nama Bidang</th>
                        <th class="py-4 px-4 text-center">Singkatan</th>
                        <th class="py-4 px-4 text-center">Jumlah Pegawai</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#d4d1f5]/30">
                    @forelse($bidangs as $bid)
                        <tr class="hover:bg-[#f8f7ff] transition-colors">
                            <td class="py-4 px-4 font-bold text-[#2e2552]">{{ $bid->nama }}</td>
                            <td class="py-4 px-4 text-center font-black text-[#8e88dd]">{{ $bid->singkatan }}</td>
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
    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="openAddModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden relative text-[#2e2552]">
            <div class="absolute top-0 left-0 w-full h-[2px] bg-[#2e2552]"></div>
            <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                <h3 class="text-base font-bold text-[#2e2552]">Tambah Bidang Baru</h3>
                <button @click="openAddModal = false" class="text-[#5a508f] hover:text-[#2e2552]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('admin.bidang.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="space-y-1">
                    <label for="nama" class="block text-xs font-bold text-[#5a508f] uppercase">Nama Bidang Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" id="nama" required placeholder="Contoh: Bidang Aplikasi Informatika" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>
                <div class="space-y-1">
                    <label for="singkatan" class="block text-xs font-bold text-[#5a508f] uppercase">Singkatan / Label <span class="text-rose-500">*</span></label>
                    <input type="text" name="singkatan" id="singkatan" required placeholder="Contoh: Aptika" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-[#d4d1f5]/40 pt-4">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-2xl">Batalkan</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl">Simpan Bidang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT BIDANG -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="openEditModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden relative text-[#2e2552]">
            <div class="absolute top-0 left-0 w-full h-[2px] bg-[#2e2552]"></div>
            <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                <h3 class="text-base font-bold text-[#2e2552]">Edit Master Bidang</h3>
                <button @click="openEditModal = false" class="text-[#5a508f] hover:text-[#2e2552]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form :action="'/admin/bidang/' + editBidang.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Nama Bidang Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" required x-model="editBidang.nama" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Singkatan / Label <span class="text-rose-500">*</span></label>
                    <input type="text" name="singkatan" required x-model="editBidang.singkatan" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm">
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
