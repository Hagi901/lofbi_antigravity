@extends('layouts.app')

@section('page_title', 'Buka Sesi Opname Fisik')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 d-flex align-items-center border-bottom-0">
                <a href="{{ route('opname.index') }}" class="btn btn-light btn-sm me-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clipboard-check text-primary me-2"></i>Buka Sesi Opname Fisik Persediaan</h6>
            </div>
            <div class="card-body p-4 pt-2">

                @if($errors->any())
                    <div class="alert alert-danger shadow-sm border-0 small rounded-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('opname.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Periode Opname <span class="text-danger">*</span></label>
                        <select class="form-select border-0 shadow-sm bg-light" name="periode" required>
                            <option value="">-- Pilih Periode --</option>
                            @foreach($periodes as $p)
                                <option value="{{ $p }}" {{ old('periode') == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Sesuai ketentuan SAKTI: opname dilakukan tiap akhir semester.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control border-0 shadow-sm bg-light" name="tanggal" required value="{{ old('tanggal', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Keterangan / Dasar Pelaksanaan</label>
                        <textarea class="form-control border-0 shadow-sm bg-light" name="keterangan" rows="3"
                            placeholder="Contoh: Opname fisik akhir Semester I TA 2026 berdasarkan SE Kemenkeu...">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- Info otomatis --}}
                    <div class="alert alert-info border-0 shadow-sm small rounded-3 py-3">
                        <i class="fa-solid fa-circle-info me-2 text-info fs-5"></i>
                        Setelah klik <strong>"Buka Sesi Opname"</strong>, sistem akan otomatis memuat seluruh jenis barang persediaan yang aktif beserta <strong>stok buku (snapshot)</strong> saat ini. Anda kemudian dapat mengisi hasil hitung fisik per barang.
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4 mt-2">
                        <a href="{{ route('opname.index') }}" class="btn btn-light me-2 fw-bold shadow-sm rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm rounded-pill">
                            <i class="fa-solid fa-folder-open me-2"></i>Buka Sesi Opname
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Panel Panduan --}}
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>Alur Opname SAKTI</h6>
                <ol class="list-unstyled mb-0" style="line-height: 2;">
                    <li><span class="badge bg-secondary-subtle text-secondary border me-2">1</span> <strong>Buka Sesi</strong> — sistem snapshot stok buku</li>
                    <li><span class="badge bg-warning-subtle text-warning border me-2">2</span> <strong>Input Fisik</strong> — isi jumlah hasil hitung</li>
                    <li><span class="badge bg-warning-subtle text-warning border me-2">3</span> <strong>Ajukan</strong> — kirim ke Validator (KPA)</li>
                    <li><span class="badge bg-success-subtle text-success border me-2">4</span> <strong>Disetujui</strong> — stok disesuaikan otomatis</li>
                    <li><span class="badge bg-dark text-white me-2">5</span> <strong>Cetak</strong> Berita Acara Opname Fisik</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection