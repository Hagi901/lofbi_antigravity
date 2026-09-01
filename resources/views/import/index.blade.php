@extends('layouts.app')

@section('page_title', 'Pusat Sinkronisasi Cerdas SIMAN & SAKTI')

@section('content')
<!-- Header Halaman -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-wand-magic-sparkles fs-5"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0">Satu Pintu Sinkronisasi Otomatis (SIMAN & SAKTI)</h5>
            </div>
            <p class="text-muted small mb-0 ms-1">
                Unggah file laporan atau ekspor spreadsheet apa saja. Sistem LOFBI akan <strong>secara otomatis mendeteksi</strong> apakah dokumen merupakan <strong>Aset Tetap (SIMAN)</strong> atau <strong>Persediaan (SAKTI)</strong> dan langsung memperbarui database terkait.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill font-monospace" style="font-size: 11px;">
                <i class="fa-solid fa-circle-check me-1"></i> Smart Auto-Detect Engine Ready
            </span>
        </div>
    </div>
</div>

<!-- Alert Sukses / Error -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-4 p-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
            <div>
                <strong>Sukses!</strong> {{ session('success') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 small rounded-4 p-3 mb-4">
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
    <!-- PANEL UTAMA: FORM UPLOAD CERDAS -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Unggah Berkas Laporan / Ekspor
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('import.template', 'sakti') }}" class="btn btn-light btn-sm text-success fw-bold rounded-pill shadow-sm" style="font-size: 11px;" title="Unduh Template Format Persediaan">
                        <i class="fa-solid fa-file-excel me-1"></i> Template SAKTI
                    </a>
                    <a href="{{ route('import.template', 'siman') }}" class="btn btn-light btn-sm text-primary fw-bold rounded-pill shadow-sm" style="font-size: 11px;" title="Unduh Template Format Aset">
                        <i class="fa-solid fa-file-excel me-1"></i> Template SIMAN
                    </a>
                </div>
            </div>

            <div class="card-body p-4 pt-2">
                <form action="{{ route('import.auto') }}" method="POST" enctype="multipart/form-data" id="smartUploadForm">
                    @csrf

                    <!-- Dropzone Area -->
                    <div class="border border-2 border-dashed rounded-4 p-4 text-center mb-3 bg-light bg-opacity-50" id="dropzoneBox">
                        <div class="bg-white rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-file-import text-primary fs-3"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Pilih atau Tarik File Spreadsheet ke Sini</h6>
                        <p class="text-muted small mb-3">Mendukung format <strong>.xlsx, .xls, .csv, .txt</strong> (Maksimal 25 MB)</p>
                        
                        <div class="col-md-8 mx-auto">
                            <input type="file" name="file_dokumen" id="fileDokumen" class="form-control bg-white border shadow-sm" accept=".xlsx,.xls,.csv,.txt" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Mode Pemrosesan Dokumen</label>
                            <select name="jenis_dokumen" class="form-select form-select-sm bg-light border-0 shadow-sm">
                                <option value="auto" selected>✨ Otomatis Deteksi Dokumen (Direkomendasikan)</option>
                                <option value="sakti">📦 Paksa Sebagai Persediaan (SAKTI)</option>
                                <option value="siman">🏛️ Paksa Sebagai Aset Tetap (SIMAN)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Ruangan / Gudang Penempatan Default</label>
                            <select name="ruangan_id" class="form-select form-select-sm bg-light border-0 shadow-sm">
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ str_contains(strtolower($room->nama), 'gudang') ? 'selected' : '' }}>
                                        {{ $room->nama }} ({{ $room->gedung }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold w-100 rounded-pill shadow-sm py-2" style="font-size: 14px;">
                        <i class="fa-solid fa-bolt me-2"></i>Mulai Sinkronisasi Otomatis
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- PANEL KANAN: CARA KERJA & PANDUAN OTOMATIS -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-circle-question text-info me-2"></i>Cara Kerja Deteksi Otomatis
                </h6>
            </div>
            <div class="card-body p-4 pt-1">
                <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border mb-3">
                    <span class="badge bg-success text-white p-2 rounded-circle">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 12.5px;">Jika Berkas Berisi Persediaan (SAKTI)</h6>
                        <p class="small text-muted mb-0" style="font-size: 11px; line-height: 1.5;">
                            Sistem mengenali kata kunci seperti <em>Satuan</em>, <em>Saldo Stok</em>, <em>Harga Satuan</em>, atau kodefikasi `1.01...`. Data persediaan & batch FIFO di LOFBI langsung diperbarui.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border mb-3">
                    <span class="badge bg-primary text-white p-2 rounded-circle">
                        <i class="fa-solid fa-landmark"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 12.5px;">Jika Berkas Berisi Aset Tetap (SIMAN)</h6>
                        <p class="small text-muted mb-0" style="font-size: 11px; line-height: 1.5;">
                            Sistem mengenali kolom <em>NUP</em>, <em>Kodefikasi BMN</em> (`3.05...`), <em>Masa Manfaat</em>, atau <em>Nilai Perolehan</em>. Data aset dan kalkulasi penyusutan semesteran langsung dibuat.
                        </p>
                    </div>
                </div>

                <div class="alert alert-light border small rounded-3 p-2 mb-0 text-center" style="font-size: 11px;">
                    <i class="fa-solid fa-shield-halved text-primary me-1"></i> Data yang sudah ada tidak akan terduplikasi (*update or create* otomatis).
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL RIWAYAT SINKRONISASI -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Riwayat Aktivitas Sinkronisasi Terakhir
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
