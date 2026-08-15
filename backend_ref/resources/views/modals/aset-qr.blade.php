<!-- Modal 7: QR Code Aset -->
<div class="modal fade" id="modalAsetQr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:var(--kemenhub-blue-dark);color:#fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-qr-code me-1"></i> QR Code Aset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-sm">
                <div class="row gy-3">
                    <div class="col-md-5">
                        <div class="p-4 text-center" style="border:1px dashed #c4cdd8;background:#f7f8fb;min-height:240px;display:flex;align-items:center;justify-content:center;">
                            <div>
                                <div class="fs-4 text-muted mb-2">QR Code Placeholder</div>
                                <div class="fs-xs text-muted">// TODO-BACKEND: GET /api/aset/{id}/qr - generate QR asli dari library QR di backend</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h5 id="modalAsetQrTitle">ELK-LAP-001</h5>
                        <p class="mb-2"><strong>Kode Aset:</strong> <span id="modalAsetQrKode">ELK-LAP-001</span></p>
                        <p class="mb-2"><strong>Nama:</strong> Laptop Lenovo ThinkPad E14</p>
                        <p class="mb-2"><strong>Lokasi:</strong> Ruang Tata Usaha</p>
                        <p class="mb-2"><strong>Kondisi:</strong> Baik</p>
                        <p class="mb-2"><strong>Riwayat Mutasi Terakhir:</strong> Dipindah dari Gudang Persediaan pada 02 Agt 2026</p>
                        <button class="btn-lofbi btn-primary-lofbi role-action" onclick="alert('Cetak Label QR dijalankan.');">Cetak Label QR</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
