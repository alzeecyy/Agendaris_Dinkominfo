<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIRENA Dinkominfo Banyumas</title>
    <!-- Favicon / Logo Resmi -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banyumas-crest.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- AlpineJS for interactive elements -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #09103c !important;
            background-image: 
                linear-gradient(135deg, rgba(27, 59, 187, 0.82) 0%, rgba(16, 36, 128, 0.90) 50%, rgba(9, 16, 60, 0.95) 100%),
                url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22100%22%20height%3D%22100%22%20viewBox%3D%220%200%20100%20100%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.25%22%3E%3Cpath%20d%3D%22M50%2C10%20Q50%2C50%2010%2C50%20Q50%2C50%2050%2C90%20Q50%2C50%2090%2C50%20Q50%2C50%2050%2C10%20Z%22%2F%3E%3Cpath%20d%3D%22M0%2C-40%20Q0%2C0%20-40%2C0%20Q0%2C0%200%2C40%20Q0%2C0%2040%2C0%20Q0%2C0%200%2C-40%20Z%22%2F%3E%3Cpath%20d%3D%22M100%2C-40%20Q100%2C0%2060%2C0%20Q100%2C0%20100%2C40%20Q100%2C0%20140%2C0%20Q100%2C0%20100%2C-40%20Z%22%2F%3E%3Cpath%20d%3D%22M0%2C60%20Q0%2C100%20-40%2C100%20Q0%2C100%200%2C140%20Q0%2C100%2040%2C100%20Q0%2C100%200%2C60%20Z%22%2F%3E%3Cpath%20d%3D%22M100%2C60%20Q100%2C100%2060%2C100%20Q100%2C100%20100%2C140%20Q100%2C100%20140%2C100%20Q100%2C100%20100%2C60%20Z%22%2F%3E%3Cpath%20d%3D%22M50%2C0%20C22.4%2C0%200%2C22.4%200%2C50%20C0%2C77.6%2022.4%2C100%2050%2C100%20C77.6%2C100%20100%2C77.6%20100%2C50%20C100%2C22.4%2077.6%200%2050%2C0%20Z%20M50%2C12%20C71%2C12%2088%2C29%2088%2C50%20C88%2C71%2071%2C88%2050%2C88%20C29%2C88%2012%2C71%2012%2C50%20C12%2C29%2029%2C12%2050%2C12%20Z%22%20fill-opacity%3D%220.15%22%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E") !important;
            background-repeat: no-repeat, repeat !important;
            background-size: cover, 180px 180px !important;
            background-position: center center, center center !important;
            background-attachment: fixed !important;
        }
        .glass-card {
            background: #ffffff !important;
            box-shadow: 0 25px 50px -12px rgba(9, 16, 60, 0.25) !important;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="min-h-screen bg-[#eef2ff] text-[#2e2552] flex flex-col items-center justify-center p-3.5 sm:p-6 md:p-8 relative overflow-x-hidden antialiased select-none">
    
    <!-- Ambient Background Glows -->
    <div class="fixed -top-32 -left-32 w-[350px] sm:w-[450px] h-[350px] sm:h-[450px] bg-[#1b3bbb]/15 rounded-full filter blur-[100px] sm:blur-[120px] pointer-events-none"></div>
    <div class="fixed -bottom-32 -right-32 w-[350px] sm:w-[450px] h-[350px] sm:h-[450px] bg-[#8e88dd]/20 rounded-full filter blur-[110px] sm:blur-[140px] pointer-events-none"></div>

    <div class="w-full max-w-5xl my-auto relative z-10 flex flex-col items-center">
        <!-- Main Container Card (Split Layout: Left Gradient Hero, Right Clean White Form) -->
        <div x-data="{ showPassword: false }" class="w-full bg-white border border-white/80 rounded-2xl sm:rounded-3xl md:rounded-[32px] shadow-2xl shadow-[#09103c]/25 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-0 lg:min-h-[580px] relative">
            
            <!-- ================= LEFT PANEL: EXTRA WIDE GRADIENT NAVY BRANDING HERO (Desktop Only) ================= -->
            <div class="hidden lg:flex lg:col-span-7 bg-gradient-to-br from-[#09103c] via-[#102480] to-[#1b3bbb] text-white p-8 lg:p-12 lg:pr-16 flex-col justify-between relative overflow-hidden">
                
                <!-- High-End Ultra-Fluid Organic Wave Divider (Single Cubic Bezier per Layer - 100% Silky Smooth) -->
                <div class="absolute top-0 bottom-0 -right-[1px] w-36 lg:w-48 z-20 pointer-events-none hidden lg:block">
                    <svg class="h-full w-full" viewBox="0 0 140 1000" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="wave-glow" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#1b3bbb" stop-opacity="0.10" />
                            </linearGradient>
                        </defs>

                        <!-- Layer 1: Outer Soft Blue Glow -->
                        <path d="M140,0 L140,1000 L10,1000 C130,650 -10,350 70,0 Z" fill="url(#wave-glow)" />

                        <!-- Layer 2: Soft Translucent White Layer -->
                        <path d="M140,0 L140,1000 L35,1000 C135,650 5,350 85,0 Z" fill="rgba(255,255,255,0.22)" />

                        <!-- Layer 3: Soft Highlight White Layer -->
                        <path d="M140,0 L140,1000 L60,1000 C140,650 20,350 100,0 Z" fill="rgba(255,255,255,0.45)" />

                        <!-- Layer 4: Foreground Solid Pure White Main Wave -->
                        <path d="M140,0 L140,1000 L85,1000 C145,650 35,350 115,0 Z" fill="#ffffff" />
                    </svg>
                </div>

                <!-- Decorative Subtle Background Pattern -->
                <div class="absolute inset-0 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:24px_24px] opacity-10 pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-indigo-500/20 rounded-full filter blur-2xl pointer-events-none"></div>
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-[#8e88dd]/30 rounded-full filter blur-2xl pointer-events-none"></div>

                <!-- Top Brand Header -->
                <div class="relative z-10 space-y-6">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Kabupaten Banyumas" class="w-12 h-12 object-contain shrink-0 filter drop-shadow-md">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-white/10 border border-white/20 rounded-full text-indigo-200 text-[9.5px] font-extrabold uppercase tracking-widest">
                                Dinkominfo Banyumas
                            </span>
                            <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight leading-none mt-1" style="text-shadow: 0 2px 10px rgba(27,59,187,0.4);">SIRENA</h1>
                        </div>
                    </div>

                    <div class="pt-1 max-w-[380px] space-y-2">
                        <h3 class="text-lg lg:text-xl font-black text-white leading-snug tracking-tight [text-wrap:balance]">
                            Sistem Informasi Agenda & Notulensi Rapat
                        </h3>
                        <p class="text-xs lg:text-sm text-blue-100/80 font-medium leading-relaxed [text-wrap:balance]">
                            Platform terintegrasi untuk pengelolaan agenda dinas, presensi digital, dan penyusunan notulensi rapat berbasis AI.
                        </p>
                    </div>
                </div>

                <!-- Middle Feature Pills -->
                <div class="relative z-10 py-4 my-auto space-y-3.5 max-w-[380px]">
                    <div class="bg-gradient-to-r from-white/12 to-white/5 border border-white/20 rounded-2xl p-3.5 sm:p-4 flex items-center gap-3.5 shadow-sm hover:from-white/20 hover:to-white/10 hover:border-white/30 transition-all duration-300 group backdrop-blur-md">
                        <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-amber-300 border border-amber-300/30 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-white tracking-wide">Agenda & Kalender Kegiatan</h4>
                            <p class="text-[11px] text-indigo-200/90 font-medium truncate mt-0.5">Monitoring agenda dinas secara real-time</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-white/12 to-white/5 border border-white/20 rounded-2xl p-3.5 sm:p-4 flex items-center gap-3.5 shadow-sm hover:from-white/20 hover:to-white/10 hover:border-white/30 transition-all duration-300 group backdrop-blur-md">
                        <div class="w-10 h-10 rounded-xl bg-emerald-400/20 text-emerald-300 border border-emerald-300/30 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-white tracking-wide">Presensi Digital Mandiri</h4>
                            <p class="text-[11px] text-indigo-200/90 font-medium truncate mt-0.5">Pengisian absensi & tanda tangan digital</p>
                        </div>
                    </div>

<<<<<<< HEAD
                    <div class="glass-panel border border-white/15 rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm hover:bg-white/15 transition-all">
                        <div class="w-9 h-9 rounded-xl bg-sky-400/20 text-sky-300 border border-sky-300/30 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 003-3V4.5a3 3 0 10-6 0v8.25a3 3 0 003 3z"></path>
=======
                    <div class="bg-gradient-to-r from-white/12 to-white/5 border border-white/20 rounded-2xl p-3.5 sm:p-4 flex items-center gap-3.5 shadow-sm hover:from-white/20 hover:to-white/10 hover:border-white/30 transition-all duration-300 group backdrop-blur-md">
                        <div class="w-10 h-10 rounded-xl bg-sky-400/20 text-sky-300 border border-sky-300/30 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path>
>>>>>>> 76c68e8 (style: pembaruan latar belakang gradasi biru batik kawung, kartu solid putih, dan tata letak login gelombang 3D)
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-white tracking-wide">Notulensi Otomatis AI</h4>
                            <p class="text-[11px] text-indigo-200/90 font-medium truncate mt-0.5">Transkripsi audio & ringkasan hasil rapat</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= RIGHT PANEL: PREMIUM SOLID WHITE LOGIN FORM ================= -->
            <div class="col-span-1 lg:col-span-5 bg-white p-6 sm:p-10 md:p-12 flex flex-col justify-center relative z-10">
                
                <div class="space-y-6 my-auto max-w-md mx-auto w-full">

                    <!-- Form Header Greeting -->
                    <div class="space-y-2">
                        <!-- Mobile Brand Header (Clean White Theme) -->
                        <div class="lg:hidden flex items-center gap-3 pb-2 border-b border-slate-100 mb-2">
                            <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Kabupaten Banyumas" class="w-10 h-10 object-contain shrink-0">
                            <div>
                                <span class="inline-block text-[#1b3bbb] text-[9.5px] font-black uppercase tracking-wider">Dinkominfo Banyumas</span>
                                <h1 class="text-xl font-black text-[#09103c] tracking-tight leading-none mt-0.5">SIRENA</h1>
                            </div>
                        </div>

                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#1b3bbb]/10 text-[#1b3bbb] text-[10px] font-extrabold uppercase tracking-widest rounded-full border border-[#1b3bbb]/20">
                                PORTAL SIRENA
                            </span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#09103c] tracking-tight leading-tight">Selamat Datang Kembali</h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium leading-relaxed">
                            Masuk dengan NIP dan kata sandi untuk mengakses sistem SIRENA.
                        </p>
                    </div>

                    <!-- Form Element -->
                    <form action="{{ route('login') }}" method="POST" class="space-y-5 pt-1">
                        @csrf

                        <!-- Error Alert -->
                        @if($errors->any())
                            <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl p-4 text-xs space-y-1.5 shadow-sm">
                                <div class="font-bold flex items-center gap-2 text-rose-800">
                                    <svg class="w-4.5 h-4.5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Gagal Masuk Ke Sistem</span>
                                </div>
                                @foreach($errors->all() as $error)
                                    <p class="pl-6 text-rose-600 font-medium leading-relaxed">• {{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <!-- Warning Alert -->
                        @if(session('warning'))
                            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-4 text-xs flex items-start gap-3 shadow-sm">
                                <svg class="w-4.5 h-4.5 shrink-0 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div class="space-y-0.5">
                                    <h4 class="font-bold text-amber-900">Pemberitahuan</h4>
                                    <p class="text-amber-700 font-medium leading-relaxed">{{ session('warning') }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Field 1: NIP Input -->
                        <div class="space-y-1.5">
                            <label for="nip" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                                Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400 group-focus-within:text-[#1b3bbb] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="nip" id="nip" value="{{ old('nip') }}" placeholder="Masukkan 18 digit NIP tanpa spasi" required autofocus
                                       class="w-full pl-12 pr-4 py-3.5 bg-slate-50 hover:bg-slate-100/60 border border-slate-200 rounded-2xl text-slate-900 text-sm font-semibold placeholder-slate-400 focus:bg-white focus:border-[#1b3bbb] focus:ring-4 focus:ring-[#1b3bbb]/15 transition-all duration-200 shadow-xs">
                            </div>
                        </div>

                        <!-- Field 2: Password Input with Toggle Eye Icon -->
                        <div class="space-y-1.5">
                            <label for="password" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                                Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400 group-focus-within:text-[#1b3bbb] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" placeholder="Masukkan kata sandi akun Anda" required
                                       class="w-full pl-12 pr-12 py-3.5 bg-slate-50 hover:bg-slate-100/60 border border-slate-200 rounded-2xl text-slate-900 text-sm font-semibold placeholder-slate-400 focus:bg-white focus:border-[#1b3bbb] focus:ring-4 focus:ring-[#1b3bbb]/15 transition-all duration-200 shadow-xs">
                                
                                <!-- Eye Icon Button -->
                                <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-[#1b3bbb] transition-colors cursor-pointer" title="Tampilkan/Sembunyikan Kata Sandi">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.03 10.03 0 012.122-.32c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                                class="w-full py-3.5 sm:py-4 bg-gradient-to-r from-[#1b3bbb] to-[#0b1554] hover:from-[#09103c] hover:to-[#1b3bbb] active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-widest rounded-2xl transition-all duration-300 shadow-md shadow-[#1b3bbb]/30 hover:shadow-lg flex items-center justify-center gap-2.5 cursor-pointer mt-2">
                            <span>MASUK KE SIRENA</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </form>

                </div>
            </div>
        </div>

        <!-- ================= EXTERNAL FOOTER ================= -->
        <div class="mt-4 sm:mt-6 text-center text-slate-500 text-[10.5px] sm:text-xs font-semibold tracking-wide">
            &copy; 2026 Dinas Komunikasi dan Informatika Kabupaten Banyumas.
        </div>
    </div>

    <!-- Form Submit Loading Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (typeof form.checkValidity === 'function' && !form.checkValidity()) return;
                    if (form.dataset.submitting === 'true') {
                        e.preventDefault();
                        return;
                    }
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) {
                        form.dataset.submitting = 'true';
                        btn.disabled = true;
                        btn.classList.add('opacity-75', 'cursor-not-allowed');
                        const spinnerSvg = `<svg class="w-4 h-4 mr-2 animate-spin text-current shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                        btn.innerHTML = `<span class="inline-flex items-center justify-center">${spinnerSvg}<span>Memuat Hak Akses SIRENA...</span></span>`;
                    }
                });
            }
        });
    </script>
</body>
</html>
