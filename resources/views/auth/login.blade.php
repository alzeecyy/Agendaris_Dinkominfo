<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SiRENA Dinkominfo Banyumas</title>
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
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="min-h-screen bg-[#eef2ff] text-[#2e2552] flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 relative overflow-x-hidden antialiased select-none">
    
    <!-- Ambient Background Glows -->
    <div class="fixed -top-32 -left-32 w-[450px] h-[450px] bg-[#1b3bbb]/15 rounded-full filter blur-[120px] pointer-events-none"></div>
    <div class="fixed -bottom-32 -right-32 w-[450px] h-[450px] bg-[#8e88dd]/20 rounded-full filter blur-[140px] pointer-events-none"></div>

    <div class="w-full max-w-5xl my-auto relative z-10 flex flex-col items-center">
        <!-- Main Container Card (Split Layout: Left Gradient Hero, Right Clean White Form) -->
        <div x-data="{ showPassword: false }" class="w-full glass-card border border-white/90 rounded-3xl md:rounded-[36px] shadow-2xl shadow-[#09103c]/10 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[580px] relative">
            
            <!-- ================= LEFT PANEL: GRADIENT NAVY BRANDING HERO ================= -->
            <div class="lg:col-span-6 bg-gradient-to-br from-[#09103c] via-[#142370] to-[#1b3bbb] text-white p-7 sm:p-9 md:p-10 flex flex-col justify-between relative overflow-hidden">
                
                <!-- White Organic Wave Divider (Slim elegant curve at panel edge) -->
                <div class="hidden lg:block absolute top-0 bottom-0 -right-2 w-16 z-20 pointer-events-none text-white">
                    <svg class="h-full w-full" viewBox="0 0 100 1000" preserveAspectRatio="none" fill="currentColor">
                        <path d="M100,0 L100,1000 L60,1000 C90,850 30,700 60,500 C90,300 30,150 60,0 Z"></path>
                    </svg>
                </div>

                <!-- Decorative Subtle Background Pattern -->
                <div class="absolute inset-0 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:24px_24px] opacity-10 pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-indigo-500/20 rounded-full filter blur-2xl pointer-events-none"></div>
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-[#8e88dd]/30 rounded-full filter blur-2xl pointer-events-none"></div>

                <!-- Top Brand Header -->
                <div class="relative z-10 space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 glass-panel border border-white/20 p-2.5 flex items-center justify-center shadow-lg shrink-0">
                            <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Kabupaten Banyumas" class="w-full h-full object-contain filter drop-shadow-md">
                        </div>
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-md bg-white/10 text-indigo-200 text-[10px] font-bold uppercase tracking-[0.15em] border border-white/10">Dinkominfo Banyumas</span>
                            <h1 class="text-3xl font-black text-white tracking-tight mt-1" style="text-shadow: 0 2px 12px rgba(27,59,187,0.4);">SiRENA</h1>
                        </div>
                    </div>

                    <div class="pt-1 max-w-[300px]">
                        <h3 class="text-lg sm:text-xl font-extrabold text-white/95 leading-snug tracking-tight">
                            Sistem Informasi Agenda & Notulensi Rapat
                        </h3>
                        <p class="text-[12.5px] text-indigo-100/70 font-medium leading-relaxed mt-3">
                            Platform terintegrasi untuk pengelolaan agenda dinas, presensi digital, dan penyusunan notulensi rapat berbasis AI.
                        </p>
                    </div>
                </div>

                <!-- Middle Feature Pills -->
                <div class="relative z-10 py-6 my-auto space-y-3 max-w-[320px]">
                    <div class="glass-panel border border-white/15 rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm hover:bg-white/15 transition-all">
                        <div class="w-9 h-9 rounded-xl bg-amber-400/20 text-amber-300 border border-amber-300/30 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-white">Agenda & Kalender Kegiatan</h4>
                            <p class="text-[10.5px] text-indigo-200 truncate">Monitoring agenda dinas secara real-time</p>
                        </div>
                    </div>

                    <div class="glass-panel border border-white/15 rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm hover:bg-white/15 transition-all">
                        <div class="w-9 h-9 rounded-xl bg-emerald-400/20 text-emerald-300 border border-emerald-300/30 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-white">Presensi Digital Mandiri</h4>
                            <p class="text-[10.5px] text-indigo-200 truncate">Pengisian absensi & tanda tangan digital</p>
                        </div>
                    </div>

                    <div class="glass-panel border border-white/15 rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm hover:bg-white/15 transition-all">
                        <div class="w-9 h-9 rounded-xl bg-sky-400/20 text-sky-300 border border-sky-300/30 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-white">Notulensi Otomatis AI</h4>
                            <p class="text-[10.5px] text-indigo-200 truncate">Transkripsi audio & ringkasan hasil rapat</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= RIGHT PANEL: PREMIUM LOGIN FORM ================= -->
            <div class="lg:col-span-6 bg-gradient-to-br from-white via-[#f8f9ff] to-[#eef2ff] p-7 sm:p-10 md:p-12 lg:pl-10 flex flex-col justify-center relative z-10 overflow-hidden">
                
                <!-- Decorative Accent Orb -->
                <div class="absolute -bottom-16 -right-16 w-48 h-48 bg-[#1b3bbb]/5 rounded-full filter blur-3xl pointer-events-none"></div>
                <div class="absolute -top-16 -left-16 w-40 h-40 bg-indigo-400/5 rounded-full filter blur-3xl pointer-events-none"></div>
                
                <div class="space-y-6 my-auto relative z-10 max-w-md mx-auto w-full">
                    <!-- Form Header Greeting -->
                    <div class="space-y-3 pb-5">
                        <div>
                            <span class="inline-block px-3.5 py-1.5 rounded-full bg-gradient-to-r from-[#1b3bbb]/10 to-indigo-500/10 text-[#1b3bbb] text-[10.5px] font-black uppercase tracking-wider border border-[#1b3bbb]/15">
                                PORTAL SIRENA
                            </span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-[#09103c] tracking-tight leading-tight">Selamat Datang<br>Kembali</h2>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed">
                            Masuk dengan NIP dan kata sandi untuk mengakses sistem SiRENA.
                        </p>
                    </div>

                    <!-- Thin Divider -->
                    <div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

                    <!-- Form Element -->
                    <form action="{{ route('login') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Error Alert -->
                        @if($errors->any())
                            <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl p-4 text-xs space-y-1.5 shadow-2xs">
                                <div class="font-bold flex items-center gap-1.5 text-rose-800">
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
                            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-4 text-xs flex items-start gap-2.5 shadow-2xs">
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
                        <div class="space-y-2">
                            <label for="nip" class="block text-[11px] font-black uppercase tracking-wider text-slate-600">
                                Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-[#8e88dd] group-focus-within:text-[#1b3bbb] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="nip" id="nip" value="{{ old('nip') }}" placeholder="Masukkan 18 digit NIP tanpa spasi" required autofocus
                                       class="w-full pl-12 pr-4 py-3.5 bg-white hover:bg-slate-50/50 border-2 border-slate-200/80 rounded-xl text-slate-900 text-sm font-semibold placeholder-slate-400 focus:bg-white focus:border-[#1b3bbb] focus:ring-4 focus:ring-[#1b3bbb]/10 transition-all duration-200 shadow-sm">
                            </div>
                        </div>

                        <!-- Field 2: Password Input with Toggle Eye Icon -->
                        <div class="space-y-2">
                            <label for="password" class="block text-[11px] font-black uppercase tracking-wider text-slate-600">
                                Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-[#8e88dd] group-focus-within:text-[#1b3bbb] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" placeholder="Masukkan kata sandi akun Anda" required
                                       class="w-full pl-12 pr-12 py-3.5 bg-white hover:bg-slate-50/50 border-2 border-slate-200/80 rounded-xl text-slate-900 text-sm font-semibold placeholder-slate-400 focus:bg-white focus:border-[#1b3bbb] focus:ring-4 focus:ring-[#1b3bbb]/10 transition-all duration-200 shadow-sm">
                                
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
                                class="w-full py-4 bg-gradient-to-r from-[#1b3bbb] to-[#2a4fd4] hover:from-[#09103c] hover:to-[#1b3bbb] active:scale-[0.98] text-white font-extrabold text-xs uppercase tracking-widest rounded-xl transition-all duration-300 shadow-lg shadow-[#1b3bbb]/25 hover:shadow-xl hover:shadow-[#1b3bbb]/30 flex items-center justify-center gap-2.5 cursor-pointer pt-4">
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
        <div class="mt-6 text-center text-slate-500 text-xs font-semibold tracking-wide">
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
                        btn.innerHTML = `<span class="inline-flex items-center justify-center">${spinnerSvg}<span>Memuat Hak Akses SiRENA...</span></span>`;
                    }
                });
            }
        });
    </script>
</body>
</html>
