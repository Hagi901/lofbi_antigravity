@extends('layouts.app')

@section('page_title', 'Dashboard Utama')

@section('content')
@php
    $userRole = Auth::user()->role ?? '';
@endphp

<!-- Banner Sambutan -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, var(--color-primary), #0284c7);">
            <div class="card-body p-4 text-white position-relative">
                <i class="fa-solid fa-ship position-absolute top-50 end-0 translate-middle-y me-4 d-none d-md-block" style="font-size: 7rem; opacity: 0.12;"></i>
                <h1 class="fw-bold mb-1 fs-4 text-white">Selamat Datang, {{ Auth::user()->name ?? 'Pengguna Sistem' }}!</h1>
                <p class="mb-0 opacity-90 small">
                    Sistem Layanan Operasional Fasilitas &amp; Barang Inventaris (LOFBI) - <strong>KSOP Kelas I Banten</strong>.
                    <span class="badge bg-white text-primary text-capitalize ms-2 fw-semibold px-2 py-1">{{ $userRole ?: 'Operator' }}</span>
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
                <i class="fa-solid fa-triangle-exclamation fs-4 text-warning me-3 flex-shrink-0"></i>
                <div>
                    <h2 class="fw-bold mb-0 text-dark fs-6">Perhatian Sistem Terkini</h2>
                    <small class="text-muted">
                        @if(($pengajuanMenunggu ?? 0) > 0)
                            Terdapat <strong>{{ $pengajuanMenunggu }} pengajuan barang keluar</strong> yang menunggu validasi.
                        @endif
                        @if(($stokMenipis ?? 0) > 0)
                            Terdapat <strong>{{ $stokMenipis }} item persediaan</strong> dengan stok mendekati atau di bawah batas minimum.
                        @endif
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if(($pengajuanMenunggu ?? 0) > 0 && in_array($userRole, ['validator', 'admin']))
                    <a href="{{ route('inventory.pengajuan') }}" class="btn btn-warning btn-sm">
                        <i class="fa-solid fa-stamp me-1"></i> Validasi Pengajuan
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-dark btn-sm">
                    Lihat Semua
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Kartu Ringkasan Statistik -->
<div class="row g-3 mb-4">
    <!-- Kartu 1: Aset Aktif -->
    <div class="col-md-3 col-sm-6">
        <x-stat-card
            label="Total Aset Aktif"
            :value="number_format($totalAset ?? 0)"
            subtext="Unit Terdaftar"
            icon="fa-solid fa-boxes-stacked"
            color="primary"
            :url="url('/assets')"
        />
    </div>

    <!-- Kartu 2: Persediaan -->
    <div class="col-md-3 col-sm-6">
        <x-stat-card
            label="Total Stok Persediaan"
            :value="number_format($totalStokPersediaan ?? 0)"
            subtext="Unit Fisik"
            icon="fa-solid fa-box-open"
            color="success"
            :url="url('/inventory')"
        />
    </div>

    <!-- Kartu 3: Nilai Buku Total -->
    <div class="col-md-3 col-sm-6">
        <x-stat-card
            label="Total Nilai Buku Aset"
            :value="'Rp ' . number_format($totalNilaiBuku ?? 0, 0, ',', '.')"
            subtext="Metode SIMAN"
            icon="fa-solid fa-rupiah-sign"
            color="warning"
            :url="url('/reports')"
        />
    </div>

    <!-- Kartu 4: Barang Rusak -->
    <div class="col-md-3 col-sm-6">
        <x-stat-card
            label="Aset Kondisi Rusak"
            :value="number_format($asetRusak ?? 0)"
            subtext="Perlu Pemeliharaan"
            icon="fa-solid fa-screwdriver-wrench"
            color="danger"
            :url="url('/assets')"
        />
    </div>
</div>

<div class="row g-3">
    <!-- Bagian Grafik Visual -->
    <div class="col-lg-8 mb-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <h2 class="fw-bold mb-0 text-dark fs-6">
                    <i class="fa-solid fa-chart-line text-primary me-2"></i>Tren Mutasi Persediaan Barang ({{ date('Y') }})
                </h2>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-light">
                    <i class="fa-solid fa-file-lines me-1"></i> Laporan
                </a>
            </div>
            <div class="card-body-app">
                <canvas id="mutasiChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <!-- Aktivitas Terbaru Real dari AuditLog -->
    <div class="col-lg-4 mb-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <h2 class="fw-bold mb-0 text-dark fs-6">
                    <i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Aktivitas Terbaru
                </h2>
                <a href="{{ route('notifications.index') }}" class="text-decoration-none small fw-semibold text-primary">Semua &rarr;</a>
            </div>
            <div class="card-body-app p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentLogs ?? [] as $log)
                        @php
                            $iconBg = match($log->modul) {
                                'Aset' => 'background: var(--color-primary-light); color: var(--color-primary);',
                                'Persediaan' => 'background: var(--color-success-light); color: var(--color-success);',
                                'Opname' => 'background: var(--color-warning-light); color: var(--color-warning);',
                                default => 'background: var(--color-info-light); color: var(--color-info);',
                            };
                            $icon = match($log->modul) {
                                'Aset' => 'fa-boxes-stacked',
                                'Persediaan' => 'fa-box-open',
                                'Opname' => 'fa-clipboard-check',
                                default => 'fa-bell',
                            };
                        @endphp
                        <li class="list-group-item px-3 py-3 border-bottom">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; {{ $iconBg }}">
                                    <i class="fa-solid {{ $icon }}" style="font-size: 13px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 fw-semibold small text-dark">{{ $log->modul }} &bull; {{ $log->aksi }}</p>
                                    <p class="mb-1 text-muted" style="font-size: 11.5px; line-height: 1.4;">{{ $log->detail }}</p>
                                    <small class="text-secondary" style="font-size: 10.5px;">
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
                        borderColor: '#1E40AF',
                        backgroundColor: 'rgba(30, 64, 175, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#1E40AF'
                    }, 
                    {
                        label: 'Barang Keluar (Unit)',
                        data: {!! json_encode($chartKeluar ?? [5, 12, 20, 15, 25, 0]) !!},
                        borderColor: '#D97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#D97706'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Poppins', sans-serif", size: 12 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5] },
                        ticks: { font: { family: "'Poppins', sans-serif" } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Poppins', sans-serif" } }
                    }
                }
            }
        });
    });
</script>
@endsection
