<!-- Modal 6: Transfer Masuk -->
<div class="modal fade" id="modalTransferMasuk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:var(--kemenhub-blue-dark);color:#fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-arrow-left-right me-1"></i> Transfer Masuk Antar Ruangan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-sm">
                <form onsubmit="handleTransferMasukSubmit(event)">
                    <div class="mb-3"><label class="form-label fw-semibold">Ruangan Asal</label><select class="form-select"><option>Gudang Persediaan</option><option>Ruang Tata Usaha</option><option>Ruang Kepala</option></select></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Ruangan Tujuan</label><select class="form-select"><option>Ruang Tata Usaha</option><option>Gudang Persediaan</option><option>Ruang Kepala</option></select></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Jumlah</label><input type="number" class="form-control" placeholder="20" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Catatan</label><textarea class="form-control" rows="3" placeholder="Catatan transfer"></textarea></div>
                    <button type="submit" class="btn-lofbi btn-primary-lofbi w-100 justify-content-center py-2 role-action">Simpan Transfer</button>
                </form>
            </div>
        </div>
    </div>
</div>
