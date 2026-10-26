@extends('layouts.app')

@section('page_title', 'Manajemen Aset Tetap')

@section('content')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@php
    $totalAsetCount = count($assets ?? []);
    $totalPerolehan = 0;
    $totalNilaiBuku = 0;
    foreach ($assets ?? [] as $a) {
        $totalPerolehan += (float) ($a->nilai_perolehan ?? 0);
        $totalNilaiBuku += (float) ($a->hitungPenyusutanGarisLurus()['nilai_buku'] ?? 0);
    }
    $userRole = Auth::user()->role ?? '';
@endphp

<!-- Page Header -->
<x-page-header title="Manajemen Aset Tetap" subtitle="Penyusutan otomatis metode Garis Lurus SIMAN semesteran">
    <x-slot:actions>
        @if(in_array($userRole, ['admin', 'operator']))
        <a href="{{ route('upload.index') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Berkas
        </a>
        @endif
    </x-slot:actions>
</x-page-header>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <x-stat-card
            label="Total Item Aset"
            :value="number_format($totalAsetCount)"
            subtext="Unit Terdaftar"
            icon="fa-solid fa-boxes-stacked"
            color="primary"
        />
    </div>
    <div class="col-md-4 col-sm-6">
        <x-stat-card
            label="Total Nilai Perolehan"
            :value="'Rp ' . number_format($totalPerolehan, 0, ',', '.')"
            icon="fa-solid fa-money-bill-wave"
            color="success"
        />
    </div>
    <div class="col-md-4 col-sm-12">
        <x-stat-card
            label="Total Nilai Buku (SIMAN)"
            :value="'Rp ' . number_format($totalNilaiBuku, 0, ',', '.')"
            icon="fa-solid fa-calculator"
            color="info"
        />
    </div>
</div>

<!-- Data Table Card -->
<div class="card-app">
    <div class="card-header-app">
        <h2 class="fs-6 fw-bold mb-0 text-dark">
            <i class="fa-solid fa-layer-group text-primary me-2"></i>Daftar Aset Aktif BMN
        </h2>
    </div>

    <div class="card-body-app">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 small mb-3">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table id="tabelAset" class="table table-hover table-sm align-middle border-bottom w-100" style="font-size: 12.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold border-0 py-2" width="16%">KODE & NUP</th>
                        <th class="text-secondary small fw-bold border-0 py-2" width="30%">NAMA BARANG & LOKASI</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-center" width="10%">KONDISI</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-end" width="14%">NILAI PEROLEHAN</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-end" width="13%">PENYUSUTAN</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-end" width="13%">NILAI BUKU</th>
                        <th class="text-secondary small fw-bold border-0 py-2 text-center" width="4%">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets ?? [] as $index => $asset)
                        @php
                            $nilaiPerolehan = (float) ($asset->nilai_perolehan ?? 0);
                            $susut          = $asset->hitungPenyusutanGarisLurus();
                            $akumulasi      = $susut['akumulasi'];
                            $nilaiBuku      = $susut['nilai_buku'];
                            $kondisiRaw     = $asset->condition ?? $asset->kondisi ?? 'Baik';
                        @endphp
                        <tr>
                            {{-- KODE + BMN & NUP --}}
                            <td class="py-2">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11px;">
                                        {{ $asset->asset_code ?? $asset->kode_aset ?? 'AST-000' }}
                                    </span>
                                    <span class="badge bg-light text-primary border" style="font-size: 9.5px;" title="Nomor Urut Pendaftaran">
                                        NUP {{ $asset->nup ?? 1 }}
                                    </span>
                                </div>
                                @if($asset->kode_bmn)
                                    <span class="text-muted font-monospace d-block" style="font-size: 10px;" title="Kode BMN SIMAN">
                                        {{ $asset->kode_bmn }}
                                    </span>
                                @endif
                            </td>

                            {{-- NAMA BARANG + Ruangan + PJ + S/N --}}
                            <td class="py-2">
                                <a href="{{ route('assets.show', $asset->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $asset->name ?? $asset->jenisBarang?->nama_generik ?? 'Aset #' . $asset->id }}
                                </a>
                                <div class="text-muted" style="font-size: 11px;">
                                    <span><i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $asset->room?->name ?? $asset->ruangan?->nama ?? 'Belum Dialokasikan' }}</span>
                                    @if($asset->penanggung_jawab)
                                        <span class="text-secondary ms-1">&bull; <i class="fa-solid fa-user text-primary me-1"></i>{{ $asset->penanggung_jawab }}</span>
                                    @endif
                                    @if($asset->no_seri)
                                        <span class="font-monospace text-muted ms-1">&bull; S/N: {{ $asset->no_seri }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- KONDISI --}}
                            <td class="text-center py-2">
                                <x-status-badge :status="$kondisiRaw" />
                            </td>

                            {{-- NILAI PEROLEHAN --}}
                            <td class="text-end fw-bold text-dark py-2">
                                Rp {{ number_format($nilaiPerolehan, 0, ',', '.') }}
                            </td>

                            {{-- NILAI PENYUSUTAN (Terpakai) --}}
                            <td class="text-end text-danger fw-semibold py-2" style="font-size: 11.5px;">
                                - Rp {{ number_format($akumulasi, 0, ',', '.') }}
                            </td>

                            {{-- NILAI BUKU (Sisa) --}}
                            <td class="text-end fw-bold text-primary py-2" style="font-size: 13px;">
                                Rp {{ number_format($nilaiBuku, 0, ',', '.') }}
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center py-2">
                                <x-action-buttons
                                    :viewUrl="route('assets.show', $asset->id)"
                                    :editUrl="in_array($userRole, ['admin', 'operator']) ? route('assets.edit', $asset->id) : null"
                                    :canEdit="in_array($userRole, ['admin', 'operator'])"
                                    :canDelete="$userRole === 'admin'"
                                    :deleteAction="$userRole === 'admin' ? route('assets.destroy', $asset->id) : null"
                                    deleteConfirm="Apakah Anda yakin ingin menghapus aset ini secara permanen?"
                                />
                            </td>
                        </tr>
                    @empty
                        {{-- DataTables handles empty state --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tabelAset').DataTable({
            language: window.DataTablesIndonesian || {},
            ordering: true,
            pageLength: 10,
        });
    });
</script>
@endpush
@endsection
