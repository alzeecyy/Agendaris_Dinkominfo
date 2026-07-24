@extends('layouts.app')

@section('title', $notulensi->status === 'disahkan' ? 'Notulensi Rapat Resmi' : 'Tinjau Notulensi')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs / Back button & Title -->
    <div class="space-y-1 border-b border-[#d4d1f5]/40 pb-4">
        <a href="{{ route('agenda.show', $agenda->id) }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-[#5a508f] hover:text-[#2e2552] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path>
            </svg>
            <span>Kembali ke Detail Agenda</span>
        </a>
        <h1 class="text-xl font-black text-[#2e2552] tracking-tight pt-1">
            {{ $notulensi->status === 'disahkan' ? 'Notulensi Rapat Resmi' : 'Tinjau Draf Notulensi' }}
        </h1>
    </div>

    <div x-data="{ openRevisiPanel: false }" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- LEFT PANEL: READ ONLY MINUTES DETAILS -->
        <div class="lg:col-span-2 space-y-6 bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 md:p-8 shadow-sm">
            <div>
                @if($notulensi->status === 'disahkan')
                    <span class="px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-600 border border-emerald-200 text-[10px] font-bold uppercase tracking-wide">Disahkan ✓</span>
                @else
                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-600 border border-amber-200 text-[10px] font-bold uppercase tracking-wide">Menunggu Review</span>
                @endif
                <h2 class="text-lg font-black text-[#2e2552] mt-2 leading-tight">{{ $agenda->judul }}</h2>
                <p class="text-xs text-[#5a508f] mt-1">Nomor Surat: <strong class="text-[#2e2552]">{{ $agenda->nomor_surat_dasar ?? '-' }}</strong></p>
            </div>

            <!-- Read only fields -->
            <div class="space-y-5 text-sm text-[#2e2552] border-t border-[#d4d1f5]/40 pt-4">
                <div class="space-y-1.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Ringkasan Rapat</h4>
                    <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed font-medium text-slate-700">{!! $notulensi->ringkasan_html !!}</div>
                </div>
                
                @if(!empty($notulensi->pembahasan))
                <div class="space-y-1.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">{{ $notulensi->pembahasan_title }}</h4>
                    <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed whitespace-pre-line font-medium text-slate-700">{{ $notulensi->pembahasan }}</div>
                </div>
                @endif

                @if(!empty($notulensi->keputusan))
                <div class="space-y-1.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">{{ $notulensi->keputusan_title }}</h4>
                    <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 text-emerald-600 font-bold rounded-2xl leading-relaxed whitespace-pre-line">{{ $notulensi->keputusan }}</div>
                </div>
                @endif

                @if(!empty($notulensi->kesimpulan))
                <div class="space-y-1.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Kesimpulan</h4>
                    <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed whitespace-pre-line font-medium text-slate-700">{{ $notulensi->kesimpulan }}</div>
                </div>
                @endif

                <div class="space-y-1.5" x-data="{ showTranscript: false }">
                    <button @click="showTranscript = !showTranscript" type="button" class="flex items-center gap-1.5 text-xs text-[#8e88dd] hover:text-[#2e2552] font-bold focus:outline-none">
                        <span x-text="showTranscript ? 'Sembunyikan Transkrip Lengkap' : 'Lihat Transkrip Percakapan Lengkap (AI)'"></span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': showTranscript}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="showTranscript" x-cloak class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed text-xs text-slate-600 whitespace-pre-line mt-2">
                        {{ $notulensi->transkrip_raw }}
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: DECISION / DOWNLOAD ACTIONS -->
        <div class="space-y-6">
            @if($notulensi->status === 'disahkan')
                <!-- Official Approved Document & Downloads Card -->
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-5">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Unduh Berkas Resmi</h3>
                        <p class="text-[10px] text-[#5a508f] mt-1.5 leading-relaxed font-medium">Dokumen ini telah disahkan oleh Pimpinan. Silakan unduh salinan resmi di bawah ini.</p>
                    </div>

                    <div class="space-y-2.5">
                        <a href="{{ route('notulensi.export.pdf', $agenda->id) }}" target="_blank" data-no-pjax
                           class="w-full py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-rose-600/15 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Unduh Dokumen (PDF)</span>
                        </a>

                        <a href="{{ route('notulensi.export.docx', $agenda->id) }}" target="_blank" data-no-pjax
                           class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-600/15 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <span>Unduh Dokumen (Word / DOCX)</span>
                        </a>
                    </div>

                    @if(isset($approverInfo))
                        <div class="p-3.5 bg-slate-50 border border-slate-200/60 rounded-2xl text-xs space-y-1">
                            <p class="text-[9px] font-bold text-[#8e88dd] uppercase tracking-wider">Pejabat Pengesah Notulensi:</p>
                            <p class="font-bold text-[#2e2552]">{{ $approverInfo->name }}</p>
                            <p class="text-[10px] text-[#5a508f]">{{ $approverInfo->jabatan }} {{ $approverInfo->sub_jabatan ?? '' }}</p>
                            <p class="text-[9px] text-slate-400 font-mono">NIP. {{ $approverInfo->nip }}</p>
                            @if(!empty($notulensi->tanda_tangan_approver))
                                <div class="pt-2 mt-2 border-t border-slate-200/60">
                                    <p class="text-[9px] font-bold text-emerald-600 flex items-center gap-1 mb-1">
                                        <span>✓ Tanda Tangan Digital Tersimpan:</span>
                                    </p>
                                    <img src="{{ $notulensi->tanda_tangan_approver }}" class="h-10 border border-slate-200 bg-white rounded-lg p-1" />
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            @elseif($isApprover)
                <!-- Approval Control Card -->
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-6" x-data="approverSignatureApp()">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Persetujuan Dokumen</h3>
                        <p class="text-[10px] text-[#5a508f] mt-1.5 leading-relaxed font-medium">Harap tinjau draf di sebelah kiri secara saksama sebelum mengambil keputusan.</p>
                    </div>

                    <div class="space-y-3">
                        <!-- 1. APPROVE ACTION BUTTON (Triggers Modal) -->
                        <button type="button" @click="openSignatureModal()" 
                                class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.98] text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-emerald-600/10 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Sahkan & Setujui Dokumen</span>
                        </button>

                        <!-- 2. REVISE SWITCH BUTTON -->
                        <button @click="openRevisiPanel = !openRevisiPanel" 
                                class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-[#5a508f] font-bold text-xs rounded-xl border border-[#d4d1f5] transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Tolak & Minta Perbaikan</span>
                        </button>
                    </div>

                    <!-- Hidden Form for Approval -->
                    <form action="{{ route('notulensi.review.approve', $agenda->id) }}" method="POST" id="approval-form">
                        @csrf
                        <input type="hidden" name="tanda_tangan_approver" id="tanda_tangan_approver_input" x-model="signatureData">
                    </form>

                    <!-- DIGITAL SIGNATURE MODAL -->
                    <div x-show="showModal" x-cloak x-transition.opacity
                         class="fixed inset-0 z-[99999] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                        
                        <div class="bg-white rounded-3xl p-5 sm:p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4 transform transition-all">
                            
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-extrabold text-[#09103c]">Tanda Tangan Digital Pimpinan</h3>
                                        <p class="text-[10.5px] text-slate-500 font-medium">Goreskan tanda tangan Anda sebelum mengesahkan.</p>
                                    </div>
                                </div>
                                <button type="button" @click="closeSignatureModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Canvas Container -->
                            <div class="space-y-2 bg-slate-50 border border-slate-200/80 rounded-2xl p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Area Tanda Tangan <span class="text-rose-500">*</span></span>
                                    <button type="button" @click="clearSignature()" class="text-[10px] font-bold text-rose-500 hover:underline">Reset / Hapus</button>
                                </div>
                                <div class="border-2 border-dashed border-indigo-200 hover:border-indigo-400 rounded-xl p-1 bg-white transition-all">
                                    <canvas id="approver-sig-canvas" class="w-full h-36 rounded-lg cursor-crosshair touch-none"></canvas>
                                </div>
                                <p class="text-[9.5px] text-slate-400 italic">Gunakan jari (layar sentuh HP) atau kursor mouse (komputer) untuk tanda tangan.</p>
                            </div>

                            <!-- Modal Action Buttons -->
                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" @click="closeSignatureModal()"
                                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition-colors">
                                    Batal
                                </button>
                                <button type="button" @click="confirmAndSubmit()"
                                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Sahkan & Simpan Tanda Tangan</span>
                                </button>
                            </div>

                        </div>

                    </div>

                    <!-- Script definition -->
                    <script>
                        function approverSignatureApp() {
                            return {
                                showModal: false,
                                signatureData: '',
                                openSignatureModal() {
                                    this.showModal = true;
                                    setTimeout(() => {
                                        this.initCanvas();
                                    }, 100);
                                },
                                closeSignatureModal() {
                                    this.showModal = false;
                                },
                                initCanvas() {
                                    const canvas = document.getElementById('approver-sig-canvas');
                                    if (!canvas) return;

                                    if (window.approverSigPad) {
                                        window.approverSigPad.off();
                                        window.approverSigPad = null;
                                    }

                                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                                    const rect = canvas.getBoundingClientRect();
                                    
                                    canvas.width = rect.width * ratio;
                                    canvas.height = rect.height * ratio;

                                    const ctx = canvas.getContext('2d');
                                    ctx.scale(ratio, ratio);

                                    window.approverSigPad = new SignaturePad(canvas, {
                                        penColor: '#09103c',
                                        minWidth: 1.5,
                                        maxWidth: 3.5,
                                    });
                                },
                                clearSignature() {
                                    if (window.approverSigPad) {
                                        window.approverSigPad.clear();
                                        this.signatureData = '';
                                    }
                                },
                                confirmAndSubmit() {
                                    if (!window.approverSigPad || window.approverSigPad.isEmpty()) {
                                        if (typeof Swal !== 'undefined') {
                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'Tanda Tangan Masih Kosong!',
                                                text: 'Mohon goreskan tanda tangan digital Anda terlebih dahulu pada kotak di atas sebelum mengesahkan.',
                                                confirmButtonText: 'Mengerti'
                                            });
                                        } else {
                                            alert('Mohon goreskan tanda tangan digital Anda terlebih dahulu!');
                                        }
                                        return;
                                    }

                                    const dataUrl = window.approverSigPad.toDataURL('image/png');
                                    this.signatureData = dataUrl;
                                    
                                    const hiddenInput = document.getElementById('tanda_tangan_approver_input');
                                    if (hiddenInput) {
                                        hiddenInput.value = dataUrl;
                                    }

                                    this.closeSignatureModal();

                                    if (typeof window.showHeavyLoading === 'function') {
                                        window.showHeavyLoading('Mengesahkan Notulensi', 'Sistem sedang memproses pengesahan dokumen dan tanda tangan digital Pimpinan...');
                                    }

                                    document.getElementById('approval-form').submit();
                                }
                            }
                        }
                    </script>

                    <!-- 3. REVISION INPUT PANEL -->
                    <div x-show="openRevisiPanel" x-cloak x-transition
                         class="p-4 bg-rose-50/50 border border-rose-200 rounded-2xl space-y-3">
                        <form action="{{ route('notulensi.review.revision', $agenda->id) }}" method="POST">
                            @csrf
                            <label for="catatan_revisi" class="block text-[10px] font-bold text-rose-600 uppercase">Catatan Revisi untuk Sekretaris</label>
                            <textarea name="catatan_revisi" id="catatan_revisi" rows="4" required placeholder="Tuliskan bagian mana yang perlu diperbaiki (misal: koreksi redaksi keputusan rapat poin kedua)..."
                                      class="w-full mt-1.5 px-3 py-2 bg-white border border-[#d4d1f5] rounded-xl text-xs text-[#2e2552] placeholder-slate-400 focus:outline-none"></textarea>
                            
                            <button type="submit" 
                                    class="w-full mt-3 py-2.5 bg-rose-600 hover:bg-rose-500 active:scale-[0.98] text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-rose-600/20 transition-all">
                                Kirim Catatan Perbaikan
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- Read Only Info Card for Secretary -->
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Status Dokumen</h3>
                    <div class="p-4 bg-amber-50/50 border border-amber-200 text-amber-800 rounded-2xl text-xs space-y-2 leading-relaxed">
                        <p class="font-bold flex items-center gap-1">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Mode Pratinjau (Mode Baca)</span>
                        </p>
                        <p>Anda sedang meninjau draf notulensi ini. Pengesahan hanya dapat dilakukan oleh Pimpinan/Ketua Rapat yang berwenang.</p>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
