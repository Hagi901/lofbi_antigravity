@extends('layouts.app')

@section('page_title', 'Kartu Buku Persediaan Barang')

@section('content')
@php
    $userRole = Auth::user()->role ?? '';
@endphp

<!-- Header Informasi Barang -->
<div class="card-app mb-4">
    <div class="card-body-app">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm btn-action-icon" title="Kembali ke Daftar">
                    <i class="fa-solid fa-arrow-left text-secondary"></i>
                </a>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h1 class="fw-bold text-dark mb-0 fs-6">{{ $persediaan->name }}</h1>
                        <span class="badge-app badge-app-primary">
                            {{ $persediaan->jenisBarang?->kategori?->nama ?? 'Persediaan' }}
                        </span>
                    </div>
                    <div class="text-muted small">
                        <span><i class="fa-solid fa-barcode me-1"></i> ID: <strong>INV-{{ str_pad($persediaan->id, 3, '0', STR_PAD_LEFT) }}</strong></span>
                        <span class="mx-1">&bull;</span>
                        <span><i class="fa-solid fa-scale-balanced me-1"></i> Satuan: <strong>{{ $persediaan->satuan }}</strong></span>
                        <span class="mx-1">&bull;</span>
                        <span><i class="fa-solid fa-location-dot text-danger me-1"></i> Lokasi: {{ $persediaan->ruangan?->nama ?? 'Gudang Utama' }}</span>
                        <span class="mx-1">&bull;</span>
                        <span><i class="fa-solid fa-shield-halved text-warning me-1"></i> Stok Min: {{ $persediaan->stok_minimum }} {{ $persediaan->satuan }}</span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(in_array($userRole, ['admin', 'operator']))
                    <a href="{{ route('upload.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Berkas
                    </a>
                    <a href="{{ route('inventory.out.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-arrow-up-from-bracket"></i> Ajukan Keluar
                    </a>
                    <a href="{{ route('inventory.edit', $persediaan->id) }}" class="btn btn-light btn-sm btn-action-icon" title="Edit Master">
                        <i class="fa-solid fa-pen-to-square text-secondary"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 3 Kartu Ringkasan Saldo -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <x-stat-card
            label="Total Sisa Stok (FIFO)"
            :value="number_format($sisaStok)"
            :subtext="$persediaan->satuan"
            icon="fa-solid fa-cubes-stacked"
            color="success"
        />
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="stat-card-widget stat-primary h-100">
            <div class="pe-2">
                <div class="stat-label">Total Nilai Saldo Gudang</div>
                <div class="stat-value">Rp {{ number_format($totalNilaiRupiah, 0, ',', '.') }}</div>
                @if(!empty($hasHargaBelumDiisi))
                    <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill mt-1" style="font-size: 11px;">
                        (Nilai belum lengkap - {{ $jumlahTerdampak ?? 0 }} batch terdampak)
                    </span>
                @endif
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-rupiah-sign"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-12">
        <x-stat-card
            label="Jumlah Batch Terdaftar"
            :value="number_format($persediaan->batches->count())"
            subtext="Batch Masuk"
            icon="fa-solid fa-layer-group"
            color="warning"
        />
    </div>
</div>

<!-- Tabel Rincian Batch & Mutasi (Nav Tabs) -->
<div class="card-app">
    <div class="card-header-app pb-0 pt-3 border-bottom-0">
        <ul class="nav nav-tabs card-header-tabs" id="inventoryDetailTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold pb-2 px-3" id="batch-tab" data-bs-toggle="tab" data-bs-target="#batch" type="button" style="font-size: 13px;">
                    <i class="fa-solid fa-boxes-packing text-primary me-2"></i>Rincian Batch Masuk (FIFO)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-muted pb-2 px-3" id="mutasi-tab" data-bs-toggle="tab" data-bs-target="#mutasi" type="button" style="font-size: 13px;">
                    <i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>Riwayat Seluruh Mutasi
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body-app">
        <div class="tab-content">
            <!-- TAB 1: RINCIAN BATCH FIFO -->
            <div class="tab-pane fade show active" id="batch" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-bottom" style="font-size: 12.5px;">
                        <thead class="table-light">
                            <tr>
                                <th class="small fw-bold text-center border-0 py-2" width="6%">BATCH</th>
                                <th class="small fw-bold border-0 py-2">NO DOKUMEN / FAKTUR</th>
                                <th class="small fw-bold border-0 py-2">SUPPLIER / SUMBER</th>
                                <th class="small fw-bold text-center border-0 py-2">TGL MASUK</th>
                                <th class="small fw-bold text-center border-0 py-2">QTY MASUK</th>
                                <th class="small fw-bold text-end border-0 py-2">HARGA SATUAN</th>
                                <th class="small fw-bold text-center border-0 py-2">SISA STOK</th>
                                <th class="small fw-bold text-end border-0 py-2">TOTAL NILAI SISA</th>
                                <th class="small fw-bold text-center border-0 py-2">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($persediaan->batches as $b)
                                <tr>
                                    <td class="text-center fw-bold py-2">#{{ $b->no_batch }}</td>
                                    <td class="py-2">
                                        <strong>{{ $b->no_faktur ?: '-' }}</strong>
                                        @if($b->nota_dinas)
                                            <div class="text-muted small" style="font-size: 11px;">{{ $b->nota_dinas }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $b->supplier ?: 'Penyedia Barang' }}</td>
                                    <td class="text-center py-2">{{ $b->tanggal_masuk ? \Carbon\Carbon::parse($b->tanggal_masuk)->format('d M Y') : '-' }}</td>
                                    <td class="text-center fw-bold text-dark py-2">{{ $b->jumlah_masuk }} {{ $persediaan->satuan }}</td>
                                    <td class="text-end py-2">
                                        @if($b->harga_belum_diisi || is_null($b->harga_satuan))
                                            <span class="text-muted fst-italic">Belum diisi</span>
                                        @else
                                            Rp {{ number_format($b->harga_satuan, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold py-2 {{ $b->sisa_stok > 0 ? 'text-success' : 'text-muted' }}">
                                        {{ $b->sisa_stok }} {{ $persediaan->satuan }}
                                    </td>
                                    <td class="text-end fw-bold py-2">
                                        @if($b->harga_belum_diisi || is_null($b->harga_satuan))
                                            <span class="text-muted fst-italic">-</span>
                                        @else
                                            Rp {{ number_format($b->sisa_stok * $b->harga_satuan, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-center py-2">
                                        @if($b->sisa_stok > 0)
                                            <span class="badge-app badge-app-success">Tersedia</span>
                                        @else
                                            <span class="badge-app badge-app-secondary">Habis Terpakai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-0 border-0">
                                        <x-empty-state
                                            icon="fa-solid fa-boxes-packing"
                                            title="Belum Ada Catatan Batch Masuk"
                                            message="Belum ada riwayat batch masuk yang terdaftar untuk barang ini."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: RIWAYAT MUTASI -->
            <div class="tab-pane fade" id="mutasi" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-bottom" style="font-size: 12.5px;">
                        <thead class="table-light">
                            <tr>
                                <th class="small fw-bold text-center border-0 py-2" width="5%">NO</th>
                                <th class="small fw-bold border-0 py-2">TANGGAL</th>
                                <th class="small fw-bold text-center border-0 py-2">JENIS</th>
                                <th class="small fw-bold text-center border-0 py-2">JUMLAH</th>
                                <th class="small fw-bold border-0 py-2">TUJUAN / PENERIMA</th>
                                <th class="small fw-bold border-0 py-2">PETUGAS</th>
                                <th class="small fw-bold text-center border-0 py-2">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($persediaan->transaksis as $index => $t)
                                <tr>
                                    <td class="text-center text-muted py-2">{{ $index + 1 }}</td>
                                    <td class="py-2">{{ $t->tanggal ? \Carbon\Carbon::parse($t->tanggal)->format('d M Y') : '-' }}</td>
                                    <td class="text-center py-2">
                                        @if($t->jenis == 'masuk')
                                            <span class="badge-app badge-app-primary"><i class="fa-solid fa-arrow-down-to-bracket"></i> Masuk</span>
                                        @else
                                            <span class="badge-app badge-app-danger"><i class="fa-solid fa-arrow-up-from-bracket"></i> Keluar</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold py-2">{{ $t->jumlah }} {{ $persediaan->satuan }}</td>
                                    <td class="py-2">{{ $t->unit_kerja_penerima ?: 'Gudang Utama' }}</td>
                                    <td class="py-2">{{ $t->diajukanOleh?->name ?? 'Sistem' }}</td>
                                    <td class="text-center py-2">
                                        <x-status-badge :status="$t->status" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-0 border-0">
                                        <x-empty-state
                                            icon="fa-solid fa-clock-rotate-left"
                                            title="Belum Ada Riwayat Mutasi"
                                            message="Belum ada transaksi mutasi barang keluar atau masuk yang tercatat."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
