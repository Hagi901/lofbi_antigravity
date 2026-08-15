<!-- Modal 4: Opname Baru -->
<div class="modal fade" id="modalOpnameBaru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:var(--kemenhub-blue-dark);color:#fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-clipboard2-plus-fill me-1"></i> Sesi Opname Fisik Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-sm">
                <form onsubmit="handleOpnameBaruSubmit(event)">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Ruangan Target</label>
                        <select class="form-select"><option value="1">Ruang Tata Usaha</option><option value="2">Gudang Persediaan</option></select>
                    </div>
                    <button type="submit" class="btn-lofbi btn-primary-lofbi w-100 justify-content-center py-2">Buka Sesi Opname</button>
                </form>
            </div>
        </div>
    </div>
</div>
