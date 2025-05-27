@extends('layouts.frontend')

@section('title', 'Beranda')

@section('content')
<!-- Banner -->
<div class="jumbotron jumbotron-fluid bg-orange">
    <div class="container text-center">
        <h1 class="text-orange display-3" data-aos="fade-right" data-aos-duration="2000">{{ shop_setting('name', 'Madu Lebah Liar') }}</h1>
        <p class="lead" data-aos="fade-right" data-aos-duration="2000">{{ shop_setting('tagline', 'Nikmati madu asli lebah liar berkualitas tinggi') }}</p>
        <a href="#produk" class="btn btn-orange btn-lg" data-aos="fade-up" data-aos-duration="2000">
            <i class="fas fa-shopping-bag"></i> Pesan Sekarang
        </a>
    </div>
</div>

<!-- Sejarah Toko -->
<div class="container my-5" id="sejarah">
    <h2 class="text-center text-gelap mb-4">Sejarah Toko</h2>
    <div class="row">
        <div class="col-md-12 text-justify">
            <p>{{ shop_setting('name', 'Toko Madu Barokah') }} berlokasi di {{ shop_setting('address', 'Jl. Cut Nyak Dien, Mlati Kidul, Kota Kudus') }}. Beroperasi sejak tahun
                2016 untuk menambah pendapatan usaha. Toko ini merupakan usaha kecil yang bergerak dalam bidang
                penjualan atau pemasaran yang menyediakan produk madu yang diperoleh dari ternak madu asli lebah
                liar dan di panen dengan baik.</p>
        </div>
    </div>
</div>

<!-- Produk -->
<div class="container my-5" id="produk">
    <h2 class="text-center text-gelap mb-4 pt-5">Produk Kami</h2>
    <div class="row">
        @foreach ($produks as $produk)
        <div class="col-6 col-md-4 mb-4 product-container" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
            <div class="card-product h-100">
                <div class="position-relative">
                    @if($produk->gambar)
                    <img src="{{ asset('storage/' . $produk->gambar) }}" class="card-img-top"
                        alt="{{ $produk->nama_produk }}" style="height: 200px; object-fit: cover;">
                    @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                    @endif

                    @if($produk->kategori)
                    <span class="category-badge text-white"
                        style="background-color: {{ $produk->kategori->warna ?? '#6C757D' }};">
                        {{ $produk->kategori->nama_kategori }}
                    </span>
                    @endif

                    @if($produk->stok <= 5 && $produk->stok > 0)
                        <span class="badge badge-warning position-absolute" style="top: 10px; right: 10px; font-size: 0.7em;">
                            Stok Terbatas
                        </span>
                        @elseif($produk->stok <= 0)
                            <span class="badge badge-danger position-absolute" style="top: 10px; right: 10px; font-size: 0.7em;">
                            Habis
                            </span>
                            @endif
                </div>

                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-gelap mb-2">{{ $produk->nama_produk }}</h5>
                    <p class="card-text text-muted small mb-3">{{ Str::limit($produk->deskripsi, 60) }}</p>

                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="h4 text-gelap mb-0 font-weight-bold">
                                    Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                </span>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-box"></i> Stok: {{ $produk->stok }}
                            </small>
                        </div>

                        <div class="product-actions">
                            <button class="btn btn-orange btn-sm btn-cart add-to-cart-btn"
                                data-id="{{ $produk->id }}"
                                data-name="{{ $produk->nama_produk }}"
                                data-price="{{ $produk->harga }}"
                                data-image="{{ $produk->gambar ? asset('storage/' . $produk->gambar) : '' }}"
                                data-stock="{{ $produk->stok }}"
                                {{ $produk->stok <= 0 ? 'disabled' : '' }}>
                                @if($produk->stok <= 0)
                                    <i class="fas fa-times-circle"></i> Habis
                                    @else
                                    <i class="fas fa-cart-plus"></i> Keranjang
                                    @endif
                            </button>

                            @auth
                            <button class="btn btn-success btn-sm btn-cart checkout-direct-btn"
                                data-id="{{ $produk->id }}"
                                data-name="{{ $produk->nama_produk }}"
                                data-price="{{ $produk->harga }}"
                                data-stock="{{ $produk->stok }}"
                                {{ $produk->stok <= 0 ? 'disabled' : '' }}>
                                @if($produk->stok <= 0)
                                    <i class="fas fa-times-circle"></i> Habis
                                    @else
                                    <i class="fas fa-shopping-cart"></i> Beli Sekarang
                                    @endif
                            </button>
                            @else
                            <button class="btn btn-success btn-sm btn-cart" onclick="showLoginPrompt()">
                                <i class="fas fa-shopping-cart"></i> Beli Sekarang
                            </button>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($produks->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Belum Ada Produk</h5>
        <p class="text-muted">Produk akan segera tersedia</p>
    </div>
    @endif
</div>

<!-- Kontak -->
<div class="container my-5" id="kontak">
    <div class="row">
        <div class="col-md-6" data-aos="fade-right">
            <h2 class="text-gelap mb-4">
                <i class="fas fa-phone"></i> Hubungi Kami
            </h2>
            <div class="card">
                <div class="card-body">
                    <table class="table table-borderless text-gelap">
                        <tr>
                            <td><i class="fas fa-map-marker-alt"></i> <strong>Alamat</strong></td>
                            <td><strong>:</strong></td>
                            <td>{{ shop_setting('address', 'Jl. Cut Nyak Dien, Mlati Kidul, Kota Kudus') }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-phone"></i> <strong>Telepon</strong></td>
                            <td><strong>:</strong></td>
                            <td><a href="https://wa.me/{{ shop_setting('whatsapp', '628977136172') }}" target="_blank" class="text-gelap">{{ shop_setting('phone', '08977136172') }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-envelope"></i> <strong>Email</strong></td>
                            <td><strong>:</strong></td>
                            <td><a href="mailto:{{ shop_setting('email', 'info@tokomadubarokah.com') }}" class="text-gelap">{{ shop_setting('email', 'info@tokomadubarokah.com') }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-clock"></i> <strong>Jam Buka</strong></td>
                            <td><strong>:</strong></td>
                            <td>08:00 - 17:00 WIB</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6" data-aos="fade-left">
            <div class="embed-responsive embed-responsive-4by3 mb-3">
                <iframe class="embed-responsive-item"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.152479539574!2d110.84144091478017!3d-7.006493894928826!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e708a065a6c6c3b%3A0x4828d63c08682b!2sJl.%20Cut%20Nyak%20Dien%2C%20Mlati%20Kidul%2C%20Kota%20Kudus!5e0!3m2!1sen!2sid!4v1621311688062!5m2!1sen!2sid"
                    allowfullscreen="" loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .jumbotron {
        height: 100vh;
        display: flex;
        align-items: center;
        background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)),
                         url('{{ asset("assets/landingpage/images/banner.png") }}');
        background-size: cover;
        background-position: center;
        margin-bottom: 0;
    }

    .add-to-cart-btn {
        transition: all 0.3s ease;
    }

    .add-to-cart-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    }

    .add-to-cart-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .checkout-direct-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Cart notification function menggunakan SweetAlert
        function showNotification(message, type = 'info') {
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

        @auth
        // Function to update cart count from server for authenticated users
        function updateCartCountFromServer() {
            $.ajax({
                url: '{{ route("frontend.cart.count") }}',
                method: 'GET',
                success: function(response) {
                    const count = response.success ? response.count : 0;
                    const badge = document.getElementById('cartBadge');

                    if (count > 0) {
                        if (badge) {
                            badge.textContent = count > 99 ? '99+' : count;
                            badge.style.display = 'flex';
                        }
                    } else {
                        if (badge) badge.style.display = 'none';
                    }
                },
                error: function(xhr) {
                    console.log('Failed to get cart count:', xhr);
                }
            });
        }

        // Load cart count on page load for authenticated users
        updateCartCountFromServer();
        @else
        // Function to add to localStorage cart for guests
        function addToLocalStorageCart(productId, productName, price, quantity = 1, image = null) {
            const cartKey = 'madu_barokah_cart';
            let cart = [];

            const cartData = localStorage.getItem(cartKey);
            if (cartData) {
                cart = JSON.parse(cartData);
            }

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

            localStorage.setItem(cartKey, JSON.stringify(cart));

            // Update cart display
            updateLocalStorageCartDisplay();

            // Show success message
            showNotification('Produk berhasil ditambahkan ke keranjang!', 'success');
        }

        // Function to update localStorage cart display
        function updateLocalStorageCartDisplay() {
            const cartKey = 'madu_barokah_cart';
            const cartData = localStorage.getItem(cartKey);
            const cart = cartData ? JSON.parse(cartData) : [];
            const count = cart.reduce((total, item) => total + item.quantity, 0);

            const badge = document.getElementById('cartBadgeGuest');

            if (count > 0) {
                if (badge) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'flex';
                }
            } else {
                if (badge) badge.style.display = 'none';
            }
        }

        // For guests, load cart from localStorage
        updateLocalStorageCartDisplay();
        @endauth

        // Add to cart functionality
        $('.add-to-cart-btn').click(function() {
            const btn = $(this);
            const productId = btn.data('id');
            const productName = btn.data('name');
            const price = parseInt(btn.data('price'));
            const image = btn.data('image');
            const stock = parseInt(btn.data('stock'));

            if (stock <= 0) {
                showNotification('Produk ini sedang habis', 'error');
                return;
            }

            // Add loading state
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menambah...');

            @auth
            // For authenticated users, add to database
            $.ajax({
                url: '{{ route("frontend.cart.add") }}',
                method: 'POST',
                data: {
                    id_produk: productId,
                    quantity: 1
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        updateCartCountFromServer();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Gagal menambahkan produk ke keranjang';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showNotification(message, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
            @else
            // For guests, add to localStorage
            setTimeout(() => {
                addToLocalStorageCart(productId, productName, price, 1, image);
                btn.prop('disabled', false).html(originalText);
            }, 500);
            @endauth
        });

        // Checkout direct functionality
        $('.checkout-direct-btn').click(function() {
            const btn = $(this);
            const productId = btn.data('id');
            const productName = btn.data('name');
            const price = parseInt(btn.data('price'));
            const stock = parseInt(btn.data('stock'));

            if (stock <= 0) {
                showNotification('Produk ini sedang habis', 'error');
                return;
            }

            // Show confirmation dialog
            Swal.fire({
                title: 'Checkout Langsung',
                html: `
                <div class="text-left">
                    <p><strong>Produk:</strong> ${productName}</p>
                    <p><strong>Harga:</strong> Rp ${price.toLocaleString('id-ID')}</p>
                    <p><strong>Jumlah:</strong> 
                        <input type="number" id="checkoutQuantity" value="1" min="1" max="${stock}" class="form-control" style="width: 80px; display: inline-block;">
                    </p>
                    <hr>
                    <p><strong>Total:</strong> <span id="checkoutTotal">Rp ${price.toLocaleString('id-ID')}</span></p>
                </div>
            `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-bolt"></i> Lanjut Checkout',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                preConfirm: () => {
                    const quantity = parseInt(document.getElementById('checkoutQuantity').value);
                    if (quantity < 1 || quantity > stock) {
                        Swal.showValidationMessage(`Jumlah harus antara 1 dan ${stock}`);
                        return false;
                    }
                    return quantity;
                },
                didOpen: () => {
                    // Update total when quantity changes
                    document.getElementById('checkoutQuantity').addEventListener('input', function() {
                        const quantity = parseInt(this.value) || 1;
                        const total = price * quantity;
                        document.getElementById('checkoutTotal').textContent = `Rp ${total.toLocaleString('id-ID')}`;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const quantity = result.value;

                    // Add to cart with specified quantity and redirect to checkout
                    $.ajax({
                        url: '{{ route("frontend.cart.add") }}',
                        method: 'POST',
                        data: {
                            id_produk: productId,
                            quantity: quantity
                        },
                        success: function(response) {
                            if (response.success) {
                                // Redirect to checkout
                                window.location.href = '{{ route("frontend.checkout") }}';
                            } else {
                                showNotification(response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let message = 'Gagal memproses checkout';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            showNotification(message, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection