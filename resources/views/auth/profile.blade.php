@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="h-full flex flex-col gap-6 select-none">
    <!-- Header Page -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-xl font-black text-[#09103c] tracking-tight">PROFIL SAYA</h1>
            <p class="text-slate-500 text-xs font-semibold mt-1">Detail informasi kepegawaian Anda di sistem Agendaris</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" 
               class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-2xl text-xs font-bold text-slate-700 shadow-sm transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Profile Card -->
        <div class="bg-white rounded-[32px] p-8 shadow-md border border-slate-100/60 flex flex-col items-center text-center">
            <!-- Large Initials Avatar -->
            <div class="w-24 h-24 bg-[#1b3bbb]/10 rounded-[32px] flex items-center justify-center font-black text-2xl text-[#1b3bbb] border border-[#1b3bbb]/20 shadow-md mb-5">
                {{ substr(Auth::user()->name, 0, 2) }}
            </div>
            
            <h2 class="text-base font-black text-[#09103c] leading-tight">{{ Auth::user()->name }}</h2>
            <p class="text-[11px] text-slate-400 font-mono mt-1">NIP. {{ Auth::user()->nip }}</p>
            
            <div class="mt-4">
                @php
                    $roleLabels = [
                        'admin' => 'Administrator',
                        'sekretaris_master' => 'Sekretaris Master',
                        'ketua_master' => 'Kepala Dinas',
                        'sekretaris_bidang' => 'Sekretaris Bidang',
                        'ketua_bidang' => 'Ketua Bidang',
                        'staff' => 'Staff Pegawai',
                    ];
                    $roleColors = [
                        'admin' => 'bg-red-50 text-red-700 border-red-200',
                        'sekretaris_master' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'ketua_master' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                        'sekretaris_bidang' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'ketua_bidang' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'staff' => 'bg-blue-50 text-blue-700 border-blue-200',
                    ];
                @endphp
                <span class="inline-block text-[10px] font-bold px-3 py-1 rounded-full border {{ $roleColors[Auth::user()->role] ?? 'bg-slate-100 border-slate-200 text-slate-700' }} uppercase tracking-wider">
                    {{ $roleLabels[Auth::user()->role] ?? 'User' }}
                </span>
            </div>

            <div class="w-full border-t border-slate-100 my-6"></div>

            <div class="w-full space-y-3">
                <a href="{{ route('password.change') }}" 
                   class="w-full py-3 bg-[#1b3bbb]/5 hover:bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-2xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Ubah Kata Sandi
                </a>

                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" 
                            class="w-full py-3 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-2xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Keluar dari Sistem
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Side: Detailed Details Card -->
        <div class="lg:col-span-2 bg-white rounded-[32px] p-8 shadow-md border border-slate-100/60 space-y-6">
            <div>
                <h3 class="text-sm font-black text-[#09103c] border-b border-slate-100 pb-3 uppercase tracking-wider">Informasi Kepegawaian</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- NIP -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Induk Pegawai (NIP)</span>
                    <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 font-mono">{{ Auth::user()->nip }}</div>
                </div>

                <!-- Nama Lengkap -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</span>
                    <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">{{ Auth::user()->name }}</div>
                </div>

                <!-- Jabatan -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jabatan / Fungsi</span>
                    <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">{{ Auth::user()->jabatan }}</div>
                </div>

                <!-- Bidang -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bidang / Unit Kerja</span>
                    <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">
                        {{ Auth::user()->bidang->nama ?? 'Dinas Komunikasi dan Informatika (Master)' }}
                        @if(Auth::user()->bidang->singkatan ?? false)
                            ({{ Auth::user()->bidang->singkatan }})
                        @endif
                    </div>
                </div>

                <!-- Role Sistem -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Hak Akses Sistem</span>
                    <div class="text-xs font-bold text-[#09103c] bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">
                        {{ $roleLabels[Auth::user()->role] ?? Auth::user()->role }}
                    </div>
                </div>

                <!-- Status Akun -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Akun</span>
                    <div class="text-xs font-bold text-emerald-600 bg-emerald-50/50 border border-emerald-100 rounded-2xl px-4 py-3 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Aktif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
