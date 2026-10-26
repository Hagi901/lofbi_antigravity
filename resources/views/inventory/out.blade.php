@extends('layouts.app')

@section('page_title', 'Catat Barang Keluar')

@section('content')
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm btn-action-icon" title="Kembali ke Daftar">
                        <i class="fa-solid fa-arrow-left text-secondary"></i>
                    </a>
                    <h1 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-arrow-up-from-bracket text-primary me-2"></i>Form Pengajuan Barang Keluar</h1>
                </div>
            </div>
            <div class="card-body-app">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 small mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('inventory.out.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label-app">Pilih Barang <span class="text-danger">*</span></label>
                        <select class="form-select form-select-app" name="inventory_item_id" required>
                            <option value="">-- Pilih Barang yang Akan Dikeluarkan --</option>
                            @foreach($items as $item)
                                @php
                                    $sisaStok = $item->total_stok ?? $item->batches()->sum('sisa_stok');
                                @endphp
                                <option value="{{ $item->id }}" {{ old('inventory_item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->item_code ?? 'INV-'.$item->id }} - {{ $item->name }} 
                                    (Stok Tersedia: {{ $sisaStok }} {{ $item->satuan ?? 'Unit' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-app">Jumlah Keluar <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-app" name="qty_out" min="1" value="{{ old('qty_out') }}" placeholder="Contoh: 5" required>
                        <div class="form-text small text-muted mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i> Sistem akan otomatis memotong stok dari tumpukan (batch) yang paling lama sesuai metode FIFO.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-app">Unit Kerja Penerima / Keperluan</label>
                        <input type="text" class="form-control form-control-app" name="unit_kerja_penerima" value="{{ old('unit_kerja_penerima') }}" placeholder="Contoh: Seksi Operasional / Keperluan Pelayanan">
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4 mt-4">
                        <a href="{{ route('inventory.index') }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-arrow-up-from-bracket me-1"></i> Ajukan Keluar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel Informasi di Sebelah Kanan -->
    <div class="col-lg-4 mb-4">
        <div class="card-app h-100" style="background: var(--color-primary-light); border-color: var(--color-primary-border);">
            <div class="card-body-app">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 40px; height: 40px; background: var(--color-primary); color: #fff;">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-0 fs-6">Ketentuan Barang Keluar</h2>
                </div>
                <p class="small text-muted mb-3" style="line-height: 1.6;">Pastikan jumlah barang yang dikeluarkan <strong>tidak melebihi</strong> sisa stok yang ada di gudang saat ini.</p>
                <p class="small text-muted mb-0" style="line-height: 1.6;">Pengambilan stok akan diprioritaskan dari barang yang pertama kali dibeli <strong>(First-In, First-Out)</strong> secara otomatis oleh sistem untuk menjaga perputaran barang tetap sehat.</p>
            </div>
        </div>
    </div>
</div>
@endsection
