    <!-- Navbar -->
    <nav class="navbar fixed-top navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand text-gelap" href="#">Toko Madu Barokah</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link text-gelap" href="#">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-gelap" href="#produk">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-gelap" href="#kontak">Kontak</a>
                    </li>
                    <li class="nav-item">
                        @auth
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">Logout</button>
                            </form>
                        @endauth
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-link nav-link">Login</a>
                        @endguest
                    </li>
                </ul>
            </div>
        </div>
    </nav>