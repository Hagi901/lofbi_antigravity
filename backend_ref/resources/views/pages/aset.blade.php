<!-- SECTION 2: MANAJEMEN ASET PAGE -->
<div id="pageSection-aset" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <div class="page-header-inner">
            <div>
                <h1 class="page-title">Manajemen Aset (KBM &amp; BMN)</h1>
                <p class="page-subtitle">Pendataan inventaris barang milik negara berdasarkan kondisi, merk, dan ruangan.</p>
            </div>
            <div class="page-header-actions">
                <button class="btn-lofbi btn-primary-lofbi role-action" data-bs-toggle="modal" data-bs-target="#modalTambahAset"><i class="bi bi-plus-circle me-1"></i> Tambah Aset Baru</button>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i class="bi bi-archive-fill"></i> Daftar Aset (Model: Aset)</h2></div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>Kode Aset</th><th>Jenis Barang</th><th>Merk &amp; Model</th><th>Sub Kategori</th><th>Kondisi</th><th>Ruangan</th><th>Masa Manfaat</th><th>Metode Penyusutan</th><th>Akumulasi Penyusutan</th><th>Nilai Perolehan</th><th>Nilai Buku</th><th>QR Code</th><th>Riwayat</th><th>Dokumen</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">ELK-LAP-001</td>
                        <td>Laptop</td>
                        <td>Lenovo ThinkPad E14</td>
                        <td>Elektronik</td>
                        <td><span class="table-badge success">baik</span></td>
                        <td>Ruang Tata Usaha</td>
                        <td>4 tahun</td>
                        <td>Garis Lurus</td>
                        <td>Rp 3.000.000</td>
                        <td>Rp 12.000.000</td>
                        <td class="fw-bold text-kemenhub">Rp 9.000.000</td>
                        <td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action" data-bs-toggle="modal" data-bs-target="#modalAsetQr" onclick="openAsetQr('ELK-LAP-001')"><i class="bi bi-qr-code"></i></button></td>
                        <td><button class="btn btn-sm btn-outline-primary py-0 px-2 role-action" onclick="openAsetMutasi('ELK-LAP-001')">Riwayat Mutasi</button></td>
                        <td><button class="btn btn-sm btn-outline-warning py-0 px-2 role-action">Upload Dokumen</button></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">ELK-LAP-002</td>
                        <td>Laptop</td>
                        <td>ASUS ExpertBook B1</td>
                        <td>Elektronik</td>
                        <td><span class="table-badge warning">rusak_ringan</span></td>
                        <td>Ruang Kepala</td>
                        <td>4 tahun</td>
                        <td>Saldo Menurun</td>
                        <td>Rp 5.250.000</td>
                        <td>Rp 10.500.000</td>
                        <td class="fw-bold text-kemenhub">Rp 5.250.000</td>
                        <td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action" data-bs-toggle="modal" data-bs-target="#modalAsetQr" onclick="openAsetQr('ELK-LAP-002')"><i class="bi bi-qr-code"></i></button></td>
                        <td><button class="btn btn-sm btn-outline-primary py-0 px-2 role-action" onclick="openAsetMutasi('ELK-LAP-002')">Riwayat Mutasi</button></td>
                        <td><button class="btn btn-sm btn-outline-warning py-0 px-2 role-action">Upload Dokumen</button></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">ELK-PRN-001</td>
                        <td>Printer</td>
                        <td>Canon PIXMA G2020</td>
                        <td>Elektronik</td>
                        <td><span class="table-badge success">baik</span></td>
                        <td>Ruang Tata Usaha</td>
                        <td>5 tahun</td>
                        <td>Garis Lurus</td>
                        <td>Rp 312.500</td>
                        <td>Rp 2.500.000</td>
                        <td class="fw-bold text-kemenhub">Rp 2.187.500</td>
                        <td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action" data-bs-toggle="modal" data-bs-target="#modalAsetQr" onclick="openAsetQr('ELK-PRN-001')"><i class="bi bi-qr-code"></i></button></td>
                        <td><button class="btn btn-sm btn-outline-primary py-0 px-2 role-action" onclick="openAsetMutasi('ELK-PRN-001')">Riwayat Mutasi</button></td>
                        <td><button class="btn btn-sm btn-outline-warning py-0 px-2 role-action">Upload Dokumen</button></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">FUR-MJK-001</td>
                        <td>Meja Kerja</td>
                        <td>Olympic</td>
                        <td>Furnitur</td>
                        <td><span class="table-badge success">baik</span></td>
                        <td>Ruang Tata Usaha</td>
                        <td>8 tahun</td>
                        <td>Garis Lurus</td>
                        <td>Rp 675.000</td>
                        <td>Rp 1.800.000</td>
                        <td class="fw-bold text-kemenhub">Rp 1.125.000</td>
                        <td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action" data-bs-toggle="modal" data-bs-target="#modalAsetQr" onclick="openAsetQr('FUR-MJK-001')"><i class="bi bi-qr-code"></i></button></td>
                        <td><button class="btn btn-sm btn-outline-primary py-0 px-2 role-action" onclick="openAsetMutasi('FUR-MJK-001')">Riwayat Mutasi</button></td>
                        <td><button class="btn btn-sm btn-outline-warning py-0 px-2 role-action">Upload Dokumen</button></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">FUR-MJK-002</td>
                        <td>Meja Kerja</td>
                        <td>Olympic</td>
                        <td>Furnitur</td>
                        <td><span class="table-badge danger">rusak_berat</span></td>
                        <td>Ruang Kepala</td>
                        <td>8 tahun</td>
                        <td>Saldo Menurun</td>
                        <td>Rp 1.800.000</td>
                        <td>Rp 1.800.000</td>
                        <td class="fw-bold text-danger">Rp 0</td>
                        <td><button class="btn btn-sm btn-outline-secondary py-0 px-2 role-action" data-bs-toggle="modal" data-bs-target="#modalAsetQr" onclick="openAsetQr('FUR-MJK-002')"><i class="bi bi-qr-code"></i></button></td>
                        <td><button class="btn btn-sm btn-outline-primary py-0 px-2 role-action" onclick="openAsetMutasi('FUR-MJK-002')">Riwayat Mutasi</button></td>
                        <td><button class="btn btn-sm btn-outline-warning py-0 px-2 role-action">Upload Dokumen</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
