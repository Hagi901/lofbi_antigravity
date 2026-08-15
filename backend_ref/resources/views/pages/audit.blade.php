<!-- SECTION 7: AUDIT TRAIL PAGE -->
<div id="pageSection-audit" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <h1 class="page-title">Audit Trail Sistem</h1>
        <p class="page-subtitle">Rekam jejak semua transaksi dan perubahan data di modul aset, persediaan, opname, dan approval.</p>
    </div>
    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i class="bi bi-journal-text"></i> Log Transaksi Sistem (Model: AuditLog)</h2></div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead><tr><th>Tanggal &amp; Jam</th><th>Pengguna</th><th>Modul</th><th>Aksi</th><th>Detail Perubahan</th></tr></thead>
                <tbody>
                    <tr><td class="fs-xs">05 Agt 2026, 11:05</td><td>Validator LOFBI</td><td>Aset</td><td>Edit</td><td>Perbarui kondisi ELK-PRN-001 menjadi baik</td></tr>
                    <tr><td class="fs-xs">05 Agt 2026, 10:42</td><td>Operator LOFBI</td><td>Persediaan</td><td>Tambah</td><td>Stok Pulpen +50 pcs, supplier Sinar Dunia</td></tr>
                    <tr><td class="fs-xs">05 Agt 2026, 09:59</td><td>Admin LOFBI</td><td>Opname</td><td>Edit</td><td>Update hasil opname OPN-001 Ruang Tata Usaha</td></tr>
                    <tr><td class="fs-xs">04 Agt 2026, 16:30</td><td>Pimpinan KSOP</td><td>Approval</td><td>Approve</td><td>Pengajuan barang keluar Seksi Kepegawaian</td></tr>
                    <tr><td class="fs-xs">04 Agt 2026, 15:10</td><td>Admin LOFBI</td><td>Persediaan</td><td>Hapus</td><td>Batch lama Kertas A4 dihapus</td></tr>
                    <tr><td class="fs-xs">03 Agt 2026, 14:11</td><td>Kasubbag LOFBI</td><td>Opname</td><td>Tambah</td><td>Buat sesi opname baru OPN-002</td></tr>
                    <tr><td class="fs-xs">02 Agt 2026, 10:20</td><td>Validator LOFBI</td><td>Aset</td><td>Edit</td><td>Perbarui ruangan FUR-MJK-002 ke Ruang Kepala</td></tr>
                    <tr><td class="fs-xs">01 Agt 2026, 08:55</td><td>Admin LOFBI</td><td>Persediaan</td><td>Approve</td><td>Terima transfer masuk 20 pcs tinta Canon</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
