<!-- Modal 2: Barang Masuk -->
<div class="modal fade" id="modalBarangMasuk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:var(--kemenhub-blue-dark);color:#fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-box-arrow-in-down me-1"></i> Catat Barang Masuk (Batch FIFO)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-sm">
                <form onsubmit="handleBarangMasukSubmit(event)">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Referensi</label>
                        <input type="text" class="form-control" placeholder="REF-2026-001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Faktur</label>
                        <input type="text" class="form-control" placeholder="INV-2026-089" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nota Dinas</label>
                        <input type="text" class="form-control" placeholder="ND/2026/08" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Supplier</label>
                        <input type="text" class="form-control" placeholder="Nama Supplier" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Masuk</label>
                        <input type="date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Masuk</label>
                        <input type="number" class="form-control" placeholder="50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Harga Satuan (Rp)</label>
                        <input type="number" class="form-control" placeholder="45000" required>
                    </div>
                    <button type="submit" class="btn-lofbi btn-primary-lofbi w-100 justify-content-center py-2 role-action">Simpan Barang Masuk</button>
                </form>
            </div>
        </div>
    </div>
</div>
