@extends('layouts.app')

@section('page_title', 'Detail Informasi & Penyusutan Aset')

@section('content')
<div class="row">
    <!-- Panel Kiri: Identitas & Status Umur Aset -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 text-center">
                <div class="d-flex justify-content-between mb-3">
                    <a href="{{ route('assets.index') }}" class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <button class="btn btn-outline-primary btn-sm fw-bold rounded-pill shadow-sm px-3">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Aset
                    </button>
                </div>

                <!-- Ikon Aset -->
                <div class="bg-primary bg-opacity-10 rounded-4 d-flex align-items-center justify-content-center mx-auto mb-4 mt-2" style="width: 120px; height: 120px;">
                    <i class="fa-solid fa-laptop text-primary" style="font-size: 4rem;"></i>
                </div>

                <h5 class="fw-bold text-dark mb-1">Laptop Asus ROG</h5>
                <p class="text-muted small mb-3">Elektronik - LAP-001</p>
                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill mb-4"><i class="fa-solid fa-check me-1"></i> Kondisi Baik</span>

                <!-- Bagian Baru: Indikator Umur Ekonomis -->
                <div class="border-top pt-4 text-start">
                    <h6 class="fw-bold text-secondary mb-3 small"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Status Nilai & Umur Aset</h6>
                    <div class="mb-2 d-flex justify-content-between small">
                        <span class="fw-bold text-muted">Sisa Umur Ekonomis</span>
                        <span class="fw-bold text-primary">100% (5 Tahun)</span>
                    </div>
                    <div class="progress shadow-sm" style="height: 12px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="small text-muted mt-3 mb-0" style="line-height: 1.5;">Aset ini baru didaftarkan. Nilai buku saat ini masih utuh dan belum mengalami pemotongan beban penyusutan tahunan.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Kanan: Spesifikasi Lengkap & Tabel Depresiasi -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0 pt-4 px-4">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-primary" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Detail & Penyusutan</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-secondary" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">Riwayat Mutasi</button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4 tab-content" id="myTabContent">
                <!-- Tab Detail & Penyusutan -->
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="small text-muted mb-1 fw-bold">Lokasi Penempatan Saat Ini</p>
                            <h6 class="fw-bold text-dark"><i class="fa-solid fa-location-dot text-danger me-2"></i>Ruang Server / IT</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="small text-muted mb-1 fw-bold">Tanggal Perolehan</p>
                            <h6 class="fw-bold text-dark"><i class="fa-regular fa-calendar-check text-success me-2"></i>15 Agustus 2026</h6>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary mb-3 mt-2 border-bottom pb-2">Informasi Keuangan (Metode Garis Lurus)</h6>
                    <div class="row bg-light rounded-3 p-3 mb-4 mx-0 shadow-sm">
                        <div class="col-md-3 text-center border-end mb-3 mb-md-0">
                            <p class="small text-muted mb-1 fw-bold">Nilai Perolehan</p>
                            <h6 class="fw-bold text-dark mb-0">Rp 15.000.000</h6>
                        </div>
                        <div class="col-md-3 text-center border-end mb-3 mb-md-0">
                            <p class="small text-muted mb-1 fw-bold">Umur Ekonomis</p>
                            <h6 class="fw-bold text-dark mb-0">5 Tahun</h6>
                        </div>
                        <div class="col-md-3 text-center border-end mb-3 mb-md-0">
                            <p class="small text-muted mb-1 fw-bold">Beban Susut / Thn</p>
                            <h6 class="fw-bold text-danger mb-0">Rp 3.000.000</h6>
                        </div>
                        <div class="col-md-3 text-center">
                            <p class="small text-muted mb-1 fw-bold">Nilai Buku Terkini</p>
                            <h6 class="fw-bold text-primary mb-0">Rp 15.000.000</h6>
                        </div>
                    </div>

                    <!-- Tabel Proyeksi Penyusutan -->
                    <h6 class="fw-bold text-secondary mb-3 small"><i class="fa-solid fa-table-list text-secondary me-2"></i>Tabel Proyeksi Penyusutan (5 Tahun)</h6>
                    <div class="table-responsive border rounded-3 shadow-sm">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary small fw-bold text-center py-2">TAHUN KE-</th>
                                    <th class="text-secondary small fw-bold py-2">BEBAN PENYUSUTAN</th>
                                    <th class="text-secondary small fw-bold py-2">AKUMULASI PENYUSUTAN</th>
                                    <th class="text-secondary small fw-bold text-end py-2">NILAI BUKU TERSISA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center fw-bold bg-primary bg-opacity-10">0 (2026)</td>
                                    <td class="bg-primary bg-opacity-10">-</td>
                                    <td class="bg-primary bg-opacity-10">-</td>
                                    <td class="text-end fw-bold text-primary bg-primary bg-opacity-10">Rp 15.000.000</td>
                                </tr>
                                <tr>
                                    <td class="text-center text-muted">1 (2027)</td>
                                    <td class="text-danger small">Rp 3.000.000</td>
                                    <td class="text-muted small">Rp 3.000.000</td>
                                    <td class="text-end fw-bold text-dark">Rp 12.000.000</td>
                                </tr>
                                <tr>
                                    <td class="text-center text-muted">2 (2028)</td>
                                    <td class="text-danger small">Rp 3.000.000</td>
                                    <td class="text-muted small">Rp 6.000.000</td>
                                    <td class="text-end fw-bold text-dark">Rp 9.000.000</td>
                                </tr>
                                <tr>
                                    <td class="text-center text-muted">3 (2029)</td>
                                    <td class="text-danger small">Rp 3.000.000</td>
                                    <td class="text-muted small">Rp 9.000.000</td>
                                    <td class="text-end fw-bold text-dark">Rp 6.000.000</td>
                                </tr>
                                <tr>
                                    <td class="text-center text-muted">4 (2030)</td>
                                    <td class="text-danger small">Rp 3.000.000</td>
                                    <td class="text-muted small">Rp 12.000.000</td>
                                    <td class="text-end fw-bold text-dark">Rp 3.000.000</td>
                                </tr>
                                <tr>
                                    <td class="text-center text-muted">5 (2031)</td>
                                    <td class="text-danger small">Rp 3.000.000</td>
                                    <td class="text-muted small">Rp 15.000.000</td>
                                    <td class="text-end fw-bold text-danger">Rp 0 (Habis)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Riwayat Mutasi -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 py-3 border-light">
                            <div class="d-flex align-items-start">
                                <div class="bg-success-subtle text-success rounded-circle p-2 me-3"><i class="fa-solid fa-download"></i></div>
                                <div>
                                    <p class="mb-0 fw-bold small text-dark">Aset Didaftarkan ke Sistem</p>
                                    <p class="mb-1 text-muted" style="font-size: 13px;">Oleh: M. Rivaldo Firdaus (Operator Sistem)</p>
                                    <small class="text-secondary" style="font-size: 11px;">15 Agustus 2026, 10:00 WIB</small>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-tabs .nav-link { border: none; border-bottom: 3px solid transparent; color: #6c757d; }
    .nav-tabs .nav-link.active { border-color: #0d6efd; color: #0d6efd !important; background: transparent; }
    .nav-tabs .nav-link:hover { border-color: #e9ecef; }
</style>
@endsection