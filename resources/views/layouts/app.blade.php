<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sirena') - Dinkominfo Banyumas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(27, 59, 187, 0.05);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(27, 59, 187, 0.2);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(27, 59, 187, 0.4);
        }

        /* Color Palette Override from Purple to Royal Blue/Navy */
        .text-\[\#2e2552\] { color: #09103c !important; }
        .bg-\[\#2e2552\] { background-color: #1b3bbb !important; }
        .text-\[\#5a508f\] { color: #475569 !important; }
        .text-\[\#8e88dd\] { color: #1b3bbb !important; }
        .text-\[\#bda6ff\] { color: #dbeafe !important; }
        .bg-\[\#8ba0f2\]\/10 { background-color: rgba(27, 59, 187, 0.08) !important; }
        .text-\[\#8ba0f2\] { color: #1b3bbb !important; }
        .bg-\[\#bc8bf2\]\/10 { background-color: rgba(27, 59, 187, 0.08) !important; }
        .text-\[\#bc8bf2\] { color: #1b3bbb !important; }
        .border-\[\#d4d1f5\]\/60 { border-color: rgba(203, 213, 225, 0.6) !important; }
        .border-\[\#d4d1f5\]\/20 { border-color: rgba(203, 213, 225, 0.2) !important; }
        .border-\[\#d4d1f5\]\/30 { border-color: rgba(203, 213, 225, 0.3) !important; }
        .border-\[\#d4d1f5\] { border-color: #cbd5e1 !important; }
        .bg-\[\#f3f2fe\] { background-color: #f1f5f9 !important; }
        .bg-\[\#fcfbff\] { background-color: #f8fafc !important; }
        .bg-\[\#f8f7ff\] { background-color: #f1f5f9 !important; }
        .bg-\[\#bc8bf2\] { background-color: #1b3bbb !important; }
        .bg-\[\#8ba0f2\] { background-color: #3b82f6 !important; }
        .bg-\[\#9f95d9\] { background-color: #64748b !important; }
        .bg-\[\#8e88dd\] { background-color: #1b3bbb !important; }
        .hover\:bg-\[\#8e88dd\]\/20:hover { background-color: rgba(27, 59, 187, 0.1) !important; }
        .hover\:border-\[\#8e88dd\]\/50:hover { border-color: rgba(27, 59, 187, 0.5) !important; }
        .hover\:bg-\[\#f8f7ff\]:hover { background-color: #f1f5f9 !important; }
        .hover\:bg-\[\#3d326a\]:hover { background-color: #0d228c !important; }

        /* Custom SweetAlert2 Premium Modern Glassmorphic Styling */
        .swal2-popup {
            border-radius: 28px !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(203, 213, 225, 0.5) !important;
            box-shadow: 0 25px 50px -12px rgba(9, 16, 60, 0.18) !important;
            padding: 2.25rem 2rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .swal2-title {
            color: #09103c !important;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            line-height: 1.4 !important;
            margin-bottom: 0.75rem !important;
        }
        .swal2-html-container {
            color: #475569 !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            line-height: 1.6 !important;
            margin: 0 !important;
        }
        .swal2-actions {
            margin-top: 1.75rem !important;
            gap: 0.75rem !important;
            width: 100% !important;
            justify-content: center !important;
        }
        .swal2-confirm {
            background: linear-gradient(135deg, #1b3bbb 0%, #3b82f6 100%) !important;
            color: white !important;
            border-radius: 14px !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            padding: 0.65rem 1.75rem !important;
            box-shadow: 0 4px 10px rgba(27, 59, 187, 0.2) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: none !important;
            outline: none !important;
        }
        .swal2-confirm:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 14px rgba(27, 59, 187, 0.35) !important;
        }
        .swal2-confirm:focus {
            box-shadow: 0 0 0 3px rgba(27, 59, 187, 0.3) !important;
        }
        .swal2-cancel {
            background: #f1f5f9 !important;
            color: #475569 !important;
            border-radius: 14px !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            padding: 0.65rem 1.75rem !important;
            transition: all 0.2s ease !important;
            border: 1px solid #cbd5e1 !important;
            outline: none !important;
        }
        .swal2-cancel:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }
        .swal2-icon {
            scale: 0.85 !important;
            margin-bottom: 0.5rem !important;
        }
        .swal2-icon.swal2-warning {
            border-color: #f59e0b !important;
            color: #f59e0b !important;
        }
        .swal2-icon.swal2-question {
            border-color: #3b82f6 !important;
            color: #3b82f6 !important;
        }
        .swal2-icon.swal2-error {
            border-color: #ef4444 !important;
            color: #ef4444 !important;
        }
        .swal2-icon.swal2-success {
            border-color: #10b981 !important;
            color: #10b981 !important;
        }
        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            background-color: #10b981 !important;
        }
        .swal2-icon.swal2-success .swal2-success-ring {
            border: 4px solid rgba(16, 185, 129, 0.2) !important;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
    @yield('styles')
    <!-- SweetAlert2 for beautiful custom alerts and confirms -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Signature Pad library for digital signatures -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <!-- AlpineJS for interactive elements -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-screen text-[#2e2552] overflow-hidden antialiased">

    <!-- Outer Page App Window -->
    <div class="h-screen flex flex-col p-4 md:p-6 gap-6 max-w-[1600px] w-full mx-auto overflow-hidden">
        
        <!-- TOP NAVBAR (Header) -->
        <header class="w-full bg-white/80 backdrop-blur-md rounded-3xl border border-slate-200/60 px-6 py-4 flex items-center justify-between shadow-md relative z-50 text-[#09103c]">
            <!-- Left Logo -->
            <div class="flex items-center gap-3 select-none">
                <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Banyumas" class="h-9 w-auto hover:scale-105 transition-transform duration-300">
                <div class="flex flex-col justify-center">
                    <h1 class="text-[11px] font-extrabold leading-none text-[#09103c] tracking-tight">Dinas Komunikasi dan Informatika</h1>
                    <span class="text-[9px] text-slate-500 font-semibold tracking-tight mt-1 leading-none">Pemerintah Kabupaten Banyumas</span>
                </div>
            </div>



            <!-- Right Profile Drodown -->
            @if(Auth::check())
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
                        'admin' => 'bg-red-50 text-red-700 border-red-100',
                        'sekretaris_master' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'ketua_master' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
                        'sekretaris_bidang' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'ketua_bidang' => 'bg-purple-50 text-purple-700 border-purple-100',
                        'staff' => 'bg-blue-50 text-blue-700 border-blue-100',
                    ];
                @endphp
                <a href="{{ route('profile') }}" class="relative shrink-0 select-none text-[#09103c] flex items-center gap-3 p-1.5 rounded-2xl hover:bg-slate-50 transition-colors">
                    <div class="hidden md:block text-right" style="margin: 0; padding: 0; display: flex; flex-direction: column; justify-content: center; gap: 4px;">
                        <div class="text-xs font-black text-[#09103c]" style="line-height: 1; margin: 0; padding: 0;">{{ Auth::user()->name }}</div>
                        <div style="line-height: 1; margin: 0; padding: 0;">
                            <span class="inline-block text-[8px] font-extrabold px-2 py-0.5 rounded-full border {{ $roleColors[Auth::user()->role] ?? 'bg-slate-100 border-slate-200 text-slate-700' }} uppercase tracking-wider" style="line-height: 1; vertical-align: middle;">
                                {{ $roleLabels[Auth::user()->role] ?? 'User' }}
                            </span>
                        </div>
                        <div class="text-[9px] text-slate-500 font-bold font-mono" style="line-height: 1; margin: 0; padding: 0;">NIP. {{ Auth::user()->nip }}</div>
                    </div>
                    <div class="w-10 h-10 bg-[#1b3bbb]/10 rounded-2xl flex items-center justify-center font-bold text-[#1b3bbb] border border-[#1b3bbb]/20 shadow-sm transition-colors">
                        {{ substr(Auth::user()->name, 0, 2) }}
                    </div>
                </a>
            @endif
        </header>

        <!-- MAIN LAYOUT WRAPPER (Sidebar + Content Panel) -->
        <div class="flex-1 min-h-0 min-w-0 flex flex-col md:flex-row gap-6 items-stretch">
            
            <!-- LEFT NAVBAR (Sidebar) -->
            <aside class="w-full md:w-60 bg-white/80 backdrop-blur-md rounded-3xl border border-slate-200/60 flex flex-col py-6 shrink-0 z-20 shadow-md text-[#09103c]">
                <div class="px-5 mb-4">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-500">Menu Navigasi</span>
                </div>

                <nav class="flex-1 w-full flex flex-col gap-1 px-3">
                    @if(Auth::check() && !Auth::user()->isAdmin())
                        <!-- Dashboard Link -->
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs transition-all duration-200 
                           {{ request()->routeIs('dashboard') ? 'bg-[#1b3bbb] text-white shadow-lg shadow-[#1b3bbb]/20' : 'text-slate-600 hover:bg-[#1b3bbb]/5 hover:text-[#1b3bbb]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Dashboard Utama</span>
                        </a>

                        <!-- Calendar Link -->
                        <a href="{{ route('calendar') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs transition-all duration-200 
                           {{ request()->routeIs('calendar') ? 'bg-[#1b3bbb] text-white shadow-lg shadow-[#1b3bbb]/20' : 'text-slate-600 hover:bg-[#1b3bbb]/5 hover:text-[#1b3bbb]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Kalender Rinci</span>
                        </a>

                        <!-- Riwayat Link -->
                        <a href="{{ route('riwayat') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs transition-all duration-200 
                           {{ request()->routeIs('riwayat') ? 'bg-[#1b3bbb] text-white shadow-lg shadow-[#1b3bbb]/20' : 'text-slate-600 hover:bg-[#1b3bbb]/5 hover:text-[#1b3bbb]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Riwayat Rapat</span>
                        </a>

                        <!-- Profil Link -->
                        <a href="{{ route('profile') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs transition-all duration-200 
                           {{ request()->routeIs('profile') ? 'bg-[#1b3bbb] text-white shadow-lg shadow-[#1b3bbb]/20' : 'text-slate-600 hover:bg-[#1b3bbb]/5 hover:text-[#1b3bbb]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Kelola Profil</span>
                        </a>
                    @endif

                    <!-- Admin Menus -->
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <!-- Dashboard Admin Link -->
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs transition-all duration-200 
                           {{ request()->routeIs('dashboard') ? 'bg-[#1b3bbb] text-white shadow-lg shadow-[#1b3bbb]/20' : 'text-slate-600 hover:bg-[#1b3bbb]/5 hover:text-[#1b3bbb]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Dashboard Admin</span>
                        </a>

                        <!-- Users CRUD -->
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs transition-all duration-200 
                           {{ request()->routeIs('admin.users.index') ? 'bg-[#1b3bbb] text-white shadow-lg shadow-[#1b3bbb]/20' : 'text-slate-600 hover:bg-[#1b3bbb]/5 hover:text-[#1b3bbb]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Kelola Pegawai</span>
                        </a>

                        <!-- Bidang CRUD -->
                        <a href="{{ route('admin.bidang.index') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs transition-all duration-200 
                           {{ request()->routeIs('admin.bidang.index') ? 'bg-[#1b3bbb] text-white shadow-lg shadow-[#1b3bbb]/20' : 'text-slate-600 hover:bg-[#1b3bbb]/5 hover:text-[#1b3bbb]' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span>Kelola Bidang</span>
                        </a>

                    @endif
                </nav>

                <!-- User Logout Footer -->
                @if(Auth::check())
                    <div class="mt-auto w-full px-3">
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 border border-rose-100 transition-all duration-200">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span>Keluar Sistem</span>
                            </button>
                        </form>
                    </div>
                @endif
            </aside>

            <!-- MAIN CONTENT AREA CONTAINER -->
            <main id="main-content" class="flex-1 min-w-0 bg-slate-50 rounded-[32px] p-6 md:p-8 flex flex-col gap-6 shadow-2xl relative {{ request()->routeIs('profile') ? 'overflow-hidden' : 'overflow-auto' }} text-[#090c24] border border-white/10">
                
                <!-- Floating Toast Notifications -->
                @if(session('success') || session('error') || session('warning'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-init="setTimeout(() => show = false, 5000)"
                         class="fixed bottom-6 right-6 z-50 max-w-sm w-full bg-white border rounded-2xl shadow-2xl p-4 flex gap-3 animate-bounce"
                         :class="{
                             'border-emerald-300': '{{ session('success') }}',
                             'border-rose-300': '{{ session('error') }}',
                             'border-amber-300': '{{ session('warning') }}'
                         }">
                        <!-- Icon -->
                        @if(session('success'))
                            <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @elseif(session('error'))
                            <svg class="w-6 h-6 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        @endif
                        <div class="flex-1">
                            <p class="text-xs font-bold text-[#2e2552]">
                                {{ session('success') ? 'Berhasil' : (session('error') ? 'Gagal' : 'Perhatian') }}
                            </p>
                            <p class="text-[11px] text-[#5a508f] mt-0.5">
                                {{ session('success') ?? session('error') ?? session('warning') }}
                            </p>
                        </div>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                <!-- Dynamic Page Content -->
                <div id="pjax-container" class="flex-1 min-w-0 w-full">
                    @yield('content')
                </div>

                <footer class="mt-2 border-t border-[#d4d1f5]/60 pt-3 text-center text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                    &copy; 2026 Dinas Komunikasi dan Informatika Kabupaten Banyumas.
                </footer>
            </main>
        </div>
    </div>

    <!-- Heavy Process Loading Overlay Modal -->
    <div id="heavy-loading-overlay" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-md hidden items-center justify-center p-4 transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center shadow-2xl border border-slate-100 flex flex-col items-center gap-5 transform transition-transform duration-300">
            <div class="relative flex items-center justify-center">
                <div class="w-16 h-16 rounded-full border-4 border-[#1b3bbb]/20 border-t-[#1b3bbb] animate-spin"></div>
                <div class="absolute w-8 h-8 rounded-full bg-[#1b3bbb]/10 animate-ping"></div>
            </div>
            <div class="space-y-2">
                <h3 id="heavy-loading-title" class="text-base font-extrabold text-[#09103c]">Sedang Memproses Data</h3>
                <p id="heavy-loading-message" class="text-xs text-slate-500 leading-relaxed font-medium">Mohon tunggu sejenak, sistem sedang menyelesaikan permintaan Anda...</p>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-gradient-to-r from-[#1b3bbb] to-blue-400 h-full w-full animate-pulse"></div>
            </div>
        </div>
    </div>

    @yield('scripts')
    
    <script>
        window.showHeavyLoading = function(title, message) {
            const overlay = document.getElementById('heavy-loading-overlay');
            if (!overlay) return;
            if (title) document.getElementById('heavy-loading-title').innerText = title;
            if (message) document.getElementById('heavy-loading-message').innerText = message;
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        };

        window.hideHeavyLoading = function() {
            const overlay = document.getElementById('heavy-loading-overlay');
            if (!overlay) return;
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        };

        document.addEventListener('DOMContentLoaded', function() {
            // PJAX Clicks Interceptor
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;
                
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                
                try {
                    const url = new URL(link.href, window.location.href);
                    if (url.origin !== window.location.origin) return;
                    if (link.getAttribute('target') === '_blank') return;
                    if (link.getAttribute('download') !== null) return;
                    
                    // Skip forms, auth, export, download, and data-no-pjax links
                    if (link.closest('form') || url.pathname.includes('/logout') || url.pathname.includes('/export') || url.pathname.includes('/download')) return;
                    if (link.hasAttribute('data-no-pjax')) return;

                    e.preventDefault();
                    loadPage(url.href);
                } catch(err) {
                    // Fallback on error
                }
            });

            function loadPage(url) {
                // Close profile menu on PJAX navigation
                window.dispatchEvent(new CustomEvent('close-profile-menu'));

                // Premium linear loader
                let loader = document.getElementById('pjax-loader');
                if (!loader) {
                    loader = document.createElement('div');
                    loader.id = 'pjax-loader';
                    loader.style.position = 'fixed';
                    loader.style.top = '0';
                    loader.style.left = '0';
                    loader.style.height = '3px';
                    loader.style.backgroundColor = '#8e88dd';
                    loader.style.zIndex = '9999';
                    loader.style.width = '0%';
                    loader.style.transition = 'width 0.4s ease';
                    document.body.appendChild(loader);
                }
                loader.style.width = '15%';
                setTimeout(() => { if(loader) loader.style.width = '75%'; }, 100);

                fetch(url)
                    .then(res => res.text())
                    .then(html => {
                        if (loader) {
                            loader.style.width = '100%';
                            setTimeout(() => loader.remove(), 150);
                        }
                        
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const currentContainer = document.getElementById('pjax-container');
                        const newContainer = doc.getElementById('pjax-container');
                        
                        if (currentContainer && newContainer) {
                            currentContainer.innerHTML = newContainer.innerHTML;

                            // Execute script tags inside the new container for PJAX compatibility
                            const scripts = currentContainer.querySelectorAll('script');
                            scripts.forEach(oldScript => {
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                                document.body.appendChild(newScript);
                                newScript.remove();
                            });

                            document.title = doc.title;
                            history.pushState({ url: url }, doc.title, url);
                            
                            // Synchronize the sidebar active status dynamically
                            const currentNav = document.querySelector('aside nav');
                            const newNav = doc.querySelector('aside nav');
                            if (currentNav && newNav) {
                                currentNav.innerHTML = newNav.innerHTML;
                            }
                            
                            // Synchronize header breadcrumb
                            const currentTitle = document.querySelector('.hidden.sm\\:flex.items-center.gap-2.text-xs.font-bold');
                            const newTitle = doc.querySelector('.hidden.sm\\:flex.items-center.gap-2.text-xs.font-bold');
                            if (currentTitle && newTitle) {
                                currentTitle.innerHTML = newTitle.innerHTML;
                            }
                            
                            // Dynamic main container scroll state synchronization (PJAX)
                            const mainEl = document.getElementById('main-content');
                            if (mainEl) {
                                if (url.includes('/profile')) {
                                    mainEl.classList.remove('overflow-auto');
                                    mainEl.classList.add('overflow-hidden');
                                } else {
                                    mainEl.classList.remove('overflow-hidden');
                                    mainEl.classList.add('overflow-auto');
                                }
                            }

                            // Force close all Alpine modals/dropdowns on page transition
                            if (window.Alpine) {
                                document.querySelectorAll('[x-data]').forEach(el => {
                                    try {
                                        const data = window.Alpine.$data(el);
                                        if (data && 'openModal' in data) {
                                            data.openModal = false;
                                        }
                                    } catch(err) {}
                                });
                            }
                            
                            // Emit a custom complete event in case other libraries/scripts need to re-bind
                            window.dispatchEvent(new CustomEvent('pjax:complete'));
                        } else {
                            window.location.href = url;
                        }
                    })
                    .catch(err => {
                        window.location.href = url;
                    });
            }

            // Global Form Submit Interceptor with Double-Click Prevention & Loading States
            document.addEventListener('submit', function(e) {
                const form = e.target;
                
                // HTML5 validity check
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    return;
                }

                // Handle forms with data-confirm attributes (SweetAlert2)
                if (form.hasAttribute('data-confirm')) {
                    e.preventDefault();
                    const message = form.getAttribute('data-confirm');
                    const confirmBtnText = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
                    const heavyTitle = form.getAttribute('data-heavy-title');
                    
                    Swal.fire({
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Batal',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (heavyTitle) {
                                window.showHeavyLoading(heavyTitle, form.getAttribute('data-heavy-msg') || 'Mohon tunggu, proses sedang berjalan...');
                            } else {
                                Swal.fire({
                                    title: 'Memproses...',
                                    text: 'Mohon tunggu sejenak...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            }
                            form.removeAttribute('data-confirm');
                            const btn = form.querySelector('button[type="submit"], input[type="submit"]');
                            if (btn) {
                                btn.style.pointerEvents = 'none';
                                btn.classList.add('opacity-75', 'cursor-not-allowed');
                            }
                            form.submit();
                        }
                    });
                    return;
                }

                if (form.hasAttribute('data-no-loading')) return;

                // Double click prevention
                if (form.dataset.submitting === 'true') {
                    e.preventDefault();
                    return;
                }
                form.dataset.submitting = 'true';

                // Heavy process modal trigger
                if (form.hasAttribute('data-heavy-loading')) {
                    const title = form.getAttribute('data-heavy-title') || 'Sedang Memproses...';
                    const message = form.getAttribute('data-heavy-msg') || 'Mohon tidak menutup atau memuat ulang halaman selama proses berjalan.';
                    window.showHeavyLoading(title, message);
                } else {
                    // Show linear top loader for any standard form submission
                    let loader = document.getElementById('pjax-loader');
                    if (!loader) {
                        loader = document.createElement('div');
                        loader.id = 'pjax-loader';
                        loader.style.position = 'fixed';
                        loader.style.top = '0';
                        loader.style.left = '0';
                        loader.style.height = '3px';
                        loader.style.backgroundColor = '#1b3bbb';
                        loader.style.zIndex = '9999';
                        loader.style.width = '0%';
                        loader.style.transition = 'width 0.4s ease';
                        document.body.appendChild(loader);
                    }
                    loader.style.width = '30%';
                    setTimeout(() => { if (loader) loader.style.width = '80%'; }, 150);
                }

                // Submit button loading state
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"], button:not([type="button"])');
                if (submitBtn) {
                    submitBtn.style.pointerEvents = 'none';
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

                    const customText = form.getAttribute('data-loading-text') || submitBtn.getAttribute('data-loading-text');
                    const textToUse = customText || 'Memproses...';
                    const spinnerSvg = `<svg class="w-4 h-4 mr-2 animate-spin text-current inline shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

                    submitBtn.innerHTML = `<span class="inline-flex items-center justify-center">${spinnerSvg}<span>${textToUse}</span></span>`;

                    // Disable button on next tick so native browser form submit isn't aborted
                    setTimeout(() => {
                        if (submitBtn) submitBtn.disabled = true;
                    }, 20);

                    // Automatic safety reset after 10 seconds if form submission stays on same page
                    setTimeout(() => {
                        if (document.body.contains(submitBtn) && form.dataset.submitting === 'true') {
                            form.dataset.submitting = 'false';
                            submitBtn.disabled = false;
                            submitBtn.style.pointerEvents = '';
                            submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                        }
                    }, 10000);
                }
            });

            window.addEventListener('popstate', function(e) {
                if (e.state && e.state.url) {
                    loadPage(e.state.url);
                } else {
                    window.location.reload();
                }
            });
        });
    </script>
</body>
</html>
