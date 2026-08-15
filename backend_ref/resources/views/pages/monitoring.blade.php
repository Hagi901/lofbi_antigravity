<!-- SECTION 5: MONITORING ASET REAL-TIME -->
<div id="pageSection-monitoring" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <h1 class="page-title">Monitoring Aset Real-Time</h1>
        <p class="page-subtitle">Melacak lokasi aset, kondisi aktual, dan aktivitas sistem terakhir.</p>
    </div>
    <div class="d-grid-2 mt-4">
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i class="bi bi-geo-alt"></i> Tracking Lokasi Aset</h2></div>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Kode Aset</th><th>Ruangan Terakhir</th><th>Terakhir Update</th><th>Status Kondisi</th></tr></thead>
                    <tbody>
                        <tr><td class="fw-bold">ELK-LAP-001</td><td>Ruang Tata Usaha</td><td>05 Agt 2026, 10:22</td><td><span class="table-badge success">Baik</span></td></tr>
                        <tr><td class="fw-bold">ELK-PRN-001</td><td>Gudang Persediaan</td><td>05 Agt 2026, 09:58</td><td><span class="table-badge success">Baik</span></td></tr>
                        <tr><td class="fw-bold">FUR-MJK-002</td><td>Ruang Kepala</td><td>03 Agt 2026, 16:14</td><td><span class="table-badge danger">Rusak Berat</span></td></tr>
                        <tr><td class="fw-bold">ELK-LAP-002</td><td>Ruang Kepala</td><td>28 Jun 2026, 08:07</td><td><span class="table-badge warning">Rusak Ringan</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel p-4">
            <div class="panel-header"><h2 class="panel-title"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Aset Belum Opname &gt; 6 Bulan</h2></div>
            <div class="panel-body">
                <div class="stat-card warning">
                    <div class="stat-card-value">24</div>
                    <div class="stat-card-label">Aset belum di-opname selama lebih dari 6 bulan</div>
                </div>
                <p class="fs-sm text-muted mt-3">Data dummy peringatan. Backend `GET /api/monitoring/peringatan-opname` akan memeriksa status `last_opname_date` dan trigger notifikasi.</p>
            </div>
        </div>
    </div>
    <div class="panel mt-4">
        <div class="panel-header"><h2 class="panel-title"><i class="bi bi-list-check"></i> Log Aktivitas Sistem Terbaru</h2></div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Detail</th></tr></thead>
                <tbody>
                    <tr><td class="fs-xs">05 Agt 2026, 10:42</td><td>Operator LOFBI</td><td>Update lokasi aset</td><td>ELK-LAP-001 dipindah ke Ruang Tata Usaha</td></tr>
                    <tr><td class="fs-xs">05 Agt 2026, 09:59</td><td>Validator LOFBI</td><td>Verifikasi kondisi</td><td>FUR-MJK-002 dikategorikan rusak berat</td></tr>
                    <tr><td class="fs-xs">04 Agt 2026, 16:20</td><td>Admin LOFBI</td><td>Sinkronisasi data</td><td>Baru ditarik dari API backend</td></tr>
                    <tr><td class="fs-xs">03 Agt 2026, 14:11</td><td>Kasubbag LOFBI</td><td>Perbarui status opname</td><td>OPN-001 status draft</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
