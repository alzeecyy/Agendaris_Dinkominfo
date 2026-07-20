@extends('layouts.app')

@section('title', 'Detail Agenda')

@section('content')
@php
    $predefinedRooms = [
        'Ruang Rapat Kartini', 'Aula Utama Kominfo', 'Ruang Rapat Kepala Dinas',
        'Ruang PPID', 'Ruang Bidang IKP', 'Ruang Server TIK', 'Ruang Bidang Aptika', 'Ruang Bidang Statistik & Persandian'
    ];
    $isPredefined = in_array($agenda->lokasi, $predefinedRooms);
    $initialTempat = $isPredefined ? $agenda->lokasi : 'Lainnya';
    $initialTempatLainnya = $isPredefined ? '' : $agenda->lokasi;
@endphp
<div x-data="agendaDetail" class="space-y-6">
    
    <!-- Breadcrumbs / Back button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('calendar', ['date' => $agenda->tanggal->toDateString()]) }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-[#5a508f] hover:text-[#2e2552] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path>
            </svg>
            <span>Kembali ke Kalender Rinci</span>
        </a>
        
        @if($isSecretaryOfAgenda)
            <!-- Edit Agenda Trigger (Sekretaris only) -->
            <div x-data="{ openEditModal: false }">
                <div class="flex items-center gap-2">
                    <button @click="openEditModal = true" 
                            class="px-4 py-2 bg-white border border-[#d4d1f5] hover:bg-[#8e88dd]/15 text-xs font-bold text-[#2e2552] rounded-xl transition-all shadow-sm">
                        Edit Agenda
                    </button>
                    <form action="{{ route('agenda.destroy', $agenda->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus agenda ini beserta seluruh presensi/notulensinya?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold rounded-xl transition-all shadow-sm">
                            Hapus Agenda
                        </button>
                    </form>
                </div>

                <!-- EDIT MODAL -->
                <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
                    <div @click.away="openEditModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden relative">
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-[#2e2552] to-[#8e88dd]"></div>
                        <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#2e2552]">Edit Agenda Kegiatan</h3>
                            <button @click="openEditModal = false" class="text-[#5a508f] hover:text-[#2e2552]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <form action="{{ route('agenda.update', $agenda->id) }}" method="POST" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-[#2e2552]">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#5a508f] uppercase">Judul Kegiatan / Rapat <span class="text-rose-500">*</span></label>
                                <input type="text" name="judul" required value="{{ $agenda->judul }}" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:ring-2 focus:ring-[#8e88dd] focus:outline-none">
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Tanggal <span class="text-rose-500">*</span></label>
                                    <input type="date" name="tanggal" required value="{{ $agenda->tanggal->toDateString() }}"
                                           min="{{ now()->subMonths(6)->toDateString() }}"
                                           max="{{ now()->addMonths(6)->toDateString() }}"
                                           class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Mulai <span class="text-rose-500">*</span></label>
                                    <input type="time" name="jam_mulai" required value="{{ substr($agenda->jam_mulai, 0, 5) }}" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Selesai <span class="text-rose-500">*</span></label>
                                    <input type="time" name="jam_selesai" required value="{{ substr($agenda->jam_selesai, 0, 5) }}" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                                </div>
                            </div>
                            <!-- Tempat / Ruangan Dropdown & Kategori Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label for="tempat_edit" class="block text-xs font-bold text-[#5a508f] uppercase">Tempat / Ruangan <span class="text-rose-500">*</span></label>
                                    <select id="tempat_edit" x-model="tempat"
                                            class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                                        <option value="Ruang Rapat Kartini">Ruang Rapat Kartini (Gedung A)</option>
                                        <option value="Aula Utama Kominfo">Aula Utama Kominfo (Gedung A)</option>
                                        <option value="Ruang Rapat Kepala Dinas">Ruang Rapat Kepala Dinas (Gedung A)</option>
                                        <option value="Ruang PPID">Ruang PPID (Gedung B)</option>
                                        <option value="Ruang Bidang IKP">Ruang Bidang IKP (Gedung B)</option>
                                        <option value="Ruang Server TIK">Ruang Server TIK (Gedung B)</option>
                                        <option value="Ruang Bidang Aptika">Ruang Bidang Aptika (Gedung B)</option>
                                        <option value="Ruang Bidang Statistik & Persandian">Ruang Bidang Statistik & Persandian (Gedung B)</option>
                                        <option value="Lainnya">Lainnya (Isi Kustom)...</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Kategori <span class="text-rose-500">*</span></label>
                                    <select name="kategori" required class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                                        <option value="rapat" {{ $agenda->kategori === 'rapat' ? 'selected' : '' }}>Rapat</option>
                                        <option value="sosialisasi" {{ $agenda->kategori === 'sosialisasi' ? 'selected' : '' }}>Sosialisasi</option>
                                        <option value="pelatihan" {{ $agenda->kategori === 'pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                        <option value="kegiatan_lainnya" {{ $agenda->kategori === 'kegiatan_lainnya' ? 'selected' : '' }}>Kegiatan Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Custom Tempat text field (show if Lainnya is selected) -->
                            <div x-show="tempat === 'Lainnya'" x-transition class="space-y-1">
                                <label for="tempat_lainnya_edit" class="block text-xs font-bold text-[#5a508f] uppercase">Nama Tempat Baru <span class="text-rose-500">*</span></label>
                                <input type="text" id="tempat_lainnya_edit" x-model="tempatLainnya" placeholder="Contoh: Ruang Tamu Sekretariat"
                                       class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                            </div>

                            <input type="hidden" name="lokasi" :value="combinedLokasi">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#5a508f] uppercase">Deskripsi</label>
                                <textarea name="deskripsi" rows="3" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">{{ $agenda->deskripsi }}</textarea>
                            </div>
                            

                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-[#5a508f] uppercase">Hak Akses / Peserta Rapat <span class="text-rose-500">*</span></label>
                                @php
                                    $hakAksesArray = $agenda->hak_akses;
                                    $isSemua = in_array('semua_orang', $hakAksesArray);
                                    $allBidangs = \App\Models\Bidang::all();
                                @endphp
                                <div x-data="{
                                    semua: {{ $isSemua ? 'true' : 'false' }},
                                    @if(Auth::user()->isSekretarisBidang())
                                        bidangs: [{!! implode(',', array_map(fn($id) => "'" . e($id) . "'", array_values(array_unique(array_merge([ (string)Auth::user()->bidang_id ], array_filter($hakAksesArray, fn($x) => $x !== 'semua_orang')))))) !!}],
                                        isSekBid: true,
                                        ownBidangId: '{{ Auth::user()->bidang_id }}',
                                    @else
                                        bidangs: [{!! implode(',', array_map(fn($id) => "'" . e($id) . "'", array_values(array_filter($hakAksesArray, fn($x) => $x !== 'semua_orang')))) !!}],
                                        isSekBid: false,
                                        ownBidangId: null,
                                    @endif
                                    toggle() {
                                        if (this.semua) this.bidangs = ['1', '2', '3'];
                                        else this.bidangs = [];
                                    },
                                    check(id) {
                                        if (this.isSekBid) {
                                            if (!this.bidangs.includes(this.ownBidangId)) {
                                                this.bidangs.push(this.ownBidangId);
                                            }
                                            if (3 <= this.bidangs.length) {
                                                alert('Sekretaris Bidang hanya dapat memilih maksimal 1 bidang tambahan.');
                                                this.bidangs = [this.ownBidangId, id];
                                            }
                                        }
                                        this.semua = this.bidangs.length === 3;
                                    }
                                }">
                                    @if(Auth::user()->isSekretarisBidang())
                                        <!-- Enforce own bidang submission -->
                                        <input type="hidden" name="bidangs[]" value="{{ Auth::user()->bidang_id }}">
                                    @else
                                        <label class="flex items-center text-xs text-[#2e2552] font-bold mb-1 cursor-pointer select-none">
                                            <input type="checkbox" name="semua_orang" value="1" x-model="semua" @change="toggle()" class="mr-2 rounded border-[#d4d1f5] text-[#8e88dd]">
                                            Semua Orang (Lintas Dinas)
                                        </label>
                                    @endif

                                    <div class="grid grid-cols-1 gap-2 {{ Auth::user()->isSekretarisBidang() ? '' : 'pl-6' }} mt-1">
                                        @foreach($allBidangs as $b)
                                            <label class="flex items-center text-xs text-[#5a508f] cursor-pointer select-none font-medium">
                                                <input type="checkbox" name="bidangs[]" value="{{ $b->id }}" x-model="bidangs" @change="check('{{ $b->id }}')"
                                                       @if(Auth::user()->isSekretarisBidang() && Auth::user()->bidang_id == $b->id) disabled @endif
                                                       class="mr-2 rounded border-[#d4d1f5] text-[#8e88dd]">
                                                <span class="{{ Auth::user()->isSekretarisBidang() && Auth::user()->bidang_id == $b->id ? 'font-bold text-[#2e2552]' : '' }}">
                                                    {{ $b->nama }}
                                                    @if(Auth::user()->isSekretarisBidang() && Auth::user()->bidang_id == $b->id)
                                                        <span class="text-[9px] text-[#5a508f] lowercase ml-1">(Wajib ikut / Tidak bisa di-uncheck)</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl">
                                <span class="text-xs font-bold text-[#2e2552]">Butuh Presensi Digital?</span>
                                <input type="checkbox" name="butuh_presensi" value="1" {{ $agenda->butuh_presensi ? 'checked' : '' }} class="rounded border-[#d4d1f5] text-[#8e88dd]">
                            </div>

                            <div class="flex items-center justify-end gap-2 border-t border-[#d4d1f5]/40 pt-4">
                                <button type="button" @click="openEditModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] text-xs font-bold rounded-2xl">Batal</button>
                                <button type="submit" class="px-5 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- TOP GRID: Card Rapat (Left) vs Absensi/Notulensi (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        
        <!-- Left Column: Info Detail Agenda & Disahkan Notulensi -->
        <div class="space-y-6 min-w-0">
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 md:p-8 shadow-sm space-y-6 h-full flex flex-col justify-between">
                <div class="space-y-6">
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
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border 
                            {{ $badgeStyles[$agenda->kategori] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                            {{ $kategoriLabels[$agenda->kategori] ?? $agenda->kategori }}
                        </span>
                        <span class="text-xs text-[#5a508f]">Dibuat oleh: <strong class="text-[#2e2552]">{{ $agenda->sekretaris->name }}</strong></span>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-black text-[#2e2552] tracking-wide leading-snug">{{ $agenda->judul }}</h1>
                        <p class="text-xs text-[#5a508f] leading-relaxed">{{ $agenda->deskripsi ?? 'Tidak ada deskripsi tambahan.' }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4 border-t border-b border-[#d4d1f5]/40 text-sm">
                        <!-- Tanggal -->
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-[#8e88dd] uppercase font-bold">Tanggal</p>
                                <p class="text-xs font-bold text-[#2e2552] mt-0.5">{{ $agenda->tanggal->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                        <!-- Jam -->
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-[#8e88dd] uppercase font-bold">Waktu</p>
                                <p class="text-xs font-bold text-[#2e2552] mt-0.5">{{ substr($agenda->jam_mulai, 0, 5) }} - {{ substr($agenda->jam_selesai, 0, 5) }} WIB</p>
                            </div>
                        </div>
                        <!-- Lokasi -->
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-[#8e88dd] uppercase font-bold">Lokasi</p>
                                <p class="text-xs font-bold text-[#2e2552] mt-0.5">{{ $agenda->lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nomor Surat -->
                <div class="p-4 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl space-y-2 mt-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Nomor Surat</h3>
                    <p class="text-xs text-[#2e2552] font-semibold leading-relaxed">
                        {!! $agenda->nomor_surat_dasar ? e($agenda->nomor_surat_dasar) : '<span class="text-[#8e88dd] italic">Belum diisi oleh Sekretaris.</span>' !!}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Absensi Digital & Dokumentasi Notulensi -->
        <div class="flex flex-col gap-6 min-w-0 h-full justify-between">
            <!-- 1. ABSENSI DIGITAL (Pegawai Internal Mandiri) -->
            @if($agenda->butuh_presensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm flex-1 flex flex-col justify-between gap-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Absensi Digital</h3>
                    
                    @if($ownPresensi)
                        @php
                            $statusColors = [
                                'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'izin' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'sakit' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'alfa' => 'bg-red-50 text-red-700 border-red-200'
                            ];
                            $statusLabels = [
                                'hadir' => 'Hadir ✓',
                                'izin' => 'Izin Terdaftar ✓',
                                'sakit' => 'Sakit Terdaftar ✓',
                                'alfa' => 'Alfa (Tidak Hadir) ✓'
                            ];
                        @endphp
                        <div class="space-y-3">
                            <div class="w-full text-center py-3 border rounded-xl text-xs font-bold {{ $statusColors[$ownPresensi->status] }}">
                                Kehadiran Anda: {{ $statusLabels[$ownPresensi->status] }}
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
                                    <div class="border border-slate-100 rounded-lg p-2 bg-white mt-1 flex items-center justify-center h-16 w-36 overflow-hidden shadow-inner">
                                        <img src="{{ asset('storage/' . $ownPresensi->tanda_tangan) }}" alt="Tanda Tangan" class="max-h-full max-w-full object-contain">
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        @if($agenda->isPresensiExpired())
                            <div class="bg-red-50/60 border border-red-200/80 rounded-2xl p-4 space-y-3">
                                <div class="flex items-start gap-2.5">
                                    <div class="p-1 bg-red-100 text-red-600 rounded-lg shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="space-y-0.5 text-left">
                                        <h4 class="text-[11px] font-bold text-red-800 leading-tight">Batas Waktu Presensi Berakhir</h4>
                                        <p class="text-[10px] text-red-600/90 leading-relaxed font-medium">Pengisian presensi mandiri dibatasi maksimal 1 jam setelah rapat selesai.</p>
                                    </div>
                                </div>
                                <div class="border-t border-red-200/40"></div>
                                <div class="flex items-center justify-between text-xs bg-red-100/60 border border-red-200/50 rounded-xl px-3 py-2">
                                    <span class="text-[9px] font-bold text-red-700 uppercase tracking-wider">Status Kehadiran:</span>
                                    <span class="text-[10px] font-black text-white bg-red-600 px-2.5 py-0.5 rounded-md">ALFA</span>
                                </div>
                            </div>
                        @else
                            <div class="space-y-3">
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-2xl flex items-center justify-between text-xs">
                                    <span class="text-[#5a508f] font-medium flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                        <span>Status Kehadiran:</span>
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-extrabold uppercase text-[10px] border border-rose-300">Belum Absen</span>
                                </div>
                                <button @click="openAbsenModal = true; initSignaturePad()" 
                                        class="w-full py-3 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                                    <span>Isi Presensi Kehadiran</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <!-- 2. STATUS NOTULENSI AI (Hanya Rapat) -->
            @if($agenda->kategori === 'rapat' && $agenda->notulensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm flex-1 flex flex-col justify-between gap-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Dokumentasi Notulensi</h3>
                    
                    @php
                        $notulenLabels = [
                            'draft' => 'Draft Belum Direview',
                            'menunggu_review' => 'Menunggu Review Ketua',
                            'disahkan' => 'Telah Disahkan Pimpinan',
                        ];
                        $notulenColors = [
                            'draft' => 'bg-blue-50 text-blue-600 border-blue-200',
                            'menunggu_review' => 'bg-amber-50 text-amber-600 border-amber-200',
                            'disahkan' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                        ];
                    @endphp
                    
                    <div class="flex flex-col flex-1 justify-between gap-4">
                        <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-3">
                            <span class="text-xs text-[#5a508f]">Status Notulen:</span>
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full border font-bold uppercase {{ $notulenColors[$agenda->notulensi->status] ?? 'bg-slate-50 text-slate-600' }}">
                                {{ $notulenLabels[$agenda->notulensi->status] ?? $agenda->notulensi->status }}
                            </span>
                        </div>

                        @if($agenda->notulensi->status === 'draft')
                            @if($isSecretaryOfAgenda)
                                @if($agenda->notulensi->catatan_revisi)
                                    <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-2xl text-xs space-y-1">
                                        <p class="font-bold">Butuh Revisi / Perbaikan:</p>
                                        <p class="italic">"{{ $agenda->notulensi->catatan_revisi }}"</p>
                                    </div>
                                @endif
                                <a href="{{ route('notulensi.edit', $agenda->id) }}" 
                                   class="w-full py-3.5 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                                    <span>Kelola Transkrip & AI Notulen</span>
                                </a>
                            @else
                                <p class="text-xs text-[#8e88dd] text-center py-2 italic">Draf notulensi rapat sedang dirapikan oleh sekretaris.</p>
                            @endif
                        @elseif($agenda->notulensi->status === 'menunggu_review')
                            @if($isApproverOfAgenda)
                                <a href="{{ route('notulensi.review', $agenda->id) }}" 
                                   class="w-full py-3.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                                    <span>Tinjau & Sahkan Notulensi</span>
                                </a>
                            @endif

                            @if($isSecretaryOfAgenda)
                                <div class="space-y-2">
                                    <a href="{{ route('notulensi.edit', $agenda->id) }}" 
                                       class="w-full py-3 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                                        <span>Kelola & Edit Notulensi</span>
                                    </a>
                                    <a href="{{ route('notulensi.review', $agenda->id) }}" 
                                       class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-[#5a508f] font-bold text-xs rounded-xl border border-[#d4d1f5] transition-all flex items-center justify-center gap-2">
                                        <span>Pratinjau Mode Baca</span>
                                    </a>
                                </div>
                            @endif

                            @if(!$isApproverOfAgenda && !$isSecretaryOfAgenda)
                                <p class="text-xs text-[#5a508f] text-center py-3 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl font-medium">
                                    Menunggu verifikasi dari pimpinan berwenang.
                                </p>
                            @endif
                        @elseif($agenda->notulensi->status === 'disahkan')
                            <a href="{{ route('notulensi.review', $agenda->id) }}" 
                               class="w-full py-3 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                <span>Lihat Notulensi Rapat Resmi</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- BOTTOM GRID: Rekap Kehadiran Bidang (Left 1/3) vs Koreksi Presensi Pegawai (Right 2/3) -->
    @if($agenda->butuh_presensi && Auth::user()->role !== 'staff')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
            <!-- Left Column: Rekap Kehadiran Bidang -->
            <div class="{{ $isSecretaryOfAgenda ? 'lg:col-span-1' : 'lg:col-span-3' }} flex flex-col">
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4 h-full flex flex-col">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Rekap Kehadiran Bidang</h3>
                    
                    <div class="{{ $isSecretaryOfAgenda ? 'space-y-3' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4' }}">
                        @foreach($recap as $rc)
                            <div @click="showBidangDetails({{ $rc->bidang_id }}, '{{ addslashes($rc->bidang_nama) }}')" 
                                 class="p-3 bg-[#f8f7ff] border border-[#d4d1f5]/40 hover:border-[#8e88dd]/60 hover:bg-[#f3f2fe] rounded-2xl text-xs space-y-2 cursor-pointer transition-all duration-200 shadow-sm">
                                <div class="font-bold text-[#2e2552] flex items-center justify-between gap-2">
                                    <span class="truncate">{{ $rc->bidang_nama }}</span>
                                    <span class="text-[9px] text-[#8e88dd] font-bold uppercase tracking-wider flex items-center gap-0.5">
                                        <span>Detail</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="grid grid-cols-5 gap-1.5 text-center text-[9px] font-bold text-[#5a508f]">
                                    <div class="bg-emerald-50 text-emerald-600 py-1 rounded-lg border border-emerald-100">Hadir: {{ $rc->hadir }}</div>
                                    <div class="bg-amber-50 text-amber-600 py-1 rounded-lg border border-amber-100">Izin: {{ $rc->izin }}</div>
                                    <div class="bg-rose-50 text-rose-600 py-1 rounded-lg border border-rose-100">Sakit: {{ $rc->sakit }}</div>
                                    <div class="bg-red-50 text-red-600 py-1 rounded-lg border border-red-100">Alfa: {{ $rc->alfa }}</div>
                                    <div class="bg-slate-100 text-slate-500 py-1 rounded-lg border border-slate-200">Belum: {{ $rc->belum }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Koreksi Presensi Pegawai (Spans 2 columns, list arranged in a 2-column grid) -->
            @if($isSecretaryOfAgenda)
                <div class="lg:col-span-2 flex flex-col">
                    <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4 h-full flex flex-col">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Koreksi Presensi Pegawai</h3>
                            <button @click="openGuestModal = true" class="px-3.5 py-2 bg-[#2e2552] hover:bg-[#3d326a] text-white text-[10px] font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                <span>+ Tamu Eksternal</span>
                            </button>
                        </div>
                        
                        <div class="max-h-96 overflow-y-auto pr-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($participants as $part)
                                    <div class="flex items-center justify-between gap-3 p-3 bg-[#f8f7ff] border border-[#d4d1f5]/20 rounded-2xl shadow-sm">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-[#2e2552] truncate" title="{{ $part->name }}">{{ $part->name }}</div>
                                            <div class="text-[9px] text-[#5a508f] truncate font-medium mt-0.5" title="{{ $part->jabatan }}">{{ $part->jabatan }}</div>
                                        </div>
                                        
                                        <form action="{{ route('agenda.absen.koreksi', $agenda->id) }}" method="POST" class="shrink-0">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $part->id }}">
                                            <select name="status" onchange="this.form.submit()" 
                                                    class="text-[10px] bg-white border border-[#d4d1f5] rounded-xl text-[#2e2552] px-2.5 py-1 font-bold focus:outline-none focus:ring-1 focus:ring-[#8e88dd]">
                                                @if($agenda->isPresensiExpired())
                                                    <option value="alfa" {{ $part->status_presensi === 'alfa' ? 'selected' : '' }}>Alfa</option>
                                                @else
                                                    <option value="Belum Absen" {{ $part->status_presensi === 'Belum Absen' ? 'selected' : '' }}>Belum Absen</option>
                                                @endif
                                                <option value="hadir" {{ $part->status_presensi === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                <option value="izin" {{ $part->status_presensi === 'izin' ? 'selected' : '' }}>Izin</option>
                                                <option value="sakit" {{ $part->status_presensi === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                            </select>
                                        </form>
                                    </div>
                                @endforeach

                                @foreach($externalParticipants as $guest)
                                    <div class="flex items-center justify-between gap-3 p-3 bg-[#f0effd] border border-[#d4d1f5]/40 rounded-2xl shadow-sm">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <div class="text-xs font-bold text-[#2e2552] truncate" title="{{ $guest->nama }}">{{ $guest->nama }}</div>
                                                <span class="inline-block shrink-0 px-1.5 py-0.5 bg-[#8e88dd]/20 text-[#2e2552] text-[8px] font-black rounded uppercase tracking-wider">Tamu</span>
                                            </div>
                                            <div class="text-[9px] text-[#5a508f] truncate font-medium mt-0.5" title="{{ $guest->jabatan }} - {{ $guest->instansi }}">
                                                {{ $guest->jabatan }} di <strong>{{ $guest->instansi }}</strong>
                                            </div>
                                        </div>
                                        
                                        <form action="{{ route('notulensi.external.delete', $guest->id) }}" method="POST" class="shrink-0" data-confirm="Apakah Anda yakin ingin menghapus tamu eksternal ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-500 p-1.5 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus Tamu">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- DETAIL MODAL FOR ATTENDEES TABLE -->
    @if(Auth::user()->role !== 'staff')
    <div x-show="openDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="openDetailModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden relative text-[#2e2552]">
            <div class="absolute top-0 left-0 w-full h-[2px] bg-[#2e2552]"></div>
            
            <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#2e2552]">Detail Kehadiran Rapat</h3>
                    <p class="text-xs text-[#5a508f]" x-text="selectedBidangName"></p>
                </div>
                <button @click="openDetailModal = false" class="text-[#5a508f] hover:text-[#2e2552]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <table class="w-full text-left text-xs text-[#2e2552]">
                    <thead class="text-[10px] font-bold uppercase tracking-wider text-[#5a508f] border-b border-[#d4d1f5]/40">
                        <tr>
                            <th class="py-3 px-3">Nama Pegawai</th>
                            <th class="py-3 px-3">Jabatan</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3">Keterangan</th>
                            <th class="py-3 px-3 text-center">TTD</th>
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
             
            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-[#2e2552] to-[#8e88dd]"></div>

            <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#2e2552]">Tambah Tamu Eksternal</h3>
                    <p class="text-xs text-[#5a508f]">Masukkan nama undangan dari luar Dinkominfo</p>
                </div>
                <button @click="openGuestModal = false" class="text-[#5a508f] hover:text-[#2e2552] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            
            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-[#2e2552] to-[#8e88dd]"></div>

            <div class="p-6 border-b border-[#d4d1f5]/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#2e2552]">Isi Formulir Presensi Kehadiran</h3>
                    <p class="text-xs text-[#5a508f]">Konfirmasi kehadiran Anda pada agenda ini</p>
                </div>
                <button @click="openAbsenModal = false" class="text-[#5a508f] hover:text-[#2e2552] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <button type="submit" class="px-5 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl shadow-sm">
                        Kirim Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function registerAgendaDetail() {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('agendaDetail', () => ({
                openDetailModal: false,
                openAbsenModal: false,
                openGuestModal: false,
                status: 'hadir',
                keterangan: '',
                signatureData: '',
                isSignatureEmpty: true,
                signaturePad: null,
                selectedBidangName: '',
                detailParticipants: [],
                allParticipants: @json(Auth::user()->role === 'staff' ? [] : $participants),
                tempat: '{{ $initialTempat }}',
                tempatLainnya: '{{ $initialTempatLainnya }}',
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
                get combinedLokasi() {
                    return this.tempat === 'Lainnya' ? this.tempatLainnya : this.tempat;
                },
                showBidangDetails(bidId, bidName) {
                    this.selectedBidangName = bidName;
                    this.detailParticipants = this.allParticipants.filter(p => p.bidang_id == bidId);
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
                            alert('Tanda tangan digital wajib diisi sebelum mengirim presensi.');
                            return;
                        }
                        this.signatureData = this.signaturePad.toDataURL('image/png');
                    } else {
                        this.signatureData = '';
                    }
                }
            }));
        }
    }

    if (typeof Alpine !== 'undefined') {
        registerAgendaDetail();
    } else {
        document.addEventListener('alpine:init', registerAgendaDetail);
    }
    </script>
</div>
@endsection
