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
            <a href="{{ route('upload.index') }}" class="{{ request()->is('upload*') ? 'active' : '' }}">
                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload & Validasi
            </a>

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
                
                <!-- TOMBOL DRAWER NOTIFIKASI -->
                @php
                    $unreadNotifCount = Auth::user() ? \App\Models\Notifikasi::where('penerima_id', Auth::id())->whereNull('dibaca_pada')->count() : 0;
                    $pendingOutCount  = in_array($userRole, ['validator', 'admin']) ? \App\Models\TransaksiPersediaan::where('jenis','keluar')->where('status','menunggu')->count() : 0;
                    $totalBadgeCount  = $unreadNotifCount + $pendingOutCount;
                @endphp
                <div class="me-3">
                    <button class="btn btn-light rounded-circle position-relative border-0 d-flex align-items-center justify-content-center shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#notificationDrawer" aria-controls="notificationDrawer" style="width: 40px; height: 40px;" title="Buka Pusat Notifikasi">
                        <i class="fa-regular fa-bell text-secondary fs-5"></i>
                        @if($totalBadgeCount > 0)
                        <span id="unreadNotifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            {{ $totalBadgeCount }}
                        </span>
                        @endif
                    </button>
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
                            <small class="text-muted text-capitalize">{{ Auth::user()->role ?? 'Administrator' }} - KSOP Banten</small>
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

    <!-- OFFCANVAS DRAWER NOTIFIKASI (~50% Lebar Layar) -->
    <div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="notificationDrawer" aria-labelledby="notificationDrawerLabel" style="width: 50vw; min-width: 320px; max-width: 640px;">
        <div class="offcanvas-header bg-white border-bottom p-4">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-bell fs-5"></i>
                </div>
                <div>
                    <h5 class="offcanvas-title fw-bold text-dark mb-0" id="notificationDrawerLabel">Pusat Notifikasi</h5>
                    <span class="text-muted small">Pemberitahuan alur validasi dan operasional</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('notifications.markAllRead') }}" method="POST" class="d-inline" id="formMarkAllRead">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm text-primary fw-semibold rounded-pill px-3 border shadow-none" style="font-size: 11.5px;">
                        <i class="fa-solid fa-check-double me-1"></i> Tandai Dibaca
                    </button>
                </form>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column bg-light bg-opacity-50">
            @php
                $drawerNotifs = Auth::user() ? \App\Models\Notifikasi::where('penerima_id', Auth::id())->latest()->take(25)->get() : collect();
            @endphp

            @if($drawerNotifs->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($drawerNotifs as $notif)
                        <div class="list-group-item p-3 border-bottom {{ is_null($notif->dibaca_pada) ? 'bg-white' : 'bg-transparent text-muted' }}">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="rounded-circle p-2 mt-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; 
                                    {{ $notif->jenis === 'upload_baru' ? 'background-color: #EBF5FF; color: #1E40AF;' : ($notif->jenis === 'ada_ditolak' ? 'background-color: #FEE2E2; color: #DC2626;' : 'background-color: #ECFDF5; color: #059669;') }}">
                                    @if($notif->jenis === 'upload_baru')
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    @elseif($notif->jenis === 'ada_ditolak')
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    @else
                                        <i class="fa-solid fa-circle-check"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <h6 class="mb-0 fw-bold {{ is_null($notif->dibaca_pada) ? 'text-dark' : 'text-secondary' }}" style="font-size: 13px;">
                                            {{ $notif->judul }}
                                        </h6>
                                        <span class="text-muted text-nowrap" style="font-size: 10.5px;">
                                            {{ $notif->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="mb-2 small {{ is_null($notif->dibaca_pada) ? 'text-secondary' : 'text-muted' }}" style="font-size: 12px; line-height: 1.4;">
                                        {{ $notif->pesan }}
                                    </p>
                                    @if($notif->tautan)
                                        <div>
                                            <a href="{{ $notif->tautan }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-none" style="font-size: 11px;">
                                                @if($notif->jenis === 'upload_baru')
                                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Periksa Berkas
                                                @elseif($notif->jenis === 'ada_ditolak')
                                                    <i class="fa-solid fa-filter me-1"></i> Lihat yang Ditolak
                                                @elseif($notif->jenis === 'disetujui')
                                                    <i class="fa-solid fa-boxes-stacked me-1"></i> Lihat Data
                                                @else
                                                    <i class="fa-solid fa-arrow-right me-1"></i> Buka Tautan
                                                @endif
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 my-auto text-muted">
                    <i class="fa-solid fa-bell-slash fs-1 text-secondary opacity-25 mb-2 d-block"></i>
                    <p class="small mb-0">Tidak ada notifikasi baru saat ini.</p>
                </div>
            @endif
        </div>
        <div class="p-3 bg-white border-top text-center">
            <a href="{{ route('notifications.index') }}" class="small text-primary text-decoration-none fw-bold">
                Buka Halaman Lengkap Notifikasi &rarr;
            </a>
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

            // Auto mark read saat drawer dibuka
            const drawerEl = document.getElementById('notificationDrawer');
            if (drawerEl) {
                drawerEl.addEventListener('show.bs.offcanvas', function () {
                    fetch('{{ route("notifications.markAllRead") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(() => {
                        const badge = document.getElementById('unreadNotifBadge');
                        if (badge) badge.remove();
                    }).catch(err => console.error(err));
                });
            }
        });
    </script>
</body>
</html>