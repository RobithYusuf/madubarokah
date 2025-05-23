<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Madu Barokah</title>
    {{-- Bootstrap --}}
    <link rel="stylesheet" href="{{ asset('assets/landingpage/css/bootstrap.min.css') }}">
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

        .navbar .text-gelap:hover {
            color: #664200;
        }

        .btn-orange {
            background-color: #FFA500;
            color: #fff;
        }

        .btn-orange:hover {
            background-color: #cc8400;
            color: #fff;
        }

        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            border: 3px solid #cc8400;
            transform: translateY(-10px);
            /* Move the card upwards on hover */
            transition: transform 0.3s ease, border 0.3s ease;
            /* Smooth transition */
        }

        #produk a {
            color: black;
            text-decoration: none;
        }

        .card-img-top {
            width: 100%;
            /* Lebar gambar penuh mengikuti card */
            height: 200px;
            /* Tetapkan tinggi seragam */
            object-fit: cover;
            /* Memastikan gambar tetap proporsional tanpa distorsi */
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }


        /* Styles untuk jumbotron fullscreen */
        .jumbotron {
            height: 100vh;
            display: flex;
            align-items: center;
            background-image: url('{{ asset('assets/landingpage/images/banner.png') }}');
            background-size: cover;
            background-position: center;
        }

        .jumbotron::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .jumbotron .container {
            /* background-color: rgba(255, 255, 255, 0.8); */
            padding: 2rem;
            border-radius: 10px;
            z-index: 1;
        }

        .jumbotron .ordernow {
            border: none;
            /* Hilangkan border */
            padding: 10px 20px;
            /* Atur padding */
            text-align: center;
            /* Teks di tengah */
            text-decoration: none;
            /* Hilangkan garis bawah teks */
            display: inline-block;
            /* Membuat tombol sebagai elemen inline-block */
            font-size: 16px;
            /* Ukuran font */
            cursor: pointer;
            /* Ubah kursor menjadi pointer */
            border-radius: 4px;
            /* Sudut melengkung (opsional) */
            /* transition: background-color 0.3s ease; Efek transisi saat hover */
        }

        /* Styles untuk navbar mengambang */
        .navbar {
            background-color: rgba(255, 255, 255, 0.8);
            width: 90%;
            margin: 20px auto 0 auto;
            /* Jarak atas 20px, margin kiri-kanan auto */
            border-radius: 20px;
            /* Sudut membulat */
            transition: all 0.3s ease;
        }

        /* Custom styles for navbar toggler */
        .navbar-toggler {
            border-color: #cc8400;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=UTF8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='rgba%28 204, 132, 0, 1 %29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        /* Styles untuk navbar ketika di-scroll */
        .navbar.scrolled {
            margin-top: 0;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 767.98px) {
            .navbar-collapse {
                position: absolute;
                top: 110%;
                left: 0;
                width: 100%;
                background-color: rgba(255, 255, 255, 0.9);
                z-index: 1;
                padding: 1rem;
                border-radius: 20px;
            }

            .nav-link {
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>

<body>

    {{-- Header --}}
    @include('partials.navbarLandingpage')
    
    <!-- Banner -->
    <div class="jumbotron jumbotron-fluid bg-orange">
        <div class="container text-center">
            <h1 class="text-orange display-3" data-aos="fade-right" data-aos-duration="2000">Madu Lebah Liar</h1>
            <p class="lead" data-aos="fade-right" data-aos-duration="2000">Nikmati madu asli lebah liar berkualitas
                tinggi</p>
            <a href="#produk" class="ordernow btn-orange" data-aos="fade-up" data-aos-duration="2000">Pesan Sekarang</a>
        </div>
    </div>

    <!-- Sejarah Toko -->
    <div class="container my-5" id="sejarah">
        <h2 class="text-center text-gelap mb-4">Sejarah Toko</h2>
        <div class="row">
            <div class="col-md-12">
                <p>Toko Madu Barokah berlokasi di Jl. Cut Nyak Dien, Mlati Kidul, Kota Kudus. Beroperasi sejak tahun
                    2016 untuk menambah pendapatan usaha. Toko ini merupakan usaha kecil yang bergerak dalam bidang
                    penjualan atau pemasaran yang menyediakan produk madu yang diperoleh dari ternak madu asli lebah
                    liar dan di panen dengan baik.</p>
            </div>
        </div>
    </div>

    <!-- Produk -->
    {{-- <div class="container my-5" id="produk">
        <h2 class="text-center text-gelap mb-4 pt-5">Produk Kami</h2>
        <div class="row">
            <div class="col-6 col-md-4 mb-4">
                <a href="#">
                    <div class="card">
                        <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Madu Botol Besar">
                        <div class="card-body">
                            <h5 class="card-title">Madu Botol Besar</h5>
                            <p class="card-text d-flex justify-content-between">
                                <span class="harga h6 text-gelap">
                                    Rp. <span class="h5">140.000</span>
                                </span>
                                <span class="terjual h6 text-gelap">
                                    9 Terjual
                                </span>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 mb-4">
                <a href="#">
                    <div class="card">
                        <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Madu Botol Sedang">
                        <div class="card-body">
                            <h5 class="card-title">Madu Botol Sedang</h5>
                            <p class="card-text d-flex justify-content-between">
                                <span class="harga h6 text-gelap">
                                    Rp. <span class="h5">87.000</span>
                                </span>
                                <span class="terjual h6 text-gelap">
                                    9 Terjual
                                </span>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 mb-4">
                <a href="#">
                    <div class="card">
                        <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Madu Botol Kecil">
                        <div class="card-body">
                            <h5 class="card-title">Madu Botol Kecil</h5>
                            <p class="card-text d-flex justify-content-between">
                                <span class="harga h6 text-gelap">
                                    Rp. <span class="h5">45.000</span>
                                </span>
                                <span class="terjual h6 text-gelap">
                                    9 Terjual
                                </span>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div> --}}
    <div class="container my-5" id="produk">
        <h2 class="text-center text-gelap mb-4 pt-5">Produk Kami</h2>
        <div class="row">
            @foreach ($produks as $produk)
                <div class="col-6 col-md-4 mb-4">
                    <a href="#" data-toggle="modal" data-target="#produkModal{{ $produk->id }}"> {{-- Link ke detail produk --}}
                        <div class="card">
                            <img src="{{ asset('storage/' . $produk->gambar) }}" class="card-img-top"
                                alt="{{ $produk->nama_produk }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $produk->nama_produk }}</h5>
                                <p class="card-text d-flex justify-content-between">
                                    <span class="harga h6 text-gelap">
                                        Rp. <span class="h5">{{ number_format($produk->harga, 0, ',', '.') }}</span>
                                    </span>
                                    <span class="terjual h6 text-gelap">
                                        {{ $produk->terjual ?? 0 }} Terjual
                                    </span>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Modal Detail Produk -->
                <div class="modal fade" id="produkModal{{ $produk->id }}" tabindex="-1"
                    aria-labelledby="produkModalLabel{{ $produk->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $produk->name }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <img src="{{ asset('storage/' . $produk->image) }}" class="img-fluid mb-3"
                                    alt="{{ $produk->name }}">
                                <p>{{ $produk->description }}</p>
                                <p><strong>Harga: </strong>Rp{{ number_format($produk->price, 0, ',', '.') }}</p>
                                <button class="btn btn-success add-to-cart" data-id="{{ $produk->id }}">Tambah ke
                                    Keranjang</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    <!-- Statistik -->
    <div class="container my-5">
        <h2 class="text-center text-gelap mb-4">Statistik Penjualan</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-gelap">
                        <h5 class="card-title">Jumlah Produk Terjual Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="text-gelap">1,500 botol</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-gelap">
                        <h5 class="card-title">Produk Terlaris Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="text-gelap">Madu Botol Sedang</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5" id="kontak">
        <div class="row">
            <div class="col-md-6">
                <h2 class="text-gelap mb-4">Hubungi Kami</h2>
                <table class="table border-0 text-gelap">
                    <tr>
                        <td><strong>Alamat</strong></td>
                        <td><strong>:</strong></td>
                        <td>Jl. Cut Nyak Dien, Mlati Kidul, Kota Kudus</td>
                    </tr>
                    <tr>
                        <td><strong>Telepon</strong></td>
                        <td><strong>:</strong></td>
                        <td><a href="https://wa.me/628977136172" target="_blank" class="text-gelap">08977136172</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td><strong>:</strong></td>
                        <td><a href="mailto:info@tokomadubarokah.com" class="text-gelap">info@tokomadubarokah.com</a>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div class="embed-responsive embed-responsive-4by3 mb-3">
                    <iframe class="embed-responsive-item"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.152479539574!2d110.84144091478017!3d-7.006493894928826!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e708a065a6c6c3b%3A0x4828d63c08682b!2sJl.%20Cut%20Nyak%20Dien%2C%20Mlati%20Kidul%2C%20Kota%20Kudus!5e0!3m2!1sen!2sid!4v1621311688062!5m2!1sen!2sid"
                        allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    @include('partials.footerLandingpage')

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="{{ asset('assets/landingpage/js/bootstrap.min.js') }}"></script>

    {{-- Animasi --}}
    <script src="{{ asset('assets/aos/aos.js') }}"></script>
    <script>
        AOS.init();
    </script>
</body>

</html>
