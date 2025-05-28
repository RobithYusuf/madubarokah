<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Toko Madu Barokah</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/bootstrap.min.css') }}">
    {{-- Font Awesome --}}
    <link href="{{ asset('assets/sbadmin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    {{-- Font Awesome CDN sebagai fallback --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    {{-- Animasi --}}
    <link rel="stylesheet" href="{{ asset('assets/aos/aos.css') }}">

    <style>
        body {
            font-family: Tahoma, Geneva, Verdana, sans-serif;
            background-color: #FFF8E1;
        }

        .bg-yellow {
            background-color: #FFF8E1;
        }

        .bg-orange {
            background-color: #FFA500;
            color: #fff;
        }

        .bg-gelap {
            background-color: #cc8400;
            color: #fff;
        }

        .text-orange {
            color: #FFA500;
        }

        .text-gelap {
            color: #cc8400;
        }

        .navbar .text-gelap {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .navbar .text-gelap:hover {
            color: #664200;
            transform: translateY(-1px);
        }

        .navbar .text-gelap::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: linear-gradient(90deg, #FFA500, #cc8400);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .navbar .text-gelap:hover::after {
            width: 100%;
        }

        .btn-orange {
            background-color: #FFA500;
            color: #fff;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            border: none;
            box-shadow: 0 2px 8px rgba(255, 165, 0, 0.2);
        }

        .btn-orange:hover {
            background-color: #cc8400;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(204, 132, 0, 0.3);
        }

        .btn-orange:active {
            transform: translateY(0);
        }

        .btn-orange i {
            margin-right: 0.5rem;
        }

        /* Navbar Login Button */
        .navbar .btn-orange {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Card default */
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Card untuk produk dengan hover yang minimalist dan profesional */
        .card-product {
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            overflow: hidden;
            position: relative;
            margin-bottom: 1.5rem;
        }

        .card-product:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(204, 132, 0, 0.12);
            border-color: rgba(204, 132, 0, 0.3);
        }

        /* Container produk untuk mencegah layout shift */
        .product-container {
            padding: 0.5rem;
        }

        /* Badge kategori yang lebih rapi */
        .category-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            font-size: 0.7em;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Card untuk keranjang/cart dengan hover yang berbeda */
        .card-cart {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-cart:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
            transform: translateY(-1px);
        }

        /* Styles untuk navbar mengambang */
        .navbar {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            width: 90%;
            margin: 20px auto 0 auto;
            border-radius: 25px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar.scrolled {
            margin-top: 10px;
            background-color: rgba(255, 255, 255, 0.98);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            width: 95%;
        }

        /* Cart badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            min-width: 20px;
        }

        .cart-icon {
            position: relative;
            display: inline-block;
        }

        /* Main content dengan padding untuk navbar mengambang */
        .cart-container {
            padding-top: 120px;
            min-height: 100vh;
        }

        /* Custom styles for navbar toggler */
        .navbar-toggler {
            border: 2px solid #cc8400;
            border-radius: 8px;
            padding: 4px 8px;
            transition: all 0.3s ease;
        }

        .navbar-toggler:hover {
            background-color: rgba(204, 132, 0, 0.1);
            transform: scale(1.05);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(204, 132, 0, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=UTF8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='rgba%28 204, 132, 0, 1 %29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        /* Product action buttons */
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-cart {
            flex: 1;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-cart i {
            margin-right: 0.4rem;
        }

        .btn-checkout-direct {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-checkout-direct:hover:not(:disabled) {
            background-color: #218838;
            border-color: #1e7e34;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        /* Mobile adjustments */
        @media (max-width: 767.98px) {
            .navbar {
                width: 95%;
                margin: 15px auto 0 auto;
                border-radius: 20px;
            }

            .navbar.scrolled {
                width: 98%;
                margin-top: 5px;
            }

            .navbar-collapse {
                position: absolute;
                top: 110%;
                left: 0;
                width: 100%;
                background-color: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(15px);
                border-radius: 20px;
                margin-top: 10px;
                padding: 1.5rem;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.2);
                z-index: 1000;
            }

            .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 12px;
                margin: 0.25rem 0;
                transition: all 0.3s ease;
            }

            .nav-link:hover {
                background-color: rgba(204, 132, 0, 0.1);
                transform: translateX(5px);
            }

            .cart-container {
                padding-top: 110px;
            }

            .product-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-cart {
                font-size: 0.85rem;
                padding: 0.6rem 1rem;
            }
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }

        .loading-spinner {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Memproses...</p>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar fixed-top navbar-expand-lg" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand text-gelap font-weight-bold" href="{{ route('frontend.home') }}">
                <i class="fas fa-spa"></i> Toko Madu Barokah
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link text-gelap" href="{{ route('frontend.home') }}">
                            <i class="fas fa-home"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-gelap" href="{{ route('frontend.home') }}#produk">
                            <i class="fas fa-box"></i> Produk
                        </a>
                    </li>
                    @auth
                    <li class="nav-item">
                        <a class="nav-link text-gelap position-relative" href="{{ route('frontend.cart.index') }}" id="cartLink">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-badge" id="cartBadge" style="display: none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-gelap" href="{{ route('frontend.history.index') }}">
                            <i class="fas fa-history"></i> Riwayat Transaksi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-gelap" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link text-gelap cart-guest" href="#" onclick="showLoginPrompt()">
                            <i class="fas fa-shopping-cart position-relative"></i>
                            <span class="cart-badge" id="cartBadgeGuest" style="display: none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="btn btn-orange btn-sm ml-2">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="bg-gelap py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-white">Toko Madu Barokah</h5>
                    <p class="text-white-50">Madu asli berkualitas tinggi langsung dari peternak terpercaya.</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-white">Kontak Kami</h6>
                    <p class="text-white-50 mb-1">
                        <i class="fas fa-map-marker-alt"></i> Jl. Cut Nyak Dien, Mlati Kidul, Kota Kudus
                    </p>
                    <p class="text-white-50 mb-1">
                        <i class="fas fa-phone"></i> <a href="https://wa.me/628977136172" class="text-white-50">08977136172</a>
                    </p>
                    <p class="text-white-50">
                        <i class="fas fa-envelope"></i> info@tokomadubarokah.com
                    </p>
                </div>
            </div>
            <hr class="border-light">
            <div class="text-center">
                <p class="mb-0 text-white-50">&copy; {{ date('Y') }} Toko Madu Barokah. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/landingpage/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/aos/aos.js') }}"></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Initialize AOS
        AOS.init();

        // Navbar scroll effect
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('#mainNavbar').addClass('scrolled');
            } else {
                $('#mainNavbar').removeClass('scrolled');
            }
        });

        // CSRF Token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Cart Management
        class CartManager {
            constructor() {
                this.cartKey = 'madu_barokah_cart';
                this.updateCartDisplay();
            }

            getCart() {
                const cart = localStorage.getItem(this.cartKey);
                return cart ? JSON.parse(cart) : [];
            }

            addToCart(productId, productName, price, quantity = 1, image = null) {
                let cart = this.getCart();
                const existingItem = cart.find(item => item.id == productId);

                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    cart.push({
                        id: productId,
                        name: productName,
                        price: price,
                        quantity: quantity,
                        image: image,
                        added_at: new Date().toISOString()
                    });
                }

                localStorage.setItem(this.cartKey, JSON.stringify(cart));
                this.updateCartDisplay();

                // Show success message
                this.showNotification('Produk berhasil ditambahkan ke keranjang!', 'success');
            }

            removeFromCart(productId) {
                let cart = this.getCart();
                cart = cart.filter(item => item.id != productId);
                localStorage.setItem(this.cartKey, JSON.stringify(cart));
                this.updateCartDisplay();
            }

            updateQuantity(productId, quantity) {
                let cart = this.getCart();
                const item = cart.find(item => item.id == productId);
                if (item) {
                    item.quantity = quantity;
                    if (quantity <= 0) {
                        this.removeFromCart(productId);
                    } else {
                        localStorage.setItem(this.cartKey, JSON.stringify(cart));
                        this.updateCartDisplay();
                    }
                }
            }

            clearCart() {
                localStorage.removeItem(this.cartKey);
                this.updateCartDisplay();
            }

            getCartTotal() {
                const cart = this.getCart();
                return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
            }

            getCartCount() {
                const cart = this.getCart();
                return cart.reduce((count, item) => count + item.quantity, 0);
            }

            updateCartDisplay() {
                const count = this.getCartCount();
                const badge = document.getElementById('cartBadge');
                const badgeGuest = document.getElementById('cartBadgeGuest');

                if (count > 0) {
                    if (badge) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'flex';
                    }
                    if (badgeGuest) {
                        badgeGuest.textContent = count > 99 ? '99+' : count;
                        badgeGuest.style.display = 'flex';
                    }
                } else {
                    if (badge) badge.style.display = 'none';
                    if (badgeGuest) badgeGuest.style.display = 'none';
                }
            }

            showNotification(message, type = 'info') {
                Swal.fire({
                    icon: type === 'success' ? 'success' : type === 'error' ? 'error' : 'info',
                    title: type === 'success' ? 'Berhasil!' : type === 'error' ? 'Error!' : 'Info',
                    text: message,
                    showConfirmButton: false,
                    timer: 3000,
                    toast: true,
                    position: 'top-end',
                    timerProgressBar: true
                });
            }

            // Sync cart to database when user logs in
            syncCartToDatabase() {
                const cart = this.getCart();
                if (cart.length > 0) {
                    $.ajax({
                        url: '{{ route("frontend.cart.sync") }}',
                        method: 'POST',
                        data: {
                            cart: cart
                        },
                        success: (response) => {
                            if (response.success) {
                                this.clearCart();
                                console.log('Cart synced to database');
                            }
                        },
                        error: (xhr) => {
                            console.error('Failed to sync cart:', xhr);
                        }
                    });
                }
            }
        }

        // Initialize cart manager
        const cartManager = new CartManager();

        // Show login prompt for guests
        function showLoginPrompt() {
            if (confirm('Anda perlu login untuk melihat keranjang. Login sekarang?')) {
                window.location.href = '{{ route("login") }}';
            }
        }

        // Loading overlay functions
        function showLoading() {
            $('#loadingOverlay').fadeIn();
        }

        function hideLoading() {
            $('#loadingOverlay').fadeOut();
        }

        // Auto-sync cart when page loads for authenticated users
        @auth
        $(document).ready(function() {
            // Sync local storage cart to database
            cartManager.syncCartToDatabase();
        });
        @endauth
    </script>

    @stack('scripts')
</body>

</html>