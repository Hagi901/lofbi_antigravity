<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Opname Fisik BMN</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; color: #000; line-height: 1.5; margin: 30px; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h3 { margin: 0; font-size: 14px; text-transform: uppercase; font-weight: bold; }
        .kop h2 { margin: 2px 0; font-size: 16px; text-transform: uppercase; font-weight: bold; }
        .kop p { margin: 0; font-size: 11px; font-style: italic; }
        .title { text-align: center; margin-bottom: 20px; }
        .title h4 { margin: 0; font-size: 14px; text-decoration: underline; text-transform: uppercase; }
        .title p { margin: 3px 0 0 0; font-size: 11px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
        table.data th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .ttd-wrapper { width: 100%; margin-top: 40px; }
        .ttd-box { width: 45%; display: inline-block; vertical-align: top; text-align: center; font-size: 11px; }
        .ttd-space { height: 70px; }
        .footer-note { margin-top: 30px; font-size: 10px; color: #555; font-style: italic; }
    </style>
</head>
<body>
    <!-- KOP SURAT RESMI -->
    <div class="kop">
        <h3>KEMENTERIAN PERHUBUNGAN</h3>
        <h3>DIREKTORAT JENDERAL PERHUBUNGAN LAUT</h3>
        <h2>KANTOR KESYAHBANDARAN DAN OTORITAS PELABUHAN KELAS I BANTEN</h2>
        <p>Jl. Raya Pelabuhan No. 1, Banten &bull; Telp/Fax: (0254) 123456</p>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="title">
        <h4>BERITA ACARA HASIL OPNAME FISIK BARANG MILIK NEGARA (BMN)</h4>
        <p>Nomor: BA/OPN/{{ $sesi->tanggal ? $sesi->tanggal->format('Y/m') : date('Y/m') }}/{{ str_pad($sesi->id, 3, '0', STR_PAD_LEFT) }}</p>
    </div>

    <p>
        Pada hari ini, <strong>{{ $sesi->tanggal ? \Carbon\Carbon::parse($sesi->tanggal)->translatedFormat('l, d F Y') : \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</strong>, 
        telah dilaksanakan verifikasi dan perhitungan fisik (Stock Opname) Barang Milik Negara (BMN) pada:
    </p>

    <table style="width: 100%; margin-bottom: 15px; font-size: 11px;">
        <tr>
            <td style="width: 25%; font-weight: bold;">Ruangan / Lokasi</td>
            <td style="width: 3%;">:</td>
            <td><strong>{{ $sesi->ruangan?->nama ?? 'Semua Ruangan' }} ({{ $sesi->ruangan?->gedung ?? 'Gedung Utama' }})</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Petugas Pelaksana</td>
            <td>:</td>
            <td>{{ $sesi->admin?->name ?? 'Admin LOFBI' }} ({{ $sesi->admin?->email ?? '-' }})</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Status Pemeriksaan</td>
            <td>:</td>
            <td>{{ ucfirst($sesi->status ?? 'selesai') }} &mdash; Terverifikasi Lengkap</td>
        </tr>
    </table>

    <p>Adapun hasil rincian perhitungan fisik adalah sebagai berikut:</p>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 18%;">KODE ASET</th>
                <th style="width: 35%;">NAMA & SPESIFIKASI BARANG</th>
                <th style="width: 20%;">KONDISI FISIK</th>
                <th style="width: 22%;">CATATAN PEMERIKSAAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sesi->details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><strong>{{ $detail->aset?->kode_aset ?? '-' }}</strong></td>
                    <td>
                        <strong>{{ $detail->aset?->name ?? 'Barang #' . $detail->aset_id }}</strong><br>
                        <small style="color:#555;">Merk: {{ $detail->aset?->merk ?? '-' }} | Model: {{ $detail->aset?->model ?? '-' }}</small>
                    </td>
                    <td class="text-center">
                        {{ ucfirst(str_replace('_', ' ', $detail->kondisi_aktual ?? ($detail->aset?->kondisi ?? 'baik'))) }}
                    </td>
                    <td>{{ $detail->catatan ?: 'Fisik barang sesuai data sistem' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 15px;">Tidak ada rincian aset dalam sesi opname ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        Demikian Berita Acara Opname Fisik ini dibuat dengan sebenarnya dan ditandatangani untuk dipergunakan sebagaimana mestinya 
        sesuai ketentuan penatausahaan BMN yang berlaku.
    </p>

    <!-- TANDA TANGAN -->
    <div class="ttd-wrapper">
        <div class="ttd-box" style="float: left;">
            <p>Mengetahui,<br><strong>Penanggung Jawab Ruangan</strong></p>
            <div class="ttd-space"></div>
            <p><strong>( .................................................. )</strong><br>NIP. ..........................................</p>
        </div>
        <div class="ttd-box" style="float: right;">
            <p>Banten, {{ $sesi->tanggal ? \Carbon\Carbon::parse($sesi->tanggal)->translatedFormat('d F Y') : date('d F Y') }}<br><strong>Petugas Pengurus Barang (BMN)</strong></p>
            <div class="ttd-space"></div>
            <p><strong>( {{ $sesi->admin?->name ?? 'Admin LOFBI' }} )</strong><br>NIP. 19980514 202401 1 001</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer-note">
        Dokumen resmi digenerate otomatis oleh Sistem LOFBI KSOP Kelas I Banten pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }} WIB.
    </div>

    @if(!class_exists('\Barryvdh\DomPDF\Facade\Pdf'))
    <script>
        window.onload = function() { window.print(); }
    </script>
    @endif
</body>
</html>
