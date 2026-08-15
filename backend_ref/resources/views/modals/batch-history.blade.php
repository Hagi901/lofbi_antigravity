<!-- Modal 9: Riwayat Batch FIFO -->
<div class="modal fade" id="modalBatchHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:var(--kemenhub-blue-dark);color:#fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-stack"></i> Riwayat Batch FIFO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-sm">
                <p class="mb-3">Daftar batch masuk diurutkan FIFO (batch tertua keluar duluan).</p>
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead><tr><th>Tanggal</th><th>Supplier</th><th>Jumlah Masuk</th><th>Sisa Stok Batch</th><th>Harga per Batch</th></tr></thead>
                        <tbody id="modalBatchHistoryBody">
                            <tr><td class="fs-xs">01 Mei 2026</td><td>Sinar Dunia</td><td class="fw-bold">50</td><td>12</td><td>Rp 45.000</td></tr>
                            <tr><td class="fs-xs">15 Mei 2026</td><td>Sinar Dunia</td><td class="fw-bold">40</td><td>5</td><td>Rp 45.000</td></tr>
                            <tr><td class="fs-xs">05 Jun 2026</td><td>Sinar Dunia</td><td class="fw-bold">30</td><td>0</td><td>Rp 45.000</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
