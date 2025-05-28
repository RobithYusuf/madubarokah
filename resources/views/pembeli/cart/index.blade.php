@extends('layouts.frontend')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="cart-container">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent px-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}" class="text-gelap">Beranda</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Keranjang Belanja</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card-cart">
                    <div class="card-header bg-gelap">
                        <h4 class="mb-0 text-white">
                            <i class="fas fa-shopping-cart"></i> Keranjang Belanja
                        </h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" style="display: none;" id="successAlert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" style="display: none;" id="errorAlert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                        </div>
                        @endif

                        @if($cartItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th><i class="fas fa-box"></i> Produk</th>
                                        <th><i class="fas fa-tag"></i> Harga</th>
                                        <th><i class="fas fa-sort-numeric-up"></i> Jumlah</th>
                                        <th><i class="fas fa-calculator"></i> Subtotal</th>
                                        <th><i class="fas fa-cogs"></i> Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cartItems as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->produk->gambar)
                                                <div class="mr-3" style="flex-shrink: 0;">
                                                    <img src="{{ asset('storage/' . $item->produk->gambar) }}"
                                                        alt="{{ $item->produk->nama_produk }}"
                                                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                                        class="shadow-sm">
                                                </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-1 text-gelap">{{ $item->produk->nama_produk }}</h6>
                                                    @if($item->produk->kategori)
                                                    <span class="badge text-white"
                                                        style="background-color: {{ $item->produk->kategori->warna ?? '#6C757D' }}; font-size: 0.7em;">
                                                        {{ $item->produk->kategori->nama_kategori }}
                                                    </span>
                                                    @endif
                                                    <small class="text-muted d-block mt-1">
                                                        Stok tersedia: {{ $item->produk->stok }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gelap font-weight-bold">
                                                Rp {{ number_format($item->produk->harga, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('frontend.cart.update.item', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <div class="input-group" style="width: 140px;">
                                                    <input type="number" name="quantity" value="{{ $item->quantity }}"
                                                        min="1" max="{{ $item->produk->stok }}"
                                                        class="form-control form-control-sm text-center">
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-outline-primary btn-sm" title="Update jumlah">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <span class="text-gelap font-weight-bold h6">
                                                Rp {{ number_format($item->quantity * $item->produk->harga, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('frontend.cart.remove', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm delete-item-btn"
                                                    data-form-id="delete-form-{{ $item->id }}"
                                                    title="Hapus dari keranjang">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>

                                            <form id="delete-form-{{ $item->id }}" action="{{ route('frontend.cart.remove', $item->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Total Section -->
                        <div class="row mt-4">
                            <div class="col-md-8">
                                <!-- Cart Actions -->
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('frontend.home') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left"></i> Lanjut Belanja
                                    </a>
                                    <button type="button" class="btn btn-outline-danger clear-cart-btn"
                                        data-form-id="clear-cart-form">
                                        <i class="fas fa-trash"></i> Kosongkan Keranjang
                                    </button>
                                </div>

                                <form id="clear-cart-form" action="{{ route('frontend.cart.clear') }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                            <div class="col-md-4">
                                <!-- Total Card -->
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-gelap">
                                            <i class="fas fa-calculator"></i> Ringkasan Pesanan
                                        </h5>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal ({{ $cartItems->sum('quantity') }} item):</span>
                                            <span class="font-weight-bold text-gelap">
                                                Rp {{ number_format($total, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="h5 text-gelap">Total:</span>
                                            <span class="h5 text-gelap font-weight-bold">
                                                Rp {{ number_format($total, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        <a href="{{ route('frontend.checkout') }}" class="btn btn-orange btn-block btn-lg">
                                            <i class="fas fa-shopping-cart"></i> Pesan Sekarang
                                        </a>
                                        <small class="text-muted d-block mt-2 text-center">
                                            * Harga belum termasuk ongkos kirim
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Empty Cart -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-shopping-cart fa-4x text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-3">Keranjang Belanja Kosong</h4>
                            <p class="text-muted mb-4">
                                Belum ada produk dalam keranjang belanja Anda.<br>
                                Yuk, mulai berbelanja dan temukan produk madu berkualitas!
                            </p>
                            <a href="{{ route('frontend.home') }}" class="btn btn-orange btn-lg">
                                <i class="fas fa-shopping-bag"></i> Mulai Berbelanja
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@push('styles')
<style>
    .breadcrumb-item a {
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: #cc8400 !important;
        transform: translateX(2px);
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #cc8400;
        font-size: 0.9em;
    }

    .table td {
        vertical-align: middle;
        border-color: #f8f9fa;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(204, 132, 0, 0.05);
    }

    .input-group input:focus {
        border-color: #cc8400;
        box-shadow: 0 0 0 0.2rem rgba(204, 132, 0, 0.25);
    }

    .btn-outline-primary {
        border-color: #cc8400;
        color: #cc8400;
    }

    .btn-outline-primary:hover {
        background-color: #cc8400;
        border-color: #cc8400;
    }

    .gap-2>* {
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.9em;
        }

        .d-flex.gap-2 {
            flex-direction: column;
        }

        .gap-2>* {
            margin-right: 0;
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Show SweetAlert for session messages
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: @json(session('success')),
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        @endif

        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: @json(session('error')),
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
        @endif

        // Delete single item confirmation
        $('.delete-item-btn').on('click', function() {
            const formId = $(this).data('form-id');

            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Yakin ingin menghapus produk ini dari keranjang?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        });

        // Clear cart confirmation  
        $('.clear-cart-btn').on('click', function() {
            const formId = $(this).data('form-id');

            Swal.fire({
                title: 'Kosongkan Keranjang?',
                text: 'Yakin ingin menghapus semua produk dari keranjang? Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kosongkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        });
        // Auto-submit form when quantity changes
        $('input[name="quantity"]').on('change', function() {
            const form = $(this).closest('form');
            const quantity = parseInt($(this).val());
            const max = parseInt($(this).attr('max'));
            const productName = $(this).closest('tr').find('h6').text();

            if (quantity > max) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Tidak Mencukupi',
                    text: `Stok ${productName} hanya tersedia ${max} unit`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
                $(this).val(max);
                return;
            }

            if (quantity < 1) {
                $(this).val(1);
                return;
            }

            // Show loading on submit button
            const $submitBtn = $(this).siblings('.input-group-append').find('button');
            const originalHtml = $submitBtn.html();
            $submitBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

            // Submit form
            form.submit();
        });

        // Smooth scroll for alerts (legacy support)
        if ($('.alert:visible').length > 0) {
            $('html, body').animate({
                scrollTop: $('.alert:visible').offset().top - 150
            }, 500);
        }
    });
</script>
@endpush
@endsection