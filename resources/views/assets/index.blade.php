@extends('layouts.app')

@section('page_title', 'Manajemen Aset Tetap')

@section('content')
<!-- Memanggil CSS DataTables untuk Styling Tabel Interaktif -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card border-0 shadow-sm rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Daftar Aset Aktif</h6>
        
        @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
        <!-- Tombol Tambah Aset Aktif -->
        <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm rounded-pill">
            <i class="fa-solid fa-plus me-1"></i> Tambah Aset Baru
        </a>
        @endif
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
            <table id="tabelAset" class="table table-hover align-middle border-bottom w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold border-0">KODE</th>
                        <th class="text-secondary small fw-bold border-0">NAMA BARANG</th>
                        <th class="text-secondary small fw-bold border-0 text-center">KONDISI</th>
                        <th class="text-secondary small fw-bold border-0 text-end">NILAI PEROLEHAN (Harga Beli)</th>
                        <th class="text-secondary small fw-bold border-0 text-end">NILAI PENYUSUTAN (Terpakai)</th>
                        <th class="text-secondary small fw-bold border-0 text-end">NILAI BUKU (Sisa)</th>
                        <th class="text-secondary small fw-bold border-0 text-center">AKSI</th>
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
                            <td>
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <span class="badge bg-white text-dark border shadow-sm px-2 py-1" style="font-size: 11px; letter-spacing: 0.3px;">
                                        {{ $asset->asset_code ?? $asset->kode_aset ?? 'AST-000' }}
                                    </span>
                                    <span class="badge bg-light text-primary border" style="font-size: 10px;" title="Nomor Urut Pendaftaran">
                                        NUP {{ $asset->nup ?? 1 }}
                                    </span>
                                </div>
                                @if($asset->kode_bmn)
                                    <small class="text-muted font-monospace d-block" style="font-size: 10px;" title="Kodefikasi BMN SIMAN">
                                        <i class="fa-solid fa-barcode text-secondary me-1"></i>{{ $asset->kode_bmn }}
                                    </small>
                                @endif
                            </td>

                            {{-- NAMA BARANG + Ruangan + PJ + S/N --}}
                            <td>
                                <a href="{{ route('assets.show', $asset->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $asset->name ?? $asset->jenisBarang?->nama_generik ?? 'Aset #' . $asset->id }}
                                </a>
                                <br>
                                <small class="text-muted">
                                    <i class="fa-solid fa-location-dot text-danger me-1" style="font-size: 10px;"></i>
                                    {{ $asset->room?->name ?? $asset->ruangan?->nama ?? 'Belum Dialokasikan' }}
                                </small>
                                @if($asset->penanggung_jawab)
                                    <small class="text-secondary ms-1">
                                        &bull; <i class="fa-solid fa-user text-primary me-1" style="font-size: 10px;"></i>PJ: {{ $asset->penanggung_jawab }}
                                    </small>
                                @endif
                                @if($asset->no_seri)
                                    <br><small class="text-muted font-monospace" style="font-size: 10px;">
                                        S/N: {{ $asset->no_seri }}
                                    </small>
                                @endif
                            </td>

                            {{-- KONDISI --}}
                            <td class="text-center">
                                <span class="badge {{ $kondisiClass }} border px-2 py-1">{{ $kondisiLabel }}</span>
                            </td>

                            {{-- NILAI PEROLEHAN --}}
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($nilaiPerolehan, 0, ',', '.') }}
                            </td>

                            {{-- NILAI PENYUSUTAN (Terpakai) --}}
                            <td class="text-end text-danger fw-bold">
                                - Rp {{ number_format($akumulasi, 0, ',', '.') }}
                            </td>

                            {{-- NILAI BUKU (Sisa) --}}
                            <td class="text-end fw-bold text-primary" style="font-size: 1rem;">
                                Rp {{ number_format($nilaiBuku, 0, ',', '.') }}
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-light btn-sm text-secondary shadow-sm" title="Lihat Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-light btn-sm text-primary shadow-sm" title="Edit Aset">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @if((Auth::user()->role ?? '') === 'admin')
                                            <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini secara permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm shadow-sm" title="Hapus Aset">
                                                    <i class="fa-solid fa-trash"></i>
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