<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Agendaris Dinkominfo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Decorative background blurred shapes -->
    <div class="absolute top-10 left-10 w-72 h-72 bg-blue-300/20 rounded-full filter blur-3xl"></div>
    <div class="absolute bottom-10 right-10 w-80 h-80 bg-indigo-300/10 rounded-full filter blur-3xl"></div>

    <div class="w-full max-w-md z-10 space-y-6">
        <!-- Logo & Header -->
        <div class="text-center flex flex-col items-center justify-center">
            <img src="{{ asset('images/logo-banyumas-crest.png') }}" alt="Logo Kabupaten Banyumas" class="w-20 h-20 object-contain hover:scale-105 transition-transform duration-300">
            <h1 class="text-2xl font-black text-[#09103c] tracking-widest mt-4">SIRENA</h1>
            <p class="text-slate-500 text-xs font-semibold mt-1">Kalender & Notulensi Rapat Dinkominfo Banyumas</p>
        </div>

        <!-- Premium White Card -->
        <div class="bg-white rounded-[32px] p-8 shadow-2xl relative border border-slate-100">
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
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

                @if(session('warning'))
                    <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-2xl p-4 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                <!-- Username Input (NIP) -->
                <div class="space-y-1.5">
                    <label for="nip" class="block text-xs font-bold uppercase tracking-wider text-[#09103c]">Nomor Induk Pegawai (NIP)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <input type="text" name="nip" id="nip" value="{{ old('nip') }}" placeholder="Masukkan 18 Digit NIP" required autofocus
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-[#09103c] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] focus:border-transparent transition-all duration-200 text-sm">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#09103c]">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <input type="password" name="password" id="password" placeholder="••••••••" required
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-[#09103c] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1b3bbb] focus:border-transparent transition-all duration-200 text-sm">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3.5 bg-[#1b3bbb] hover:bg-[#0d228c] active:scale-[0.98] text-white font-bold text-xs uppercase tracking-wider rounded-2xl transition-all duration-200 shadow-lg shadow-[#1b3bbb]/20 flex items-center justify-center gap-2">
                    <span>Masuk Ke Sistem</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Footer copyright -->
        <div class="text-center text-slate-500 text-[10px] font-semibold">
            &copy; 2026 Dinas Komunikasi dan Informatika Kabupaten Banyumas.
        </div>
    </div>
</body>
</html>
