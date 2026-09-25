@extends('layouts.app')

@section('page_title', 'Rincian Hasil Opname Fisik Persediaan')

@section('content')
<div class="row">
    <!-- Panel Header Sesi -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <a href="{{ route('opname.index') }}" class="btn btn-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <h5 class="fw-bold text-dark mb-0">Opname Fisik Persediaan — {{ $sesi->periode ?? 'Sesi #' . $sesi->id }}</h5>
                        <span class="badge {{ $sesi->statusBadgeClass() }} border px-3 py-1 rounded-pill">
                            <i class="fa-solid fa-circle-dot me-1" style="font-size: 8px;"></i> {{ $sesi->statusLabel() }}
                        </span>
                    </div>
                    <p class="text-muted small mb-0 ms-md-4 ps-md-2">
                        <i class="fa-regular fa-calendar-check text-primary me-1"></i> Tanggal: <strong>{{ $sesi->tanggal ? $sesi->tanggal->format('d F Y') : '-' }}</strong>
                        &bull; <i class="fa-solid fa-user text-secondary me-1"></i> Petugas: <strong>{{ $sesi->admin?->name ?? 'Admin LOFBI' }}</strong>
                        @if($sesi->approver)
                            &bull; <i class="fa-solid fa-user-check text-success me-1"></i> Approver: <strong>{{ $sesi->approver->name }}</strong>
                            ({{ $sesi->tanggal_persetujuan ? $sesi->tanggal_persetujuan->format('d M Y') : '-' }})
                        @endif
                    </p>
                    @if($sesi->keterangan)
                        <div class="small text-muted mt-2 ms-md-4 ps-md-2">
                            <i class="fa-solid fa-info-circle me-1"></i> Dasar/Keterangan: {{ $sesi->keterangan }}
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if(in_array($sesi->status, ['draft', 'ditolak']) && in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                        <a href="{{ route('opname.input_fisik', $sesi->id) }}" class="btn btn-warning fw-bold px-3 shadow-sm rounded-pill">
                            <i class="fa-solid fa-pen-to-square me-1"></i> {{ $sesi->status === 'ditolak' ? 'Perbaiki Hasil Fisik' : 'Rekam Hasil Fisik' }}
                        </a>
                    @endif

                    @if($sesi->status === 'menunggu_persetujuan' && in_array(Auth::user()->role ?? '', ['admin', 'validator']))
                        <!-- Tombol Setujui -->
                        <form action="{{ route('opname.approve', $sesi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin menyetujui hasil Opname Fisik ini? Penyesuaian stok akan diproses otomatis.');">
                            @csrf
                            <button type="submit" class="btn btn-success fw-bold px-3 shadow-sm rounded-pill">
                                <i class="fa-solid fa-check me-1"></i> Setujui Opname (KPA)
                            </button>
                        </form>

                        <!-- Tombol Tolak (Modal Trigger) -->
                        <button type="button" class="btn btn-outline-danger fw-bold px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalRejectOpname">
                            <i class="fa-solid fa-xmark me-1"></i> Tolak
                        </button>
                    @endif

                    @if($sesi->status === 'disetujui')
                        <a href="{{ route('reports.opname.pdf', ['sesi_id' => $sesi->id]) }}" target="_blank" class="btn btn-dark fw-bold px-3 shadow-sm rounded-pill">
                            <i class="fa-solid fa-print me-1"></i> Cetak Berita Acara (BA)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Sukses / Error / Catatan Penolakan -->
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 small rounded-3 mb-3">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 small rounded-3 mb-3">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($sesi->status === 'ditolak' && $sesi->catatan_penolakan)
            <div class="alert alert-danger border-0 shadow-sm rounded-3 small mb-3">
                <i class="fa-solid fa-circle-exclamation me-2 fs-6"></i>
                <strong>Catatan Penolakan oleh Approver:</strong> {{ $sesi->catatan_penolakan }}
            </div>
        @endif
    </div>

    <!-- Statistik Ringkas Sesi Opname -->
    @php
        $totalItems = $sesi->details->count();
        $totalStokBuku = $sesi->details->sum('stok_buku');
        $totalStokFisik = $sesi->details->whereNotNull('stok_fisik')->sum('stok_fisik');
        $itemsSelisih = $sesi->jumlahSelisih();
    @endphp
    <div class="col-12 mb-4">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="fa-solid fa-boxes-stacked fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold">Total Jenis Barang</small>
                            <h5 class="fw-bold mb-0 text-dark">{{ $totalItems }} <small class="text-muted fw-normal fs-6">Item</small></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="fa-solid fa-book-bookmark fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold">Total Stok Sistem</small>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($totalStokBuku, 0, ',', '.') }} <small class="text-muted fw-normal fs-6">Unit</small></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="fa-solid fa-clipboard-check fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold">Total Stok Fisik</small>
                            <h5 class="fw-bold mb-0 text-dark">
                                @if($sesi->status === 'draft')
                                    <span class="text-muted fs-6">Belum Diinput</span>
                                @else
                                    {{ number_format($totalStokFisik, 0, ',', '.') }} <small class="text-muted fw-normal fs-6">Unit</small>
                                @endif
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="{{ $itemsSelisih > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }} rounded-3 p-3">
                            <i class="fa-solid {{ $itemsSelisih > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check' }} fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold">Temuan Selisih</small>
                            <h5 class="fw-bold mb-0 {{ $itemsSelisih > 0 ? 'text-danger' : 'text-success' }}">
                                @if($sesi->status === 'draft')
                                    <span class="text-muted fs-6">-</span>
                                @elseif($itemsSelisih > 0)
                                    {{ $itemsSelisih }} <small class="text-danger fw-normal fs-6">Item Selisih</small>
                                @else
                                    0 <small class="text-success fw-normal fs-6">Sesuai (Cocok)</small>
                                @endif
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Persediaan & Perbandingan Stok SAKTI -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-list-check text-primary me-2"></i>Daftar Perbandingan Stok Sistem vs Fisik (Bahan & Hasil Opname)
                </h6>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="table-responsive mt-2">
                    <table class="table table-hover align-middle border-bottom">
                        <thead class="table-light">
                            <tr>
                                <th class="text-secondary small fw-bold text-center" width="5%">NO</th>
                                <th class="text-secondary small fw-bold" width="30%">NAMA BARANG & SPESIFIKASI</th>
                                <th class="text-secondary small fw-bold text-center" width="10%">SATUAN</th>
                                <th class="text-secondary small fw-bold text-end" width="13%">STOK SISTEM (BUKU)</th>
                                <th class="text-secondary small fw-bold text-end" width="13%">HASIL FISIK</th>
                                <th class="text-secondary small fw-bold text-center" width="12%">SELISIH</th>
                                <th class="text-secondary small fw-bold" width="17%">KETERANGAN / PENYEBAB</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sesi->details as $index => $detail)
                                @php
                                    $namaBarang = $detail->persediaan?->name ?? $detail->persediaan?->jenisBarang?->nama_generik ?? 'Barang #' . $detail->persediaan_id;
                                    $stokBuku = (int) $detail->stok_buku;
                                    $stokFisik = $detail->stok_fisik;
                                    $selisih = $detail->selisih;
                                @endphp
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $namaBarang }}</div>
                                        <small class="text-muted">
                                            Kategori: {{ $detail->persediaan?->jenisBarang?->kategori?->nama ?? 'Persediaan BMN' }}
                                            @if($detail->persediaan?->merk) &bull; Merk: {{ $detail->persediaan->merk }} @endif
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border">{{ $detail->satuan ?? $detail->persediaan?->satuan ?? '-' }}</span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace text-dark">
                                        {{ number_format($stokBuku, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold font-monospace">
                                        @if($stokFisik !== null)
                                            <span class="text-primary">{{ number_format($stokFisik, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted small fst-italic">Belum diisi</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($selisih !== null)
                                            <span class="badge {{ $detail->selisihBadgeClass() }} border px-2 py-1 font-monospace">
                                                {{ $detail->selisihLabel() }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $detail->catatan ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Tidak ada data rincian pada sesi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($sesi->status === 'menunggu_persetujuan' && in_array(Auth::user()->role ?? '', ['admin', 'validator']))
<!-- Modal Tolak Opname -->
<div class="modal fade" id="modalRejectOpname" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('opname.reject', $sesi->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Tolak Hasil Opname Fisik
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-2">
                    <p class="small text-muted">Berikan alasan/catatan perbaikan agar Operator dapat memeriksa ulang dan memperbaiki data hitung fisik.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Catatan Penolakan / Instruksi Revisi <span class="text-danger">*</span></label>
                        <textarea name="catatan_penolakan" class="form-control bg-light border-0 shadow-sm" rows="4" placeholder="Contoh: Tolak karena jumlah Kertas HVS perlu dihitung ulang di gudang arsip..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-3 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-xmark me-1"></i> Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
