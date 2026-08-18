@extends('layouts.app')

@section('page_title', 'Mulai Opname Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center">
                <a href="{{ route('opname.index') }}" class="btn btn-light btn-sm me-3"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
                <h6 class="fw-bold mb-0 text-dark">Formulir Pengecekan Fisik (Opname)</h6>
            </div>
            <div class="card-body p-4">
                <form action="#" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal Opname</label>
                            <input type="date" class="form-control" name="tanggal" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama Pemeriksa (Petugas)</label>
                            <input type="text" class="form-control" name="petugas" placeholder="Masukkan nama petugas..." required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Catatan / Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Contoh: Pengecekan rutin stok gudang ATK bulan Agustus..."></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="alert alert-info py-2 small d-flex align-items-center">
                        <i class="fa-solid fa-circle-info me-2 fs-5"></i>
                        <span>Setelah klik <strong>"Buat Sesi Opname"</strong>, sistem akan membuatkan lembar kerja untuk mulai mencocokkan data barang satu per satu.</span>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary fw-bold px-4">Buat Sesi Opname <i class="fa-solid fa-arrow-right ms-1"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection