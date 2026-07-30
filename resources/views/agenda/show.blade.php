@extends('layouts.app')

@section('title', 'Detail Agenda')


@section('content')
@php
    $predefinedRooms = [
        'Aula Rapat Dinkominfo',
        'Ruang Pelatihan',
        'Smart Room Graha Satria'
    ];
    $isPredefined = in_array($agenda->lokasi, $predefinedRooms);
    $initialTempat = $isPredefined ? $agenda->lokasi : 'Lainnya';
    $initialTempatLainnya = $isPredefined ? '' : $agenda->lokasi;
@endphp
<div x-data="agendaDetail" data-participants='@json($participants)' style="display: flex; flex-direction: column; width: 100%; gap: 1rem;" class="w-full min-w-0">
    
    <!-- Breadcrumbs / Back button -->
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; min-width: 0; padding-bottom: 0.25rem;" class="w-full">
        <a href="{{ route('calendar', ['date' => $agenda->tanggal->toDateString()]) }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-[#5a508f] hover:text-[#1b3bbb] transition-colors py-1 group">
            <svg class="w-4 h-4 shrink-0 text-[#5a508f] group-hover:text-[#1b3bbb] group-hover:-translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path>
            </svg>
            <span>Kembali ke Kalender Rinci</span>
        </a>
        
        @if($isSecretaryOfAgenda)
            <!-- Edit Agenda Trigger (Sekretaris only) -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <button type="button" @click="openEditModal = true" 
                        class="px-2.5 py-1.5 sm:px-4 sm:py-2 bg-white border border-[#d4d1f5] hover:bg-[#8e88dd]/15 text-[11px] sm:text-xs font-bold text-[#2e2552] rounded-xl transition-all shadow-sm shrink-0 cursor-pointer">
                    Edit Agenda
                </button>
                <form action="{{ route('agenda.destroy', $agenda->id) }}" method="POST" data-title="Hapus Agenda Ini?" data-confirm="Agenda beserta seluruh data presensi & notulensi akan dihapus permanen dari sistem." data-confirm-btn="Hapus Agenda">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-2.5 py-1.5 sm:px-4 sm:py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-[11px] sm:text-xs font-bold rounded-xl transition-all shadow-sm shrink-0 cursor-pointer">
                        Hapus Agenda
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- TOP GRID: Card Agenda (Full width jika Sosialisasi/Pelatihan tanpa presensi, 2-kolom jika Rapat / Membutuhkan Presensi) -->
    <div style="width: 100%; min-width: 0;" class="grid grid-cols-1 {{ ($agenda->kategori !== 'rapat' && !$agenda->butuh_presensi) ? 'grid-cols-1' : 'lg:grid-cols-2' }} gap-3 sm:gap-4.5 items-stretch">
        
        <!-- Left Column: Info Detail Agenda -->
        <div class="min-w-0 flex flex-col h-full">
            <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[24px] p-3.5 sm:p-5 shadow-sm h-full flex flex-col justify-between gap-3">
                <div class="space-y-3 sm:space-y-4">
                    <!-- Category badge -->
                    <div class="flex items-center justify-between">
                        @php
                            $badgeStyles = [
                                'rapat' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'sosialisasi' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'pelatihan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'kegiatan_lainnya' => 'bg-slate-100 text-slate-700 border-slate-200',
                            ];
                            $kategoriLabels = [
                                'rapat' => 'Rapat',
                                'sosialisasi' => 'Sosialisasi',
                                'pelatihan' => 'Pelatihan',
                                'kegiatan_lainnya' => 'Kegiatan Lainnya',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] sm:text-xs font-bold border 
                            {{ $badgeStyles[$agenda->kategori] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                            {{ $kategoriLabels[$agenda->kategori] ?? $agenda->kategori }}
                        </span>
                        <div class="flex items-center gap-1.5 text-[9.5px] sm:text-xs text-[#5a508f]">
                            <span>Dibuat oleh: <strong class="text-[#2e2552]">{{ $agenda->sekretaris->name }}</strong></span>
                            @if($agenda->sekretaris?->bidang)
                                <span class="px-2 py-0.5 rounded-full bg-indigo-50 border border-indigo-200 text-[#1b3bbb] text-[9px] sm:text-[10px] font-black uppercase tracking-wider" title="{{ $agenda->sekretaris->bidang->nama }}">
                                    {{ $agenda->sekretaris->bidang->singkatan ?? $agenda->sekretaris->bidang->nama }}
                                </span>
                            @elseif($agenda->sekretaris?->isSekretarisDinas() || $agenda->sekretaris?->isSekretaris())
                                <span class="px-2 py-0.5 rounded-full bg-purple-50 border border-purple-200 text-purple-700 text-[9px] sm:text-[10px] font-black uppercase tracking-wider">
                                    Sekretariat Dinas
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-1 sm:space-y-2">
                        <h1 class="text-sm sm:text-2xl font-black text-[#2e2552] tracking-wide leading-snug">{{ $agenda->judul }}</h1>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] leading-relaxed">{{ $agenda->deskripsi ?? 'Tidak ada deskripsi tambahan.' }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3 border-t border-b border-[#d4d1f5]/40 text-xs">
                        <!-- Tanggal -->
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="p-2 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] text-[#8e88dd] uppercase font-bold tracking-wider">Tanggal</p>
                                <p class="text-[11px] sm:text-xs font-bold text-[#2e2552] truncate mt-0.5" title="{{ $agenda->tanggal->translatedFormat('d F Y') }}">{{ $agenda->tanggal->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                        <!-- Jam -->
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="p-2 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] text-[#8e88dd] uppercase font-bold tracking-wider">Waktu</p>
                                <p class="text-[11px] sm:text-xs font-bold text-[#2e2552] whitespace-nowrap mt-0.5">{{ substr($agenda->jam_mulai, 0, 5) }} - {{ substr($agenda->jam_selesai, 0, 5) }} WIB</p>
                            </div>
                        </div>
                        <!-- Lokasi -->
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="p-2 bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] text-[#8e88dd] uppercase font-bold tracking-wider">Lokasi</p>
                                <p class="text-[11px] sm:text-xs font-bold text-[#2e2552] truncate mt-0.5" title="{{ $agenda->lokasi }}">{{ $agenda->lokasi }}</p>
                            </div>
                        </div>
                    </div>

                    @php
                        $invitedBidangLabels = [];
                        $hakAksesRaw = (array)($agenda->hak_akses ?? []);
                        if (in_array('semua_orang', $hakAksesRaw)) {
                            $invitedBidangLabels[] = [
                                'name' => 'Semua Bidang & Subbag (Lintas Dinas)',
                                'bg' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                            ];
                        } else {
                            foreach ($hakAksesRaw as $hId) {
                                if ($hId === 'kadin') {
                                    $invitedBidangLabels[] = [
                                        'name' => 'Kadin',
                                        'bg' => 'bg-amber-50 border-amber-200 text-amber-900',
                                    ];
                                } else {
                                    $bObj = \App\Models\Bidang::find($hId);
                                    if ($bObj) {
                                        $invitedBidangLabels[] = [
                                            'name' => $bObj->singkatan ?? $bObj->nama,
                                            'bg' => 'bg-indigo-50 border-indigo-200 text-[#1b3bbb]',
                                        ];
                                    }
                                }
                            }
                            if (empty($invitedBidangLabels) && $agenda->participants()->exists()) {
                                $partBidangIds = $agenda->participants()->pluck('bidang_id')->unique()->filter()->toArray();
                                foreach ($partBidangIds as $pbId) {
                                    $bObj = \App\Models\Bidang::find($pbId);
                                    if ($bObj) {
                                        $invitedBidangLabels[] = [
                                            'name' => $bObj->singkatan ?? $bObj->nama,
                                            'bg' => 'bg-indigo-50 border-indigo-200 text-[#1b3bbb]',
                                        ];
                                    }
                                }
                            }
                        }
                    @endphp

                    @if(!empty($invitedBidangLabels))
                        <!-- Bidang / Subbag Peserta Rapat (Bidang Diundang) -->
                        <div class="px-3 py-2 bg-slate-50/80 border border-[#d4d1f5]/50 rounded-2xl space-y-1.5">
                            <span class="text-[9.5px] font-extrabold text-[#5a508f] uppercase tracking-wider block flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#8e88dd] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                </svg>
                                <span>Peserta / Bidang Diundang:</span>
                            </span>
                            <div class="flex items-center gap-1 flex-wrap pt-0.5">
                                @foreach($invitedBidangLabels as $lbl)
                                    <span class="px-2 py-0.5 rounded-full border text-[8.5px] sm:text-[9px] font-bold shadow-2xs {{ $lbl['bg'] }}">
                                        {{ $lbl['name'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <!-- Nomor Surat (KHUSUS AGENDA RAPAT - PERSIS DI BAWAH GRID TANGGAL/LOKASI) -->
                    @if($agenda->kategori === 'rapat')
                        <div class="p-3.5 sm:p-4 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Nomor Surat</h3>
                                @if($isSecretaryOfAgenda || Auth::user()->isAdmin())
                                    <button type="button" @click="openNomorSuratModal = true" class="text-[10.5px] font-extrabold text-[#1b3bbb] hover:text-indigo-800 flex items-center gap-1 bg-[#1b3bbb]/10 hover:bg-[#1b3bbb]/20 px-2.5 py-1 rounded-xl border border-[#1b3bbb]/20 transition-all cursor-pointer shrink-0">
                                        <svg class="w-3.5 h-3.5 text-[#1b3bbb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        <span>{{ $agenda->nomor_surat_dasar ? 'Edit Nomor Surat' : '+ Isi Nomor Surat' }}</span>
                                    </button>
                                @endif
                            </div>
                            <p class="text-xs text-[#2e2552] font-semibold leading-relaxed">
                                @if($agenda->nomor_surat_dasar)
                                    {{ $agenda->nomor_surat_dasar }}
                                @else
                                    @if($isSecretaryOfAgenda || Auth::user()->isAdmin())
                                        <button type="button" @click="openNomorSuratModal = true" class="text-[#8e88dd] hover:text-[#1b3bbb] italic cursor-pointer hover:underline text-left">
                                            Belum diisi oleh Admin. Klik untuk mengisi...
                                        </button>
                                    @else
                                        <span class="text-[#8e88dd] italic">Belum diisi oleh Admin.</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    <!-- QUICK MODAL: EDIT NOMOR SURAT (KHUSUS RAPAT) -->
    @if($agenda->kategori === 'rapat' && ($isSecretaryOfAgenda || Auth::user()->isAdmin()))
        <div x-show="openNomorSuratModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
            <div @click.away="openNomorSuratModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden relative text-[#2e2552] animate-in fade-in zoom-in duration-200">
                <div class="px-5 py-3.5 sm:py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0 rounded-t-3xl">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-white">Isi / Ubah Nomor Surat</h3>
                    <p class="text-[10px] sm:text-xs text-[#d4d1f5] font-medium mt-0.5">Lengkapi nomor surat pelaksanaan agenda</p>
                </div>
                <button type="button" @click="openNomorSuratModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
                <form action="{{ route('agenda.update-nomor-surat', $agenda->id) }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-1.5">
                        <label for="quick_nomor_surat_dasar" class="block text-xs font-bold uppercase tracking-wider text-[#5a508f]">
                            Nomor Surat Dasar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nomor_surat_dasar" id="quick_nomor_surat_dasar" required 
                               value="{{ old('nomor_surat_dasar', $agenda->nomor_surat_dasar) }}" 
                               placeholder="Contoh: 005/123/2026 Perihal Undangan Rapat Evaluasi SPBE" 
                               class="w-full px-3.5 py-2.5 bg-[#f8f7ff] border border-[#d4d1f5] rounded-xl text-xs font-medium text-[#2e2552] focus:ring-2 focus:ring-[#1b3bbb] focus:bg-white focus:outline-none transition-all">
                        <p class="text-[10px] text-slate-500 font-medium">Nomor surat ini otomatis dicantumkan di notulensi & dokumen PDF/Word.</p>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="openNomorSuratModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-[#1b3bbb] hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-[#1b3bbb]/20 transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Simpan Nomor Surat</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Right Column: Absensi Digital, Notulensi & Rekap Kehadiran Bidang (Hanya Tampil Jika Rapat ATAU Membutuhkan Presensi) -->
    @if($agenda->kategori === 'rapat' || $agenda->butuh_presensi)
        @php
            $hasAbsensiCard = (bool)$agenda->butuh_presensi;
            $hasNotulensiCard = ($agenda->kategori === 'rapat' && (bool)$agenda->notulensi);
            $hasMultipleRightCards = $hasAbsensiCard && $hasNotulensiCard;
        @endphp
        <div class="flex flex-col gap-3.5 sm:gap-4.5 min-w-0 h-full">
            <!-- 1. ABSENSI DIGITAL (Pegawai Internal Mandiri) -->
            @if($agenda->butuh_presensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[24px] p-3.5 sm:p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Absensi Digital</h3>
                        <span class="text-[10px] font-bold text-[#5a508f] bg-[#f3f2fe] px-2.5 py-0.5 rounded-full border border-[#d4d1f5]/40">
                            Mandiri
                        </span>
                    </div>
                    
                    @if($ownPresensi)
                        @php
                            $statusColors = [
                                'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'izin' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'sakit' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'alfa' => 'bg-red-50 text-red-700 border-red-200'
                            ];
                            $statusLabels = [
                                'hadir' => 'Kehadiran Anda: Hadir ✓',
                                'izin' => 'Kehadiran Anda: Izin Terdaftar ✓',
                                'sakit' => 'Kehadiran Anda: Sakit Terdaftar ✓',
                                'alfa' => 'Kehadiran Anda: Alfa (Tidak Hadir) ✓'
                            ];
                        @endphp
                        <div class="space-y-3">
                            <div class="w-full text-center py-2.5 sm:py-3 border rounded-xl text-xs font-bold {{ $statusColors[$ownPresensi->status] }}">
                                {{ $statusLabels[$ownPresensi->status] }}
                            </div>
                            @if($ownPresensi->keterangan)
                                <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-3 text-xs">
                                    <span class="block font-bold text-[#2e2552] uppercase text-[9px] tracking-wider mb-1">Catatan Keterangan:</span>
                                    <p class="text-slate-700 leading-relaxed">{{ $ownPresensi->keterangan }}</p>
                                </div>
                            @endif
                            @if($ownPresensi->tanda_tangan)
                                <div class="bg-[#fcfbff] border border-slate-200/60 rounded-xl p-3 text-xs flex flex-col items-center">
                                    <span class="w-full block font-bold text-[#2e2552] uppercase text-[9px] tracking-wider mb-1 text-left">Tanda Tangan Digital:</span>
                                    <div class="border border-slate-100 rounded-lg p-1.5 bg-white mt-1 flex items-center justify-center h-14 w-32 overflow-hidden shadow-inner">
                                        <img src="{{ asset('storage/' . $ownPresensi->tanda_tangan) }}" alt="Tanda Tangan" class="max-h-full max-w-full object-contain">
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        @if($agenda->isPresensiNotStarted())
                            <!-- KONDISI 1: Sebelum waktu mulai rapat -->
                            <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-2.5 sm:p-4 space-y-2">
                                <div class="flex items-start gap-2">
                                    <div class="p-1 bg-amber-100 text-amber-700 rounded-lg shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="space-y-0.5 text-left">
                                        <h4 class="text-[10.5px] font-bold text-amber-900 leading-tight">Absensi Belum Dibuka</h4>
                                        <p class="text-[9.5px] text-amber-700/90 leading-relaxed font-medium">
                                            Absensi dapat dilakukan saat rapat dimulai ({{ $agenda->tanggal ? $agenda->tanggal->translatedFormat('d F Y') : '' }} jam {{ substr($agenda->jam_mulai, 0, 5) }} WIB).
                                        </p>
                                    </div>
                                </div>
                                <div class="border-t border-amber-200/40"></div>
                                <button disabled class="w-full py-1.5 sm:py-2.5 bg-slate-100 border border-slate-200 text-slate-400 font-bold text-[11px] sm:text-xs rounded-xl cursor-not-allowed flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>Absensi Belum Dibuka</span>
                                </button>
                            </div>
                        @elseif($agenda->isPresensiExpired())
                            <!-- KONDISI 4: Lebih dari 1 jam setelah rapat selesai -->
                            <div class="bg-red-50/60 border border-red-200/80 rounded-xl p-2.5 sm:p-4 space-y-2">
                                <div class="flex items-start gap-2">
                                    <div class="p-1 bg-red-100 text-red-600 rounded-lg shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="space-y-0.5 text-left">
                                        <h4 class="text-[10.5px] font-bold text-red-800 leading-tight">Absensi Telah Ditutup</h4>
                                        <p class="text-[9.5px] text-red-600/90 leading-relaxed font-medium">Batas waktu pengisian presensi mandiri (1 jam setelah rapat selesai) telah berakhir.</p>
                                    </div>
                                </div>
                                <div class="border-t border-red-200/40"></div>
                                <div class="flex items-center justify-between text-xs bg-red-100/60 border border-red-200/50 rounded-xl px-2.5 py-1.5">
                                    <span class="text-[8.5px] font-bold text-red-700 uppercase tracking-wider">Status Kehadiran:</span>
                                    <span class="text-[9.5px] font-black text-white bg-red-600 px-2 py-0.5 rounded-md">ALFA</span>
                                </div>
                            </div>
                        @elseif($agenda->isPresensiInGracePeriod())
                            <!-- KONDISI 3: Setelah rapat selesai tetapi masih dalam toleransi 1 jam -->
                            <div class="space-y-2">
                                <div class="p-2.5 bg-amber-50 border border-amber-200/80 rounded-xl space-y-1 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="text-amber-900 font-bold flex items-center gap-1 text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>Masa Toleransi Absensi</span>
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-extrabold uppercase text-[8px] border border-amber-300">Toleransi 1 Jam</span>
                                    </div>
                                    <p class="text-[9.5px] text-amber-700 leading-relaxed font-medium">
                                        Jadwal rapat telah selesai ({{ substr($agenda->jam_selesai, 0, 5) }} WIB). Anda masih dapat melakukan absensi hingga jam {{ \Carbon\Carbon::parse($agenda->tanggal->toDateString() . ' ' . $agenda->jam_selesai)->addHour()->format('H:i') }} WIB.
                                    </p>
                                </div>
                                <button @click="openAbsenModal = true; initSignaturePad()" 
                                        class="w-full py-2.5 sm:py-3 bg-gradient-to-r from-[#1b3bbb] to-[#0b1554] hover:from-[#0b1554] hover:to-[#1b3bbb] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md shadow-[#1b3bbb]/20 hover:scale-[1.01] active:scale-95 transition-all duration-300 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Isi Presensi Kehadiran</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                            </div>
                        @else
                            <!-- KONDISI 2: Berlangsung normal saat waktu rapat -->
                            <div class="space-y-2">
                                <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-xl flex items-center justify-between text-xs">
                                    <span class="text-rose-800 font-medium flex items-center gap-1 text-[10px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        <span>Status Kehadiran:</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-700 font-extrabold uppercase text-[9px] border border-rose-300">Belum Absen</span>
                                </div>
                                <button @click="openAbsenModal = true; initSignaturePad()" 
                                        class="w-full py-2.5 sm:py-3 bg-gradient-to-r from-[#1b3bbb] to-[#0b1554] hover:from-[#0b1554] hover:to-[#1b3bbb] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md shadow-[#1b3bbb]/20 hover:scale-[1.01] active:scale-95 transition-all duration-300 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Isi Presensi Kehadiran</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <!-- 2. REKAP KEHADIRAN BIDANG (Hanya untuk Non-Rapat: Sosialisasi, Pelatihan, dll - Tepat di bawah Card Absensi Digital) -->
            @if($agenda->kategori !== 'rapat')
                @if($agenda->butuh_presensi && $isSecretaryOfAgenda)
                    <div x-data="{ showAllRecap: false }" class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[24px] p-3.5 sm:p-5 shadow-sm flex-1 flex flex-col justify-between space-y-3">
                        <div class="space-y-3">
                            <div class="border-b border-[#d4d1f5]/40 pb-2">
                                <h3 class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-[#2e2552]">Rekap Kehadiran Bidang</h3>
                            </div>
                            
                            <div class="space-y-2">
                                @foreach($recap as $index => $rc)
                                    <div x-show="showAllRecap || {{ $index }} < 3"
                                         @click="showBidangDetails({{ $rc->bidang_id }}, '{{ addslashes($rc->bidang_nama) }}')" 
                                         class="p-2.5 bg-[#f8f7ff] border border-[#d4d1f5]/40 hover:border-[#8e88dd] hover:bg-[#f3f2fe] rounded-xl text-xs space-y-1.5 cursor-pointer transition-all duration-200 shadow-2xs group">
                                        <div class="font-bold text-[#2e2552] flex items-center justify-between gap-2">
                                            <span class="truncate group-hover:text-[#1b3bbb] transition-colors font-extrabold text-[11px] sm:text-xs">{{ $rc->bidang_nama }}</span>
                                            <span class="text-[8.5px] text-[#1b3bbb] font-bold uppercase tracking-wider flex items-center gap-0.5 shrink-0 bg-white px-1.5 py-0.5 rounded-md border border-[#d4d1f5]/60 shadow-2xs">
                                                <span>Detail</span>
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-5 gap-1 text-center text-[8.5px] font-bold">
                                            <div class="bg-emerald-50 text-emerald-600 py-0.5 rounded border border-emerald-100">Hadir: {{ $rc->hadir }}</div>
                                            <div class="bg-amber-50 text-amber-600 py-0.5 rounded border border-amber-100">Izin: {{ $rc->izin }}</div>
                                            <div class="bg-rose-50 text-rose-600 py-0.5 rounded border border-rose-100">Sakit: {{ $rc->sakit }}</div>
                                            <div class="bg-red-50 text-red-600 py-0.5 rounded border border-red-100">Alfa: {{ $rc->alfa }}</div>
                                            <div class="bg-slate-100 text-slate-500 py-0.5 rounded border border-slate-200">Belum: {{ $rc->belum }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if(count($recap) > 3)
                            <button type="button" @click="showAllRecap = !showAllRecap" 
                                    class="w-full py-1.5 px-3 text-[10.5px] font-extrabold text-[#1b3bbb] hover:text-[#09103c] bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5]/60 rounded-xl transition-all flex items-center justify-center gap-1 cursor-pointer shadow-2xs mt-auto">
                                <span x-text="showAllRecap ? 'Sembunyikan Bidang Lainnya' : 'Tampilkan Semua Bidang ({{ count($recap) }})'"></span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="showAllRecap ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif
            @endif

            <!-- 3. STATUS NOTULENSI AI (Hanya Rapat yang Memiliki Notulensi) -->
            @if($agenda->kategori === 'rapat' && $agenda->notulensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[24px] p-3.5 sm:p-5 shadow-sm flex-1 flex flex-col justify-between space-y-3">
                    <h3 class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-[#2e2552]">Dokumentasi Notulensi</h3>
                    
                    @php
                        $notulensi = $agenda->notulensi;
                        $hasAudio = !empty($notulensi->audio_path) || (!empty($notulensi->audio_files) && count($notulensi->audio_files) > 0);
                        $isTranscribing = $notulensi->is_transcribing && $hasAudio;

                        $hasDraftContent = !empty($notulensi->ringkasan) 
                            || !empty($notulensi->transkrip_raw) 
                            || !empty($notulensi->pembahasan) 
                            || $hasAudio;

                        if ($isTranscribing) {
                            $notulenLabel = 'Proses Transkripsi';
                            $notulenColor = 'bg-sky-50 text-sky-600 border-sky-200';
                        } elseif ($notulensi->transkrip_error) {
                            $notulenLabel = 'Gagal Transkripsi';
                            $notulenColor = 'bg-rose-50 text-rose-600 border-rose-200';
                        } elseif ($notulensi->status === 'revisi') {
                            $notulenLabel = 'Perlu Revisi Admin';
                            $notulenColor = 'bg-rose-50 text-rose-700 border-rose-200';
                        } elseif ($notulensi->status === 'draft') {
                            if ($hasDraftContent) {
                                $notulenLabel = 'Draft Belum Direview';
                                $notulenColor = 'bg-blue-50 text-blue-600 border-blue-200';
                            } else {
                                $notulenLabel = 'Belum Ada Draft';
                                $notulenColor = 'bg-slate-100 text-slate-500 border-slate-200';
                            }
                        } elseif ($notulensi->status === 'menunggu_review') {
                            $notulenLabel = 'Menunggu Review Pimpinan';
                            $notulenColor = 'bg-amber-50 text-amber-700 border-amber-200';
                        } elseif ($notulensi->status === 'disahkan') {
                            $notulenLabel = 'Telah Disahkan Pimpinan';
                            $notulenColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        } else {
                            $notulenLabel = strtoupper($notulensi->status);
                            $notulenColor = 'bg-slate-50 text-slate-600 border-slate-200';
                        }
                    @endphp
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-2">
                            <span class="text-[11px] sm:text-xs text-[#5a508f]">Status Notulen:</span>
                            <span class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase {{ $notulenColor }}">
                                {{ $notulenLabel }}
                            </span>
                        </div>

                        @if($agenda->notulensi->status === 'revisi')
                            @if($agenda->notulensi->catatan_revisi)
                                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-xl text-xs space-y-1">
                                    <p class="font-extrabold flex items-center gap-1.5 text-rose-700">
                                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Catatan Revisi Pimpinan:</span>
                                    </p>
                                    <p class="italic text-[#2e2552] font-medium pl-5.5">"{{ $agenda->notulensi->catatan_revisi }}"</p>
                                </div>
                            @endif

                            @if($isSecretaryOfAgenda)
                                <a href="{{ route('notulensi.edit', $agenda->id) }}" 
                                   class="w-full py-1.5 sm:py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5">
                                    <span>Perbaiki & Edit Notulensi</span>
                                </a>
                            @else
                                <p class="text-xs text-rose-700 text-center py-2 italic font-medium">
                                    Draf notulensi perlu perbaikan dan sedang dikembalikan ke admin.
                                </p>
                            @endif
                        @elseif($agenda->notulensi->status === 'draft')
                            <div class="bg-[#f8f7ff] border border-[#d4d1f5]/50 rounded-2xl p-3 sm:p-3.5 space-y-1">
                                <div class="flex items-start gap-2.5">
                                    <div class="p-1.5 bg-[#1b3bbb]/10 text-[#1b3bbb] rounded-xl shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="space-y-0.5 text-left">
                                        <h4 class="text-xs font-extrabold text-[#2e2552]">
                                            {{ $hasDraftContent ? 'Draf Notulensi Tersedia' : 'Informasi Dokumentasi' }}
                                        </h4>
                                        <p class="text-[10.5px] text-[#5a508f] font-medium leading-normal">
                                            {{ $hasDraftContent ? 'Draf notulensi rapat telah diisi/diunggah.' : 'Notulensi rapat belum diisi atau diunggah.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @if($isSecretaryOfAgenda)
                                <a href="{{ route('notulensi.edit', $agenda->id) }}" 
                                   class="w-full py-2.5 sm:py-3 bg-gradient-to-r from-[#1b3bbb] to-[#0b1554] hover:from-[#0b1554] hover:to-[#1b3bbb] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md shadow-[#1b3bbb]/20 hover:scale-[1.01] active:scale-95 transition-all duration-300 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Kelola & Edit Notulen</span>
                                </a>
                            @elseif($hasDraftContent)
                                <p class="text-xs text-[#8e88dd] text-center py-1.5 italic font-medium">
                                    Draf notulensi rapat sedang dirapikan oleh admin.
                                </p>
                            @endif
                        @elseif($agenda->notulensi->status === 'menunggu_review')
                            @if($isApproverOfAgenda)
                                <div class="space-y-2.5">
                                    <div class="bg-amber-50/80 border border-amber-200/80 rounded-xl p-3 space-y-1">
                                        <p class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Memerlukan Persetujuan Anda</span>
                                        </p>
                                        <p class="text-[10.5px] text-amber-800 font-medium leading-relaxed">
                                            Draf notulensi telah diajukan dan siap untuk Anda tinjau dan sahkan.
                                        </p>
                                    </div>
                                    <a href="{{ route('notulensi.review', $agenda->id) }}" 
                                       class="w-full py-2 sm:py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Tinjau & Sahkan Notulensi</span>
                                    </a>
                                </div>
                            @elseif($isSecretaryOfAgenda)
                                <div class="space-y-2">
                                    <a href="{{ route('notulensi.edit', $agenda->id) }}" 
                                       class="w-full py-2.5 sm:py-3 bg-gradient-to-r from-[#1b3bbb] to-[#0b1554] hover:from-[#0b1554] hover:to-[#1b3bbb] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md shadow-[#1b3bbb]/20 hover:scale-[1.01] active:scale-95 transition-all duration-300 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <span>Kelola & Edit Notulensi</span>
                                    </a>
                                    <a href="{{ route('notulensi.review', $agenda->id) }}" 
                                       class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-[#2e2552] font-bold text-[11px] sm:text-xs rounded-xl border border-[#d4d1f5] transition-all flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#5a508f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Draf Notulensi</span>
                                    </a>
                                </div>
                            @else
                                <div class="space-y-2 p-3 bg-amber-50/60 border border-amber-200/60 rounded-xl text-center">
                                    <p class="text-[11px] font-bold text-amber-900 flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Notulensi Sedang Ditinjau</span>
                                    </p>
                                    <p class="text-[10.5px] text-amber-800 font-medium">
                                        Notulensi rapat dalam proses verifikasi & pengesahan Pimpinan. Dapat diakses setelah resmi disahkan.
                                    </p>
                                </div>
                            @endif
                        @elseif($agenda->notulensi->status === 'disahkan')
                            <div class="space-y-2">
                                <a href="{{ route('notulensi.review', $agenda->id) }}" 
                                   class="w-full py-2.5 sm:py-3 bg-gradient-to-r from-[#1b3bbb] to-[#0b1554] hover:from-[#0b1554] hover:to-[#1b3bbb] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md shadow-[#1b3bbb]/20 hover:scale-[1.01] active:scale-95 transition-all duration-300 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    <span>Lihat Notulensi Rapat Resmi</span>
                                </a>

                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('notulensi.export.pdf', $agenda->id) }}" target="_blank" data-no-pjax
                                       class="py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-extrabold text-[11px] rounded-xl border border-rose-200 transition-all flex items-center justify-center gap-1.5 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>Unduh PDF</span>
                                    </a>

                                    <a href="{{ route('notulensi.export.docx', $agenda->id) }}" target="_blank" data-no-pjax
                                       class="py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-extrabold text-[11px] rounded-xl border border-blue-200 transition-all flex items-center justify-center gap-1.5 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>Unduh Word</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
    </div>

    <!-- BOTTOM SECTION: CONDITIONAL LAYOUT FOR RAPAT VS NON-RAPAT (KHUSUS SEKRETARIS PEMBUAT AGENDA) -->
    @if($agenda->butuh_presensi && $isSecretaryOfAgenda)
        @if($agenda->kategori === 'rapat')
            <!-- KHUSUS AGENDA "RAPAT": DUAL COLUMN (REKAP BIDANG KIRI & KOREKSI PRESENSI KANAN) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-stretch">
                <!-- LEFT COLUMN: REKAP KEHADIRAN BIDANG -->
                <div class="lg:col-span-5 bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-4 sm:p-6 shadow-sm flex flex-col justify-between h-full space-y-3 sm:space-y-4">
                    <div class="space-y-3 flex-1 flex flex-col">
                        <div class="border-b border-[#d4d1f5]/40 pb-2.5 flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-[#2e2552]">Rekap Kehadiran Bidang</h3>
                            <span class="text-[10px] bg-[#1b3bbb]/10 text-[#1b3bbb] font-extrabold px-2.5 py-0.5 rounded-full border border-[#1b3bbb]/20">
                                {{ count($recap) }} Bidang
                            </span>
                        </div>
                        
                        <div class="space-y-2 max-h-[460px] overflow-y-auto pr-1 flex-1">
                            @foreach($recap as $rc)
                                <div @click="showBidangDetails({{ $rc->bidang_id }}, '{{ addslashes($rc->bidang_nama) }}')" 
                                     class="p-3 bg-[#f8f7ff] border border-[#d4d1f5]/40 hover:border-[#8e88dd] hover:bg-[#f3f2fe] rounded-2xl text-xs space-y-1.5 cursor-pointer transition-all duration-200 shadow-2xs group">
                                    <div class="font-bold text-[#2e2552] flex items-center justify-between gap-2">
                                        <span class="truncate group-hover:text-[#1b3bbb] transition-colors font-extrabold text-xs sm:text-sm">{{ $rc->bidang_nama }}</span>
                                        <span class="text-[9px] text-[#1b3bbb] font-extrabold uppercase tracking-wider flex items-center gap-0.5 shrink-0 bg-white px-2 py-0.5 rounded-lg border border-[#d4d1f5]/70 shadow-2xs">
                                            <span>Detail</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-5 gap-1.5 text-center text-[9px] font-bold">
                                        <div class="bg-emerald-50 text-emerald-600 py-1 rounded-xl border border-emerald-100">Hadir: {{ $rc->hadir }}</div>
                                        <div class="bg-amber-50 text-amber-600 py-1 rounded-xl border border-amber-100">Izin: {{ $rc->izin }}</div>
                                        <div class="bg-rose-50 text-rose-600 py-1 rounded-xl border border-rose-100">Sakit: {{ $rc->sakit }}</div>
                                        <div class="bg-red-50 text-red-600 py-1 rounded-xl border border-red-100">Alfa: {{ $rc->alfa }}</div>
                                        <div class="bg-slate-100 text-slate-500 py-1 rounded-xl border border-slate-200">Belum: {{ $rc->belum }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: KOREKSI PRESENSI PEGAWAI -->
                <div class="lg:col-span-7 bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-4 sm:p-6 shadow-sm flex flex-col justify-between h-full space-y-3 sm:space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#d4d1f5]/40 pb-3">
                        <div>
                            <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-[#2e2552]">Koreksi Presensi Pegawai</h3>
                            <p class="text-[10.5px] sm:text-xs text-[#5a508f] font-medium mt-0.5">Ubah status presensi pegawai atau tambahkan tamu eksternal secara manual.</p>
                        </div>
                        @if(Auth::user()->isAdmin() || $isSecretaryOfAgenda)
                            <button @click="openGuestModal = true" class="px-3 py-1.5 sm:px-4 sm:py-2.5 bg-[#1b3bbb] hover:bg-[#09103c] text-white text-[11px] sm:text-xs font-bold rounded-xl transition-all shadow-md shadow-[#1b3bbb]/20 flex items-center justify-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                <span>Tamu Eksternal</span>
                            </button>
                        @endif
                    </div>
                    
                    <div class="max-h-[460px] overflow-y-auto pr-1 space-y-4">
                        @php
                            $groupedParticipants = $participants->groupBy(function($part) {
                                return ($part->role === 'ketua_master' || !$part->bidang_id) ? 0 : $part->bidang_id;
                            })->sortBy(function($parts, $bidangId) {
                                if ($bidangId == 0) return -1;
                                $bid = $parts->first()->bidang;
                                return $bid ? $bid->id : $bidangId;
                            });
                        @endphp

                        @forelse($groupedParticipants as $bidId => $groupParts)
                            @php
                                if ($bidId == 0) {
                                    $groupTitle = 'Kepala Dinas';
                                } else {
                                    $firstBid = $groupParts->first()->bidang;
                                    $groupTitle = $firstBid ? $firstBid->nama : 'Lainnya';
                                }
                            @endphp

                            <div class="bg-slate-50/70 border border-[#d4d1f5]/80 rounded-2xl p-3.5 space-y-3 shadow-2xs">
                                <div class="flex items-center justify-between pb-2 border-b border-[#d4d1f5]/50">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#1b3bbb] shrink-0"></span>
                                        <h4 class="text-xs font-black text-[#09103c]">{{ $groupTitle }}</h4>
                                    </div>
                                    <span class="text-[10px] bg-[#2e2552]/10 text-[#2e2552] px-2.5 py-0.5 rounded-full font-bold">
                                        {{ count($groupParts) }} Pegawai
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($groupParts as $part)
                                        <div class="flex items-center justify-between gap-3 p-3 bg-white border border-[#d4d1f5]/40 hover:border-[#8e88dd]/60 rounded-2xl shadow-xs transition-all">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-bold text-[#2e2552] truncate" title="{{ $part->name }}">{{ $part->name }}</div>
                                                <div class="text-[10px] text-[#5a508f] truncate font-medium mt-0.5" title="{{ $part->jabatan }}">{{ $part->jabatan }}</div>
                                            </div>
                                            @if(Auth::user()->isAdmin() || $isSecretaryOfAgenda)
                                                <div x-data="{ currentStatus: '{{ $part->status_presensi ?: 'Belum Absen' }}', openStatus: false, isLoading: false }" @click.outside="openStatus = false" class="relative shrink-0">
                                                    <form action="{{ route('agenda.absen.koreksi', $agenda->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $part->id }}">
                                                        <input type="hidden" name="status" :value="currentStatus">
                                                        <button type="button" @click="openStatus = !openStatus" :disabled="isLoading"
                                                                class="px-2.5 py-1.5 bg-white hover:bg-slate-50 border border-[#d4d1f5] hover:border-[#1b3bbb] rounded-xl text-[#09103c] text-[11px] font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs disabled:opacity-75 disabled:cursor-wait">
                                                            <template x-if="isLoading">
                                                                <svg class="w-3 h-3 text-[#1b3bbb] animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                </svg>
                                                            </template>
                                                            <span class="capitalize" x-text="isLoading ? 'Memproses...' : (currentStatus === 'hadir' ? 'Hadir' : (currentStatus === 'izin' ? 'Izin' : (currentStatus === 'sakit' ? 'Sakit' : (currentStatus === 'alfa' ? 'Alfa' : 'Belum Absen'))))"></span>
                                                            <svg x-show="!isLoading" class="w-3 h-3 text-[#1b3bbb] transition-transform duration-200" :class="openStatus ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                        </button>
                                                        <div x-show="openStatus" x-cloak 
                                                             x-transition:enter="transition ease-out duration-150 transform" 
                                                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1" 
                                                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                                                             x-transition:leave="transition ease-in duration-100 transform" 
                                                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                                                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1" 
                                                             class="absolute right-0 top-full mt-1 w-36 bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 overflow-hidden">
                                                            <div class="space-y-0.5">
                                                                <template x-for="st in [
                                                                    { value: 'Belum Absen', label: 'Belum Absen' },
                                                                    { value: 'hadir', label: 'Hadir' },
                                                                    { value: 'izin', label: 'Izin' },
                                                                    { value: 'sakit', label: 'Sakit' },
                                                                    { value: 'alfa', label: 'Alfa' }
                                                                ]" :key="st.value">
                                                                    <button type="button" 
                                                                            @click="
                                                                                if (currentStatus !== st.value) {
                                                                                    currentStatus = st.value; 
                                                                                    openStatus = false; 
                                                                                    isLoading = true;
                                                                                    if (typeof Swal !== 'undefined') {
                                                                                        Swal.fire({
                                                                                            title: 'Memperbarui Presensi...',
                                                                                            text: 'Mohon tunggu sebentar',
                                                                                            allowOutsideClick: false,
                                                                                            showConfirmButton: false,
                                                                                            didOpen: () => { Swal.showLoading(); }
                                                                                        });
                                                                                    }
                                                                                    $nextTick(() => $el.closest('form').submit());
                                                                                } else {
                                                                                    openStatus = false;
                                                                                }
                                                                            " 
                                                                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-[11px] font-semibold transition-colors"
                                                                            :class="currentStatus === st.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                                                        <span x-text="st.label"></span>
                                                                        <svg x-show="currentStatus === st.value" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-[10px] px-2.5 py-1 rounded-xl font-bold uppercase border {{ $part->status_presensi === 'hadir' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($part->status_presensi === 'izin' ? 'bg-amber-50 text-amber-700 border-amber-200' : ($part->status_presensi === 'sakit' ? 'bg-rose-50 text-rose-700 border-rose-200' : ($part->status_presensi === 'alfa' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-slate-100 text-slate-600 border-slate-200'))) }}">
                                                    {{ $part->status_presensi ?: 'Belum' }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            @if($externalParticipants->isEmpty())
                                <div class="p-6 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-xs text-slate-500 font-bold">Belum ada peserta terdaftar.</p>
                                </div>
                            @endif
                        @endforelse

                        @if($externalParticipants->isNotEmpty())
                            <div class="bg-slate-50/70 border border-[#d4d1f5]/80 rounded-2xl p-3.5 space-y-3 shadow-2xs">
                                <div class="flex items-center justify-between pb-2 border-b border-[#d4d1f5]/50">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#1b3bbb] shrink-0"></span>
                                        <h4 class="text-xs font-black text-[#09103c]">Tamu Eksternal</h4>
                                    </div>
                                    <span class="text-[10px] bg-[#8e88dd]/20 text-[#2e2552] px-2.5 py-0.5 rounded-full font-bold">
                                        {{ count($externalParticipants) }} Tamu
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($externalParticipants as $guest)
                                        <div class="flex items-center justify-between gap-3 p-3 bg-[#f0effd] border border-[#d4d1f5]/60 rounded-2xl shadow-xs">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 min-w-0">
                                                    <div class="text-xs font-bold text-[#2e2552] truncate" title="{{ $guest->nama }}">{{ $guest->nama }}</div>
                                                    <span class="inline-block shrink-0 px-1.5 py-0.5 bg-[#8e88dd]/20 text-[#2e2552] text-[8px] font-black rounded uppercase tracking-wider">Tamu</span>
                                                </div>
                                                <div class="text-[10px] text-[#5a508f] truncate font-medium mt-0.5" title="{{ $guest->jabatan }} - {{ $guest->instansi }}">
                                                    {{ $guest->jabatan }} di <strong>{{ $guest->instansi }}</strong>
                                                </div>
                                            </div>
                                            @if($isSecretaryOfAgenda)
                                                <form action="{{ route('notulensi.external.delete', $guest->id) }}" method="POST" class="shrink-0" data-title="Hapus Tamu Eksternal?" data-confirm="Data tamu eksternal ini akan dihapus dari daftar presensi rapat." data-confirm-btn="Hapus Tamu">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-500 p-1.5 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus Tamu">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
        @else
            <!-- KEGIATAN LAINNYA (SOSIALISASI, PELATIHAN, DLL): FULL WIDTH KOREKSI PRESENSI PEGAWAI -->
            <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-4 sm:p-6 shadow-sm space-y-3 sm:space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#d4d1f5]/40 pb-3">
                    <div>
                        <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-[#2e2552]">Koreksi Presensi Pegawai</h3>
                        <p class="text-[10.5px] sm:text-xs text-[#5a508f] font-medium mt-0.5">Ubah status presensi pegawai atau tambahkan tamu eksternal secara manual.</p>
                    </div>
                    @if(Auth::user()->isAdmin() || $isSecretaryOfAgenda)
                        <button @click="openGuestModal = true" class="px-3 py-1.5 sm:px-4 sm:py-2.5 bg-[#1b3bbb] hover:bg-[#09103c] text-white text-[11px] sm:text-xs font-bold rounded-xl transition-all shadow-md shadow-[#1b3bbb]/20 flex items-center justify-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <span>Tamu Eksternal</span>
                        </button>
                    @endif
                </div>
                
                <div class="max-h-[460px] overflow-y-auto pr-1 space-y-4">
                    @php
                        $groupedParticipants = $participants->groupBy(function($part) {
                            return ($part->role === 'ketua_master' || !$part->bidang_id) ? 0 : $part->bidang_id;
                        })->sortBy(function($parts, $bidangId) {
                            if ($bidangId == 0) return -1;
                            $bid = $parts->first()->bidang;
                            return $bid ? $bid->id : $bidangId;
                        });
                    @endphp

                    @forelse($groupedParticipants as $bidId => $groupParts)
                        @php
                            if ($bidId == 0) {
                                $groupTitle = 'Kepala Dinas';
                            } else {
                                $firstBid = $groupParts->first()->bidang;
                                $groupTitle = $firstBid ? $firstBid->nama : 'Lainnya';
                            }
                        @endphp

                        <div class="bg-slate-50/70 border border-[#d4d1f5]/80 rounded-2xl p-3.5 space-y-3 shadow-2xs">
                            <div class="flex items-center justify-between pb-2 border-b border-[#d4d1f5]/50">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#1b3bbb] shrink-0"></span>
                                    <h4 class="text-xs font-black text-[#09103c]">{{ $groupTitle }}</h4>
                                </div>
                                <span class="text-[10px] bg-[#2e2552]/10 text-[#2e2552] px-2.5 py-0.5 rounded-full font-bold">
                                    {{ count($groupParts) }} Pegawai
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($groupParts as $part)
                                    <div class="flex items-center justify-between gap-3 p-3 bg-white border border-[#d4d1f5]/40 hover:border-[#8e88dd]/60 rounded-2xl shadow-xs transition-all">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-[#2e2552] truncate" title="{{ $part->name }}">{{ $part->name }}</div>
                                            <div class="text-[10px] text-[#5a508f] truncate font-medium mt-0.5" title="{{ $part->jabatan }}">{{ $part->jabatan }}</div>
                                        </div>
                                        @if(Auth::user()->isAdmin() || $isSecretaryOfAgenda)
                                            <div x-data="{ currentStatus: '{{ $part->status_presensi ?: 'Belum Absen' }}', openStatus: false, isLoading: false }" @click.outside="openStatus = false" class="relative shrink-0">
                                                <form action="{{ route('agenda.absen.koreksi', $agenda->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $part->id }}">
                                                    <input type="hidden" name="status" :value="currentStatus">
                                                    <button type="button" @click="openStatus = !openStatus" :disabled="isLoading"
                                                            class="px-2.5 py-1.5 bg-white hover:bg-slate-50 border border-[#d4d1f5] hover:border-[#1b3bbb] rounded-xl text-[#09103c] text-[11px] font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs disabled:opacity-75 disabled:cursor-wait">
                                                        <template x-if="isLoading">
                                                            <svg class="w-3 h-3 text-[#1b3bbb] animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </template>
                                                        <span class="capitalize" x-text="isLoading ? 'Memproses...' : (currentStatus === 'hadir' ? 'Hadir' : (currentStatus === 'izin' ? 'Izin' : (currentStatus === 'sakit' ? 'Sakit' : (currentStatus === 'alfa' ? 'Alfa' : 'Belum Absen'))))"></span>
                                                        <svg x-show="!isLoading" class="w-3 h-3 text-[#1b3bbb] transition-transform duration-200" :class="openStatus ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    </button>
                                                    <div x-show="openStatus" x-cloak 
                                                         x-transition:enter="transition ease-out duration-150 transform" 
                                                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1" 
                                                         x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                                                         x-transition:leave="transition ease-in duration-100 transform" 
                                                         x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                                                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1" 
                                                         class="absolute right-0 top-full mt-1 w-36 bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 overflow-hidden">
                                                        <div class="space-y-0.5">
                                                            <template x-for="st in [
                                                                { value: 'Belum Absen', label: 'Belum Absen' },
                                                                { value: 'hadir', label: 'Hadir' },
                                                                { value: 'izin', label: 'Izin' },
                                                                { value: 'sakit', label: 'Sakit' },
                                                                { value: 'alfa', label: 'Alfa' }
                                                            ]" :key="st.value">
                                                                <button type="button" 
                                                                        @click="
                                                                            if (currentStatus !== st.value) {
                                                                                currentStatus = st.value; 
                                                                                openStatus = false; 
                                                                                isLoading = true;
                                                                                if (typeof Swal !== 'undefined') {
                                                                                    Swal.fire({
                                                                                        title: 'Memperbarui Presensi...',
                                                                                        text: 'Mohon tunggu sebentar',
                                                                                        allowOutsideClick: false,
                                                                                        showConfirmButton: false,
                                                                                        didOpen: () => { Swal.showLoading(); }
                                                                                    });
                                                                                }
                                                                                $nextTick(() => $el.closest('form').submit());
                                                                            } else {
                                                                                openStatus = false;
                                                                            }
                                                                        " 
                                                                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-[11px] font-semibold transition-colors"
                                                                        :class="currentStatus === st.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                                                    <span x-text="st.label"></span>
                                                                    <svg x-show="currentStatus === st.value" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-[10px] px-2.5 py-1 rounded-xl font-bold uppercase border {{ $part->status_presensi === 'hadir' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($part->status_presensi === 'izin' ? 'bg-amber-50 text-amber-700 border-amber-200' : ($part->status_presensi === 'sakit' ? 'bg-rose-50 text-rose-700 border-rose-200' : ($part->status_presensi === 'alfa' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-slate-100 text-slate-600 border-slate-200'))) }}">
                                                {{ $part->status_presensi ?: 'Belum' }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        @if($externalParticipants->isEmpty())
                            <div class="p-6 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <p class="text-xs text-slate-500 font-bold">Belum ada peserta terdaftar.</p>
                            </div>
                        @endif
                    @endforelse

                    @if($externalParticipants->isNotEmpty())
                        <div class="bg-slate-50/70 border border-[#d4d1f5]/80 rounded-2xl p-3.5 space-y-3 shadow-2xs">
                            <div class="flex items-center justify-between pb-2 border-b border-[#d4d1f5]/50">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#1b3bbb] shrink-0"></span>
                                    <h4 class="text-xs font-black text-[#09103c]">Tamu Eksternal</h4>
                                </div>
                                <span class="text-[10px] bg-[#8e88dd]/20 text-[#2e2552] px-2.5 py-0.5 rounded-full font-bold">
                                    {{ count($externalParticipants) }} Tamu
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($externalParticipants as $guest)
                                    <div class="flex items-center justify-between gap-3 p-3 bg-[#f0effd] border border-[#d4d1f5]/60 rounded-2xl shadow-xs">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <div class="text-xs font-bold text-[#2e2552] truncate" title="{{ $guest->nama }}">{{ $guest->nama }}</div>
                                                <span class="inline-block shrink-0 px-1.5 py-0.5 bg-[#8e88dd]/20 text-[#2e2552] text-[8px] font-black rounded uppercase tracking-wider">Tamu</span>
                                            </div>
                                            <div class="text-[10px] text-[#5a508f] truncate font-medium mt-0.5" title="{{ $guest->jabatan }} - {{ $guest->instansi }}">
                                                {{ $guest->jabatan }} di <strong>{{ $guest->instansi }}</strong>
                                            </div>
                                        </div>
                                        @if($isSecretaryOfAgenda)
                                            <form action="{{ route('notulensi.external.delete', $guest->id) }}" method="POST" class="shrink-0" data-title="Hapus Tamu Eksternal?" data-confirm="Data tamu eksternal ini akan dihapus dari daftar presensi rapat." data-confirm-btn="Hapus Tamu">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-500 p-1.5 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus Tamu">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif

    <!-- DETAIL MODAL FOR ATTENDEES TABLE -->
    @if(Auth::user()->role !== 'staff' || Auth::user()->isSekretariat())
    <div x-show="openDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="openDetailModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden relative text-[#2e2552]">
            <div class="px-5 py-3.5 sm:py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0 rounded-t-3xl">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-white">Detail Kehadiran Rapat</h3>
                    <p class="text-[10px] sm:text-xs text-[#d4d1f5] font-medium mt-0.5" x-text="selectedBidangName"></p>
                </div>
                <button type="button" @click="openDetailModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <table class="w-full text-left text-xs text-[#2e2552]">
                    <thead class="bg-[#ebf2ff] text-[#1b3bbb] border-y border-[#bfd5ff] select-none">
                        <tr>
                            <th class="py-2.5 px-3 font-black uppercase tracking-wider">Nama Pegawai</th>
                            <th class="py-2.5 px-3 font-black uppercase tracking-wider">Jabatan</th>
                            <th class="py-2.5 px-3 font-black uppercase tracking-wider text-center">Status</th>
                            <th class="py-2.5 px-3 font-black uppercase tracking-wider">Keterangan</th>
                            <th class="py-2.5 px-3 font-black uppercase tracking-wider text-center">TTD</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d4d1f5]/20">
                        <template x-for="p in detailParticipants" :key="p.id">
                            <tr class="hover:bg-[#f8f7ff] transition-colors">
                                <td class="py-3 px-3 font-bold">
                                    <div x-text="p.name"></div>
                                    <div class="font-mono text-[9px] text-[#5a508f] mt-0.5" x-text="p.nip"></div>
                                </td>
                                <td class="py-3 px-3 text-slate-700" x-text="p.jabatan"></td>
                                <td class="py-3 px-3 text-center font-bold">
                                    <span class="inline-block px-2 py-0.5 rounded-lg border text-[9px] uppercase tracking-wider font-extrabold"
                                          :class="{
                                              'bg-emerald-50 text-emerald-600 border-emerald-200': p.status_presensi === 'hadir',
                                              'bg-amber-50 text-amber-600 border-amber-200': p.status_presensi === 'izin',
                                              'bg-rose-50 text-rose-600 border-rose-200': p.status_presensi === 'sakit',
                                              'bg-red-50 text-red-600 border-red-200': p.status_presensi === 'alfa',
                                              'bg-slate-100 text-slate-400 border-slate-200': p.status_presensi === 'Belum Absen'
                                          }"
                                          x-text="p.status_presensi === 'Belum Absen' ? 'Belum Absen' : (p.status_presensi === 'alfa' ? 'Alfa' : p.status_presensi)">
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-500 italic text-[11px]" x-text="p.keterangan || '-'"></td>
                                <td class="py-2 px-3 text-center">
                                    <template x-if="p.tanda_tangan">
                                        <div class="inline-flex items-center justify-center p-1 bg-white border border-slate-200 rounded-lg h-9 w-14 overflow-hidden shadow-sm">
                                            <img :src="'/storage/' + p.tanda_tangan" alt="TTD" class="max-h-full max-w-full object-contain">
                                        </div>
                                    </template>
                                    <template x-if="!p.tanda_tangan">
                                        <span class="text-slate-400">-</span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="detailParticipants.length === 0">
                            <td colspan="5" class="py-6 text-center text-[#8e88dd] italic font-medium">Tidak ada pegawai terdaftar di bidang ini.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 bg-[#f8f7ff] border-t border-[#d4d1f5]/40 flex justify-end">
                <button @click="openDetailModal = false" class="px-4 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl shadow-sm">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    @if($isSecretaryOfAgenda)
    <!-- MODAL: DAFTAR HADIR TAMU EKSTERNAL -->
    <div x-show="openGuestModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="openGuestModal = false" 
             class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden relative text-[#2e2552]"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
             
            <div class="px-5 py-3.5 sm:py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0 rounded-t-3xl">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-white">Tambah Tamu Eksternal</h3>
                    <p class="text-[10px] sm:text-xs text-[#d4d1f5] font-medium mt-0.5">Masukkan nama undangan dari luar Dinkominfo</p>
                </div>
                <button type="button" @click="openGuestModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('notulensi.external.add', $agenda->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="space-y-1">
                    <label for="ext_nama_show" class="block text-xs font-bold text-[#5a508f] uppercase">Nama Tamu <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" id="ext_nama_show" required placeholder="Contoh: Budi Santoso, S.Kom"
                           class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>
                <div class="space-y-1">
                    <label for="ext_jabatan_show" class="block text-xs font-bold text-[#5a508f] uppercase">Jabatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="jabatan" id="ext_jabatan_show" required placeholder="Contoh: Analis Infrastruktur"
                           class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>
                <div class="space-y-1">
                    <label for="ext_instansi_show" class="block text-xs font-bold text-[#5a508f] uppercase">Instansi Asal <span class="text-rose-500">*</span></label>
                    <input type="text" name="instansi" id="ext_instansi_show" required placeholder="Contoh: Bappeda Litbang"
                           class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                </div>

                <!-- Footer / Action Buttons -->
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="openGuestModal = false" class="px-4 py-2.5 border border-[#cbd5e1] hover:bg-slate-50 text-[#2e2552] text-xs font-bold rounded-2xl">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl shadow-sm">
                        + Tambah Tamu
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL: ISI PRESENSI MANDIRI DENGAN TTD -->
    <div x-show="openAbsenModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="openAbsenModal = false" 
             class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden relative text-[#2e2552]"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-5 py-3.5 sm:py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0 rounded-t-3xl">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-white">Isi Formulir Presensi Kehadiran</h3>
                    <p class="text-[10px] sm:text-xs text-[#d4d1f5] font-medium mt-0.5">Konfirmasi kehadiran Anda pada agenda ini</p>
                </div>
                <button type="button" @click="openAbsenModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('agenda.absen', $agenda->id) }}" method="POST" @submit="submitAbsen($event)" class="p-6 space-y-4">
                @csrf
                
                <!-- 1. Identitas Pegawai (Read-Only) -->
                <div class="bg-slate-50 border border-slate-200/60 rounded-2xl p-4 space-y-2.5">
                    <span class="block text-[9px] font-bold text-[#5a508f] uppercase tracking-wider">Identitas Pegawai Terautentikasi</span>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                        <div>
                            <span class="text-[#5a508f] block text-[10px]">Nama Pegawai</span>
                            <span class="font-bold text-[#2e2552]">{{ Auth::user()->name }}</span>
                        </div>
                        <div>
                            <span class="text-[#5a508f] block text-[10px]">NIP</span>
                            <span class="font-bold text-[#2e2552] font-mono">{{ Auth::user()->nip }}</span>
                        </div>
                        <div>
                            <span class="text-[#5a508f] block text-[10px]">Jabatan</span>
                            <span class="font-bold text-[#2e2552] truncate block" title="{{ Auth::user()->jabatan }}">{{ Auth::user()->jabatan }}</span>
                        </div>
                        <div>
                            <span class="text-[#5a508f] block text-[10px]">Bidang</span>
                            <span class="font-bold text-[#2e2552]">{{ Auth::user()->bidang->nama ?? 'Sekretariat / Lintas Bidang' }}</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Pilihan Kehadiran -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Status Kehadiran <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex items-center gap-2 p-3 border border-[#cbd5e1] rounded-2xl cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-200 transition-colors"
                               :class="{'bg-emerald-55/10 border-emerald-300 text-emerald-700': status === 'hadir'}">
                            <input type="radio" name="status" value="hadir" x-model="status" required class="hidden">
                            <span class="w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center" :class="{'border-emerald-500': status === 'hadir'}">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" x-show="status === 'hadir'"></span>
                            </span>
                            <span class="text-xs font-bold">Hadir</span>
                        </label>
                        
                        <label class="flex items-center gap-2 p-3 border border-[#cbd5e1] rounded-2xl cursor-pointer hover:bg-amber-50/50 hover:border-amber-200 transition-colors"
                               :class="{'bg-amber-50 border-amber-300 text-amber-700': status === 'izin'}">
                            <input type="radio" name="status" value="izin" x-model="status" required class="hidden">
                            <span class="w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center" :class="{'border-amber-500': status === 'izin'}">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500" x-show="status === 'izin'"></span>
                            </span>
                            <span class="text-xs font-bold">Izin</span>
                        </label>

                        <label class="flex items-center gap-2 p-3 border border-[#cbd5e1] rounded-2xl cursor-pointer hover:bg-rose-50/50 hover:border-rose-200 transition-colors"
                               :class="{'bg-rose-55/10 border-rose-300 text-rose-700': status === 'sakit'}">
                            <input type="radio" name="status" value="sakit" x-model="status" required class="hidden">
                            <span class="w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center" :class="{'border-rose-500': status === 'sakit'}">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500" x-show="status === 'sakit'"></span>
                            </span>
                            <span class="text-xs font-bold">Sakit</span>
                        </label>
                    </div>
                </div>

                <!-- 3. Keterangan Catatan (Hanya untuk Izin) -->
                <div class="space-y-1" x-show="status === 'izin'" x-transition>
                    <label for="keterangan" class="block text-xs font-bold text-[#5a508f] uppercase">
                        Alasan / Keterangan Izin <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="keterangan" name="keterangan" rows="2" x-model="keterangan" :required="status === 'izin'"
                              placeholder="Masukkan alasan atau keterangan Anda mengambil izin..."
                              class="w-full px-4 py-2 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none focus:ring-2 focus:ring-[#8e88dd]"></textarea>
                </div>

                <!-- 4. Canvas TTD Digital (Hanya untuk Hadir) -->
                <div class="space-y-1" x-show="status === 'hadir'" x-transition>
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-[#5a508f] uppercase">Tanda Tangan Digital <span class="text-rose-500">*</span></label>
                        <button type="button" @click="clearSignature" class="text-[10px] font-bold text-rose-600 hover:text-rose-800 transition-colors">
                            Bersihkan / Hapus
                        </button>
                    </div>
                    <div class="border border-[#d4d1f5] rounded-2xl overflow-hidden bg-slate-50 relative">
                        <canvas id="signature-canvas" class="w-full h-32 cursor-crosshair block bg-slate-50"></canvas>
                        <div x-show="isSignatureEmpty" class="absolute inset-0 pointer-events-none flex items-center justify-center text-[10px] text-slate-400 font-bold select-none uppercase tracking-wider">
                            Goreskan Tanda Tangan Anda di Sini
                        </div>
                    </div>
                    <input type="hidden" name="signature" id="signature-input" x-model="signatureData">
                </div>

                <!-- Footer / Action Buttons -->
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="openAbsenModal = false" class="px-4 py-2.5 border border-[#cbd5e1] hover:bg-slate-50 text-[#2e2552] text-xs font-bold rounded-2xl">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 sm:px-6 sm:py-3 bg-gradient-to-r from-[#1b3bbb] to-[#0b1554] hover:from-[#0b1554] hover:to-[#1b3bbb] text-white text-xs font-bold rounded-xl sm:rounded-2xl shadow-md shadow-[#1b3bbb]/20 hover:scale-[1.02] active:scale-95 transition-all duration-300 cursor-pointer">
                        Kirim Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
@php
    $editJudul = old('judul', $agenda->judul);
    $editKategori = old('kategori', $agenda->kategori);
    $editTanggal = old('tanggal', $agenda->tanggal ? $agenda->tanggal->format('Y-m-d') : '');
    $editJamMulai = old('jam_mulai', substr($agenda->jam_mulai, 0, 5));
    $editJamSelesai = old('jam_selesai', substr($agenda->jam_selesai, 0, 5));
    $editLokasi = old('lokasi', $agenda->lokasi);
    $editDeskripsi = old('deskripsi', $agenda->deskripsi ?? '');
    $editButuhPresensi = (bool) old('butuh_presensi', $agenda->butuh_presensi);
    $editBidangs = array_values(array_map('strval', (array) old('bidangs', $agenda->hak_akses ?? [])));
    $editSemuaOrang = (bool) old('semua_orang', in_array('semua_orang', (array) ($agenda->hak_akses ?? [])));
    $editKadinTarget = in_array('kadin', $editBidangs);
    $editParticipants = array_values(array_map('strval', (array) old('participants', $agenda->participants->pluck('id')->toArray())));
    $validationErrors = collect($errors->getMessages())->mapWithKeys(fn($v, $k) => [$k => $v[0]])->all();
@endphp

<script>
    function registerAgendaDetail() {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('agendaDetail', () => ({
                openEditModal: {{ $errors->any() ? 'true' : 'false' }},
                openDetailModal: false,
                openAbsenModal: false,
                openNomorSuratModal: false,
                openGuestModal: false,
                status: 'hadir',
                keterangan: '',
                signatureData: '',
                isSignatureEmpty: true,
                signaturePad: null,
                selectedBidangName: '',
                detailParticipants: [],
                allParticipants: @json((Auth::user()->role === 'staff' && !Auth::user()->isSekretariat()) ? [] : $participants),

                // Edit Modal State
                judul: @json($editJudul),
                kategori: @json($editKategori),
                openKategori: false,
                selectedDate: @json($editTanggal),
                selectedTime: @json($editJamMulai),
                selectedEndTime: @json($editJamSelesai),
                lokasiVal: @json($editLokasi),
                openLokasi: false,
                deskripsi: @json($editDeskripsi),
                butuh_presensi: {{ $editButuhPresensi ? 'true' : 'false' }},

                bidangs: @json($editBidangs),
                semuaOrang: {{ $editSemuaOrang ? 'true' : 'false' }},
                semuaSekretariat: false,
                kadinTarget: {{ $editKadinTarget ? 'true' : 'false' }},

                ownBidangId: @json((string)Auth::user()->bidang_id),
                isSekBid: {{ (Auth::user()->role === 'sekretaris_bidang' && !Auth::user()->isSekretariat()) ? 'true' : 'false' }},
                isSekretariatScope: {{ Auth::user()->isSekretariatScope() ? 'true' : 'false' }},
                allBidangIds: @json(array_values(array_map('strval', $bidangsUserData->pluck('id')->toArray()))),
                totalCount: {{ count($bidangsUserData) }},
                sekretariatSubbagIds: @json(array_values(array_map('strval', $sekretariatSubbagIds ?? []))),
                sekId: @json((string)$sekretariatId),
                kadinUserId: @json((string)$kadinUserId),
                kadinUser: @json($kadinUserData),
                showBidangLimitWarning: false,
                bidangsUserData: @json($bidangsUserData),
                currentUserId: @json((string)Auth::id()),
                selectedParticipants: @json($editParticipants),
                participantModalOpen: false,
                searchParticipant: '',
                adminValidationErrorMessage: '',
                formErrors: @json($validationErrors),
                isDirty: false,

                init() {
                    this.$watch('status', value => {
                        if (value === 'hadir') {
                            this.initSignaturePad();
                        }
                    });
                    this.$watch('openAbsenModal', value => {
                        if (value && this.status === 'hadir') {
                            this.initSignaturePad();
                        }
                    });

                    let sekSubIds = (this.sekretariatSubbagIds || []).map(String);
                    let curBids = (this.bidangs || []).map(String);
                    this.semuaSekretariat = sekSubIds.length > 0 && sekSubIds.every(id => curBids.includes(id));
                },

                validateForm(e) {
                    this.formErrors = {};
                    let hasError = false;

                    if (!this.judul || !this.judul.trim()) {
                        this.formErrors.judul = 'Judul kegiatan / rapat wajib diisi.';
                        hasError = true;
                    }

                    if (!this.kategori) {
                        this.formErrors.kategori = 'Kategori wajib dipilih.';
                        hasError = true;
                    }

                    if (!this.selectedDate) {
                        this.formErrors.tanggal = 'Tanggal wajib diisi.';
                        hasError = true;
                    }

                    if (!this.selectedTime) {
                        this.formErrors.jam_mulai = 'Jam mulai wajib diisi.';
                        hasError = true;
                    }

                    if (!this.selectedEndTime) {
                        this.formErrors.jam_selesai = 'Jam selesai wajib diisi.';
                        hasError = true;
                    } else if (this.selectedTime && this.selectedEndTime <= this.selectedTime) {
                        this.formErrors.jam_selesai = 'Jam selesai harus setelah jam mulai.';
                        hasError = true;
                    }

                    if (!this.lokasiVal) {
                        this.formErrors.lokasi = 'Tempat / ruangan wajib dipilih.';
                        hasError = true;
                    }

                    if ((!this.bidangs || this.bidangs.length === 0) && !this.semuaOrang) {
                        this.formErrors.bidangs = 'Pilih minimal 1 sasaran bidang / unit.';
                        hasError = true;
                    }

                    if (!this.selectedParticipants || this.selectedParticipants.length === 0) {
                        this.formErrors.participants = 'Pilih minimal 1 peserta rapat.';
                        hasError = true;
                    }

                    if (!this.validateAdminSelection()) {
                        hasError = true;
                    }

                    if (hasError) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.$nextTick(() => {
                            if (this.$refs.editFormContainer) {
                                this.$refs.editFormContainer.scrollTo({ top: 0, behavior: 'smooth' });
                            }
                        });
                        return false;
                    }

                    return true;
                },

                isAdminUser(user) {
                    if (!user) return false;
                    let r = user.role;
                    return r === 'sekretaris_bidang' || r === 'sekretaris_master';
                },

                isKetuaUser(user) {
                    if (!user) return false;
                    let r = user.role;
                    let j = (user.jabatan || '').toLowerCase();
                    if (this.isAdminUser(user)) return false;
                    return r === 'ketua_bidang' || r === 'ketua_master' || j.includes('kepala') || j.includes('kabid') || j.includes('kasubbag') || j.includes('kadin') || j.includes('sekdin');
                },

                isMandatoryUser(user) {
                    if (!user) return false;
                    return this.isKetuaUser(user);
                },

                toggleUserParticipant(user) {
                    this.isDirty = true;
                    let uId = String(user.id);
                    let curParts = (this.selectedParticipants || []).map(String);

                    if (this.isAdminUser(user)) {
                        let unitId = String(user.bidang_id);
                        let b = (this.bidangsUserData || []).find(item => String(item.id) === unitId);
                        if (b) {
                            let unitAdminIds = b.users.filter(u => this.isAdminUser(u)).map(u => String(u.id));
                            if (!curParts.includes(uId)) {
                                curParts = curParts.filter(id => !unitAdminIds.includes(id));
                                curParts.push(uId);
                            }
                        }
                        this.selectedParticipants = curParts;
                    }
                },

                validateAdminSelection() {
                    let selectedBidangIds = (this.bidangs || []).map(String);
                    let curSelected = (this.selectedParticipants || []).map(String);
                    let missingAdminBidangNames = [];

                    (this.bidangsUserData || []).forEach(b => {
                        if (selectedBidangIds.includes(String(b.id))) {
                            let unitAdmins = (b.users || []).filter(u => this.isAdminUser(u));
                            if (unitAdmins.length > 0) {
                                let selectedCount = unitAdmins.filter(u => curSelected.includes(String(u.id))).length;
                                if (selectedCount < 1) {
                                    missingAdminBidangNames.push(b.nama || b.singkatan);
                                }
                            }
                        }
                    });

                    if (missingAdminBidangNames.length > 0) {
                        let msg = 'Pilih minimal 1 Admin dari unit yang diundang (' + missingAdminBidangNames.join(', ') + ').';
                        this.adminValidationErrorMessage = msg;
                        this.formErrors.participants = msg;
                        this.$nextTick(() => {
                            if (this.$refs.editFormContainer) {
                                this.$refs.editFormContainer.scrollTo({ top: 9999, behavior: 'smooth' });
                            }
                        });
                        return false;
                    }
                    this.adminValidationErrorMessage = '';
                    delete this.formErrors.participants;
                    return true;
                },

                toggleKadinTarget() {
                    this.isDirty = true;
                    let kId = String(this.kadinUserId);
                    let curParts = (this.selectedParticipants || []).map(String);
                    let curBids = (this.bidangs || []).map(String);

                    if (this.kadinTarget) {
                        if (!curBids.includes('kadin')) curBids.push('kadin');
                        if (kId && !curParts.includes(kId)) curParts.push(kId);
                    } else {
                        curBids = curBids.filter(b => b !== 'kadin');
                        if (kId) curParts = curParts.filter(p => p !== kId);
                    }
                    this.bidangs = curBids;
                    this.syncParticipants();
                },

                toggleSemua() {
                    this.isDirty = true;
                    if (this.semuaOrang) {
                        this.bidangs = Array.from(this.allBidangIds);
                    } else {
                        this.bidangs = [];
                    }
                    this.syncParticipants();
                },

                toggleSemuaSekretariat() {
                    this.isDirty = true;
                    let sekSubIds = (this.sekretariatSubbagIds || []).map(String);
                    let curBids = (this.bidangs || []).map(String);

                    if (this.semuaSekretariat) {
                        sekSubIds.forEach(id => {
                            if (!curBids.includes(id)) {
                                curBids.push(id);
                            }
                        });
                        if (!this.isSekretariatScope && curBids.length > 3) {
                            curBids = curBids.slice(0, 3);
                            this.showBidangLimitWarning = true;
                        } else {
                            this.showBidangLimitWarning = false;
                        }
                    } else {
                        curBids = curBids.filter(id => !sekSubIds.includes(id));
                        if (this.ownBidangId && !curBids.includes(String(this.ownBidangId))) {
                            curBids.push(String(this.ownBidangId));
                        }
                        if (this.sekId && !curBids.includes(String(this.sekId))) {
                            curBids.push(String(this.sekId));
                        }
                        this.showBidangLimitWarning = false;
                    }
                    this.bidangs = curBids;
                    this.semuaSekretariat = sekSubIds.length > 0 && sekSubIds.every(id => curBids.includes(id));
                    this.syncParticipants();
                },

                checkBidang(id) {
                    this.isDirty = true;
                    this.$nextTick(() => {
                        let strId = String(id);
                        let curBids = (this.bidangs || []).map(String);
                        let curParts = (this.selectedParticipants || []).map(String);

                        if (curBids.includes(strId)) {
                            let b = (this.bidangsUserData || []).find(item => String(item.id) === strId);
                            if (b && Array.isArray(b.users)) {
                                b.users.forEach(u => {
                                    let uId = String(u.id);
                                    if (!curParts.includes(uId)) {
                                        curParts.push(uId);
                                    }
                                });
                            }
                        } else {
                            let b = (this.bidangsUserData || []).find(item => String(item.id) === strId);
                            if (b && Array.isArray(b.users)) {
                                let unitUserIds = b.users.map(u => String(u.id));
                                curParts = curParts.filter(uId => !unitUserIds.includes(uId) || uId === String(this.currentUserId));
                            }
                        }
                        this.selectedParticipants = curParts;

                        if (this.isSekBid || this.isSekretariatScope) {
                            if (this.ownBidangId && !curBids.includes(String(this.ownBidangId))) {
                                curBids.push(String(this.ownBidangId));
                            }
                            if (this.isSekretariatScope && this.sekId && !curBids.includes(String(this.sekId))) {
                                curBids.push(String(this.sekId));
                            }
                            let numericBids = curBids.filter(b => b !== 'kadin' && b !== String(this.sekId));
                            if (!this.isSekretariatScope && numericBids.length > 3) {
                                this.showBidangLimitWarning = true;
                                curBids = curBids.filter(bId => bId !== strId);
                            } else {
                                this.showBidangLimitWarning = false;
                            }
                        }
                        this.bidangs = curBids;
                        let sekSubIds = (this.sekretariatSubbagIds || []).map(String);
                        this.semuaSekretariat = sekSubIds.length > 0 && sekSubIds.every(id => curBids.includes(id));
                        this.semuaOrang = (this.bidangs.length === this.totalCount);
                        this.syncParticipants();
                    });
                },

                filteredUsers(users) {
                    if (!users || !Array.isArray(users)) return [];
                    if (!this.searchParticipant || !this.searchParticipant.trim()) return users;
                    let q = this.searchParticipant.toLowerCase().trim();
                    return users.filter(u => 
                        (u.name && String(u.name).toLowerCase().includes(q)) || 
                        (u.jabatan && String(u.jabatan).toLowerCase().includes(q)) ||
                        (u.nip && String(u.nip).toLowerCase().includes(q))
                    );
                },

                get visibleBidangs() {
                    let selectedBidangIds = (this.bidangs || []).map(String);
                    return (this.bidangsUserData || []).filter(b => {
                        let isSelected = selectedBidangIds.includes(String(b.id));
                        if (!isSelected) return false;
                        if (this.searchParticipant && this.searchParticipant.trim()) {
                            return this.filteredUsers(b.users).length > 0;
                        }
                        return true;
                    });
                },

                get totalFilteredUsersCount() {
                    let count = 0;
                    this.visibleBidangs.forEach(b => {
                        count += this.filteredUsers(b.users).length;
                    });
                    return count;
                },

                syncParticipants() {
                    let selectedBidangIds = (this.bidangs || []).map(String);
                    let activeUserIds = [];
                    let mandatoryUserIds = [];

                    if (this.currentUserId) {
                        mandatoryUserIds.push(String(this.currentUserId));
                    }

                    let currentSelected = (this.selectedParticipants || []).map(String);

                    (this.bidangsUserData || []).forEach(b => {
                        if (selectedBidangIds.includes(String(b.id))) {
                            let unitAdmins = (b.users || []).filter(u => this.isAdminUser(u));
                            let hasAdminSelected = unitAdmins.some(u => currentSelected.includes(String(u.id)));

                            (b.users || []).forEach(u => {
                                let uId = String(u.id);
                                activeUserIds.push(uId);
                                if (this.isMandatoryUser(u)) {
                                    mandatoryUserIds.push(uId);
                                }
                            });

                            if (!hasAdminSelected && unitAdmins.length > 0) {
                                mandatoryUserIds.push(String(unitAdmins[0].id));
                            }
                        }
                    });

                    if (this.kadinTarget && this.kadinUserId) {
                        let kId = String(this.kadinUserId);
                        activeUserIds.push(kId);
                        mandatoryUserIds.push(kId);
                    }

                    let newSelection = currentSelected.filter(id => activeUserIds.includes(id));

                    selectedBidangIds.forEach(bidId => {
                        let b = (this.bidangsUserData || []).find(item => String(item.id) === bidId);
                        if (b && Array.isArray(b.users)) {
                            let hasAnySelected = b.users.some(u => currentSelected.includes(String(u.id)));
                            if (!hasAnySelected) {
                                b.users.forEach(u => {
                                    let uId = String(u.id);
                                    if (!newSelection.includes(uId)) {
                                        newSelection.push(uId);
                                    }
                                });
                            }
                        }
                    });

                    mandatoryUserIds.forEach(id => {
                        if (!newSelection.includes(id)) {
                            newSelection.push(id);
                        }
                    });

                    this.selectedParticipants = newSelection;
                    if (this.kadinUserId) {
                        this.kadinTarget = this.selectedParticipants.map(String).includes(String(this.kadinUserId));
                    }
                },

                toggleBidangUsers(bidangId) {
                    this.isDirty = true;
                    let b = this.bidangsUserData.find(item => String(item.id) === String(bidangId));
                    if (!b) return;
                    let bUserIds = b.users.map(u => String(u.id));
                    let currentSelected = this.selectedParticipants.map(String);
                    let allChecked = bUserIds.every(id => currentSelected.includes(id));

                    if (!allChecked) {
                        bUserIds.forEach(id => {
                            if (!currentSelected.includes(id)) {
                                currentSelected.push(id);
                            }
                        });
                    } else {
                        currentSelected = currentSelected.filter(id => {
                            let u = b.users.find(usr => String(usr.id) === String(id));
                            if (u && this.isMandatoryUser(u)) return true;
                            return !bUserIds.includes(id);
                        });
                    }
                    this.selectedParticipants = currentSelected;
                },

                isBidangAllChecked(bidangId) {
                    let b = this.bidangsUserData.find(item => String(item.id) === String(bidangId));
                    if (!b || !b.users || b.users.length === 0) return false;
                    let currentSelected = this.selectedParticipants.map(String);
                    return b.users.every(u => currentSelected.includes(String(u.id)));
                },

                init() {
                    this.$watch('status', value => {
                        if (value === 'hadir') {
                            this.initSignaturePad();
                        }
                    });
                    this.$watch('openAbsenModal', value => {
                        if (value && this.status === 'hadir') {
                            this.initSignaturePad();
                        }
                    });
                },
                showBidangDetails(bidId, bidName) {
                    this.selectedBidangName = bidName;
                    if (bidId == 0) {
                        this.detailParticipants = this.allParticipants.filter(p => p.role === 'ketua_master' || !p.bidang_id || p.bidang_id == 0);
                    } else {
                        this.detailParticipants = this.allParticipants.filter(p => p.bidang_id == bidId && p.role !== 'ketua_master');
                    }
                    this.openDetailModal = true;
                },
                initSignaturePad() {
                    this.$nextTick(() => {
                        const canvas = document.getElementById('signature-canvas');
                        if (!canvas) return;

                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext("2d").scale(ratio, ratio);

                        if (this.signaturePad) {
                            this.signaturePad.off();
                        }

                        this.signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgba(255, 255, 255, 0)',
                            penColor: '#09103c'
                        });

                        this.signaturePad.addEventListener("beginStroke", () => {
                            this.isSignatureEmpty = false;
                        });

                        this.clearSignature();
                    });
                },
                clearSignature() {
                    if (this.signaturePad) {
                        this.signaturePad.clear();
                    }
                    this.signatureData = '';
                    this.isSignatureEmpty = true;
                },
                submitAbsen(e) {
                    if (this.status === 'hadir') {
                        if (this.isSignatureEmpty || !this.signaturePad || this.signaturePad.isEmpty()) {
                            e.preventDefault();
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    text: 'Tanda tangan digital wajib diisi sebelum mengirim presensi.',
                                    icon: 'warning',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert('Tanda tangan digital wajib diisi sebelum mengirim presensi.');
                            }
                            return;
                        }
                        this.signatureData = this.signaturePad.toDataURL('image/png');
                        const sigInput = document.getElementById('signature-input');
                        if (sigInput) {
                            sigInput.value = this.signatureData;
                        }
                    } else {
                        this.signatureData = '';
                        const sigInput = document.getElementById('signature-input');
                        if (sigInput) {
                            sigInput.value = '';
                        }
                    }

                    const form = e.target;
                    if (form.dataset.submitting === 'true') {
                        e.preventDefault();
                        return;
                    }
                }
            }));
        }
    }

    window.submitStatusKoreksi = function(selectEl) {
        if (!selectEl || !selectEl.form) return;
        
        selectEl.style.pointerEvents = 'none';

        if (typeof window.showHeavyLoading === 'function') {
            window.showHeavyLoading('Memperbarui Status Presensi...', 'Mohon tunggu sejenak, status presensi pegawai sedang disimpan.');
        }

        let loader = document.getElementById('pjax-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'pjax-loader';
            loader.style.position = 'fixed';
            loader.style.top = '0';
            loader.style.left = '0';
            loader.style.height = '3.5px';
            loader.style.backgroundColor = '#1b3bbb';
            loader.style.boxShadow = '0 0 10px rgba(27, 59, 187, 0.5)';
            loader.style.zIndex = '99999';
            loader.style.width = '0%';
            loader.style.transition = 'width 0.3s ease';
            document.body.appendChild(loader);
        }
        loader.style.width = '35%';
        setTimeout(() => { if (loader) loader.style.width = '85%'; }, 150);

        selectEl.form.submit();
    };

    if (typeof Alpine !== 'undefined') {
        registerAgendaDetail();
    } else {
        document.addEventListener('alpine:init', registerAgendaDetail);
    }
    </script>

    <!-- Floating AI Background Processing Toast (Sekretaris Only - Non-blocking top-right position) -->
    @if($isSecretaryOfAgenda && $agenda->notulensi && $agenda->notulensi->is_transcribing && (!empty($agenda->notulensi->audio_path) || (!empty($agenda->notulensi->audio_files) && count($agenda->notulensi->audio_files) > 0)))
        <div class="fixed top-24 right-6 z-50 bg-[#09103c] text-white p-4 rounded-2xl shadow-2xl border border-sky-500/30 flex items-center gap-3.5 max-w-sm animate-bounce pointer-events-none">
            <div class="w-9 h-9 bg-sky-500/20 text-sky-400 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="space-y-0.5 text-left flex-1 min-w-0">
                <p class="text-xs font-bold text-sky-300">Transkripsi AI Sedang Berjalan</p>
                <p class="text-[10px] text-slate-300 leading-tight">Rekaman audio rapat sedang diolah oleh AI. Halaman akan diperbarui otomatis.</p>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.reload();
            }, 4000);
        </script>
    @endif
    @if($isSecretaryOfAgenda)
    <!-- MODAL EDIT AGENDA KEGIATAN -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-sm select-none">
        <div @click.away="openEditModal = false" 
             class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-xl shadow-2xl overflow-hidden relative text-[#2e2552] my-auto flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
             
            <!-- Header Modal Edit -->
            <div class="px-5 py-3.5 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0 rounded-t-3xl shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 bg-white/10 rounded-xl border border-white/20 shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-white leading-tight">Edit Agenda Kegiatan</h3>
                        <p class="text-[10px] text-[#d4d1f5] font-medium leading-tight">Perbarui rincian rapat atau kegiatan Dinkominfo</p>
                    </div>
                </div>
                <button type="button" @click="openEditModal = false" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form Edit Agenda -->
            <form action="{{ route('agenda.update', $agenda->id) }}" method="POST" @submit="validateForm($event)" x-ref="editFormContainer" class="p-4 sm:p-5 space-y-3.5 overflow-y-auto max-h-[calc(90vh-120px)] scroll-smooth">
                @csrf
                @method('PUT')

                <!-- Title & Category Row -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 space-y-1">
                        <label for="edit_judul" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Judul Kegiatan / Rapat <span class="text-rose-500 font-bold">*</span></label>
                        <input type="text" name="judul" id="edit_judul" x-model="judul" placeholder="Contoh: Rapat Koordinasi Layanan SPBE"
                               :class="formErrors.judul ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 text-rose-900 placeholder-rose-400' : 'border-[#d4d1f5] bg-[#f8f7ff] hover:bg-[#f3f2fe] focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 text-[#2e2552]'"
                               class="w-full px-3.5 py-2 rounded-xl text-xs placeholder-slate-400 transition-all font-semibold"
                               @input="if(formErrors.judul) delete formErrors.judul">
                        <template x-if="formErrors.judul">
                            <p class="text-[10.5px] text-rose-600 font-bold mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span x-text="formErrors.judul"></span>
                            </p>
                        </template>
                    </div>
                    <div class="space-y-1 relative" @click.outside="openKategori = false">
                        <label for="edit_kategori" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Kategori <span class="text-rose-500 font-bold">*</span></label>
                        <input type="hidden" name="kategori" id="edit_kategori" x-model="kategori">
                        <button type="button" @click="openKategori = !openKategori" 
                                :class="formErrors.kategori ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 text-rose-900' : 'border-[#d4d1f5] bg-[#f8f7ff] hover:bg-[#f3f2fe] focus:bg-white focus:ring-2 focus:ring-[#1b3bbb] text-[#09103c]'"
                                class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer focus:outline-none">
                            <span class="truncate" x-text="kategori ? (kategori === 'rapat' ? 'Rapat' : (kategori === 'sosialisasi' ? 'Sosialisasi' : (kategori === 'pelatihan' ? 'Pelatihan' : 'Kegiatan Lainnya'))) : 'Pilih Kategori'"></span>
                            <svg class="w-3.5 h-3.5 text-[#1b3bbb] transition-transform duration-200" :class="openKategori ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <template x-if="formErrors.kategori">
                            <p class="text-[10.5px] text-rose-600 font-bold mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span x-text="formErrors.kategori"></span>
                            </p>
                        </template>
                        <div x-show="openKategori" x-cloak 
                             x-transition:enter="transition ease-out duration-150 transform" 
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-100 transform" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1" 
                             class="absolute left-0 top-full mt-1 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                            <div class="space-y-0.5">
                                <template x-for="opt in [
                                    { value: 'rapat', label: 'Rapat' },
                                    { value: 'sosialisasi', label: 'Sosialisasi' },
                                    { value: 'pelatihan', label: 'Pelatihan' },
                                    { value: 'kegiatan_lainnya', label: 'Kegiatan Lainnya' }
                                ]" :key="opt.value">
                                    <button type="button" @click="kategori = opt.value; openKategori = false; if(formErrors.kategori) delete formErrors.kategori" 
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                            :class="kategori === opt.value ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                        <span class="text-left leading-snug" x-text="opt.label"></span>
                                        <svg x-show="kategori === opt.value" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date & Hours Row -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="col-span-2 sm:col-span-1 space-y-1">
                        <label for="edit_tanggal" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Tanggal <span class="text-rose-500 font-bold">*</span></label>
                        <input type="date" name="tanggal" id="edit_tanggal" x-model="selectedDate"
                               min="{{ now()->subMonths(6)->toDateString() }}"
                               max="{{ now()->addMonths(6)->toDateString() }}"
                               :class="formErrors.tanggal ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 text-rose-900' : 'border-[#d4d1f5] bg-[#f8f7ff] hover:bg-[#f3f2fe] focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 text-[#2e2552]'"
                               class="w-full px-3 py-2 rounded-xl text-xs font-semibold transition-all"
                               @change="if(formErrors.tanggal) delete formErrors.tanggal">
                        <template x-if="formErrors.tanggal">
                            <p class="text-[10.5px] text-rose-600 font-bold mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span x-text="formErrors.tanggal"></span>
                            </p>
                        </template>
                    </div>
                    <div class="space-y-1">
                        <label for="edit_jam_mulai" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Jam Mulai <span class="text-rose-500 font-bold">*</span></label>
                        <input type="time" name="jam_mulai" id="edit_jam_mulai" x-model="selectedTime"
                               :class="formErrors.jam_mulai ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 text-rose-900' : 'border-[#d4d1f5] bg-[#f8f7ff] hover:bg-[#f3f2fe] focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 text-[#2e2552]'"
                               class="w-full px-2.5 py-2 rounded-xl text-xs font-semibold transition-all"
                               @change="if(formErrors.jam_mulai) delete formErrors.jam_mulai">
                        <template x-if="formErrors.jam_mulai">
                            <p class="text-[10.5px] text-rose-600 font-bold mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span x-text="formErrors.jam_mulai"></span>
                            </p>
                        </template>
                    </div>
                    <div class="space-y-1">
                        <label for="edit_jam_selesai" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Jam Selesai <span class="text-rose-500 font-bold">*</span></label>
                        <input type="time" name="jam_selesai" id="edit_jam_selesai" x-model="selectedEndTime"
                               :class="formErrors.jam_selesai ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 text-rose-900' : 'border-[#d4d1f5] bg-[#f8f7ff] hover:bg-[#f3f2fe] focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 text-[#2e2552]'"
                               class="w-full px-2.5 py-2 rounded-xl text-xs font-semibold transition-all"
                               @change="if(formErrors.jam_selesai) delete formErrors.jam_selesai">
                        <template x-if="formErrors.jam_selesai">
                            <p class="text-[10.5px] text-rose-600 font-bold mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span x-text="formErrors.jam_selesai"></span>
                            </p>
                        </template>
                    </div>
                </div>

                <!-- Location & Description Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1 relative" @click.outside="openLokasi = false">
                        <label for="edit_tempat" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Tempat / Ruangan <span class="text-rose-500 font-bold">*</span></label>
                        <input type="hidden" id="edit_tempat" name="lokasi" :value="lokasiVal">
                        <button type="button" @click="openLokasi = !openLokasi" 
                                :class="formErrors.lokasi ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 text-rose-900' : 'border-[#d4d1f5] bg-[#f8f7ff] hover:bg-[#f3f2fe] focus:bg-white focus:ring-2 focus:ring-[#1b3bbb] text-[#09103c]'"
                                class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition-all cursor-pointer focus:outline-none">
                            <span class="truncate" x-text="lokasiVal || 'Pilih Lokasi / Ruangan'"></span>
                            <svg class="w-3.5 h-3.5 text-[#1b3bbb] transition-transform duration-200" :class="openLokasi ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <template x-if="formErrors.lokasi">
                            <p class="text-[10.5px] text-rose-600 font-bold mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span x-text="formErrors.lokasi"></span>
                            </p>
                        </template>
                        <div x-show="openLokasi" x-cloak 
                             x-transition:enter="transition ease-out duration-150 transform" 
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-100 transform" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1" 
                             class="absolute left-0 bottom-full mb-1 w-full bg-white border border-[#cbd5e1] rounded-2xl shadow-xl shadow-[#1b3bbb]/10 p-1.5 z-50 max-h-52 overflow-y-auto">
                            <div class="space-y-0.5">
                                <template x-for="loc in ['Aula Rapat Dinkominfo', 'Ruang Pelatihan', 'Smart Room Graha Satria']" :key="loc">
                                    <button type="button" @click="lokasiVal = loc; openLokasi = false; if(formErrors.lokasi) delete formErrors.lokasi" 
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors text-left"
                                            :class="lokasiVal === loc ? 'bg-[#1b3bbb] text-white font-bold' : 'text-[#09103c] hover:bg-[#1b3bbb]/10 hover:text-[#1b3bbb]'">
                                        <span class="text-left leading-snug" x-text="loc"></span>
                                        <svg x-show="lokasiVal === loc" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="edit_deskripsi" class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">Deskripsi (Opsional)</label>
                        <input type="text" name="deskripsi" id="edit_deskripsi" x-model="deskripsi" placeholder="Masukkan rincian singkat agenda..."
                               class="w-full px-3.5 py-2 bg-[#f8f7ff] hover:bg-[#f3f2fe] border border-[#d4d1f5] rounded-xl text-[#2e2552] text-xs placeholder-slate-400 focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 transition-all font-semibold">
                    </div>
                </div>

                <!-- Hak Akses & Kelola Peserta -->
                <div class="space-y-2 border-t border-[#d4d1f5]/60 pt-2.5">
                    <!-- Hidden Payload Inputs -->
                    <template x-for="bidangId in bidangs" :key="'bidang-' + bidangId">
                        <input type="hidden" name="bidangs[]" :value="bidangId">
                    </template>
                    <template x-for="userId in selectedParticipants" :key="'participant-' + userId">
                        <input type="hidden" name="participants[]" :value="userId">
                    </template>
                    <template x-if="semuaOrang">
                        <input type="hidden" name="semua_orang" value="1">
                    </template>

                    <!-- Header & Manage Participants Button Row -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-[11px] font-bold text-[#5a508f] uppercase tracking-wider">
                                Sasaran Bidang & Peserta <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <p class="text-[10.5px] text-slate-500 font-medium">Pilihan pimpinan ACC dan notulis otomatis tercentang wajib</p>
                        </div>
                        <button type="button" 
                                @click="participantModalOpen = true"
                                :class="selectedParticipants.length === 0 || formErrors.participants ? 'bg-rose-50 text-rose-600 border-rose-300 hover:bg-rose-100 ring-2 ring-rose-500/20' : 'bg-[#f8f7ff] text-[#1b3bbb] border-[#d4d1f5] hover:border-[#1b3bbb] hover:bg-[#f3f2fe]'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-xl text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Kelola Peserta</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold" 
                                  :class="selectedParticipants.length === 0 || formErrors.participants ? 'bg-rose-600 text-white animate-pulse' : 'bg-[#1b3bbb] text-white'" 
                                  x-text="selectedParticipants.length"></span>
                        </button>
                    </div>

                    <template x-if="formErrors.bidangs">
                        <p class="text-[10.5px] text-rose-600 font-bold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span x-text="formErrors.bidangs"></span>
                        </p>
                    </template>
                    <template x-if="formErrors.participants">
                        <p class="text-[10.5px] text-rose-600 font-bold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span x-text="formErrors.participants"></span>
                        </p>
                    </template>

                    <!-- Bidang Selection List Card -->
                    <div :class="formErrors.bidangs ? 'border-rose-400 bg-rose-50/30 ring-2 ring-rose-500/20' : 'border-[#d4d1f5] bg-[#f8f7ff]'"
                         class="border rounded-2xl p-3 space-y-2 transition-all">
                        <!-- Inline Alert for 3-Bidang Limit -->
                        <div x-show="showBidangLimitWarning" x-cloak class="p-2.5 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-[11px] font-semibold flex items-center gap-2 animate-in fade-in duration-200 shadow-2xs">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Admin Bidang hanya dapat memilih maksimal 3 bidang (bidang Anda + maksimal 2 bidang tambahan).</span>
                        </div>

                        @if(Auth::user()->isSekretarisMaster())
                            <label class="flex items-center gap-2 px-2.5 py-1.5 bg-white rounded-xl border border-[#d4d1f5] hover:border-[#1b3bbb] transition-all cursor-pointer select-none">
                                <input type="checkbox" x-model="semuaOrang" @change="toggleSemua()" 
                                       class="w-4 h-4 rounded border-[#d4d1f5] text-[#1b3bbb] focus:ring-[#1b3bbb] transition-all">
                                <span class="text-xs text-[#2e2552] font-extrabold">Semua Bidang / Rapat Lintas Dinas (Semua Orang)</span>
                            </label>
                        @endif

                        <div class="grid grid-cols-1 gap-1 max-h-[140px] overflow-y-auto pr-1">
                            <!-- Checkbox Kepala Dinas (Kadin) -->
                            <label class="flex items-center justify-between px-2.5 py-1.5 rounded-xl border border-transparent hover:border-[#d4d1f5] hover:bg-white transition-all cursor-pointer select-none">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" value="kadin" x-model="kadinTarget" @change="toggleKadinTarget()"
                                           class="w-4 h-4 rounded border-[#d4d1f5] text-[#1b3bbb] focus:ring-[#1b3bbb] focus:ring-offset-0 transition-all shrink-0">
                                    <span class="text-xs text-[#2e2552] font-semibold">
                                        Kepala Dinas <span class="text-[#5a508f] font-normal">(Kadin)</span>
                                    </span>
                                </div>
                            </label>
                            @foreach($bidangsWithUsers as $bid)
                                @php
                                    $isSekretariatItem = (strcasecmp($bid->singkatan, 'Sekretariat') === 0 || strcasecmp($bid->nama, 'Sekretariat') === 0);
                                    $isSubbag = (str_contains(strtolower($bid->nama), 'subbag') || str_contains(strtolower($bid->singkatan), 'subbag')) && !$isSekretariatItem;
                                    $isUserBidang = Auth::user()->isSekretarisBidang() && Auth::user()->bidang_id == $bid->id;
                                @endphp
                                @if($isSekretariatItem)
                                    <!-- Checkbox Lingkup Sekretariat (Right above Sekretariat) -->
                                    <label class="flex items-center gap-2 px-2.5 py-1.5 bg-indigo-50/80 rounded-xl border border-indigo-200/80 hover:border-[#1b3bbb] transition-all cursor-pointer select-none my-0.5">
                                        <input type="checkbox" x-model="semuaSekretariat" @change="toggleSemuaSekretariat()" 
                                               class="w-4 h-4 rounded border-[#d4d1f5] text-[#1b3bbb] focus:ring-[#1b3bbb] transition-all shrink-0">
                                        <span class="text-xs text-[#1b3bbb] font-extrabold">Lingkup Sekretariat</span>
                                    </label>
                                @endif
                                <label class="flex items-center justify-between px-2.5 py-1.5 rounded-xl border border-transparent hover:border-[#d4d1f5] hover:bg-white transition-all cursor-pointer select-none {{ $isSubbag ? 'pl-6' : '' }}">
                                    <div class="flex items-center gap-2">
                                        @if($isSubbag)
                                            <span class="text-[#8e88dd] text-xs font-bold shrink-0 -mr-1">└</span>
                                        @endif
                                        <input type="checkbox" value="{{ $bid->id }}" x-model="bidangs" @change="checkBidang('{{ $bid->id }}')"
                                               @if($isUserBidang) disabled @endif
                                               class="w-4 h-4 rounded border-[#d4d1f5] text-[#1b3bbb] focus:ring-[#1b3bbb] focus:ring-offset-0 transition-all shrink-0">
                                        <span class="text-xs text-[#2e2552] font-semibold {{ $isUserBidang ? 'font-extrabold text-[#1b3bbb]' : '' }}">
                                            {{ $bid->nama }} <span class="text-[#5a508f] font-normal">({{ $bid->singkatan }})</span>
                                        </span>
                                    </div>
                                    @if($isUserBidang)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200/80 shrink-0 ml-2">
                                            Wajib Hadir
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Presensi Toggle & Action Footer -->
                <div class="flex items-center justify-between gap-2 border-t border-[#d4d1f5]/60 pt-3 shrink-0">
                    <label for="edit_butuh_presensi" class="flex items-center justify-between p-2 px-3 bg-gradient-to-r from-[#f8f7ff] to-[#f3f2fe] border border-[#d4d1f5] rounded-xl flex-1 min-w-0 cursor-pointer select-none">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-3.5 h-3.5 text-[#1b3bbb] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-[11px] sm:text-xs font-bold text-[#2e2552] whitespace-nowrap truncate">Memerlukan Presensi Digital?</span>
                        </div>
                        <div class="relative inline-flex items-center ml-1.5 shrink-0">
                            <input type="checkbox" name="butuh_presensi" id="edit_butuh_presensi" x-model="butuh_presensi" value="1" class="sr-only">
                            <div :style="butuh_presensi ? 'background-color: #1b3bbb !important;' : 'background-color: #cbd5e1 !important;'"
                                 class="w-9 h-5 rounded-full p-0.5 transition-all duration-200 relative flex items-center shadow-inner shrink-0">
                                <div :style="butuh_presensi ? 'transform: translateX(16px) !important; background-color: #ffffff !important;' : 'transform: translateX(0px) !important; background-color: #ffffff !important;'"
                                     class="w-4 h-4 rounded-full shadow-md transition-transform duration-200 border border-slate-200"></div>
                            </div>
                        </div>
                    </label>

                    <div class="flex items-center justify-end gap-1.5 shrink-0">
                        <button type="button" @click="openEditModal = false"
                                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-[#2e2552] text-xs font-bold rounded-xl transition-all active:scale-[0.98] whitespace-nowrap">
                            Batalkan
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-[#1b3bbb] hover:bg-[#09103c] text-white text-xs font-extrabold rounded-xl shadow-md shadow-[#1b3bbb]/20 transition-all active:scale-[0.98] flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KELOLA PESERTA MODAL FOR EDIT AGENDA -->
    <div x-show="participantModalOpen" x-cloak 
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 bg-slate-950/70 backdrop-blur-sm select-none">
        <div @click.away="participantModalOpen = false" 
             class="bg-white rounded-3xl shadow-2xl border border-[#d4d1f5] w-full max-w-xl flex flex-col max-h-[85vh] overflow-hidden animate-in fade-in zoom-in duration-200">
            
            <!-- Header Modal Kelola Peserta -->
            <div class="px-5 py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-xl border border-white/15 shrink-0">
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Kelola Peserta Rapat & Kewenangan</h3>
                        <p class="text-[11px] text-indigo-100 font-medium">Pilih tepat 1 Admin dari setiap unit yang diundang</p>
                    </div>
                </div>
                <button @click="participantModalOpen = false" type="button" class="p-1.5 bg-white/10 hover:bg-rose-500/80 rounded-xl text-white transition-all cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body Modal Kelola Peserta -->
            <div x-ref="modalBody" class="p-4 sm:p-5 overflow-y-auto no-scrollbar space-y-4 flex-1 bg-slate-50/50">
                 <!-- Search Bar -->
                <div class="relative">
                    <input type="text" x-model="searchParticipant" placeholder="Cari nama, NIP, atau jabatan peserta..." 
                           class="w-full pl-9 pr-8 py-2 bg-white border border-[#d4d1f5] rounded-xl text-xs text-[#2e2552] placeholder-slate-400 focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/20 transition-all font-semibold shadow-2xs">
                    <svg class="w-4 h-4 text-[#1b3bbb] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <button type="button" x-show="searchParticipant.length > 0" @click="searchParticipant = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Inline Validation Alert Banner inside Kelola Peserta Modal -->
                <template x-if="adminValidationErrorMessage">
                    <div class="p-3 bg-amber-50 border-2 border-amber-400 text-amber-900 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm animate-in fade-in duration-200">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="p-1 bg-amber-200/80 rounded-lg shrink-0">
                                <svg class="w-4 h-4 text-amber-800 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <span x-text="adminValidationErrorMessage" class="leading-tight"></span>
                        </div>
                        <button type="button" @click="adminValidationErrorMessage = ''" class="p-1 text-amber-700 hover:text-amber-950 hover:bg-amber-200/60 rounded-lg transition-all cursor-pointer shrink-0 ml-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </template>

                <template x-if="bidangs.length === 0">
                    <div class="p-8 text-center bg-white rounded-2xl border border-dashed border-[#d4d1f5] shadow-2xs">
                        <p class="text-xs text-slate-500 font-bold">Pilih minimal satu bidang di atas terlebih dahulu untuk mengelola peserta.</p>
                    </div>
                </template>

                <!-- Group Card Khusus Kepala Dinas (Kadin) -->
                <template x-if="kadinUser && kadinTarget && (!searchParticipant || kadinUser.name.toLowerCase().includes(searchParticipant.toLowerCase()) || kadinUser.jabatan.toLowerCase().includes(searchParticipant.toLowerCase()))">
                    <div class="bg-gradient-to-r from-purple-100 via-purple-100/90 to-purple-200/70 border border-purple-400 rounded-2xl p-3.5 space-y-2.5 shadow-2xs">
                        <div class="flex items-center justify-between pb-2 border-b border-purple-200">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                                <span class="text-xs font-extrabold text-purple-950">Kepala Dinas (Kadin)</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            <label class="flex items-start gap-2.5 p-2 bg-white/90 rounded-xl border border-purple-300 cursor-pointer select-none transition-all">
                                <input type="checkbox" :value="kadinUser.id" x-model="selectedParticipants" :disabled="true" class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 mt-0.5 shrink-0 opacity-80 cursor-not-allowed">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-purple-950 leading-tight truncate">
                                        <span x-text="kadinUser.name"></span>
                                    </div>
                                    <div class="text-[10px] text-purple-700 font-medium truncate" x-text="kadinUser.jabatan || 'Kepala Dinas / Kadin'"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </template>

                <template x-for="bidang in visibleBidangs" :key="bidang.id">
                    <div class="bg-white border border-[#d4d1f5]/80 rounded-2xl p-3.5 space-y-2.5 shadow-2xs">
                        <div class="flex items-center justify-between pb-2 border-b border-[#d4d1f5]/40">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#1b3bbb]"></span>
                                <span class="text-xs font-extrabold text-[#2e2552]" x-text="bidang.nama + ' (' + bidang.singkatan + ')'"></span>
                            </div>
                            <button type="button" @click="toggleBidangUsers(bidang.id)" class="text-[11px] font-extrabold text-[#1b3bbb] hover:underline cursor-pointer">
                                <span x-text="isBidangAllChecked(bidang.id) ? 'Hapus Centang Staf' : 'Centang Semua Staf'"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            <template x-for="user in filteredUsers(bidang.users)" :key="user.id">
                                <label class="flex items-start gap-2.5 p-2.5 rounded-xl border select-none transition-all cursor-pointer"
                                       :class="isAdminUser(user) 
                                               ? 'bg-gradient-to-r from-amber-50/90 to-amber-100/60 border-amber-300 hover:border-amber-400' 
                                               : (isKetuaUser(user)
                                                   ? 'bg-gradient-to-r from-purple-100 via-purple-100/90 to-purple-200/70 border-purple-400 hover:border-purple-500' 
                                                   : 'bg-[#f8f7ff] hover:bg-indigo-50/50 border-[#d4d1f5]/60 hover:border-[#1b3bbb]')">
                                    
                                    <input type="checkbox" 
                                           :value="user.id" 
                                           x-model="selectedParticipants" 
                                           :disabled="isMandatoryUser(user)"
                                           class="w-4 h-4 rounded border-slate-300 mt-0.5 shrink-0"
                                           :class="isAdminUser(user) ? 'text-amber-600 focus:ring-amber-500' : (isKetuaUser(user) ? 'text-purple-600 focus:ring-purple-500' : 'text-[#1b3bbb] focus:ring-[#1b3bbb]')"
                                           :class="isMandatoryUser(user) ? 'opacity-80 cursor-not-allowed' : ''">
                                    
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold leading-tight truncate" :class="isAdminUser(user) ? 'text-amber-950' : (isKetuaUser(user) ? 'text-purple-950' : 'text-[#2e2552]')">
                                            <span x-text="user.name" class="truncate"></span>
                                        </div>
                                        <div class="text-[10px] font-medium truncate mt-0.5" :class="isAdminUser(user) ? 'text-amber-800' : (isKetuaUser(user) ? 'text-purple-800' : 'text-[#5a508f]')" x-text="user.jabatan"></div>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer Modal Kelola Peserta -->
            <div class="px-5 py-3.5 bg-[#f8f7ff] border-t border-[#d4d1f5] flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                <div class="text-xs font-bold text-[#5a508f] flex items-center gap-1">
                    <template x-if="selectedParticipants.length === 0">
                        <span class="text-rose-600 font-black flex items-center gap-1">Pilih minimal 1 peserta!</span>
                    </template>
                    <template x-if="selectedParticipants.length > 0">
                        <span>Total Terpilih: <span class="text-[#1b3bbb] font-extrabold" x-text="selectedParticipants.length"></span> Peserta</span>
                    </template>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" @click="participantModalOpen = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-[#2e2552] text-xs font-bold rounded-xl transition-all cursor-pointer">
                        Tutup
                    </button>
                    <button type="button" 
                            @click="if(validateAdminSelection()) { participantModalOpen = false; }" 
                            :class="selectedParticipants.length === 0 ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-[#1b3bbb] hover:bg-[#09103c] text-white shadow-md shadow-[#1b3bbb]/20 cursor-pointer'"
                            class="px-5 py-2 text-xs font-extrabold rounded-xl transition-all">
                        Simpan Peserta
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

