
{{-- SB Admin CSS --}}
<link href="{{ asset('assets/sbadmin/css/sb-admin-2.min.css') }}" rel="stylesheet">
{{-- Font Awesome --}}
<link href="{{ asset('assets/sbadmin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
{{-- Font Awesome CDN sebagai fallback --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body,
    html {
        height: 100%;
        margin: 0;
        overflow: hidden;
    }

    .background-container {
        background-image: url('{{ asset("assets/landingpage/images/photo-1473973266408-ed4e27abdd47.jpg") }}');
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

    .auth-container {
        position: absolute;
        top: 0;
        right: 0;
        width: 400px;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 30px;
        overflow-y: auto;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
    }

    .card {
        border: none;
        background: transparent;
        box-shadow: none;
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
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #FF8C00;
        border-color: #FF8C00;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
        transform: translateY(-1px);
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
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .brand-name:hover {
        transform: scale(1.05);
        text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7);
    }

    .form-control {
        transition: all 0.3s ease;
    }

    .form-control:focus {
        transform: translateY(-1px);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
    }

    @media (max-width: 768px) {
        .auth-container {
            width: 100%;
            position: relative;
            height: auto;
            min-height: 100vh;
        }

        .brand-name {
            position: relative;
            top: 0;
            left: 0;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .background-container {
            display: none;
        }

        body {
            background: linear-gradient(135deg, #FFA500, #FF8C00);
        }
    }
</style>