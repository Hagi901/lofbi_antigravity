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
    <!-- Google Fonts (Poppins) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .sidebar { width: 250px; height: 100vh; position: fixed; background: #0f172a; color: white; padding-top: 15px; z-index: 1040; transition: transform 0.3s ease-in-out; }
        .sidebar a { text-decoration: none; color: #cbd5e1; padding: 12px 20px; display: block; border-left: 4px solid transparent; transition: 0.3s; font-size: 13.5px; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: #ffffff; border-left-color: #0d6efd; }
        .main-content { margin-left: 250px; min-height: 100vh; display: flex; flex-direction: column; transition: margin-left 0.3s ease; }
        .navbar { background: #ffffff; border-bottom: 1px solid #e2e8f0; z-index: 1030; }
        .content-area { padding: 25px; flex-grow: 1; }

        /* Responsive Mobile Styles */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 0 0 25px rgba(0,0,0,0.5);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .content-area {
                padding: 15px !important;
            }
        }
        .sidebar-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 1035;
            display: none;
        }
        .sidebar-backdrop.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- BACKDROP MOBILE -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <!-- SIDEBAR KIRI -->
    <div class="sidebar py-3">
        <div class="text-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="text-warning fw-bold mb-0">LOFBI</h4>
            <small class="text-light">KSOP Kelas I Banten</small>
        </div>
        @php $userRole = Auth::user()->role ?? 'viewer'; @endphp
        <div class="mt-3">
            <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie me-2"></i> Dashboard
            </a>
            <a href="{{ url('/assets') }}" class="{{ request()->is('assets*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked me-2"></i> Manajemen Aset
            </a>
            <a href="{{ url('/inventory') }}" class="{{ request()->is('inventory') || request()->is('inventory/in*') || request()->is('inventory/out*') ? 'active' : '' }}">
                <i class="fa-solid fa-box-open me-2"></i> Persediaan (FIFO)
            </a>
            @if(in_array($userRole, ['validator', 'admin']))
            @php
                $pendingCount = \App\Models\TransaksiPersediaan::where('jenis','keluar')->where('status','menunggu')->count();
            @endphp
            <a href="{{ route('inventory.pengajuan') }}" class="{{ request()->is('inventory/pengajuan') ? 'active' : '' }}" style="padding-left: 36px;">
                <i class="fa-solid fa-stamp me-2 text-warning"></i>
                <span class="small">Validasi Barang Keluar</span>
                @if($pendingCount > 0)
                    <span class="badge bg-warning text-dark ms-1 rounded-pill" style="font-size: 10px;">{{ $pendingCount }}</span>
                @endif
            </a>
            @endif
            <a href="{{ url('/opname') }}" class="{{ request()->is('opname*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-check me-2"></i> Opname Fisik
            </a>
            <a href="{{ url('/reports') }}" class="{{ request()->is('reports*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-pdf me-2"></i> Laporan
            </a>
            @if(in_array($userRole, ['admin', 'operator']))
            <a href="{{ route('import.index') }}" class="{{ request()->is('import*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-import me-2"></i> Input File Dokumen
            </a>
            @endif
            @if($userRole === 'admin')
            <a href="{{ route('settings.index') }}" class="{{ request()->is('settings*') ? 'active' : '' }}">
                <i class="fa-solid fa-sliders me-2"></i> Pengaturan Sistem
            </a>
            @endif
        </div>
    </div>


    <!-- KONTEN UTAMA KANAN -->
    <div class="main-content">
        <!-- NAVBAR ATAS -->
        <nav class="navbar navbar-expand-lg px-3 px-lg-4 py-3 shadow-sm">
            <div class="d-flex align-items-center">
                <button class="btn btn-light border-0 me-2 d-lg-none rounded-circle d-flex align-items-center justify-content-center shadow-sm" id="mobileSidebarToggle" type="button" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-bars fs-5 text-dark"></i>
                </button>
                <h5 class="mb-0 fw-bold text-dark fs-6 fs-md-5">@yield('page_title', 'Dashboard')</h5>
            </div>
            
            <div class="ms-auto d-flex align-items-center">
                
                <!-- DROPDOWN NOTIFIKASI -->
                @php
                    $pendingNotifCount = \App\Models\TransaksiPersediaan::where('jenis','keluar')->where('status','menunggu')->count();
                @endphp
                <div class="dropdown me-3">
                    <button class="btn btn-light rounded-circle position-relative border-0 d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px;">
                        <i class="fa-regular fa-bell text-secondary fs-5"></i>
                        @if($pendingNotifCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            {{ $pendingNotifCount }}
                        </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="width: 320px;">
                        <li class="px-3 py-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-bell text-warning me-2"></i>Notifikasi</h6>
                            <a href="{{ route('notifications.index') }}" class="small text-decoration-none fw-bold">Semua &rarr;</a>
                        </li>
                        @if($pendingNotifCount > 0)
                        <li>
                            <a class="dropdown-item py-3 border-bottom" href="{{ route('notifications.index') }}">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning bg-opacity-25 text-warning rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fa-solid fa-clock text-warning"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 small fw-bold text-dark">{{ $pendingNotifCount }} Pengajuan Menunggu</p>
                                        <p class="mb-0 small text-muted" style="font-size: 11px;">Barang keluar menunggu validasi.</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @else
                        <li class="px-3 py-3 text-center text-muted small">
                            <i class="fa-solid fa-circle-check text-success me-1"></i> Tidak ada notifikasi baru.
                        </li>
                        @endif
                        <li><a class="dropdown-item text-center small text-primary fw-bold py-2 bg-light" href="{{ route('notifications.index') }}">Buka Pusat Notifikasi</a></li>
                    </ul>
                </div>

                <!-- DROPDOWN PROFIL -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle border-0 fw-bold shadow-sm rounded-pill px-3 py-2 bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle me-2 fw-bold" style="width: 30px; height: 30px; font-size: 13px;">
                            {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        {{ explode(' ', Auth::user()->name ?? 'Admin')[0] }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="width: 250px;">
                        <li class="px-3 py-3 bg-light border-bottom text-center">
                            <p class="mb-0 fw-bold text-dark">{{ Auth::user()->name ?? 'Pengguna LOFBI' }}</p>
                            <small class="text-muted text-capitalize">{{ Auth::user()->role ?? 'Administrator' }} — KSOP Banten</small>
                        </li>
                        
                        <!-- Menu Profil -->
                        <li>
                            <a class="dropdown-item py-2 mt-2" href="{{ route('profile.index') }}">
                                <i class="fa-solid fa-user-gear me-2 text-primary"></i> Data Profil
                            </a>
                        </li>
                        
                        <!-- Menu Pengaturan -->
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('settings.index') }}">
                                <i class="fa-solid fa-sliders me-2 text-secondary"></i> Pengaturan
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider my-2"></li>
                        
                        <!-- Menu Logout -->
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar (Logout)
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

    <!-- Script Bootstrap & Interaktivitas -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Dropdown initialization
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // Mobile Sidebar Toggle & Backdrop
            const toggleBtn = document.getElementById('mobileSidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (toggleBtn && sidebar && backdrop) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                });

                backdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>