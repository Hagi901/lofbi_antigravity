@extends('layouts.app')

@section('page_title', 'Kartu Buku Persediaan Barang')

@section('content')
<div class="row">
    <!-- Header Informasi Barang -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-arrow-left"></i>
                            </a>
                            <h5 class="fw-bold text-dark mb-0">{{ $persediaan->name }}</h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1 rounded-pill">
                                {{ $persediaan->jenisBarang?->kategori?->nama ?? 'Persediaan' }}
                            </span>
                        </div>
                        <p class="text-muted small mb-0 ms-4 ps-2">
                            <i class="fa-solid fa-barcode me-1"></i> ID: <strong>INV-{{ str_pad($persediaan->id, 3, '0', STR_PAD_LEFT) }}</strong>
                            &bull; <i class="fa-solid fa-scale-balanced me-1"></i> Satuan: <strong>{{ $persediaan->satuan }}</strong>
                            &bull; <i class="fa-solid fa-location-dot text-danger me-1"></i> Lokasi: {{ $persediaan->ruangan?->nama ?? 'Gudang Utama' }}
                            &bull; <i class="fa-solid fa-shield-halved text-warning me-1"></i> Stok Min: {{ $persediaan->stok_minimum }} {{ $persediaan->satuan }}
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                            <a href="{{ route('inventory.in.create') }}" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Batch Masuk
                            </a>
                            <a href="{{ route('inventory.out.create') }}" class="btn btn-danger btn-sm fw-bold px-3 shadow-sm">
                                <i class="fa-solid fa-minus me-1"></i> Ajukan Keluar
                            </a>
                            <a href="{{ route('inventory.edit', $persediaan->id) }}" class="btn btn-light btn-sm text-primary shadow-sm" title="Edit Master">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Kartu Ringkasan Saldo -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-success border-4">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-cubes-stacked text-success fs-4"></i>
                </div>
                <div>
                    <p class="text-muted small fw-bold mb-1">Total Sisa Stok (FIFO)</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($sisaStok) }} <span class="fs-6 text-muted fw-normal">{{ $persediaan->satuan }}</span></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-primary border-4">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-rupiah-sign text-primary fs-4"></i>
                </div>
                <div>
                    <p class="text-muted small fw-bold mb-1">Total Nilai Saldo Gudang</p>
                    <h3 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalNilaiRupiah, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-warning border-4">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-layer-group text-warning fs-4"></i>
                </div>
                <div>
                    <p class="text-muted small fw-bold mb-1">Jumlah Batch Terdaftar</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ $persediaan->batches->count() }} <span class="fs-6 text-muted fw-normal">Batch</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Batch & Mutasi (Nav Tabs) -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <ul class="nav nav-tabs border-bottom" id="inventoryDetailTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold text-dark pb-3" id="batch-tab" data-bs-toggle="tab" data-bs-target="#batch" type="button">
                            <i class="fa-solid fa-boxes-packing text-primary me-2"></i>Rincian Batch Masuk (FIFO)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold text-muted pb-3" id="mutasi-tab" data-bs-toggle="tab" data-bs-target="#mutasi" type="button">
                            <i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>Riwayat Seluruh Mutasi
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">
                    
                    <!-- TAB 1: RINCIAN BATCH FIFO -->
                    <div class="tab-pane fade show active" id="batch" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small fw-bold text-center" width="5%">BATCH</th>
                                        <th class="small fw-bold">NO DOKUMEN / FAKTUR</th>
                                        <th class="small fw-bold">SUPPLIER / SUMBER</th>
                                        <th class="small fw-bold text-center">TGL MASUK</th>
                                        <th class="small fw-bold text-center">QTY MASUK</th>
                                        <th class="small fw-bold text-end">HARGA SATUAN</th>
                                        <th class="small fw-bold text-center">SISA STOK</th>
                                        <th class="small fw-bold text-end">TOTAL NILAI SISA</th>
                                        <th class="small fw-bold text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($persediaan->batches as $b)
                                        <tr>
                                            <td class="text-center fw-bold">#{{ $b->no_batch }}</td>
                                            <td>
                                                <strong>{{ $b->no_faktur ?: '-' }}</strong>
                                                @if($b->nota_dinas)
                                                    <br><small class="text-muted">{{ $b->nota_dinas }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $b->supplier ?: 'Penyedia Barang' }}</td>
                                            <td class="text-center">{{ $b->tanggal_masuk ? \Carbon\Carbon::parse($b->tanggal_masuk)->format('d M Y') : '-' }}</td>
                                            <td class="text-center fw-bold text-dark">{{ $b->jumlah_masuk }} {{ $persediaan->satuan }}</td>
                                            <td class="text-end">Rp {{ number_format($b->harga_satuan, 0, ',', '.') }}</td>
                                            <td class="text-center fw-bold {{ $b->sisa_stok > 0 ? 'text-success' : 'text-muted' }}">
                                                {{ $b->sisa_stok }} {{ $persediaan->satuan }}
                                            </td>
                                            <td class="text-end fw-bold">Rp {{ number_format($b->sisa_stok * $b->harga_satuan, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @if($b->sisa_stok > 0)
                                                    <span class="badge bg-success-subtle text-success border border-success">Tersedia</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border">Habis Terpakai</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">Belum ada catatan batch masuk untuk barang ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: RIWAYAT MUTASI -->
                    <div class="tab-pane fade" id="mutasi" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small fw-bold text-center" width="5%">NO</th>
                                        <th class="small fw-bold">TANGGAL</th>
                                        <th class="small fw-bold text-center">JENIS</th>
                                        <th class="small fw-bold text-center">JUMLAH</th>
                                        <th class="small fw-bold">TUJUAN / PENERIMA</th>
                                        <th class="small fw-bold">PETUGAS</th>
                                        <th class="small fw-bold text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($persediaan->transaksis as $index => $t)
                                        <tr>
                                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                                            <td>{{ $t->tanggal ? \Carbon\Carbon::parse($t->tanggal)->format('d M Y') : '-' }}</td>
                                            <td class="text-center">
                                                @if($t->jenis == 'masuk')
                                                    <span class="badge bg-success"><i class="fa-solid fa-arrow-down me-1"></i>Masuk</span>
                                                @else
                                                    <span class="badge bg-danger"><i class="fa-solid fa-arrow-up me-1"></i>Keluar</span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold">{{ $t->jumlah }} {{ $persediaan->satuan }}</td>
                                            <td>{{ $t->unit_kerja_penerima ?: 'Gudang Utama' }}</td>
                                            <td>{{ $t->diajukanOleh?->name ?? 'Sistem' }}</td>
                                            <td class="text-center">
                                                @php
                                                    $stBadge = match($t->status) {
                                                        'disetujui' => 'bg-success-subtle text-success border-success',
                                                        'ditolak' => 'bg-danger-subtle text-danger border-danger',
                                                        default => 'bg-warning-subtle text-warning border-warning',
                                                    };
                                                @endphp
                                                <span class="badge {{ $stBadge }} border px-2 py-1 text-capitalize">
                                                    {{ $t->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat transaksi mutasi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
