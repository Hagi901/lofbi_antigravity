<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Opname Fisik Persediaan - {{ $sesi->periode ?? 'LOFBI' }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; line-height: 1.4; margin: 25px 30px; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; }
        .kop h3 { margin: 0; font-size: 13px; text-transform: uppercase; font-weight: bold; }
        .kop h2 { margin: 2px 0; font-size: 15px; text-transform: uppercase; font-weight: bold; }
        .kop p { margin: 0; font-size: 10px; font-style: italic; }
        .title { text-align: center; margin-bottom: 15px; }
        .title h4 { margin: 0; font-size: 13px; text-decoration: underline; text-transform: uppercase; }
        .title p { margin: 3px 0 0 0; font-size: 10px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px 6px; font-size: 10px; }
        table.data th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .font-bold { font-weight: bold; }
        .ttd-wrapper { width: 100%; margin-top: 30px; }
        .ttd-box { width: 45%; display: inline-block; vertical-align: top; text-align: center; font-size: 11px; }
        .ttd-space { height: 60px; }
        .footer-note { margin-top: 20px; font-size: 9px; color: #555; font-style: italic; }
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
        <h4>BERITA ACARA HASIL OPNAME FISIK BARANG PERSEDIAAN</h4>
        <p>Nomor: BA/OPN-PSD/{{ $sesi->tanggal ? $sesi->tanggal->format('Y/m') : date('Y/m') }}/{{ str_pad($sesi->id, 3, '0', STR_PAD_LEFT) }}</p>
    </div>

    <p>
        Pada hari ini, <strong>{{ $sesi->tanggal ? \Carbon\Carbon::parse($sesi->tanggal)->translatedFormat('l, d F Y') : \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</strong>, 
        telah dilaksanakan verifikasi dan perhitungan fisik (Stock Opname) terhadap seluruh Barang Persediaan pada Satuan Kerja Kantor Kesyahbandaran dan Otoritas Pelabuhan Kelas I Banten (UAKPB: 022.04.2900.413670) untuk:
    </p>

    <table style="width: 100%; margin-bottom: 10px; font-size: 11px;">
        <tr>
            <td style="width: 25%; font-weight: bold;">Periode Opname</td>
            <td style="width: 3%;">:</td>
            <td><strong>{{ $sesi->periode ?? 'Semester Berjalan' }}</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tanggal Pelaksanaan</td>
            <td>:</td>
            <td>{{ $sesi->tanggal ? $sesi->tanggal->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Petugas Pemeriksa</td>
            <td>:</td>
            <td>{{ $sesi->admin?->name ?? 'Petugas Persediaan' }} ({{ $sesi->admin?->email ?? '-' }})</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Pejabat Yang Menyetujui</td>
            <td>:</td>
            <td>{{ $sesi->approver?->name ?? 'Kasubbag Tata Usaha / KPA' }} ({{ $sesi->tanggal_persetujuan ? $sesi->tanggal_persetujuan->translatedFormat('d F Y') : 'Menunggu Approval' }})</td>
        </tr>
        @if($sesi->keterangan)
        <tr>
            <td style="font-weight: bold;">Dasar / Keterangan</td>
            <td>:</td>
            <td>{{ $sesi->keterangan }}</td>
        </tr>
        @endif
    </table>

    <p style="margin-bottom: 5px;">Rincian hasil perhitungan fisik persediaan (Stok Sistem vs Kondisi Fisik Aktual):</p>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 34%;">NAMA BARANG & SPESIFIKASI</th>
                <th style="width: 10%;">SATUAN</th>
                <th style="width: 13%;">STOK BUKU (SISTEM)</th>
                <th style="width: 13%;">STOK FISIK (AKTUAL)</th>
                <th style="width: 11%;">SELISIH</th>
                <th style="width: 15%;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sesi->details as $index => $detail)
                @php
                    $namaBarang = $detail->persediaan?->name ?? $detail->persediaan?->jenisBarang?->nama_generik ?? 'Barang #' . $detail->persediaan_id;
                    $stokBuku = (int) $detail->stok_buku;
                    $stokFisik = $detail->stok_fisik;
                    $selisih = $detail->selisih;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $namaBarang }}</strong>
                        @if($detail->persediaan?->merk)
                            <br><span style="font-size: 9px; color: #555;">Merk/Tipe: {{ $detail->persediaan->merk }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $detail->satuan ?? $detail->persediaan?->satuan ?? '-' }}</td>
                    <td class="text-end font-bold">{{ number_format($stokBuku, 0, ',', '.') }}</td>
                    <td class="text-end font-bold">{{ $stokFisik !== null ? number_format($stokFisik, 0, ',', '.') : '-' }}</td>
                    <td class="text-center font-bold">
                        @if($selisih !== null)
                            {{ $selisih > 0 ? '+'.$selisih : $selisih }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $detail->catatan ?: ($selisih === 0 ? 'Cocok' : '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 12px;">Tidak ada data rincian persediaan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #fafafa; font-weight: bold;">
                <td colspan="3" class="text-center">TOTAL REKAPITULASI</td>
                <td class="text-end">{{ number_format($sesi->details->sum('stok_buku'), 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($sesi->details->whereNotNull('stok_fisik')->sum('stok_fisik'), 0, ',', '.') }}</td>
                <td class="text-center">{{ $sesi->jumlahSelisih() }} Item Selisih</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <p>
        Demikian Berita Acara Opname Fisik Persediaan ini dibuat dengan sebenarnya sesuai dengan standar penatausahaan persediaan aplikasi SAKTI Kementerian Keuangan RI untuk dipergunakan sebagaimana mestinya.
    </p>

    <!-- TANDA TANGAN -->
    <div class="ttd-wrapper">
        <div class="ttd-box" style="float: left;">
            <p>Menyetujui,<br><strong>Kasubbag TU / Approver (KPA)</strong></p>
            <div class="ttd-space"></div>
            <p><strong>( {{ $sesi->approver?->name ?? '..................................................' }} )</strong><br>NIP. {{ $sesi->approver ? '19850312 201012 1 002' : '..........................................' }}</p>
        </div>
        <div class="ttd-box" style="float: right;">
            <p>Banten, {{ $sesi->tanggal ? \Carbon\Carbon::parse($sesi->tanggal)->translatedFormat('d F Y') : date('d F Y') }}<br><strong>Petugas Pengurus Persediaan</strong></p>
            <div class="ttd-space"></div>
            <p><strong>( {{ $sesi->admin?->name ?? 'Admin LOFBI' }} )</strong><br>NIP. 19980514 202401 1 001</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer-note">
        Dokumen resmi digenerate otomatis oleh Sistem LOFBI (Logistik & Financial BMN Interface) KSOP Kelas I Banten pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }} WIB.
    </div>

    @if(!class_exists('\Barryvdh\DomPDF\Facade\Pdf'))
    <script>
        window.onload = function() { window.print(); }
    </script>
    @endif
</body>
</html>
