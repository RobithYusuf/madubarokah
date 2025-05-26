@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Detail Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
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
                                                 style="width: 50px; height: 50px; object-fit: cover;" 
                                                 class="me-3">
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $item->produk->nama_produk }}</h6>
                                                <small class="text-muted">{{ $item->produk->kategori->nama_kategori ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($item->produk->harga, 0, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($item->quantity * $item->produk->harga, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Informasi Checkout</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontend.checkout.process') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" value="COD" id="cod" checked>
                                <label class="form-check-label" for="cod">
                                    COD (Cash on Delivery)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" value="Transfer Bank" id="transfer">
                                <label class="form-check-label" for="transfer">
                                    Transfer Bank
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" value="E-Wallet" id="ewallet">
                                <label class="form-check-label" for="ewallet">
                                    E-Wallet
                                </label>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            Buat Pesanan
                        </button>
                        
                        <a href="{{ route('frontend.cart.index') }}" class="btn btn-secondary w-100 mt-2">
                            Kembali ke Keranjang
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
