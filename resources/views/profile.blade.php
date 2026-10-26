@extends('layouts.app')

@section('page_title', 'Profil Pengguna')

@section('content')
<!-- Page Header -->
<x-page-header title="Data Profil Pengguna" subtitle="Pengaturan informasi akun dan keamanan kata sandi pengguna">
</x-page-header>

<div class="row align-items-start">
    <!-- Kartu Foto & Identitas Singkat -->
    <div class="col-lg-4 mb-4">
        <div class="card-app overflow-hidden">
            <div style="height: 100px; background: linear-gradient(135deg, var(--sidebar-bg), #1e293b);"></div>
            <div class="card-body-app text-center position-relative pb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white fw-bold shadow-sm" style="margin-top: -55px; width: 80px; height: 80px; font-size: 1.75rem; border: 4px solid #ffffff;">
                    {{ strtoupper(substr($user->name ?? 'User', 0, 2)) }}
                </div>
                <h2 class="fw-bold mt-3 mb-1 text-dark fs-6">{{ $user->name ?? 'Pengguna LOFBI' }}</h2>
                <p class="text-muted small mb-3"><i class="fa-solid fa-envelope me-1"></i> {{ $user->email ?? '-' }}</p>
                <span class="badge-app badge-app-success text-capitalize">
                    <i class="fa-solid fa-shield-halved"></i> Role: {{ $user->role ?? 'Operator' }}
                </span>
            </div>
            <ul class="list-group list-group-flush border-top" style="font-size: 12.5px;">
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 text-muted">
                    <span><i class="fa-solid fa-building me-2"></i> Instansi</span>
                    <strong class="text-dark">KSOP Kelas I Banten</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 text-muted">
                    <span><i class="fa-solid fa-calendar-check me-2"></i> Terdaftar Sejak</span>
                    <strong class="text-dark">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</strong>
                </li>
            </ul>
        </div>
    </div>

    <!-- Kartu Form Profil & Ubah Password -->
    <div class="col-lg-8 mb-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 small mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 small mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card-app">
            <div class="card-header-app pb-0 pt-3 border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold pb-2 px-3" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" style="font-size: 13px;">
                            <i class="fa-solid fa-user-pen me-2 text-primary"></i>Data Akun
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-muted pb-2 px-3" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" style="font-size: 13px;">
                            <i class="fa-solid fa-shield-halved me-2 text-warning"></i>Ubah Password
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body-app p-4">
                <div class="tab-content" id="profileTabsContent">
                    <!-- TAB 1: INFORMASI DATA AKUN -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label-app">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control form-control-app" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-app">Alamat Email</label>
                                    <input type="email" name="email" class="form-control form-control-app" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-app">Hak Akses / Role</label>
                                    <input type="text" class="form-control form-control-app bg-light text-muted text-capitalize" value="{{ $user->role ?? '-' }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-app">Instansi</label>
                                    <input type="text" class="form-control form-control-app bg-light text-muted" value="KSOP Kelas I Banten" readonly>
                                </div>
                            </div>

                            <div class="text-end pt-3 border-top">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Perbarui Profil
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: KEAMANAN & UBAH PASSWORD -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="alert alert-warning py-2 small d-flex align-items-center mb-4 border-0">
                            <i class="fa-solid fa-triangle-exclamation me-3 fs-5"></i>
                            <div>
                                <strong>Perhatian:</strong> Pastikan password baru minimal 6 karakter kombinasi yang aman.
                            </div>
                        </div>

                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label-app">Password Saat Ini</label>
                                <input type="password" name="current_password" class="form-control form-control-app" placeholder="Masukkan password lama" required>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-app">Password Baru</label>
                                    <input type="password" name="password" class="form-control form-control-app" placeholder="Minimal 6 karakter" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-app">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_confirmation" class="form-control form-control-app" placeholder="Ulangi password baru" required>
                                </div>
                            </div>

                            <div class="text-end pt-3 border-top">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
