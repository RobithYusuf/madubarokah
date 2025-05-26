@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Keranjang Belanja</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($cartItems->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cartItems as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->produk->gambar)
                                            <img src="{{ asset('storage/' . $item->produk->gambar) }}" 
                                                 alt="{{ $item->produk->nama_produk }}" 
                                                 style="width: 60px; height: 60px; object-fit: cover;" 
                                                 class="me-3">
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $item->produk->nama_produk }}</h6>
                                                @if($item->produk->kategori)
                                                <span class="badge text-white" 
                                                      style="background-color: {{ $item->produk->kategori->warna ?? '#6C757D' }}; font-size: 0.7em;">
                                                    {{ $item->produk->kategori->nama_kategori }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($item->produk->harga, 0, ',', '.') }}</td>
                                    <td>
                                        <form action="{{ route('frontend.cart.update', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group" style="width: 120px;">
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" 
                                                       min="1" max="{{ $item->produk->stok }}" 
                                                       class="form-control form-control-sm">
                                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                                    Update
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>Rp {{ number_format($item->quantity * $item->produk->harga, 0, ',', '.') }}</td>
                                    <td>
                                        <form action="{{ route('frontend.cart.remove', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <form action="{{ route('frontend.cart.clear') }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Yakin ingin mengosongkan keranjang?')">
                                    <i class="fa fa-trash me-1"></i> Kosongkan Keranjang
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('frontend.checkout') }}" class="btn btn-primary">
                                <i class="fa fa-shopping-cart me-1"></i> Checkout
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fa fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Keranjang Kosong</h5>
                        <p class="text-muted">Belum ada produk dalam keranjang belanja Anda</p>
                        <a href="{{ route('Landingpage.index') }}" class="btn btn-primary">
                            <i class="fa fa-arrow-left me-1"></i> Mulai Belanja
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
