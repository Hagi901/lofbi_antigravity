@extends('layouts.app')

@section('page_title', 'Input File Dokumen')

@section('content')
<!-- Header Halaman -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-file-import fs-5"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0">Input File Laporan / Dokumen</h5>
            </div>
            <p class="text-muted small mb-0 ms-1">
                Cukup masukkan file laporan spreadsheet (.xlsx, .xls, .csv). Aplikasi LOFBI akan <strong>secara otomatis membaca</strong> apakah berkas berupa <strong>Barang Persediaan</strong> atau <strong>Aset Inventarisasi</strong> dan langsung memperbarui data yang sesuai.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('import.template', 'sakti') }}" class="btn btn-light btn-sm text-success fw-bold rounded-pill shadow-sm" style="font-size: 11.5px;">
                <i class="fa-solid fa-file-excel me-1"></i> Template Persediaan
            </a>
            <a href="{{ route('import.template', 'siman') }}" class="btn btn-light btn-sm text-primary fw-bold rounded-pill shadow-sm" style="font-size: 11.5px;">
                <i class="fa-solid fa-file-excel me-1"></i> Template Aset
            </a>
        </div>
    </div>
</div>

<!-- Alert Sukses / Error -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-4 p-3 mb-4">
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

<!-- FORM UPLOAD UTAMA -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('import.auto') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Area Upload File -->
            <div class="border border-2 border-dashed rounded-4 p-4 text-center mb-4 bg-light bg-opacity-50">
                <div class="bg-white rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 58px; height: 58px;">
                    <i class="fa-solid fa-cloud-arrow-up text-primary fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Pilih File Spreadsheet untuk Diunggah</h6>
                <p class="text-muted small mb-3">Mendukung format file <strong>.xlsx, .xls, .csv, .txt</strong> (Maksimal 25 MB)</p>
                
                <div class="col-md-6 mx-auto">
                    <input type="file" name="file_dokumen" class="form-control bg-white border shadow-sm" accept=".xlsx,.xls,.csv,.txt" required>
                </div>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-secondary">
                        <i class="fa-solid fa-location-dot text-primary me-1"></i> Ruangan / Gudang Penempatan Default
                    </label>
                    <select name="ruangan_id" class="form-select form-select-sm bg-light border-0 shadow-sm">
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ str_contains(strtolower($room->nama), 'gudang') ? 'selected' : '' }}>
                                {{ $room->nama }} ({{ $room->gedung }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary fw-bold w-100 rounded-pill shadow-sm py-2" style="font-size: 13.5px;">
                        <i class="fa-solid fa-arrow-up-from-bracket me-1"></i> Unggah & Proses File
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABEL RIWAYAT UPLOAD TERAKHIR -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Riwayat Input Dokumen Terakhir
        </h6>
    </div>
    <div class="card-body p-4 pt-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 12.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold py-2 text-center" width="5%">NO</th>
                        <th class="text-secondary small fw-bold py-2" width="20%">WAKTU INPUT</th>
                        <th class="text-secondary small fw-bold py-2" width="18%">PETUGAS / OPERATOR</th>
                        <th class="text-secondary small fw-bold py-2 text-center" width="15%">JENIS DATA</th>
                        <th class="text-secondary small fw-bold py-2" width="42%">RINCIAN DATA</th>
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
                                @php
                                    $isPersediaan = str_contains(strtolower($log->aksi . ' ' . $log->detail), 'persediaan');
                                @endphp
                                <span class="badge {{ $isPersediaan ? 'bg-success-subtle text-success border-success' : 'bg-primary-subtle text-primary border-primary' }} border px-2 py-1">
                                    {{ $isPersediaan ? 'Barang Persediaan' : 'Aset Inventarisasi' }}
                                </span>
                            </td>
                            <td class="text-muted">
                                {{ $log->detail }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat aktivitas input dokumen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
