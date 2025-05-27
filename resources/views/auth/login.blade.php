@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-honey-pot honey-icon"></i>
        <h3>MASUK</h3>
        <p>Silahkan masuk ke aplikasi menggunakan akun anda.</p>
    </div>
    <div class="card-body">
        <form id="loginForm" class="user">
            @csrf
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> Username
                </label>
                <input type="text"
                    class="form-control form-control-user @error('username') is-invalid @enderror"
                    id="username" name="username" placeholder="Masukkan username" required>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Username wajib diisi.</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-group">
                    <input type="password"
                        class="form-control form-control-user @error('password') is-invalid @enderror"
                        id="password" name="password" placeholder="Masukkan password" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Password wajib diisi.</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <div class="custom-control custom-checkbox small">
                    <input type="checkbox" class="custom-control-input" id="rememberMe" name="remember">
                    <label class="custom-control-label" for="rememberMe">Ingat saya</label>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary btn-user btn-block flex-grow-1 m-1" id="submitBtn">
                    <span id="submitBtnText">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </span>
                </button>
                <a href="{{ route('register') }}" class="btn btn-secondary btn-user btn-block flex-grow-1 m-1">
                    <i class="fas fa-user-plus"></i> Daftar
                </a>
            </div>
        </form>
    </div>
    <div class="card-footer text-center mt-3">
        <a href="#" class="small text-muted">Lupa password?</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    togglePasswordVisibility('#togglePassword', '#password');
    
    // Login form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const $submitBtn = $('#submitBtn');
        const $submitBtnText = $('#submitBtnText');
        
        // Disable submit button
        $submitBtn.prop('disabled', true);
        $submitBtnText.html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        $.ajax({
            url: '{{ route("login") }}',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.href = response.redirect_url || '/';
                    });
                } else {
                    showNotification(response.message, 'error', 'Login Gagal');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat login.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }
                }
                
                showNotification(errorMessage, 'error', 'Login Gagal');
            },
            complete: function() {
                // Re-enable submit button
                $submitBtn.prop('disabled', false);
                $submitBtnText.html('<i class="fas fa-sign-in-alt"></i> Masuk');
            }
        });
    });
});
</script>
@endpush
