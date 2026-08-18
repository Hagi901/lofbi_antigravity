<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LOFBI KSOP Banten</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #0f172a; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh;
            margin: 0;
            background-image: radial-gradient(circle at top right, #1e293b, #0f172a);
        }
        .login-card { 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            background: #ffffff;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        /* Menjadikan ikon mata seperti tombol yang bisa diklik */
        .toggle-password {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary mb-1">LOFBI</h3>
                            <p class="text-muted small">Layanan Operasional Fasilitas & Barang Inventaris<br><strong>KSOP Kelas I Banten</strong></p>
                        </div>

                        <!-- Area Notifikasi Pesan Sukses / Error -->
                        @if ($errors->any())
                            <div class="alert alert-danger py-2 small border-0 shadow-sm">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $errors->first() }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success py-2 small border-0 shadow-sm">
                                <i class="fa-solid fa-check-circle me-1"></i> {{ session('success') }}
                            </div>
                        @endif

                        <!-- Form Login -->
                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Alamat Email</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-secondary"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" name="email" value="{{ old('email') }}" required placeholder="admin@lofbi.com">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <!-- Bagian Label dan Lupa Password yang sejajar -->
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0">Password</label>
                                    <a href="#" class="small text-decoration-none">Lupa Password?</a>
                                </div>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-secondary"></i></span>
                                    <!-- Tambah ID 'password' agar bisa diatur oleh Javascript -->
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" name="password" id="password" required placeholder="••••••••">
                                    <!-- Tombol Ikon Mata -->
                                    <span class="input-group-text bg-white border-start-0 toggle-password" id="togglePasswordBtn">
                                        <i class="fa-solid fa-eye text-secondary" id="eyeIcon"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                Masuk ke Sistem <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-4 text-secondary small opacity-75">
                    &copy; 2026 - M. Rivaldo Firdaus (2023081045)<br>
                    Sistem Informasi
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk mengaktifkan fitur mata (Show/Hide Password) -->
    <script>
        const togglePasswordBtn = document.querySelector('#togglePasswordBtn');
        const passwordInput = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePasswordBtn.addEventListener('click', function () {
            // Cek tipe saat ini, ubah ke tipe sebaliknya
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Ubah gambar ikon antara mata terbuka (fa-eye) dan tertutup (fa-eye-slash)
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>