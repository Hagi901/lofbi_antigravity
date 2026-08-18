@extends('layouts.app')

@section('page_title', 'Dashboard Utama')

@section('content')
<!-- Tambahan CSS untuk Efek Hover Kartu -->
<style>
    .card-link-wrapper {
        display: block;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .card-link-wrapper:hover {
        transform: translateY(-5px);
    }
    .card-link-wrapper:hover .card {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .card-link-wrapper:hover h3 {
        color: #0d6efd !important; /* Angka berubah biru saat disentuh */
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
            <div class="card-body p-4 text-white position-relative">
                <i class="fa-solid fa-ship position-absolute top-50 end-0 translate-middle-y me-4" style="font-size: 8rem; opacity: 0.1;"></i>
                <h4 class="fw-bold mb-1">Selamat Datang, Operator Sistem!</h4>
                <p class="mb-0 opacity-75">Sistem Informasi Layanan Operasional Fisik Barang & Inventaris (LOFBI) - KSOP Kelas I Banten.</p>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Ringkasan Statistik (Sekarang Bisa Diklik) -->
<div class="row mb-4">
    <!-- Kartu 1: Aset Aktif -> Mengarah ke Manajemen Aset -->
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/assets') }}" class="card-link-wrapper">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-primary border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-boxes-stacked text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-1">Total Aset Aktif</p>
                        <h3 class="fw-bold mb-0 text-dark transition-color">1,284</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Kartu 2: Persediaan -> Mengarah ke Persediaan FIFO -->
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/inventory') }}" class="card-link-wrapper">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-success border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-box-open text-success fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-1">Persediaan (Baru)</p>
                        <h3 class="fw-bold mb-0 text-dark transition-color">450</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Kartu 3: Opname Selesai -> Mengarah ke Opname Fisik -->
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/opname') }}" class="card-link-wrapper">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-warning border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-clipboard-check text-warning fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-1">Opname Selesai</p>
                        <h3 class="fw-bold mb-0 text-dark transition-color">12 <span class="fs-6 text-muted fw-normal">Sesi</span></h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Kartu 4: Barang Rusak -> Mengarah ke Laporan (atau halaman filter khusus) -->
    <div class="col-md-3">
        <a href="{{ url('/reports') }}" class="card-link-wrapper">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-danger border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-screwdriver-wrench text-danger fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-1">Barang Rusak</p>
                        <h3 class="fw-bold mb-0 text-dark transition-color">18</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <!-- Bagian Grafik Visual -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-chart-line text-primary me-2"></i>Tren Mutasi Barang (2026)</h6>
                <button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-download"></i> Unduh</button>
            </div>
            <div class="card-body">
                <canvas id="mutasiChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Aktivitas Terbaru -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Aktivitas Terbaru</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-4 py-3 border-light">
                        <div class="d-flex align-items-start">
                            <div class="bg-success-subtle text-success rounded-circle p-2 me-3"><i class="fa-solid fa-arrow-right-to-bracket"></i></div>
                            <div>
                                <p class="mb-0 fw-bold small text-dark">Barang Masuk (ATK)</p>
                                <p class="mb-1 text-muted" style="font-size: 12px;">50 Rim Kertas HVS ditambahkan.</p>
                                <small class="text-secondary" style="font-size: 11px;">10 Menit yang lalu</small>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item px-4 py-3 border-light">
                        <div class="d-flex align-items-start">
                            <div class="bg-warning-subtle text-warning rounded-circle p-2 me-3"><i class="fa-solid fa-clipboard-check"></i></div>
                            <div>
                                <p class="mb-0 fw-bold small text-dark">Sesi Opname Disimpan</p>
                                <p class="mb-1 text-muted" style="font-size: 12px;">Pengecekan Ruang IT selesai.</p>
                                <small class="text-secondary" style="font-size: 11px;">1 Jam yang lalu</small>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item px-4 py-3">
                        <div class="d-flex align-items-start">
                            <div class="bg-danger-subtle text-danger rounded-circle p-2 me-3"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                            <div>
                                <p class="mb-0 fw-bold small text-dark">Barang Keluar (Elektronik)</p>
                                <p class="mb-1 text-muted" style="font-size: 12px;">2 Unit Printer didistribusikan.</p>
                                <small class="text-secondary" style="font-size: 11px;">Kemarin, 14:30</small>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-white text-center py-3 border-0">
                <a href="#" class="text-decoration-none small fw-bold">Lihat Semua Aktivitas</a>
            </div>
        </div>
    </div>
</div>

<!-- Library Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('mutasiChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags'],
                datasets: [
                    {
                        label: 'Barang Masuk',
                        data: [65, 59, 80, 81, 56, 55, 40, 75],
                        borderColor: '#0d6efd', /* Warna Biru */
                        backgroundColor: 'rgba(13, 110, 253, 0.15)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0d6efd'
                    }, 
                    {
                        label: 'Barang Keluar',
                        data: [28, 48, 40, 19, 86, 27, 90, 45],
                        borderColor: '#fd7e14', /* Warna Oranye Terang */
                        backgroundColor: 'rgba(253, 126, 20, 0.15)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#fd7e14'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endsection