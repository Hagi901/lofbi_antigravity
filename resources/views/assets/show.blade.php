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
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-outline-primary btn-sm fw-bold rounded-pill shadow-sm px-3">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Aset
                    </a>
                </div>

                <!-- Ikon Aset -->
                <div class="bg-primary bg-opacity-10 rounded-4 d-flex align-items-center justify-content-center mx-auto mb-4 mt-2" style="width: 120px; height: 120px;">
                    <i class="fa-solid fa-laptop text-primary" style="font-size: 4rem;"></i>
                </div>

                <h5 class="fw-bold text-dark mb-1">{{ $asset->name }}</h5>
                <p class="text-muted small mb-3">{{ $asset->category->name ?? 'Aset' }} — <span class="fw-bold text-primary">{{ $asset->asset_code }}</span></p>
                <span class="badge {{ $asset->kondisi === 'baik' ? 'bg-success-subtle text-success border border-success' : ($asset->kondisi === 'rusak_ringan' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-danger-subtle text-danger border border-danger') }} px-3 py-2 rounded-pill mb-4">
                    <i class="fa-solid fa-circle-info me-1"></i> Kondisi: {{ $asset->condition }}
                </span>

                <!-- Indikator Umur Ekonomis -->
                <div class="border-top pt-4 text-start">
                    <h6 class="fw-bold text-secondary mb-3 small"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Status Nilai & Umur Aset</h6>
                    <div class="mb-2 d-flex justify-content-between small">
                        <span class="fw-bold text-muted">Masa Manfaat</span>
                        <span class="fw-bold text-primary">{{ $asset->useful_life_years ?? 5 }} Tahun</span>
                    </div>
                    <div class="progress shadow-sm" style="height: 12px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="small text-muted mt-3 mb-0" style="line-height: 1.5;">Metode perhitungan depresiasi: <strong>{{ $asset->metode_penyusutan ?? 'Garis Lurus' }}</strong> (Kemenhub BMN Standard).</p>
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
                            <h6 class="fw-bold text-dark"><i class="fa-solid fa-location-dot text-danger me-2"></i>{{ $asset->room->name ?? 'Gudang Utama' }}</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="small text-muted mb-1 fw-bold">Tanggal Perolehan</p>
                            <h6 class="fw-bold text-dark"><i class="fa-regular fa-calendar-check text-success me-2"></i>{{ $asset->tanggal_perolehan ? \Carbon\Carbon::parse($asset->tanggal_perolehan)->format('d F Y') : '-' }}</h6>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary mb-3 mt-2 border-bottom pb-2">Informasi Keuangan (Metode Garis Lurus)</h6>
                    <div class="row bg-light rounded-3 p-3 mb-4 mx-0 shadow-sm">
                        <div class="col-md-3 text-center border-end mb-3 mb-md-0">
                            <p class="small text-muted mb-1 fw-bold">Nilai Perolehan</p>
                            <h6 class="fw-bold text-dark mb-0">Rp {{ number_format($asset->acquisition_value ?? 0, 0, ',', '.') }}</h6>
                        </div>
                        <div class="col-md-3 text-center border-end mb-3 mb-md-0">
                            <p class="small text-muted mb-1 fw-bold">Umur Manfaat</p>
                            <h6 class="fw-bold text-dark mb-0">{{ $asset->useful_life_years ?? 5 }} Tahun</h6>
                        </div>
                        <div class="col-md-3 text-center border-end mb-3 mb-md-0">
                            <p class="small text-muted mb-1 fw-bold">Beban Susut / Thn</p>
                            <h6 class="fw-bold text-danger mb-0">Rp {{ number_format(($asset->useful_life_years > 0 ? $asset->acquisition_value / $asset->useful_life_years : 0), 0, ',', '.') }}</h6>
                        </div>
                        <div class="col-md-3 text-center">
                            <p class="small text-muted mb-1 fw-bold">Nilai Buku Terkini</p>
                            <h6 class="fw-bold text-primary mb-0">Rp {{ number_format($asset->book_value ?? 0, 0, ',', '.') }}</h6>
                        </div>
                    </div>

                    <!-- Tabel Proyeksi Penyusutan -->
                    <h6 class="fw-bold text-secondary mb-3 small"><i class="fa-solid fa-table-list text-secondary me-2"></i>Tabel Proyeksi Penyusutan ({{ $asset->useful_life_years ?? 5 }} Tahun)</h6>
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
                                @php
                                    $tahunPerolehan = $asset->tanggal_perolehan ? (int)date('Y', strtotime($asset->tanggal_perolehan)) : (int)date('Y');
                                    $umur = (int) ($asset->useful_life_years ?: 5);
                                    $nilaiAwal = (float) ($asset->acquisition_value ?: 0);
                                    $susutPerTahun = $umur > 0 ? $nilaiAwal / $umur : 0;
                                @endphp
                                @for($i = 0; $i <= $umur; $i++)
                                    @php
                                        $beban = $i == 0 ? 0 : $susutPerTahun;
                                        $akumulasi = $susutPerTahun * $i;
                                        $sisa = max(0, $nilaiAwal - $akumulasi);
                                    @endphp
                                    <tr class="{{ $i == 0 ? 'bg-primary bg-opacity-10' : '' }}">
                                        <td class="text-center {{ $i == 0 ? 'fw-bold bg-primary bg-opacity-10' : 'text-muted' }}">{{ $i }} ({{ $tahunPerolehan + $i }})</td>
                                        <td class="{{ $i == 0 ? 'bg-primary bg-opacity-10' : 'text-danger small' }}">{{ $i == 0 ? '-' : 'Rp ' . number_format($beban, 0, ',', '.') }}</td>
                                        <td class="{{ $i == 0 ? 'bg-primary bg-opacity-10' : 'text-muted small' }}">{{ $i == 0 ? '-' : 'Rp ' . number_format($akumulasi, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold {{ $i == 0 ? 'text-primary bg-primary bg-opacity-10' : 'text-dark' }}">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Riwayat Mutasi -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <div class="timeline p-3">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;"><i class="fa-solid fa-plus"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Pendaftaran Perdana Aset</h6>
                                <p class="small text-muted mb-0">Dicatat pada sistem LOFBI di lokasi {{ $asset->room->name ?? 'Gudang Utama' }}.</p>
                                <small class="text-secondary">{{ $asset->created_at ? $asset->created_at->diffForHumans() : 'Baru saja' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection