@extends('layouts.app')

@section('page_title', 'Pusat Unduh Laporan')

@section('content')
<!-- Page Header -->
<x-page-header title="Pusat Laporan & Ekspor" subtitle="Unduh laporan berkala aset BMN, buku persediaan FIFO, dan Berita Acara Opname Fisik">
</x-page-header>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 small mb-3" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Panel Filter Laporan --}}
<div class="card-app mb-4">
    <div class="card-header-app">
        <h2 class="fs-6 fw-bold mb-0 text-dark"><i class="fa-solid fa-filter me-2 text-primary"></i>Parameter Filter Laporan</h2>
    </div>
    <div class="card-body-app">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label-app">Kategori Barang</label>
                <select id="filter-kategori" class="form-select form-select-app">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-app">Bulan</label>
                <select id="filter-bulan" class="form-select form-select-app">
                    <option value="">Semua Bulan</option>
                    @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $label)
                        <option value="{{ $val }}" {{ date('m') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label-app">Tahun</label>
                <select id="filter-tahun" class="form-select form-select-app">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="bg-light rounded-3 px-3 py-2 small text-muted w-100 text-center border">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                    Pilih filter lalu klik tombol PDF atau Excel di bawah
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Kartu Laporan --}}
<div class="row g-3 mb-4">
    {{-- Kartu Laporan Aset --}}
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card-app h-100 position-relative overflow-hidden text-center p-4">
            <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: var(--color-primary);"></div>
            <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle mx-auto" style="width: 70px; height: 70px; background: var(--color-primary-light); color: var(--color-primary); font-size: 2rem;">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <h3 class="fw-bold text-dark fs-6 mb-2">Laporan Aset Tetap</h3>
            <p class="small text-muted mb-4">Rekapitulasi aset BMN, kondisi barang, nilai buku SIMAN, dan sebaran lokasi ruangan di KSOP Kelas I Banten.</p>
            <div class="d-flex gap-2 justify-content-center">
                <button onclick="downloadLaporan('{{ route('reports.aset.pdf') }}')"
                    class="btn btn-outline-danger btn-sm flex-grow-1">
                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                </button>
                <button onclick="downloadLaporan('{{ route('reports.aset.excel') }}')"
                    class="btn btn-outline-success btn-sm flex-grow-1">
                    <i class="fa-solid fa-file-excel me-1"></i> Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Kartu Laporan Persediaan --}}
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card-app h-100 position-relative overflow-hidden text-center p-4">
            <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: var(--color-success);"></div>
            <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle mx-auto" style="width: 70px; height: 70px; background: var(--color-success-light); color: var(--color-success); font-size: 2rem;">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <h3 class="fw-bold text-dark fs-6 mb-2">Laporan Persediaan</h3>
            <p class="small text-muted mb-4">Riwayat mutasi barang masuk &amp; keluar metode FIFO beserta sisa saldo stok gudang terkini.</p>
            <div class="d-flex gap-2 justify-content-center">
                <button onclick="downloadLaporan('{{ route('reports.persediaan.pdf') }}')"
                    class="btn btn-outline-danger btn-sm flex-grow-1">
                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                </button>
                <button onclick="downloadLaporan('{{ route('reports.persediaan.excel') }}')"
                    class="btn btn-outline-success btn-sm flex-grow-1">
                    <i class="fa-solid fa-file-excel me-1"></i> Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Kartu Berita Acara Opname --}}
    <div class="col-md-4">
        <div class="card-app h-100 position-relative overflow-hidden text-center p-4">
            <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: var(--color-warning);"></div>
            <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle mx-auto" style="width: 70px; height: 70px; background: var(--color-warning-light); color: var(--color-warning); font-size: 2rem;">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <h3 class="fw-bold text-dark fs-6 mb-2">Berita Acara Opname</h3>
            <p class="small text-muted mb-4">Cetak dokumen resmi hasil verifikasi fisik gudang untuk pelaporan pertanggungjawaban pimpinan.</p>
            <div>
                <a href="{{ route('reports.opname.pdf') }}" target="_blank"
                    class="btn btn-primary w-100 btn-sm">
                    <i class="fa-solid fa-print me-1"></i> Cetak Berita Acara
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Preview Filter Aktif --}}
<div class="card-app">
    <div class="card-header-app">
        <h2 class="fs-6 fw-bold mb-0 text-dark"><i class="fa-solid fa-table text-primary me-2"></i>Informasi Filter Aktif</h2>
    </div>
    <div class="card-body-app">
        <div id="filter-summary" class="bg-light rounded-3 px-3 py-2 small text-muted border">
            <i class="fa-solid fa-circle-info me-2 text-primary"></i>
            Filter aktif: <strong id="summary-text">Semua Kategori</strong> &bull;
            Periode: <strong id="summary-period">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</strong>
        </div>
    </div>
</div>

<script>
    const bulanNames = {
        '01': 'Januari', '02': 'Februari', '03': 'Maret', '04': 'April',
        '05': 'Mei', '06': 'Juni', '07': 'Juli', '08': 'Agustus',
        '09': 'September', '10': 'Oktober', '11': 'November', '12': 'Desember'
    };

    function downloadLaporan(baseUrl) {
        const kategori = document.getElementById('filter-kategori').value;
        const bulan    = document.getElementById('filter-bulan').value;
        const tahun    = document.getElementById('filter-tahun').value;

        const params = new URLSearchParams();
        if (kategori) params.append('kategori', kategori);
        if (bulan)    params.append('bulan', bulan);
        if (tahun)    params.append('tahun', tahun);

        const url = baseUrl + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    }

    function updateSummary() {
        const kategori = document.getElementById('filter-kategori').value || 'Semua Kategori';
        const bulan    = document.getElementById('filter-bulan').value;
        const tahun    = document.getElementById('filter-tahun').value;

        document.getElementById('summary-text').textContent = kategori;
        const periodText = (bulan ? bulanNames[bulan] + ' ' : '') + (tahun || '');
        document.getElementById('summary-period').textContent = periodText || 'Semua Periode';
    }

    document.getElementById('filter-kategori').addEventListener('change', updateSummary);
    document.getElementById('filter-bulan').addEventListener('change', updateSummary);
    document.getElementById('filter-tahun').addEventListener('change', updateSummary);
    updateSummary();
</script>
@endsection
