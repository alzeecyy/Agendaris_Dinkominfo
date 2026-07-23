@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="w-full flex flex-col gap-6 select-none">
    <!-- Header Page -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" 
               class="p-2 sm:p-2.5 rounded-2xl bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/80 shadow-xs transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-lg sm:text-2xl font-black text-[#09103c] tracking-tight leading-tight">PROFIL SAYA</h1>
                <p class="text-slate-500 text-xs sm:text-sm font-semibold mt-0.5">Detail informasi kepegawaian Anda di sistem Sirena</p>
            </div>
        </div>

        <a href="{{ route('password.change') }}" 
           class="px-4 py-2.5 bg-[#1b3bbb]/5 hover:bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-2 border border-[#1b3bbb]/15 shrink-0 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <span>Ubah Password</span>
        </a>
    </div>

    <!-- Main Content Container -->
    <div class="w-full bg-white rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
        <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <h3 class="text-xs sm:text-sm font-extrabold text-[#09103c] uppercase tracking-wider">Informasi Kepegawaian</h3>
            
            <div class="text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/80 rounded-full px-3 py-1 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Akun Aktif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <!-- NIP -->
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nomor Induk Pegawai (NIP)</span>
                <div class="text-sm sm:text-base font-bold text-[#09103c] bg-slate-50 border border-slate-200/80 rounded-2xl px-4 py-3 font-mono">{{ Auth::user()->nip }}</div>
            </div>

            <!-- Nama Lengkap -->
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</span>
                <div class="text-sm sm:text-base font-bold text-[#09103c] bg-slate-50 border border-slate-200/80 rounded-2xl px-4 py-3">{{ Auth::user()->name }}</div>
            </div>

            <!-- Jabatan -->
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Jabatan / Fungsi</span>
                <div class="text-sm sm:text-base font-bold text-[#09103c] bg-slate-50 border border-slate-200/80 rounded-2xl px-4 py-3">{{ Auth::user()->jabatan }}</div>
            </div>

            <!-- Bidang -->
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Bidang / Unit Kerja</span>
                <div class="text-sm sm:text-base font-bold text-[#09103c] bg-slate-50 border border-slate-200/80 rounded-2xl px-4 py-3">
                    {{ Auth::user()->bidang->nama ?? 'Dinas Komunikasi dan Informatika (Master)' }}
                    @if(Auth::user()->bidang->singkatan ?? false)
                        ({{ Auth::user()->bidang->singkatan }})
                    @endif
                </div>
            </div>

            @php
                $bidSuffix = Auth::user()->bidang ? ' ' . (Auth::user()->bidang->singkatan ?? Auth::user()->bidang->nama) : '';
                $roleLabels = [
                    'admin' => 'Administrator',
                    'sekretaris_master' => 'Sekretaris Dinas',
                    'ketua_master' => 'Kepala Dinas',
                    'sekretaris_bidang' => 'Admin Bidang' . $bidSuffix,
                    'ketua_bidang' => 'Ketua Bidang' . $bidSuffix,
                    'staff' => 'Staff Pegawai',
                ];
            @endphp

            <!-- Role Sistem -->
            <div class="space-y-1.5 sm:col-span-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Hak Akses Sistem</span>
                <div class="text-sm sm:text-base font-bold text-[#09103c] bg-slate-50 border border-slate-200/80 rounded-2xl px-4 py-3">
                    {{ $roleLabels[Auth::user()->role] ?? Auth::user()->role }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
