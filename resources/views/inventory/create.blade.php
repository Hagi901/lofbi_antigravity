@extends('layouts.app')

@section('page_title', 'Tambah Master Barang Persediaan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-box-open text-primary me-2"></i>Form Tambah Master Barang Persediaan
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

                <form action="{{ route('inventory.store') }}" method="POST">
                    @csrf

                    <!-- Identitas Master Barang -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-tag me-2"></i>1. Identitas Master Barang
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-secondary">Nama &amp; Kode Barang BMN <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: BUKU PELAUT (1.01.03.01.014.000037)" value="{{ old('name') }}" required>
                                <small class="text-muted" style="font-size: 11px;">Bisa menyertakan kode 16 digit standar BMN Kemenhub/SIMAN.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Kategori Persediaan <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Satuan Barang <span class="text-danger">*</span></label>
                                <input type="text" name="satuan" class="form-control text-uppercase" placeholder="Contoh: BUAH, LITER, DUS, RIM, KL, PCS" value="{{ old('satuan') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Merk / Spesifikasi</label>
                                <input type="text" name="merk" class="form-control" placeholder="Contoh: Pertamina, PaperOne, Standar Hubla" value="{{ old('merk') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Batas Stok Minimum</label>
                                <input type="number" name="stok_minimum" class="form-control" min="0" value="{{ old('stok_minimum', 10) }}">
                                <small class="text-muted" style="font-size: 11px;">Peringatan akan muncul jika sisa stok di bawah batas ini.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Lokasi Gudang / Ruangan</label>
                                <select name="ruangan_id" class="form-select">
                                    <option value="">-- Pilih Lokasi Gudang --</option>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}" {{ old('ruangan_id') == $r->id ? 'selected' : '' }}>{{ $r->nama }} ({{ $r->gedung }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Saldo Awal / Batch Pertama (Opsional) -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-success mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-boxes-packing me-2"></i>2. Saldo Awal / Batch Pertama (Opsional)
                        </h6>
                        <p class="text-muted small mb-3">Jika barang sudah memiliki saldo fisik saat ini, Anda dapat langsung mencatatkannya sebagai Batch 1.</p>
                        
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary">Jumlah Saldo Awal</label>
                                <input type="number" name="initial_qty" class="form-control" min="0" placeholder="0" value="{{ old('initial_qty', 0) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary">Harga Satuan Perolehan (Rp)</label>
                                <input type="number" name="initial_price" class="form-control" min="0" step="1" placeholder="Contoh: 51700" value="{{ old('initial_price') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary">Nomor Dokumen / Bukti</label>
                                <input type="text" name="no_faktur" class="form-control" placeholder="Contoh: SALDO-AWAL-2026" value="{{ old('no_faktur') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary">Supplier / Asal Barang</label>
                                <input type="text" name="supplier" class="form-control" placeholder="Contoh: Ditjen Hubla / Pembelian" value="{{ old('supplier') }}">
                            </div>
                        </div>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <a href="{{ route('inventory.index') }}" class="btn btn-light px-4 me-2">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Master Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
