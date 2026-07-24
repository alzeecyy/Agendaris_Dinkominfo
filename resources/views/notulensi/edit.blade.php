@extends('layouts.app')

@section('title', 'Kelola Notulensi')

@section('content')
<div x-data="notulenEditor" class="space-y-6 -mt-6">
    
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#d4d1f5]/40 pb-4">
        <div>
            <a href="{{ route('agenda.show', $agenda->id) }}" 
               class="inline-flex items-center gap-2 text-xs font-bold text-[#5a508f] hover:text-[#2e2552] transition-colors -mt-2 mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path>
                </svg>
                <span>Kembali ke Detail Agenda</span>
            </a>
            <h1 class="text-xl font-black text-[#2e2552]">Kelola Notulensi Rapat</h1>
            <p class="text-xs text-[#5a508f] font-medium mt-1">Lengkapi informasi rapat, unggah rekaman suara, edit notulensi AI, lalu simpan atau ajukan ke pimpinan.</p>
        </div>

        <!-- Status Badge -->
        <div class="shrink-0 flex items-center gap-2">
            @php
                $statusBadges = [
                    'draft' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'menunggu_review' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'disahkan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'revisi' => 'bg-rose-50 text-rose-700 border-rose-200',
                ];
                $statusLabels = [
                    'draft' => 'DRAFT',
                    'menunggu_review' => 'MENUNGGU REVIEW',
                    'disahkan' => 'TELAH DISAHKAN',
                    'revisi' => 'PERLU REVISI',
                ];
                $status = $notulensi->status ?? 'draft';
            @endphp
            <span class="px-3 py-1.5 rounded-xl border text-xs font-extrabold tracking-wider {{ $statusBadges[$status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                {{ $statusLabels[$status] ?? strtoupper($status) }}
            </span>
        </div>
    </div>

    <!-- Segmented Control Mode Input Toggle (Left Aligned) -->
    <div class="flex justify-start">
        <div class="inline-flex items-center p-1 bg-white border border-[#d4d1f5]/80 rounded-2xl shadow-xs">
            <button type="button" @click="inputMode = 'audio'"
                    :class="inputMode === 'audio' ? 'bg-[#1b3bbb] text-white shadow-xs font-black' : 'text-[#5a508f] hover:text-[#2e2552] font-bold'"
                    class="px-5 py-2 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                <span>🎙️ Audio</span>
            </button>
            <button type="button" @click="inputMode = 'teks'"
                    :class="inputMode === 'teks' ? 'bg-[#1b3bbb] text-white shadow-xs font-black' : 'text-[#5a508f] hover:text-[#2e2552] font-bold'"
                    class="px-5 py-2 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                <span>📝 Teks</span>
            </button>
        </div>
    </div>

    <!-- Active Transcribing Prominent Top Banner (Immediately Visible Without Scrolling) -->
    @if($notulensi->is_transcribing)
        <div x-show="inputMode === 'audio'" class="p-4 bg-amber-50 border border-amber-300 rounded-3xl flex items-center justify-between gap-4 shadow-sm animate-pulse">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase text-amber-900 tracking-wider">Status Transkripsi Sedang Aktif</h4>
                    <p class="text-xs text-amber-800 font-medium">Sistem sedang memproses berkas suara rapat secara offline. Halaman ini akan diperbarui otomatis saat selesai.</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-amber-200 text-amber-900 text-[10px] font-extrabold rounded-xl uppercase tracking-wider shrink-0 hidden sm:inline-block">Proses Berjalan</span>
        </div>
        <script>
            (function() {
                let errorCount = 0;
                const maxErrors = 5;
                const checkStatus = function() {
                    fetch('{{ route("notulensi.status", $agenda->id) }}?_t=' + Date.now(), { cache: 'no-store', headers: { 'Cache-Control': 'no-cache' } })
                        .then(r => {
                            if (!r.ok) throw new Error('HTTP ' + r.status);
                            return r.json();
                        })
                        .then(data => {
                            errorCount = 0;
                            if (!data.is_transcribing) {
                                window.location.reload();
                            } else {
                                setTimeout(checkStatus, 3000);
                            }
                        })
                        .catch(e => {
                            errorCount++;
                            if (errorCount < maxErrors) {
                                setTimeout(checkStatus, 4000);
                            } else {
                                console.warn('Koneksi terputus saat memantau status transkripsi.');
                                const statusBadge = document.getElementById('ai-status-badge');
                                if (statusBadge) {
                                    statusBadge.innerHTML = '<div class="flex items-center justify-between text-xs text-amber-900 font-bold p-1"><span>Koneksi terganggu. Menghentikan pemantauan otomatis.</span> <button onclick="window.location.reload()" class="px-3 py-1 bg-amber-600 text-white rounded-lg text-[10px] font-black">Muat Ulang Halaman</button></div>';
                                }
                            }
                        });
                };
                setTimeout(checkStatus, 2000);
            })();
        </script>
    @endif

    <!-- Error Validation Banner -->
    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs space-y-1 shadow-sm">
            <p class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Pengajuan Gagal. Silakan periksa kembali kelengkapan data:</span>
            </p>
            <ul class="list-disc list-inside pl-2 space-y-0.5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- MAIN 2-COLUMN GRID LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- LEFT COLUMN (2/3 width): Main Form & Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <form id="notulen-form" class="space-y-6" action="{{ route('notulensi.save', $agenda->id) }}" method="POST">
                @csrf
                
                <!-- CARD 1: Informasi Dasar Rapat -->
                <div class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-6 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-[#d4d1f5]/30 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-[#1b3bbb]/10 text-[#1b3bbb] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-[#2e2552]">Informasi Dokumen Rapat</h3>
                            <p class="text-[11px] text-[#5a508f]">Judul dan nomor surat resmi wajib diisi sebelum diajukan ke pimpinan.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="judul" class="block text-xs font-bold uppercase tracking-wider text-[#2e2552]">Nama / Judul Kegiatan Rapat <span class="text-rose-500">*</span></label>
                            <input type="text" name="judul" id="judul" required value="{{ old('judul', $agenda->judul) }}" placeholder="Contoh: Rapat Evaluasi SPBE..."
                                   class="w-full px-4 py-2.5 bg-[#f8f7ff] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#8e88dd] focus:bg-white transition-colors">
                        </div>

                        <div class="space-y-1.5" id="container-nomor-surat">
                            <label for="nomor_surat_dasar" class="block text-xs font-bold uppercase tracking-wider text-[#2e2552]">Nomor Surat Dasar <span class="text-rose-500">*</span></label>
                            <input type="text" name="nomor_surat_dasar" id="nomor_surat_dasar" value="{{ old('nomor_surat_dasar', $agenda->nomor_surat_dasar) }}" placeholder="Contoh: 005/123/2026 Perihal Undangan Rapat Evaluasi SPBE"
                                   class="w-full px-4 py-2.5 bg-[#f8f7ff] border @error('nomor_surat_dasar') border-rose-500 ring-2 ring-rose-200 @else border-[#d4d1f5] @enderror rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd] focus:bg-white transition-colors">
                            
                            @error('nomor_surat_dasar')
                                <p class="text-[11px] font-bold text-rose-600 mt-1.5 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror

                            <div id="alert-surat-kosong" x-show="showNomorSuratAlert" x-cloak class="p-3 bg-amber-50 border border-amber-300/80 rounded-2xl mt-2 text-amber-900 text-xs flex items-center gap-2.5 shadow-xs">
                                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <span class="font-extrabold block text-[10.5px] uppercase tracking-wider text-amber-900">PERINGATAN: NOMOR SURAT BELUM DIISI</span>
                                    <p class="text-[10.5px] text-amber-800 leading-tight">Nomor Surat Dasar wajib diisi terlebih dahulu sebelum notulensi diajukan ke Pimpinan/Ketua.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2A: Mode Audio - Transkrip Percakapan Rapat -->
                <div x-show="inputMode === 'audio'" class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-[#d4d1f5]/30 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-[#2e2552]">Transkrip Percakapan Rapat</h3>
                                <p class="text-[11px] text-[#5a508f]">Teks lengkap hasil pengenalan suara rapat dari seluruh berkas audio.</p>
                            </div>
                        </div>

                        <!-- Button Analisis Ulang -->
                        <button type="button" @click="regenerateSummary" :disabled="loading"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#1b3bbb] hover:bg-[#09103c] text-white text-[10px] font-bold uppercase tracking-wider rounded-xl shadow-sm transition-all disabled:opacity-50">
                            <svg class="w-3.5 h-3.5 animate-spin" x-show="loading" fill="none" viewBox="0 0 24 24" style="display: none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg class="w-3.5 h-3.5" x-show="!loading" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span x-text="loading ? 'Memproses...' : 'Analisis Ulang'"></span>
                        </button>
                    </div>

                    <textarea name="transkrip_raw" id="transkrip_raw" rows="10" x-model="transkripRaw" placeholder="Transkrip lengkap percakapan rapat..."
                              class="w-full px-4 py-3 bg-[#f8f7ff] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-xs focus:outline-none focus:ring-2 focus:ring-[#8e88dd] focus:bg-white leading-relaxed font-mono transition-colors"></textarea>
                </div>

                <!-- CARD 2B: Mode Teks - Input Catatan Mentah Rapat -->
                <div x-show="inputMode === 'teks'" class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-[#d4d1f5]/30 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-[#2e2552]">Catatan Mentah Rapat (Hasil Ketikan / Paste)</h3>
                                <p class="text-[11px] text-[#5a508f]">Ketik atau tempelkan catatan rapat di sini, lalu klik Rapikan Teks untuk menyusun notulensi formal.</p>
                            </div>
                        </div>

                        <!-- Button Rapikan Teks -->
                        <button type="button" @click="refineText" :disabled="loading"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all disabled:opacity-50">
                            <svg class="w-4 h-4 animate-spin" x-show="loading" fill="none" viewBox="0 0 24 24" style="display: none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg class="w-4 h-4" x-show="!loading" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span x-text="loading ? 'Merapikan...' : 'Rapikan Teks'"></span>
                        </button>
                    </div>

                    <!-- Textarea Input Catatan Mentah (Mode Teks) -->
                    <div class="space-y-1.5">
                        <textarea name="transkrip_raw" id="transkrip_raw_teks" rows="10" x-model="transkripRaw" placeholder="Ketik atau tempelkan catatan rapat di sini..."
                                  class="w-full px-4 py-3 bg-[#f8f7ff] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-xs focus:outline-none focus:ring-2 focus:ring-[#8e88dd] focus:bg-white leading-relaxed font-mono transition-colors"></textarea>
                    </div>

                    <p class="text-[11.5px] text-[#5a508f] font-medium leading-relaxed bg-[#f8f7ff] p-3 rounded-2xl border border-[#d4d1f5]/50">
                        💡 <strong>Tips:</strong> Anda bisa mengetik catatan kasar, poin-poin sederhana, atau menyalin isi obrolan rapat. Setelah selesai, klik tombol <strong>Rapikan Teks</strong> di atas untuk mengubahnya secara otomatis menjadi dokumen notulensi yang rapi dan terstruktur di bawah.
                    </p>
                </div>

                <!-- CARD 3: Hasil Ringkasan & Notulensi Rapat (Digunakan oleh Kedua Mode) -->
                <div class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-[#d4d1f5]/30 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#1b3bbb]/10 text-[#1b3bbb] flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-[#2e2552]">Hasil Ringkasan & Notulensi</h3>
                                <p class="text-[11px] text-[#5a508f]">Edit dan rapikan hasil ringkasan rapat (poin keputusan, bahasan, dan tindak lanjut).</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <textarea name="ringkasan" id="ringkasan" rows="12" placeholder="Tulis ringkasan dan notulensi rapat di sini..."
                                  class="w-full px-4 py-3 bg-[#f8f7ff] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd] focus:bg-white font-mono leading-relaxed transition-colors">{{ trim(preg_replace('/```(?:markdown)?/i', '', $notulensi->ringkasan)) }}</textarea>
                    </div>
                </div>

                <!-- BOTTOM ACTION BAR: Save & Submit Buttons -->
                <div class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-5 shadow-sm space-y-4">
                    <!-- Unsaved Changes Warning -->
                    <div x-show="isDirty" x-cloak class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl flex items-center justify-between text-xs text-amber-800 animate-pulse">
                        <span class="flex items-center gap-2 font-bold">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Ada perubahan draf yang belum disimpan!</span>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase bg-amber-200 text-amber-900 px-2 py-0.5 rounded-md shrink-0">Belum Disimpan</span>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-[11px] text-[#5a508f] font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Tekan <strong>Simpan Progress Draft</strong> sebelum keluar.</span>
                        </p>

                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto justify-end">
                            <button type="submit" @click="isDirty = false" formaction="{{ route('notulensi.save', $agenda->id) }}" formmethod="POST"
                                    class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                <span>Simpan Progress Draft</span>
                            </button>
                            <button type="button" @click="submitForReview($event)"
                                    class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-[#1b3bbb] to-indigo-600 hover:from-[#09103c] hover:to-[#1b3bbb] text-white text-xs font-bold rounded-xl shadow-md shadow-[#1b3bbb]/20 transition-all flex items-center justify-center gap-2">
                                <span>Ajukan untuk Persetujuan</span>
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- RIGHT COLUMN (1/3 width): Sidebar Cards -->
        <div class="space-y-6">
            
            <!-- SIDEBAR CARD Mode Audio: Panduan & Informasi (Tampil Pertama di Mode Audio) -->
            <div x-show="inputMode === 'audio'" class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-[#d4d1f5]/30 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-[#2e2552]">Panduan Mode Audio</h3>
                </div>

                <div class="space-y-3 text-xs text-[#5a508f] leading-relaxed">
                    <div class="p-3 bg-[#f8f7ff] rounded-2xl border border-[#d4d1f5]/50 space-y-1">
                        <span class="font-extrabold text-[#2e2552] block text-[11px]">1. Unggah Berkas Suara</span>
                        <p class="text-[10.5px]">Unggah hingga 3 berkas audio rapat (MP3, WAV, M4A, OGG, maks. 40MB per berkas).</p>
                    </div>
                    <div class="p-3 bg-[#f8f7ff] rounded-2xl border border-[#d4d1f5]/50 space-y-1">
                        <span class="font-extrabold text-[#2e2552] block text-[11px]">2. Jalankan Proses Audio</span>
                        <p class="text-[10.5px]">Klik tombol <strong>Proses Audio</strong> untuk mengonversi rekaman menjadi transkrip dan ringkasan notulensi secara otomatis.</p>
                    </div>
                    <div class="p-3 bg-[#f8f7ff] rounded-2xl border border-[#d4d1f5]/50 space-y-1">
                        <span class="font-extrabold text-[#2e2552] block text-[11px]">3. Periksa & Simpan</span>
                        <p class="text-[10.5px]">Periksa transkrip dan ringkasan, lalu klik <strong>Analisis Ulang</strong> jika ingin memperbarui hasil.</p>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR CARD Mode Audio: Rekaman Suara Rapat (Input & List Audio) -->
            <div x-show="inputMode === 'audio'" class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-[#d4d1f5]/30 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-[#2e2552]">Rekaman Suara Rapat</h3>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                        {{ count($notulensi->audio_files ?? []) }}/3
                    </span>
                </div>

                <!-- Status Notification (Error, Complete, or In-Progress) -->
                @if($notulensi->is_transcribing)
                    <div id="ai-status-badge" class="p-4 bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl space-y-2 text-amber-900 text-xs shadow-sm">
                        <div class="flex items-center gap-2 font-black text-amber-900 uppercase tracking-wider text-[11px]">
                            <svg class="w-4 h-4 animate-spin text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Status: Mentranskripsi Audio</span>
                        </div>
                        <p class="text-[11px] text-amber-800 font-medium leading-relaxed">
                            Rekaman sedang diproses. Proses ini membutuhkan waktu beberapa saat. Jangan tutup halaman selama proses berlangsung.
                        </p>
                        <div class="pt-1 flex items-center justify-between text-[10px] font-bold text-amber-700">
                            <span>Estimasi waktu: ~1–3 menit</span>
                            <span class="px-2 py-0.5 bg-amber-200/80 rounded-md text-amber-900 uppercase">Memproses</span>
                        </div>
                    </div>
                @elseif(!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0)
                    @if($notulensi->transkrip_error)
                        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl space-y-2 text-rose-800 text-xs">
                            <div class="flex items-center gap-1.5 font-bold text-rose-900">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Transkripsi Belum Berhasil Diproses</span>
                            </div>
                            <p class="text-[10.5px] text-rose-700 leading-relaxed font-medium">
                                Transkripsi belum berhasil diproses. Silakan coba lagi menggunakan rekaman yang sudah tersimpan.
                            </p>
                            
                            <form action="{{ route('notulensi.process.audio', $agenda->id) }}" method="POST" onsubmit="if (typeof window.showHeavyLoading === 'function') window.showHeavyLoading('Memproses Ulang', 'Menjalankan ulang proses transkripsi audio rapat...');">
                                @csrf
                                <button type="submit" class="w-full mt-1 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <span>Coba Lagi Transkripsi</span>
                                </button>
                            </form>
                        </div>
                    @elseif(!empty($notulensi->transkrip_raw))
                        <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-2xl space-y-2 text-blue-900 text-xs">
                            <div class="flex items-center justify-between text-[10px] font-bold text-blue-800">
                                <span>Status Transkripsi:</span>
                                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md uppercase">Selesai</span>
                            </div>
                            <p class="text-[10px] text-blue-700 leading-relaxed font-medium">
                                Transkripsi selesai. Teks transkrip dan ringkasan dapat diperiksa di sebelah kiri.
                            </p>
                        </div>
                    @endif
                @endif

                <!-- Existing Audio Files List -->
                @if(!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0)
                    <div class="space-y-3">
                        @foreach($notulensi->audio_files as $index => $audio)
                            <div class="p-3 bg-[#f8f7ff] border border-[#d4d1f5]/50 rounded-2xl space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs font-bold text-[#2e2552] truncate flex-1" title="{{ $audio['name'] }}">
                                        {{ $index + 1 }}. {{ $audio['name'] }}
                                    </p>
                                    
                                    <!-- Delete Audio Form -->
                                    <form action="{{ route('notulensi.audio.delete', [$agenda->id, $index]) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus rekaman audio ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-500 p-1 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Rekaman">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                
                                <audio controls class="w-full h-8 bg-white border border-[#d4d1f5]/60 rounded-xl">
                                    <source src="{{ asset('storage/' . $audio['path']) }}" type="audio/mpeg">
                                    Browser tidak mendukung audio.
                                </audio>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Upload Audio Form with XHR Realtime Progress -->
                @if(empty($notulensi->audio_files) || count($notulensi->audio_files) < 3)
                    <div class="p-4 bg-[#f8f7ff] border border-[#d4d1f5]/60 border-dashed rounded-2xl space-y-3">
                        <div class="space-y-0.5 text-center">
                            <h4 class="text-xs font-bold text-[#2e2552]">Unggah Berkas Audio Rapat</h4>
                            <p class="text-[10px] text-[#5a508f]">Format yang didukung: MP3, WAV, M4A, OGG</p>
                            <p class="text-[9.5px] font-semibold text-slate-500">Maksimal ukuran file: 40MB per berkas</p>
                        </div>

                        <form id="audio-upload-form" action="{{ route('notulensi.upload', $agenda->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="audio" accept=".mp3,.wav,.m4a,.ogg,audio/*" onchange="uploadAudioXhr(this)" class="hidden" id="audio-input-compact">
                            
                            <label id="compact-upload-btn" for="audio-input-compact" class="w-full py-2.5 bg-[#1b3bbb] hover:bg-[#09103c] text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Pilih Berkas Audio</span>
                            </label>

                            <!-- Realtime Upload Progress Bar -->
                            <div id="upload-progress-container" style="display: none;" class="space-y-2 p-3 bg-white border border-[#d4d1f5]/80 rounded-xl text-left">
                                <div class="flex items-center justify-between text-[10.5px] font-bold text-[#2e2552]">
                                    <span id="upload-file-name" class="truncate max-w-[170px] text-[#1b3bbb]">file.mp3</span>
                                    <span id="upload-file-size" class="text-slate-400">0 MB</span>
                                </div>
                                
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div id="upload-progress-bar" class="bg-gradient-to-r from-[#1b3bbb] to-indigo-600 h-2.5 rounded-full transition-all duration-200" style="width: 0%"></div>
                                </div>

                                <div class="flex items-center justify-between text-[10px] font-bold">
                                    <span id="upload-status-text" class="text-[#5a508f]">Mengunggah rekaman...</span>
                                    <span id="upload-percentage" class="text-[#1b3bbb]">0%</span>
                                </div>
                            </div>

                            <!-- Upload Error Alert & Retry Button -->
                            <div id="upload-error-container" style="display: none;" class="p-3 bg-rose-50 border border-rose-200 rounded-xl space-y-2 text-rose-800 text-xs text-left">
                                <p id="upload-error-message" class="text-[10.5px] font-medium">Gagal mengunggah berkas audio.</p>
                                <button type="button" onclick="document.getElementById('audio-input-compact').click()" class="w-full py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded-lg transition-colors">
                                    Coba Lagi Unggah Audio
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-[11px] font-medium text-center">
                        Batas maksimal 3 berkas audio tercapai.
                    </div>
                @endif

                <!-- Dedicated "Proses Audio" Button -->
                @if(!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0 && !$notulensi->is_transcribing)
                    <form action="{{ route('notulensi.process.audio', $agenda->id) }}" method="POST" class="pt-2 border-t border-[#d4d1f5]/40" onsubmit="if (typeof window.showHeavyLoading === 'function') window.showHeavyLoading('Transkripsi Audio Rapat', 'Sistem sedang memproses berkas audio rapat secara offline. Mohon tunggu...');">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-[#1b3bbb] to-indigo-600 hover:from-[#09103c] hover:to-[#1b3bbb] text-white text-xs font-extrabold rounded-2xl shadow-md shadow-[#1b3bbb]/20 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Proses Audio</span>
                        </button>
                    </form>
                @endif
            </div>

            <!-- SIDEBAR CARD Mode Teks: Panduan & Informasi -->
            <div x-show="inputMode === 'teks'" class="bg-white border border-[#d4d1f5]/60 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-[#d4d1f5]/30 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-[#2e2552]">Panduan Mode Teks</h3>
                </div>

                <div class="space-y-3 text-xs text-[#5a508f] leading-relaxed">
                    <div class="p-3 bg-[#f8f7ff] rounded-2xl border border-[#d4d1f5]/50 space-y-1">
                        <span class="font-extrabold text-[#2e2552] block text-[11px]">1. Masukkan Catatan Rapat</span>
                        <p class="text-[10.5px]">Ketik poin penting rapat secara langsung atau salin draf yang sudah dibuat di aplikasi lain.</p>
                    </div>
                    <div class="p-3 bg-[#f8f7ff] rounded-2xl border border-[#d4d1f5]/50 space-y-1">
                        <span class="font-extrabold text-[#2e2552] block text-[11px]">2. Klik Rapikan Teks</span>
                        <p class="text-[10.5px]">Sistem akan merapikan tata bahasa, format markdown, serta menyusun poin pembahasan dan keputusan.</p>
                    </div>
                    <div class="p-3 bg-[#f8f7ff] rounded-2xl border border-[#d4d1f5]/50 space-y-1">
                        <span class="font-extrabold text-[#2e2552] block text-[11px]">3. Simpan / Ajukan</span>
                        <p class="text-[10.5px]">Periksa kembali hasil ringkasan sebelum menyimpan draf atau mengajukan ke pimpinan.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- JS Scripts & Alpine Registration -->
    <script>
    function registerNotulenEditor() {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('notulenEditor', () => ({
                inputMode: 'audio',
                loading: false,
                isDirty: false,
                showNomorSuratAlert: false,
                transkripRaw: @json($notulensi->transkrip_raw ?? ''),
                init() {
                    this.$nextTick(() => {
                        const form = document.getElementById('notulen-form');
                        if (form) {
                            form.querySelectorAll('input, textarea, select').forEach(el => {
                                el.addEventListener('input', () => { this.isDirty = true; });
                                el.addEventListener('change', () => { this.isDirty = true; });
                            });
                            form.addEventListener('submit', () => { this.isDirty = false; });
                        }

                        const nomorSuratInput = document.getElementById('nomor_surat_dasar');
                        if (nomorSuratInput) {
                            nomorSuratInput.addEventListener('input', () => {
                                if (nomorSuratInput.value.trim() !== '') {
                                    this.showNomorSuratAlert = false;
                                    nomorSuratInput.classList.remove('border-rose-500', 'ring-2', 'ring-rose-200');
                                    nomorSuratInput.classList.add('border-[#d4d1f5]');
                                }
                            });
                        }
                    });

                    window.addEventListener('beforeunload', (e) => {
                        if (this.isDirty) {
                            e.preventDefault();
                            e.returnValue = 'Ada perubahan draf notulensi yang belum disimpan!';
                        }
                    });
                },
                submitForReview(event) {
                    const nomorSuratInput = document.getElementById('nomor_surat_dasar');
                    const nomorSuratValue = nomorSuratInput ? nomorSuratInput.value.trim() : '';

                    if (!nomorSuratValue) {
                        this.showNomorSuratAlert = true;

                        if (event) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        if (nomorSuratInput) {
                            nomorSuratInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            nomorSuratInput.focus();
                            nomorSuratInput.classList.remove('border-[#d4d1f5]');
                            nomorSuratInput.classList.add('border-rose-500', 'ring-2', 'ring-rose-200');
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Nomor Surat Belum Diisi!',
                                text: 'Kolom Nomor Surat Dasar wajib diisi terlebih dahulu sebelum mengajukan notulensi ke Pimpinan.',
                                confirmButtonText: 'Isi Nomor Surat Sekarang',
                                confirmButtonColor: '#1b3bbb'
                            });
                        } else {
                            alert('Nomor Surat Dasar wajib diisi sebelum mengajukan notulensi!');
                        }
                        return false;
                    }

                    this.showNomorSuratAlert = false;

                    this.isDirty = false;
                    const form = document.getElementById('notulen-form');
                    if (form) {
                        form.action = '{{ route("notulensi.submit", $agenda->id) }}';
                        form.submit();
                    }
                },
                regenerateSummary() {
                    const transcript = (this.transkripRaw || '').trim();
                    if (!transcript) {
                        Swal.fire({
                            text: 'Teks transkrip rapat masih kosong!',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    Swal.fire({
                        text: 'Apakah Anda yakin ingin menganalisis ulang transkrip? Tindakan ini akan menimpa isi Ringkasan saat ini.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        this.loading = true;
                        if (typeof window.showHeavyLoading === 'function') {
                            window.showHeavyLoading('Menganalisis Transkrip', 'Sedang menganalisis ulang isi transkrip rapat. Mohon tunggu...');
                        }

                        fetch('{{ route("notulensi.regenerate", $agenda->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                transkrip_raw: transcript
                            })
                        })
                        .then(res => res.json())
                        .then(res => {
                            this.loading = false;
                            if (typeof window.hideHeavyLoading === 'function') window.hideHeavyLoading();
                            if (res.status === 'success') {
                                document.getElementById('ringkasan').value = res.data.trim();
                                this.isDirty = true;
                                Swal.fire({
                                    text: 'Analisis transkrip berhasil! Ringkasan telah diperbarui. Silakan edit dan rapikan sesuai kebutuhan.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                Swal.fire({
                                    text: res.message || 'Gagal memproses analisis transkrip.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(err => {
                            this.loading = false;
                            if (typeof window.hideHeavyLoading === 'function') window.hideHeavyLoading();
                            Swal.fire({
                                text: 'Terjadi kesalahan koneksi saat memproses analisis.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                    });
                },
                refineText() {
                    const rawText = (this.transkripRaw || '').trim();
                    if (!rawText) {
                        Swal.fire({
                            text: 'Catatan mentah rapat masih kosong! Silakan ketik atau tempelkan catatan rapat terlebih dahulu.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    Swal.fire({
                        text: 'Rapikan dan susun catatan mentah menjadi notulensi resmi terstruktur?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Rapikan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        this.loading = true;
                        if (typeof window.showHeavyLoading === 'function') {
                            window.showHeavyLoading('Merapikan Teks', 'Sedang merapikan dan menyusun catatan rapat mentah...');
                        }

                        fetch('{{ route("notulensi.refine-text", $agenda->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                teks_raw: rawText
                            })
                        })
                        .then(res => res.json())
                        .then(res => {
                            this.loading = false;
                            if (typeof window.hideHeavyLoading === 'function') window.hideHeavyLoading();
                            if (res.status === 'success') {
                                document.getElementById('ringkasan').value = res.data.trim();
                                this.isDirty = true;
                                Swal.fire({
                                    text: 'Catatan berhasil dirapikan! Ringkasan notulensi telah diperbarui. Silakan periksa dan simpan draft.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                Swal.fire({
                                    text: res.message || 'Gagal merapikan teks catatan.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(err => {
                            this.loading = false;
                            if (typeof window.hideHeavyLoading === 'function') window.hideHeavyLoading();
                            Swal.fire({
                                text: 'Terjadi kesalahan koneksi saat merapikan teks.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                    });
                }
            }));
        }
    }

    function uploadAudioXhr(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        // Check 0-byte file
        if (file.size === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Berkas Kosong!',
                text: 'Berkas audio yang dipilih berukuran 0 byte (kosong) dan tidak dapat diproses.',
                confirmButtonColor: '#1b3bbb'
            });
            input.value = '';
            return;
        }

        // Check file extension (.mp3, .wav, .m4a, .ogg)
        const allowedExts = ['mp3', 'wav', 'm4a', 'ogg'];
        const fileName = file.name;
        const ext = fileName.split('.').pop().toLowerCase();
        if (!allowedExts.includes(ext)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Tidak Didukung!',
                text: 'Format berkas ".' + ext.toUpperCase() + '" tidak didukung. Harap unggah berkas berformat MP3, WAV, M4A, atau OGG.',
                confirmButtonColor: '#1b3bbb'
            });
            input.value = '';
            return;
        }

        // Check 40MB limit (40 * 1024 * 1024 bytes)
        const maxSizeBytes = 40 * 1024 * 1024;
        if (file.size > maxSizeBytes) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran Terlalu Besar!',
                text: 'Ukuran berkas audio (' + (file.size / (1024*1024)).toFixed(1) + ' MB) melebihi batas maksimal 40 MB.',
                confirmButtonColor: '#1b3bbb'
            });
            input.value = '';
            return;
        }

        const btn = document.getElementById('compact-upload-btn');
        const container = document.getElementById('upload-progress-container');
        const errContainer = document.getElementById('upload-error-container');
        const bar = document.getElementById('upload-progress-bar');
        const percentage = document.getElementById('upload-percentage');
        const statusText = document.getElementById('upload-status-text');
        const fileNameEl = document.getElementById('upload-file-name');
        const fileSizeEl = document.getElementById('upload-file-size');

        if (btn) btn.style.display = 'none';
        if (errContainer) errContainer.style.display = 'none';
        if (container) container.style.display = 'block';

        if (fileNameEl) fileNameEl.innerText = file.name;
        if (fileSizeEl) fileSizeEl.innerText = (file.size / (1024*1024)).toFixed(1) + ' MB';

        const formData = new FormData();
        formData.append('audio', file);
        formData.append('_token', '{{ csrf_token() }}');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("notulensi.upload", $agenda->id) }}', true);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                if (bar) bar.style.width = percent + '%';
                if (percentage) percentage.innerText = percent + '%';
                if (statusText) {
                    if (percent < 100) {
                        statusText.innerText = 'Mengunggah rekaman... (' + percent + '%)';
                    } else {
                        statusText.innerText = 'Upload selesai, memproses berkas...';
                    }
                }
            }
        };

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                if (statusText) statusText.innerText = 'Upload berhasil! Menyegarkan...';
                setTimeout(function() {
                    window.location.reload();
                }, 500);
            } else {
                showUploadError('Gagal mengunggah berkas audio. Server merespons error (Status ' + xhr.status + ').');
            }
        };

        xhr.onerror = function() {
            showUploadError('Terjadi kesalahan koneksi saat mengunggah berkas audio. Silakan periksa koneksi internet Anda.');
        };

        function showUploadError(msg) {
            if (container) container.style.display = 'none';
            if (btn) btn.style.display = 'flex';
            if (errContainer) {
                errContainer.style.display = 'block';
                const errMsg = document.getElementById('upload-error-message');
                if (errMsg) errMsg.innerText = msg;
            }
            input.value = '';
        }

        xhr.send(formData);
    }

    if (typeof Alpine !== 'undefined') {
        registerNotulenEditor();
    } else {
        document.addEventListener('alpine:init', registerNotulenEditor);
    }
    </script>
</div>
@endsection
