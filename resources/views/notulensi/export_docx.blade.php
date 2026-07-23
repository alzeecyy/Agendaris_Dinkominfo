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
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000000;
            line-height: 1.5;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* Kop Surat */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000000;
            margin-bottom: 20px;
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
        }
        .kop-text h1 {
            margin: 2px 0 0 0;
            font-size: 16pt;
            font-weight: bold;
        }
        .kop-text p {
            margin: 2px 0 0 0;
            font-size: 9.5pt;
            font-style: italic;
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            text-decoration: underline;
            margin-top: 15px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        /* Metadata Table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11pt;
        }
        .meta-label {
            width: 180px;
            font-weight: bold;
        }

        /* Section Styling */
        .section-header {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000000;
            padding-bottom: 3px;
            margin-top: 20px;
            margin-bottom: 8px;
        }
        .section-body {
            margin-left: 10px;
            margin-bottom: 15px;
            text-align: justify;
            font-size: 11pt;
        }

        /* Attendance Table */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .table-data th, .table-data td {
            border: 1px solid #000000;
            padding: 6px 8px;
            font-size: 10pt;
            vertical-align: middle;
        }
        .table-data th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        /* Signature block */
        .ttd-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .ttd-table td {
            vertical-align: top;
            font-size: 11pt;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="kop-table">
        <tr>
            <td class="kop-text">
                <h2>PEMERINTAH KABUPATEN BANYUMAS</h2>
                <h1>DINAS KOMUNIKASI DAN INFORMATIKA</h1>
                <p>Jl. Kabupaten No. 1 Purwokerto Kode Pos 53115 Telp. (0281) 631776</p>
                <p>Website: kominfo.banyumaskab.go.id &bull; E-mail: kominfo@banyumaskab.go.id</p>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">
        NOTULENSI RAPAT RESMI
    </div>

    <!-- METADATA AGENDA -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Judul Rapat / Kegiatan</td>
            <td width="10">:</td>
            <td class="font-bold">{{ $agenda->judul }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nomor Surat Dasar</td>
            <td>:</td>
            <td>{{ $agenda->nomor_surat_dasar ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Hari / Tanggal</td>
            <td>:</td>
            <td>{{ $agenda->tanggal->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Waktu Pelaksanaan</td>
            <td>:</td>
            <td>Pukul {{ substr($agenda->jam_mulai, 0, 5) }} - {{ substr($agenda->jam_selesai, 0, 5) }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Tempat / Lokasi</td>
            <td>:</td>
            <td>{{ $agenda->lokasi }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pimpinan / Notulis</td>
            <td>:</td>
            <td>{{ $agenda->sekretaris->name ?? 'Sekretaris Dinas' }}</td>
        </tr>
    </table>

    <!-- RINGKASAN EKSEKUTIF -->
    @if($notulensi->ringkasan)
        <div class="section-header">I. RINGKASAN EKSEKUTIF RAPAT</div>
        <div class="section-body">
            {!! \Illuminate\Support\Str::markdown($notulensi->ringkasan) !!}
        </div>
    @endif

    <!-- PEMBAHASAN UTAMA -->
    @if($notulensi->pembahasan)
        <div class="section-header">II. {{ mb_strtoupper($notulensi->pembahasan_title ?? 'POIN-POIN PEMBAHASAN UTAMA') }}</div>
        <div class="section-body">
            {!! \Illuminate\Support\Str::markdown($notulensi->pembahasan) !!}
        </div>
    @endif

    <!-- KEPUTUSAN & KESIMPULAN -->
    @if($notulensi->keputusan || $notulensi->kesimpulan)
        <div class="section-header">III. {{ mb_strtoupper($notulensi->keputusan_title ?? 'HASIL KEPUTUSAN & TINDAK LANJUT') }}</div>
        <div class="section-body">
            @if($notulensi->keputusan)
                {!! \Illuminate\Support\Str::markdown($notulensi->keputusan) !!}
            @endif
            @if($notulensi->kesimpulan)
                <div style="margin-top: 10px;">
                    <strong>Kesimpulan Akhir:</strong><br>
                    {!! \Illuminate\Support\Str::markdown($notulensi->kesimpulan) !!}
                </div>
            @endif
        </div>
    @endif

    <!-- DAFTAR HADIR PESERTA RAPAT -->
    <div class="section-header">IV. DAFTAR HADIR PESERTA RAPAT</div>
    <table class="table-data">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Lengkap</th>
                <th>NIP</th>
                <th>Jabatan / Unit Kerja</th>
                <th width="80">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendees as $idx => $peserta)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $peserta->nama }}</td>
                    <td class="text-center">{{ $peserta->nip }}</td>
                    <td>{{ $peserta->jabatan }} - {{ $peserta->bidang }}</td>
                    <td class="text-center font-bold">{{ $peserta->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada daftar hadir peserta.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PENGESAHAN NOTULENSI -->
    <table class="ttd-table">
        <tr>
            <td width="50%"></td>
            <td width="50%" class="text-center">
                Purwokerto, {{ $notulensi->updated_at ? $notulensi->updated_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}<br>
                <strong>Mengesahkan,</strong><br>
                <strong>{{ $approverInfo->jabatan }}</strong><br>
                @if(!empty($approverInfo->sub_jabatan))
                    <strong>{{ $approverInfo->sub_jabatan }}</strong><br>
                @endif
                @if($notulensi->tanda_tangan_approver)
                    <br><img src="{{ $notulensi->tanda_tangan_approver }}" style="height: 60px; max-width: 180px;" /><br>
                @else
                    <br><div style="font-size: 9pt; font-style: italic; color: #333333; padding: 4px; border: 1px dashed #666666; display: inline-block;">[Dokumen Disahkan Digital]</div><br>
                @endif
                <strong><u>{{ $approverInfo->name }}</u></strong><br>
                NIP. {{ $approverInfo->nip }}
            </td>
        </tr>
    </table>

</body>
</html>
