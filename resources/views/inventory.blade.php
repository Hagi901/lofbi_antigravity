@extends('layouts.app')

@section('page_title', 'Persediaan Barang (FIFO)')

@section('content')
<!-- Ringkasan Cepat Persediaan -->
<div class="row mb-4">
    <!-- Kartu Statistik (Bisa kamu buat dinamis juga nanti di Controller) -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-success border-4">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-arrow-right-to-bracket text-success fs-4"></i>
                </div>
                <div>
                    <p class="text-muted small fw-bold mb-1">Total Jenis Barang</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ count($items) }} <span class="fs-6 text-muted fw-normal">Item</span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-primary border-4">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-box-open text-primary fs-4"></i>
                </div>
                <div>
                    <p class="text-muted small fw-bold mb-1">Total Seluruh Stok</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ $items->sum('batches_sum_qty_remaining') }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Kartu Persediaan Utama -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-center border-bottom-0">
        <h6 class="fw-bold mb-3 mb-md-0 text-dark">
            <i class="fa-solid fa-list-check text-primary me-2"></i>Kartu Persediaan (Metode FIFO)
        </h6>
        <div>
            @if(in_array(Auth::user()->role ?? '', ['validator', 'admin']) && ($pengajuanMenunggu ?? 0) > 0)
                <a href="{{ route('inventory.pengajuan') }}" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm me-2">
                    <i class="fa-solid fa-stamp me-1"></i> Validasi Pengajuan ({{ $pengajuanMenunggu }})
                </a>
            @endif
            @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                <a href="{{ route('inventory.in.create') }}" class="btn btn-success btn-sm fw-bold px-3 shadow-sm me-2">
                    <i class="fa-solid fa-plus me-1"></i> Input Masuk
                </a>
                <a href="{{ route('inventory.out.create') }}" class="btn btn-danger btn-sm fw-bold px-3 shadow-sm">
                    <i class="fa-solid fa-minus me-1"></i> Input Keluar
                </a>
            @endif
        </div>
    </div>
    <div class="card-body p-4 pt-0">
        
        <!-- Notifikasi Sukses dari Controller -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle border-bottom">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold border-0">ID BARANG</th>
                        <th class="text-secondary small fw-bold border-0">NAMA BARANG</th>
                        <th class="text-secondary small fw-bold border-0 text-center">SISA STOK (FIFO)</th>
                        <th class="text-secondary small fw-bold border-0 text-center">STATUS</th>
                        <th class="text-secondary small fw-bold border-0 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Looping Data Dinamis dari Database -->
                    @forelse ($items as $item)
                        @php
                            // Ambil total sisa stok hasil hitungan withSum Controller
                            $sisaStok = $item->batches_sum_qty_remaining ?? 0;
                            
                            // Logika Warna Status Otomatis
                            if ($sisaStok > 20) {
                                $badge = 'bg-success-subtle text-success border-success';
                                $icon = 'fa-check';
                                $status = 'Aman';
                            } elseif ($sisaStok > 0) {
                                $badge = 'bg-warning-subtle text-warning border-warning';
                                $icon = 'fa-triangle-exclamation';
                                $status = 'Menipis';
                            } else {
                                $badge = 'bg-danger-subtle text-danger border-danger';
                                $icon = 'fa-xmark';
                                $status = 'Habis';
                            }
                        @endphp
                        <tr>
                            <td>
                                <span class="badge bg-white text-dark border shadow-sm px-2 py-1">
                                    <!-- Sesuaikan 'item_code' dengan nama kolom di databasemu. Jika tidak ada, pakai id -->
                                    {{ $item->code ?? 'INV-' . str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td class="fw-bold text-dark">
                                <!-- Sesuaikan 'name' dengan nama kolom di databasemu -->
                                {{ $item->name ?? 'Data Barang ' . $item->id }}
                            </td>
                            <td class="text-center fw-bold {{ $sisaStok == 0 ? 'text-danger' : '' }}">
                                {{ $sisaStok }} <span class="fw-normal small text-muted">Unit</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badge }} border px-2 py-1">
                                    <i class="fa-solid {{ $icon }} me-1"></i>{{ $status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-light btn-sm text-primary shadow-sm" title="Riwayat FIFO">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <!-- Tampilan Jika Database Masih Kosong -->
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-1 mb-3 d-block opacity-25"></i>
                                Belum ada data barang persediaan. Silakan tambahkan barang baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection