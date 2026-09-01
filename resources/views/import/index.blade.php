@extends('layouts.app')

@section('page_title', 'Pusat Sinkronisasi Data SIMAN & SAKTI')

@section('content')
<!-- Header Halaman -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-cloud-arrow-up fs-5"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0">Integrasi & Sinkronisasi Dokumen SIMAN / SAKTI</h5>
            </div>
            <p class="text-muted small mb-0 ms-1">
                Unggah laporan ekspor resmi dari aplikasi <strong>SIMAN (Aset Tetap)</strong> atau <strong>SAKTI (Persediaan)</strong> untuk memperbarui data inventarisasi secara otomatis.
            </p>
        </div>
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill font-monospace" style="font-size: 11px;">
                <i class="fa-solid fa-bolt me-1"></i> Auto-Parser Engine Active
            </span>
        </div>
    </div>
</div>

<!-- Alert Sukses / Error -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-3 p-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
            <div>
                <strong>Berhasil!</strong> {{ session('success') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 small rounded-3 p-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 text-danger"></i>
            <div>
                <strong>Gagal!</strong> {{ session('error') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <!-- KARTU 1: IMPORT SAKTI (PERSEDIAAN) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success p-2 rounded-circle">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">1. Sinkronisasi Persediaan (SAKTI)</h6>
                        <small class="text-muted" style="font-size: 11px;">Laporan Rincian Buku Persediaan / Posisi Neraca</small>
                    </div>
                </div>
                <a href="{{ route('import.template', 'sakti') }}" class="btn btn-light btn-sm text-success fw-bold rounded-pill shadow-sm" style="font-size: 11px;">
                    <i class="fa-solid fa-file-excel me-1"></i> Unduh Template
                </a>
            </div>
            
            <div class="card-body p-4 pt-2">
                <div class="alert alert-light border small rounded-3 p-3 mb-3" style="font-size: 11.5px; line-height: 1.6;">
                    <i class="fa-solid fa-circle-info text-success me-1"></i> <strong>Informasi SAKTI:</strong>
                    Sistem akan mengekstrak <em>Kode Barang</em>, <em>Nama Barang</em>, <em>Satuan</em>, <em>Saldo Stok</em>, <em>Harga Satuan</em>, dan otomatis membuat batch saldo awal FIFO di kartu persediaan LOFBI.
                </div>

                <form action="{{ route('import.sakti') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Pilih File Laporan SAKTI (.xlsx, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file_sakti" class="form-control form-control-sm bg-light border-0 shadow-sm" accept=".xlsx,.xls,.csv,.txt" required>
                        <small class="text-muted" style="font-size: 10.5px;">Mendukung format spreadsheet ekspor langsung dari aplikasi SAKTI Kemenkeu.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Lokasi Gudang Penempatan</label>
                        <select name="ruangan_id" class="form-select form-select-sm bg-light border-0 shadow-sm">
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ str_contains(strtolower($room->nama), 'gudang') ? 'selected' : '' }}>
                                    {{ $room->nama }} ({{ $room->gedung }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success fw-bold w-100 rounded-pill shadow-sm py-2" style="font-size: 13px;">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Mulai Sinkronisasi Persediaan SAKTI
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KARTU 2: IMPORT SIMAN (ASET TETAP) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary p-2 rounded-circle">
                        <i class="fa-solid fa-landmark"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">2. Sinkronisasi Aset Tetap (SIMAN)</h6>
                        <small class="text-muted" style="font-size: 11px;">Master Aset BMN DJKN Kementerian Keuangan</small>
                    </div>
                </div>
                <a href="{{ route('import.template', 'siman') }}" class="btn btn-light btn-sm text-primary fw-bold rounded-pill shadow-sm" style="font-size: 11px;">
                    <i class="fa-solid fa-file-excel me-1"></i> Unduh Template
                </a>
            </div>
            
            <div class="card-body p-4 pt-2">
                <div class="alert alert-light border small rounded-3 p-3 mb-3" style="font-size: 11.5px; line-height: 1.6;">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i> <strong>Informasi SIMAN:</strong>
                    Sistem akan membaca <em>Kode Aset</em>, <em>Kodefikasi BMN 10-digit</em>, <em>NUP</em>, <em>Kondisi</em>, <em>Nilai Perolehan</em>, <em>Masa Manfaat</em>, dan langsung mengkalkulasi penyusutan garis lurus semesteran (Floor Rp 1).
                </div>

                <form action="{{ route('import.siman') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Pilih File Laporan SIMAN (.xlsx, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file_siman" class="form-control form-control-sm bg-light border-0 shadow-sm" accept=".xlsx,.xls,.csv,.txt" required>
                        <small class="text-muted" style="font-size: 10.5px;">Mendukung format spreadsheet ekspor Master Aset BMN / SIMAN.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Ruangan Penempatan Default</label>
                        <select name="ruangan_id" class="form-select form-select-sm bg-light border-0 shadow-sm">
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->nama }} ({{ $room->gedung }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold w-100 rounded-pill shadow-sm py-2" style="font-size: 13px;">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Mulai Sinkronisasi Aset Tetap SIMAN
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- TABEL RIWAYAT SINKRONISASI -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Riwayat Aktivitas Sinkronisasi Dokumen Terakhir
        </h6>
    </div>
    <div class="card-body p-4 pt-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 12.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold py-2 text-center" width="5%">NO</th>
                        <th class="text-secondary small fw-bold py-2" width="20%">WAKTU SINKRONISASI</th>
                        <th class="text-secondary small fw-bold py-2" width="18%">PETUGAS / OPERATOR</th>
                        <th class="text-secondary small fw-bold py-2 text-center" width="15%">JENIS AKSI</th>
                        <th class="text-secondary small fw-bold py-2" width="42%">RINCIAN AKTIVITAS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentImports as $idx => $log)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                            <td>
                                <i class="fa-regular fa-calendar-check text-primary me-1"></i>
                                {{ $log->created_at ? $log->created_at->format('d M Y, H:i') : '-' }} WIB
                            </td>
                            <td class="fw-semibold text-dark">
                                <i class="fa-solid fa-user-circle text-secondary me-1"></i>
                                {{ $log->user_name }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ str_contains(strtolower($log->aksi), 'sakti') ? 'bg-success-subtle text-success border-success' : 'bg-primary-subtle text-primary border-primary' }} border px-2 py-1">
                                    {{ $log->aksi }}
                                </span>
                            </td>
                            <td class="text-muted">
                                {{ $log->detail }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat aktivitas sinkronisasi dokumen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
