@extends('layouts.app')

@section('page_title', 'Edit Data Aset')

@section('content')
@php $userRole = Auth::user()->role ?? ''; @endphp

<div class="row">
    <!-- Kolom Form Utama -->
    <div class="col-lg-8 mb-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('assets.index') }}" class="btn btn-light btn-sm btn-action-icon" title="Kembali">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <h1 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Formulir Perubahan Data Aset</h1>
                </div>
            </div>
            <div class="card-body-app">

                @if ($errors->any())
                    <div class="alert alert-danger border-0 small mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('assets.update', $asset->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h2 class="fw-bold text-secondary mb-3 mt-2 border-bottom pb-2 fs-6">Informasi Dasar & Kodefikasi SIMAN / BMN</h2>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label-app">Kode Aset Internal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-app fw-bold" name="asset_code" value="{{ old('asset_code', $asset->asset_code ?? $asset->kode_aset) }}" required>
                        </div>
                        <div class="col-md-5 mb-3 mb-md-0">
                            <label class="form-label-app">Kodefikasi BMN (10 Digit SIMAN)</label>
                            <input type="text" class="form-control form-control-app font-monospace" name="kode_bmn" value="{{ old('kode_bmn', $asset->kode_bmn) }}" placeholder="Contoh: 3.05.01.05.001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-app">NUP (No. Urut)</label>
                            <input type="number" class="form-control form-control-app font-monospace text-center fw-bold" name="nup" value="{{ old('nup', $asset->nup ?? 1) }}" min="1">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-7 mb-3 mb-md-0">
                            <label class="form-label-app">Nama Barang / Tipe <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-app" name="name" value="{{ old('name', $asset->name) }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-app">Nomor Seri Pabrik (S/N)</label>
                            <input type="text" class="form-control form-control-app" name="no_seri" value="{{ old('no_seri', $asset->no_seri) }}" placeholder="Contoh: SN-88392019-ASUS">
                        </div>
                    </div>

                    <h2 class="fw-bold text-secondary mb-3 mt-4 border-bottom pb-2 fs-6">Kategorisasi, Lokasi & Penanggung Jawab</h2>
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label-app">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select form-select-app" name="category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ ($asset->jenisBarang?->kategori_id ?? $asset->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label-app">Sub Kategori <span class="text-danger">*</span></label>
                            <select class="form-select form-select-app" name="sub_category_id" required>
                                <option value="">-- Pilih Sub --</option>
                                @foreach($subCategories as $sub)
                                    <option value="{{ $sub->id }}" {{ ($asset->sub_kategori ?? $asset->sub_category_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label-app">Lokasi Ruangan <span class="text-danger">*</span></label>
                            <select class="form-select form-select-app" name="room_id" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ ($asset->ruangan_id ?? $asset->room_id) == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-app">Penanggung Jawab (PJ)</label>
                            <input type="text" class="form-control form-control-app" name="penanggung_jawab" value="{{ old('penanggung_jawab', $asset->penanggung_jawab) }}" placeholder="Nama / NIP Pemegang">
                        </div>
                    </div>

                    <h2 class="fw-bold text-secondary mb-3 mt-4 border-bottom pb-2 fs-6">Kondisi & Nilai Keuangan</h2>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label-app">Kondisi Barang <span class="text-danger">*</span></label>
                            <select class="form-select form-select-app" name="condition" required>
                                <option value="Baik" {{ $asset->condition == 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Rusak Ringan" {{ $asset->condition == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="Rusak Berat" {{ $asset->condition == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label-app">Nilai Perolehan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted" style="border-color: var(--color-border);">Rp</span>
                                <input type="number" class="form-control form-control-app" name="acquisition_value" value="{{ old('acquisition_value', $asset->acquisition_value) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-app">Umur Ekonomis <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control form-control-app" name="useful_life_years" value="{{ old('useful_life_years', $asset->useful_life_years) }}" required>
                                <span class="input-group-text bg-white text-muted" style="border-color: var(--color-border);">Tahun</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4 mt-4">
                        <a href="{{ route('assets.index') }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel Informasi -->
    <div class="col-lg-4 mb-4">
        <div class="card-app h-100" style="background: var(--color-warning-light); border-color: var(--color-warning-border);">
            <div class="card-body-app">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 40px; height: 40px; background: var(--color-warning); color: #fff;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-0 fs-6">Catatan Perubahan</h2>
                </div>
                <p class="small text-muted mb-0" style="line-height: 1.6;">Mengubah <strong>Nilai Perolehan</strong> atau <strong>Umur Ekonomis</strong> akan mempengaruhi riwayat perhitungan penyusutan otomatis pada laporan akhir tahun. Pastikan perubahan telah disetujui oleh atasan.</p>
            </div>
        </div>
    </div>
</div>
@endsection
