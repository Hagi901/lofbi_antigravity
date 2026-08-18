{{-- Memanggil kerangka utama yang baru saja kita buat --}}
@extends('layouts.app')

{{-- Mengisi judul halaman di bagian Navbar atas --}}
@section('page_title', 'Dashboard Utama')

{{-- Mengisi bagian konten tengah --}}
@section('content')
<div class="row g-4 mb-4">
    
    <!-- Card Total Aset -->
    <div class="col-md-4">
        <div class="card h-100 border-start border-4 border-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1 text-uppercase" style="font-size: 0.85rem;">Total Aset (Barang Tetap)</p>
                    <!-- Ini akan memunculkan angka dari database -->
                    <h2 class="mb-0 fw-bold">{{ $total_assets }}</h2>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded">
                    <i class="fa-solid fa-boxes-stacked text-primary fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Pengguna Sistem -->
    <div class="col-md-4">
        <div class="card h-100 border-start border-4 border-success">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1 text-uppercase" style="font-size: 0.85rem;">Pengguna Sistem</p>
                    <!-- Ini akan memunculkan angka dari database -->
                    <h2 class="mb-0 fw-bold">{{ $total_users }}</h2>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded">
                    <i class="fa-solid fa-users text-success fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Menunggu Approval -->
    <div class="col-md-4">
        <div class="card h-100 border-start border-4 border-warning">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1 text-uppercase" style="font-size: 0.85rem;">Pending Approval</p>
                    <h2 class="mb-0 fw-bold">0</h2>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded">
                    <i class="fa-solid fa-clipboard-question text-warning fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informasi Tambahan -->
<div class="card">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">Selamat Datang di LOFBI</h6>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">Laporan Opname Fisik Barang & Inventarisasi (LOFBI) adalah sistem pendamping untuk memastikan presisi data fisik dan sinkronisasi dengan SIMAN. Gunakan menu di sebelah kiri untuk mulai mengelola data aset dan persediaan (FIFO).</p>
    </div>
</div>
@endsection