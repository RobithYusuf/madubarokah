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
                <li class="nav-item">
                    <a class="nav-link text-gelap" href="{{ route('frontend.home') }}#kontak">
                        <i class="fas fa-phone"></i> Kontak
                    </a>
                </li>
                @auth
                <li class="nav-item">
                    <a class="nav-link text-gelap position-relative" href="{{ route('frontend.cart.index') }}" id="cartLink">
                        <i class="fas fa-shopping-cart"></i> Keranjang
                        <span class="cart-badge" id="cartBadge" style="display: none;">0</span>
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
                        <i class="fas fa-shopping-cart position-relative"></i> Keranjang
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Navbar scroll effect
    $(document).ready(function() {
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('#mainNavbar').addClass('scrolled');
            } else {
                $('#mainNavbar').removeClass('scrolled');
            }
        });

        // Cart Management untuk navbar
        function updateCartDisplay() {
            const cartData = localStorage.getItem('madu_barokah_cart');
            const cart = cartData ? JSON.parse(cartData) : [];
            const count = cart.reduce((total, item) => total + item.quantity, 0);

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

        // Show login prompt for guests
        window.showLoginPrompt = function() {
            Swal.fire({
                title: 'Login Diperlukan',
                text: 'Anda perlu login untuk melihat keranjang.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Login Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route("login") }}';
                }
            });
        }

        // Update cart display on page load
        updateCartDisplay();

        // Listen for storage changes to update cart count
        window.addEventListener('storage', updateCartDisplay);

        // Custom event listener untuk update cart dari halaman lain
        document.addEventListener('cartUpdated', updateCartDisplay);
    });
</script>