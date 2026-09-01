@extends('layouts.app')

@section('page_title', 'Opname Fisik Persediaan')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Riwayat Opname Fisik Persediaan</h6>
        @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
        <a href="{{ route('opname.create') }}" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm rounded-pill">
            <i class="fa-solid fa-plus me-1"></i> Buka Sesi Opname Baru
        </a>
        @endif
    </div>
    <div class="card-body p-4 pt-0">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-3">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 small rounded-3">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Panduan Alur SAKTI --}}
        <div class="alert alert-light border shadow-sm rounded-3 small mb-4">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="badge bg-secondary-subtle text-secondary border px-2">1. Draft</span>
                <i class="fa-solid fa-arrow-right text-muted"></i>
                <span class="badge bg-warning-subtle text-warning border px-2">2. Input Fisik</span>
                <i class="fa-solid fa-arrow-right text-muted"></i>
                <span class="badge bg-warning-subtle text-warning border px-2">3. Menunggu Persetujuan</span>
                <i class="fa-solid fa-arrow-right text-muted"></i>
                <span class="badge bg-success-subtle text-success border px-2">4. Disetujui → Stok Disesuaikan</span>
            </div>
        </div>

        @if(isset($sesi) && $sesi->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle border-bottom w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold">PERIODE</th>
                        <th class="text-secondary small fw-bold">TANGGAL</th>
                        <th class="text-secondary small fw-bold">PETUGAS</th>
                        <th class="text-secondary small fw-bold text-center">ITEM</th>
                        <th class="text-secondary small fw-bold text-center">SELISIH</th>
                        <th class="text-secondary small fw-bold text-center">STATUS</th>
                        <th class="text-secondary small fw-bold text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sesi as $s)
                    <tr>
                        <td class="fw-bold text-dark">{{ $s->periode ?? 'Opname #' . $s->id }}</td>
                        <td class="text-muted small">{{ $s->tanggal ? $s->tanggal->format('d M Y') : '-' }}</td>
                        <td class="small">
                            <i class="fa-solid fa-user text-secondary me-1"></i>{{ $s->admin?->name ?? 'Admin' }}
                        </td>
                        <td class="text-center fw-bold text-primary">{{ $s->details->count() }}</td>
                        <td class="text-center">
                            @php $selisih = $s->jumlahSelisih(); @endphp
                            @if($s->status === 'draft')
                                <span class="text-muted small">-</span>
                            @elseif($selisih === 0)
                                <span class="badge bg-success-subtle text-success border" style="font-size:10px;">Tidak Ada Selisih</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border" style="font-size:10px;">{{ $selisih }} item selisih</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $s->statusBadgeClass() }} border px-2 py-1">
                                {{ $s->statusLabel() }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('opname.show', $s->id) }}" class="btn btn-light btn-sm text-primary shadow-sm" title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if(in_array($s->status, ['draft', 'ditolak']) && in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                                <a href="{{ route('opname.input_fisik', $s->id) }}" class="btn btn-warning btn-sm shadow-sm fw-bold" title="Input Hasil Fisik" style="font-size:11px;">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Input Fisik
                                </a>
                                @endif
                                @if($s->status === 'disetujui')
                                <a href="{{ route('reports.opname.pdf', ['sesi_id' => $s->id]) }}" target="_blank" class="btn btn-light btn-sm text-dark shadow-sm" title="Cetak Berita Acara">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                @endif
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
            <h5 class="fw-bold text-dark mt-2">Belum ada Sesi Opname Fisik</h5>
            <p class="text-muted small mb-4" style="line-height: 1.6;">
                Lakukan opname fisik secara berkala (semesteran) sesuai ketentuan SAKTI<br>
                untuk memastikan kesesuaian stok sistem dengan kondisi fisik di gudang.
            </p>
            @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
            <a href="{{ route('opname.create') }}" class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Buka Sesi Opname Pertama
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection