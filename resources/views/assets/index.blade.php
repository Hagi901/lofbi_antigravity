@extends('layouts.app')

@section('page_title', 'Manajemen Aset Tetap')

@section('content')
<!-- Memanggil CSS DataTables untuk Styling Tabel Interaktif -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card border-0 shadow-sm rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Daftar Aset Aktif</h6>
        
        <!-- Tombol Tambah Aset Aktif -->
        <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm rounded-pill">
            <i class="fa-solid fa-plus me-1"></i> Tambah Aset Baru
        </a>
    </div>
    
    <div class="card-body p-4 pt-0">
        
        <!-- Pesan Sukses jika berhasil tambah/edit/hapus data -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <!-- Tambahkan ID 'tabelAset' agar terbaca oleh DataTables -->
            <table id="tabelAset" class="table table-hover align-middle border-bottom w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold border-0 text-center" width="5%">NO</th>
                        <th class="text-secondary small fw-bold border-0">KODE ASET</th>
                        <th class="text-secondary small fw-bold border-0">NAMA BARANG</th>
                        <th class="text-secondary small fw-bold border-0">KATEGORI</th>
                        <th class="text-secondary small fw-bold border-0">LOKASI RUANGAN</th>
                        <th class="text-secondary small fw-bold border-0 text-center">PENYUSUTAN</th>
                        <th class="text-secondary small fw-bold border-0 text-center">KONDISI</th>
                        <th class="text-secondary small fw-bold border-0 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Looping data dari Database -->
                    @forelse ($assets ?? [] as $index => $asset)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-white text-dark border shadow-sm px-2 py-1">
                                    {{ $asset->asset_code ?? 'AST-000' }}
                                </span>
                            </td>
                            <td class="fw-bold text-dark">{{ $asset->name }}</td>
                            <td>
                                <!-- Penyesuaian: Menampilkan Kategori & Sub Kategori -->
                                <span class="text-dark">{{ $asset->category->name ?? 'Tanpa Kategori' }}</span><br>
                                <span class="badge bg-light text-secondary border mt-1">{{ $asset->subCategory->name ?? '-' }}</span>
                            </td>
                            <td class="text-muted small">
                                <i class="fa-solid fa-location-dot text-danger me-1"></i> 
                                {{ $asset->room->name ?? 'Belum Dialokasikan' }}
                            </td>
                            @php
                                $nilaiPerolehan = (float) ($asset->nilai_perolehan ?? 0);
                                $susut          = $asset->hitungPenyusutanGarisLurus();
                                $akumulasi      = $susut['akumulasi'];
                                $nilaiBuku      = $susut['nilai_buku'];
                                $pctSusut       = $susut['persen_susut'];
                                $tahunBerjalan  = $susut['tahun_berjalan'];
                                $masaManfaat    = (int) ($asset->masa_manfaat ?? 0);
                                $barColor       = $pctSusut < 50 ? 'bg-success' : ($pctSusut < 80 ? 'bg-warning' : 'bg-danger');
                            @endphp
                            <td style="min-width: 170px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold text-dark">
                                        Rp {{ number_format($nilaiBuku, 0, ',', '.') }}
                                    </span>
                                    <span class="badge {{ $pctSusut >= 80 ? 'bg-danger-subtle text-danger' : ($pctSusut >= 50 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') }} border px-1" style="font-size: 10px;">
                                        {{ $pctSusut }}%
                                    </span>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;" title="Terdepresiasi {{ $pctSusut }}% — Tahun ke-{{ $tahunBerjalan }} dari {{ $masaManfaat }} tahun">
                                    <div class="progress-bar {{ $barColor }}" style="width: {{ $pctSusut }}%"></div>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 10px;">
                                    Akum: Rp {{ number_format($akumulasi, 0, ',', '.') }}
                                    &nbsp;·&nbsp; Thn ke-{{ $tahunBerjalan }}/{{ $masaManfaat }}
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $badgeClass = 'bg-success-subtle text-success border-success';
                                    if($asset->condition == 'Rusak Ringan') $badgeClass = 'bg-warning-subtle text-warning border-warning';
                                    if($asset->condition == 'Rusak Berat') $badgeClass = 'bg-danger-subtle text-danger border-danger';
                                @endphp
                                <span class="badge {{ $badgeClass }} border px-2 py-1">
                                    {{ $asset->condition }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-light btn-sm text-primary shadow-sm" title="Edit Aset">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light btn-sm text-danger shadow-sm" title="Hapus Aset">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <!-- Kosong, karena DataTables akan otomatis menampilkan 'No data available' -->
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Memanggil jQuery dan Javascript DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelAset').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
                search: "_INPUT_",
                searchPlaceholder: "Cari nama, kode, atau kategori..."
            },
            dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center mt-3"ip>',
            ordering: true, 
            pageLength: 10,
        });
    });
</script>

<style>
    .dataTables_filter input {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.375rem 0.75rem;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    }
    .dataTables_filter input:focus {
        outline: none;
        border-color: #0d6efd;
    }
</style>
@endsection