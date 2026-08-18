@extends('layouts.app')

@section('page_title', 'Profil Pengguna')

@section('content')
<div class="row align-items-start">
    <!-- Kartu Foto & Identitas Singkat -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="bg-primary" style="height: 100px;"></div>
            <div class="card-body text-center position-relative pb-4">
                <div class="bg-white rounded-circle p-1 d-inline-block position-relative" style="margin-top: -60px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <i class="fa-solid fa-circle-user text-primary" style="font-size: 6rem;"></i>
                </div>
                <h5 class="fw-bold mt-3 mb-1 text-dark">Muhammad Rivaldo Firdaus</h5>
                <p class="text-muted small mb-3"><i class="fa-solid fa-id-badge me-1"></i> NIM: 2023081045</p>
                <span class="badge bg-success-subtle text-success border border-success fw-bold px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-shield-halved me-1"></i> Administrator Sistem
                </span>
            </div>
            <ul class="list-group list-group-flush border-top">
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 small text-muted">
                    <span><i class="fa-solid fa-envelope me-2"></i> Email Akun</span>
                    <strong class="text-dark">admin@lofbi.com</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 small text-muted">
                    <span><i class="fa-solid fa-clock-rotate-left me-2"></i> Login Terakhir</span>
                    <strong class="text-dark">Hari Ini</strong>
                </li>
            </ul>
        </div>
    </div>

    <!-- Kartu Pengaturan Form Profil -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-user-pen me-2 text-primary"></i>Informasi Data Diri</h6>
            </div>
            <div class="card-body p-4">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nama Lengkap</label>
                            <input type="text" class="form-control" value="Muhammad Rivaldo Firdaus" readonly>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="form-label small fw-bold text-secondary">Nomor Induk Mahasiswa (NIM)</label>
                            <input type="text" class="form-control" value="2023081045" readonly>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Program Studi</label>
                            <input type="text" class="form-control" value="Sistem Informasi" readonly>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="form-label small fw-bold text-secondary">Instansi / Lokasi Tugas</label>
                            <input type="text" class="form-control" value="KSOP Kelas I Banten" readonly>
                        </div>
                    </div>

                    <hr class="mb-4">
                    <h6 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-lock me-2 text-warning"></i>Keamanan Akun</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Password Baru</label>
                            <input type="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="form-label small fw-bold text-secondary">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>@extends('layouts.app')

@section('page_title', 'Profil Operator Sistem')

@section('content')
<div class="row align-items-start">
    <!-- Kartu Foto & Identitas Singkat -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm overflow-hidden rounded-4">
            <div class="bg-dark position-relative" style="height: 120px; background: linear-gradient(135deg, #0f172a, #1e293b);">
                <!-- Dekorasi Background -->
            </div>
            <div class="card-body text-center position-relative pb-4">
                <div class="bg-white rounded-circle p-1 d-inline-block position-relative" style="margin-top: -70px; box-shadow: 0 8px 15px rgba(0,0,0,0.1);">
                    <!-- Menggunakan Avatar Generator Canggih -->
                    <img src="https://ui-avatars.com/api/?name=Rivaldo+Firdaus&background=0d6efd&color=fff&size=120&bold=true" class="rounded-circle" alt="Profile">
                    <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow" style="width: 35px; height: 35px;" title="Ubah Foto">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                </div>
                <h5 class="fw-bold mt-3 mb-1 text-dark">Muhammad Rivaldo Firdaus</h5>
                <p class="text-muted small mb-3"><i class="fa-solid fa-id-card me-1"></i> NIP: 19980514 202401 1 001</p>
                <span class="badge bg-success-subtle text-success border border-success fw-bold px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-user-shield me-1"></i> Operator Utama LOFBI
                </span>
            </div>
            <ul class="list-group list-group-flush border-top">
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 small text-muted">
                    <span><i class="fa-solid fa-envelope me-2"></i> Email Akun</span>
                    <strong class="text-dark">admin@lofbi.com</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 small text-muted">
                    <span><i class="fa-solid fa-building me-2"></i> Unit Kerja</span>
                    <strong class="text-dark">KSOP Kelas I Banten</strong>
                </li>
            </ul>
        </div>
    </div>

    <!-- Kartu Pengaturan Berbasis Nav Tabs Canggih -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <ul class="nav nav-tabs border-bottom" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark pb-3" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                            <i class="fa-solid fa-user-pen me-2 text-primary"></i>Data Pegawai
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
                    
                    <!-- TAB 1: INFORMASI PEGAWAI -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <form id="formProfile">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Nama Lengkap Pegawai</label>
                                    <input type="text" class="form-control fw-semibold" value="Muhammad Rivaldo Firdaus">
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label class="form-label small fw-bold text-secondary">Nomor Induk Pegawai (NIP)</label>
                                    <input type="text" class="form-control bg-light text-muted" value="19980514 202401 1 001" readonly>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Jabatan Fungsional</label>
                                    <input type="text" class="form-control bg-light text-muted" value="Operator Sistem Informasi" readonly>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label class="form-label small fw-bold text-secondary">Alamat Email Operasional</label>
                                    <input type="email" class="form-control fw-semibold" value="admin@lofbi.com">
                                </div>
                            </div>

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="button" onclick="simpanProfil()" class="btn btn-primary fw-bold px-4 shadow-sm">
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
                                <strong>Perhatian Keamanan!</strong> Pastikan password baru menggunakan kombinasi huruf besar, angka, dan simbol untuk melindungi data instansi.
                            </div>
                        </div>
                        
                        <form id="formPassword">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Password Saat Ini</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" placeholder="Masukkan password lama" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Password Baru</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                        <input type="password" class="form-control border-start-0 ps-0" placeholder="Minimal 8 karakter" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label class="form-label small fw-bold text-secondary">Konfirmasi Password Baru</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-check-double text-muted"></i></span>
                                        <input type="password" class="form-control border-start-0 ps-0" placeholder="Ulangi password baru" required>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="button" onclick="ubahPassword()" class="btn btn-danger fw-bold px-4 shadow-sm">
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

<!-- Tambahkan Pustaka Animasi SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fungsi animasi saat update profil
    function simpanProfil() {
        Swal.fire({
            title: 'Menyimpan Perubahan...',
            text: 'Melakukan sinkronisasi data pegawai ke server.',
            timer: 1500,
            timerProgressBar: true,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading() }
        }).then(() => {
            Swal.fire({ 
                icon: 'success', 
                title: 'Berhasil!', 
                text: 'Data profil operator telah diperbarui.', 
                confirmButtonColor: '#0d6efd' 
            });
        });
    }

    // Fungsi animasi saat ubah password
    function ubahPassword() {
        Swal.fire({
            title: 'Verifikasi Enkripsi...',
            text: 'Memvalidasi kekuatan keamanan password baru.',
            timer: 2000,
            timerProgressBar: true,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading() }
        }).then(() => {
            Swal.fire({ 
                icon: 'success', 
                title: 'Password Diperbarui!', 
                text: 'Silakan gunakan kredensial baru pada sesi login Anda berikutnya.', 
                confirmButtonColor: '#198754' 
            });
            // Kosongkan form otomatis setelah berhasil
            document.getElementById('formPassword').reset();
        });
    }
</script>
@endsection
@endsection