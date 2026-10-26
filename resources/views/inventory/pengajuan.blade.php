@extends('layouts.app')

@section('page_title', 'Validasi Pengajuan Barang Keluar')

@section('content')
@php
    $userRole = Auth::user()->role ?? '';
@endphp

<!-- Page Header -->
<x-page-header title="Validasi Barang Keluar" subtitle="Persetujuan pengeluaran barang persediaan oleh Validator / KPA">
    <x-slot:actions>
        <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 small mb-3" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->has('approve'))
    <div class="alert alert-danger alert-dismissible fade show border-0 small mb-3" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first('approve') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Antrian Menunggu Persetujuan --}}
<div class="card-app mb-4">
    <div class="card-header-app">
        <div class="d-flex align-items-center gap-2">
            <h2 class="fs-6 fw-bold mb-0 text-dark">
                <i class="fa-solid fa-clock text-warning me-2"></i>Antrian Pengajuan Barang Keluar
            </h2>
            @if($pengajuan->count() > 0)
                <span class="badge bg-warning text-dark">{{ $pengajuan->count() }} Menunggu</span>
            @endif
        </div>
    </div>
    <div class="card-body-app">
        @if($pengajuan->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle border-bottom" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold border-0 py-2">BARANG</th>
                            <th class="text-secondary small fw-bold text-center border-0 py-2">JML DIMINTA</th>
                            <th class="text-secondary small fw-bold border-0 py-2">UNIT PENERIMA</th>
                            <th class="text-secondary small fw-bold border-0 py-2">DIAJUKAN OLEH</th>
                            <th class="text-secondary small fw-bold border-0 py-2">TANGGAL</th>
                            @if($userRole === 'validator')
                                <th class="text-secondary small fw-bold text-center border-0 py-2">AKSI VALIDASI</th>
                            @else
                                <th class="text-secondary small fw-bold text-center border-0 py-2">STATUS</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuan as $p)
                            <tr>
                                <td class="fw-bold text-dark py-2">
                                    <i class="fa-solid fa-box-open text-primary me-2"></i>
                                    {{ $p->persediaan?->name ?? 'Barang #' . $p->persediaan_id }}
                                </td>
                                <td class="text-center fw-bold text-danger py-2">
                                    {{ $p->jumlah }} <span class="fw-normal text-muted small">Unit</span>
                                </td>
                                <td class="text-muted small py-2">{{ $p->unit_kerja_penerima ?? '-' }}</td>
                                <td class="small py-2">
                                    <i class="fa-solid fa-user me-1 text-muted"></i>
                                    {{ $p->diajukanOleh?->name ?? 'Operator' }}
                                </td>
                                <td class="text-muted small py-2">{{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d M Y') : '-' }}</td>
                                <td class="text-center py-2">
                                    @if($userRole === 'validator')
                                        <div class="d-flex gap-2 justify-content-center">
                                            {{-- Tombol Setujui --}}
                                            <form action="{{ route('inventory.approve', $p->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-sm"
                                                    onclick="return confirm('Setujui pengajuan pengeluaran {{ $p->jumlah }} unit {{ addslashes($p->persediaan?->name) }}?\nStok akan dipotong otomatis menggunakan metode FIFO.')">
                                                    <i class="fa-solid fa-check me-1"></i> Setujui
                                                </button>
                                            </form>
                                            {{-- Tombol Tolak --}}
                                            <button class="btn btn-outline-danger btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalTolak{{ $p->id }}">
                                                <i class="fa-solid fa-xmark me-1"></i> Tolak
                                            </button>
                                        </div>
                                    @else
                                        <span class="badge-app badge-app-warning">
                                            <i class="fa-solid fa-clock me-1"></i> Menunggu validator
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            {{-- Modal Tolak (hanya dirender untuk validator) --}}
                            @if($userRole === 'validator')
                            <div class="modal fade" id="modalTolak{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow" style="border-radius: var(--radius-lg);">
                                        <div class="modal-header border-0 pb-0">
                                            <h3 class="modal-title fw-bold text-danger fs-6"><i class="fa-solid fa-ban me-2"></i>Tolak Pengajuan</h3>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted small mb-3">Anda akan <strong>menolak</strong> pengajuan pengeluaran <strong>{{ $p->jumlah }} unit {{ $p->persediaan?->name }}</strong>. Stok tidak akan dikurangi.</p>
                                            <form action="{{ route('inventory.reject', $p->id) }}" method="POST" id="formTolak{{ $p->id }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="mb-3">
                                                    <label class="form-label-app">Alasan Penolakan</label>
                                                    <textarea class="form-control form-control-app" name="alasan" rows="3" placeholder="Contoh: Stok sedang dalam proses audit..."></textarea>
                                                </div>
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fa-solid fa-xmark me-1"></i> Konfirmasi Tolak
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state
                icon="fa-solid fa-clipboard-check"
                title="Tidak Ada Pengajuan Menunggu"
                message="Semua pengajuan barang keluar telah selesai divalidasi."
            />
        @endif
    </div>
</div>

{{-- Riwayat Keputusan --}}
<div class="card-app">
    <div class="card-header-app">
        <h2 class="fs-6 fw-bold mb-0 text-dark">
            <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Riwayat Keputusan (20 Terakhir)
        </h2>
    </div>
    <div class="card-body-app">
        @if($riwayat->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle border-bottom" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold border-0 py-2">BARANG</th>
                            <th class="text-secondary small fw-bold text-center border-0 py-2">JUMLAH</th>
                            <th class="text-secondary small fw-bold text-center border-0 py-2">STATUS</th>
                            <th class="text-secondary small fw-bold border-0 py-2">DISETUJUI/DITOLAK OLEH</th>
                            <th class="text-secondary small fw-bold border-0 py-2">CATATAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayat as $r)
                            <tr>
                                <td class="fw-bold text-dark py-2">{{ $r->persediaan?->name ?? '#' . $r->persediaan_id }}</td>
                                <td class="text-center py-2">{{ $r->jumlah }} Unit</td>
                                <td class="text-center py-2">
                                    <x-status-badge :status="$r->status" />
                                </td>
                                <td class="text-muted small py-2">{{ $r->diputuskanOleh?->name ?? '-' }}</td>
                                <td class="text-muted small py-2">{{ $r->catatan_penolakan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state
                icon="fa-solid fa-clock-rotate-left"
                title="Belum Ada Riwayat Keputusan"
                message="Belum ada riwayat persetujuan atau penolakan pengajuan barang keluar."
            />
        @endif
    </div>
</div>
@endsection
