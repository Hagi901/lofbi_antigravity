<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LOFBI KSOP Kelas I Banten</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (Poppins) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { 
            background-color: var(--sidebar-bg); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh;
            margin: 0;
            background-image: radial-gradient(circle at top right, #1e293b, #0f172a);
            padding: 1.5rem;
        }
        .login-card { 
            border-radius: var(--radius-xl); 
            overflow: hidden; 
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.45); 
            background: #ffffff;
            border: 1px solid var(--color-border);
        }
        .toggle-password {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
                <div class="card login-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2" style="width: 46px; height: 46px; background: var(--color-primary); color: #ffffff; font-size: 1.35rem; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.35);">
                                <i class="fa-solid fa-anchor"></i>
                            </div>
                            <h1 class="fw-bold text-dark fs-4 mb-1">LOFBI</h1>
                            <p class="text-muted small mb-0">Layanan Operasional Fasilitas &amp; Barang Inventaris<br><strong>KSOP Kelas I Banten</strong></p>
                        </div>

                        <!-- Area Notifikasi Pesan Sukses / Error -->
                        @if ($errors->any())
                            <div class="alert alert-danger py-2 small border-0 shadow-sm mb-3">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $errors->first() }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success py-2 small border-0 shadow-sm mb-3">
                                <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                            </div>
                        @endif

                        <!-- Form Login -->
                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label-app">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted" style="border-color: var(--color-border);"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" class="form-control form-control-app border-start-0 ps-0" name="email" value="{{ old('email') }}" required placeholder="admin@lofbi.com">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label-app mb-0">Password</label>
                                    <a href="#" class="small text-decoration-none text-muted" style="font-size: 11.5px;">Lupa Password?</a>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted" style="border-color: var(--color-border);"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" class="form-control form-control-app border-start-0 border-end-0 ps-0" name="password" id="password" required placeholder="••••••••">
                                    <span class="input-group-text bg-white toggle-password" id="togglePasswordBtn" style="border-color: var(--color-border);">
                                        <i class="fa-solid fa-eye text-muted" id="eyeIcon"></i>
                                    </span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                Masuk ke Sistem <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4 text-white small opacity-75" style="font-size: 11.5px;">
                    &copy; 2026 KSOP Kelas I Banten<br>
                    Sistem Inventarisasi BMN
                </div>
            </div>
        </div>
    </div>

    <!-- Script Show/Hide Password -->
    <script>
        const togglePasswordBtn = document.querySelector('#togglePasswordBtn');
        const passwordInput = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        if (togglePasswordBtn && passwordInput && eyeIcon) {
            togglePasswordBtn.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon.classList.toggle('fa-eye');
                eyeIcon.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>
