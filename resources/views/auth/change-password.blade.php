<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kata Sandi Baru - Agendaris</title>
    <!-- Favicon / Logo Resmi -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banyumas-crest.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-8 px-4 relative overflow-hidden">
    <!-- Decorative background blurred shapes -->
    <div class="absolute top-10 left-10 w-72 h-72 bg-blue-300/20 rounded-full filter blur-3xl"></div>
    <div class="absolute bottom-10 right-10 w-80 h-80 bg-indigo-300/10 rounded-full filter blur-3xl"></div>

    <div class="w-full max-w-md z-10 space-y-4">
        <!-- Logo & Header -->
        <div class="text-center flex flex-col items-center justify-center">
            <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Kabupaten Banyumas" class="w-16 h-16 object-contain hover:scale-105 transition-transform duration-300">
            <h1 class="text-xl font-black text-[#09103c] tracking-widest mt-3">PENGAMANAN AKUN</h1>
            <p class="text-slate-500 text-xs font-semibold mt-1">Pembaruan Kata Sandi Akun Anda</p>
        </div>

        <!-- Premium White Card -->
        <div class="bg-white rounded-[32px] p-6 shadow-2xl relative border border-slate-100">
            
            @if(Auth::check() && Auth::user()->must_change_password)
                <div class="mb-5 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5 text-amber-900">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Pemberitahuan Wajib!
                    </p>
                    <p class="leading-relaxed">Ini adalah login pertama Anda. Demi keamanan akun, Anda diwajibkan mengganti kata sandi bawaan administrator dengan kata sandi pribadi sebelum mengakses menu.</p>
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                @csrf

                @if($errors->any())
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl p-4 text-xs space-y-1">
                        @foreach($errors->all() as $error)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- New Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#09103c]">Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2v12a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2h6zm-6 1h6v4H9V8zm1 8v2m4-2v2"></path>
                            </svg>
                        </span>
                        <input type="password" name="password" id="password" placeholder="Masukkan kata sandi baru" required autofocus
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-[#09103c] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] focus:border-transparent transition-all duration-200 text-sm">
                    </div>
                    <p class="text-[10px] text-slate-400 pl-1">Minimal 6 karakter.</p>
                </div>

                <!-- Password Confirmation Input -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-[#09103c]">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </span>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Masukkan ulang kata sandi baru" required
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-[#09103c] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] focus:border-transparent transition-all duration-200 text-sm">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3.5 bg-[#1b3bbb] hover:bg-[#0d228c] active:scale-[0.98] text-white font-bold text-xs uppercase tracking-wider rounded-2xl transition-all duration-200 shadow-lg shadow-[#1b3bbb]/20 flex items-center justify-center">
                    Ubah Kata Sandi Sekarang
                </button>
            </form>

            @if(Auth::check() && !Auth::user()->must_change_password)
                <div class="text-center mt-5">
                    <a href="{{ route('profile') }}" class="text-xs font-bold text-slate-500 hover:text-[#1b3bbb] transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Batal & Kembali ke Profil
                    </a>
                </div>
            @endif
        </div>

        <!-- Footer copyright -->
        <div class="text-center text-slate-500 text-[10px] font-semibold">
            &copy; 2026 Dinas Komunikasi dan Informatika Kabupaten Banyumas.
        </div>
    </div>

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
                        btn.innerHTML = `<span class="inline-flex items-center justify-center">${spinnerSvg}<span>Memperbarui Kata Sandi...</span></span>`;
                    }
                });
            }
        });
    </script>
</body>
</html>
