@extends('layouts.app')

@section('page_title', 'Buka Sesi Opname Fisik')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 mb-4">
        <div class="card-app">
            <div class="card-header-app">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('opname.index') }}" class="btn btn-light btn-sm btn-action-icon" title="Kembali">
                        <i class="fa-solid fa-arrow-left text-secondary"></i>
                    </a>
                    <h1 class="fw-bold mb-0 text-dark fs-6">
                        <i class="fa-solid fa-clipboard-check text-primary me-2"></i>Buka Sesi Opname Fisik Persediaan
                    </h1>
                </div>
            </div>
            <div class="card-body-app">
                @if($errors->any())
                    <div class="alert alert-danger border-0 small mb-3">
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
                        <label class="form-label-app">Periode Opname <span class="text-danger">*</span></label>
                        <select class="form-select form-select-app" name="periode" required>
                            <option value="">-- Pilih Periode --</option>
                            @foreach($periodes as $p)
                                <option value="{{ $p }}" {{ old('periode') == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" style="font-size: 11px;">Sesuai ketentuan SAKTI: opname dilakukan tiap akhir semester.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-app">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-app" name="tanggal" required value="{{ old('tanggal', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label-app">Keterangan / Dasar Pelaksanaan</label>
                        <textarea class="form-control form-control-app" name="keterangan" rows="3"
                            placeholder="Contoh: Opname fisik akhir Semester I TA 2026 berdasarkan SE Kemenkeu...">{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="alert alert-info border-0 small rounded-3 py-3 mb-4">
                        <i class="fa-solid fa-circle-info me-2 text-info fs-5"></i>
                        Setelah klik <strong>Buka Sesi Opname</strong>, sistem akan otomatis memuat seluruh jenis barang persediaan yang aktif beserta <strong>stok buku (snapshot)</strong> saat ini. Anda kemudian dapat mengisi hasil hitung fisik per barang.
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4">
                        <a href="{{ route('opname.index') }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-folder-open me-2"></i>Buka Sesi Opname
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Panel Panduan --}}
    <div class="col-lg-4 mb-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <h2 class="fw-bold text-dark mb-0 fs-6"><i class="fa-solid fa-list-check text-primary me-2"></i>Alur Opname SAKTI</h2>
            </div>
            <div class="card-body-app">
                <ol class="list-unstyled mb-0" style="line-height: 2.2; font-size: 13px;">
                    <li><span class="badge-app badge-app-secondary me-2">1</span> <strong>Buka Sesi</strong>: snapshot stok buku</li>
                    <li><span class="badge-app badge-app-warning me-2">2</span> <strong>Input Fisik</strong>: isi hasil hitung fisik</li>
                    <li><span class="badge-app badge-app-warning me-2">3</span> <strong>Ajukan</strong>: kirim ke Validator / KPA</li>
                    <li><span class="badge-app badge-app-success me-2">4</span> <strong>Disetujui</strong>: stok otomatis disesuaikan</li>
                    <li><span class="badge-app badge-app-primary me-2">5</span> <strong>Cetak</strong>: unduh Berita Acara resmi</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
