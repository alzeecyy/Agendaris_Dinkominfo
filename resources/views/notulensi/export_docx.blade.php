<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset="utf-8">
    <title>Notulensi Rapat - {{ $agenda->judul }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000000;
        }
        .header-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .subheader-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .section-header {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 20px;
            margin-bottom: 5px;
            border-bottom: 1px solid #000000;
            padding-bottom: 2px;
        }
        .section-body {
            margin-left: 10px;
            margin-bottom: 15px;
            text-align: justify;
        }
        table.meta-table {
            width: 100%;
            border: none;
            margin-bottom: 20px;
        }
        table.meta-table td {
            border: none;
            padding: 3px 5px;
            vertical-align: top;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
            font-size: 10pt;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .signature-box {
            margin-top: 30px;
            float: right;
            width: 300px;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="header-title">PEMERINTAH KABUPATEN BANYUMAS</div>
    <div class="header-title">DINAS KOMUNIKASI DAN INFORMATIKA</div>
    <div class="subheader-title">NOTULENSI RAPAT</div>

    <table class="meta-table">
        <tr>
            <td style="width: 150px; font-weight: bold;">Agenda Kegiatan</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $agenda->judul }}</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Hari, Tanggal</td>
            <td>:</td>
            <td>{{ $agenda->tanggal->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Waktu</td>
            <td>:</td>
            <td>{{ substr($agenda->jam_mulai, 0, 5) }} s.d. {{ substr($agenda->jam_selesai, 0, 5) }} WIB</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tempat</td>
            <td>:</td>
            <td>{{ $agenda->lokasi }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Dasar Pelaksanaan</td>
            <td>:</td>
            <td>{{ $agenda->nomor_surat_dasar }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Pimpinan Rapat</td>
            <td>:</td>
            <td>{{ $notulensi->approver->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">I. Ringkasan Rapat</div>
    <div class="section-body">{!! nl2br(e($notulensi->ringkasan)) !!}</div>

    <div class="section-header">II. Poin Pembahasan</div>
    <div class="section-body">{!! nl2br(e($notulensi->pembahasan)) !!}</div>

    <div class="section-header">III. Daftar Keputusan Rapat</div>
    <div class="section-body" style="font-weight: bold;">{!! nl2br(e($notulensi->keputusan)) !!}</div>

    <div class="section-header">IV. Kesimpulan</div>
    <div class="section-body">{!! nl2br(e($notulensi->kesimpulan)) !!}</div>

    <br>

    <div class="section-header">V. Daftar Hadir Peserta Rapat</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th>Nama Peserta</th>
                <th>NIP</th>
                <th>Jabatan</th>
                <th>Bidang / Instansi</th>
                <th style="width: 80px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($attendees as $att)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>{{ $att->nama }}</td>
                    <td>{{ $att->nip }}</td>
                    <td>{{ $att->jabatan }}</td>
                    <td>{{ $att->bidang }}</td>
                    <td style="text-align: center;">{{ $att->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-box">
        <p>Purwokerto, {{ $notulensi->updated_at->translatedFormat('d F Y') }}</p>
        <p>Mengesahkan,</p>
        <br><br><br>
        <p style="font-weight: bold; text-decoration: underline; margin-bottom: 2px;">{{ $notulensi->approver->name ?? '-' }}</p>
        <p>NIP. {{ $notulensi->approver->nip ?? '-' }}</p>
    </div>

</body>
</html>
