@extends('layouts.app')

@section('page_title', 'Detail Paket Upload #' . $paket->id)

@section('content')
<!-- Header Halaman -->
<div class="card-app mb-4">
    <div class="card-body-app d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('upload.index') }}" class="btn btn-sm btn-light btn-action-icon text-muted" title="Kembali">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="fw-bold text-dark mb-0 fs-6">Paket Upload #{{ $paket->id }}: {{ $paket->nama_file }}</h1>
            </div>
            <div class="d-flex align-items-center gap-3 ms-4 ps-1 text-muted small flex-wrap">
                <span><i class="fa-solid fa-user me-1"></i> {{ $paket->pengunggah->name ?? 'Sistem' }} ({{ ucfirst($paket->pengunggah->role ?? '') }})</span>
                <span><i class="fa-solid fa-calendar me-1"></i> {{ $paket->created_at->format('d/m/Y H:i') }}</span>
                <span><i class="fa-solid fa-layer-group me-1"></i> {{ number_format($countTotal) }} total baris</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Badge Status Paket -->
            @if($paket->status === 'menunggu')
                <span class="badge-app badge-app-warning">
                    <i class="fa-solid fa-clock"></i> Menunggu Validasi
                </span>
            @elseif($paket->status === 'disetujui_sebagian')
                <span class="badge-app badge-app-info">
                    <i class="fa-solid fa-circle-half-stroke"></i> Disetujui Sebagian
                </span>
            @elseif($paket->status === 'disetujui')
                <span class="badge-app badge-app-success">
                    <i class="fa-solid fa-circle-check"></i> Disetujui Penuh
                </span>
            @elseif($paket->status === 'ditolak')
                <span class="badge-app badge-app-danger">
                    <i class="fa-solid fa-circle-xmark"></i> Ditolak
                </span>
            @endif

            @if(auth()->user()->role === 'validator' && $countMenunggu > 0)
                {{-- Tombol Aksi Validator: Setujui Semua --}}
                <form action="{{ route('upload.setujui_semua', $paket->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui seluruh {{ $countMenunggu }} baris yang belum ditolak? Data akan langsung masuk ke tabel resmi.');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-check-double me-1"></i> Setujui Semua
                    </button>
                </form>
            @endif

            <!-- Tombol Aksi Pengunggah: Upload Revisi -->
            @if(in_array(auth()->user()->role, ['admin', 'operator']) && ($countDitolak > 0 || $paket->status === 'ditolak'))
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalRevisi">
                    <i class="fa-solid fa-arrow-rotate-right me-1"></i> Upload Revisi
                </button>
            @endif
        </div>
    </div>
</div>

<!-- Alert Sukses / Error -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 small mb-3">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 small mb-3">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Filter Baris & Kontrol -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="btn-group p-1 bg-white border rounded-pill shadow-xs" role="group">
        <a href="{{ route('upload.show', $paket->id) }}" class="btn btn-sm rounded-pill px-3 {{ empty($filter) ? 'btn-primary' : 'btn-light text-muted' }}">
            Semua ({{ $countTotal }})
        </a>
        <a href="{{ route('upload.show', ['id' => $paket->id, 'filter' => 'menunggu']) }}" class="btn btn-sm rounded-pill px-3 {{ $filter === 'menunggu' ? 'btn-warning text-dark' : 'btn-light text-muted' }}">
            Menunggu ({{ $countMenunggu }})
        </a>
        <a href="{{ route('upload.show', ['id' => $paket->id, 'filter' => 'disetujui']) }}" class="btn btn-sm rounded-pill px-3 {{ $filter === 'disetujui' ? 'btn-success' : 'btn-light text-muted' }}">
            Disetujui ({{ $countDisetujui }})
        </a>
        <a href="{{ route('upload.show', ['id' => $paket->id, 'filter' => 'ditolak']) }}" class="btn btn-sm rounded-pill px-3 {{ $filter === 'ditolak' ? 'btn-danger' : 'btn-light text-muted' }}">
            Ditolak ({{ $countDitolak }})
        </a>
    </div>

    @if($countDitolak > 0 && $filter !== 'ditolak')
        <a href="{{ route('upload.show', ['id' => $paket->id, 'filter' => 'ditolak']) }}" class="btn btn-sm btn-outline-danger">
            <i class="fa-solid fa-filter me-1"></i> Tampilkan yang ditolak
        </a>
    @endif
</div>

<!-- Tabel Baris Staging -->
<div class="card-app mb-4">
    <div class="card-body-app p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 60px;">No</th>
                        <th style="min-width: 170px;">Kode Barang (16 Digit)</th>
                        <th style="min-width: 220px;">Nama Barang</th>
                        <th style="width: 90px;">Satuan</th>
                        <th class="text-end" style="width: 100px;">Saldo</th>
                        <th class="text-center" style="width: 120px;">Status</th>
                        <th style="min-width: 200px;">Catatan / Revisi</th>
                        @if(auth()->user()->role === 'validator')
                            <th class="text-end pe-3" style="width: 150px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($barisList as $b)
                        <tr class="{{ $b->status === 'ditolak' ? 'table-danger bg-opacity-25' : ($b->status === 'disetujui' ? 'table-success bg-opacity-25' : '') }}">
                            <td class="ps-3 fw-bold text-muted">{{ $b->nomor_baris }}</td>
                            <td>
                                <span class="font-monospace text-dark fw-bold">
                                    {{ $b->kode_barang_lengkap ?: '-' }}
                                </span>
                                @if($b->kb_lvl1)
                                    <div class="text-muted" style="font-size: 10.5px;">
                                        {{ $b->kb_lvl1 }}.{{ $b->kb_lvl2 }}.{{ $b->kb_lvl3 }}.{{ $b->kb_lvl4 }}.{{ $b->kb_lvl5 }}.{{ $b->kb_seq }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $b->nama_barang }}</div>
                                @if($b->kategori_sakti)
                                    <span class="badge bg-light text-muted border" style="font-size: 10px;">{{ $b->kategori_sakti }}</span>
                                @endif
                                @if($b->is_placeholder)
                                    <span class="badge bg-secondary text-white" style="font-size: 10px;">Placeholder</span>
                                @endif
                            </td>
                            <td>{{ $b->satuan }}</td>
                            <td class="text-end fw-semibold">
                                {{ number_format($b->saldo_sistem ?? 0) }}
                            </td>
                            <td class="text-center">
                                @if($b->status === 'menunggu')
                                    <span class="badge-app badge-app-warning" style="font-size: 11px;">
                                        Menunggu
                                    </span>
                                @elseif($b->status === 'disetujui')
                                    <span class="badge-app badge-app-success" style="font-size: 11px;">
                                        Disetujui
                                    </span>
                                @elseif($b->status === 'ditolak')
                                    <span class="badge-app badge-app-danger" style="font-size: 11px;">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($b->status === 'ditolak')
                                    <div class="text-danger fw-semibold" style="font-size: 11.5px;">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $b->deskripsi_revisi }}
                                    </div>
                                @elseif($b->perlu_perhatian && $b->catatan_parser)
                                    <div class="text-warning-emphasis" style="font-size: 11px;">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $b->catatan_parser }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            @if(auth()->user()->role === 'validator')
                                <td class="text-end pe-3">
                                    @if($b->status === 'menunggu')
                                        <div class="btn-group btn-group-sm">
                                            <!-- Setujui Baris -->
                                            <form action="{{ route('upload.baris.setujui', $b->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Setujui baris ini">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                            <!-- Tolak Baris (Buka Modal) -->
                                            <button type="button" class="btn btn-outline-danger" title="Tolak baris ini" onclick="bukaModalTolak({{ $b->id }}, {{ $b->nomor_baris }}, '{{ addslashes($b->nama_barang) }}')">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    @elseif($b->status === 'disetujui')
                                        <span class="text-success small fw-semibold">
                                            <i class="fa-solid fa-check me-1"></i> Selesai
                                        </span>
                                    @elseif($b->status === 'ditolak')
                                        <span class="text-danger small fw-semibold">
                                            <i class="fa-solid fa-clock me-1"></i> Tunggu Revisi
                                        </span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'validator' ? 8 : 7 }}" class="text-center py-4 text-muted">
                                Tidak ada baris yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($barisList->hasPages())
            <div class="p-3 border-top">
                {{ $barisList->links() }}
            </div>
        @endif
    </div>
</div>

@if(auth()->user()->role === 'validator')
<!-- Modal Tolak Baris (Validator) -->
<div class="modal fade" id="modalTolak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: var(--radius-lg);">
            <form id="formTolakBaris" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h3 class="modal-title fw-bold text-dark fs-6">
                        <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Tolak Baris Staging
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="small text-muted mb-2">
                        Anda akan menolak baris ke-<strong id="tolakNomorBaris"></strong>: <span id="tolakNamaBaris" class="fw-semibold text-dark"></span>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label-app">Deskripsi Revisi <span class="text-danger">*</span></label>
                        <textarea name="deskripsi_revisi" class="form-control form-control-app" rows="3" placeholder="Tuliskan alasan penolakan dan perbaikan yang diminta..." required></textarea>
                        <div class="form-text" style="font-size: 11px;">Alasan ini wajib diisi dan akan ditampilkan kepada pengunggah.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-xmark me-1"></i> Tolak Baris
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Upload Revisi (Pengunggah) -->
<div class="modal fade" id="modalRevisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: var(--radius-lg);">
            <form action="{{ route('upload.revisi', $paket->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h3 class="modal-title fw-bold text-dark fs-6">
                        <i class="fa-solid fa-arrow-rotate-right text-warning me-2"></i>Unggah Berkas Revisi
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="alert alert-info border-0 rounded-3 p-3 small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Baris dengan kode barang yang sama akan <strong>menggantikan baris yang ditolak</strong> dan kembali berstatus menunggu validasi. Baris yang <strong>sudah disetujui tidak akan terpengaruh</strong> dan tidak diduplikasi.
                    </div>
                    <div class="mb-3">
                        <label class="form-label-app">Pilih Berkas yang Sudah Diperbaiki (.xlsx / .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control form-control-app" accept=".xlsx,.csv" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-upload me-1"></i> Unggah Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModalTolak(barisId, nomorBaris, namaBarang) {
    document.getElementById('tolakNomorBaris').innerText = nomorBaris;
    document.getElementById('tolakNamaBaris').innerText = namaBarang;
    document.getElementById('formTolakBaris').action = '{{ url("/upload/baris") }}/' + barisId + '/tolak';
    var myModal = new bootstrap.Modal(document.getElementById('modalTolak'));
    myModal.show();
}
</script>
@endsection
