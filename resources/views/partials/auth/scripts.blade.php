{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Scripts --}}
<script src="{{ asset('assets/sbadmin/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/sbadmin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/sbadmin/js/sb-admin-2.min.js') }}"></script>

<script>
    // CSRF Token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global notification function
    function showNotification(message, type = 'info', title = '') {
        Swal.fire({
            icon: type === 'success' ? 'success' : type === 'error' ? 'error' : 'info',
            title: title || (type === 'success' ? 'Berhasil!' : type === 'error' ? 'Error!' : 'Info'),
            text: message,
            showConfirmButton: false,
            timer: 3000,
            toast: true,
            position: 'top-end',
            timerProgressBar: true
        });
    }

    // Show/hide password functionality
    function togglePasswordVisibility(buttonId, inputId) {
        $(buttonId).on('click', function() {
            const icon = $(this).find('i');
            icon.toggleClass("fa-eye fa-eye-slash");
            const type = icon.hasClass("fa-eye-slash") ? "text" : "password";
            $(inputId).attr("type", type);
        });
    }
</script>