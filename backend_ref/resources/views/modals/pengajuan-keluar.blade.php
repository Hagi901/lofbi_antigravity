<!-- Modal 3: Pengajuan Keluar -->
<div class="modal fade" id="modalPengajuanKeluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:var(--kemenhub-blue-dark);color:#fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-box-arrow-up-right me-1"></i> Form Pengajuan Barang Keluar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-sm">
                <form onsubmit="handlePengajuanKeluarSubmit(event)">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unit Kerja Penerima</label>
                        <input type="text" class="form-control" placeholder="Contoh: Seksi Kepegawaian" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Diminta</label>
                        <input type="number" class="form-control" placeholder="10" required>
                    </div>
                    <button type="submit" class="btn-lofbi btn-gold-lofbi w-100 justify-content-center py-2">Kirim Pengajuan</button>
                </form>
            </div>
        </div>
    </div>
</div>
