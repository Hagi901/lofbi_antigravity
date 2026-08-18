<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aset Tetap</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0 0 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA ASET TETAP</h2>
        <p>Sistem Manajemen Aset & Persediaan (LOFBI)</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Aset</th>
                <th>Nama Aset</th>
                <th>Kategori</th>
                <th>Lokasi / Ruangan</th>
                <th>Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $index => $asset)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $asset->asset_code }}</td>
                <td>{{ $asset->name }}</td>
                <td>{{ $asset->category->name ?? '-' }}</td>
                <td>{{ $asset->room->name ?? '-' }}</td>
                <td class="text-center">{{ $asset->condition }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>