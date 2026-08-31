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
        color: #0d6efd !important;
    }
</style>

<!-- Banner Sambutan -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
            <div class="card-body p-4 text-white position-relative">
                <i class="fa-solid fa-ship position-absolute top-50 end-0 translate-middle-y me-4" style="font-size: 8rem; opacity: 0.1;"></i>
                <h4 class="fw-bold mb-1">Selamat Datang, {{ Auth::user()->name ?? 'Pengguna Sistem' }}!</h4>
                <p class="mb-0 opacity-75">
                    Sistem Informasi Layanan Operasional Fasilitas &amp; Barang Inventaris (LOFBI) &mdash; <strong>KSOP Kelas I Banten</strong>.
                    <span class="badge bg-white text-primary text-capitalize ms-2 fw-bold">{{ Auth::user()->role ?? 'Operator' }}</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Alert Peringatan Jika Ada Pengajuan / Stok Menipis -->
@if(($pengajuanMenunggu ?? 0) > 0 || ($stokMenipis ?? 0) > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm rounded-4 py-3 px-4 mb-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-triangle-exclamation fs-3 text-warning me-3"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Perhatian Sistem Terkini</h6>
                    <small class="text-muted">
                        @if(($pengajuanMenunggu ?? 0) > 0)
                            Terdapat <strong>{{ $pengajuanMenunggu }} pengajuan barang keluar</strong> yang menunggu validasi.
                        @endif
                        @if(($stokMenipis ?? 0) > 0)
                            Terdapat <strong>{{ $stokMenipis }} item persediaan</strong> dengan stok mendekati / di bawah batas minimum.
                        @endif
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if(($pengajuanMenunggu ?? 0) > 0 && in_array(Auth::user()->role ?? '', ['validator', 'admin']))
                    <a href="{{ route('inventory.pengajuan') }}" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm">
                        <i class="fa-solid fa-stamp me-1"></i> Validasi Pengajuan
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-dark btn-sm fw-bold px-3">
                    Lihat Semua &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Kartu Ringkasan Statistik -->
<div class="row mb-4">
    <!-- Kartu 1: Aset Aktif -->
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/assets') }}" class="card-link-wrapper">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-primary border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-boxes-stacked text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-1">Total Aset Aktif</p>
                        <h3 class="fw-bold mb-0 text-dark transition-color">{{ number_format($totalAset ?? 0) }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Kartu 2: Persediaan -->
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/inventory') }}" class="card-link-wrapper">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-success border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-box-open text-success fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-1">Total Stok Persediaan</p>
                        <h3 class="fw-bold mb-0 text-dark transition-color">{{ number_format($totalStokPersediaan ?? 0) }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Kartu 3: Nilai Buku Total -->
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/reports') }}" class="card-link-wrapper">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-warning border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-rupiah-sign text-warning fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-1">Total Nilai Buku Aset</p>
                        <h3 class="fw-bold mb-0 text-dark transition-color" style="font-size: 1.1rem;">Rp {{ number_format($totalNilaiBuku ?? 0, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Kartu 4: Barang Rusak -->
    <div class="col-md-3">
        <a href="{{ url('/assets') }}" class="card-link-wrapper">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-danger border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-screwdriver-wrench text-danger fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-1">Aset Rusak</p>
                        <h3 class="fw-bold mb-0 text-dark transition-color">{{ number_format($asetRusak ?? 0) }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
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
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-chart-line text-primary me-2"></i>Tren Mutasi Persediaan Barang ({{ date('Y') }})</h6>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> Laporan</a>
            </div>
            <div class="card-body">
                <canvas id="mutasiChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <!-- Aktivitas Terbaru Real dari AuditLog -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Aktivitas Terbaru</h6>
                <a href="{{ route('notifications.index') }}" class="text-decoration-none small fw-bold">Semua &rarr;</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentLogs ?? [] as $log)
                        @php
                            $iconBg = match($log->modul) {
                                'Aset' => 'bg-primary-subtle text-primary',
                                'Persediaan' => 'bg-success-subtle text-success',
                                'Opname' => 'bg-warning-subtle text-warning',
                                default => 'bg-info-subtle text-info',
                            };
                            $icon = match($log->modul) {
                                'Aset' => 'fa-boxes-stacked',
                                'Persediaan' => 'fa-box-open',
                                'Opname' => 'fa-clipboard-check',
                                default => 'fa-bell',
                            };
                        @endphp
                        <li class="list-group-item px-4 py-3 border-light">
                            <div class="d-flex align-items-start">
                                <div class="{{ $iconBg }} rounded-circle p-2 me-3" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid {{ $icon }}" style="font-size: 13px;"></i>
                                </div>
                                <div>
                                    <p class="mb-0 fw-bold small text-dark">{{ $log->modul }} &bull; {{ $log->aksi }}</p>
                                    <p class="mb-1 text-muted" style="font-size: 11px;">{{ $log->detail }}</p>
                                    <small class="text-secondary" style="font-size: 10px;">
                                        {{ $log->user_name ?? 'Sistem' }} &bull; {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                                    </small>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted small">Belum ada aktivitas tercatat.</li>
                    @endforelse
                </ul>
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
                labels: {!! json_encode($chartLabels ?? ['Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags']) !!},
                datasets: [
                    {
                        label: 'Barang Masuk (Unit)',
                        data: {!! json_encode($chartMasuk ?? [10, 25, 50, 40, 30, 93]) !!},
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.12)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0d6efd'
                    }, 
                    {
                        label: 'Barang Keluar (Unit)',
                        data: {!! json_encode($chartKeluar ?? [5, 12, 20, 15, 25, 0]) !!},
                        borderColor: '#fd7e14',
                        backgroundColor: 'rgba(253, 126, 20, 0.12)',
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