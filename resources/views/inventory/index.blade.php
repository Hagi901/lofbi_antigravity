@extends('layouts.app')

@section('page_title', 'Manajemen Persediaan (Gudang)')

@section('content')
<!-- Memanggil CSS DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card border-0 shadow-sm rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-boxes-packing text-primary me-2"></i>Stok Barang Persediaan
        </h6>
        
        <div class="d-flex gap-2">
            <!-- Tombol Barang Masuk -->
            <a href="{{ route('inventory.stockInPage' ?? 'inventory.createIn') }}" class="btn btn-success btn-sm fw-bold px-3 shadow-sm rounded-pill">
                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Barang Masuk
            </a>
            
            <!-- Tombol Barang Keluar -->
            <a href="{{ route('inventory.stockOutPage' ?? 'inventory.createOut') }}" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm rounded-pill text-dark">
                <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Barang Keluar
            </a>
        </div>
    </div>
    
    <div class="card-body p-4 pt-0">
        
        <!-- Pesan Alert (Sukses/Error) -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-3">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 small rounded-3">
                <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table id="tabelInventory" class="table table-hover align-middle border-bottom w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold border-0 text-center" width="5%">NO</th>
                        <th class="text-secondary small fw-bold border-0">KODE BARANG</th>
                        <th class="text-secondary small fw-bold border-0">NAMA BARANG</th>
                        <th class="text-secondary small fw-bold border-0">KATEGORI</th>
                        <th class="text-secondary small fw-bold border-0 text-center">TOTAL STOK</th>
                        <th class="text-secondary small fw-bold border-0 text-center">SATUAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items ?? [] as $index => $item)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $item->item_code ?? '-' }}</span></td>
                            <td class="fw-bold text-dark">{{ $item->name }}</td>
                            <td class="text-muted small">{{ $item->category->name ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    // Mengambil nilai hasil dari withSum() di Controller tadi
                                    $stok = $item->batches_sum_qty_remaining ?? 0;
                                    $badgeClass = $stok > 10 ? 'bg-success' : ($stok > 0 ? 'bg-warning text-dark' : 'bg-danger');
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-6 shadow-sm">
                                    {{ $stok }}
                                </span>
                            </td>
                            <td class="text-center text-muted small">{{ $item->unit ?? 'Pcs' }}</td>
                        </tr>
                    @empty
                        <!-- DataTables akan mengurus jika kosong -->
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Script DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tabelInventory').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            ordering: true, 
            pageLength: 10,
        });
    });
</script>
@endsection