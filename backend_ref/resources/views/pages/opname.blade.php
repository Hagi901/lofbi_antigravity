<!-- SECTION 4: OPNAME FISIK PAGE -->
<div id="pageSection-opname" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <div class="page-header-inner">
            <div>
                <h1 class="page-title">Opname Fisik Barang &amp; Inventarisasi</h1>
                <p class="page-subtitle">Pencocokan jumlah stok sistem dengan fisik barang di lapangan per ruangan.</p>
            </div>
            <div class="page-header-actions">
                <button class="btn-lofbi btn-primary-lofbi role-action" data-bs-toggle="modal" data-bs-target="#modalOpnameBaru"><i class="bi bi-clipboard2-plus-fill me-1"></i> Mulai Sesi Opname</button>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i class="bi bi-clipboard2-check-fill"></i> Sesi Opname (Model: OpnameSesi)</h2></div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>ID Sesi</th><th>Ruangan</th><th>Tanggal</th><th>Petugas</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <tr><td class="fw-bold">OPN-001</td><td>Ruang Tata Usaha</td><td>04 Agt 2026</td><td>Admin LOFBI</td><td><span class="table-badge warning">draft</span></td><td><button class="btn btn-sm btn-primary py-0 px-2 role-action">Input Fisik</button></td></tr>
                    <tr><td class="fw-bold">OPN-002</td><td>Gudang Persediaan</td><td>02 Agt 2026</td><td>Kasubbag LOFBI</td><td><span class="table-badge success">selesai</span></td><td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action">Lihat BAOP</button></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
