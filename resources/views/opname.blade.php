@extends('layouts.app')

@section('page_title', 'Opname Fisik Barang')

@section('content')
<div class="card border-0 shadow-sm rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Riwayat Opname Fisik</h6>
        <a href="{{ route('opname.create') }}" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm rounded-pill">
            <i class="fa-solid fa-plus me-1"></i> Mulai Opname Baru
        </a>
    </div>
    <div class="card-body text-center py-5 mt-4">
        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px;">
            <i class="fa-solid fa-boxes-packing text-secondary" style="font-size: 4rem; opacity: 0.5;"></i>
        </div>
        <h5 class="fw-bold text-dark mt-2">Belum ada data Opname</h5>
        <p class="text-muted small mb-4" style="line-height: 1.6;">Fitur pencocokan stok sistem vs fisik di gudang akan ditampilkan di sini.<br>Lakukan pengecekan rutin untuk memastikan keakuratan data barang.</p>
        <button class="btn btn-outline-secondary btn-sm px-4 fw-bold rounded-pill shadow-sm">
            <i class="fa-solid fa-rotate-right me-1"></i> Muat Ulang Data
        </button>
    </div>
</div>
@endsection