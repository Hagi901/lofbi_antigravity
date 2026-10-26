@extends('layouts.app')

@section('page_title', 'Persediaan Barang (Metode FIFO)')

@section('content')
@php
    $userRole = Auth::user()->role ?? '';
    $totalItemCount = $totalItems ?? $items->count();
@endphp

<!-- Page Header -->
<x-page-header title="Persediaan Barang (FIFO)" subtitle="Buku inventaris persediaan dan perputaran stok sistematis">
    <x-slot:actions>
        @if(in_array($userRole, ['validator', 'admin']) && ($pengajuanMenunggu ?? 0) > 0)
            <a href="{{ route('inventory.pengajuan') }}" class="btn btn-warning btn-sm">
                <i class="fa-solid fa-stamp"></i> Validasi Pengajuan ({{ $pengajuanMenunggu }})
            </a>
        @endif
        @if(in_array($userRole, ['admin', 'operator']))
            <a href="{{ route('upload.index') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Berkas
            </a>
            <a href="{{ route('inventory.out.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Ajukan Keluar
            </a>
        @endif
    </x-slot:actions>
</x-page-header>

<!-- Ringkasan Cepat Persediaan -->
<div class="row g-3 mb-4">
    <!-- Kartu 1: Total Jenis Barang -->
    <div class="col-md-4 col-sm-6">
        <x-stat-card
            label="Total Jenis Barang"
            :value="number_format($totalItemCount)"
            subtext="Item Terdaftar"
            icon="fa-solid fa-boxes-packing"
            color="success"
        />
    </div>

    <!-- Kartu 2: Total Seluruh Stok Fisik -->
    <div class="col-md-4 col-sm-6">
        <x-stat-card
            label="Total Seluruh Stok Fisik"
            :value="number_format($totalStok ?? 0)"
            subtext="Unit Persediaan"
            icon="fa-solid fa-box-open"
            color="primary"
        />
    </div>

    <!-- Kartu 3: Total Nilai Rupiah Saldo Gudang -->
    <div class="col-md-4 col-sm-12">
        @php
            $nilaiFormatted = 'Rp ' . number_format($totalNilaiRupiah ?? 0, 0, ',', '.');
            $subtextVal = ($hasHargaBelumDiisi ?? false) ? 'Nilai belum lengkap' : null;
        @endphp
        <x-stat-card
            label="Total Nilai Persediaan"
            :value="$nilaiFormatted"
            :subtext="$subtextVal"
            icon="fa-solid fa-rupiah-sign"
            color="warning"
        />
    </div>
</div>

<!-- Tabel Kartu Persediaan Utama -->
<div class="card-app">
    <div class="card-header-app">
        <h2 class="fs-6 fw-bold mb-0 text-dark">
            <i class="fa-solid fa-list-check text-primary me-2"></i>Daftar Buku Persediaan Barang (FIFO)
        </h2>
    </div>

    <div class="card-body-app">
        <!-- Notifikasi Sukses / Error -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 small mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 small mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle border-bottom" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold border-0 py-2" width="12%">KODE / ID</th>
                        <th class="text-secondary small fw-bold border-0 py-2" width="28%">NAMA BARANG</th>
                        <th class="text-secondary small fw-bold border-0 py-2" width="18%">KATEGORI</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-center" width="14%">SISA STOK (FIFO)</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-end" width="14%">NILAI SALDO (Rp)</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-center" width="8%">STATUS</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-center" width="6%">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $sisaStok = $item->batches->sum('sisa_stok');
                            $nilaiSaldo = $item->batches->sum(fn($b) => $b->sisa_stok * $b->harga_satuan);
                            $minStok = $item->stok_minimum ?? 0;

                            if ($sisaStok > $minStok) {
                                $statusBadgeType = 'success';
                                $statusLabel = 'Aman';
                            } elseif ($sisaStok > 0) {
                                $statusBadgeType = 'warning';
                                $statusLabel = 'Menipis';
                            } else {
                                $statusBadgeType = 'danger';
                                $statusLabel = 'Habis';
                            }
                        @endphp
                        <tr>
                            <td class="py-2">
                                <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11px;">
                                    INV-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td class="py-2">
                                <a href="{{ route('inventory.show', $item->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $item->name }}
                                </a>
                                @if($item->merk)
                                    <div class="text-muted small" style="font-size: 11px;">{{ $item->merk }}</div>
                                @endif
                            </td>
                            <td class="py-2">
                                <span class="badge-app badge-app-secondary">
                                    {{ $item->jenisBarang?->kategori?->nama ?? 'Persediaan' }}
                                </span>
                            </td>
                            <td class="text-center fw-bold py-2 {{ $sisaStok <= 0 ? 'text-danger' : '' }}">
                                {{ number_format($sisaStok) }} <span class="fw-normal small text-muted">{{ $item->satuan }}</span>
                            </td>
                            <td class="text-end fw-bold text-dark py-2">
                                Rp {{ number_format($nilaiSaldo, 0, ',', '.') }}
                            </td>
                            <td class="text-center py-2">
                                <x-status-badge :status="$statusLabel" :type="$statusBadgeType" />
                            </td>
                            <td class="text-center py-2">
                                <x-action-buttons
                                    :viewUrl="route('inventory.show', $item->id)"
                                    :editUrl="in_array($userRole, ['admin', 'operator']) ? route('inventory.edit', $item->id) : null"
                                    :canEdit="in_array($userRole, ['admin', 'operator'])"
                                    :canDelete="$userRole === 'admin'"
                                    :deleteAction="$userRole === 'admin' ? route('inventory.destroy', $item->id) : null"
                                    deleteConfirm="Apakah Anda yakin ingin menghapus master barang ini beserta seluruh riwayat batch-nya?"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0 border-0">
                                <x-empty-state
                                    icon="fa-solid fa-boxes-packing"
                                    title="Belum Ada Data Barang Persediaan"
                                    message="Data master persediaan belum tercatat. Silakan unggah berkas SAKTI / SIMAN melalui menu Upload & Validasi."
                                    actionText="Upload Berkas"
                                    :actionUrl="route('upload.index')"
                                    :actionPermission="in_array($userRole, ['admin', 'operator'])"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
