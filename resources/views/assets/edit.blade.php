@extends('layouts.app')

@section('page_title', 'Edit Data Aset')

@section('content')
<div class="row">
    <!-- Kolom Form Utama -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center">
                <a href="{{ route('assets.index') }}" class="btn btn-light btn-sm me-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Formulir Perubahan Data Aset</h6>
            </div>
            <div class="card-body p-4 pt-2">
                
                @if ($errors->any())
                    <div class="alert alert-danger shadow-sm border-0 small rounded-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form action mengarah ke route update dengan parameter ID --}}
                <form action="{{ route('assets.update', $asset->id) }}" method="POST">
                    @csrf 
                    @method('PUT') {{-- Wajib ditambahkan untuk proses Update di Laravel --}}
                    
                    <h6 class="fw-bold text-secondary mb-3 mt-2 border-bottom pb-2">Informasi Dasar Aset</h6>
                    <div class="row mb-3">
                        <div class="col-md-5 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-secondary">Kode Aset <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-0 shadow-sm bg-light fw-bold text-primary" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-secondary">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-0 shadow-sm bg-light" name="name" value="{{ old('name', $asset->name) }}" required>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary mb-3 mt-4 border-bottom pb-2">Kategorisasi & Lokasi</h6>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-secondary">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select border-0 shadow-sm bg-light" name="category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $asset->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-secondary">Sub Kategori <span class="text-danger">*</span></label>
                            <select class="form-select border-0 shadow-sm bg-light" name="sub_category_id" required>
                                <option value="">-- Pilih Sub --</option>
                                @foreach($subCategories as $sub)
                                    <option value="{{ $sub->id }}" {{ $asset->sub_category_id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Lokasi Ruangan <span class="text-danger">*</span></label>
                            <select class="form-select border-0 shadow-sm bg-light" name="room_id" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ $asset->room_id == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary mb-3 mt-4 border-bottom pb-2">Kondisi & Nilai Keuangan</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-secondary">Kondisi Barang <span class="text-danger">*</span></label>
                            <select class="form-select border-0 shadow-sm bg-light" name="condition" required>
                                <option value="Baik" {{ $asset->condition == 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Rusak Ringan" {{ $asset->condition == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="Rusak Berat" {{ $asset->condition == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-secondary">Nilai Perolehan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0 text-muted">Rp</span>
                                <input type="number" class="form-control border-0 bg-light" name="acquisition_value" value="{{ old('acquisition_value', $asset->acquisition_value) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Umur Ekonomis <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <input type="number" class="form-control border-0 bg-light" name="useful_life_years" value="{{ old('useful_life_years', $asset->useful_life_years) }}" required>
                                <span class="input-group-text bg-white border-0 text-muted">Tahun</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4 mt-4">
                        <a href="{{ route('assets.index') }}" class="btn btn-light me-2 fw-bold shadow-sm rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-warning text-dark fw-bold px-4 shadow-sm rounded-pill">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Panel Informasi -->
    <div class="col-lg-4 mb-4">
        <div class="card bg-warning bg-opacity-10 border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning text-dark rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0">Catatan Perubahan</h6>
                </div>
                <p class="small text-muted mb-0" style="line-height: 1.6;">Mengubah <strong>Nilai Perolehan</strong> atau <strong>Umur Ekonomis</strong> akan mempengaruhi riwayat perhitungan penyusutan otomatis pada laporan akhir tahun. Pastikan perubahan telah disetujui oleh atasan.</p>
            </div>
        </div>
    </div>
</div>
@endsection