<!-- Modal 5: Export Laporan -->
<div class="modal fade" id="modalExportLaporan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:var(--kemenhub-blue-dark);color:#fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-download me-1"></i> Export Laporan Sistem</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-sm text-center py-4">
                <p>Unduh rekapitulasi data inventarisasi KSOP Kelas I Banten (GET /api/laporan/export):</p>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button class="btn-lofbi btn-primary-lofbi role-action" onclick="alert('Downloading PDF Laporan BAOP...'); bootstrap.Modal.getInstance(document.getElementById('modalExportLaporan')).hide();"><i class="bi bi-file-pdf"></i> Export PDF</button>
                    <button class="btn-lofbi btn-gold-lofbi role-action" onclick="alert('Downloading Excel Rekap Nilai Buku...'); bootstrap.Modal.getInstance(document.getElementById('modalExportLaporan')).hide();"><i class="bi bi-file-excel"></i> Export Excel</button>
                </div>
            </div>
        </div>
    </div>
</div>
