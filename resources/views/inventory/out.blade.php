@extends('layouts.app')

@section('page_title', 'Catat Barang Keluar')

@section('content')
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-arrow-right-from-bracket text-warning me-2"></i>Form Barang Keluar</h6>
            </div>
            <div class="card-body p-4 pt-2">
                {{-- Menampilkan pesan error (termasuk error jika stok tidak cukup dari Controller) --}}
                @if ($errors->any())
                    <div class="alert alert-danger shadow-sm border-0 small rounded-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form action mengarah ke rute simpan (storeOut) --}}
                <form action="{{ route('inventory.out.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Pilih Barang <span class="text-danger">*</span></label>
                        <select class="form-select border-0 shadow-sm bg-light" name="inventory_item_id" required>
                            <option value="">-- Pilih Barang yang Akan Dikeluarkan --</option>
                            @foreach($items as $item)
                                @php
                                    // Hitung sisa stok untuk ditampilkan di dropdown
                                    $sisaStok = $item->total_stok ?? $item->batches()->sum('sisa_stok');
                                @endphp
                                <option value="{{ $item->id }}">
                                    {{ $item->item_code ?? 'INV-'.$item->id }} - {{ $item->name }} 
                                    (Stok Tersedia: {{ $sisaStok }} {{ $item->satuan ?? 'Unit' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Jumlah Keluar <span class="text-danger">*</span></label>
                        <div class="input-group shadow-sm">
                            <input type="number" class="form-control border-0 bg-light" name="qty_out" min="1" placeholder="Contoh: 5" required>
                        </div>
                        <div class="form-text small text-muted mt-2">
                            <i class="fa-solid fa-circle-info me-1"></i> Sistem akan otomatis memotong stok dari tumpukan (batch) yang paling lama sesuai metode FIFO.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4 mt-4">
                        <a href="{{ route('inventory.index') }}" class="btn btn-light me-2 fw-bold shadow-sm">Batal</a>
                        <button type="submit" class="btn btn-warning text-dark fw-bold px-4 shadow-sm"><i class="fa-solid fa-dolly me-1"></i> Proses Barang Keluar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Panel Informasi di Sebelah Kanan -->
    <div class="col-lg-4 mb-4">
        <div class="card bg-warning bg-opacity-10 border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning text-dark rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0">Ketentuan Barang Keluar</h6>
                </div>
                <p class="small text-muted mb-3" style="line-height: 1.6;">Pastikan jumlah barang yang Anda keluarkan <strong>tidak melebihi</strong> sisa stok yang ada di gudang saat ini.</p>
                <p class="small text-muted mb-0" style="line-height: 1.6;">Pengambilan stok akan diprioritaskan dari barang yang pertama kali dibeli <strong>(First-In, First-Out)</strong> secara otomatis oleh sistem untuk menjaga perputaran barang tetap sehat.</p>
            </div>
        </div>
    </div>
</div>
@endsection