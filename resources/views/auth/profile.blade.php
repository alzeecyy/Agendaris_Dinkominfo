@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-4">
    <!-- Top Row: Back Arrow, Title, Subtitle -->
    <div class="space-y-1 border-b border-[#d4d1f5]/60 pb-3">
        <div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#5a508f] hover:text-[#1b3bbb] transition-colors py-0.5 group" title="Kembali ke Dashboard">
                <svg class="w-4 h-4 shrink-0 text-[#5a508f] group-hover:text-[#1b3bbb] group-hover:-translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path>
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
        <div>
            <h1 class="text-base sm:text-lg font-black text-[#09103c] tracking-wide leading-tight">PROFIL SAYA</h1>
            <p class="text-[#5a508f] text-[11px] sm:text-xs font-semibold mt-0.5">Detail informasi kepegawaian Anda di sistem Agendaris Dinkominfo</p>
        </div>
    </div>

    <!-- Section Card: INFORMASI KEPEGAWAIAN -->
    <div class="space-y-3">
        <div class="flex items-center justify-between border-b border-[#d4d1f5]/60 pb-2.5">
            <h2 class="text-xs sm:text-sm font-extrabold text-[#09103c] uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#1b3bbb]"></span>
                <span>INFORMASI KEPEGAWAIAN</span>
            </h2>
            <a href="{{ route('password.change') }}" class="px-3 py-1.5 bg-[#1b3bbb] hover:bg-[#09103c] text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs active:scale-95 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span>Ubah Kata Sandi</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
            <!-- Row 1: NIP & Nama Lengkap -->
            <div class="space-y-0.5">
                <span class="text-[11px] font-extrabold text-[#1b3bbb] uppercase tracking-wider block">NOMOR INDUK PEGAWAI (NIP)</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-white border border-[#d4d1f5]/80 rounded-xl px-3.5 py-2 shadow-2xs">
                    {{ Auth::user()->nip }}
                </div>
            </div>

            <div class="space-y-0.5">
                <span class="text-[11px] font-extrabold text-[#1b3bbb] uppercase tracking-wider block">NAMA LENGKAP</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-white border border-[#d4d1f5]/80 rounded-xl px-3.5 py-2 shadow-2xs">
                    {{ Auth::user()->name }}
                </div>
            </div>

            <!-- Row 2: Jabatan & Bidang -->
            <div class="space-y-0.5">
                <span class="text-[11px] font-extrabold text-[#1b3bbb] uppercase tracking-wider block">JABATAN / FUNGSI</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-white border border-[#d4d1f5]/80 rounded-xl px-3.5 py-2 shadow-2xs">
                    {{ Auth::user()->jabatan }}
                </div>
            </div>

            <div class="space-y-0.5">
                <span class="text-[11px] font-extrabold text-[#1b3bbb] uppercase tracking-wider block">BIDANG / UNIT KERJA</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-white border border-[#d4d1f5]/80 rounded-xl px-3.5 py-2 shadow-2xs">
                    {{ Auth::user()->bidang->nama ?? 'Sekretariat' }}
                    @if(Auth::user()->bidang->singkatan ?? false)
                        ({{ Auth::user()->bidang->singkatan }})
                    @endif
                </div>
            </div>

            <!-- Row 3: Hak Akses & Status Akun -->
            <div class="space-y-0.5">
                <span class="text-[11px] font-extrabold text-[#1b3bbb] uppercase tracking-wider block">HAK AKSES SISTEM</span>
                <div class="text-xs sm:text-sm font-bold text-[#09103c] bg-white border border-[#d4d1f5]/80 rounded-xl px-3.5 py-2 shadow-2xs">
                    {{ Auth::user()->role_label }}
                </div>
            </div>

            <div class="space-y-0.5">
                <span class="text-[11px] font-extrabold text-emerald-700 uppercase tracking-wider block">STATUS AKUN</span>
                <div class="text-xs sm:text-sm font-extrabold text-emerald-700 bg-emerald-50/80 border border-emerald-200 rounded-xl px-3.5 py-2 shadow-2xs flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Aktif (Terverifikasi)</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
