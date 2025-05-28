<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Toko Madu Barokah</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/bootstrap.min.css') }}">
    <!-- linkstyle -->
    <link rel="stylesheet" href="{{ asset('assets/landingpage/landingpage.css') }}">
    {{-- Font Awesome --}}
    <link href="{{ asset('assets/sbadmin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    {{-- Font Awesome CDN sebagai fallback --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    {{-- Animasi --}}
    <link rel="stylesheet" href="{{ asset('assets/aos/aos.css') }}">

    @stack('styles')
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Memproses<span class="loading-dots">...</span></p>
        </div>
    </div>

    <!-- Navbar -->
    @include('partials.frontend.navbar')

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    @include('partials.frontend.footer')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/landingpage/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/aos/aos.js') }}"></script>
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
            $('#loadingOverlay').removeClass('hide');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hide');
        }

        // Auto hide loading after page load
        $(window).on('load', function() {
            setTimeout(function() {
                hideLoading();
            }, 500); // Hide after 1 second
        });

        // Fallback if window.load doesn't fire
        $(document).ready(function() {
            setTimeout(function() {
                hideLoading();
            }, 600); // Hide after 1.5 seconds as fallback
        });

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