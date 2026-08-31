@extends('layouts.app')

@section('page_title', 'Rincian Hasil Opname Fisik')

@section('content')
<div class="row">
    <!-- Panel Header Sesi -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="{{ route('opname.index') }}" class="btn btn-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <h5 class="fw-bold text-dark mb-0">Sesi Opname Fisik #{{ str_pad($sesi->id, 3, '0', STR_PAD_LEFT) }}</h5>
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-1 rounded-pill">
                            <i class="fa-solid fa-check me-1"></i> Selesai
                        </span>
                    </div>
                    <p class="text-muted small mb-0 ms-4 ps-2">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $sesi->ruangan?->nama ?? 'Semua Ruangan' }} ({{ $sesi->ruangan?->gedung ?? 'Gedung Utama' }})
                        &bull; <i class="fa-regular fa-calendar-check text-primary me-1"></i> {{ $sesi->tanggal ? $sesi->tanggal->format('d F Y') : '-' }}
                        &bull; <i class="fa-solid fa-user me-1 text-secondary"></i> Petugas: {{ $sesi->admin?->name ?? 'Admin LOFBI' }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('reports.opname.pdf', ['sesi_id' => $sesi->id]) }}" target="_blank" class="btn btn-dark fw-bold px-3 shadow-sm">
                        <i class="fa-solid fa-print me-1"></i> Cetak Berita Acara
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Aset yang Diverifikasi -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Daftar Fisik Barang Terverifikasi ({{ $sesi->details->count() }} Item)
                </h6>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="table-responsive mt-2">
                    <table class="table table-hover align-middle border-bottom">
                        <thead class="table-light">
                            <tr>
                                <th class="text-secondary small fw-bold text-center" width="5%">NO</th>
                                <th class="text-secondary small fw-bold">KODE ASET</th>
                                <th class="text-secondary small fw-bold">NAMA BARANG</th>
                                <th class="text-secondary small fw-bold">KATEGORI</th>
                                <th class="text-secondary small fw-bold text-center">KONDISI AKTUAL</th>
                                <th class="text-secondary small fw-bold">CATATAN PEMERIKSAAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sesi->details as $index => $detail)
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-white text-dark border shadow-sm px-2 py-1">
                                            {{ $detail->aset?->kode_aset ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        {{ $detail->aset?->name ?? 'Barang #' . $detail->aset_id }}
                                        @if($detail->aset?->merk)
                                            <br><small class="text-muted fw-normal">{{ $detail->aset->merk }} {{ $detail->aset->model }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $detail->aset?->jenisBarang?->kategori?->nama ?? '-' }}</td>
                                    <td class="text-center">
                                        @php
                                            $kondisi = $detail->kondisi_aktual ?? ($detail->aset?->kondisi ?? 'baik');
                                            $badge = match($kondisi) {
                                                'rusak_berat' => 'bg-danger-subtle text-danger border-danger',
                                                'rusak_ringan' => 'bg-warning-subtle text-warning border-warning',
                                                default => 'bg-success-subtle text-success border-success',
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }} border px-2 py-1">
                                            {{ ucfirst(str_replace('_', ' ', $kondisi)) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $detail->catatan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Tidak ada data aset pada sesi ini.</td>
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
