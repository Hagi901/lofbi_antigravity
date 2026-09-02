<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aset Tetap</title>
    <style>
        body { font-family: 'Poppins', 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #1a3c6e; padding-bottom: 12px; }
        .header h2 { margin: 0; font-size: 16px; color: #1a3c6e; letter-spacing: 1px; }
        .header p { margin: 4px 0 0 0; font-size: 11px; color: #555; }
        .filter-badge {
            display: inline-block; background: #e8f0fe; color: #1a3c6e;
            border: 1px solid #c5d5f5; border-radius: 4px;
            padding: 3px 10px; font-size: 10px; font-weight: bold; margin-top: 6px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #1a3c6e; color: #fff; padding: 7px 6px; text-align: center; font-size: 10px; }
        td { border: 1px solid #d0d7e3; padding: 6px; text-align: left; font-size: 10px; }
        tr:nth-child(even) td { background-color: #f4f6fb; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-baik { color: #15803d; font-weight: bold; }
        .badge-rusak_ringan { color: #b45309; font-weight: bold; }
        .badge-rusak_berat { color: #b91c1c; font-weight: bold; }
        .footer { text-align: right; margin-top: 20px; font-size: 10px; color: #888; }
        .total-row td { background: #e8f0fe !important; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA ASET TETAP</h2>
        <p>Kantor Kesyahbandaran dan Otoritas Pelabuhan (KSOP) Kelas I Banten</p>
        <p>Sistem Informasi Manajemen Aset &amp; Persediaan &mdash; <strong>LOFBI</strong></p>
        <div>
            <span class="filter-badge">
                Kategori: {{ $filterInfo['label'] ?? 'Semua Kategori' }}
            </span>
            @if(!empty($filterInfo['bulan']))
                <span class="filter-badge">Periode: {{ \Carbon\Carbon::createFromFormat('m', $filterInfo['bulan'])->translatedFormat('F') }} {{ $filterInfo['tahun'] ?? date('Y') }}</span>
            @endif
        </div>
        <p style="margin-top:6px; color:#888;">Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB &nbsp;|&nbsp; Total: {{ $assets->count() }} Aset</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:11%;">Kode Aset</th>
                <th style="width:20%;">Nama/Jenis Barang</th>
                <th style="width:9%;">Kategori</th>
                <th style="width:13%;">Lokasi/Ruangan</th>
                <th style="width:8%;">Kondisi</th>
                <th style="width:11%;">Nilai Perolehan</th>
                <th style="width:11%;">Akum. Penyusutan</th>
                <th style="width:11%;">Nilai Buku</th>
                <th style="width:4%;">Manfaat</th>
            </tr>
        </thead>
        <tbody>
            @php $totalNilai = 0; $totalBuku = 0; @endphp
            @forelse($assets as $index => $asset)
            @php
                $totalNilai += $asset->nilai_perolehan ?? 0;
                $totalBuku  += $asset->nilai_buku ?? 0;
                $kondisi     = $asset->kondisi ?? 'baik';
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center"><strong>{{ $asset->kode_aset }}</strong></td>
                <td>{{ $asset->name }}<br><small style="color:#666;">{{ $asset->merk }} {{ $asset->model }}</small></td>
                <td class="text-center">{{ $asset->jenisBarang?->kategori?->nama ?? '-' }}</td>
                <td>{{ $asset->ruangan?->nama ?? '-' }}</td>
                <td class="text-center badge-{{ $kondisi }}">{{ ucfirst(str_replace('_', ' ', $kondisi)) }}</td>
                <td class="text-right">Rp {{ number_format($asset->nilai_perolehan ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($asset->akumulasi_penyusutan ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($asset->nilai_buku ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ $asset->masa_manfaat }} Thn</td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center" style="padding:20px; color:#999;">Tidak ada data aset untuk kategori ini.</td></tr>
            @endforelse
            @if($assets->count() > 0)
            <tr class="total-row">
                <td colspan="6" class="text-center">TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($totalBuku, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh Sistem LOFBI — KSOP Kelas I Banten &copy; {{ date('Y') }}
    </div>

    @if(!class_exists('\Barryvdh\DomPDF\Facade\Pdf'))
    <script>
        window.onload = function() { window.print(); }
    </script>
    @endif
</body>
</html>