<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Persediaan</title>
    <style>
        body { font-family: 'Poppins', 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #166534; padding-bottom: 12px; }
        .header h2 { margin: 0; font-size: 16px; color: #166534; letter-spacing: 1px; }
        .header p { margin: 4px 0 0 0; font-size: 11px; color: #555; }
        .filter-badge {
            display: inline-block; background: #dcfce7; color: #166534;
            border: 1px solid #a7f3c0; border-radius: 4px;
            padding: 3px 10px; font-size: 10px; font-weight: bold; margin-top: 6px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #166534; color: #fff; padding: 7px 6px; text-align: center; font-size: 10px; }
        td { border: 1px solid #d0e7d5; padding: 6px; text-align: left; font-size: 10px; }
        tr:nth-child(even) td { background-color: #f0fdf4; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .status-aman { color: #15803d; font-weight: bold; }
        .status-menipis { color: #b45309; font-weight: bold; }
        .status-habis { color: #b91c1c; font-weight: bold; }
        .footer { text-align: right; margin-top: 20px; font-size: 10px; color: #888; }
        .total-row td { background: #dcfce7 !important; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA PERSEDIAAN BARANG (FIFO)</h2>
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
        <p style="margin-top:6px; color:#888;">Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB &nbsp;|&nbsp; Total: {{ $items->count() }} Jenis Barang</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:10%;">Kode Barang</th>
                <th style="width:22%;">Nama Barang</th>
                <th style="width:8%;">Merk</th>
                <th style="width:10%;">Kategori</th>
                <th style="width:7%;">Satuan</th>
                <th style="width:9%;">Sisa Stok</th>
                <th style="width:9%;">Stok Min.</th>
                <th style="width:8%;">Status</th>
                <th style="width:6%;">Batch</th>
            </tr>
        </thead>
        <tbody>
            @php $totalStok = 0; @endphp
            @forelse($items as $index => $item)
            @php
                $sisaStok = (int) $item->batches->sum('sisa_stok');
                $minStok  = (int) ($item->stok_minimum ?? 0);
                $totalStok += $sisaStok;
                $statusClass = $sisaStok <= 0 ? 'status-habis' : ($sisaStok <= $minStok ? 'status-menipis' : 'status-aman');
                $statusText  = $sisaStok <= 0 ? 'Habis' : ($sisaStok <= $minStok ? 'Menipis' : 'Aman');
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center"><strong>INV-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</strong></td>
                <td>{{ $item->jenisBarang?->nama_generik ?? '-' }}</td>
                <td>{{ $item->merk ?? '-' }}</td>
                <td class="text-center">{{ $item->jenisBarang?->kategori?->nama ?? '-' }}</td>
                <td class="text-center">{{ $item->satuan ?? 'unit' }}</td>
                <td class="text-right"><strong>{{ $sisaStok }}</strong></td>
                <td class="text-right">{{ $minStok }}</td>
                <td class="text-center {{ $statusClass }}">{{ $statusText }}</td>
                <td class="text-center">{{ $item->batches->count() }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center" style="padding:20px; color:#999;">Tidak ada data persediaan untuk kategori ini.</td></tr>
            @endforelse
            @if($items->count() > 0)
            <tr class="total-row">
                <td colspan="6" class="text-center">TOTAL STOK</td>
                <td class="text-right">{{ $totalStok }}</td>
                <td colspan="3"></td>
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
