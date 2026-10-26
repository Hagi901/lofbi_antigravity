@extends('layouts.app')

@section('page_title', 'Perekaman Hasil Opname Fisik')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card-app mb-4">
            <div class="card-header-app">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('opname.show', $sesi->id) }}" class="btn btn-light btn-sm btn-action-icon" title="Kembali">
                        <i class="fa-solid fa-arrow-left text-secondary"></i>
                    </a>
                    <div>
                        <h1 class="fw-bold mb-0 text-dark fs-6">
                            <i class="fa-solid fa-pen-to-square text-warning me-2"></i>Perekaman Hasil Opname Fisik
                        </h1>
                        <small class="text-muted">{{ $sesi->periode }} &bull; Tanggal: {{ $sesi->tanggal ? $sesi->tanggal->format('d F Y') : '-' }}</small>
                    </div>
                </div>
                <div>
                    <span class="badge {{ $sesi->statusBadgeClass() }} px-3 py-2 border rounded-pill">
                        Status: {{ $sesi->statusLabel() }}
                    </span>
                </div>
            </div>

            <div class="card-body-app">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 small mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($sesi->status === 'ditolak' && $sesi->catatan_penolakan)
                    <div class="alert alert-danger border-0 small mb-3">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> <strong>Catatan Penolakan Sebelumnya:</strong> {{ $sesi->catatan_penolakan }}
                    </div>
                @endif

                <div class="alert alert-light border small rounded-3 mb-4">
                    <i class="fa-solid fa-circle-info text-primary me-2"></i>
                    Petunjuk SAKTI: Masukkan jumlah <strong>Stok Fisik</strong> yang sebenarnya dihitung di gudang atau ruangan. Sistem akan otomatis menghitung selisih (Stok Fisik - Stok Buku). Setelah disimpan, status sesi akan berubah menjadi <strong>Menunggu Persetujuan</strong> Validator / KPA.
                </div>

                <form action="{{ route('opname.save_fisik', $sesi->id) }}" method="POST" id="formOpnameFisik">
                    @csrf

                    <div class="table-responsive border rounded-3 mb-4">
                        <table class="table table-hover align-middle mb-0" id="tableOpnameFisik" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary small fw-bold text-center border-0 py-2" width="4%">NO</th>
                                    <th class="text-secondary small fw-bold border-0 py-2" width="30%">NAMA BARANG &amp; SPESIFIKASI</th>
                                    <th class="text-secondary small fw-bold text-center border-0 py-2" width="10%">SATUAN</th>
                                    <th class="text-secondary small fw-bold text-end border-0 py-2" width="12%">STOK BUKU</th>
                                    <th class="text-secondary small fw-bold text-center border-0 py-2" width="16%">STOK FISIK <span class="text-danger">*</span></th>
                                    <th class="text-secondary small fw-bold text-center border-0 py-2" width="10%">SELISIH</th>
                                    <th class="text-secondary small fw-bold border-0 py-2" width="18%">KETERANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sesi->details as $index => $detail)
                                    @php
                                        $namaBarang = $detail->persediaan?->name ?? $detail->persediaan?->jenisBarang?->nama_generik ?? 'Barang #' . $detail->persediaan_id;
                                        $stokBuku = (int) $detail->stok_buku;
                                        $stokFisikVal = old('stok_fisik.'.$detail->id, $detail->stok_fisik ?? $stokBuku);
                                        $selisihInit = (int)$stokFisikVal - $stokBuku;
                                    @endphp
                                    <tr data-detail-id="{{ $detail->id }}">
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
                                        <td class="text-end fw-bold text-dark font-monospace py-2">
                                            <span class="stok-buku-val">{{ number_format($stokBuku, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center py-2">
                                            <input type="number" 
                                                   name="stok_fisik[{{ $detail->id }}]" 
                                                   class="form-control form-control-app form-control-sm text-center fw-bold input-stok-fisik" 
                                                   min="0" 
                                                   step="1"
                                                   value="{{ $stokFisikVal }}" 
                                                   required 
                                                   data-buku="{{ $stokBuku }}"
                                                   data-target-selisih="selisih-{{ $detail->id }}">
                                        </td>
                                        <td class="text-center py-2">
                                            <span id="selisih-{{ $detail->id }}" class="badge {{ $selisihInit == 0 ? 'bg-success-subtle text-success border-success' : ($selisihInit > 0 ? 'bg-info-subtle text-info border-info' : 'bg-danger-subtle text-danger border-danger') }} border px-2 py-1 font-monospace selisih-badge">
                                                {{ $selisihInit > 0 ? '+'.$selisihInit : $selisihInit }}
                                            </span>
                                        </td>
                                        <td class="py-2">
                                            <input type="text" 
                                                   name="catatan[{{ $detail->id }}]" 
                                                   class="form-control form-control-app form-control-sm" 
                                                   placeholder="Contoh: Pemakaian belum dicatat..." 
                                                   value="{{ old('catatan.'.$detail->id, $detail->catatan) }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada daftar persediaan untuk sesi ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <a href="{{ route('opname.show', $sesi->id) }}" class="btn btn-light">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Simpan &amp; Ajukan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.input-stok-fisik');

    function updateSelisih(input) {
        const buku = parseInt(input.dataset.buku) || 0;
        const fisik = parseInt(input.value) || 0;
        const selisih = fisik - buku;
        const targetId = input.dataset.targetSelisih;
        const badge = document.getElementById(targetId);

        if (badge) {
            badge.className = 'badge border px-2 py-1 font-monospace selisih-badge ' + 
                (selisih === 0 ? 'bg-success-subtle text-success border-success' : 
                (selisih > 0 ? 'bg-info-subtle text-info border-info' : 'bg-danger-subtle text-danger border-danger'));
            badge.textContent = selisih > 0 ? '+' + selisih : selisih;
        }
    }

    inputs.forEach(input => {
        input.addEventListener('input', function() {
            updateSelisih(this);
        });
    });
});
</script>
@endsection
