<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>Masuk | RIZKI MANDIRI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sales and Inventory Management System" name="description" />
    <meta content="RIZKI MANDIRI" name="author" />
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/logo-rm.png') }}">

    <!-- Google Fonts for Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            /* Premium Dark/Nature background gradient */
            background: linear-gradient(135deg, rgba(16, 42, 28, 0.95) 0%, rgba(22, 60, 40, 0.85) 100%), 
                        url('https://images.unsplash.com/photo-1586201375761-83865001e31c?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Glassmorphism Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.05); /* Very subtle white */
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            color: #ffffff;
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            margin: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.4);
        }

        .glass-header {
            padding: 40px 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-body {
            padding: 30px;
        }

        .logo-img {
            height: 60px;
            margin-bottom: 15px;
            filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3));
        }

        /* Form Controls Overrides for Glass */
        .form-label {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .glass-input {
            background: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .glass-input:focus {
            background: rgba(0, 0, 0, 0.35) !important;
            border-color: #2fb344 !important; /* RM Green accent */
            box-shadow: 0 0 0 3px rgba(47, 179, 68, 0.2) !important;
        }

        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        /* Custom Password Eye Button */
        .glass-eye-btn {
            background: transparent !important;
            border: none !important;
            color: rgba(255, 255, 255, 0.6) !important;
        }
        
        .glass-eye-btn:hover {
            color: #ffffff !important;
        }

        /* Modern Checkbox */
        .form-check-input {
            background-color: rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .form-check-input:checked {
            background-color: #2fb344;
            border-color: #2fb344;
        }
        .form-check-label {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Premium Button */
        .btn-premium {
            background: linear-gradient(to right, #2fb344, #1b8536);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(47, 179, 68, 0.3);
            transition: all 0.3s ease;
        }

        .btn-premium:hover {
            background: linear-gradient(to right, #34c44a, #1f9e40);
            box-shadow: 0 6px 20px rgba(47, 179, 68, 0.4);
            transform: translateY(-2px);
            color: white;
        }

        /* Subtle links */
        .link-subtle {
            color: rgba(255, 255, 255, 0.6);
            transition: color 0.3s;
        }
        .link-subtle:hover {
            color: #2fb344;
        }

        /* Alert Styling */
        .glass-alert {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff8e99;
            backdrop-filter: blur(4px);
            border-radius: 12px;
        }

        /* Mobile specific adjustments */
        @media (max-width: 576px) {
            .glass-card {
                margin: 15px;
                border-radius: 20px;
            }
            .glass-header {
                padding: 30px 20px 15px;
            }
            .glass-body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="glass-card">
        <div class="glass-header">
            <img src="{{ asset('assets/images/logo-rm.png') }}" alt="RIZKI MANDIRI Logo" class="logo-img" style="height: 70px;">
            <h4 class="text-white mt-3 fw-semibold">RIZKI MANDIRI</h4>
            <p class="text-white-50 fs-14 mb-0">Sistem Manajemen & Inventaris</p>
        </div>

        <div class="glass-body">
            
            <div class="text-center mb-4">
                <h5 class="text-white mb-1">Selamat Datang</h5>
                <p class="text-white-50 fs-13">Silakan masuk menggunakan kredensial Anda.</p>
            </div>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                @if($errors->any())
                    <div class="alert glass-alert mb-4">
                        <i class="ri-error-warning-line me-1 align-middle"></i> {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-4">
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="position-relative">
                        <input type="email" class="form-control glass-input ps-4" id="email" name="email" value="{{ old('email') }}" placeholder="admin@rizkimandiri.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0" for="password-input">Kata Sandi</label>
                        <a href="javascript:void(0)" class="text-decoration-none fs-13 link-subtle" onclick="alert('Silakan hubungi Super Admin untuk reset password jaringan.')">Lupa sandi?</a>
                    </div>
                    <div class="position-relative auth-pass-inputgroup mb-3">
                        <input type="password" class="form-control glass-input pe-5" placeholder="Masukkan kata sandi" id="password-input" name="password" required>
                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none glass-eye-btn password-addon" type="button" id="password-addon">
                            <i class="ri-eye-fill align-middle"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-4 mt-2">
                    <input class="form-check-input" type="checkbox" name="remember" id="auth-remember-check">
                    <label class="form-check-label fs-14" for="auth-remember-check">Ingat sesi saya</label>
                </div>

                <div class="mt-4">
                    <button class="btn btn-premium w-100 btn-lg" type="submit">LOGIN KE SISTEM</button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <p class="mb-0 fs-12 text-white-50">&copy; <script>document.write(new Date().getFullYear())</script> RIZKI MANDIRI. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/password-addon.init.js') }}"></script>
    <script>
        // Simple script to handle password eye toggle in case the init.js fails under custom structure
        document.getElementById('password-addon').addEventListener('click', function (e) {
            const passwordInput = document.getElementById('password-input');
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                this.innerHTML = '<i class="ri-eye-off-fill align-middle"></i>';
            } else {
                passwordInput.type = "password";
                this.innerHTML = '<i class="ri-eye-fill align-middle"></i>';
            }
        });
    </script>
</body>
</html>
