@extends('layouts.app')

@section('page_title', 'Validasi Pengajuan Barang Keluar')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->has('approve'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first('approve') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- ─── ANTRIAN MENUNGGU PERSETUJUAN ───────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-clock text-warning me-2"></i>Antrian Pengajuan Barang Keluar
            @if($pengajuan->count() > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $pengajuan->count() }} Menunggu</span>
            @endif
        </h6>
        <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
    <div class="card-body p-4 pt-0">
        @if($pengajuan->count() > 0)
            <div class="table-responsive mt-2">
                <table class="table table-hover align-middle border-bottom">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold">BARANG</th>
                            <th class="text-secondary small fw-bold text-center">JML DIMINTA</th>
                            <th class="text-secondary small fw-bold">UNIT PENERIMA</th>
                            <th class="text-secondary small fw-bold">DIAJUKAN OLEH</th>
                            <th class="text-secondary small fw-bold">TANGGAL</th>
                            <th class="text-secondary small fw-bold text-center">AKSI VALIDASI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuan as $p)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <i class="fa-solid fa-box-open text-primary me-2"></i>
                                    {{ $p->persediaan?->name ?? 'Barang #' . $p->persediaan_id }}
                                </td>
                                <td class="text-center fw-bold text-danger">
                                    {{ $p->jumlah }} <span class="fw-normal text-muted small">Unit</span>
                                </td>
                                <td class="text-muted small">{{ $p->unit_kerja_penerima ?? '-' }}</td>
                                <td class="small">
                                    <i class="fa-solid fa-user me-1 text-muted"></i>
                                    {{ $p->diajukanOleh?->name ?? 'Operator' }}
                                </td>
                                <td class="text-muted small">{{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d M Y') : '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        {{-- Tombol Setujui --}}
                                        <form action="{{ route('inventory.approve', $p->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm fw-bold px-3 shadow-sm"
                                                onclick="return confirm('Setujui pengajuan pengeluaran {{ $p->jumlah }} unit {{ addslashes($p->persediaan?->name) }}?\nStok akan dipotong otomatis menggunakan metode FIFO.')">
                                                <i class="fa-solid fa-check me-1"></i> Setujui
                                            </button>
                                        </form>
                                        {{-- Tombol Tolak --}}
                                        <button class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalTolak{{ $p->id }}">
                                            <i class="fa-solid fa-xmark me-1"></i> Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            {{-- Modal Tolak --}}
                            <div class="modal fade" id="modalTolak{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-ban me-2"></i>Tolak Pengajuan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted small mb-3">Anda akan <strong>menolak</strong> pengajuan pengeluaran <strong>{{ $p->jumlah }} unit {{ $p->persediaan?->name }}</strong>. Stok tidak akan dikurangi.</p>
                                            <form action="{{ route('inventory.reject', $p->id) }}" method="POST" id="formTolak{{ $p->id }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Alasan Penolakan</label>
                                                    <textarea class="form-control border-0 shadow-sm bg-light" name="alasan" rows="3" placeholder="Contoh: Stok sedang dalam proses audit..."></textarea>
                                                </div>
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger fw-bold px-4">
                                                        <i class="fa-solid fa-xmark me-1"></i> Konfirmasi Tolak
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 mt-2">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 90px; height: 90px;">
                    <i class="fa-solid fa-inbox text-secondary" style="font-size: 2.5rem; opacity: 0.4;"></i>
                </div>
                <h6 class="fw-bold text-dark mt-2">Tidak ada pengajuan yang menunggu</h6>
                <p class="text-muted small mb-0">Semua pengajuan barang keluar sudah diproses.</p>
            </div>
        @endif
    </div>
</div>

{{-- ─── RIWAYAT KEPUTUSAN ───────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Riwayat Keputusan (20 Terakhir)
        </h6>
    </div>
    <div class="card-body p-4 pt-0">
        @if($riwayat->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle border-bottom">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary small fw-bold">BARANG</th>
                            <th class="text-secondary small fw-bold text-center">JUMLAH</th>
                            <th class="text-secondary small fw-bold text-center">STATUS</th>
                            <th class="text-secondary small fw-bold">DISETUJUI/DITOLAK OLEH</th>
                            <th class="text-secondary small fw-bold">CATATAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayat as $r)
                            <tr>
                                <td class="fw-bold text-dark small">{{ $r->persediaan?->name ?? '#' . $r->persediaan_id }}</td>
                                <td class="text-center small">{{ $r->jumlah }} Unit</td>
                                <td class="text-center">
                                    @if($r->status === 'disetujui')
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="fa-solid fa-check me-1"></i> Disetujui
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                            <i class="fa-solid fa-xmark me-1"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $r->diputuskanOleh?->name ?? '-' }}</td>
                                <td class="text-muted small">{{ $r->catatan_penolakan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted small text-center py-3 mb-0">Belum ada riwayat keputusan.</p>
        @endif
    </div>
</div>
@endsection
