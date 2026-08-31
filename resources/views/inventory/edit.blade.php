@extends('layouts.app')

@section('page_title', 'Edit Master Barang Persediaan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Master Barang Persediaan
                </h6>
                <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm text-secondary shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4 pt-2">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('inventory.update', $persediaan->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Nama &amp; Kode Barang BMN <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $persediaan->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Kategori Persediaan <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id', $persediaan->jenisBarang?->kategori_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Satuan Barang <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" class="form-control text-uppercase" value="{{ old('satuan', $persediaan->satuan) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Merk / Spesifikasi</label>
                            <input type="text" name="merk" class="form-control" value="{{ old('merk', $persediaan->merk) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Batas Stok Minimum</label>
                            <input type="number" name="stok_minimum" class="form-control" min="0" value="{{ old('stok_minimum', $persediaan->stok_minimum) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Lokasi Gudang / Ruangan</label>
                            <select name="ruangan_id" class="form-select">
                                <option value="">-- Pilih Lokasi Gudang --</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}" {{ old('ruangan_id', $persediaan->ruangan_id) == $r->id ? 'selected' : '' }}>
                                        {{ $r->nama }} ({{ $r->gedung }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-end pt-4 mt-3 border-top">
                        <a href="{{ route('inventory.index') }}" class="btn btn-light px-4 me-2">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Perbarui Master Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
