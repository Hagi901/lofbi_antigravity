<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOFBI - KSOP Banten</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { width: 250px; height: 100vh; position: fixed; background: #0f172a; color: white; padding-top: 15px; z-index: 1040; }
        .sidebar a { text-decoration: none; color: #cbd5e1; padding: 12px 20px; display: block; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: #ffffff; border-left-color: #0d6efd; }
        .main-content { margin-left: 250px; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: #ffffff; border-bottom: 1px solid #e2e8f0; z-index: 1030; }
        .content-area { padding: 25px; flex-grow: 1; }
    </style>
</head>
<body>
    <!-- SIDEBAR KIRI -->
    <div class="sidebar py-3">
        <div class="text-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="text-warning fw-bold mb-0">LOFBI</h4>
            <small class="text-light">KSOP Kelas I Banten</small>
        </div>
        <div class="mt-3">
            <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie me-2"></i> Dashboard
            </a>
            <a href="{{ url('/assets') }}" class="{{ request()->is('assets*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked me-2"></i> Manajemen Aset
            </a>
            <a href="{{ url('/inventory') }}" class="{{ request()->is('inventory*') ? 'active' : '' }}">
                <i class="fa-solid fa-box-open me-2"></i> Persediaan (FIFO)
            </a>
            <a href="{{ url('/opname') }}" class="{{ request()->is('opname*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-check me-2"></i> Opname Fisik
            </a>
            <a href="{{ url('/reports') }}" class="{{ request()->is('reports*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-pdf me-2"></i> Laporan
            </a>
        </div>
    </div>

    <!-- KONTEN UTAMA KANAN -->
    <div class="main-content">
        <!-- NAVBAR ATAS -->
        <nav class="navbar navbar-expand-lg px-4 py-3 shadow-sm">
            <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Dashboard')</h5>
            
            <div class="ms-auto d-flex align-items-center">
                
                <!-- DROPDOWN NOTIFIKASI -->
                <div class="dropdown me-3">
                    <button class="btn btn-light rounded-circle position-relative border-0 d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px;">
                        <i class="fa-regular fa-bell text-secondary fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="width: 320px;">
                        <li class="px-3 py-3 border-bottom bg-light">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-bell text-warning me-2"></i>Notifikasi Terbaru</h6>
                        </li>
                        <li>
                            <a class="dropdown-item py-3 border-bottom" href="#">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 small fw-bold text-dark">Keamanan Akun Aktif</p>
                                        <p class="mb-0 small text-muted" style="font-size: 11px;">Sistem mencatat login terbaru Anda.</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><a class="dropdown-item text-center small text-primary fw-bold py-2 bg-light" href="#">Tandai Semua Dibaca</a></li>
                    </ul>
                </div>

                <!-- DROPDOWN PROFIL -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle border-0 fw-bold shadow-sm rounded-pill px-3 py-2 bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle me-2 fw-bold" style="width: 30px; height: 30px; font-size: 13px;">
                            RF
                        </div>
                        Rivaldo
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="width: 250px;">
                        <li class="px-3 py-3 bg-light border-bottom text-center">
                            <p class="mb-0 fw-bold text-dark">M. Rivaldo Firdaus</p>
                            <small class="text-muted">Operator Sistem</small>
                        </li>
                        
                        <!-- Menu Profil -->
                        <li>
                            <a class="dropdown-item py-2 mt-2" href="{{ route('profile.index') }}">
                                <i class="fa-solid fa-id-card me-2 text-primary"></i> Profil Operator
                            </a>
                        </li>
                        
                        <!-- TAMBAHAN: Menu Pengaturan Sistem -->
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('settings.index') }}">
                                <i class="fa-solid fa-gear me-2 text-secondary"></i> Pengaturan Sistem
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider my-2"></li>
                        
                        <!-- Menu Logout (Diperbarui sesuai web.php milikmu) -->
                        <li>
                            <a class="dropdown-item py-2 text-danger fw-bold" href="{{ route('logout') }}">
                                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Keluar (Logout)
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </nav>

        <!-- AREA KONTEN -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <!-- Script Bootstrap Murni -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script "Sentilan" agar Dropdown 100% Aktif -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Mencari semua tombol yang memiliki fitur dropdown
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            
            // Memaksa Bootstrap untuk mengaktifkan semuanya
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });
    </script>
</body>
</html>