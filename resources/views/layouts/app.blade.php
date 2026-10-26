<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Dashboard') - LOFBI KSOP Kelas I Banten</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (Poppins) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- LOFBI Centralized Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Mobile Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    @php
        $userRole = Auth::user()->role ?? '';
        $knownRoles = ['admin', 'operator', 'validator', 'pimpinan'];
        $pendingOutCount = in_array($userRole, ['admin', 'validator']) 
            ? \App\Models\TransaksiPersediaan::where('jenis', 'keluar')->where('status', 'menunggu')->count() 
            : 0;
        $unreadNotifCount = Auth::user() 
            ? \App\Models\Notifikasi::where('penerima_id', Auth::id())->whereNull('dibaca_pada')->count() 
            : 0;
        $totalBadgeCount = $unreadNotifCount + $pendingOutCount;
    @endphp

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <!-- Sidebar Brand -->
        <a href="{{ url('/dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="fa-solid fa-anchor"></i>
            </div>
            <div>
                <h2 class="sidebar-brand-title">LOFBI</h2>
                <p class="sidebar-brand-subtitle">KSOP KELAS I BANTEN</p>
            </div>
        </a>

        <!-- Sidebar Navigation -->
        <div class="sidebar-nav">
            @if(in_array($userRole, $knownRoles))
                <div class="sidebar-section-label">Menu Utama</div>

                <!-- Dashboard -->
                <a href="{{ url('/dashboard') }}" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie nav-icon"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Manajemen Aset -->
                <a href="{{ url('/assets') }}" class="sidebar-link {{ request()->is('assets*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked nav-icon"></i>
                    <span>Manajemen Aset</span>
                </a>

                <!-- Persediaan (FIFO) Group -->
                <div class="sidebar-group">
                    <a href="{{ url('/inventory') }}" class="sidebar-link {{ request()->is('inventory') || request()->is('inventory/in*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box-open nav-icon"></i>
                        <span class="flex-grow-1">Persediaan (FIFO)</span>
                        @if($pendingOutCount > 0 && in_array($userRole, ['admin', 'validator']))
                            <span class="badge bg-warning text-dark rounded-pill px-2" style="font-size: 10px;">{{ $pendingOutCount }}</span>
                        @endif
                    </a>

                    @if(in_array($userRole, ['admin', 'operator', 'validator']))
                        <div class="sidebar-sub-menu {{ request()->is('inventory*') ? 'd-block' : 'd-none' }}">
                            @if(in_array($userRole, ['admin', 'operator']))
                                <a href="{{ route('inventory.out.create') }}" class="sidebar-sub-link {{ request()->is('inventory/out*') ? 'active' : '' }}">
                                    <span>Ajukan Barang Keluar</span>
                                </a>
                            @endif

                            @if(in_array($userRole, ['admin', 'validator']))
                                <a href="{{ route('inventory.pengajuan') }}" class="sidebar-sub-link {{ request()->is('inventory/pengajuan*') ? 'active' : '' }}">
                                    <span>Validasi Barang Keluar</span>
                                    @if($pendingOutCount > 0)
                                        <span class="badge bg-warning text-dark rounded-pill px-2" style="font-size: 10px;">{{ $pendingOutCount }}</span>
                                    @endif
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Opname Fisik -->
                @if(in_array($userRole, ['admin', 'operator', 'validator']))
                    <a href="{{ url('/opname') }}" class="sidebar-link {{ request()->is('opname*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-check nav-icon"></i>
                        <span>Opname Fisik</span>
                    </a>
                @endif

                <!-- Laporan -->
                <a href="{{ url('/reports') }}" class="sidebar-link {{ request()->is('reports*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-lines nav-icon"></i>
                    <span>Laporan</span>
                </a>

                <!-- Upload & Validasi -->
                @if(in_array($userRole, ['admin', 'operator', 'validator']))
                    <div class="sidebar-section-label">Sinkronisasi Data</div>
                    <a href="{{ route('upload.index') }}" class="sidebar-link {{ request()->is('upload*') ? 'active' : '' }}">
                        <i class="fa-solid fa-cloud-arrow-up nav-icon"></i>
                        <span>Upload & Validasi</span>
                    </a>
                @endif

                <!-- Pengaturan Sistem (Admin Only) -->
                @if($userRole === 'admin')
                    <div class="sidebar-section-label">Administrasi</div>
                    <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->is('settings*') ? 'active' : '' }}">
                        <i class="fa-solid fa-sliders nav-icon"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                @endif
            @endif
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOP NAVBAR -->
        <header class="navbar-top">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light d-lg-none" id="mobileSidebarToggle" type="button" aria-label="Buka navigasi">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="d-none d-sm-block">
                    <span class="badge badge-app-primary">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>KSOP Kelas I Banten</span>
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Notification Bell Button -->
                <button class="btn btn-light position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#notificationDrawer" aria-controls="notificationDrawer" title="Pusat Notifikasi">
                    <i class="fa-regular fa-bell"></i>
                    @if($totalBadgeCount > 0)
                        <span id="unreadNotifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                            {{ $totalBadgeCount }}
                        </span>
                    @endif
                </button>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 11px;">
                            {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        <span class="fw-medium d-none d-md-inline">{{ explode(' ', Auth::user()->name ?? 'Admin')[0] }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" style="min-width: 230px;">
                        <li class="px-3 py-2 border-bottom bg-light">
                            <p class="mb-0 fw-bold text-dark text-truncate">{{ Auth::user()->name ?? 'Pengguna LOFBI' }}</p>
                            <small class="text-muted text-capitalize">{{ Auth::user()->role ?? 'Administrator' }} - KSOP Banten</small>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('profile.index') }}">
                                <i class="fa-solid fa-user-gear me-2 text-primary"></i> Data Profil
                            </a>
                        </li>
                        @if($userRole === 'admin')
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('settings.index') }}">
                                    <i class="fa-solid fa-sliders me-2 text-secondary"></i> Pengaturan
                                </a>
                            </li>
                        @endif
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- CONTENT AREA -->
        <main class="content-area">
            @yield('content')
        </main>
    </div>

    <!-- NOTIFICATION OFFCANVAS DRAWER -->
    <div class="offcanvas offcanvas-end border-0 shadow-lg notification-drawer-offcanvas" tabindex="-1" id="notificationDrawer" aria-labelledby="notificationDrawerLabel">
        <div class="offcanvas-header border-bottom p-3 px-4">
            <div class="d-flex align-items-center gap-2">
                <div class="stat-icon" style="width: 36px; height: 36px; border-radius: var(--radius-md); background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <h2 class="offcanvas-title fw-bold text-dark fs-6 mb-0" id="notificationDrawerLabel">Pusat Notifikasi</h2>
                    <span class="text-muted small">Pemberitahuan alur validasi dan operasional</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('notifications.markAllRead') }}" method="POST" class="d-inline" id="formMarkAllRead">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm text-primary fw-medium">
                        <i class="fa-solid fa-check-double me-1"></i> Tandai Dibaca
                    </button>
                </form>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
            </div>
        </div>

        <div class="offcanvas-body p-0 d-flex flex-column bg-light">
            @php
                $drawerNotifs = Auth::user() ? \App\Models\Notifikasi::where('penerima_id', Auth::id())->latest()->take(25)->get() : collect();
            @endphp

            @if($drawerNotifs->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($drawerNotifs as $notif)
                        <div class="list-group-item p-3 border-bottom {{ is_null($notif->dibaca_pada) ? 'bg-white' : 'bg-transparent text-muted' }}">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="rounded-circle p-2 mt-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; 
                                    {{ $notif->jenis === 'upload_baru' ? 'background-color: #EFF6FF; color: #1E40AF;' : ($notif->jenis === 'ada_ditolak' ? 'background-color: #FEF2F2; color: #DC2626;' : 'background-color: #ECFDF5; color: #059669;') }}">
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
                                        <h3 class="mb-0 fw-semibold {{ is_null($notif->dibaca_pada) ? 'text-dark' : 'text-secondary' }}" style="font-size: 13px;">
                                            {{ $notif->judul }}
                                        </h3>
                                        <span class="text-muted text-nowrap" style="font-size: 10.5px;">
                                            {{ $notif->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="mb-2 small {{ is_null($notif->dibaca_pada) ? 'text-secondary' : 'text-muted' }}" style="font-size: 12px; line-height: 1.45;">
                                        {{ $notif->pesan }}
                                    </p>
                                    @if($notif->tautan)
                                        <div>
                                            <a href="{{ $notif->tautan }}" class="btn btn-sm btn-primary">
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
            <a href="{{ route('notifications.index') }}" class="small text-primary text-decoration-none fw-semibold">
                Buka Halaman Lengkap Notifikasi &rarr;
            </a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="{{ asset('js/datatables-id.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch('{{ route("notifications.markAllRead") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token || '{{ csrf_token() }}',
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

    @stack('scripts')
</body>
</html>
