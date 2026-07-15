@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Grid (Greeting & Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-stretch">
        
        <!-- Welcome Card -->
        <div class="md:col-span-1 bg-[#2e2552] text-white rounded-[32px] p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
            <!-- Decorative circle overlay -->
            <div class="absolute -top-12 -right-12 w-28 h-28 bg-white/5 rounded-full"></div>
            
            <div class="space-y-2 z-10">
                <span class="text-[10px] font-bold uppercase tracking-widest text-[#8e88dd]">Panel Admin</span>
                <h3 class="text-xl font-black leading-tight">Kontrol Sistem &amp; Akun Pegawai</h3>
                <p class="text-xs text-[#bda6ff] leading-relaxed">Kelola otentikasi, pembagian divisi/bidang, serta pemantauan data aktivitas Agendaris.</p>
            </div>
            
            <div class="mt-6 z-10">
                <a href="{{ route('admin.users.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-[#2e2552] hover:bg-[#ebe9fe] text-xs font-bold rounded-xl shadow-sm transition-all duration-200">
                    <span>Kelola Pegawai</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- KPI 1: Pegawai Stats -->
        <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#5a508f] uppercase">Total Akun Pegawai</span>
                <div class="p-2 bg-[#8ba0f2]/10 text-[#8ba0f2] rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h2 class="text-4xl font-black text-[#2e2552]">{{ $stats['total_users'] }}</h2>
                <div class="flex items-center gap-3 mt-1.5 text-[11px] font-bold">
                    <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100">{{ $stats['active_users'] }} Aktif</span>
                    <span class="text-slate-400 bg-slate-100 px-2 py-0.5 rounded-lg border border-slate-200">{{ $stats['inactive_users'] }} Nonaktif</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Bidang & Agenda -->
        <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#5a508f] uppercase">Bidang &amp; Agenda</span>
                <div class="p-2 bg-[#bc8bf2]/10 text-[#bc8bf2] rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h2 class="text-4xl font-black text-[#2e2552]">{{ $stats['total_bidang'] }} <span class="text-xs text-[#5a508f] font-bold">Bidang</span></h2>
                <p class="text-xs text-[#5a508f] mt-1.5 font-bold">Mengelola total <span class="text-[#2e2552]">{{ $stats['total_agenda'] }}</span> agenda rapat dinas</p>
            </div>
        </div>

        <!-- KPI 3: Notulensi Stats -->
        <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#5a508f] uppercase">Ringkasan Notulensi</span>
                <div class="p-2 bg-amber-50 text-amber-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h2 class="text-4xl font-black text-[#2e2552]">{{ $stats['total_notulensi'] }}</h2>
                <div class="flex items-center gap-3 mt-1.5 text-[11px] font-bold">
                    <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100">{{ $stats['approved_notulensi'] }} Sah</span>
                    <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-100">{{ $stats['pending_notulensi'] }} Review</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Tables Grid (Recent Users & Recent Agendas) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Card: Recent Registered Users -->
        <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm flex flex-col justify-between text-[#2e2552]">
            <div>
                <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-4 mb-4">
                    <div>
                        <h3 class="text-sm font-black text-[#2e2552] uppercase tracking-wider">Pegawai Terdaftar Terbaru</h3>
                        <p class="text-[10px] text-[#5a508f] mt-0.5">5 akun pegawai yang terakhir kali ditambahkan ke sistem</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-[#8e88dd] hover:text-[#2e2552] transition-colors">Semua Pegawai &rarr;</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-[#2e2552]">
                        <thead class="text-[10px] font-bold uppercase tracking-wider text-[#5a508f] border-b border-[#d4d1f5]/30">
                            <tr>
                                <th class="py-3 px-2">Nama</th>
                                <th class="py-3 px-2">Bidang</th>
                                <th class="py-3 px-2">Role</th>
                                <th class="py-3 px-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d4d1f5]/20">
                            @forelse($recentUsers as $user)
                                <tr class="hover:bg-[#f8f7ff] transition-colors">
                                    <td class="py-3 px-2 font-bold max-w-[120px] truncate">
                                        <div class="font-bold text-[#2e2552]">{{ $user->name }}</div>
                                        <div class="text-[10px] text-[#5a508f] font-mono mt-0.5">{{ $user->nip }}</div>
                                    </td>
                                    <td class="py-3 px-2 font-semibold text-[#5a508f]">{{ $user->bidang->singkatan ?? 'Master' }}</td>
                                    <td class="py-3 px-2 font-bold text-slate-700 capitalize">{{ str_replace('_', ' ', $user->role) }}</td>
                                    <td class="py-3 px-2 text-center">
                                        @if($user->active)
                                            <span class="inline-block text-[8px] font-black px-1.5 py-0.5 uppercase rounded bg-emerald-50 text-emerald-600 border border-emerald-100">Aktif</span>
                                        @else
                                            <span class="inline-block text-[8px] font-black px-1.5 py-0.5 uppercase rounded bg-slate-100 text-slate-400 border border-slate-200">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-[#8e88dd] italic">Tidak ada pegawai terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card: Recent Agendas -->
        <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm flex flex-col justify-between text-[#2e2552]">
            <div>
                <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-4 mb-4">
                    <div>
                        <h3 class="text-sm font-black text-[#2e2552] uppercase tracking-wider">Agenda Rapat Terkini</h3>
                        <p class="text-[10px] text-[#5a508f] mt-0.5">5 agenda rapat dinas terbaru yang terjadwal di sistem</p>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sistem Utama</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-[#2e2552]">
                        <thead class="text-[10px] font-bold uppercase tracking-wider text-[#5a508f] border-b border-[#d4d1f5]/30">
                            <tr>
                                <th class="py-3 px-2">Judul Rapat</th>
                                <th class="py-3 px-2">Tanggal / Waktu</th>
                                <th class="py-3 px-2">Penyelenggara</th>
                                <th class="py-3 px-2 text-center">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d4d1f5]/20">
                            @forelse($recentAgendas as $agenda)
                                <tr class="hover:bg-[#f8f7ff] transition-colors">
                                    <td class="py-3 px-2 font-bold max-w-[150px] truncate text-[#2e2552]">{{ $agenda->judul }}</td>
                                    <td class="py-3 px-2">
                                        <div class="font-semibold text-slate-700">{{ $agenda->tanggal->format('d M Y') }}</div>
                                        <div class="text-[10px] text-[#5a508f] mt-0.5">{{ substr($agenda->jam_mulai, 0, 5) }} - {{ substr($agenda->jam_selesai, 0, 5) }} WIB</div>
                                    </td>
                                    <td class="py-3 px-2 font-bold text-[#8e88dd]">
                                        {{ $agenda->sekretaris->bidang->singkatan ?? 'Dinkominfo (Master)' }}
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <span class="inline-block text-[8px] font-black px-2 py-0.5 rounded-full border {{ $agenda->kategori === 'rutin' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-rose-50 text-rose-700 border-rose-200' }} uppercase">
                                            {{ $agenda->kategori }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-[#8e88dd] italic">Tidak ada agenda rapat terbaru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
