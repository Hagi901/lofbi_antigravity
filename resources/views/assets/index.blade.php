@extends('layouts.app')

@section('page_title', 'Manajemen Aset Tetap')

@section('content')
<!-- Memanggil CSS DataTables untuk Styling Tabel Interaktif -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- Metric Summary Cards -->
@php
    $totalAsetCount = count($assets ?? []);
    $totalPerolehan = 0;
    $totalNilaiBuku = 0;
    foreach ($assets ?? [] as $a) {
        $totalPerolehan += (float) ($a->nilai_perolehan ?? 0);
        $totalNilaiBuku += (float) ($a->hitungPenyusutanGarisLurus()['nilai_buku'] ?? 0);
    }
@endphp
<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 px-3">
                    <i class="fa-solid fa-boxes-stacked fs-5"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Total Item Aset</span>
                    <h6 class="fw-bold mb-0 text-dark">{{ $totalAsetCount }} <small class="text-muted fw-normal">Unit Terdaftar</small></h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 px-3">
                    <i class="fa-solid fa-money-bill-wave fs-5"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Total Nilai Perolehan</span>
                    <h6 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalPerolehan, 0, ',', '.') }}</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-primary rounded-3 p-2 px-3">
                    <i class="fa-solid fa-calculator fs-5"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Total Nilai Buku (SIMAN)</span>
                    <h6 class="fw-bold mb-0 text-primary">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-layer-group text-primary me-2"></i>Daftar Aset Aktif BMN
            </h6>
            <small class="text-muted" style="font-size: 11px;">Penyusutan otomatis metode Garis Lurus SIMAN semesteran (Floor Rp 1)</small>
        </div>
        
        @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
        <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm rounded-pill">
            <i class="fa-solid fa-plus me-1"></i> Tambah Aset Baru
        </a>
        @endif
    </div>
    
    <div class="card-body p-4 pt-1">
        
        <!-- Pesan Sukses jika berhasil tambah/edit/hapus data -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-3 py-2 mb-3">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table id="tabelAset" class="table table-hover table-sm align-middle border-bottom w-100" style="font-size: 12.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold border-0 py-2" width="16%">KODE & NUP</th>
                        <th class="text-secondary small fw-bold border-0 py-2" width="30%">NAMA BARANG & LOKASI</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-center" width="10%">KONDISI</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-end" width="14%">NILAI PEROLEHAN</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-end" width="13%">PENYUSUTAN</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-end" width="13%">NILAI BUKU</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-center" width="4%">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets ?? [] as $index => $asset)
                        @php
                            $nilaiPerolehan = (float) ($asset->nilai_perolehan ?? 0);
                            $susut          = $asset->hitungPenyusutanGarisLurus();
                            $akumulasi      = $susut['akumulasi'];
                            $nilaiBuku      = $susut['nilai_buku'];

                            $kondisiClass = 'bg-success-subtle text-success border-success';
                            if(($asset->condition ?? $asset->kondisi ?? '') === 'Rusak Ringan') $kondisiClass = 'bg-warning-subtle text-warning border-warning';
                            if(($asset->condition ?? $asset->kondisi ?? '') === 'Rusak Berat')  $kondisiClass = 'bg-danger-subtle text-danger border-danger';
                            $kondisiLabel = $asset->condition ?? $asset->kondisi ?? 'Baik';
                        @endphp
                        <tr>
                            {{-- KODE + BMN & NUP --}}
                            <td class="py-2">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-white text-dark border shadow-sm px-2 py-1" style="font-size: 11px;">
                                        {{ $asset->asset_code ?? $asset->kode_aset ?? 'AST-000' }}
                                    </span>
                                    <span class="badge bg-light text-primary border" style="font-size: 9.5px;" title="Nomor Urut Pendaftaran">
                                        NUP {{ $asset->nup ?? 1 }}
                                    </span>
                                </div>
                                @if($asset->kode_bmn)
                                    <span class="text-muted font-monospace d-block" style="font-size: 10px;" title="Kode BMN SIMAN">
                                        {{ $asset->kode_bmn }}
                                    </span>
                                @endif
                            </td>

                            {{-- NAMA BARANG + Ruangan + PJ + S/N --}}
                            <td class="py-2">
                                <a href="{{ route('assets.show', $asset->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $asset->name ?? $asset->jenisBarang?->nama_generik ?? 'Aset #' . $asset->id }}
                                </a>
                                <div class="text-muted" style="font-size: 11px;">
                                    <span><i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $asset->room?->name ?? $asset->ruangan?->nama ?? 'Belum Dialokasikan' }}</span>
                                    @if($asset->penanggung_jawab)
                                        <span class="text-secondary ms-1">&bull; <i class="fa-solid fa-user text-primary me-1"></i>{{ $asset->penanggung_jawab }}</span>
                                    @endif
                                    @if($asset->no_seri)
                                        <span class="font-monospace text-muted ms-1">&bull; S/N: {{ $asset->no_seri }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- KONDISI --}}
                            <td class="text-center py-2">
                                <span class="badge {{ $kondisiClass }} border px-2 py-1" style="font-size: 10.5px;">{{ $kondisiLabel }}</span>
                            </td>

                            {{-- NILAI PEROLEHAN --}}
                            <td class="text-end fw-bold text-dark py-2">
                                Rp {{ number_format($nilaiPerolehan, 0, ',', '.') }}
                            </td>

                            {{-- NILAI PENYUSUTAN (Terpakai) --}}
                            <td class="text-end text-danger fw-semibold py-2" style="font-size: 11.5px;">
                                - Rp {{ number_format($akumulasi, 0, ',', '.') }}
                            </td>

                            {{-- NILAI BUKU (Sisa) --}}
                            <td class="text-end fw-bold text-primary py-2" style="font-size: 13px;">
                                Rp {{ number_format($nilaiBuku, 0, ',', '.') }}
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center py-2">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-light btn-sm text-secondary shadow-sm py-1 px-2" title="Lihat Detail">
                                        <i class="fa-solid fa-eye" style="font-size: 11px;"></i>
                                    </a>
                                    @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-light btn-sm text-primary shadow-sm py-1 px-2" title="Edit Aset">
                                            <i class="fa-solid fa-pen-to-square" style="font-size: 11px;"></i>
                                        </a>
                                        @if((Auth::user()->role ?? '') === 'admin')
                                            <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini secara permanen?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm shadow-sm py-1 px-2" title="Hapus Aset">
                                                    <i class="fa-solid fa-trash" style="font-size: 11px;"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- DataTables handles empty state --}}
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
            dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-2"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center mt-2"ip>',
            ordering: true, 
            pageLength: 10,
        });
    });
</script>

<style>
    #tabelAset tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .dataTables_filter input {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.25rem 0.65rem;
        font-size: 12px;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.04);
    }
    .dataTables_filter input:focus {
        outline: none;
        border-color: #0d6efd;
    }
    .dataTables_length select {
        border: 1px solid #dee2e6;
        border-radius: 0.4rem;
        padding: 0.2rem 0.5rem;
        font-size: 12px;
    }
</style>
@endsection