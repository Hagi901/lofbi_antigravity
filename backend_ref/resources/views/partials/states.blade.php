<!-- STATE SWITCHER DEMO -->
<div class="state-switcher mb-4">
    <span class="state-switcher-label"><i class="bi bi-sliders me-1"></i>Demo State:</span>
    <button class="state-btn state-btn-success active" data-state="success" type="button"><i class="bi bi-check-circle-fill me-1"></i>Success</button>
    <button class="state-btn state-btn-loading" data-state="loading" type="button"><i class="bi bi-hourglass-split me-1"></i>Loading</button>
    <button class="state-btn state-btn-empty" data-state="empty" type="button"><i class="bi bi-inbox me-1"></i>Empty</button>
    <button class="state-btn state-btn-error" data-state="error" type="button"><i class="bi bi-exclamation-octagon me-1"></i>Error</button>
    <span class="ms-auto fs-xs text-muted">Backend Status: <span id="apiStatusBadge" class="table-badge success">Online API (lofbi-api)</span></span>
</div>

<!-- STATE LOADING -->
<div id="stateLoading" style="display:none;">
    <div class="panel p-4">
        <div class="state-container" role="status">
            <div class="state-loading">
                <div class="spinner-border" style="width:44px;height:44px;border-width:3px;color:var(--kemenhub-blue-mid);margin-bottom:16px;"></div>
            </div>
            <div class="state-title">Memuat Data Sistem...</div>
            <p class="state-desc">Sedang menyinkronkan data dengan backend lofbi-api.</p>
        </div>
    </div>
</div>

<!-- STATE EMPTY -->
<div id="stateEmpty" style="display:none;">
    <div class="panel">
        <div class="state-container" role="status">
            <svg class="empty-illustration" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <rect x="30" y="60" width="140" height="100" rx="10" fill="#e8edf4"/>
                <circle cx="100" cy="38" r="22" fill="#c4cdd8"/>
            </svg>
            <div class="state-title">Data Halaman Belum Terisi</div>
            <p class="state-desc">Tabel di backend belum memiliki record. Mulai tambahkan data baru.</p>
        </div>
    </div>
</div>

<!-- STATE ERROR -->
<div id="stateError" style="display:none;">
    <div class="panel">
        <div class="state-container" role="alert">
            <i class="bi bi-exclamation-octagon-fill text-danger mb-3" style="font-size:56px;"></i>
            <div class="state-title text-danger">Gagal Terhubung ke Backend</div>
            <p class="state-desc">Koneksi ke server Laravel (http://127.0.0.1:8000/api) terputus.</p>
            <button class="btn-lofbi btn-primary-lofbi" onclick="syncWithBackendApi()"><i class="bi bi-arrow-clockwise"></i> Coba Hubungkan Ulang</button>
        </div>
    </div>
</div>
