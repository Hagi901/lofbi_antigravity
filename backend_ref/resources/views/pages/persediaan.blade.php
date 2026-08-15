<!-- SECTION 3: PERSEDIAAN & BATCH PAGE -->
<div id="pageSection-persediaan" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <div class="page-header-inner">
            <div>
                <h1 class="page-title">Manajemen Persediaan &amp; Batch Stok</h1>
                <p class="page-subtitle">Pencatatan persediaan konsumsi, nomor batch stok masuk FIFO, dan stok minimum.</p>
            </div>
            <div class="page-header-actions">
                <button class="btn-lofbi btn-primary-lofbi role-action" data-bs-toggle="modal" data-bs-target="#modalBarangMasuk"><i class="bi bi-box-arrow-in-down me-1"></i> Catat Barang Masuk</button>
                <button class="btn-lofbi btn-gold-lofbi role-action" data-bs-toggle="modal" data-bs-target="#modalPengajuanKeluar"><i class="bi bi-box-arrow-up-right me-1"></i> Ajukan Barang Keluar</button>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i class="bi bi-boxes"></i> Master Persediaan (Model: Persediaan)</h2></div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>Jenis Barang</th><th>Merk</th><th>Satuan</th><th>Stok Minimum</th><th>Total Sisa Stok</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <tr><td class="fw-bold">Pulpen</td><td>—</td><td>pcs</td><td>10 pcs</td><td class="fw-bold">120 pcs</td><td><span class="table-badge success">Stok Aman</span></td><td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action" onclick="openBatchHistory('Pulpen')">Lihat Batch</button></td></tr>
                    <tr><td class="fw-bold">Kertas A4</td><td>Sinar Dunia</td><td>rim</td><td>5 rim</td><td class="fw-bold">8 rim</td><td><span class="table-badge success">Stok Aman</span></td><td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action" onclick="openBatchHistory('Kertas A4')">Lihat Batch</button></td></tr>
                    <tr><td class="fw-bold">Tinta Printer</td><td>Canon</td><td>botol</td><td>2 botol</td><td class="fw-bold text-danger">1 botol</td><td><span class="table-badge warning">&lt; Stok Minimum</span></td><td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action" onclick="openBatchHistory('Tinta Printer')">Lihat Batch</button></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
