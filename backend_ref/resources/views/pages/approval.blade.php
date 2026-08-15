<!-- SECTION 8: APPROVAL PAGE -->
<div id="pageSection-approval" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <h1 class="page-title">Approval Pengajuan Barang Keluar</h1>
        <p class="page-subtitle">Verifikasi dan keputusan persetujuan barang keluar oleh Kasubbag / Validator / Pimpinan.</p>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title"><i class="bi bi-check2-square"></i> Daftar Pengajuan Menunggu (Model: TransaksiPersediaan)</h2>
        </div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>Tanggal</th><th>Unit Kerja Penerima</th><th>Item Diminta</th><th>Jumlah</th><th>Pengaju</th><th>Status</th><th>Aksi Keputusan</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fs-xs">04 Agt 2026</td>
                        <td class="fw-bold">Seksi Kepegawaian</td>
                        <td>Pulpen Standard 0.5mm</td>
                        <td class="fw-bold">15 pcs</td>
                        <td>Admin LOFBI</td>
                        <td><span class="table-badge warning">menunggu</span></td>
                        <td>
                            <button class="btn btn-sm btn-success py-0 px-2 me-1 role-action" onclick="alert('POST /api/persediaan/pengajuan/1/setujui')">Setujui</button>
                            <button class="btn btn-sm btn-outline-danger py-0 px-2 role-action" onclick="alert('POST /api/persediaan/pengajuan/1/tolak')">Tolak</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
