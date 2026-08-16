<!-- SECTION 1: DASHBOARD PAGE -->
<div id="pageSection-dashboard" class="page-section">
    <div class="page-header fade-in">
        <div class="page-header-inner">
            <div>
                <h1 class="page-title">Dashboard Overview</h1>
                <p class="page-subtitle">Selamat datang 123, <strong id="welcomeUserName">Admin LOFBI</strong> (<span id="welcomeUserRole">Role: Administrator</span>). Terintegrasi dengan KSOP Kelas I Banten.</p>
            </div>
            <div class="page-header-actions">
                <button class="btn-lofbi btn-outline-lofbi" onclick="syncWithBackendApi()"><i class="bi bi-arrow-clockwise"></i> Sync API</button>
                <button class="btn-lofbi btn-gold-lofbi role-action" data-bs-toggle="modal" data-bs-target="#modalExportLaporan"><i class="bi bi-download"></i> Export Laporan</button>
            </div>
        </div>
    </div>

    <div class="filter-bar">
        <label for="filterTahun" class="fs-sm fw-semibold">Tahun Anggaran:</label>
        <select id="filterTahun" class="filter-select"><option>2024</option><option>2025</option><option selected>2026</option></select>
        <label for="filterRuangan" class="fs-sm fw-semibold">Ruangan:</label>
        <select id="filterRuangan" class="filter-select"><option value="">Semua Ruangan</option><option value="1" selected>Ruang Tata Usaha</option><option value="2">Gudang Persediaan</option><option value="3">Ruang Kepala</option></select>
        <button class="btn-lofbi btn-primary-lofbi"><i class="bi bi-funnel-fill"></i> Terapkan</button>
    </div>

    <section aria-label="Statistik ringkasan">
        <div class="stat-cards-grid">
            <div class="stat-card primary" onclick="switchSection('aset')" style="cursor:pointer">
                <div class="stat-card-header"><div class="stat-card-icon"><i class="bi bi-archive-fill"></i></div><div class="stat-card-trend up">Total tercatat</div></div>
                <div class="stat-card-value" id="valTotalAsset">{{ number_format($totalAset, 0, ',', '.') }}</div>
                <div class="stat-card-label">Total Asset</div>
            </div>
            <div class="stat-card teal" onclick="switchSection('laporan')" style="cursor:pointer">
                <div class="stat-card-header"><div class="stat-card-icon"><i class="bi bi-cash-coin"></i></div><div class="stat-card-trend up">Nilai buku saat ini</div></div>
                <div class="stat-card-value" id="valNilaiBuku" style="font-size:18px;">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</div>
                <div class="stat-card-label">Total Nilai Buku</div>
            </div>
            <div class="stat-card warning" onclick="switchSection('approval')" style="cursor:pointer">
                <div class="stat-card-header"><div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div><div class="stat-card-trend down">Status: Menunggu</div></div>
                <div class="stat-card-value" id="valPendingApproval">{{ $pengajuanMenunggu }}</div>
                <div class="stat-card-label">Pengajuan Menunggu</div>
            </div>
            <div class="stat-card orange" onclick="switchSection('persediaan')" style="cursor:pointer">
                <div class="stat-card-header"><div class="stat-card-icon"><i class="bi bi-exclamation-triangle-fill"></i></div><div class="stat-card-trend up">&lt; Stok Minimum</div></div>
                <div class="stat-card-value" id="valStokMenipis">{{ $stokMenipis }}</div>
                <div class="stat-card-label">Stok Menipis</div>
            </div>
            <div class="stat-card danger" onclick="switchSection('aset')" style="cursor:pointer">
                <div class="stat-card-header"><div class="stat-card-icon"><i class="bi bi-tools"></i></div><div class="stat-card-trend flat">Ringan &amp; Berat</div></div>
                <div class="stat-card-value" id="valAssetRusak">{{ $asetRusak }}</div>
                <div class="stat-card-label">Asset Rusak</div>
            </div>
            <div class="stat-card secondary" onclick="switchSection('persediaan')" style="cursor:pointer">
                <div class="stat-card-header"><div class="stat-card-icon"><i class="bi bi-boxes"></i></div><div class="stat-card-trend up">Kategori Aktif</div></div>
                <div class="stat-card-value" id="valTotalPersediaan">{{ $totalJenisPersediaan }}</div>
                <div class="stat-card-label">Jenis Persediaan</div>
            </div>
            <div class="stat-card success" onclick="switchSection('aset')" style="cursor:pointer">
                <div class="stat-card-header"><div class="stat-card-icon"><i class="bi bi-check-circle-fill"></i></div><div class="stat-card-trend up">{{ $persenAsetBaik }}% Total</div></div>
                <div class="stat-card-value" id="valAssetBaik">{{ number_format($asetBaik, 0, ',', '.') }}</div>
                <div class="stat-card-label">Asset Kondisi Baik</div>
            </div>
        </div>
    </section>

    <section class="d-grid-2 mt-24">
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i class="bi bi-graph-up"></i>Kondisi Asset</h2></div>
            <div class="panel-body"><div class="chart-container"><canvas id="chartTrendAsset"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i class="bi bi-pie-chart-fill"></i>Distribusi Persediaan</h2></div>
            <div class="panel-body"><div class="chart-container"><canvas id="chartPersediaan"></canvas></div></div>
        </div>
    </section>

    <section class="d-grid-2 mt-24">
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i class="bi bi-lightning-charge-fill"></i>Quick Action</h2></div>
            <div class="panel-body">
                <div class="quick-actions-grid">
                    <button class="quick-action-btn qa-blue role-action" data-bs-toggle="modal" data-bs-target="#modalBarangMasuk"><i class="bi bi-box-arrow-in-down"></i>Barang Masuk</button>
                    <button class="quick-action-btn qa-teal role-action" data-bs-toggle="modal" data-bs-target="#modalOpnameBaru"><i class="bi bi-clipboard2-plus-fill"></i>Opname Baru</button>
                    <button class="quick-action-btn qa-info role-action" data-bs-toggle="modal" data-bs-target="#modalTransferMasuk"><i class="bi bi-arrow-left-right"></i>Transfer Masuk</button>
                    <button class="quick-action-btn qa-gold role-action" data-bs-toggle="modal" data-bs-target="#modalExportLaporan"><i class="bi bi-file-earmark-arrow-down-fill"></i>Export Laporan</button>
                    <button class="quick-action-btn qa-success role-action" data-bs-toggle="modal" data-bs-target="#modalTambahAset"><i class="bi bi-plus-circle-fill"></i>Tambah Asset</button>
                    <button class="quick-action-btn qa-danger" onclick="switchSection('persediaan')"><i class="bi bi-exclamation-triangle-fill"></i>Stok Menipis</button>
                    <button class="quick-action-btn qa-orange" onclick="switchSection('approval')"><i class="bi bi-check2-all"></i>Approval</button>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i class="bi bi-clock-history"></i>Transaksi Terbaru</h2></div>
            <div class="panel-body p-0">
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead><tr><th>Tanggal</th><th>Pengguna</th><th>Aksi</th><th>Modul</th></tr></thead>
                        <tbody>
                            @forelse($transaksiTerbaru as $log)
                            <tr>
                                <td class="fs-xs">{{ $log->created_at->translatedFormat('d M, H:i') }}</td>
                                <td>{{ $log->user_name }}</td>
                                <td class="fs-xs">{{ $log->aksi }}</td>
                                <td><span class="table-badge">{{ $log->modul }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center fs-xs text-muted py-3">Belum ada aktivitas tercatat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    window.LOFBI_DASHBOARD_DATA = {
        distribusiKondisi: @json($distribusiKondisi),
        distribusiPersediaan: @json($distribusiPersediaan)
    };
</script>