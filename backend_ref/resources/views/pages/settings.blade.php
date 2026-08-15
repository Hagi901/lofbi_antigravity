<!-- SECTION 11: PENGATURAN PAGE -->
<div id="pageSection-settings" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <h1 class="page-title">Pengaturan Sistem</h1>
        <p class="page-subtitle">Konfigurasi dasar instansi, preferensi sistem, manajemen role, dan cadangan data.</p>
    </div>
    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i class="bi bi-gear-fill"></i> Pengaturan Utama (Model: Setting)</h2></div>
        <div class="panel-body">
            <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-profil-tab" data-bs-toggle="tab" data-bs-target="#tab-profil" type="button" role="tab">Profil Instansi</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="tab-preferensi-tab" data-bs-toggle="tab" data-bs-target="#tab-preferensi" type="button" role="tab">Preferensi Sistem</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="tab-role-tab" data-bs-toggle="tab" data-bs-target="#tab-role" type="button" role="tab">Role &amp; Permission</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="tab-backup-tab" data-bs-toggle="tab" data-bs-target="#tab-backup" type="button" role="tab">Backup Data</button></li>
            </ul>
            <div class="tab-content p-3 border-top">
                <div class="tab-pane fade show active" id="tab-profil" role="tabpanel">
                    <form onsubmit="event.preventDefault(); alert('Profil Instansi Disimpan! Endpoint: POST /api/settings');">
                        <div class="mb-3"><label class="form-label fw-semibold">Nama KSOP</label><input type="text" class="form-control" value="KSOP Kelas I Banten"></div>
                        <div class="mb-3"><label class="form-label fw-semibold">Alamat Instansi</label><textarea class="form-control" rows="3">Jl. Yos Sudarso No. 1, Bandar Lampung</textarea></div>
                        <div class="mb-3"><label class="form-label fw-semibold">Logo Instansi</label><input class="form-control" type="text" placeholder="URL atau path logo" value="/public/images/logo-ksop.png"></div>
                        <button type="submit" class="btn-lofbi btn-primary-lofbi role-action">Simpan Profil Instansi</button>
                    </form>
                </div>
                <div class="tab-pane fade" id="tab-preferensi" role="tabpanel">
                    <form onsubmit="event.preventDefault(); alert('Preferensi Disimpan! Endpoint: POST /api/settings');">
                        <div class="mb-3"><label class="form-label fw-semibold">Format Tanggal</label><select class="form-select"><option>DD/MM/YYYY</option><option>MM/DD/YYYY</option><option selected>DD MMM YYYY</option></select></div>
                        <div class="mb-3"><label class="form-label fw-semibold">Tahun Anggaran Aktif</label><select class="form-select"><option>2024</option><option>2025</option><option selected>2026</option></select></div>
                        <button type="submit" class="btn-lofbi btn-primary-lofbi role-action">Simpan Preferensi</button>
                    </form>
                </div>
                <div class="tab-pane fade" id="tab-role" role="tabpanel">
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead><tr><th>Role</th><th>Deskripsi</th><th>Permission Contoh</th></tr></thead>
                            <tbody>
                                <tr><td class="fw-bold">Administrator</td><td>Pengelola penuh sistem</td><td>CRUD aset, persediaan, laporan, users</td></tr>
                                <tr><td class="fw-bold">Operator</td><td>Input dan update operasional</td><td>Tambah aset, catat persediaan, monitoring</td></tr>
                                <tr><td class="fw-bold">Validator</td><td>Verifikasi data dan laporan</td><td>Approve opname, validasi persediaan</td></tr>
                                <tr><td class="fw-bold">Viewer</td><td>Hanya melihat data</td><td>Read-only dashboard, aset, laporan</td></tr>
                                <tr><td class="fw-bold">Pimpinan</td><td>Review lengkap dan pengambilan keputusan</td><td>Lihat laporan, audit trail, persetujuan</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-backup" role="tabpanel">
                    <p class="fs-sm text-muted">Backup data dummy akan memanggil endpoint cadangan data backend Laravel (`POST /api/backup`).</p>
                    <button class="btn-lofbi btn-gold-lofbi role-action" onclick="alert('Backup data dijalankan. Endpoint: POST /api/backup');">Backup Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</div>
