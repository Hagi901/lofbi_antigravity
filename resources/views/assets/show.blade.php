@extends('layouts.app')

@section('page_title', 'Detail & Penyusutan Aset - ' . ($asset->asset_code ?? $asset->kode_aset))

@section('content')
<!-- Header & Navigasi -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('assets.index') }}" class="btn btn-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;" title="Kembali ke Daftar Aset">
                <i class="fa-solid fa-arrow-left text-secondary"></i>
            </a>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">{{ $asset->name ?? $asset->jenisBarang?->nama_generik ?? 'Aset #' . $asset->id }}</h6>
                    <span class="badge bg-white text-dark border shadow-sm px-2 py-1" style="font-size: 11px;">
                        {{ $asset->asset_code ?? $asset->kode_aset ?? 'AST-000' }}
                    </span>
                    <span class="badge bg-light text-primary border" style="font-size: 10px;">
                        NUP {{ $asset->nup ?? 1 }}
                    </span>
                </div>
                <small class="text-muted" style="font-size: 11px;">
                    Kategori: {{ $asset->jenisBarang?->kategori?->nama ?? $asset->category->name ?? 'Aset BMN' }}
                    @if($asset->kode_bmn) &bull; <span class="font-monospace">BMN: {{ $asset->kode_bmn }}</span> @endif
                </small>
            </div>
        </div>

        <!-- Tombol Aksi Navigasi -->
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-light btn-sm fw-bold rounded-pill px-3 shadow-sm" title="Cetak Informasi Aset">
                <i class="fa-solid fa-print me-1 text-secondary"></i> Cetak
            </button>

            @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Aset
                </a>
            @endif

            @if((Auth::user()->role ?? '') === 'admin')
                <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data aset ini secara permanen?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm fw-bold rounded-pill px-3 shadow-sm">
                        <i class="fa-solid fa-trash me-1"></i> Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Panel Kiri: Identitas & Spesifikasi Aset -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="text-center pb-3 border-bottom">
                    <!-- Icon Aset -->
                    <div class="bg-primary bg-opacity-10 rounded-3 d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-laptop text-primary fs-2"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">{{ $asset->name }}</h6>
                    @php
                        $kondisiClass = 'bg-success-subtle text-success border-success';
                        if(($asset->condition ?? $asset->kondisi ?? '') === 'Rusak Ringan') $kondisiClass = 'bg-warning-subtle text-warning border-warning';
                        if(($asset->condition ?? $asset->kondisi ?? '') === 'Rusak Berat')  $kondisiClass = 'bg-danger-subtle text-danger border-danger';
                        $kondisiLabel = $asset->condition ?? $asset->kondisi ?? 'Baik';
                    @endphp
                    <span class="badge {{ $kondisiClass }} border px-3 py-1 rounded-pill" style="font-size: 11px;">
                        <i class="fa-solid fa-circle-check me-1"></i> Kondisi: {{ $kondisiLabel }}
                    </span>
                </div>

                <!-- Spesifikasi Detail -->
                <div class="py-3 border-bottom">
                    <span class="text-secondary small fw-bold text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px;">Spesifikasi & Lokasi</span>
                    <ul class="list-unstyled mb-0 mt-2" style="font-size: 12px; line-height: 1.8;">
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Ruangan:</span>
                            <span class="fw-semibold text-dark text-end">{{ $asset->room->name ?? $asset->ruangan?->nama ?? 'Belum Dialokasikan' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Penanggung Jawab:</span>
                            <span class="fw-semibold text-dark text-end">{{ $asset->penanggung_jawab ?: '-' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">No. Seri (S/N):</span>
                            <span class="font-monospace text-dark text-end">{{ $asset->no_seri ?: '-' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Tgl Perolehan:</span>
                            <span class="fw-semibold text-dark text-end">{{ $asset->tanggal_perolehan ? \Carbon\Carbon::parse($asset->tanggal_perolehan)->format('d M Y') : '-' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Masa Manfaat:</span>
                            <span class="fw-bold text-primary text-end">{{ $asset->useful_life_years ?? $asset->masa_manfaat ?? 5 }} Thn ({{ ($asset->useful_life_years ?? $asset->masa_manfaat ?? 5) * 2 }} Sem)</span>
                        </li>
                    </ul>
                </div>

                <!-- Progres Depresiasi SIMAN -->
                @php
                    $susutData      = $asset->hitungPenyusutanGarisLurus();
                    $persenSusut    = $susutData['persen_susut'];
                    $semBerjalan    = $susutData['semester_berjalan'];
                    $totSem         = $susutData['total_semester'];
                @endphp
                <div class="pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11.5px;">
                        <span class="fw-bold text-muted">Progres Penyusutan</span>
                        <span class="fw-bold text-primary">{{ $persenSusut }}%</span>
                    </div>
                    <div class="progress shadow-sm mb-2" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $persenSusut }}%;" aria-valuenow="{{ $persenSusut }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted" style="font-size: 10.5px;">
                        <span>Semester {{ $semBerjalan }} dari {{ $totSem }}</span>
                        <span>Metode: Garis Lurus SIMAN</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Panel Kanan: Ringkasan Finansial & Jadwal Penyusutan SIMAN -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-2 border-bottom-0 pt-3 px-3">
                <ul class="nav nav-tabs card-header-tabs" id="assetTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-primary py-2 px-3" id="depresiasi-tab" data-bs-toggle="tab" data-bs-target="#depresiasi" type="button" role="tab" style="font-size: 12.5px;">
                            <i class="fa-solid fa-table-list me-1"></i> Jadwal Penyusutan SIMAN
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-secondary py-2 px-3" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat" type="button" role="tab" style="font-size: 12.5px;">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Riwayat Mutasi
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-3 tab-content" id="assetTabContent">
                <!-- Tab Jadwal Penyusutan -->
                <div class="tab-pane fade show active" id="depresiasi" role="tabpanel">
                    
                    <!-- 4 Kartu Metrik Keuangan Ringkas -->
                    @php
                        $nilaiBukuKini  = $susutData['nilai_buku'];
                        $akumKini       = $susutData['akumulasi'];
                        $susutPSemester = $susutData['susut_per_semester'];
                        $susutPTahun    = $susutData['susut_per_tahun'];
                    @endphp
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="bg-light rounded-3 p-2 text-center border">
                                <span class="text-muted d-block" style="font-size: 10px; font-weight: 600;">NILAI PEROLEHAN</span>
                                <span class="fw-bold text-dark" style="font-size: 12.5px;">Rp {{ number_format($asset->nilai_perolehan ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light rounded-3 p-2 text-center border">
                                <span class="text-muted d-block" style="font-size: 10px; font-weight: 600;">BEBAN / SEMESTER</span>
                                <span class="fw-bold text-danger" style="font-size: 12.5px;">Rp {{ number_format($susutPSemester, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light rounded-3 p-2 text-center border">
                                <span class="text-muted d-block" style="font-size: 10px; font-weight: 600;">AKUMULASI SUSUT</span>
                                <span class="fw-bold text-warning" style="font-size: 12.5px;">Rp {{ number_format($akumKini, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light rounded-3 p-2 text-center border border-primary border-opacity-25">
                                <span class="text-primary d-block" style="font-size: 10px; font-weight: 600;">NILAI BUKU KINI</span>
                                <span class="fw-bold text-primary" style="font-size: 13px;">Rp {{ number_format($nilaiBukuKini, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Proyeksi Penyusutan Per Semester -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-secondary" style="font-size: 11.5px;">
                            <i class="fa-solid fa-calendar-days text-primary me-1"></i>Proyeksi Penyusutan ({{ $totSem }} Semester / {{ $asset->useful_life_years ?? $asset->masa_manfaat ?? 5 }} Tahun)
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 9.5px;">
                            Lock Constraint: Rp 1
                        </span>
                    </div>

                    <div class="table-responsive border rounded-3 shadow-sm" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-secondary small fw-bold text-center py-2" width="14%">SEMESTER</th>
                                    <th class="text-secondary small fw-bold py-2" width="16%">PERIODE</th>
                                    <th class="text-secondary small fw-bold text-end py-2" width="22%">BEBAN SUSUT</th>
                                    <th class="text-secondary small fw-bold text-end py-2" width="22%">AKUMULASI</th>
                                    <th class="text-secondary small fw-bold text-end py-2" width="22%">NILAI BUKU</th>
                                    <th class="text-secondary small fw-bold text-center py-2" width="4%">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $tglAwal       = $asset->tanggal_perolehan
                                        ? \Carbon\Carbon::parse($asset->tanggal_perolehan)
                                        : \Carbon\Carbon::now();
                                    $umur          = (int) ($asset->useful_life_years ?? $asset->masa_manfaat ?: 5);
                                    $nilaiAwal     = (float) ($asset->nilai_perolehan ?: 0);
                                    $totSem        = $umur * 2;
                                    $bebanSem      = $totSem > 0 ? $nilaiAwal / $totSem : 0;
                                    $semKiniAktif  = $susutData['semester_berjalan'];
                                @endphp

                                {{-- Baris 0: Perolehan Awal --}}
                                <tr>
                                    <td class="text-center fw-bold text-muted py-1">0</td>
                                    <td class="text-muted small py-1">{{ $tglAwal->format('d/m/Y') }}</td>
                                    <td class="text-end text-muted py-1">-</td>
                                    <td class="text-end text-muted py-1">-</td>
                                    <td class="text-end fw-bold text-dark py-1">Rp {{ number_format($nilaiAwal, 0, ',', '.') }}</td>
                                    <td class="text-center py-1"><span class="badge bg-secondary-subtle text-secondary border" style="font-size: 8.5px;">Awal</span></td>
                                </tr>

                                @for($s = 1; $s <= $totSem; $s++)
                                    @php
                                        $akumS   = min($bebanSem * $s, $nilaiAwal - 1);
                                        $sisaS   = max($nilaiAwal - $akumS, 1);
                                        $isHabis = ($s == $totSem);

                                        $tglSem  = $tglAwal->copy()->addMonths($s * 6);
                                        $label   = 'S' . (($s % 2 == 1) ? '1' : '2') . ' ' . $tglSem->format('Y');

                                        $isKini  = ($s == $semKiniAktif);
                                        $isPast  = ($s < $semKiniAktif);
                                    @endphp
                                    <tr class="{{ $isKini ? 'table-primary fw-semibold' : '' }}">
                                        <td class="text-center py-1">
                                            <span class="fw-bold">{{ $s }}</span>
                                            @if($isKini)
                                                <span class="badge bg-primary ms-1" style="font-size: 8.5px;">Kini</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted py-1">{{ $label }}</td>
                                        <td class="text-end text-danger py-1">Rp {{ number_format($bebanSem, 0, ',', '.') }}</td>
                                        <td class="text-end text-muted py-1">Rp {{ number_format($akumS, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold py-1 {{ $isHabis ? 'text-danger' : ($isKini ? 'text-primary' : 'text-dark') }}">
                                            Rp {{ number_format($sisaS, 0, ',', '.') }}
                                            @if($isHabis)
                                                <span class="badge bg-danger-subtle text-danger border ms-1" style="font-size: 8px;">Lock Rp 1</span>
                                            @endif
                                        </td>
                                        <td class="text-center py-1">
                                            @if($isKini)
                                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 8.5px;">Aktif</span>
                                            @elseif($isHabis)
                                                <span class="badge bg-danger-subtle text-danger border" style="font-size: 8.5px;">Habis</span>
                                            @elseif($isPast)
                                                <span class="badge bg-light text-muted border" style="font-size: 8.5px;">Lewat</span>
                                            @else
                                                <span class="badge bg-light text-muted border" style="font-size: 8.5px;">Proyeksi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- Tab Riwayat Mutasi -->
                <div class="tab-pane fade" id="riwayat" role="tabpanel">
                    <div class="p-2">
                        <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border mb-2">
                            <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark" style="font-size: 13px;">Pendaftaran Perdana Aset</h6>
                                <p class="small text-muted mb-1" style="font-size: 11.5px;">
                                    Aset didaftarkan pada sistem LOFBI di lokasi <strong>{{ $asset->room->name ?? $asset->ruangan?->nama ?? 'Gudang Utama' }}</strong>.
                                </p>
                                <small class="text-secondary" style="font-size: 10.5px;">
                                    <i class="fa-regular fa-clock me-1"></i>{{ $asset->created_at ? $asset->created_at->format('d M Y, H:i') : '-' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection