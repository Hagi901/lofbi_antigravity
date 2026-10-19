{{-- Reading this as: upload staging dashboard for government logistics operators and validators, with a clean maritime administrative language, leaning toward Bootstrap 5 utilities + Poppins + restrained motion. --}}
{{-- DESIGN_VARIANCE: 4 | MOTION_INTENSITY: 3 | VISUAL_DENSITY: 5 --}}
@extends('layouts.app')

@section('page_title', 'Upload Data & Validasi')

@section('content')
<!-- Header Halaman -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-cloud-arrow-up fs-5"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0">Upload & Validasi Data</h5>
            </div>
            <p class="text-muted small mb-0 ms-1 max-w-[65ch]">
                Seluruh data persediaan SAKTI masuk melalui modul ini ke antrean staging, lalu diperiksa dan disetujui validator sebelum resmi masuk ke inventaris.
            </p>
        </div>
        <div class="d-flex gap-2">
            @if(in_array(auth()->user()->role, ['admin', 'operator']))
                <a href="#formUpload" class="btn btn-primary btn-sm fw-bold rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-plus me-1"></i> Upload File
                </a>
            @endif
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
                <strong>Perhatian!</strong> {{ session('error') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Ringkasan Statistik Status Paket -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('upload.index', ['status' => 'menunggu']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Menunggu</span>
                        <h4 class="fw-bold text-warning mb-0">{{ $statMenunggu }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('upload.index', ['status' => 'disetujui_sebagian']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Disetujui Sebagian</span>
                        <h4 class="fw-bold text-info mb-0">{{ $statSebagian }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-circle-half-stroke fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('upload.index', ['status' => 'disetujui']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Disetujui Penuh</span>
                        <h4 class="fw-bold text-success mb-0">{{ $statDisetujui }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('upload.index', ['status' => 'ditolak']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block mb-1">Ditolak</span>
                        <h4 class="fw-bold text-danger mb-0">{{ $statDitolak }}</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-circle-xmark fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

@if(in_array(auth()->user()->role, ['admin', 'operator']))
<!-- Form Upload Paket (Hanya Admin / Operator) -->
<div class="card border-0 shadow-sm rounded-4 mb-4" id="formUpload">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <h6 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-file-excel text-success me-2"></i>Unggah Paket Persediaan SAKTI Baru
        </h6>
        <p class="text-muted small mb-0">
            Dukung format file Excel (.xlsx) atau CSV (maksimal 10 MB). File Word (.docx) tidak didukung.
        </p>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-dark">Berkas Laporan (.xlsx / .csv) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control rounded-3" accept=".xlsx,.csv" required>
                    <div class="form-text" style="font-size: 11px;">
                        Contoh: <code>bahan_opname_fisik.xlsx</code>. Sheet default: <em>input LOFBI</em>.
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark">Ruangan / Gudang Tujuan</label>
                    <select name="ruangan_id" class="form-select rounded-3">
                        <option value="">Gudang Utama (Default)</option>
                        @foreach($ruangans as $r)
                            <option value="{{ $r->id }}">{{ $r->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark">Nama Sheet (Opsional)</label>
                    <input type="text" name="sheet_name" class="form-control rounded-3" placeholder="Contoh: input LOFBI">
                    <div class="form-text" style="font-size: 11px;">Kosongkan untuk deteksi otomatis.</div>
                </div>
                <div class="col-12 text-end pt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-upload me-1"></i> Proses Upload
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Tabel Daftar Paket Unggahan -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold text-dark mb-0">Riwayat Paket Unggahan</h6>
            <span class="text-muted small">Menampilkan riwayat berkas staging yang telah diunggah</span>
        </div>
        @if(request()->filled('status'))
            <a href="{{ route('upload.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-xmark me-1"></i> Hapus Filter
            </a>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 80px;">ID</th>
                        <th>Nama Berkas</th>
                        <th>Pengunggah</th>
                        <th class="text-center">Jumlah Baris</th>
                        <th class="text-center">Status Paket</th>
                        <th>Waktu Unggah</th>
                        <th class="text-end pe-4" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pakets as $p)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">#{{ $p->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-file-excel text-success fs-5"></i>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $p->nama_file }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Tipe: {{ strtoupper($p->jenis) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $p->pengunggah->name ?? 'Sistem' }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ ucfirst($p->pengunggah->role ?? '') }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                    {{ number_format($p->jumlah_baris) }} baris
                                </span>
                            </td>
                            <td class="text-center">
                                @if($p->status === 'menunggu')
                                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-clock me-1"></i> Menunggu
                                    </span>
                                @elseif($p->status === 'disetujui_sebagian')
                                    <span class="badge bg-info text-white px-3 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-circle-half-stroke me-1"></i> Sebagian
                                    </span>
                                @elseif($p->status === 'disetujui')
                                    <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-circle-check me-1"></i> Disetujui
                                    </span>
                                @elseif($p->status === 'ditolak')
                                    <span class="badge bg-danger text-white px-3 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Ditolak
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-3 py-1 rounded-pill">{{ ucfirst($p->status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size: 12px;">
                                {{ $p->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('upload.show', $p->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                    @if(auth()->user()->role === 'validator' && in_array($p->status, ['menunggu', 'disetujui_sebagian']))
                                        <i class="fa-solid fa-user-check me-1"></i> Validasi
                                    @else
                                        <i class="fa-solid fa-eye me-1"></i> Detail
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fs-2 mb-2 d-block text-secondary opacity-50"></i>
                                Belum ada berkas staging yang diunggah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pakets->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $pakets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
