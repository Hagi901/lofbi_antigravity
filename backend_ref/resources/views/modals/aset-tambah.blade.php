<!-- Modal 1: Tambah Aset -->
<div class="modal fade" id="modalTambahAset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header" style="background:var(--kemenhub-blue-dark);color:#fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-plus-circle me-1"></i> Form Tambah Aset Baru (POST /api/aset)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-sm">
                <form id="formTambahAset" onsubmit="handleTambahAsetSubmit(event)">
                    <div class="row gx-2">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-semibold">Kode Aset</label>
                            <input type="text" class="form-control" placeholder="Contoh: ELK-LAP-003" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-semibold">Jenis Barang</label>
                            <select class="form-select">
                                <option>Laptop</option>
                                <option>Printer</option>
                                <option>Meja Kerja</option>
                                <option>Kursi</option>
                                <option>AC</option>
                                <option>Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Merk &amp; Model</label>
                        <input type="text" class="form-control" placeholder="Contoh: Dell Latitude 3420" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nilai Perolehan (Rp)</label>
                        <input type="number" class="form-control" placeholder="15000000" required>
                    </div>
                    <div class="row gx-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Sub Kategori</label>
                            <select class="form-select"><option>Elektronik</option><option>Furnitur</option><option>ATK</option><option>Rumah Tangga</option></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kondisi Aset</label>
                            <select class="form-select">
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row gx-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Masa Manfaat (tahun)</label>
                            <input type="number" class="form-control" placeholder="5" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Metode Penyusutan</label>
                            <select class="form-select">
                                <option>Garis Lurus</option>
                                <option>Saldo Menurun</option>
                            </select>
                            <!-- TODO-BACKEND: perhitungan akumulasi penyusutan otomatis oleh backend -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ruangan Penempatan</label>
                        <select class="form-select"><option value="1">Ruang Tata Usaha</option><option value="2">Gudang Persediaan</option><option value="3">Ruang Kepala</option></select>
                    </div>
                    <button type="submit" class="btn-lofbi btn-primary-lofbi w-100 justify-content-center py-2 role-action">Simpan Aset Baru</button>
                </form>
            </div>
        </div>
    </div>
</div>
