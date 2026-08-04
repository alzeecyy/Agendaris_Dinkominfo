<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" 
      xmlns:w="urn:schemas-microsoft-com:office:word" 
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="utf-8">
    <title>Notulensi Rapat - {{ $agenda->id }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm 2cm 2cm 2cm;
        }
        body, p, table, td, th, div, span, li, a, h1, h2, h3, h4 {
            font-family: 'Times New Roman', Times, serif !important;
            color: #000000 !important;
        }
        body {
            font-size: 12pt;
            line-height: 1.5;
            color: #000000;
        }
        p {
            font-size: 12pt;
            line-height: 1.5;
            color: #000000;
            margin-top: 0;
            margin-bottom: 10pt;
            text-align: justify;
        }

        /* Kop Surat Official */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000000;
            margin-bottom: 20px;
        }
        .kop-logo {
            width: 85px;
            padding-bottom: 10px;
            vertical-align: middle;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
            padding-bottom: 10px;
            color: #000000;
        }
        .kop-text .subjudul-kabupaten {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.2;
            color: #000000;
            text-transform: uppercase;
        }
        .kop-text .judul-dinas {
            margin: 2px 0 0 0;
            font-size: 16pt;
            font-weight: bold;
            line-height: 1.2;
            color: #000000;
            text-transform: uppercase;
        }
        .kop-text .alamat-text {
            margin: 4px 0 0 0;
            font-size: 10pt;
            font-style: italic;
            line-height: 1.3;
            color: #000000;
        }

        /* Judul Dokumen (18 pt, Bold, Center) */
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 18pt;
            text-decoration: underline;
            margin-top: 15px;
            margin-bottom: 20px;
            text-transform: uppercase;
            color: #000000;
            letter-spacing: 0.5px;
        }

        /* Metadata Rapat (11 pt) */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11pt;
            color: #000000;
            line-height: 1.4;
        }
        .meta-label {
            width: 180px;
            font-weight: bold;
        }
        .meta-colon {
            width: 10px;
            text-align: center;
        }

        /* Heading Bagian (12 pt, Bold, UpperCase) */
        .section-header {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000000;
            padding-bottom: 3px;
            margin-top: 22px;
            margin-bottom: 10px;
            color: #000000;
        }

        /* Content Body, Paragraphs, Lists & Bullet Points (12 pt, Solid Black #000000, Line-height 1.5, Regular Weight) */
        .section-body,
        .section-body *,
        .section-body p,
        .section-body ul,
        .section-body ol,
        .section-body li,
        .section-body span,
        .section-body div,
        .section-body td,
        .section-body th,
        .section-body h1,
        .section-body h2,
        .section-body h3,
        .section-body h4,
        .section-body h5,
        .section-body h6 {
            font-family: 'Times New Roman', Times, serif !important;
            font-size: 12pt !important;
            line-height: 1.5 !important;
            color: #000000 !important;
            font-weight: normal !important;
        }
        .section-body {
            text-align: justify;
            margin-bottom: 15px;
        }
        .section-body p {
            margin-top: 0;
            margin-bottom: 10px;
            text-align: justify;
        }
        .section-body ul, .section-body ol {
            margin-top: 4px;
            margin-bottom: 10px;
            padding-left: 25px;
            color: #000000 !important;
        }
        .section-body li {
            margin-bottom: 4px;
            text-align: justify;
            color: #000000 !important;
        }
        .section-body strong, 
        .section-body strong *, 
        .section-body b, 
        .section-body b * {
            font-weight: bold !important;
            color: #000000 !important;
        }
        .section-body em, .section-body i {
            font-style: italic !important;
            color: #000000 !important;
        }

        /* Data Tables (11 pt, Border tipis hitam) */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .table-data th, .table-data td {
            border: 1px solid #000000;
            padding: 5px 7px;
            font-size: 11pt;
            color: #000000;
            vertical-align: middle;
            line-height: 1.3;
        }
        .table-data th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        /* Signature / Pengesahan */
        .sig-container {
            margin-top: 35px;
            width: 100%;
            page-break-inside: avoid;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-table td {
            vertical-align: top;
            font-size: 11pt;
            color: #000000;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <table class="kop-table">
        <tr>
            <td class="kop-text">
                <div class="subjudul-kabupaten">PEMERINTAH KABUPATEN BANYUMAS</div>
                <div class="judul-dinas">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                <div class="alamat-text">Jl. Kabupaten No. 1 Purwokerto Kode Pos 53115 Telp. (0281) 631776</div>
                <div class="alamat-text">Website: kominfo.banyumaskab.go.id &bull; Email: kominfo@banyumaskab.go.id</div>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">NOTULENSI RAPAT RESMI</div>

    <!-- METADATA RAPAT -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Agenda / Kegiatan</td>
            <td class="meta-colon">:</td>
            <td><strong>{{ $agenda->judul }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Hari, Tanggal</td>
            <td class="meta-colon">:</td>
            <td>{{ $agenda->tanggal->locale('id')->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Waktu Pelaksanaan</td>
            <td class="meta-colon">:</td>
            <td>Pukul {{ substr($agenda->jam_mulai, 0, 5) }} s.d. {{ substr($agenda->jam_selesai, 0, 5) }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Tempat / Lokasi</td>
            <td class="meta-colon">:</td>
            <td>{{ $agenda->lokasi }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nomor Surat Dasar</td>
            <td class="meta-colon">:</td>
            <td>{{ $agenda->nomor_surat_dasar ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pimpinan Rapat</td>
            <td class="meta-colon">:</td>
            <td>{{ $notulensi->approver->name ?? '-' }} ({{ $notulensi->approver->jabatan ?? '-' }})</td>
        </tr>
    </table>

    <!-- I. NOTULENSI RAPAT / RINGKASAN EKSEKUTIF -->
    <div class="section-header" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000000; padding-bottom: 3px; margin-top: 22px; margin-bottom: 10px; color: #000000;">I. RINGKASAN EKSEKUTIF RAPAT</div>
    <div class="section-body" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; font-weight: normal; color: #000000; line-height: 1.5;">
        {!! $notulensi->ringkasan_html ?? \Illuminate\Support\Str::markdown($notulensi->ringkasan ?? '-') !!}
    </div>

    <!-- II. POIN-POIN PEMBAHASAN UTAMA -->
    @if(!empty($notulensi->pembahasan))
        <div class="section-header" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000000; padding-bottom: 3px; margin-top: 22px; margin-bottom: 10px; color: #000000;">II. {{ mb_strtoupper($notulensi->pembahasan_title ?? 'POIN-POIN PEMBAHASAN UTAMA') }}</div>
        <div class="section-body" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; font-weight: normal; color: #000000; line-height: 1.5;">
            {!! $notulensi->pembahasan_html ?? \Illuminate\Support\Str::markdown($notulensi->pembahasan) !!}
        </div>
    @endif

    <!-- III. HASIL KEPUTUSAN & TINDAK LANJUT -->
    @if(!empty($notulensi->keputusan) || !empty($notulensi->kesimpulan))
        <div class="section-header" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000000; padding-bottom: 3px; margin-top: 22px; margin-bottom: 10px; color: #000000;">III. {{ mb_strtoupper($notulensi->keputusan_title ?? 'HASIL KEPUTUSAN & TINDAK LANJUT') }}</div>
        <div class="section-body" style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; font-weight: normal; color: #000000; line-height: 1.5;">
            @if(!empty($notulensi->keputusan))
                {!! $notulensi->keputusan_html ?? \Illuminate\Support\Str::markdown($notulensi->keputusan) !!}
            @endif
            @if(!empty($notulensi->kesimpulan))
                <div style="margin-top: 10px; font-weight: normal;">
                    <strong style="font-weight: bold;">Kesimpulan Akhir:</strong><br>
                    {!! $notulensi->kesimpulan_html ?? \Illuminate\Support\Str::markdown($notulensi->kesimpulan) !!}
                </div>
            @endif
        </div>
    @endif

    <!-- IV. DAFTAR HADIR PESERTA RAPAT -->
    <div class="section-header">IV. DAFTAR HADIR PESERTA RAPAT</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 38%;">Nama Peserta / NIP</th>
                <th style="width: 25%; text-align: center;">Jabatan / Unit Kerja</th>
                <th style="width: 12%; text-align: center;">Status</th>
                <th style="width: 20%; text-align: center;">Tanda Tangan / Ket.</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($attendees as $att)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>
                        <strong>{{ $att->nama }}</strong>
                        @if($att->nip && $att->nip !== '-')
                            <br><span style="font-size: 9.5pt;">NIP. {{ $att->nip }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $att->jabatan }} - {{ $att->bidang }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $att->status }}</td>
                    <td style="text-align: center;">
                        @if($att->status === 'Hadir' && $att->tanda_tangan)
                            @php
                                $sigPath = public_path('storage/' . $att->tanda_tangan);
                            @endphp
                            @if(file_exists($sigPath))
                                <img src="{{ $sigPath }}" alt="TTD" style="max-height: 25px; max-width: 90px; display: block; margin: 0 auto;">
                            @else
                                <span style="font-size: 9pt; font-style: italic;">[TTD Terverifikasi]</span>
                            @endif
                        @elseif($att->status === 'Izin' && $att->keterangan)
                            <span style="font-size: 9pt; font-style: italic;">{{ $att->keterangan }}</span>
                        @else
                            <span>-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- V. REKAPITULASI PRESENSI INTERNAL PER BIDANG -->
    @if(!empty($recap))
        <div class="section-header">V. REKAPITULASI PRESENSI INTERNAL PER BIDANG</div>
        <table class="table-data" style="width: 75%;">
            <thead>
                <tr>
                    <th>Nama Bidang / Unit Kerja</th>
                    <th style="width: 15%; text-align: center;">Hadir</th>
                    <th style="width: 15%; text-align: center;">Izin</th>
                    <th style="width: 15%; text-align: center;">Sakit</th>
                    <th style="width: 15%; text-align: center;">Alfa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recap as $rc)
                    <tr>
                        <td>{{ $rc->bidang_nama }}</td>
                        <td style="text-align: center;">{{ $rc->hadir }}</td>
                        <td style="text-align: center;">{{ $rc->izin }}</td>
                        <td style="text-align: center;">{{ $rc->sakit }}</td>
                        <td style="text-align: center;">{{ $rc->alfa ?? $rc->belum }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- SIGNATURE PENGESAHAN SECTION -->
    <div class="sig-container">
        <table class="sig-table">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <p style="margin-bottom: 4px;">Purwokerto, {{ $notulensi->updated_at ? $notulensi->updated_at->locale('id')->translatedFormat('d F Y') : now()->locale('id')->translatedFormat('d F Y') }}</p>
                    <p style="font-weight: bold; margin-bottom: 4px;">Mengesahkan,</p>
                    <p style="font-weight: bold; margin-bottom: 2px;">{{ $approverInfo->jabatan }}</p>
                    @if(!empty($approverInfo->sub_jabatan))
                        <p style="font-weight: bold; margin-bottom: 6px;">{{ $approverInfo->sub_jabatan }}</p>
                    @endif
                    <div style="height: 65px; margin: 8px 0;">
                        @if($notulensi->tanda_tangan_approver)
                            <img src="{{ $notulensi->tanda_tangan_approver }}" style="max-height: 65px; max-width: 190px;" alt="TTD Approver" />
                        @else
                            <div style="font-size: 9pt; font-style: italic; border: 1px dashed #000000; padding: 6px 12px; display: inline-block;">
                                [Dokumen Disahkan Digital]
                            </div>
                        @endif
                    </div>
                    <p style="font-weight: bold; text-decoration: underline; margin-bottom: 2px;">{{ $approverInfo->name }}</p>
                    <p style="margin: 0;">NIP. {{ $approverInfo->nip }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
