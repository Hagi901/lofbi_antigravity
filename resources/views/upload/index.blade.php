@extends('layouts.app')

@section('page_title', 'Upload Data & Validasi')

@section('content')
@php
    $userRole = Auth::user()->role ?? '';
@endphp

<!-- Header Halaman -->
<x-page-header title="Upload &amp; Validasi Data" subtitle="Data persediaan SAKTI masuk ke antrean staging untuk diverifikasi sebelum resmi masuk ke inventaris">
    <x-slot:actions>
        @if(in_array($userRole, ['admin', 'operator']))
            <a href="#formUpload" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload File
            </a>
        @endif
    </x-slot:actions>
</x-page-header>

<!-- Alert Sukses / Error -->
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

<!-- Ringkasan Statistik Status Paket -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-stat-card
            label="Menunggu"
            :value="$statMenunggu"
            subtext="Paket Staging"
            icon="fa-solid fa-clock-rotate-left"
            color="warning"
            :url="route('upload.index', ['status' => 'menunggu'])"
        />
    </div>
    <div class="col-6 col-md-3">
        <x-stat-card
            label="Disetujui Sebagian"
            :value="$statSebagian"
            subtext="Paket Staging"
            icon="fa-solid fa-circle-half-stroke"
            color="info"
            :url="route('upload.index', ['status' => 'disetujui_sebagian'])"
        />
    </div>
    <div class="col-6 col-md-3">
        <x-stat-card
            label="Disetujui Penuh"
            :value="$statDisetujui"
            subtext="Paket Selesai"
            icon="fa-solid fa-circle-check"
            color="success"
            :url="route('upload.index', ['status' => 'disetujui'])"
        />
    </div>
    <div class="col-6 col-md-3">
        <x-stat-card
            label="Ditolak"
            :value="$statDitolak"
            subtext="Perlu Revisi"
            icon="fa-solid fa-circle-xmark"
            color="danger"
            :url="route('upload.index', ['status' => 'ditolak'])"
        />
    </div>
</div>

@if(in_array($userRole, ['admin', 'operator']))
<!-- Form Upload Paket (Hanya Admin / Operator) -->
<div class="card-app mb-4" id="formUpload">
    <div class="card-header-app">
        <h2 class="fs-6 fw-bold text-dark mb-0">
            <i class="fa-solid fa-file-excel text-success me-2"></i>Unggah Paket Persediaan SAKTI Baru
        </h2>
    </div>
    <div class="card-body-app">
        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-app">Berkas Laporan (.xlsx / .csv) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control form-control-app" accept=".xlsx,.csv" required>
                    <div class="form-text" style="font-size: 11px;">
                        Contoh: <code>bahan_opname_fisik.xlsx</code>. Sheet default: <em>input LOFBI</em>. Format Word (.docx) tidak didukung.
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label-app">Ruangan / Gudang Tujuan</label>
                    <select name="ruangan_id" class="form-select form-select-app">
                        <option value="">Gudang Utama (Default)</option>
                        @foreach($ruangans as $r)
                            <option value="{{ $r->id }}">{{ $r->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label-app">Nama Sheet (Opsional)</label>
                    <input type="text" name="sheet_name" class="form-control form-control-app" placeholder="Contoh: input LOFBI">
                    <div class="form-text" style="font-size: 11px;">Kosongkan untuk deteksi otomatis.</div>
                </div>
                <div class="col-12 text-end pt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Proses Upload
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Tabel Daftar Paket Unggahan -->
<div class="card-app">
    <div class="card-header-app">
        <div>
            <h2 class="fs-6 fw-bold text-dark mb-0">Riwayat Paket Unggahan</h2>
            <span class="text-muted small">Menampilkan riwayat berkas staging yang telah diunggah</span>
        </div>
        @if(request()->filled('status'))
            <a href="{{ route('upload.index') }}" class="btn btn-sm btn-light">
                <i class="fa-solid fa-xmark me-1"></i> Hapus Filter
            </a>
        @endif
    </div>
    <div class="card-body-app p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 80px;">ID</th>
                        <th>Nama Berkas</th>
                        <th>Pengunggah</th>
                        <th class="text-center">Jumlah Baris</th>
                        <th class="text-center">Status Paket</th>
                        <th>Waktu Unggah</th>
                        <th class="text-end pe-4" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pakets as $p)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">#{{ $p->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-file-excel text-success fs-5"></i>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $p->nama_file }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Tipe: {{ strtoupper($p->jenis) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $p->pengunggah->name ?? 'Sistem' }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ ucfirst($p->pengunggah->role ?? '') }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                    {{ number_format($p->jumlah_baris) }} baris
                                </span>
                            </td>
                            <td class="text-center">
                                @if($p->status === 'menunggu')
                                    <span class="badge-app badge-app-warning">
                                        <i class="fa-solid fa-clock"></i> Menunggu
                                    </span>
                                @elseif($p->status === 'disetujui_sebagian')
                                    <span class="badge-app badge-app-info">
                                        <i class="fa-solid fa-circle-half-stroke"></i> Sebagian
                                    </span>
                                @elseif($p->status === 'disetujui')
                                    <span class="badge-app badge-app-success">
                                        <i class="fa-solid fa-circle-check"></i> Disetujui
                                    </span>
                                @elseif($p->status === 'ditolak')
                                    <span class="badge-app badge-app-danger">
                                        <i class="fa-solid fa-circle-xmark"></i> Ditolak
                                    </span>
                                @else
                                    <span class="badge-app badge-app-secondary">{{ ucfirst($p->status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size: 12px;">
                                {{ $p->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('upload.show', $p->id) }}" class="btn btn-sm btn-outline-primary">
                                    @if($userRole === 'validator' && in_array($p->status, ['menunggu', 'disetujui_sebagian']))
                                        <i class="fa-solid fa-user-check me-1"></i> Validasi
                                    @else
                                        <i class="fa-solid fa-eye me-1"></i> Detail
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0 border-0">
                                <x-empty-state
                                    icon="fa-solid fa-cloud-arrow-up"
                                    title="Belum Ada Paket Unggahan"
                                    message="Belum ada berkas SAKTI yang diunggah ke antrean staging."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pakets->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $pakets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
