<header class="app-topbar" role="banner">
    <button class="topbar-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>

    <div class="topbar-breadcrumb" aria-label="Breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="#" style="color:var(--kemenhub-blue-mid);font-weight:500;">
                        <i class="bi bi-house-fill me-1" aria-hidden="true"></i>Beranda
                    </a>
                </li>
                <li class="breadcrumb-item active" id="breadcrumbActive">Dashboard</li>
            </ol>
        </nav>
    </div>

    <div class="topbar-clock">
        <i class="bi bi-clock" style="color:var(--kemenhub-gold-dark)"></i>
        <span id="topbar-clock">Memuat...</span>
    </div>

    <div class="topbar-divider"></div>

    <div class="topbar-actions">
        <button class="topbar-icon-btn" type="button" title="Sync API Backend" onclick="syncWithBackendApi()">
            <i class="bi bi-arrow-clockwise"></i>
        </button>

        <!-- Switch Role Fast Toggle (5 Roles Requirement) -->
        <div class="dropdown">
            <button class="topbar-user-btn" type="button" id="roleToggleBtn" data-bs-toggle="dropdown">
                <i class="bi bi-shield-lock-fill text-gold me-1"></i>
                <span id="currentRoleText" class="fw-bold">Role: Administrator</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header">Simulasi Ganti Role User</h6></li>
                <li><a class="dropdown-item py-2 active" href="#" onclick="switchUserRole('admin')"><i class="bi bi-person-badge me-2"></i>Admin LOFBI (Administrator)</a></li>
                <li><a class="dropdown-item py-2" href="#" onclick="switchUserRole('operator')"><i class="bi bi-person-workspace me-2"></i>Operator LOFBI (Operator)</a></li>
                <li><a class="dropdown-item py-2" href="#" onclick="switchUserRole('validator')"><i class="bi bi-person-check me-2"></i>Validator LOFBI (Validator)</a></li>
                <li><a class="dropdown-item py-2" href="#" onclick="switchUserRole('viewer')"><i class="bi bi-eye-fill me-2"></i>Viewer LOFBI (Viewer)</a></li>
                <li><a class="dropdown-item py-2" href="#" onclick="switchUserRole('pimpinan')"><i class="bi bi-person-badge-fill me-2"></i>Pimpinan KSOP (Pimpinan)</a></li>
            </ul>
        </div>

        <div class="topbar-divider"></div>

        <div class="dropdown">
            <button class="topbar-user-btn" type="button" id="userDropdown" data-bs-toggle="dropdown">
                <div class="topbar-avatar" id="topbarAvatar">AL</div>
                <span class="topbar-username d-none d-md-inline" id="topbarUserName">Admin LOFBI</span>
                <i class="bi bi-chevron-down fs-xs ms-1 text-muted"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold" id="dropdownUserName">Admin LOFBI</div>
                    <div class="fs-xs text-muted">KSOP Kelas I Banten</div>
                </li>
                <li><a class="dropdown-item py-2" href="#" onclick="switchSection('dashboard')"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item py-2" href="#" onclick="switchSection('users')"><i class="bi bi-person-circle me-2"></i>Kelola Pengguna</a></li>
                <li><a class="dropdown-item py-2" href="#" onclick="switchSection('settings')"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item py-2 text-danger" href="#" onclick="alert('Logged out! Token cleared.')"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
            </ul>
        </div>
    </div>
</header>
