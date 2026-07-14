@extends('layouts.app')

@section('title', 'Riwayat Kegiatan')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-black text-[#2e2552] tracking-wide">Riwayat Kegiatan & Rapat</h1>
        <p class="text-xs text-[#5a508f] mt-0.5">Arsip seluruh kegiatan dan status kehadiran Anda di Dinkominfo</p>
    </div>

    <!-- History Table Card -->
    <div class="bg-white border border-[#d4d1f5]/60 rounded-[32px] p-6 shadow-sm overflow-hidden">
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
                    @forelse($riwayatData as $item)
                        <tr class="hover:bg-[#f8f7ff] transition-colors">
                            <td class="py-4 px-4 font-bold text-[#2e2552]">
                                <a href="{{ route('agenda.show', $item->id) }}" class="hover:text-[#8e88dd] transition-colors">
                                    {{ $item->judul }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
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
    </div>
</div>
@endsection
