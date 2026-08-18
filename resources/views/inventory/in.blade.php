@extends('layouts.app')

@section('page_title', 'Catat Barang Masuk')

@section('content')
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-arrow-right-to-bracket text-success me-2"></i>Form Barang Masuk</h6>
            </div>
            <div class="card-body p-4 pt-2">
                {{-- Menampilkan pesan error jika ada isian yang salah --}}
                @if ($errors->any())
                    <div class="alert alert-danger shadow-sm border-0 small rounded-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form action mengarah ke rute simpan (storeIn) --}}
                <form action="{{ route('inventory.in.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Pilih Barang <span class="text-danger">*</span></label>
                        <select class="form-select border-0 shadow-sm bg-light" name="inventory_item_id" required>
                            <option value="">-- Pilih Barang Persediaan --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->item_code ?? 'INV-'.$item->id }} - {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-secondary">Jumlah Masuk <span class="text-danger">*</span></label>
                            <input type="number" class="form-control border-0 shadow-sm bg-light" name="qty_received" min="1" placeholder="Contoh: 10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Harga Beli Satuan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0 text-muted">Rp</span>
                                <input type="number" class="form-control border-0 bg-light" name="purchase_price" min="0" placeholder="Contoh: 50000" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4 mt-4">
                        <a href="{{ route('inventory.index') }}" class="btn btn-light me-2 fw-bold shadow-sm">Batal</a>
                        <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm"><i class="fa-solid fa-save me-1"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Panel Informasi di Sebelah Kanan -->
    <div class="col-lg-4 mb-4">
        <div class="card bg-success bg-opacity-10 border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h6 class="fw-bold text-success mb-0">Sistem Batch (FIFO)</h6>
                </div>
                <p class="small text-muted mb-0" style="line-height: 1.6;">Setiap barang yang masuk akan dicatat sebagai <strong>Batch baru</strong>. Saat barang dikeluarkan nanti, sistem akan otomatis mengambil stok dari Batch yang paling lama (First-In, First-Out) untuk mencegah barang menumpuk terlalu lama di gudang.</p>
            </div>
        </div>
    </div>
</div>
@endsection