<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion shadow-sm" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('frontend.home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            @if(shop_setting('logo'))
            <img src="{{ asset('storage/' . shop_setting('logo')) }}" alt="{{ shop_setting('name') }}" style="height: 36px; width: auto;">
            @else
            <i class="fas fa-spa"></i>
            @endif
        </div>
        <div class="sidebar-brand-text mx-3">{{ shop_setting('name', 'Madu') }}</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Data Master
    </div>

    <!-- Nav Item - Kategori -->
    <li class="nav-item {{ request()->is('admin/kategori*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.kategori.index') }}">
            <i class="fas fa-fw fa-tags"></i>
            <span>Kategori Produk</span>
        </a>
    </li>

    <!-- Nav Item - Produk -->
    <li class="nav-item {{ request()->is('admin/produk*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.produk.index') }}">
            <i class="fas fa-fw fa-boxes"></i>
            <span>Produk</span>
        </a>
    </li>
    <!-- Nav Item - Pengiriman -->
    <li class="nav-item {{ request()->is('admin/shipping*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.shipping.index') }}">
            <i class="fas fa-fw fa-shipping-fast"></i>
            <span>Pengiriman</span>
        </a>
    </li>

    <!-- Nav Item - Payment Channel -->
    <li class="nav-item {{ request()->is('admin/payment*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.payment.index') }}">
            <i class="fas fa-fw fa-credit-card"></i>
            <span>Payment Channel</span>
        </a>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Transaksi
    </div>

    <!-- Nav Item - Pesanan -->
    <li class="nav-item {{ request()->is('admin/pesanan*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.pesanan.index') }}">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Pesanan</span>
        </a>
    </li>



    <!-- Nav Item - Laporan -->
    <li class="nav-item {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLaporan"
            aria-expanded="{{ request()->routeIs('admin.laporan.*') ? 'true' : 'false' }}" aria-controls="collapseLaporan">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Laporan</span>
        </a>
        <div id="collapseLaporan" class="collapse {{ request()->routeIs('admin.laporan.*') ? 'show' : '' }}"
            aria-labelledby="headingLaporan" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Menu Laporan:</h6>
                
                <a class="collapse-item {{ request()->routeIs('admin.laporan.index') || request()->routeIs('admin.laporan.transaksi') ? 'active' : '' }}" 
                   href="{{ route('admin.laporan.index') }}">
                    <i class="fas fa-list mr-1"></i>Laporan Transaksi
                </a>
                
                <a class="collapse-item {{ request()->routeIs('admin.laporan.penjualan') ? 'active' : '' }}" 
                   href="{{ route('admin.laporan.penjualan') }}">
                    <i class="fas fa-chart-line mr-1"></i>Laporan Penjualan
                </a>
                
                <a class="collapse-item {{ request()->routeIs('admin.laporan.produk') ? 'active' : '' }}" 
                   href="{{ route('admin.laporan.produk') }}">
                    <i class="fas fa-box mr-1"></i>Laporan Produk
                </a>
                
                <a class="collapse-item {{ request()->routeIs('admin.laporan.pelanggan') ? 'active' : '' }}" 
                   href="{{ route('admin.laporan.pelanggan') }}">
                    <i class="fas fa-users mr-1"></i>Laporan Pelanggan
                </a>
                
                <a class="collapse-item {{ request()->routeIs('admin.laporan.pengiriman') ? 'active' : '' }}" 
                   href="{{ route('admin.laporan.pengiriman') }}">
                    <i class="fas fa-shipping-fast mr-1"></i>Laporan Pengiriman
                </a>
                
            
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Pengaturan
    </div>

    <!-- Nav Item - Pengguna -->
    <li class="nav-item {{ request()->is('admin/user*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.user.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Kelola Pengguna</span>
        </a>
    </li>

    <!-- Nav Item - Pengaturan Toko -->
    <!-- Nav Item - Pengaturan Toko -->
    <li class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.settings.shop') }}">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Pengaturan Toko</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->