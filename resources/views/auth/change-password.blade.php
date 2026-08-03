<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kata Sandi Baru - Agendaris Dinkominfo</title>
    <!-- Favicon / Logo Resmi -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banyumas-crest.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
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

        /* Royal Blue & Dark Navy Horizontal Mirror Sliding Gradient */
        button[type="submit"],
        .btn-gradient-mirror {
            background-image: linear-gradient(to right, #1b3bbb 0%, #09103c 50%, #1b3bbb 100%) !important;
            background-size: 200% 100% !important;
            background-position: left center !important;
            transition: background-position 0.4s ease-in-out, transform 0.2s ease, box-shadow 0.2s ease !important;
        }
        button[type="submit"]:hover,
        .btn-gradient-mirror:hover {
            background-position: right center !important;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-8 px-4 relative overflow-hidden text-slate-800 antialiased select-none">
    <!-- Ambient Background Glows -->
    <div class="fixed -top-32 -left-32 w-[350px] sm:w-[450px] h-[350px] sm:h-[450px] bg-[#1b3bbb]/15 rounded-full filter blur-[100px] sm:blur-[120px] pointer-events-none"></div>
    <div class="fixed -bottom-32 -right-32 w-[350px] sm:w-[450px] h-[350px] sm:h-[450px] bg-[#8e88dd]/20 rounded-full filter blur-[110px] sm:blur-[140px] pointer-events-none"></div>

    <div class="w-full max-w-md z-10 space-y-4">
        <!-- Logo & Header -->
        <div class="text-center flex flex-col items-center justify-center">
            <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Kabupaten Banyumas" class="w-16 h-16 object-contain hover:scale-105 transition-transform duration-300 filter drop-shadow-md">
            <h1 class="text-xl font-extrabold text-white tracking-wide mt-3">Pengamanan Akun</h1>
            <p class="text-blue-100/80 text-xs font-semibold mt-1">Pembaruan Kata Sandi Akun SIRENA</p>
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
                    <a href="{{ route('profile') }}" class="text-xs font-bold text-[#5a508f] hover:text-[#1b3bbb] transition-colors inline-flex items-center gap-2 group">
                        <svg class="w-4 h-4 shrink-0 text-[#5a508f] group-hover:text-[#1b3bbb] group-hover:-translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path>
                        </svg>
                        <span>Batal & Kembali ke Profil</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Footer copyright -->
        <div class="text-center text-white text-[10px] sm:text-xs font-semibold tracking-wide">
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
