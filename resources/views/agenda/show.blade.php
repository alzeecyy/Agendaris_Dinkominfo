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
<div x-data="{
    openDetailModal: false,
    openAbsenModal: false,
    openGuestModal: false,
    openNomorSuratModal: false,
    status: 'hadir',
    keterangan: '',
    signatureData: '',
    isSignatureEmpty: true,
    signaturePad: null,
    selectedBidangName: '',
    detailParticipants: [],
    get allParticipants() {
        try {
            const root = document.querySelector('[data-participants]');
            return JSON.parse(root ? root.getAttribute('data-participants') : '[]');
        } catch(e) {
            return [];
        }
    },
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
            canvas.getContext('2d').scale(ratio, ratio);

            if (this.signaturePad) {
                this.signaturePad.off();
            }

            this.signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: '#09103c'
            });

            this.signaturePad.addEventListener('beginStroke', () => {
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
}" data-participants='@json($participants)' class="space-y-6">
    
    <!-- Breadcrumbs / Back button -->
    <div class="flex items-center justify-between gap-2">
        <a href="{{ route('calendar', ['date' => $agenda->tanggal->toDateString()]) }}" 
           class="inline-flex items-center gap-1.5 text-[11px] sm:text-xs font-bold text-[#5a508f] hover:text-[#2e2552] transition-colors min-w-0">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7"></path>
            </svg>
            <span class="truncate">Kembali ke Kalender Rinci</span>
        </a>
        
        @if($isSecretaryOfAgenda)
            <!-- Edit Agenda Trigger (Sekretaris only) -->
            <div x-data="{ 
                openEditModal: {{ $errors->any() ? 'true' : 'false' }}, 
                tempat: '{{ addslashes($initialTempat) }}', 
                tempatLainnya: '{{ addslashes($initialTempatLainnya) }}',
                get combinedLokasi() {
                    return this.tempat === 'Lainnya' ? this.tempatLainnya : this.tempat;
                }
            }">
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button @click="openEditModal = true" 
                            class="px-2.5 py-1.5 sm:px-4 sm:py-2 bg-white border border-[#d4d1f5] hover:bg-[#8e88dd]/15 text-[11px] sm:text-xs font-bold text-[#2e2552] rounded-xl transition-all shadow-sm shrink-0">
                        Edit Agenda
                    </button>
                    <form action="{{ route('agenda.destroy', $agenda->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus agenda ini beserta seluruh presensi/notulensinya?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-2.5 py-1.5 sm:px-4 sm:py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-[11px] sm:text-xs font-bold rounded-xl transition-all shadow-sm shrink-0">
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
                            
                            @if($errors->any())
                                <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs space-y-1">
                                    <p class="font-bold flex items-center gap-1.5">
                                        <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span>Perubahan Agenda Gagal Disimpan:</span>
                                    </p>
                                    <ul class="list-disc list-inside pl-2 space-y-0.5 font-medium">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
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
                                    <select id="tempat_edit" name="lokasi" required
                                            class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                                        <option value="" disabled {{ empty($agenda->lokasi) ? 'selected' : '' }}>Pilih Lokasi / Ruangan</option>
                                        <option value="Aula Rapat Dinkominfo" {{ $agenda->lokasi === 'Aula Rapat Dinkominfo' ? 'selected' : '' }}>Aula Rapat Dinkominfo</option>
                                        <option value="Ruang Pelatihan" {{ $agenda->lokasi === 'Ruang Pelatihan' ? 'selected' : '' }}>Ruang Pelatihan</option>
                                        <option value="Smart Room Graha Satria" {{ $agenda->lokasi === 'Smart Room Graha Satria' ? 'selected' : '' }}>Smart Room Graha Satria</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-[#5a508f] uppercase">Kategori <span class="text-rose-500">*</span></label>
                                    <select name="kategori" required class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                                        <option value="" disabled {{ empty($agenda->kategori) ? 'selected' : '' }}>Pilih Kategori</option>
                                        <option value="rapat" {{ $agenda->kategori === 'rapat' ? 'selected' : '' }}>Rapat</option>
                                        <option value="sosialisasi" {{ $agenda->kategori === 'sosialisasi' ? 'selected' : '' }}>Sosialisasi</option>
                                        <option value="pelatihan" {{ $agenda->kategori === 'pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                        <option value="kegiatan_lainnya" {{ $agenda->kategori === 'kegiatan_lainnya' ? 'selected' : '' }}>Kegiatan Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#5a508f] uppercase">Deskripsi</label>
                                <textarea name="deskripsi" rows="3" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">{{ $agenda->deskripsi }}</textarea>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-[#5a508f] uppercase">Nomor Surat Dasar</label>
                                <input type="text" name="nomor_surat_dasar" value="{{ old('nomor_surat_dasar', $agenda->nomor_surat_dasar) }}" placeholder="Contoh: 005/123/2026 Perihal Undangan Rapat" class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-[#2e2552] text-sm focus:outline-none">
                            </div>
                            

                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-[#5a508f] uppercase">Bidang & Peserta Rapat <span class="text-rose-500">*</span></label>
                                @php
                                    $hakAksesArray = $agenda->hak_akses;
                                    $isSemua = in_array('semua_orang', $hakAksesArray);
                                    $allBidangs = $bidangsWithUsers;
                                    $allBidangIds = $allBidangs->pluck('id')->map(fn($id) => (string)$id)->toArray();
                                    $totalBidangCount = count($allBidangIds);

                                    if (Auth::user()->isSekretarisBidang()) {
                                        $initialBidangs = array_values(array_unique(array_merge([ (string)Auth::user()->bidang_id ], $isSemua ? $allBidangIds : array_filter($hakAksesArray, fn($x) => $x !== 'semua_orang'))));
                                    } else {
                                        $initialBidangs = $isSemua ? $allBidangIds : array_values(array_map(fn($x) => (string)$x, array_filter($hakAksesArray, fn($x) => $x !== 'semua_orang')));
                                    }

                                    if ($agenda->participants()->exists()) {
                                        $initialParticipants = $agenda->participants->pluck('id')->map(fn($id) => (string)$id)->toArray();
                                    } else {
                                        $initialParticipants = [];
                                        foreach ($allBidangs as $b) {
                                            if (in_array((string)$b->id, $initialBidangs)) {
                                                foreach ($b->users as $u) {
                                                    $initialParticipants[] = (string)$u->id;
                                                }
                                            }
                                        }
                                    }

                                    $bidangsUserData = $allBidangs->map(function($b) {
                                        return [
                                            'id' => (string)$b->id,
                                            'nama' => $b->nama,
                                            'singkatan' => $b->singkatan,
                                            'users' => $b->users->map(function($u) {
                                                return [
                                                    'id' => (string)$u->id,
                                                    'name' => $u->name,
                                                    'nip' => $u->nip ?? '-',
                                                    'jabatan' => $u->jabatan ?? '-',
                                                ];
                                            })->values()->toArray(),
                                        ];
                                    })->values()->toArray();
                                @endphp
                                <div x-data='{
                                    semua: {{ $isSemua ? "true" : "false" }},
                                    allBidangIds: {{ json_encode(array_values($allBidangIds)) }},
                                    totalCount: {{ $totalBidangCount }},
                                    bidangs: {{ json_encode(array_values($initialBidangs)) }},
                                    isSekBid: {{ Auth::user()->isSekretarisBidang() ? "true" : "false" }},
                                    ownBidangId: "{{ Auth::user()->bidang_id }}",
                                    searchParticipant: '',

                                    filteredUsers(users) {
                                        if (!this.searchParticipant || !this.searchParticipant.trim()) return users;
                                        let q = this.searchParticipant.toLowerCase().trim();
                                        return users.filter(u => (u.name && u.name.toLowerCase().includes(q)) || (u.jabatan && u.jabatan.toLowerCase().includes(q)));
                                    },

                                    toggleSemua() {
                                        if (this.semua) {
                                            this.bidangs = Array.from(this.allBidangIds);
                                        } else {
                                            this.bidangs = [];
                                        }
                                        this.syncParticipants();
                                    },

                                    check(id) {
                                        if (this.isSekBid) {
                                            if (!this.bidangs.includes(this.ownBidangId)) {
                                                this.bidangs.push(this.ownBidangId);
                                            }
                                            if (this.bidangs.length > 3) {
                                                Swal.fire({
                                                    title: 'Batas Maksimal Bidang',
                                                    text: 'Sekretaris / Admin Bidang hanya dapat memilih maksimal 3 bidang (bidang Anda + maksimal 2 bidang tambahan).',
                                                    icon: 'warning',
                                                    confirmButtonText: 'Mengerti',
                                                    confirmButtonColor: '#1b3bbb',
                                                    customClass: {
                                                        popup: 'rounded-3xl shadow-2xl border border-[#d4d1f5]',
                                                        confirmButton: 'px-5 py-2.5 bg-[#1b3bbb] text-white text-xs font-bold rounded-xl shadow-md'
                                                    }
                                                });
                                                this.bidangs = this.bidangs.filter(bId => String(bId) !== String(id));
                                            }
                                        }
                                        this.semua = (this.bidangs.length === this.totalCount);
                                        this.syncParticipants();
                                    },

                                    syncParticipants() {
                                        let activeUserIds = [];
                                        this.bidangsUserData.forEach(b => {
                                            if (this.bidangs.includes(b.id)) {
                                                b.users.forEach(u => {
                                                    activeUserIds.push(u.id);
                                                });
                                            }
                                        });
                                        let newSelection = this.selectedParticipants.filter(id => activeUserIds.includes(id));
                                        activeUserIds.forEach(id => {
                                            if (!newSelection.includes(id)) {
                                                newSelection.push(id);
                                            }
                                        });
                                        this.selectedParticipants = newSelection;
                                    },

                                    toggleBidangUsers(bidangId) {
                                        let b = this.bidangsUserData.find(item => item.id === bidangId);
                                        if (!b) return;
                                        let bUserIds = b.users.map(u => u.id);
                                        let allChecked = bUserIds.every(id => this.selectedParticipants.includes(id));

                                        if (!allChecked) {
                                            bUserIds.forEach(id => {
                                                if (!this.selectedParticipants.includes(id)) {
                                                    this.selectedParticipants.push(id);
                                                }
                                            });
                                        } else {
                                            this.selectedParticipants = this.selectedParticipants.filter(id => !bUserIds.includes(id));
                                        }
                                    },

                                    isBidangAllChecked(bidangId) {
                                        let b = this.bidangsUserData.find(item => item.id === bidangId);
                                        if (!b || b.users.length === 0) return false;
                                        return b.users.every(u => this.selectedParticipants.includes(u.id));
                                    }
                                }'>
                                    <!-- Hidden Inputs for Selected Participants -->
                                    <template x-for="userId in selectedParticipants" :key="userId">
                                        <input type="hidden" name="participants[]" :value="userId">
                                    </template>

                                    @if(Auth::user()->isSekretarisBidang())
                                        <input type="hidden" name="bidangs[]" value="{{ Auth::user()->bidang_id }}">
                                    @else
                                        <label class="flex items-center text-xs text-[#2e2552] font-bold mb-1 cursor-pointer select-none">
                                            <input type="checkbox" name="semua_orang" value="1" x-model="semua" @change="toggleSemua()" class="mr-2 rounded border-[#d4d1f5] text-[#8e88dd]">
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

                                    <!-- Kelola Peserta Button Bar -->
                                    <div class="pt-2.5 mt-2 border-t border-[#d4d1f5]/40 flex items-center justify-between">
                                        <button type="button" @click="participantModalOpen = true" 
                                                :class="selectedParticipants.length === 0 ? 'bg-rose-50 border-rose-300 text-rose-700' : 'bg-[#f3f2fe] hover:bg-[#e4e1fb] text-[#2e2552] border-[#d4d1f5]'"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-xl text-xs font-bold transition-all cursor-pointer active:scale-95">
                                            <svg class="w-4 h-4" :class="selectedParticipants.length === 0 ? 'text-rose-600' : 'text-[#8e88dd]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            <span>Kelola Peserta</span>
                                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold" :class="selectedParticipants.length === 0 ? 'bg-rose-600 text-white animate-pulse' : 'bg-[#2e2552] text-white'" x-text="selectedParticipants.length"></span>
                                        </button>
                                        <span :class="selectedParticipants.length === 0 ? 'text-rose-600 font-extrabold animate-pulse' : 'text-[#5a508f] font-medium'" class="text-[11px]" x-text="selectedParticipants.length === 0 ? '⚠️ Minimal 1 peserta!' : selectedParticipants.length + ' peserta diundang'"></span>
                                    </div>

                                    <!-- KELOLA PESERTA MODAL -->
                                    <div x-show="participantModalOpen" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-xs select-none">
                                        <div @click.away="participantModalOpen = false" class="bg-white rounded-2xl md:rounded-3xl shadow-2xl border border-slate-200/80 w-full max-w-xl flex flex-col max-h-[85vh] overflow-hidden animate-in fade-in zoom-in duration-200">
                                            
                                            <div class="px-5 py-4 bg-gradient-to-r from-[#09103c] via-[#1b3bbb] to-[#09103c] text-white flex items-center justify-between shrink-0">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="p-2 bg-white/10 rounded-xl border border-white/15">
                                                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-extrabold text-white">Kelola Peserta Rapat</h3>
                                                        <p class="text-[11px] text-indigo-100 font-medium">Hilangkan centang jika terdapat anggota bidang yang tidak diundang</p>
                                                    </div>
                                                </div>
                                                <button @click="participantModalOpen = false" type="button" class="p-1.5 bg-white/10 hover:bg-rose-500 rounded-xl text-white transition-all cursor-pointer">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1">
                                                <!-- Search Bar for Participants -->
                                                <div class="relative">
                                                    <input type="text" x-model="searchParticipant" placeholder="Cari nama atau jabatan peserta..." 
                                                           class="w-full pl-9 pr-8 py-2 bg-slate-100/90 border border-slate-200/90 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#1b3bbb] focus:ring-2 focus:ring-[#1b3bbb]/10 transition-all font-medium">
                                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>
                                                    <button type="button" x-show="searchParticipant.length > 0" @click="searchParticipant = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>

                                                <template x-if="bidangs.length === 0">
                                                    <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                                        <p class="text-xs text-slate-500 font-bold">Pilih minimal satu bidang di atas terlebih dahulu untuk mengelola peserta.</p>
                                                    </div>
                                                </template>

                                                <template x-for="bidang in bidangsUserData.filter(b => bidangs.includes(b.id))" :key="bidang.id">
                                                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 space-y-2.5">
                                                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-2.5 h-2.5 rounded-full bg-[#1b3bbb]"></span>
                                                                <span class="text-xs font-black text-[#09103c]" x-text="bidang.nama + ' (' + bidang.singkatan + ')'"></span>
                                                            </div>
                                                            <button type="button" @click="toggleBidangUsers(bidang.id)" class="text-[10.5px] font-extrabold text-[#1b3bbb] hover:underline cursor-pointer">
                                                                <span x-text="isBidangAllChecked(bidang.id) ? 'Hapus Centang Semua' : 'Centang Semua'"></span>
                                                            </button>
                                                        </div>

                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                                            <template x-for="user in filteredUsers(bidang.users)" :key="user.id">
                                                                <label class="flex items-start gap-2.5 p-2 bg-white rounded-xl border border-slate-200/60 hover:border-indigo-200 cursor-pointer select-none transition-all">
                                                                    <input type="checkbox" :value="user.id" x-model="selectedParticipants" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mt-0.5 shrink-0">
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-800 leading-tight truncate" x-text="user.name"></div>
                                                                        <div class="text-[10px] text-slate-500 font-medium truncate" x-text="user.jabatan"></div>
                                                                    </div>
                                                                </label>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Modal Footer -->
                                            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                                                <div class="text-xs font-bold text-slate-600 flex items-center gap-1">
                                                    <template x-if="selectedParticipants.length === 0">
                                                        <span class="text-rose-600 font-black flex items-center gap-1">⚠️ Pilih minimal 1 peserta!</span>
                                                    </template>
                                                    <template x-if="selectedParticipants.length > 0">
                                                        <span>Total Terpilih: <span class="text-[#1b3bbb] font-black" x-text="selectedParticipants.length"></span> Peserta</span>
                                                    </template>
                                                </div>
                                                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                                                    <button type="button" @click="participantModalOpen = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer">
                                                        Tutup
                                                    </button>
                                                    <button type="button" 
                                                            @click="if(selectedParticipants.length === 0) { alert('Pilih minimal 1 peserta rapat.'); } else { participantModalOpen = false; }" 
                                                            :class="selectedParticipants.length === 0 ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-[#1b3bbb] hover:bg-indigo-700 text-white shadow-md cursor-pointer'"
                                                            class="px-5 py-2 text-xs font-extrabold rounded-xl transition-all">
                                                        Simpan Peserta
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
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

    <!-- TOP GRID: Card Rapat (Left) vs Absensi/Notulensi/Rekap (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5 sm:gap-6 items-stretch">
        
        <!-- Left Column: Info Detail Agenda -->
        <div class="space-y-2.5 sm:space-y-6 min-w-0 flex flex-col">
            <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-2.5 sm:p-6 md:p-8 shadow-sm space-y-2.5 sm:space-y-6 h-full flex flex-col justify-between">
                <div class="space-y-2.5 sm:space-y-6">
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
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] sm:text-xs font-bold border 
                            {{ $badgeStyles[$agenda->kategori] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                            {{ $kategoriLabels[$agenda->kategori] ?? $agenda->kategori }}
                        </span>
                        <span class="text-[9.5px] sm:text-xs text-[#5a508f]">Dibuat oleh: <strong class="text-[#2e2552]">{{ $agenda->sekretaris->name }}</strong></span>
                    </div>

                    <div class="space-y-1 sm:space-y-2">
                        <h1 class="text-sm sm:text-2xl font-black text-[#2e2552] tracking-wide leading-snug">{{ $agenda->judul }}</h1>
                        <p class="text-[10px] sm:text-xs text-[#5a508f] leading-relaxed">{{ $agenda->deskripsi ?? 'Tidak ada deskripsi tambahan.' }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 sm:gap-4 py-2 sm:py-4 border-t border-b border-[#d4d1f5]/40 text-xs">
                        <!-- Tanggal -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="p-1.5 sm:p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-lg sm:rounded-2xl text-[#2e2552] shrink-0">
                                <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[8.5px] sm:text-[10px] text-[#8e88dd] uppercase font-bold">Tanggal</p>
                                <p class="text-[10.5px] sm:text-xs font-bold text-[#2e2552] mt-0.5">{{ $agenda->tanggal->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                        <!-- Jam -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="p-1.5 sm:p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-lg sm:rounded-2xl text-[#2e2552] shrink-0">
                                <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[8.5px] sm:text-[10px] text-[#8e88dd] uppercase font-bold">Waktu</p>
                                <p class="text-[10.5px] sm:text-xs font-bold text-[#2e2552] mt-0.5">{{ substr($agenda->jam_mulai, 0, 5) }} - {{ substr($agenda->jam_selesai, 0, 5) }} WIB</p>
                            </div>
                        </div>
                        <!-- Lokasi -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="p-1.5 sm:p-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-lg sm:rounded-2xl text-[#2e2552] shrink-0">
                                <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[8.5px] sm:text-[10px] text-[#8e88dd] uppercase font-bold">Lokasi</p>
                                <p class="text-[10.5px] sm:text-xs font-bold text-[#2e2552] mt-0.5">{{ $agenda->lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nomor Surat -->
                <div class="p-4 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-2xl space-y-2.5 mt-4">
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
                                    Belum diisi oleh Sekretaris. Klik untuk mengisi...
                                </button>
                            @else
                                <span class="text-[#8e88dd] italic">Belum diisi oleh Sekretaris.</span>
                            @endif
                        @endif
                    </p>
                </div>
            </div>
        </div>

    <!-- QUICK MODAL: EDIT NOMOR SURAT -->
    @if($isSecretaryOfAgenda || Auth::user()->isAdmin())
        <div x-show="openNomorSuratModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
            <div @click.away="openNomorSuratModal = false" class="bg-white border border-[#d4d1f5]/60 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden relative text-[#2e2552] animate-in fade-in zoom-in duration-200">
                <div class="h-1.5 w-full bg-gradient-to-r from-[#1b3bbb] to-indigo-600"></div>
                <div class="p-5 border-b border-[#d4d1f5]/40 flex items-center justify-between bg-slate-50/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#1b3bbb]/10 text-[#1b3bbb] border border-[#1b3bbb]/20 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-[#09103c]">Isi / Ubah Nomor Surat</h3>
                            <p class="text-[10.5px] text-slate-500 font-medium">Lengkapi nomor surat pelaksanaan agenda</p>
                        </div>
                    </div>
                    <button type="button" @click="openNomorSuratModal = false" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Right Column: Absensi Digital, Notulensi & Rekap Kehadiran Bidang -->
        <div class="flex flex-col gap-6 min-w-0 h-full">
            <!-- 1. ABSENSI DIGITAL (Pegawai Internal Mandiri) -->
            @if($agenda->butuh_presensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-2xl md:rounded-[32px] p-3.5 sm:p-6 shadow-sm space-y-3 sm:space-y-4">
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
                                        class="w-full py-1.5 sm:py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5">
                                    <span>Isi Presensi Kehadiran</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                            </div>
                        @else
                            <!-- KONDISI 2: Berlangsung normal saat waktu rapat -->
                            <div class="space-y-2">
                                <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs">
                                    <span class="text-[#5a508f] font-medium flex items-center gap-1 text-[10px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Status Kehadiran:</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 font-extrabold uppercase text-[9px] border border-emerald-300">Belum Absen</span>
                                </div>
                                <button @click="openAbsenModal = true; initSignaturePad()" 
                                        class="w-full py-1.5 sm:py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5">
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

            <!-- 2. STATUS NOTULENSI AI (Hanya Rapat) ATAU REKAP KEHADIRAN BIDANG (Jika Non-Rapat / Tanpa Notulensi) -->
            @if($agenda->kategori === 'rapat' && $agenda->notulensi)
                <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-2.5 sm:p-6 shadow-sm space-y-2.5 sm:space-y-4 flex-1 flex flex-col justify-between">
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
                        } elseif ($notulensi->status === 'draft') {
                            if ($hasDraftContent) {
                                $notulenLabel = 'Draft Belum Direview';
                                $notulenColor = 'bg-blue-50 text-blue-600 border-blue-200';
                            } else {
                                $notulenLabel = 'Belum Ada Draft';
                                $notulenColor = 'bg-slate-100 text-slate-500 border-slate-200';
                            }
                        } elseif ($notulensi->status === 'menunggu_review') {
                            $notulenLabel = 'Menunggu Review Ketua';
                            $notulenColor = 'bg-amber-50 text-amber-600 border-amber-200';
                        } elseif ($notulensi->status === 'disahkan') {
                            $notulenLabel = 'Telah Disahkan Pimpinan';
                            $notulenColor = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                        } else {
                            $notulenLabel = strtoupper($notulensi->status);
                            $notulenColor = 'bg-slate-50 text-slate-600 border-slate-200';
                        }
                    @endphp
                    
                    <div class="flex flex-col gap-2.5 sm:gap-4">
                        <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-2">
                            <span class="text-[11px] sm:text-xs text-[#5a508f]">Status Notulen:</span>
                            <span class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase {{ $notulenColor }}">
                                {{ $notulenLabel }}
                            </span>
                        </div>

                        @if($agenda->notulensi->status === 'draft')
                            @if($isSecretaryOfAgenda)
                                @if($agenda->notulensi->transkrip_error)
                                    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-2.5 sm:p-4 rounded-xl text-xs space-y-2">
                                        <p class="font-bold flex items-center gap-1">
                                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>Transkripsi Belum Berhasil Diproses</span>
                                        </p>
                                        <p class="text-[10.5px] text-rose-700 leading-relaxed font-medium">Transkripsi belum berhasil diproses. Silakan coba lagi menggunakan rekaman yang tersimpan.</p>
                                        <form action="{{ route('notulensi.process.audio', $agenda->id) }}" method="POST" onsubmit="if (typeof window.showHeavyLoading === 'function') window.showHeavyLoading('Memproses Ulang AI', 'Menjalankan ulang proses transkripsi AI audio rapat...');">
                                            @csrf
                                            <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center justify-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                <span>Coba Lagi Transkripsi AI</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                @if($agenda->notulensi->catatan_revisi)
                                    <div class="bg-rose-50 border border-rose-200 text-rose-700 p-2.5 sm:p-4 rounded-xl text-xs space-y-1">
                                        <p class="font-bold">Butuh Revisi / Perbaikan:</p>
                                        <p class="italic">"{{ $agenda->notulensi->catatan_revisi }}"</p>
                                    </div>
                                @endif
                                <a href="{{ route('notulensi.edit', $agenda->id) }}" 
                                   class="w-full py-1.5 sm:py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5">
                                    <span>Kelola Transkrip & AI Notulen</span>
                                </a>
                            @else
                                <p class="text-xs text-[#8e88dd] text-center py-2 italic">
                                    {{ $hasDraftContent ? 'Draf notulensi rapat sedang dirapikan oleh sekretaris.' : 'Notulensi rapat belum diunggah/dibuat oleh sekretaris.' }}
                                </p>
                            @endif
                        @elseif($agenda->notulensi->status === 'menunggu_review')
                            @if($isApproverOfAgenda)
                                <a href="{{ route('notulensi.review', $agenda->id) }}" 
                                   class="w-full py-1.5 sm:py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                                    <span>Tinjau & Sahkan Notulensi</span>
                                </a>
                            @endif

                            @if($isSecretaryOfAgenda)
                                <div class="space-y-1.5 sm:space-y-2">
                                    <a href="{{ route('notulensi.edit', $agenda->id) }}" 
                                       class="w-full py-1.5 sm:py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5">
                                        <span>Kelola & Edit Notulensi</span>
                                    </a>
                                    <a href="{{ route('notulensi.review', $agenda->id) }}" 
                                       class="w-full py-1.5 sm:py-2 bg-slate-100 hover:bg-slate-200 text-[#5a508f] font-bold text-[11px] sm:text-xs rounded-xl border border-[#d4d1f5] transition-all flex items-center justify-center gap-1.5">
                                        <span>Pratinjau Mode Baca</span>
                                    </a>
                                </div>
                            @endif

                            @if(!$isApproverOfAgenda && !$isSecretaryOfAgenda)
                                <p class="text-xs text-[#5a508f] text-center py-2.5 bg-[#f8f7ff] border border-[#d4d1f5]/40 rounded-xl font-medium">
                                    Menunggu verifikasi dari pimpinan berwenang.
                                </p>
                            @endif
                        @elseif($agenda->notulensi->status === 'disahkan')
                            <a href="{{ route('notulensi.review', $agenda->id) }}" 
                               class="w-full py-1.5 sm:py-2.5 bg-[#2e2552] hover:bg-[#3d326a] text-white font-bold text-[11px] sm:text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                <span>Lihat Notulensi Rapat Resmi</span>
                            </a>
                        @endif
                    </div>
                </div>
            @elseif($agenda->butuh_presensi && (Auth::user()->role !== 'staff' || Auth::user()->isSekretariat()))
                <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-2.5 sm:p-6 shadow-sm space-y-2.5 sm:space-y-4 flex-1 flex flex-col justify-between">
                    <div class="flex items-center justify-between border-b border-[#d4d1f5]/40 pb-2">
                        <h3 class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-[#2e2552]">Rekap Kehadiran Bidang</h3>
                        <span class="text-[9.5px] text-[#5a508f] font-bold">Klik untuk detail</span>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($recap as $rc)
                            <div @click="showBidangDetails({{ $rc->bidang_id }}, '{{ addslashes($rc->bidang_nama) }}')" 
                                 class="p-2.5 bg-[#f8f7ff] border border-[#d4d1f5]/40 hover:border-[#8e88dd] hover:bg-[#f3f2fe] rounded-xl text-xs space-y-1.5 cursor-pointer transition-all duration-200 shadow-sm group">
                                <div class="font-bold text-[#2e2552] flex items-center justify-between gap-2">
                                    <span class="truncate group-hover:text-[#1b3bbb] transition-colors font-extrabold text-[11px] sm:text-xs">{{ $rc->bidang_nama }}</span>
                                    <span class="text-[8.5px] text-[#8e88dd] font-bold uppercase tracking-wider flex items-center gap-0.5 shrink-0 bg-white px-1.5 py-0.5 rounded-md border border-[#d4d1f5]/60 shadow-xs">
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
            @endif
        </div>
    </div>

    <!-- BOTTOM SECTION: FULL-WIDTH KOREKSI PRESENSI PEGAWAI -->
    @if($agenda->butuh_presensi && ($isSecretaryOfAgenda || Auth::user()->role !== 'staff' || Auth::user()->isSekretariat()))
        <div class="bg-white border border-[#d4d1f5]/60 rounded-xl md:rounded-[32px] p-2.5 sm:p-6 shadow-sm space-y-2.5 sm:space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#d4d1f5]/40 pb-3">
                <div>
                    <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-[#2e2552]">Koreksi Presensi Pegawai</h3>
                    <p class="text-[10.5px] sm:text-xs text-[#5a508f] font-medium mt-0.5">Ubah status presensi pegawai atau tambahkan tamu eksternal secara manual.</p>
                </div>
                @if($isSecretaryOfAgenda)
                    <button @click="openGuestModal = true" class="px-3 py-1.5 sm:px-4 sm:py-2.5 bg-[#1b3bbb] hover:bg-[#09103c] text-white text-[11px] sm:text-xs font-bold rounded-xl transition-all shadow-md shadow-[#1b3bbb]/20 flex items-center justify-center gap-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <span>+ Tamu Eksternal</span>
                    </button>
                @endif
            </div>
            
            <div class="max-h-[460px] overflow-y-auto pr-1">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($participants as $part)
                        <div class="flex items-center justify-between gap-3 p-3.5 bg-[#f8f7ff] border border-[#d4d1f5]/40 hover:border-[#8e88dd]/60 rounded-2xl shadow-xs transition-all">
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-[#2e2552] truncate" title="{{ $part->name }}">{{ $part->name }}</div>
                                <div class="text-[10px] text-[#5a508f] truncate font-medium mt-0.5" title="{{ $part->jabatan }}">{{ $part->jabatan }}</div>
                            </div>
                            @if($isSecretaryOfAgenda)
                                <form action="{{ route('agenda.absen.koreksi', $agenda->id) }}" method="POST" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $part->id }}">
                                    <select name="status" onchange="this.form.submit()" 
                                            class="text-[11px] bg-white border border-[#d4d1f5] rounded-xl text-[#2e2552] px-3 py-1.5 font-bold focus:outline-none focus:ring-1 focus:ring-[#8e88dd] cursor-pointer shadow-xs">
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
                            @else
                                <span class="text-[10px] px-2.5 py-1 rounded-xl font-bold uppercase border"
                                      class="{{ $part->status_presensi === 'hadir' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $part->status_presensi }}
                                </span>
                            @endif
                        </div>
                    @endforeach

                    @foreach($externalParticipants as $guest)
                        <div class="flex items-center justify-between gap-3 p-3.5 bg-[#f0effd] border border-[#d4d1f5]/60 rounded-2xl shadow-xs">
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
                                <form action="{{ route('notulensi.external.delete', $guest->id) }}" method="POST" class="shrink-0" data-confirm="Apakah Anda yakin ingin menghapus tamu eksternal ini?">
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
        </div>
    @endif

    <!-- DETAIL MODAL FOR ATTENDEES TABLE -->
    @if(Auth::user()->role !== 'staff' || Auth::user()->isSekretariat())
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
                allParticipants: @json((Auth::user()->role === 'staff' && !Auth::user()->isSekretariat()) ? [] : $participants),
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
                    } else {
                        this.signatureData = '';
                    }

                    const form = e.target;
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
                        btn.innerHTML = `<span class="inline-flex items-center justify-center">${spinnerSvg}<span>Mengirim Presensi...</span></span>`;
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
</div>
@endsection

