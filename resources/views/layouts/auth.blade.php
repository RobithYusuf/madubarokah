<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Toko Madu Barokah</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Styles --}}
    @include('partials.auth.styles')

    @stack('styles')
</head>

<body>
    <div class="background-container">
        <div class="overlay"></div>
        <div class="brand-name" onclick="window.location.href='{{ route('frontend.home') }}'">
            <i class="fas fa-spa"></i> Toko Madu Barokah
        </div>
    </div>

    <div class="auth-container">
        @yield('content')
    </div>

    {{-- Scripts --}}
    @include('partials.auth.scripts')

    @stack('scripts')
</body>

</html>