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
