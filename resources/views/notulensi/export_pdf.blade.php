<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notulensi Rapat - {{ $agenda->id }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        /* Header Kop Surat */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000000;
            margin-bottom: 20px;
        }
        .kop-logo {
            width: 80px;
            padding-bottom: 10px;
            vertical-align: middle;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
            padding-bottom: 10px;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-text h1 {
            margin: 2px 0 0 0;
            font-size: 16pt;
            font-weight: bold;
        }
        .kop-text p {
            margin: 5px 0 0 0;
            font-size: 9pt;
            color: #555555;
            font-style: italic;
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            text-decoration: underline;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        /* Metadata Table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .meta-label {
            width: 180px;
            font-weight: bold;
        }
        .meta-colon {
            width: 10px;
        }

        /* Heading Sections */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #cccccc;
            padding-bottom: 3px;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        .section-content {
            margin-left: 10px;
            text-align: justify;
        }
        .section-content h3, .section-content h4, .section-content h5 {
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .section-content h3 { font-size: 12pt; }
        .section-content h4 { font-size: 11pt; }
        .section-content h5 { font-size: 10pt; }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #444444;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }

        /* Sign-off signature */
        .sig-container {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-table td {
            width: 50%;
            vertical-align: top;
        }
        .sig-box {
            padding-left: 50px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo" style="height: 90px; width: auto;">
                @endif
            </td>
            <td class="kop-text">
                <h2>PEMERINTAH KABUPATEN BANYUMAS</h2>
                <h1>DINAS KOMUNIKASI DAN INFORMATIKA</h1>
                <p>Jl. Kabupaten No. 3, Purwokerto, Jawa Tengah. Telp: (0281) 632822</p>
            </td>
        </tr>
    </table>

    <div class="doc-title">NOTULENSI RAPAT</div>

    <!-- METADATA RAPAT -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Agenda Kegiatan</td>
            <td class="meta-colon">:</td>
            <td><strong>{{ $agenda->judul }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Hari, Tanggal</td>
            <td class="meta-colon">:</td>
            <td>{{ $agenda->tanggal->locale('id')->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Waktu</td>
            <td class="meta-colon">:</td>
            <td>{{ substr($agenda->jam_mulai, 0, 5) }} s.d. {{ substr($agenda->jam_selesai, 0, 5) }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Tempat</td>
            <td class="meta-colon">:</td>
            <td>{{ $agenda->lokasi }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nomor Surat</td>
            <td class="meta-colon">:</td>
            <td>{{ $agenda->nomor_surat_dasar }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pimpinan Rapat</td>
            <td class="meta-colon">:</td>
            <td>{{ $notulensi->approver->name ?? '-' }} ({{ $notulensi->approver->jabatan ?? '-' }})</td>
        </tr>
    </table>

    <!-- NOTULENSI RAPAT -->
    <div class="section-title">I. Notulensi Rapat</div>
    <div class="section-content">{!! $notulensi->ringkasan_html !!}</div>

    <div class="page-break"></div>

    <!-- DAFTAR HADIR PESERTA -->
    <div class="section-title">V. Daftar Hadir Peserta Rapat</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th>Nama Peserta / NIP</th>
                <th>Jabatan</th>
                <th>Bidang / Instansi</th>
                <th style="width: 70px; text-align: center;">Status</th>
                <th style="width: 140px; text-align: center;">Tanda Tangan / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($attendees as $att)
                <tr>
                    <td style="text-align: center; vertical-align: middle;">{{ $no++ }}</td>
                    <td style="vertical-align: middle;">
                        <strong>{{ $att->nama }}</strong>
                        @if($att->nip && $att->nip !== '-')
                            <br><span style="font-size: 8pt; color: #555555; font-family: monospace;">NIP. {{ $att->nip }}</span>
                        @endif
                    </td>
                    <td style="vertical-align: middle;">{{ $att->jabatan }}</td>
                    <td style="vertical-align: middle;">{{ $att->bidang }}</td>
                    <td style="text-align: center; font-weight: bold; vertical-align: middle;">{{ $att->status }}</td>
                    <td style="text-align: center; vertical-align: middle;">
                        @if($att->status === 'Hadir' && $att->tanda_tangan)
                            @php
                                $sigPath = public_path('storage/' . $att->tanda_tangan);
                            @endphp
                            @if(file_exists($sigPath))
                                <img src="{{ $sigPath }}" alt="TTD" style="max-height: 30px; max-width: 100px; display: block; margin: 0 auto;">
                            @else
                                <span style="color: #999999; font-size: 8pt;">[File TTD Hilang]</span>
                            @endif
                        @elseif($att->status === 'Izin' && $att->keterangan)
                            <span style="font-size: 8pt; font-style: italic; color: #555555; line-height: 1.2; display: block;">{{ $att->keterangan }}</span>
                        @else
                            <span style="color: #999999;">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- REKAPITULASI PRESENSI INTERNAL PER BIDANG -->
    <div class="section-title" style="margin-top: 30px;">VI. Rekapitulasi Presensi Internal Per Bidang</div>
    <table class="data-table" style="width: 70%;">
        <thead>
            <tr>
                <th>Nama Bidang</th>
                <th style="width: 70px; text-align: center;">Hadir</th>
                <th style="width: 70px; text-align: center;">Izin</th>
                <th style="width: 70px; text-align: center;">Sakit</th>
                <th style="width: 70px; text-align: center;">Belum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recap as $rc)
                <tr>
                    <td>{{ $rc->bidang_nama }}</td>
                    <td style="text-align: center;">{{ $rc->hadir }}</td>
                    <td style="text-align: center;">{{ $rc->izin }}</td>
                    <td style="text-align: center;">{{ $rc->sakit }}</td>
                    <td style="text-align: center;">{{ $rc->belum }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SIGN SIGNATURE SECTION -->
    <div class="sig-container">
        <table class="sig-table">
            <tr>
                <td></td>
                <td>
                    <div class="sig-box">
                        <p>Purwokerto, {{ $notulensi->updated_at->locale('id')->translatedFormat('d F Y') }}</p>
                        <p>Mengesahkan,</p>
                        <p style="font-weight: bold; margin-bottom: 60px;">{{ $notulensi->approver->jabatan ?? 'Pimpinan Rapat' }}</p>
                        <p style="font-weight: bold; text-decoration: underline; margin-bottom: 2px;">{{ $notulensi->approver->name ?? '-' }}</p>
                        <p>NIP. {{ $notulensi->approver->nip ?? '-' }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
