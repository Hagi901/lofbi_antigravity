@extends('layouts.app')

@section('page_title', 'Profil Pengguna')

@section('content')
<div class="row align-items-start">
    <!-- Kartu Foto & Identitas Singkat -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm overflow-hidden rounded-4">
            <div class="bg-dark position-relative" style="height: 120px; background: linear-gradient(135deg, #0f172a, #1e293b);">
            </div>
            <div class="card-body text-center position-relative pb-4">
                <div class="bg-white rounded-circle p-1 d-inline-block position-relative" style="margin-top: -70px; box-shadow: 0 8px 15px rgba(0,0,0,0.1);">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'User') }}&background=0d6efd&color=fff&size=120&bold=true" class="rounded-circle" alt="Profile">
                </div>
                <h5 class="fw-bold mt-3 mb-1 text-dark">{{ $user->name ?? 'Pengguna LOFBI' }}</h5>
                <p class="text-muted small mb-3"><i class="fa-solid fa-envelope me-1"></i> {{ $user->email ?? '-' }}</p>
                <span class="badge bg-success-subtle text-success border border-success fw-bold px-3 py-2 rounded-pill text-capitalize">
                    <i class="fa-solid fa-shield-halved me-1"></i> Role: {{ $user->role ?? 'Operator' }}
                </span>
            </div>
            <ul class="list-group list-group-flush border-top">
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 small text-muted">
                    <span><i class="fa-solid fa-building me-2"></i> Instansi</span>
                    <strong class="text-dark">KSOP Kelas I Banten</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 small text-muted">
                    <span><i class="fa-solid fa-calendar-check me-2"></i> Terdaftar Sejak</span>
                    <strong class="text-dark">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</strong>
                </li>
            </ul>
        </div>
    </div>

    <!-- Kartu Form Profil & Ubah Password -->
    <div class="col-lg-8">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <ul class="nav nav-tabs border-bottom" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark pb-3" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                            <i class="fa-solid fa-user-pen me-2 text-primary"></i>Data Akun
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-muted pb-3" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                            <i class="fa-solid fa-shield-halved me-2 text-warning"></i>Ubah Password
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="profileTabsContent">
                    
                    <!-- TAB 1: INFORMASI DATA AKUN -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control fw-semibold" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label class="form-label small fw-bold text-secondary">Alamat Email</label>
                                    <input type="email" name="email" class="form-control fw-semibold" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Hak Akses / Role</label>
                                    <input type="text" class="form-control bg-light text-muted text-capitalize" value="{{ $user->role ?? '-' }}" readonly>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label class="form-label small fw-bold text-secondary">Instansi</label>
                                    <input type="text" class="form-control bg-light text-muted" value="KSOP Kelas I Banten" readonly>
                                </div>
                            </div>

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Perbarui Profil
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: KEAMANAN & UBAH PASSWORD -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="alert alert-warning py-2 small d-flex align-items-center mb-4 border-0 shadow-sm">
                            <i class="fa-solid fa-triangle-exclamation me-3 fs-4"></i>
                            <div>
                                <strong>Perhatian:</strong> Pastikan password baru minimal 6 karakter kombinasi yang aman.
                            </div>
                        </div>
                        
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Password Saat Ini</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" name="current_password" class="form-control border-start-0 ps-0" placeholder="Masukkan password lama" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Password Baru</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                        <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Minimal 6 karakter" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label class="form-label small fw-bold text-secondary">Konfirmasi Password Baru</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-check-double text-muted"></i></span>
                                        <input type="password" name="password_confirmation" class="form-control border-start-0 ps-0" placeholder="Ulangi password baru" required>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-danger fw-bold px-4 shadow-sm">
                                    <i class="fa-solid fa-shield-halved me-2"></i> Update Password
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