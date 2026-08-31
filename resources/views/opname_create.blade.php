@extends('layouts.app')

@section('page_title', 'Mulai Opname Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 d-flex align-items-center border-bottom-0">
                <a href="{{ route('opname.index') }}" class="btn btn-light btn-sm me-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;"><i class="fa-solid fa-arrow-left"></i></a>
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clipboard-check text-primary me-2"></i>Formulir Pengecekan Fisik (Opname)</h6>
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

                <form action="{{ route('opname.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Target Lokasi Ruangan <span class="text-danger">*</span></label>
                        <select class="form-select border-0 shadow-sm bg-light" name="ruangan_id" required>
                            <option value="">-- Pilih Ruangan untuk Opname --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->nama ?? $room->name }} ({{ $room->gedung ?? 'Gedung Utama' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-secondary">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control border-0 shadow-sm bg-light" name="tanggal" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nama Pemeriksa / Petugas</label>
                            <input type="text" class="form-control border-0 shadow-sm bg-light" name="petugas" value="{{ Auth::user()->name ?? 'Petugas Opname' }}" placeholder="Nama Petugas..." required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Catatan / Keterangan Pelaksanaan</label>
                        <textarea class="form-control border-0 shadow-sm bg-light" name="keterangan" rows="3" placeholder="Contoh: Pengecekan fisik inventaris semesteran BMN KSOP Banten..."></textarea>
                    </div>

                    <div class="alert alert-info py-3 small d-flex align-items-center rounded-3 border-0 shadow-sm">
                        <i class="fa-solid fa-circle-info me-3 fs-4 text-info"></i>
                        <span>Setelah klik <strong>"Simpan & Verifikasi Opname"</strong>, sistem akan merekam kondisi seluruh aset pada ruangan tersebut dan memperbarui tanggal terakhir opname.</span>
                    </div>

                    <div class="d-flex justify-content-end border-top pt-4 mt-4">
                        <a href="{{ route('opname.index') }}" class="btn btn-light me-2 fw-bold shadow-sm rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm rounded-pill">
                            <i class="fa-solid fa-clipboard-check me-1"></i> Simpan & Verifikasi Opname
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection