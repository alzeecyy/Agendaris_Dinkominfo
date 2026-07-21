<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Sirena Dinkominfo Banyumas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
        }
        /* Custom hover lift effect */
        .hover-card-trigger {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-card-trigger:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 20px -5px rgba(27, 59, 187, 0.1), 0 8px 8px -5px rgba(27, 59, 187, 0.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-screen flex flex-col text-[#09103c] relative overflow-hidden antialiased">
    <!-- Background glowing spots -->
    <div class="absolute top-0 right-1/4 w-[400px] h-[400px] bg-blue-300/20 rounded-full filter blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-[300px] h-[300px] bg-[#1b3bbb]/5 rounded-full filter blur-[80px] pointer-events-none"></div>

    <!-- Outer Portal Container -->
    <div class="h-full w-full max-w-[1100px] mx-auto p-4 md:p-6 flex flex-col gap-4 md:gap-5 z-10">
        
        <!-- Header / Top Bar -->
        <header class="flex items-center justify-between relative bg-white/60 backdrop-blur-md rounded-2xl border border-slate-200/60 px-5 py-3 shadow-sm text-[#09103c] z-50">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 select-none z-10">
                <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Banyumas" class="h-9 w-auto hover:scale-105 transition-transform duration-300">
                <div class="flex flex-col justify-center">
                    <h1 class="text-[11px] font-extrabold leading-none text-[#09103c] tracking-tight">Dinas Komunikasi dan Informatika</h1>
                    <span class="text-[9px] text-slate-500 font-semibold tracking-tight mt-1 leading-none">Pemerintah Kabupaten Banyumas</span>
                </div>
            </div>



            <!-- Profile Area -->
            @auth
                @php
                    $roleLabels = [
                        'admin' => 'Administrator',
                        'sekretaris_master' => 'Sekretaris Dinas',
                        'ketua_master' => 'Kepala Dinas',
                        'sekretaris_bidang' => 'Admin Bidang',
                        'ketua_bidang' => 'Ketua Bidang',
                        'staff' => 'Staff Pegawai',
                    ];
                    $roleColors = [
                        'admin' => 'bg-red-50 text-red-700 border-red-100',
                        'sekretaris_master' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'ketua_master' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
                        'sekretaris_bidang' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'ketua_bidang' => 'bg-purple-50 text-purple-700 border-purple-100',
                        'staff' => 'bg-blue-50 text-blue-700 border-blue-100',
                    ];
                @endphp
                <a href="{{ route('profile') }}" class="relative shrink-0 select-none text-[#09103c] flex items-center gap-2.5 p-1 rounded-xl hover:bg-slate-50 transition-colors">
                    <div class="hidden sm:block text-right" style="margin: 0; padding: 0; display: flex; flex-direction: column; justify-content: center; gap: 3px;">
                        <div class="text-[11px] font-black text-[#09103c]" style="line-height: 1; margin: 0; padding: 0;">{{ Auth::user()->name }}</div>
                        <div style="line-height: 1; margin: 0; padding: 0;">
                            <span class="inline-block text-[7.5px] font-extrabold px-1.5 py-0.5 rounded-full border {{ $roleColors[Auth::user()->role] ?? 'bg-slate-100 border-slate-200 text-slate-700' }} uppercase tracking-wider" style="line-height: 1; vertical-align: middle;">
                                {{ $roleLabels[Auth::user()->role] ?? 'User' }}
                            </span>
                        </div>
                        <div class="text-[8px] text-slate-500 font-bold font-mono" style="line-height: 1; margin: 0; padding: 0;">NIP. {{ Auth::user()->nip }}</div>
                    </div>
                    <div class="w-8.5 h-8.5 bg-[#1b3bbb]/10 rounded-xl flex items-center justify-center font-extrabold text-xs text-[#1b3bbb] border border-[#1b3bbb]/20 shadow-sm hover:bg-[#1b3bbb]/20 transition-colors">
                        {{ substr(Auth::user()->name, 0, 2) }}
                    </div>
                </a>
            @endauth
        </header>

        <!-- Main Content Portal -->
        <main class="flex-1 flex flex-col justify-center items-center gap-5 py-2 min-h-0 overflow-auto">
            <!-- Hero Welcome Card -->
            <div class="w-full bg-white/60 backdrop-blur-md rounded-[24px] border border-slate-200/60 p-6 md:p-8 shadow-xl text-center space-y-3 max-w-3xl animate-fade-in shrink-0">
                <div class="flex justify-center">
                    <span class="text-[8px] font-bold uppercase tracking-widest text-[#3b59f3] bg-[#3b59f3]/10 px-3.5 py-1 rounded-full border border-[#3b59f3]/20">Portal Sirena</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-[#09103c] tracking-tight leading-tight">
                    Halo, {{ Auth::user()->name }}
                </h2>
                <p class="text-xs md:text-sm text-slate-600 font-medium max-w-xl mx-auto leading-relaxed">
                    Sistem koordinasi dinas, pencatatan presensi rapat mandiri, dan penyusunan notulensi kerja Dinas Komunikasi dan Informatika Kabupaten Banyumas. Silakan pilih menu di bawah ini untuk memulai.
                </p>
            </div>

            <!-- Features Navigation Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full max-w-4xl shrink-0">
                <!-- Card 1: Dashboard / Kelola Pegawai (Solid Highlight) -->
                @if(Auth::check() && !Auth::user()->isAdmin())
                    <a href="{{ route('dashboard') }}" class="hover-card-trigger bg-gradient-to-br from-[#1b3bbb] to-[#0a1250] rounded-[24px] border border-[#1b3bbb]/40 p-6 flex flex-col justify-between shadow-lg relative overflow-hidden group text-white">
                        <div class="absolute -top-12 -right-12 w-24 h-24 bg-white/5 rounded-full group-hover:scale-110 transition-transform duration-300"></div>
                        <div class="space-y-3 z-10">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-base font-extrabold text-white">Dashboard Utama</h3>
                                <p class="text-[11px] text-blue-100/80 leading-normal">Pantau agenda rapat, presensi mandiri, dan statistik kedinasan Anda secara real-time.</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-white group-hover:gap-2.5 transition-all duration-300 z-10">
                            <span>Buka Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @elseif(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('dashboard') }}" class="hover-card-trigger bg-gradient-to-br from-[#1b3bbb] to-[#0a1250] rounded-[24px] border border-[#1b3bbb]/40 p-6 flex flex-col justify-between shadow-lg relative overflow-hidden group text-white">
                        <div class="absolute -top-12 -right-12 w-24 h-24 bg-white/5 rounded-full group-hover:scale-110 transition-transform duration-300"></div>
                        <div class="space-y-3 z-10">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-base font-extrabold text-white">Dashboard Admin</h3>
                                <p class="text-[11px] text-blue-100/80 leading-normal">Pantau statistik sistem, aktivitas pegawai, dan agenda kedinasan secara real-time.</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-white group-hover:gap-2.5 transition-all duration-300 z-10">
                            <span>Buka Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endif

                <!-- Card 2: Calendar / Bidang Admin (Clean White Card) -->
                @if(Auth::check() && !Auth::user()->isAdmin())
                    <a href="{{ route('calendar') }}" class="hover-card-trigger bg-white rounded-[24px] border border-slate-200/80 p-6 flex flex-col justify-between shadow-md relative overflow-hidden group text-[#090c24]">
                        <div class="space-y-3">
                            <div class="w-10 h-10 bg-[#1b3bbb]/5 rounded-xl flex items-center justify-center text-[#1b3bbb] group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-base font-extrabold text-[#090c24]">Kalender Rinci</h3>
                                <p class="text-[11px] text-slate-500 leading-normal">Lihat peta agenda bulanan, koordinasi terjadwal, dan detail teknis rapat.</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-[#1b3bbb] group-hover:gap-2.5 transition-all duration-300">
                            <span>Buka Kalender</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @elseif(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="hover-card-trigger bg-white rounded-[24px] border border-slate-200/80 p-6 flex flex-col justify-between shadow-md relative overflow-hidden group text-[#090c24]">
                        <div class="space-y-3">
                            <div class="w-10 h-10 bg-[#1b3bbb]/5 rounded-xl flex items-center justify-center text-[#1b3bbb] group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-base font-extrabold text-[#090c24]">Kelola Pegawai</h3>
                                <p class="text-[11px] text-slate-500 leading-normal">Tambah, edit, hapus, reset password, dan kelola status aktif pengguna sistem.</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-[#1b3bbb] group-hover:gap-2.5 transition-all duration-300">
                            <span>Kelola Pengguna</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endif

                <!-- Card 3: Riwayat Rapat / Ganti Password (Clean White Card) -->
                @if(Auth::check() && !Auth::user()->isAdmin())
                    <a href="{{ route('riwayat') }}" class="hover-card-trigger bg-white rounded-[24px] border border-slate-200/80 p-6 flex flex-col justify-between shadow-md relative overflow-hidden group text-[#090c24]">
                        <div class="space-y-3">
                            <div class="w-10 h-10 bg-[#1b3bbb]/5 rounded-xl flex items-center justify-center text-[#1b3bbb] group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-base font-extrabold text-[#090c24]">Riwayat Rapat</h3>
                                <p class="text-[11px] text-slate-500 leading-normal">Akses berkas notulensi, unduh PDF/Word, dan periksa risalah rapat sebelumnya.</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-[#1b3bbb] group-hover:gap-2.5 transition-all duration-300">
                            <span>Buka Riwayat</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @elseif(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('admin.bidang.index') }}" class="hover-card-trigger bg-white rounded-[24px] border border-slate-200/80 p-6 flex flex-col justify-between shadow-md relative overflow-hidden group text-[#090c24]">
                        <div class="space-y-3">
                            <div class="w-10 h-10 bg-[#1b3bbb]/5 rounded-xl flex items-center justify-center text-[#1b3bbb] group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-base font-extrabold text-[#090c24]">Kelola Bidang</h3>
                                <p class="text-[11px] text-slate-500 leading-normal">Tambah, perbarui, dan atur struktur bidang/seksi di bawah Dinkominfo Banyumas.</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-[#1b3bbb] group-hover:gap-2.5 transition-all duration-300">
                            <span>Kelola Bidang</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endif
            </div>
        </main>

        <!-- Footer Area -->
        <footer class="text-center text-slate-500 text-[10px] font-bold uppercase tracking-wider select-none shrink-0">
            &copy; 2026 Dinas Komunikasi dan Informatika Kabupaten Banyumas. <span class="tracking-normal">SIRENA V2.0</span>
        </footer>
    </div>
</body>
</html>
