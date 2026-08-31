@extends('layouts.app')

@section('page_title', 'Pusat Unduh Laporan')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ─── PANEL FILTER LAPORAN ────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-filter me-2 text-primary"></i>Parameter Filter Laporan</h6>
    </div>
    <div class="card-body pt-0 px-4 pb-4">
        {{-- Form ini akan meneruskan query param ke tombol download yang dibuat oleh JS --}}
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Kategori Barang</label>
                <select id="filter-kategori" class="form-select border-0 shadow-sm fw-bold text-secondary">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Bulan</label>
                <select id="filter-bulan" class="form-select border-0 shadow-sm fw-bold text-secondary">
                    <option value="">Semua Bulan</option>
                    @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $label)
                        <option value="{{ $val }}" {{ date('m') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Tahun</label>
                <select id="filter-tahun" class="form-select border-0 shadow-sm fw-bold text-secondary">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="bg-light rounded-3 px-3 py-2 small text-muted w-100 text-center">
                    <i class="fa-solid fa-info-circle me-1 text-primary"></i>
                    Pilih filter lalu klik tombol PDF atau Excel di bawah
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── KARTU LAPORAN ───────────────────────────────────────────────────── --}}
<div class="row">

    {{-- Kartu Laporan Aset --}}
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden card-hover rounded-4">
            <div class="bg-primary position-absolute top-0 start-0 w-100" style="height: 4px;"></div>
            <div class="card-body text-center py-5">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-desktop text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Laporan Aset Tetap</h5>
                <p class="small text-muted mb-4 px-2">Rekapitulasi aset, kondisi barang, nilai buku, dan sebaran lokasi ruangan di KSOP Kelas I Banten.</p>
                <div class="d-flex gap-2 justify-content-center px-3">
                    <button onclick="downloadLaporan('{{ route('reports.aset.pdf') }}')"
                        class="btn btn-outline-danger btn-sm fw-bold flex-grow-1 shadow-sm">
                        <i class="fa-solid fa-file-pdf me-1"></i> PDF
                    </button>
                    <button onclick="downloadLaporan('{{ route('reports.aset.excel') }}')"
                        class="btn btn-outline-success btn-sm fw-bold flex-grow-1 shadow-sm">
                        <i class="fa-solid fa-file-excel me-1"></i> Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Laporan Persediaan --}}
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden card-hover rounded-4">
            <div class="bg-success position-absolute top-0 start-0 w-100" style="height: 4px;"></div>
            <div class="card-body text-center py-5">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-boxes-stacked text-success" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Laporan Persediaan</h5>
                <p class="small text-muted mb-4 px-2">Riwayat mutasi barang masuk &amp; keluar (Metode FIFO) beserta sisa saldo stok gudang saat ini.</p>
                <div class="d-flex gap-2 justify-content-center px-3">
                    <button onclick="downloadLaporan('{{ route('reports.persediaan.pdf') }}')"
                        class="btn btn-outline-danger btn-sm fw-bold flex-grow-1 shadow-sm">
                        <i class="fa-solid fa-file-pdf me-1"></i> PDF
                    </button>
                    <button onclick="downloadLaporan('{{ route('reports.persediaan.excel') }}')"
                        class="btn btn-outline-success btn-sm fw-bold flex-grow-1 shadow-sm">
                        <i class="fa-solid fa-file-excel me-1"></i> Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Berita Acara Opname --}}
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden card-hover rounded-4">
            <div class="bg-warning position-absolute top-0 start-0 w-100" style="height: 4px;"></div>
            <div class="card-body text-center py-5">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-file-signature text-warning" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Berita Acara Opname</h5>
                <p class="small text-muted mb-4 px-2">Cetak dokumen resmi hasil verifikasi fisik gudang untuk pelaporan pertanggungjawaban pimpinan.</p>
                <div class="px-3">
                    <button onclick="alert('Fitur Berita Acara Opname akan segera tersedia.')"
                        class="btn btn-dark w-100 btn-sm fw-bold shadow-sm">
                        <i class="fa-solid fa-print me-1"></i> Cetak Berita Acara
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── PREVIEW TABEL DATA ──────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 mt-2">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-table text-primary me-2"></i>Informasi Filter Aktif</h6>
    </div>
    <div class="card-body pt-0 px-4 pb-4">
        <div id="filter-summary" class="bg-light rounded-3 px-4 py-3 small text-muted">
            <i class="fa-solid fa-circle-info me-2 text-primary"></i>
            Filter aktif: <strong id="summary-text">Semua Kategori</strong> —
            Periode: <strong id="summary-period">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</strong>
        </div>
    </div>
</div>

<style>
    .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important; }
</style>

<script>
    const bulanNames = {
        '01': 'Januari', '02': 'Februari', '03': 'Maret', '04': 'April',
        '05': 'Mei', '06': 'Juni', '07': 'Juli', '08': 'Agustus',
        '09': 'September', '10': 'Oktober', '11': 'November', '12': 'Desember'
    };

    // Fungsi download dengan filter aktif dikirim ke URL sebagai query string
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

    // Update tampilan ringkasan filter setiap kali diubah
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