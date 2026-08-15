<!-- SECTION 6: LAPORAN BAOP & DBR PAGE -->
<div id="pageSection-laporan" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <h1 class="page-title">Laporan &amp; Export Data</h1>
        <p class="page-subtitle">Generasi laporan resmi Berita Acara Opname (BAOP), Daftar Barang Ruangan (DBR), dan Nilai Buku.</p>
    </div>

    <div class="d-grid-3 mt-4">
        <div class="panel p-4 text-center">
            <i class="bi bi-file-earmark-text-fill text-kemenhub" style="font-size:48px;"></i>
            <h3 class="panel-title justify-content-center mt-3 fs-5">Laporan BAOP</h3>
            <p class="fs-xs text-muted mt-2">Berita Acara Opname Fisik hasil rekonsiliasi stok.</p>
            <button class="btn-lofbi btn-primary-lofbi w-100 mt-3 justify-content-center role-action" onclick="alert('Generate GET /api/laporan/baop')">Cetak BAOP (PDF)</button>
        </div>
        <div class="panel p-4 text-center">
            <i class="bi bi-door-open-fill text-gold" style="font-size:48px;"></i>
            <h3 class="panel-title justify-content-center mt-3 fs-5">Daftar Barang Ruangan (DBR)</h3>
            <p class="fs-xs text-muted mt-2">Kartu inventarisasi barang milik negara per ruangan.</p>
            <button class="btn-lofbi btn-gold-lofbi w-100 mt-3 justify-content-center role-action" onclick="alert('Generate GET /api/laporan/dbr')">Cetak DBR (PDF)</button>
        </div>
        <div class="panel p-4 text-center">
            <i class="bi bi-calculator-fill text-success" style="font-size:48px;"></i>
            <h3 class="panel-title justify-content-center mt-3 fs-5">Rekap Nilai Buku</h3>
            <p class="fs-xs text-muted mt-2">Perhitungan nilai perolehan, akumulasi penyusutan, dan nilai buku.</p>
            <button class="btn-lofbi btn-outline-lofbi w-100 mt-3 justify-content-center role-action" onclick="alert('Generate GET /api/laporan/nilai-buku')">Export Excel</button>
        </div>
    </div>
</div>
