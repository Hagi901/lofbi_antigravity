<!-- SECTION 10: PENGGUNA (USERS) PAGE (5 Roles Requirement) -->
<div id="pageSection-users" class="page-section" style="display:none;">
    <div class="page-header fade-in">
        <h1 class="page-title">Manajemen Pengguna (User Accounts)</h1>
        <p class="page-subtitle">Daftar pengguna dengan otorisasi role internal (Administrator, Operator, Validator, Viewer, Pimpinan).</p>
    </div>
    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i class="bi bi-people-fill"></i> User System (App\Models\User)</h2></div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead><tr><th>Nama</th><th>Email</th><th>Role Access</th><th>Status Token</th></tr></thead>
                <tbody>
                    <tr><td class="fw-bold">Admin LOFBI</td><td>admin@lofbi.test</td><td><span class="table-badge primary">Administrator</span></td><td><span class="table-badge success">Aktif (Sanctum)</span></td></tr>
                    <tr><td class="fw-bold">Operator LOFBI</td><td>operator@lofbi.test</td><td><span class="table-badge warning">Operator</span></td><td><span class="table-badge success">Aktif</span></td></tr>
                    <tr><td class="fw-bold">Validator LOFBI</td><td>validator@lofbi.test</td><td><span class="table-badge success">Validator</span></td><td><span class="table-badge success">Aktif</span></td></tr>
                    <tr><td class="fw-bold">Viewer LOFBI</td><td>viewer@lofbi.test</td><td><span class="table-badge secondary">Viewer</span></td><td><span class="table-badge success">Aktif</span></td></tr>
                    <tr><td class="fw-bold">Pimpinan KSOP</td><td>pimpinan@lofbi.test</td><td><span class="table-badge info">Pimpinan</span></td><td><span class="table-badge success">Aktif</span></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
