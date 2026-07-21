@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="flex flex-col gap-4 select-none">
    <!-- Header Page -->
    <div class="flex flex-col">
        <a href="{{ route('dashboard') }}" 
           class="inline-flex items-center text-slate-500 hover:text-[#1b3bbb] transition-colors duration-200 -mt-2.5 mb-4 p-1 -ml-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-lg font-black text-[#09103c] tracking-tight leading-none">PROFIL SAYA</h1>
            <p class="text-slate-500 text-[10px] font-semibold mt-1.5 leading-none">Detail informasi kepegawaian Anda di sistem Sirena</p>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="bg-white rounded-[32px] p-6 shadow-md border border-slate-100/60 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-3">
            <h3 class="text-xs font-black text-[#09103c] uppercase tracking-wider">Informasi Kepegawaian</h3>
            
            <a href="{{ route('password.change') }}" 
               class="px-3.5 py-2 bg-[#1b3bbb]/5 hover:bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-2xl text-[11px] font-bold transition-all duration-200 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Ubah Kata Sandi
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- NIP -->
            <div class="space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Induk Pegawai (NIP)</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-2 font-mono">{{ Auth::user()->nip }}</div>
            </div>

            <!-- Nama Lengkap -->
            <div class="space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-2">{{ Auth::user()->name }}</div>
            </div>

            <!-- Jabatan -->
            <div class="space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Jabatan / Fungsi</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-2">{{ Auth::user()->jabatan }}</div>
            </div>

            <!-- Bidang -->
            <div class="space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Bidang / Unit Kerja</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-2">
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
            <div class="space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Hak Akses Sistem</span>
                <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-2">
                    {{ $roleLabels[Auth::user()->role] ?? Auth::user()->role }}
                </div>
            </div>

            <!-- Status Akun -->
            <div class="space-y-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Status Akun</span>
                <div class="text-xs font-bold text-emerald-600 bg-emerald-50/50 border border-emerald-100 rounded-2xl px-4 py-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Aktif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
