@extends('layouts.app')

@section('page_title', 'Edit Master Barang Persediaan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-app">
            <div class="card-header-app">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm btn-action-icon" title="Kembali ke Daftar">
                        <i class="fa-solid fa-arrow-left text-secondary"></i>
                    </a>
                    <h1 class="fw-bold mb-0 text-dark fs-6">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Master Barang Persediaan
                    </h1>
                </div>
            </div>

            <div class="card-body-app">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 small mb-4" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('inventory.update', $persediaan->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-app">Nama &amp; Kode Barang BMN <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-app" value="{{ old('name', $persediaan->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-app">Kategori Persediaan <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select form-select-app" required>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id', $persediaan->jenisBarang?->kategori_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-app">Satuan Barang <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" class="form-control form-control-app text-uppercase" value="{{ old('satuan', $persediaan->satuan) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-app">Merk / Spesifikasi</label>
                            <input type="text" name="merk" class="form-control form-control-app" value="{{ old('merk', $persediaan->merk) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-app">Batas Stok Minimum</label>
                            <input type="number" name="stok_minimum" class="form-control form-control-app" min="0" value="{{ old('stok_minimum', $persediaan->stok_minimum) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label-app">Lokasi Gudang / Ruangan</label>
                            <select name="ruangan_id" class="form-select form-select-app">
                                <option value="">-- Pilih Lokasi Gudang --</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}" {{ old('ruangan_id', $persediaan->ruangan_id) == $r->id ? 'selected' : '' }}>
                                        {{ $r->nama }} ({{ $r->gedung }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4 mt-4">
                        <a href="{{ route('inventory.index') }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
