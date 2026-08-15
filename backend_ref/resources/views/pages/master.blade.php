<!-- SECTION 9: MASTER DATA PAGE -->
<div id="pageSection-master" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <h1 class="page-title">Master Data Referensi</h1>
        <p class="page-subtitle">Pengelolaan data Ruangan (Ruangan) &amp; Kategori Barang (Kategori).</p>
    </div>

    <div class="d-grid-2 mt-4">
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i class="bi bi-geo-alt-fill"></i> Ruangan (App\Models\Ruangan)</h2></div>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead><tr><th>ID</th><th>Nama Ruangan</th><th>Gedung</th></tr></thead>
                    <tbody>
                        <tr><td>1</td><td class="fw-bold">Ruang Tata Usaha</td><td>Gedung Utama</td></tr>
                        <tr><td>2</td><td class="fw-bold">Gudang Persediaan</td><td>Gedung Utama</td></tr>
                        <tr><td>3</td><td class="fw-bold">Ruang Kepala</td><td>Gedung Utama</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i class="bi bi-tags-fill"></i> Kategori (App\Models\Kategori)</h2></div>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead><tr><th>ID</th><th>Nama Kategori</th><th>Tipe</th></tr></thead>
                    <tbody>
                        <tr><td>1</td><td class="fw-bold">ATK</td><td><span class="table-badge secondary">persediaan</span></td></tr>
                        <tr><td>2</td><td class="fw-bold">Rumah Tangga</td><td><span class="table-badge secondary">persediaan</span></td></tr>
                        <tr><td>3</td><td class="fw-bold">Elektronik</td><td><span class="table-badge primary">aset</span></td></tr>
                        <tr><td>4</td><td class="fw-bold">Furnitur</td><td><span class="table-badge primary">aset</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
