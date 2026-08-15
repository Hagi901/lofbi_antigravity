<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="LOFBI — Sistem Laporan Opname Fisik Barang & Inventarisasi KSOP Kelas I Banten, Kementerian Perhubungan RI">
    <title>@yield('title', 'LOFBI — KSOP Kelas I Banten | Sistem Informasi Inventarisasi')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- LOFBI CSS -->
    <link rel="stylesheet" href="{{ asset('css/lofbi.css') }}">
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

<div class="app-wrapper">

    <!-- ======================== SIDEBAR NAVIGASI ======================== -->
    @include('partials.sidebar')

    <!-- ======================== MAIN AREA ======================== -->
    <div class="app-main" id="appMain" role="main">

        <!-- TOPBAR -->
        @include('partials.topbar')

        <!-- PAGE CONTENT -->
        <div class="app-content">

            <!-- STATE SWITCHER DEMO -->
            @include('partials.states')

            <!-- ALL PAGE SECTIONS -->
            @yield('content')

        </div><!-- /.app-content -->

        <!-- FOOTER -->
        @include('partials.footer')

    </div>
</div>

<!-- ======================== MODALS ======================== -->
@include('modals.aset-tambah')
@include('modals.barang-masuk')
@include('modals.pengajuan-keluar')
@include('modals.opname-baru')
@include('modals.export-laporan')
@include('modals.transfer-masuk')
@include('modals.aset-qr')
@include('modals.aset-mutasi')
@include('modals.batch-history')

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js 4 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<!-- LOFBI API Bridge & Main JS -->
<script src="{{ asset('js/lofbi-api.js') }}"></script>
<script src="{{ asset('js/lofbi.js') }}"></script>

<!-- SPA Navigation & Dynamic Role Script -->
<script>
function switchSection(targetNav) {
    document.querySelectorAll('.page-section').forEach(sec => sec.style.display = 'none');
    const targetEl = document.getElementById(`pageSection-${targetNav}`);
    if (targetEl) {
        targetEl.style.display = 'block';
        targetEl.classList.add('fade-in');
    }

    document.querySelectorAll('.nav-item-link').forEach(link => {
        link.classList.remove('active');
        if (link.dataset.nav === targetNav) link.classList.add('active');
    });

    const titleMap = {
        'dashboard': 'Dashboard',
        'aset': 'Manajemen Aset',
        'persediaan': 'Persediaan & Batch',
        'opname': 'Opname Fisik',
        'monitoring': 'Monitoring Aset',
        'laporan': 'Laporan BAOP & DBR',
        'audit': 'Audit Trail',
        'approval': 'Approval Pengajuan',
        'master': 'Master Data',
        'users': 'Pengguna (Users)',
        'settings': 'Pengaturan Sistem'
    };
    const bc = document.getElementById('breadcrumbActive');
    if (bc) bc.textContent = titleMap[targetNav] || 'Dashboard';
}

document.querySelectorAll('[data-nav]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        switchSection(this.dataset.nav);
    });
});

function openAsetQr(code) {
    document.getElementById('modalAsetQrTitle').textContent = `QR Code Aset ${code}`;
    document.getElementById('modalAsetQrKode').textContent = code;
    // TODO-BACKEND: GET /api/aset/{code}/qr — fetch QR payload dari backend
}

function openAsetMutasi(code) {
    // TODO-BACKEND: GET /api/aset/{code}/mutasi — fetch riwayat mutasi aset dari database
    const dummyData = {
        'ELK-LAP-001': [
            ['01 Jul 2026', 'Gudang Persediaan', 'baik', 'Masuk awal ke gudang'],
            ['03 Agt 2026', 'Ruang Tata Usaha', 'baik', 'Dipindahkan oleh Operator LOFBI'],
            ['05 Agt 2026', 'Ruang Tata Usaha', 'baik', 'Verifikasi kondisi terakhir']
        ],
        'ELK-LAP-002': [
            ['15 Jun 2026', 'Gudang Persediaan', 'baik', 'Masuk ke gudang'],
            ['20 Jul 2026', 'Ruang Kepala', 'baik', 'Dipindahkan ke Ruang Kepala'],
            ['01 Agt 2026', 'Ruang Kepala', 'rusak_ringan', 'Terdeteksi kerusakan baterai']
        ],
        'ELK-PRN-001': [
            ['10 Mei 2026', 'Gudang Persediaan', 'baik', 'Masuk ke gudang dari pengadaan'],
            ['12 Jun 2026', 'Ruang Tata Usaha', 'baik', 'Dipindahkan untuk kebutuhan TU']
        ],
        'FUR-MJK-001': [
            ['01 Jan 2024', 'Ruang Tata Usaha', 'baik', 'Pengadaan aset baru'],
            ['05 Agt 2026', 'Ruang Tata Usaha', 'baik', 'Verifikasi tahunan']
        ],
        'FUR-MJK-002': [
            ['01 Jan 2024', 'Ruang Tata Usaha', 'baik', 'Pengadaan aset baru'],
            ['15 Feb 2026', 'Ruang Kepala', 'baik', 'Dipindah ke Ruang Kepala'],
            ['02 Agt 2026', 'Ruang Kepala', 'rusak_berat', 'Kerusakan struktural dilaporkan oleh Validator']
        ]
    };
    const rows = dummyData[code] || [['—', '—', '—', 'Data tidak ditemukan']];
    document.querySelector('#modalAsetMutasi .modal-title').textContent = `Riwayat Mutasi ${code}`;
    document.getElementById('modalAsetMutasiBody').innerHTML = rows.map(r =>
        `<tr><td class="fs-xs">${r[0]}</td><td>${r[1]}</td><td>${r[2]}</td><td>${r[3]}</td></tr>`
    ).join('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAsetMutasi')).show();
}

function openBatchHistory(itemName) {
    // TODO-BACKEND: GET /api/persediaan/{id}/batch — fetch riwayat batch dari database (FIFO order)
    const batchData = {
        'Pulpen': [
            ['01 Mei 2026', 'Sinar Dunia', 50, 12, 'Rp 2.500'],
            ['15 Jun 2026', 'Fajar Abadi', 40, 38, 'Rp 2.600'],
            ['05 Agu 2026', 'Sinar Dunia', 60, 60, 'Rp 2.550']
        ],
        'Kertas A4': [
            ['10 Apr 2026', 'Kertas Nusantara', 20, 2, 'Rp 48.000'],
            ['01 Jun 2026', 'Sinar Dunia', 15, 6, 'Rp 50.000'],
            ['20 Jul 2026', 'Kertas Nusantara', 10, 10, 'Rp 49.000']
        ],
        'Tinta Printer': [
            ['05 Mar 2026', 'Canon Authorized', 6, 0, 'Rp 85.000'],
            ['10 Jun 2026', 'Canon Authorized', 4, 0, 'Rp 88.000'],
            ['01 Agu 2026', 'Toko ATK Mandiri', 3, 1, 'Rp 90.000']
        ]
    };
    const rows = batchData[itemName] || [['—', '—', '—', '—', '—']];
    document.querySelector('#modalBatchHistory .modal-title').textContent = `Riwayat Batch FIFO: ${itemName}`;
    document.getElementById('modalBatchHistoryBody').innerHTML = rows.map(r =>
        `<tr><td class="fs-xs">${r[0]}</td><td>${r[1]}</td><td class="fw-bold">${r[2]}</td><td>${r[3]}</td><td>${r[4]}</td></tr>`
    ).join('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBatchHistory')).show();
}

function switchUserRole(role) {
    // TODO-BACKEND: Simulasi 5 Role Permission (admin, operator, validator, viewer, pimpinan)
    const roleMap = {
        admin: { label: 'Administrator', badge: 'primary', name: 'Admin LOFBI', avatar: 'AL' },
        operator: { label: 'Operator', badge: 'warning', name: 'Operator LOFBI', avatar: 'OP' },
        validator: { label: 'Validator', badge: 'success', name: 'Validator LOFBI', avatar: 'VL' },
        viewer: { label: 'Viewer', badge: 'secondary', name: 'Viewer LOFBI', avatar: 'VW' },
        pimpinan: { label: 'Pimpinan', badge: 'info', name: 'Pimpinan KSOP', avatar: 'PK' }
    };
    const info = roleMap[role] || roleMap.admin;
    const roleText = `Role: ${info.label}`;

    document.getElementById('currentRoleText').textContent = roleText;
    document.getElementById('sidebarUserName').textContent = info.name;
    document.getElementById('sidebarUserRole').textContent = roleText;
    document.getElementById('topbarUserName').textContent = info.name;
    document.getElementById('dropdownUserName').textContent = info.name;
    const welcomeName = document.getElementById('welcomeUserName');
    if (welcomeName) welcomeName.textContent = info.name;
    const welcomeRole = document.getElementById('welcomeUserRole');
    if (welcomeRole) welcomeRole.textContent = roleText;

    document.getElementById('sidebarAvatar').textContent = info.avatar;
    document.getElementById('topbarAvatar').textContent = info.avatar;

    // Untuk role Viewer, sembunyikan semua tombol aksi (tambah, edit, delete, approve, export)
    document.querySelectorAll('.role-action').forEach(el => {
        el.style.display = role === 'viewer' ? 'none' : '';
    });
}
</script>
@stack('scripts')
</body>
</html>
