@extends('layouts.app')

@section('page_title', 'Rincian Hasil Opname Fisik Persediaan')

@section('content')
@php
    $userRole = Auth::user()->role ?? '';
    $totalItems = $sesi->details->count();
    $totalStokBuku = $sesi->details->sum('stok_buku');
    $totalStokFisik = $sesi->details->whereNotNull('stok_fisik')->sum('stok_fisik');
    $itemsSelisih = $sesi->jumlahSelisih();
@endphp

<!-- Panel Header Sesi -->
<div class="card-app mb-4">
    <div class="card-body-app d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <a href="{{ route('opname.index') }}" class="btn btn-light btn-sm btn-action-icon" title="Kembali ke Daftar">
                    <i class="fa-solid fa-arrow-left text-secondary"></i>
                </a>
                <h1 class="fw-bold text-dark mb-0 fs-6">Opname Fisik Persediaan: {{ $sesi->periode ?? 'Sesi #' . $sesi->id }}</h1>
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
                    <i class="fa-solid fa-circle-info me-1"></i> Dasar/Keterangan: {{ $sesi->keterangan }}
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if(in_array($sesi->status, ['draft', 'ditolak']) && in_array($userRole, ['admin', 'operator']))
                <a href="{{ route('opname.input_fisik', $sesi->id) }}" class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-pen-to-square me-1"></i> {{ $sesi->status === 'ditolak' ? 'Perbaiki Fisik' : 'Rekam Fisik' }}
                </a>
            @endif

            @if($sesi->status === 'menunggu_persetujuan' && in_array($userRole, ['admin', 'validator']))
                <form action="{{ route('opname.approve', $sesi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin menyetujui hasil Opname Fisik ini? Penyesuaian stok akan diproses otomatis.');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-check me-1"></i> Setujui Opname
                    </button>
                </form>

                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalRejectOpname">
                    <i class="fa-solid fa-xmark me-1"></i> Tolak
                </button>
            @endif

            @if($sesi->status === 'disetujui')
                <a href="{{ route('reports.opname.pdf', ['sesi_id' => $sesi->id]) }}" target="_blank" class="btn btn-light btn-sm">
                    <i class="fa-solid fa-print me-1"></i> Cetak BA
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Alert Sukses / Error / Catatan Penolakan -->
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

@if($sesi->status === 'ditolak' && $sesi->catatan_penolakan)
    <div class="alert alert-danger border-0 small mb-3">
        <i class="fa-solid fa-circle-exclamation me-2 fs-6"></i>
        <strong>Catatan Penolakan oleh Approver:</strong> {{ $sesi->catatan_penolakan }}
    </div>
@endif

<!-- Statistik Ringkas Sesi Opname -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <x-stat-card
            label="Total Jenis Barang"
            :value="$totalItems"
            subtext="Item"
            icon="fa-solid fa-boxes-stacked"
            color="primary"
        />
    </div>
    <div class="col-md-3 col-6">
        <x-stat-card
            label="Total Stok Sistem"
            :value="number_format($totalStokBuku, 0, ',', '.')"
            subtext="Unit Sistem"
            icon="fa-solid fa-book-bookmark"
            color="info"
        />
    </div>
    <div class="col-md-3 col-6">
        <x-stat-card
            label="Total Stok Fisik"
            :value="$sesi->status === 'draft' ? 'Belum Diinput' : number_format($totalStokFisik, 0, ',', '.')"
            subtext="Unit Aktual"
            icon="fa-solid fa-clipboard-check"
            color="success"
        />
    </div>
    <div class="col-md-3 col-6">
        <x-stat-card
            label="Temuan Selisih"
            :value="$sesi->status === 'draft' ? '-' : $itemsSelisih"
            :subtext="$itemsSelisih > 0 ? 'Item Selisih' : 'Sesuai (Cocok)'"
            :icon="$itemsSelisih > 0 ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-circle-check'"
            :color="$itemsSelisih > 0 ? 'danger' : 'success'"
        />
    </div>
</div>

@if(!empty($hasHargaBelumDiisi))
    <div class="alert alert-warning border-warning d-flex align-items-center justify-content-between p-2 px-3 rounded-3 shadow-sm mb-4">
        <div class="d-flex align-items-center gap-2 small">
            <i class="fa-solid fa-triangle-exclamation text-warning fs-6"></i>
            <span>Terdapat <strong>{{ $jumlahTerdampak }}</strong> barang dengan harga belum terisi (berasal dari berkas SAKTI opname fisik tanpa nominal harga).</span>
        </div>
        <span class="badge bg-warning text-dark border border-warning rounded-pill">
            (Nilai belum lengkap)
        </span>
    </div>
@endif

<!-- Tabel Rincian Persediaan & Perbandingan Stok SAKTI -->
<div class="card-app">
    <div class="card-header-app">
        <h2 class="fs-6 fw-bold mb-0 text-dark">
            <i class="fa-solid fa-list-check text-primary me-2"></i>Daftar Perbandingan Stok Sistem vs Fisik (Bahan &amp; Hasil Opname)
        </h2>
    </div>
    <div class="card-body-app">
        <div class="table-responsive">
            <table class="table table-hover align-middle border-bottom" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold text-center border-0 py-2" width="5%">NO</th>
                        <th class="text-secondary small fw-bold border-0 py-2" width="30%">NAMA BARANG &amp; SPESIFIKASI</th>
                        <th class="text-secondary small fw-bold text-center border-0 py-2" width="10%">SATUAN</th>
                        <th class="text-secondary small fw-bold text-end border-0 py-2" width="13%">STOK SISTEM</th>
                        <th class="text-secondary small fw-bold text-end border-0 py-2" width="13%">HASIL FISIK</th>
                        <th class="text-secondary small fw-bold text-center border-0 py-2" width="12%">SELISIH</th>
                        <th class="text-secondary small fw-bold border-0 py-2" width="17%">KETERANGAN</th>
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
                            <td class="text-center text-muted fw-bold py-2">{{ $index + 1 }}</td>
                            <td class="py-2">
                                <div class="fw-bold text-dark">{{ $namaBarang }}</div>
                                <small class="text-muted" style="font-size: 11px;">
                                    Kategori: {{ $detail->persediaan?->jenisBarang?->kategori?->nama ?? 'Persediaan BMN' }}
                                    @if($detail->persediaan?->merk) &bull; Merk: {{ $detail->persediaan->merk }} @endif
                                </small>
                            </td>
                            <td class="text-center py-2">
                                <span class="badge-app badge-app-secondary">{{ $detail->satuan ?? $detail->persediaan?->satuan ?? '-' }}</span>
                            </td>
                            <td class="text-end fw-bold font-monospace text-dark py-2">
                                {{ number_format($stokBuku, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold font-monospace py-2">
                                @if($stokFisik !== null)
                                    <span class="text-primary">{{ number_format($stokFisik, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted small fst-italic">Belum diisi</span>
                                @endif
                            </td>
                            <td class="text-center py-2">
                                @if($selisih !== null)
                                    <span class="badge {{ $detail->selisihBadgeClass() }} border px-2 py-1 font-monospace">
                                        {{ $detail->selisihLabel() }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-muted small py-2">
                                {{ $detail->catatan ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0 border-0">
                                <x-empty-state
                                    icon="fa-solid fa-boxes-packing"
                                    title="Tidak Ada Rincian Data"
                                    message="Tidak ada data rincian persediaan pada sesi opname ini."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($sesi->status === 'menunggu_persetujuan' && in_array($userRole, ['admin', 'validator']))
<!-- Modal Tolak Opname -->
<div class="modal fade" id="modalRejectOpname" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: var(--radius-lg);">
            <form action="{{ route('opname.reject', $sesi->id) }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h3 class="modal-title fw-bold text-danger fs-6">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Tolak Hasil Opname Fisik
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="small text-muted mb-3">Berikan alasan atau catatan perbaikan agar Operator dapat memeriksa ulang dan memperbaiki data hitung fisik.</p>
                    <div class="mb-3">
                        <label class="form-label-app">Catatan Penolakan / Instruksi Revisi <span class="text-danger">*</span></label>
                        <textarea name="catatan_penolakan" class="form-control form-control-app" rows="4" placeholder="Contoh: Tolak karena jumlah Kertas HVS perlu dihitung ulang..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-xmark me-1"></i> Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
