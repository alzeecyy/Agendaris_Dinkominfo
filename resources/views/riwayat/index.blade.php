@extends('layouts.app')

@section('title', 'Riwayat Kegiatan')

@section('content')
<div x-data="{ 
    searchQuery: '',
    filterKategori: '',
    filterTanggal: '',
    filterStatus: '',
    currentPage: 1,
    itemsPerPage: 10,
    agendas: [
        @foreach($riwayatData as $item)
        {
            id: {{ $item->id }},
            judul: '{{ addslashes($item->judul) }}',
            kategori: '{{ $item->kategori }}',
            tanggal: '{{ $item->tanggal->toDateString() }}',
            status_kehadiran: '{{ $item->status_kehadiran ?? '' }}',
            lokasi: '{{ addslashes($item->lokasi) }}'
        },
        @endforeach
    ],
    matchesFilter(judul, kategori, tanggalStr, statusKehadiran) {
        const matchesSearch = !this.searchQuery || 
            judul.toLowerCase().includes(this.searchQuery.toLowerCase());
            
        const matchesKategori = !this.filterKategori || kategori === this.filterKategori;
        
        const matchesTanggal = !this.filterTanggal || tanggalStr === this.filterTanggal;
        
        let matchesStatus = true;
        if (this.filterStatus) {
            if (this.filterStatus === 'none') {
                matchesStatus = !statusKehadiran;
            } else {
                matchesStatus = statusKehadiran === this.filterStatus;
            }
        }
        
        return matchesSearch && matchesKategori && matchesTanggal && matchesStatus;
    },
    get filteredAgendas() {
        return this.agendas.filter(a => {
            const matchesSearch = !this.searchQuery || 
                a.judul.toLowerCase().includes(this.searchQuery.toLowerCase());
                
            const matchesKategori = !this.filterKategori || a.kategori === this.filterKategori;
            
            const matchesTanggal = !this.filterTanggal || a.tanggal === this.filterTanggal;
            
            let matchesStatus = true;
            if (this.filterStatus) {
                if (this.filterStatus === 'none') {
                    matchesStatus = !a.status_kehadiran;
                } else {
                    matchesStatus = a.status_kehadiran === this.filterStatus;
                }
            }
            
            return matchesSearch && matchesKategori && matchesTanggal && matchesStatus;
        });
    },
    get totalPages() {
        return Math.ceil(this.filteredAgendas.length / this.itemsPerPage) || 1;
    },
    isAgendaVisible(agendaId) {
        const index = this.filteredAgendas.findIndex(a => a.id === agendaId);
        if (index === -1) return false;
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return index >= start && index < end;
    },
    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.stripeRows();
        }
    },
    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.stripeRows();
        }
    },
    setPage(page) {
        this.currentPage = page;
        this.stripeRows();
    },
    resetPagination() {
        this.currentPage = 1;
        this.stripeRows();
    },
    stripeRows() {
        this.$nextTick(() => {
            let visibleIndex = 0;
            document.querySelectorAll('.agenda-row').forEach(row => {
                if (row.style.display !== 'none') {
                    if (visibleIndex % 2 === 0) {
                        row.classList.remove('bg-[#fcfbff]');
                    } else {
                        row.classList.add('bg-[#fcfbff]');
                    }
                    visibleIndex++;
                }
            });
        });
    }
}" 
x-init="
    $watch('searchQuery', () => resetPagination());
    $watch('filterKategori', () => resetPagination());
    $watch('filterTanggal', () => resetPagination());
    $watch('filterStatus', () => resetPagination());
    stripeRows();
"
class="space-y-6">
    <div>
        <h1 class="text-xl font-black text-[#2e2552] tracking-wide">Riwayat Kegiatan & Rapat</h1>
        <p class="text-xs text-[#5a508f] mt-0.5">Arsip seluruh kegiatan dan status kehadiran Anda di Dinkominfo</p>
    </div>

    <!-- History Table Card -->
    <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Search Agenda Name -->
            <div class="relative">
                <input type="text" x-model="searchQuery" placeholder="Cari nama agenda..."
                       class="w-full pl-10 pr-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-xs text-[#2e2552] focus:outline-none focus:ring-2 focus:ring-[#8e88dd]">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#5a508f]/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Kategori Filter -->
            <div>
                <select x-model="filterKategori" 
                        class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-xs text-[#2e2552] focus:outline-none">
                    <option value="">Semua Kategori</option>
                    <option value="rapat">Rapat</option>
                    <option value="sosialisasi">Sosialisasi</option>
                    <option value="pelatihan">Pelatihan</option>
                    <option value="kegiatan_lainnya">Kegiatan Lainnya</option>
                </select>
            </div>
            
            <!-- Tanggal Filter -->
            <div>
                <input type="date" x-model="filterTanggal" 
                       class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-xs text-[#2e2552] focus:outline-none">
            </div>
            
            <!-- Status Filter -->
            <div>
                <select x-model="filterStatus" 
                        class="w-full px-4 py-2.5 bg-[#f3f2fe] border border-[#d4d1f5] rounded-2xl text-xs text-[#2e2552] focus:outline-none">
                    <option value="">Semua Kehadiran</option>
                    <option value="hadir">Hadir</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="none">Belum Absen (-)</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2e2552]">
                <thead class="text-xs font-bold uppercase tracking-wider text-[#5a508f] border-b border-[#d4d1f5]/40">
                    <tr>
                        <th class="py-4 px-4">Nama Agenda Kegiatan</th>
                        <th class="py-4 px-4">Kategori</th>
                        <th class="py-4 px-4">Tanggal & Jam</th>
                        <th class="py-4 px-4">Lokasi</th>
                        <th class="py-4 px-4 text-center">Status Kehadiran</th>
                        <th class="py-4 px-4 text-right">Notulensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#d4d1f5]/30">
                    <!-- Client-side Empty State for filters -->
                    <tr x-show="filteredAgendas.length === 0" class="hover:bg-transparent">
                        <td colspan="6" class="py-8 px-4 text-center text-[#8e88dd] italic font-medium">Tidak ada riwayat kegiatan yang cocok dengan kriteria filter.</td>
                    </tr>
                    @forelse($riwayatData as $item)
                        <tr class="agenda-row hover:bg-[#f8f7ff] transition-colors"
                            x-show="matchesFilter('{{ addslashes($item->judul) }}', '{{ $item->kategori }}', '{{ $item->tanggal->toDateString() }}', '{{ $item->status_kehadiran }}') && isAgendaVisible({{ $item->id }})">
                            <td class="py-4 px-4 font-bold text-[#2e2552]">
                                <a href="{{ route('agenda.show', $item->id) }}" class="hover:text-[#8e88dd] transition-colors">
                                    {{ $item->judul }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
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
                                <span class="inline-block text-[10px] px-2.5 py-0.5 font-bold uppercase rounded-lg border 
                                    {{ $badgeStyles[$item->kategori] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                    {{ $kategoriLabels[$item->kategori] ?? $item->kategori }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs font-semibold">
                                <div>{{ $item->tanggal->translatedFormat('d M Y') }}</div>
                                <div class="text-[#8e88dd] mt-0.5 font-bold">{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</div>
                            </td>
                            <td class="py-4 px-4 text-xs text-[#5a508f] font-medium truncate max-w-[150px]" title="{{ $item->lokasi }}">
                                {{ $item->lokasi }}
                            </td>
                            <td class="py-4 px-4 text-center text-xs">
                                @if($item->status_kehadiran === 'hadir')
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 font-bold">Hadir ✓</span>
                                @elseif($item->status_kehadiran === 'izin')
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-amber-50 text-amber-600 border border-amber-200 font-bold">Izin</span>
                                @elseif($item->status_kehadiran === 'sakit')
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 font-bold">Sakit</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 border border-slate-200 font-semibold">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right text-xs">
                                @if($item->notulensi_status === 'disahkan')
                                    <div class="flex items-center justify-end gap-2 font-bold">
                                        <a href="{{ route('notulensi.export.pdf', $item->id) }}" class="text-rose-600 hover:text-rose-500 transition-colors">PDF</a>
                                        <span class="text-[#d4d1f5]">|</span>
                                        <a href="{{ route('notulensi.export.docx', $item->id) }}" class="text-blue-600 hover:text-blue-500 transition-colors">Word</a>
                                    </div>
                                @else
                                    <span class="text-[#8e88dd] italic font-medium">Belum Disahkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-[#8e88dd] italic font-medium">Tidak terdapat data riwayat kegiatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination controls -->
        <div x-show="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between border-t border-[#d4d1f5]/40 pt-4 mt-4 text-xs font-bold text-[#5a508f] gap-4">
            <!-- Showing x to y of z entries -->
            <div>
                Menampilkan 
                <span x-text="Math.min((currentPage - 1) * itemsPerPage + 1, filteredAgendas.length)"></span>
                sampai
                <span x-text="Math.min(currentPage * itemsPerPage, filteredAgendas.length)"></span>
                dari
                <span x-text="filteredAgendas.length"></span>
                kegiatan
            </div>
            
            <!-- Page buttons -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <!-- Previous Button -->
                <button @click="prevPage()" :disabled="currentPage === 1"
                        class="p-2 rounded-xl border border-[#d4d1f5] hover:bg-[#8e88dd]/10 disabled:opacity-40 disabled:hover:bg-transparent transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <!-- Page numbers -->
                <template x-for="p in totalPages" :key="p">
                    <button @click="setPage(p)"
                            x-text="p"
                            class="px-3.5 py-2 rounded-xl border transition-all duration-200"
                            :class="currentPage === p ? 'bg-[#2e2552] text-white border-[#2e2552] shadow-sm' : 'border-[#d4d1f5] hover:bg-[#8e88dd]/10'">
                    </button>
                </template>
                
                <!-- Next Button -->
                <button @click="nextPage()" :disabled="currentPage === totalPages"
                        class="p-2 rounded-xl border border-[#d4d1f5] hover:bg-[#8e88dd]/10 disabled:opacity-40 disabled:hover:bg-transparent transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
