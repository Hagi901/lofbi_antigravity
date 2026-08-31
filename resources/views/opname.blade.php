@extends('layouts.app')

@section('page_title', 'Opname Fisik Barang')

@section('content')
<div class="card border-0 shadow-sm rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Riwayat Opname Fisik</h6>
        @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
        <a href="{{ route('opname.create') }}" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm rounded-pill">
            <i class="fa-solid fa-plus me-1"></i> Mulai Opname Baru
        </a>
        @endif
    </div>
    <div class="card-body p-4 pt-0">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(isset($sesi) && $sesi->count() > 0)
            <div class="table-responsive mt-2">
                <table class="table table-hover align-middle border-bottom w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold text-center" width="5%">NO</th>
                            <th class="text-secondary small fw-bold">RUANGAN TARGET</th>
                            <th class="text-secondary small fw-bold">TANGGAL OPNAME</th>
                            <th class="text-secondary small fw-bold">PETUGAS / ADMIN</th>
                            <th class="text-secondary small fw-bold text-center">TOTAL ITEM</th>
                            <th class="text-secondary small fw-bold text-center">STATUS</th>
                            <th class="text-secondary small fw-bold text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sesi as $index => $s)
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                <td class="fw-bold text-dark">
                                    <i class="fa-solid fa-location-dot text-danger me-2"></i>{{ $s->ruangan->nama ?? 'Semua Ruangan' }}
                                </td>
                                <td>{{ $s->tanggal ? $s->tanggal->format('d M Y') : '-' }}</td>
                                <td>{{ $s->admin->name ?? 'Admin LOFBI' }}</td>
                                <td class="text-center fw-bold text-primary">{{ $s->details->count() }} Aset</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-1 rounded-pill">
                                        <i class="fa-solid fa-check me-1"></i> Selesai
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('opname.show', $s->id) }}" class="btn btn-light btn-sm text-primary shadow-sm" title="Lihat Hasil Opname">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('reports.opname.pdf', ['sesi_id' => $s->id]) }}" target="_blank" class="btn btn-light btn-sm text-dark shadow-sm" title="Cetak Berita Acara">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 mt-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px;">
                    <i class="fa-solid fa-boxes-packing text-secondary" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
                <h5 class="fw-bold text-dark mt-2">Belum ada riwayat Opname</h5>
                <p class="text-muted small mb-4" style="line-height: 1.6;">Fitur pencocokan stok sistem vs fisik di ruangan KSOP Banten akan dicatat di sini.<br>Lakukan pengecekan berkala untuk memastikan keakuratan inventaris.</p>
                <a href="{{ route('opname.create') }}" class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-sm">
                    <i class="fa-solid fa-plus me-1"></i> Mulai Sesi Opname Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection