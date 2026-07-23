@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="flex flex-col gap-3 select-none max-w-4xl mx-auto">
    <!-- Header Page -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" 
               class="p-1.5 rounded-xl bg-slate-100/80 hover:bg-slate-200 text-slate-500 hover:text-slate-800 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-base sm:text-lg font-black text-[#09103c] tracking-tight leading-tight">PROFIL SAYA</h1>
                <p class="text-slate-500 text-[10px] sm:text-xs font-semibold">Detail informasi kepegawaian Anda di sistem Sirena</p>
            </div>
        </div>

        <a href="{{ route('password.change') }}" 
           class="px-3 py-1.5 bg-[#1b3bbb]/5 hover:bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-xl text-[11px] font-bold transition-all duration-200 flex items-center gap-1.5 border border-[#1b3bbb]/10 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <span>Ubah Password</span>
        </a>
    </div>

    <!-- Main Content Container -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-100 space-y-3">
        <div class="border-b border-slate-100 pb-2.5 flex items-center justify-between">
            <h3 class="text-[11px] sm:text-xs font-black text-[#09103c] uppercase tracking-wider">Informasi Kepegawaian</h3>
            
            <div class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-full px-2.5 py-0.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Akun Aktif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
            <!-- NIP -->
            <div class="space-y-1">
                <span class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Induk Pegawai (NIP)</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/60 rounded-xl px-3 py-2 font-mono">{{ Auth::user()->nip }}</div>
            </div>

            <!-- Nama Lengkap -->
            <div class="space-y-1">
                <span class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/60 rounded-xl px-3 py-2">{{ Auth::user()->name }}</div>
            </div>

            <!-- Jabatan -->
            <div class="space-y-1">
                <span class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider block">Jabatan / Fungsi</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/60 rounded-xl px-3 py-2">{{ Auth::user()->jabatan }}</div>
            </div>

            <!-- Bidang -->
            <div class="space-y-1">
                <span class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider block">Bidang / Unit Kerja</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/60 rounded-xl px-3 py-2">
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
            <div class="space-y-1 sm:col-span-2">
                <span class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider block">Hak Akses Sistem</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/60 rounded-xl px-3 py-2">
                    {{ $roleLabels[Auth::user()->role] ?? Auth::user()->role }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
