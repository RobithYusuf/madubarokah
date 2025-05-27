@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-honey-pot honey-icon"></i>
        <h3>DAFTAR</h3>
        <p>Silahkan daftar terlebih dahulu untuk mulai berbelanja.</p>
    </div>
    <div class="card-body">
        <form id="registerForm" class="user">
            @csrf
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> Username
                </label>
                <input type="text" class="form-control form-control-user" id="username" name="username"
                    placeholder="Masukkan username" required>
                <div class="invalid-feedback">Username wajib diisi.</div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-group">
                    <input type="password" class="form-control form-control-user" id="password" name="password"
                        placeholder="Masukkan password" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">Password wajib diisi.</div>
                </div>
            </div>

            <div class="form-group">
                <label for="nama" class="form-label">
                    <i class="fas fa-id-card"></i> Nama Lengkap
                </label>
                <input type="text" class="form-control form-control-user" id="nama" name="nama"
                    placeholder="Masukkan nama lengkap" required>
                <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
            </div>
            
            <div class="form-group">
                <label for="nohp" class="form-label">
                    <i class="fas fa-phone"></i> No HP
                </label>
                <input type="tel" class="form-control form-control-user" id="nohp" name="nohp"
                    placeholder="Contoh: 08123456789" required>
                <div class="invalid-feedback">No HP wajib diisi.</div>
            </div>
            
            <div class="form-group">
                <label for="alamat" class="form-label">
                    <i class="fas fa-map-marker-alt"></i> Alamat
                </label>
                <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                    placeholder="Masukkan alamat lengkap" required></textarea>
                <div class="invalid-feedback">Alamat wajib diisi.</div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary btn-user btn-block flex-grow-1 m-1" id="submitBtn">
                    <span id="submitBtnText">
                        <i class="fas fa-user-plus"></i> Daftar
                    </span>
                </button>
                <a href="{{ route('login') }}" class="btn btn-secondary btn-user btn-block flex-grow-1 m-1">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </a>
            </div>
        </form>
    </div>
    <div class="card-footer text-center mt-2">
        <small class="text-muted">
            Dengan mendaftar, Anda menyetujui <a href="#" class="text-decoration-none">syarat & ketentuan</a> kami.
        </small>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    togglePasswordVisibility('#togglePassword', '#password');
    
    // Register form submission
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        const $submitBtn = $('#submitBtn');
        const $submitBtnText = $('#submitBtnText');
        
        // Disable submit button
        $submitBtn.prop('disabled', true);
        $submitBtnText.html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        $.ajax({
            url: '{{ route("register") }}',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registrasi Berhasil!',
                        text: response.message,
                        showConfirmButton: true,
                        confirmButtonText: 'Login Sekarang',
                        confirmButtonColor: '#FFA500'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("login") }}';
                        }
                    });
                } else {
                    showNotification(response.message, 'error', 'Registrasi Gagal');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat registrasi.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }
                }
                
                showNotification(errorMessage, 'error', 'Registrasi Gagal');
            },
            complete: function() {
                // Re-enable submit button
                $submitBtn.prop('disabled', false);
                $submitBtnText.html('<i class="fas fa-user-plus"></i> Daftar');
            }
        });
    });
    
    // Validate phone number format
    $('#nohp').on('input', function() {
        let value = $(this).val();
        // Remove non-numeric characters
        value = value.replace(/\D/g, '');
        // Limit to reasonable phone number length
        if (value.length > 15) {
            value = value.substring(0, 15);
        }
        $(this).val(value);
    });
    
    // Real-time validation
    $('#registerForm input, #registerForm textarea').on('blur', function() {
        validateField($(this));
    });
    
    function validateField($field) {
        const value = $field.val().trim();
        const fieldName = $field.attr('name');
        let isValid = true;
        let message = '';
        
        if (!value) {
            isValid = false;
            message = 'Field ini wajib diisi.';
        } else {
            switch(fieldName) {
                case 'username':
                    if (value.length < 3) {
                        isValid = false;
                        message = 'Username minimal 3 karakter.';
                    }
                    break;
                case 'password':
                    if (value.length < 6) {
                        isValid = false;
                        message = 'Password minimal 6 karakter.';
                    }
                    break;
                case 'nohp':
                    if (value.length < 10 || value.length > 15) {
                        isValid = false;
                        message = 'No HP harus 10-15 digit.';
                    }
                    break;
            }
        }
        
        if (isValid) {
            $field.removeClass('is-invalid').addClass('is-valid');
        } else {
            $field.removeClass('is-valid').addClass('is-invalid');
            $field.siblings('.invalid-feedback').text(message);
        }
        
        return isValid;
    }
});
</script>
@endpush
