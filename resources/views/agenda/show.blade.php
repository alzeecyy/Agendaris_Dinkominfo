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
                    <form action="{{ route('agenda.destroy', $agenda->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda ini beserta seluruh presensi/notulensinya?')">
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
                            
                            <div class="space-y-1 bg-[#8e88dd]/10 p-4 border border-[#8e88dd]/20 rounded-2xl">
                                <label class="block text-xs font-bold text-[#2e2552] uppercase">Nomor Surat Dasar Pelaksanaan</label>
                                <input type="text" name="nomor_surat_dasar" value="{{ $agenda->nomor_surat_dasar }}" class="w-full mt-1.5 px-4 py-2.5 bg-white border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
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

    <!-- MAIN GRID DETAIL: Left Panel (Content), Right Panel (Presensi/Notulensi) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Panel: Info Detail Agenda -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 md:p-8 shadow-sm space-y-6">
                <!-- Category badge -->
                <div class="flex items-center justify-between">
                    @php
                        $badgeStyles = [
                            'rapat' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'sosialisasi' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'pelatihan' => 'bg-lime-50 text-lime-700 border-lime-200',
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

                <!-- Dasar Pelaksanaan -->
                <div class="p-4 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Dasar Pelaksanaan / Surat Undangan atau Tugas</h3>
                    <p class="text-xs text-[#2e2552] font-semibold leading-relaxed">
                        {!! $agenda->nomor_surat_dasar ? e($agenda->nomor_surat_dasar) : '<span class="text-[#8e88dd] italic">Belum diisi oleh Sekretaris.</span>' !!}
                    </p>
                </div>
            </div>

            <!-- RENDER NOTULENSI DOKUMEN JIKA DISAHKAN -->
            @if($agenda->notulensi && $agenda->notulensi->status === 'disahkan')
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 md:p-8 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-[#d4d1f5]/40 pb-4 gap-3">
                        <div>
                            <span class="px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-600 border border-emerald-200 text-[10px] font-bold uppercase tracking-wide">Disahkan ✓</span>
                            <h2 class="text-base font-bold text-[#2e2552] mt-1">Notulensi Hasil Rapat Resmi</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('notulensi.export.pdf', $agenda->id) }}" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/15 flex items-center gap-1.5 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>PDF</span>
                            </a>
                            <a href="{{ route('notulensi.export.docx', $agenda->id) }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-555 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-600/15 flex items-center gap-1.5 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <span>Word</span>
                            </a>
                        </div>
                    </div>

                    <!-- Notulensi Contents display -->
                    <div class="space-y-6 text-sm text-[#2e2552]">
                        <div class="space-y-1.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Ringkasan Rapat</h4>
                            <p class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed">{{ $agenda->notulensi->ringkasan }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Poin Pembahasan</h4>
                            <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed whitespace-pre-line">{{ $agenda->notulensi->pembahasan }}</div>
                        </div>
                        <div class="space-y-1.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Daftar Keputusan</h4>
                            <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed whitespace-pre-line text-emerald-600 font-bold">{{ $agenda->notulensi->keputusan }}</div>
                        </div>
                        <div class="space-y-1.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#5a508f]">Kesimpulan</h4>
                            <div class="bg-[#f8f7ff] p-4 border border-[#d4d1f5]/40 rounded-2xl leading-relaxed whitespace-pre-line">{{ $agenda->notulensi->kesimpulan }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Panel: Presensi & Notulensi Actions -->
        <div class="space-y-6">
            
            <!-- 1. ABSENSI DIGITAL (Pegawai Internal Mandiri) -->
            @if($agenda->butuh_presensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Absensi Digital</h3>
                    
                    @if($ownPresensi)
                        @php
                            $statusColors = [
                                'hadir' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                'izin' => 'bg-amber-50 text-amber-600 border-amber-200',
                                'sakit' => 'bg-rose-50 text-rose-600 border-rose-200'
                            ];
                            $statusLabels = [
                                'hadir' => 'Hadir ✓',
                                'izin' => 'Izin Terdaftar ✓',
                                'sakit' => 'Sakit Terdaftar ✓'
                            ];
                        @endphp
                        <div class="w-full text-center py-3 border rounded-xl text-xs font-bold {{ $statusColors[$ownPresensi->status] }}">
                            Kehadiran Anda: {{ $statusLabels[$ownPresensi->status] }}
                        </div>
                    @else
                        <div x-data="{ openOptions: false }" class="relative w-full">
                            <button @click="openOptions = !openOptions" 
                                    class="w-full py-3 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                                <span>Klik Presensi Kehadiran</span>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': openOptions}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="openOptions" @click.away="openOptions = false" x-cloak 
                                 class="absolute top-full left-0 w-full mt-2 bg-white border border-[#d4d1f5] rounded-2xl shadow-2xl overflow-hidden z-20">
                                <form action="{{ route('agenda.absen', $agenda->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" name="status" value="hadir" class="w-full px-4 py-3 text-left text-xs font-bold text-emerald-600 hover:bg-[#f8f7ff] transition-colors border-b border-[#d4d1f5]/40">
                                        Hadir (Masuk Rapat)
                                    </button>
                                    <button type="submit" name="status" value="izin" class="w-full px-4 py-3 text-left text-xs font-bold text-amber-600 hover:bg-[#f8f7ff] transition-colors border-b border-[#d4d1f5]/40">
                                        Izin (Kegiatan Dinas Lain)
                                    </button>
                                    <button type="submit" name="status" value="sakit" class="w-full px-4 py-3 text-left text-xs font-bold text-rose-600 hover:bg-[#f8f7ff] transition-colors">
                                        Sakit
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- 2. STATUS NOTULENSI AI (Hanya Rapat) -->
            @if($agenda->kategori === 'rapat' && $agenda->notulensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Dokumentasi Notulensi</h3>
                    
                    @php
                        $notulenLabels = [
                            'draft' => 'Draft Belum Direview',
                            'menunggu_review' => 'Menunggu Review Ketua',
                            'disahkan' => 'Telah Disahkan Pimpinan',
                        ];
                        $notulenColors = [
                            'draft' => 'bg-blue-50 text-blue-600 border-blue-200',
                            'menunggu_review' => 'bg-amber-55 text-amber-600 border-amber-200',
                            'disahkan' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                        ];
                    @endphp
                    
                    <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-3">
                        <span class="text-xs text-[#5a508f]">Status Notulen:</span>
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full border font-bold uppercase {{ $notulenColors[$agenda->notulensi->status] }}">
                            {{ $notulenLabels[$agenda->notulensi->status] }}
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
                        @else
                            <p class="text-xs text-[#5a508f] text-center py-3 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl font-medium">
                                Menunggu verifikasi dari pimpinan berwenang.
                            </p>
                        @endif
                    @elseif($agenda->notulensi->status === 'disahkan')
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-600 font-bold text-center flex items-center justify-center gap-2">
                            <span>Notulensi Disetujui Pimpinan</span>
                            <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
            @endif

            <!-- 3. TABEL REKAPITULASI PRESENSI INTERNAL -->
            @if($agenda->butuh_presensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Rekap Kehadiran Bidang</h3>
                    
                    <div class="space-y-3">
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
                                <div class="grid grid-cols-4 gap-2 text-center text-[10px] font-bold text-[#5a508f]">
                                    <div class="bg-emerald-50 text-emerald-600 py-1 rounded-lg border border-emerald-100">Hadir: {{ $rc->hadir }}</div>
                                    <div class="bg-amber-50 text-amber-600 py-1 rounded-lg border border-amber-100">Izin: {{ $rc->izin }}</div>
                                    <div class="bg-rose-50 text-rose-600 py-1 rounded-lg border border-rose-100">Sakit: {{ $rc->sakit }}</div>
                                    <div class="bg-slate-100 text-slate-500 py-1 rounded-lg border border-slate-200">Belum: {{ $rc->belum }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 4. KELOLA PRESENSI MANUAL (Hanya Sekretaris) -->
                @if($isSecretaryOfAgenda)
                    <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#2e2552]">Koreksi Presensi Pegawai</h3>
                        
                        <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
                            @foreach($participants as $part)
                                <div class="flex items-center justify-between gap-2 p-2 bg-[#f8f7ff] border border-[#d4d1f5]/20 rounded-2xl">
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-[#2e2552] truncate">{{ $part->name }}</div>
                                        <div class="text-[9px] text-[#5a508f] truncate font-medium">{{ $part->jabatan }}</div>
                                    </div>
                                    
                                    <form action="{{ route('agenda.absen.koreksi', $agenda->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $part->id }}">
                                        <select name="status" onchange="this.form.submit()" 
                                                class="text-[10px] bg-white border border-[#d4d1f5] rounded-xl text-[#2e2552] px-2 py-1 font-bold focus:outline-none">
                                            <option value="Belum Absen" {{ $part->status_presensi === 'Belum Absen' ? 'selected' : '' }}>Belum Absen</option>
                                            <option value="hadir" {{ $part->status_presensi === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="izin" {{ $part->status_presensi === 'izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="sakit" {{ $part->status_presensi === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                        </select>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- DETAIL MODAL FOR ATTENDEES TABLE -->
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
                            <th class="py-3 px-3">NIP</th>
                            <th class="py-3 px-3">Jabatan</th>
                            <th class="py-3 px-3 text-center">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#d4d1f5]/20">
                        <template x-for="p in detailParticipants" :key="p.id">
                            <tr class="hover:bg-[#f8f7ff] transition-colors">
                                <td class="py-3 px-3 font-bold" x-text="p.name"></td>
                                <td class="py-3 px-3 font-mono text-[10px] text-[#5a508f]" x-text="p.nip"></td>
                                <td class="py-3 px-3 text-slate-700" x-text="p.jabatan"></td>
                                <td class="py-3 px-3 text-center font-bold">
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg border text-[9px] uppercase tracking-wider"
                                          :class="{
                                              'bg-emerald-55 text-emerald-600 border-emerald-200': p.status_presensi === 'hadir',
                                              'bg-amber-50 text-amber-600 border-amber-200': p.status_presensi === 'izin',
                                              'bg-rose-55 text-rose-600 border-rose-200': p.status_presensi === 'sakit',
                                              'bg-slate-100 text-slate-400 border-slate-200': p.status_presensi === 'Belum Absen'
                                          }"
                                          x-text="p.status_presensi === 'Belum Absen' ? 'Belum Absen' : p.status_presensi">
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="detailParticipants.length === 0">
                            <td colspan="4" class="py-6 text-center text-[#8e88dd] italic font-medium">Tidak ada pegawai terdaftar di bidang ini.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 bg-[#f8f7ff] border-t border-[#d4d1f5]/40 flex justify-end">
                <button @click="openDetailModal = false" class="px-4 py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white text-xs font-bold rounded-2xl shadow-sm">Tutup</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('agendaDetail', () => ({
        openDetailModal: false,
        selectedBidangName: '',
        detailParticipants: [],
        allParticipants: @json($participants),
        tempat: '{{ $initialTempat }}',
        tempatLainnya: '{{ $initialTempatLainnya }}',
        get combinedLokasi() {
            return this.tempat === 'Lainnya' ? this.tempatLainnya : this.tempat;
        },
        showBidangDetails(bidId, bidName) {
            this.selectedBidangName = bidName;
            this.detailParticipants = this.allParticipants.filter(p => p.bidang_id == bidId);
            this.openDetailModal = true;
        }
    }));
});
</script>
@endsection
@endsection
