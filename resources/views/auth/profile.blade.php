@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="w-full bg-white rounded-2xl md:rounded-[28px] p-4 sm:p-5 md:p-6 shadow-xs border border-slate-100/80 space-y-4">
    <!-- Top Row: Back Arrow, Title, Subtitle -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="p-1.5 sm:p-2 rounded-xl bg-slate-100/80 hover:bg-slate-200 text-slate-600 transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-base sm:text-lg font-black text-[#09103c] tracking-tight leading-tight">PROFIL SAYA</h1>
                <p class="text-slate-500 text-[10px] sm:text-xs font-semibold">Detail informasi kepegawaian Anda di sistem Sirena</p>
            </div>
        </div>
    </div>

    <!-- Section Card: INFORMASI KEPEGAWAIAN -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-xs sm:text-sm font-extrabold text-[#09103c] uppercase tracking-wider">INFORMASI KEPEGAWAIAN</h2>
            <a href="{{ route('password.change') }}" class="px-3 py-1.5 bg-indigo-50/80 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-indigo-100/80 shrink-0">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span>Ubah Kata Sandi</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
            <!-- Row 1: NIP & Nama Lengkap -->
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">NOMOR INDUK PEGAWAI (NIP)</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/70 rounded-xl px-3.5 py-2 font-mono shadow-xs">
                    {{ Auth::user()->nip }}
                </div>
            </div>

            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">NAMA LENGKAP</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/70 rounded-xl px-3.5 py-2 shadow-xs">
                    {{ Auth::user()->name }}
                </div>
            </div>

            <!-- Row 2: Jabatan & Bidang -->
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">JABATAN / FUNGSI</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/70 rounded-xl px-3.5 py-2 shadow-xs">
                    {{ Auth::user()->jabatan }}
                </div>
            </div>

            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">BIDANG / UNIT KERJA</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/70 rounded-xl px-3.5 py-2 shadow-xs">
                    {{ Auth::user()->bidang->nama ?? 'Sekretariat' }}
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

            <!-- Row 3: Hak Akses & Status Akun -->
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">HAK AKSES SISTEM</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-slate-50/80 border border-slate-200/70 rounded-xl px-3.5 py-2 shadow-xs">
                    {{ $roleLabels[Auth::user()->role] ?? Auth::user()->role }}
                </div>
            </div>

            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">STATUS AKUN</span>
                <div class="text-xs sm:text-sm font-bold text-emerald-600 bg-slate-50/80 border border-slate-200/70 rounded-xl px-3.5 py-2 shadow-xs flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Aktif</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
