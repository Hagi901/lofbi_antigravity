@extends('layouts.app')

@section('page_title', 'Opname Fisik Persediaan')

@section('content')
@php
    $userRole = Auth::user()->role ?? '';
@endphp

<!-- Page Header -->
<x-page-header title="Opname Fisik Persediaan" subtitle="Pencatatan dan verifikasi kesesuaian fisik stok gudang dengan sistem (SAKTI)">
    <x-slot:actions>
        @if(in_array($userRole, ['admin', 'operator']))
            <a href="{{ route('opname.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Buka Sesi Opname
            </a>
        @endif
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 small mb-3">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 small mb-3">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Panduan Alur SAKTI --}}
<div class="card-app mb-4 p-3 bg-white">
    <div class="d-flex align-items-center gap-2 flex-wrap text-muted small">
        <span class="badge-app badge-app-secondary">1. Draft</span>
        <i class="fa-solid fa-arrow-right text-muted" style="font-size: 11px;"></i>
        <span class="badge-app badge-app-warning">2. Input Fisik</span>
        <i class="fa-solid fa-arrow-right text-muted" style="font-size: 11px;"></i>
        <span class="badge-app badge-app-warning">3. Menunggu Persetujuan</span>
        <i class="fa-solid fa-arrow-right text-muted" style="font-size: 11px;"></i>
        <span class="badge-app badge-app-success">4. Disetujui (Stok Disesuaikan)</span>
    </div>
</div>

<div class="card-app">
    <div class="card-header-app">
        <h2 class="fs-6 fw-bold mb-0 text-dark">
            <i class="fa-solid fa-clipboard-check text-primary me-2"></i>Riwayat Sesi Opname Fisik
        </h2>
    </div>

    <div class="card-body-app">
        @if(isset($sesi) && $sesi->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle border-bottom w-100" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold border-0 py-2">PERIODE</th>
                            <th class="text-secondary small fw-bold border-0 py-2">TANGGAL</th>
                            <th class="text-secondary small fw-bold border-0 py-2">PETUGAS</th>
                            <th class="text-secondary small fw-bold text-center border-0 py-2">ITEM</th>
                            <th class="text-secondary small fw-bold text-center border-0 py-2">SELISIH</th>
                            <th class="text-secondary small fw-bold text-center border-0 py-2">STATUS</th>
                            <th class="text-secondary small fw-bold text-center border-0 py-2">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sesi as $s)
                            <tr>
                                <td class="fw-bold text-dark py-2">{{ $s->periode ?? 'Opname #' . $s->id }}</td>
                                <td class="text-muted small py-2">{{ $s->tanggal ? $s->tanggal->format('d M Y') : '-' }}</td>
                                <td class="small py-2">
                                    <i class="fa-solid fa-user text-secondary me-1"></i>{{ $s->admin?->name ?? 'Admin' }}
                                </td>
                                <td class="text-center fw-bold text-primary py-2">{{ $s->details->count() }}</td>
                                <td class="text-center py-2">
                                    @php $selisih = $s->jumlahSelisih(); @endphp
                                    @if($s->status === 'draft')
                                        <span class="text-muted small">-</span>
                                    @elseif($selisih === 0)
                                        <span class="badge-app badge-app-success" style="font-size: 10px;">Cocok</span>
                                    @else
                                        <span class="badge-app badge-app-danger" style="font-size: 10px;">{{ $selisih }} item selisih</span>
                                    @endif
                                </td>
                                <td class="text-center py-2">
                                    <span class="badge {{ $s->statusBadgeClass() }} border px-2 py-1">
                                        {{ $s->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-center py-2">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('opname.show', $s->id) }}" class="btn btn-light btn-sm btn-action-icon" title="Lihat Detail">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </a>
                                        @if(in_array($s->status, ['draft', 'ditolak']) && in_array($userRole, ['admin', 'operator']))
                                            <a href="{{ route('opname.input_fisik', $s->id) }}" class="btn btn-warning btn-sm" title="Input Hasil Fisik" style="font-size: 11px;">
                                                <i class="fa-solid fa-pen-to-square me-1"></i>Input Fisik
                                            </a>
                                        @endif
                                        @if($s->status === 'disetujui')
                                            <a href="{{ route('reports.opname.pdf', ['sesi_id' => $s->id]) }}" target="_blank" class="btn btn-light btn-sm btn-action-icon" title="Cetak Berita Acara">
                                                <i class="fa-solid fa-print text-dark"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state
                icon="fa-solid fa-boxes-packing"
                title="Belum Ada Sesi Opname Fisik"
                message="Lakukan opname fisik secara berkala (semesteran) sesuai ketentuan SAKTI untuk memastikan kesesuaian stok sistem dengan kondisi fisik di gudang."
                actionText="Buka Sesi Pertama"
                :actionUrl="route('opname.create')"
                :actionPermission="in_array($userRole, ['admin', 'operator'])"
            />
        @endif
    </div>
</div>
@endsection
