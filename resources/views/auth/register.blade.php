<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Madu Barokah - Register</title>
    <link href="{{ asset('assets/sbadmin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/sbadmin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        .background-container {
            background-image: url('{{ asset('assets/landingpage/images/photo-1473973266408-ed4e27abdd47.jpg') }}');
            background-size: cover;
            background-position: center;
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
        }

        .login-container {
            position: absolute;
            top: 0;
            right: 0;
            width: 400px;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            overflow-y: auto;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            background: transparent;
        }

        .card-header {
            background-color: #FFA500;
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .card-header p {
            text-align: left;
        }

        .btn-primary {
            background-color: #FFA500;
            border-color: #FFA500;
        }

        .btn-primary:hover {
            background-color: #FF8C00;
            border-color: #FF8C00;
        }

        .btn-primary:focus {
            background-color: #FFA500;
            border-color: #FFA500;
        }

        .btn-primary:visited {
            background-color: #FFA500;
            border-color: #FFA500;
        }

        .btn-primary:active {
            background-color: #FFA500;
            border-color: #FFA500;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .btn-secondary:focus {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:visited {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:active {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .form-control:focus {
            border-color: #FFA500;
            box-shadow: 0 0 0 0.2rem rgba(255, 165, 0, 0.25);
        }

        .honey-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .brand-name {
            position: absolute;
            top: 30px;
            left: 50px;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body>
    <div class="background-container">
        <div class="overlay"></div>
        <div class="brand-name">Toko Madu Barokah</div>
    </div>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-honey-pot honey-icon"></i>
                <h3 id="formTitle">DAFTAR</h3>
                <p id="formTitleP">Silahkan daftar terlebih dahulu.</p>
            </div>
            <div class="card-body">
                <form id="registerForm" class="user">
                    @csrf
                    <div class="form-group">
                        <label for="username" class="form-label"><i class="fas fa-user"></i> Username</label>
                        <input type="text" class="form-control form-control-user" id="username" name="username"
                            placeholder="Masukkan username" required>
                        <div class="invalid-feedback">
                            Username wajib diisi.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label"><i class="fas fa-lock"></i> Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-user" id="password" name="password"
                                placeholder="Masukkan password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="icon_click">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="invalid-feedback">
                            Password wajib diisi.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama" class="form-label"><i class="fas fa-user"></i> Nama</label>
                        <input type="text" class="form-control form-control-user" id="nama" name="nama"
                            placeholder="Masukkan Nama" required>
                        <div class="invalid-feedback">
                            Nama wajib diisi.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nohp" class="form-label"><i class="fas fa-phone"></i> No HP</label>
                        <input type="tel" class="form-control form-control-user" id="nohp" name="nohp"
                            placeholder="Masukkan no HP" required>
                        <div class="invalid-feedback">
                            No HP wajib diisi.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="alamat" class="form-label"><i class="fas fa-map-marker-alt"></i> Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat" required></textarea>
                        <div class="invalid-feedback">
                            Alamat wajib diisi.
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-secondary btn-user btn-block flex-grow-1 m-1" id="submitBtn"><span id="submitBtnText">Daftar</span></button>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-user btn-block flex-grow-1 m-1" id="switchBtn"><span id="switchBtnText">Masuk</span></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Library SBADMIN --}}
    <script src="{{ asset('assets/sbadmin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/sbadmin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/sbadmin/js/sb-admin-2.min.js') }}"></script>
    {{-- Sweet Alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Custom JS --}}
    <script>
        $(document).ready(function() {
            // Show hide password
            $("#icon_click").on('click', function() {
                const icon = $(this).find('i');
                icon.toggleClass("fa-eye fa-eye-slash");
                const type = icon.hasClass("fa-eye-slash") ? "text" : "password";
                $("#password").attr("type", type);
            });
            // Register Form
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/register', // Sesuaikan dengan URL login Anda
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Akun berhasil dibuat!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                // Redirect ke halaman logib setelah akun dibuat
                                window.location.href = '/login';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Registrasi Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Terjadi kesalahan saat login.'
                        });
                    }
                });
            });

        });
    </script>
</body>

</html>
