<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Sirena Dinkominfo Banyumas</title>
    <!-- Favicon / Logo Resmi -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banyumas-crest.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
        }
        .hover-card-trigger {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-card-trigger:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px -5px rgba(27, 59, 187, 0.15), 0 8px 10px -6px rgba(27, 59, 187, 0.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen md:h-screen flex flex-col text-[#09103c] relative overflow-y-auto md:overflow-hidden antialiased">
    <!-- Background glowing spots -->
    <div class="absolute top-0 right-1/4 w-[450px] h-[450px] bg-blue-300/20 rounded-full filter blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-[350px] h-[350px] bg-[#1b3bbb]/5 rounded-full filter blur-[80px] pointer-events-none"></div>

    <!-- Outer Portal Container -->
    <div class="min-h-screen md:h-full w-full max-w-[1150px] mx-auto p-4 md:p-6 lg:p-8 flex flex-col justify-between gap-4 md:gap-6 z-10">
        
        <!-- Header / Top Bar -->
        <header class="flex items-center justify-between relative bg-white/70 backdrop-blur-md rounded-2xl border border-slate-200/80 px-4 py-3 md:px-6 md:py-3.5 shadow-sm text-[#09103c] z-50 shrink-0">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 select-none z-10 min-w-0">
                <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Banyumas" class="h-8 sm:h-9 md:h-10 w-auto hover:scale-105 transition-transform duration-300 shrink-0">
                <div class="flex flex-col justify-center min-w-0">
                    <h1 class="text-xs sm:text-sm md:text-base font-extrabold leading-tight text-[#09103c] tracking-tight truncate">Dinas Komunikasi dan Informatika</h1>
                    <span class="text-[9px] sm:text-[10px] md:text-xs text-slate-500 font-semibold tracking-tight leading-none truncate">Pemerintah Kabupaten Banyumas</span>
                </div>
            </div>

            <!-- Profile Area -->
            @auth
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
                    $roleColors = [
                        'admin' => 'bg-red-50 text-red-700 border-red-100',
                        'sekretaris_master' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'ketua_master' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
                        'sekretaris_bidang' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'ketua_bidang' => 'bg-purple-50 text-purple-700 border-purple-100',
                        'staff' => 'bg-blue-50 text-blue-700 border-blue-100',
                    ];
                @endphp
                @if(Auth::user()->isAdmin())
                    <div x-data="{ openAdminMenu: false }" class="relative shrink-0 select-none">
                        <button type="button" @click="openAdminMenu = !openAdminMenu" class="text-[#09103c] flex items-center gap-2.5 p-1 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer focus:outline-none">
                            <div class="hidden lg:flex flex-col justify-center gap-0.5 text-right">
                                <div class="text-xs md:text-sm font-black text-[#09103c] leading-none">{{ Auth::user()->name }}</div>
                                <div>
                                    <span class="inline-block text-[8px] md:text-[9px] font-extrabold px-2 py-0.5 rounded-full border {{ $roleColors[Auth::user()->role] ?? 'bg-slate-100 border-slate-200 text-slate-700' }} uppercase tracking-wider leading-none">
                                        {{ $roleLabels[Auth::user()->role] ?? 'User' }}
                                    </span>
                                </div>
                                <div class="text-[9px] md:text-[10px] text-slate-500 font-bold font-mono leading-none">NIP. {{ Auth::user()->nip }}</div>
                            </div>
                            <div class="w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 bg-[#1b3bbb]/10 rounded-xl md:rounded-2xl flex items-center justify-center font-extrabold text-xs md:text-sm text-[#1b3bbb] border border-[#1b3bbb]/20 shadow-sm hover:bg-[#1b3bbb]/20 transition-colors shrink-0">
                                {{ substr(Auth::user()->name, 0, 2) }}
                            </div>
                        </button>

                        <div x-show="openAdminMenu" 
                             @click.away="openAdminMenu = false" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-150 transform"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-2 w-60 bg-white border border-[#d4d1f5]/80 rounded-2xl shadow-xl z-50 p-3.5 space-y-3 text-[#2e2552]">
                            
                            <div class="pb-2.5 border-b border-[#d4d1f5]/50 flex items-center gap-2.5">
                                <div class="w-8 h-8 bg-red-50 text-red-600 rounded-xl flex items-center justify-center font-black text-xs border border-red-100 shrink-0">
                                    Ad
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-black text-[#2e2552] truncate">{{ Auth::user()->name }}</div>
                                    <div class="text-[10px] text-[#5a508f] font-mono truncate">NIP. {{ Auth::user()->nip }}</div>
                                </div>
                            </div>

                            <div>
                                <a href="{{ route('password.change') }}" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-[#2e2552] hover:bg-[#1b3bbb]/5 hover:text-[#1b3bbb] transition-colors">
                                    <svg class="w-4 h-4 text-[#8e88dd]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <span>Ubah Kata Sandi</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('profile') }}" class="relative shrink-0 select-none text-[#09103c] flex items-center gap-2.5 p-1 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="hidden lg:flex flex-col justify-center gap-0.5 text-right">
                            <div class="text-xs md:text-sm font-black text-[#09103c] leading-none">{{ Auth::user()->name }}</div>
                            <div>
                                <span class="inline-block text-[8px] md:text-[9px] font-extrabold px-2 py-0.5 rounded-full border {{ $roleColors[Auth::user()->role] ?? 'bg-slate-100 border-slate-200 text-slate-700' }} uppercase tracking-wider leading-none">
                                    {{ $roleLabels[Auth::user()->role] ?? 'User' }}
                                </span>
                            </div>
                            <div class="text-[9px] md:text-[10px] text-slate-500 font-bold font-mono leading-none">NIP. {{ Auth::user()->nip }}</div>
                        </div>
                        <div class="w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 bg-[#1b3bbb]/10 rounded-xl md:rounded-2xl flex items-center justify-center font-extrabold text-xs md:text-sm text-[#1b3bbb] border border-[#1b3bbb]/20 shadow-sm hover:bg-[#1b3bbb]/20 transition-colors shrink-0">
                            {{ substr(Auth::user()->name, 0, 2) }}
                        </div>
                    </a>
                @endif
            @endauth
        </header>

        <!-- Main Content Portal -->
        <main class="flex-1 flex flex-col items-center justify-center gap-4 md:gap-6 py-2 min-h-0">
            <!-- Hero Welcome Card -->
            <div class="w-full bg-white/70 backdrop-blur-md rounded-2xl md:rounded-[24px] border border-slate-200/80 p-5 sm:p-6 md:p-8 shadow-sm md:shadow-md text-center space-y-2 md:space-y-3 max-w-3xl animate-fade-in shrink-0">
                <div class="flex justify-center">
                    <span class="text-[9px] md:text-[10px] font-extrabold uppercase tracking-widest text-[#1b3bbb] bg-[#1b3bbb]/10 px-3.5 py-1 rounded-full border border-[#1b3bbb]/20">Portal Sirena</span>
                </div>
                <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-[#09103c] tracking-tight leading-tight">
                    Halo, {{ Auth::user()->name }}
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-slate-600 font-medium max-w-xl mx-auto leading-relaxed">
                    Sistem koordinasi dinas, pencatatan presensi rapat mandiri, dan penyusunan notulensi kerja Dinas Komunikasi dan Informatika Kabupaten Banyumas.
                </p>
            </div>

            <!-- Features Navigation Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4 md:gap-6 w-full max-w-4xl shrink-0">
                <!-- Card 1: Dashboard / Kelola Pegawai -->
                @if(Auth::check() && !Auth::user()->isAdmin())
                    <a href="{{ route('dashboard') }}" class="hover-card-trigger bg-gradient-to-br from-[#1b3bbb] to-[#0b1554] rounded-2xl md:rounded-[24px] border border-[#1b3bbb]/40 p-4 sm:p-5 md:p-6 flex flex-col justify-between shadow-md relative overflow-hidden group text-white">
                        <div class="space-y-2 md:space-y-3 z-10">
                            <div class="w-9 h-9 md:w-11 md:h-11 bg-white/15 rounded-xl md:rounded-2xl flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base md:text-lg font-extrabold text-white">Dashboard Utama</h3>
                                <p class="text-xs md:text-xs text-blue-100/80 leading-relaxed">Pantau agenda rapat, presensi mandiri, dan statistik kedinasan secara real-time.</p>
                            </div>
                        </div>
                        <div class="mt-3 md:mt-5 flex items-center gap-1.5 text-xs md:text-sm font-bold text-white group-hover:gap-2.5 transition-all duration-300 z-10">
                            <span>Buka Dashboard</span>
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @elseif(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('dashboard') }}" class="hover-card-trigger bg-gradient-to-br from-[#1b3bbb] to-[#0b1554] rounded-2xl md:rounded-[24px] border border-[#1b3bbb]/40 p-4 sm:p-5 md:p-6 flex flex-col justify-between shadow-md relative overflow-hidden group text-white">
                        <div class="space-y-2 md:space-y-3 z-10">
                            <div class="w-9 h-9 md:w-11 md:h-11 bg-white/15 rounded-xl md:rounded-2xl flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base md:text-lg font-extrabold text-white">Dashboard Admin</h3>
                                <p class="text-xs md:text-xs text-blue-100/80 leading-relaxed">Pantau statistik sistem, aktivitas pegawai, dan agenda kedinasan secara real-time.</p>
                            </div>
                        </div>
                        <div class="mt-3 md:mt-5 flex items-center gap-1.5 text-xs md:text-sm font-bold text-white group-hover:gap-2.5 transition-all duration-300 z-10">
                            <span>Buka Dashboard</span>
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endif

                <!-- Card 2: Calendar / Bidang Admin (Clean White Card) -->
                @if(Auth::check() && !Auth::user()->isAdmin())
                    <a href="{{ route('calendar') }}" class="hover-card-trigger bg-white rounded-2xl md:rounded-[24px] border border-slate-200/80 p-4 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md relative overflow-hidden group text-[#09103c]">
                        <div class="space-y-2 md:space-y-3">
                            <div class="w-9 h-9 md:w-11 md:h-11 bg-[#1b3bbb]/5 rounded-xl md:rounded-2xl flex items-center justify-center text-[#1b3bbb] group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base md:text-lg font-extrabold text-[#09103c]">Kalender Rinci</h3>
                                <p class="text-xs md:text-xs text-slate-500 leading-relaxed">Lihat peta agenda bulanan, koordinasi terjadwal, dan detail teknis rapat.</p>
                            </div>
                        </div>
                        <div class="mt-3 md:mt-5 flex items-center gap-1.5 text-xs md:text-sm font-bold text-[#1b3bbb] group-hover:gap-2.5 transition-all duration-300">
                            <span>Buka Kalender</span>
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @elseif(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="hover-card-trigger bg-white rounded-2xl md:rounded-[24px] border border-slate-200/80 p-4 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md relative overflow-hidden group text-[#09103c]">
                        <div class="space-y-2 md:space-y-3">
                            <div class="w-9 h-9 md:w-11 md:h-11 bg-[#1b3bbb]/5 rounded-xl md:rounded-2xl flex items-center justify-center text-[#1b3bbb] group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base md:text-lg font-extrabold text-[#09103c]">Kelola Pegawai</h3>
                                <p class="text-xs md:text-xs text-slate-500 leading-relaxed">Tambah, edit, hapus, reset password, dan kelola akun pegawai.</p>
                            </div>
                        </div>
                        <div class="mt-3 md:mt-5 flex items-center gap-1.5 text-xs md:text-sm font-bold text-[#1b3bbb] group-hover:gap-2.5 transition-all duration-300">
                            <span>Kelola Pengguna</span>
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endif

                <!-- Card 3: Riwayat Rapat / Ganti Password (Clean White Card) -->
                @if(Auth::check() && !Auth::user()->isAdmin())
                    <a href="{{ route('riwayat') }}" class="hover-card-trigger bg-white rounded-2xl md:rounded-[24px] border border-slate-200/80 p-4 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md relative overflow-hidden group text-[#09103c]">
                        <div class="space-y-2 md:space-y-3">
                            <div class="w-9 h-9 md:w-11 md:h-11 bg-[#1b3bbb]/5 rounded-xl md:rounded-2xl flex items-center justify-center text-[#1b3bbb] group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base md:text-lg font-extrabold text-[#09103c]">Riwayat Rapat</h3>
                                <p class="text-xs md:text-xs text-slate-500 leading-relaxed">Akses berkas notulensi PDF/Word dan risalah rapat sebelumnya.</p>
                            </div>
                        </div>
                        <div class="mt-3 md:mt-5 flex items-center gap-1.5 text-xs md:text-sm font-bold text-[#1b3bbb] group-hover:gap-2.5 transition-all duration-300">
                            <span>Buka Riwayat</span>
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @elseif(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('admin.bidang.index') }}" class="hover-card-trigger bg-white rounded-2xl md:rounded-[24px] border border-slate-200/80 p-4 sm:p-5 md:p-6 flex flex-col justify-between shadow-sm hover:shadow-md relative overflow-hidden group text-[#09103c]">
                        <div class="space-y-2 md:space-y-3">
                            <div class="w-9 h-9 md:w-11 md:h-11 bg-[#1b3bbb]/5 rounded-xl md:rounded-2xl flex items-center justify-center text-[#1b3bbb] group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base md:text-lg font-extrabold text-[#09103c]">Kelola Bidang</h3>
                                <p class="text-xs md:text-xs text-slate-500 leading-relaxed">Tambah, perbarui, dan atur struktur bidang/seksi kedinasan.</p>
                            </div>
                        </div>
                        <div class="mt-3 md:mt-5 flex items-center gap-1.5 text-xs md:text-sm font-bold text-[#1b3bbb] group-hover:gap-2.5 transition-all duration-300">
                            <span>Kelola Bidang</span>
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endif
            </div>
        </main>

        <!-- Footer Area -->
        <footer class="text-center text-slate-400 text-[10px] md:text-xs font-medium select-none shrink-0 py-2">
            &copy; 2026 Dinas Komunikasi dan Informatika Kabupaten Banyumas
        </footer>
    </div>
</body>
</html>
