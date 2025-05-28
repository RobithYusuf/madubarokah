@extends('layouts.frontend')
@section('title', 'Checkout')
@section('content')
<div class="cart-container">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent px-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}" class="text-gelap">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.cart.index') }}" class="text-gelap">Keranjang</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Checkout</li>
                    </ol>
                </nav>
            </div>
        </div>
        <form action="{{ route('frontend.checkout.process') }}" method="POST" id="checkoutForm">
            @csrf
            <div class="row">
                <!-- Left Column: Product Details & Shipping Info -->
                <div class="col-md-7">
                    <!-- Product Details -->
                    <div class="card-cart mb-4">
                        <div class="card-header bg-gelap">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-list"></i> Detail Pesanan ({{ $cartItems->sum('quantity') }} item)
                            </h5>
                        </div>

                        @if (session('success'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: '{{ session('
                                    success ') }}',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            });
                        </script>
                        @endif
                        @if (session('error'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: '{{ session('
                                    error ') }}',
                                    showConfirmButton: true
                                });
                            });
                        </script>
                        @endif
                        <div class="card-body">
                            @if($provinces->count() == 0)
                            <div class="alert alert-danger mb-4">
                                <h6><i class="fas fa-exclamation-triangle"></i> Data Wilayah Belum Tersedia</h6>
                                <p class="mb-2">Sistem belum memiliki data provinsi dan kota untuk pengiriman.</p>
                                <p class="mb-0"><strong>Hubungi administrator</strong> untuk melakukan sinkronisasi data wilayah.</p>
                            </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($cartItems as $item)
                                        <tr>
                                            <td style="width: 80px;">
                                                @if($item->produk->gambar)
                                                <img src="{{ asset('storage/' . $item->produk->gambar) }}"
                                                    alt="{{ $item->produk->nama_produk }}"
                                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                                    class="shadow-sm">
                                                @endif
                                            </td>
                                            <td>
                                                <h6 class="mb-1 text-gelap">{{ $item->produk->nama_produk }}</h6>
                                                @if($item->produk->kategori)
                                                <span class="badge text-white mb-1"
                                                    style="background-color: {{ $item->produk->kategori->warna ?? '#6C757D' }}; font-size: 0.7em;">
                                                    {{ $item->produk->kategori->nama_kategori }}
                                                </span>
                                                @endif
                                                <div class="text-muted small">
                                                    {{ $item->quantity }} x Rp {{ number_format($item->produk->harga, 0, ',', '.') }}
                                                    <br><span class="badge badge-secondary">{{ $item->produk->berat ?? 500 }}g x {{ $item->quantity }} = {{ ($item->produk->berat ?? 500) * $item->quantity }}g</span>
                                                </div>
                                            </td>
                                            <td class="text-right align-middle">
                                                <span class="font-weight-bold text-gelap">
                                                    Rp {{ number_format($item->quantity * $item->produk->harga, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- Subtotal -->
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between">
                                    <strong>Subtotal:</strong>
                                    <strong class="text-gelap">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Shipping Information -->
                    <div class="card-cart">
                        <div class="card-header bg-gelap">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-shipping-fast"></i> Informasi Pengiriman
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Nama Penerima <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_penerima" value="{{ Auth::user()->nama ?? '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Telepon Penerima <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="telepon_penerima" value="{{ Auth::user()->nohp ?? '' }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Provinsi Tujuan <span class="text-danger">*</span></label>
                                    <select class="form-control" name="destination_province" id="destinationProvince" required>
                                        <option value="">Pilih Provinsi</option>
                                        @foreach($provinces as $province)
                                        <option value="{{ $province->province_id }}">{{ $province->province_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Kota/Kabupaten <span class="text-danger">*</span></label>
                                    <select class="form-control" name="destination_city" id="destinationCity" required>
                                        <option value="">Pilih Kota/Kabupaten</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="alamat_lengkap" rows="3"
                                    placeholder="Masukkan alamat lengkap (nama jalan, nomor rumah, RT/RW, kode pos, dll.)" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Pilih Kurir <span class="text-danger">*</span></label>
                                    <select class="form-control" name="courier" id="courierSelect" required>
                                        <option value="">Pilih Kurir</option>
                                        <option value="jne">JNE</option>
                                        <option value="pos">POS Indonesia</option>
                                        <option value="tiki">TIKI</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0">
                                        <small><strong><i class="fas fa-info-circle"></i> Info Paket:</strong><br>
                                            Berat total: <strong id="totalWeightDisplay">{{ $totalWeight ?? 1000 }}g</strong><br>
                                            Asal: <strong id="originCityDisplay">{{ config('shop.address') }}</strong><br>
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Berat dihitung dari produk aktual</span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3" id="shippingServiceSection" style="display: none;">
                                <label class="form-label font-weight-bold">Pilih Layanan Pengiriman <span class="text-danger">*</span></label>
                                <small class="text-muted d-block mb-2">Klik pada kartu untuk memilih layanan pengiriman</small>
                                <div id="shippingServices"></div>
                                <input type="hidden" name="service" id="selectedService">
                                <input type="hidden" name="shipping_cost" id="shippingCost">
                                <input type="hidden" name="selected_shipping_data" id="selectedShippingData">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Right Column: Payment & Summary -->
                <div class="col-md-5">
                    <!-- Payment Methods -->
                    <div class="card-cart mb-4">
                        <div class="card-header bg-gelap">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-credit-card"></i> Metode Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($paymentChannels->count() > 0)
                            @foreach($paymentChannels as $group => $channels)
                            <div class="payment-group mb-4">
                                <h6 class="payment-group-title">
                                    @if($group == 'Virtual Account')
                                    <i class="fas fa-university"></i>
                                    @elseif($group == 'E-Wallet')
                                    <i class="fas fa-mobile-alt"></i>
                                    @elseif($group == 'Retail')
                                    <i class="fas fa-store"></i>
                                    @elseif($group == 'QRIS')
                                    <i class="fas fa-qrcode"></i>
                                    @else
                                    <i class="fas fa-credit-card"></i>
                                    @endif
                                    {{ $group }}
                                </h6>
                                <div class="payment-channels-grid">
                                    @foreach($channels as $channel)
                                    <div class="payment-channel-card" data-channel="{{ $channel->code }}" data-fee-flat="{{ $channel->fee_flat }}" data-fee-percent="{{ $channel->fee_percent }}">
                                        <input type="radio" name="metode_pembayaran" value="{{ $channel->code }}" id="payment_{{ $channel->code }}" class="payment-channel-input" {{ $loop->parent->first && $loop->first ? ' checked' : '' }}>
                                        <label for="payment_{{ $channel->code }}" class="payment-channel-label">
                                            <div class="payment-channel-content">
                                                <div class="payment-channel-icon">
                                                    @if($channel->icon_url)
                                                    <img src="{{ $channel->icon_url }}" alt="{{ $channel->name }}" class="channel-icon">
                                                    @else
                                                    @if(str_contains(strtolower($channel->name), 'bri'))
                                                    <div class="fallback-icon bri">BRI</div>
                                                    @elseif(str_contains(strtolower($channel->name), 'bca'))
                                                    <div class="fallback-icon bca">BCA</div>
                                                    @elseif(str_contains(strtolower($channel->name), 'mandiri'))
                                                    <div class="fallback-icon mandiri">MDR</div>
                                                    @elseif(str_contains(strtolower($channel->name), 'bni'))
                                                    <div class="fallback-icon bni">BNI</div>
                                                    @elseif(str_contains(strtolower($channel->name), 'gopay'))
                                                    <div class="fallback-icon gopay">GP</div>
                                                    @elseif(str_contains(strtolower($channel->name), 'ovo'))
                                                    <div class="fallback-icon ovo">OVO</div>
                                                    @elseif(str_contains(strtolower($channel->name), 'dana'))
                                                    <div class="fallback-icon dana">DN</div>
                                                    @elseif(str_contains(strtolower($channel->name), 'qris'))
                                                    <div class="fallback-icon qris">QR</div>
                                                    @else
                                                    <div class="fallback-icon default"><i class="fas fa-credit-card"></i></div>
                                                    @endif
                                                    @endif
                                                </div>
                                                <div class="payment-channel-info">
                                                    <div class="payment-channel-name">{{ $channel->name }}</div>
                                                    @if($channel->fee_flat > 0 || $channel->fee_percent > 0)
                                                    <div class="payment-channel-fee">
                                                        @if($channel->fee_flat > 0)
                                                        +Rp {{ number_format($channel->fee_flat, 0, ',', '.') }}
                                                        @endif
                                                        @if($channel->fee_percent > 0)
                                                        {{ $channel->fee_flat > 0 ? ' + ' : '+' }}{{ $channel->fee_percent }}%
                                                        @endif
                                                    </div>
                                                    @else
                                                    <div class="payment-channel-fee free">Gratis</div>
                                                    @endif
                                                </div>
                                                <div class="payment-channel-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="payment-group mb-4">
                                <h6 class="payment-group-title">
                                    <i class="fas fa-university"></i> Transfer Bank
                                </h6>
                                <div class="payment-channels-grid">
                                    <div class="payment-channel-card">
                                        <input type="radio" name="metode_pembayaran" value="manual_transfer" id="payment_manual" class="payment-channel-input" checked>
                                        <label for="payment_manual" class="payment-channel-label">
                                            <div class="payment-channel-content">
                                                <div class="payment-channel-icon">
                                                    <div class="fallback-icon default"><i class="fas fa-university"></i></div>
                                                </div>
                                                <div class="payment-channel-info">
                                                    <div class="payment-channel-name">Transfer Manual</div>
                                                    <div class="payment-channel-fee free">Gratis</div>
                                                </div>
                                                <div class="payment-channel-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- Order Summary -->
                    <div class="card-cart">
                        <div class="card-header bg-gelap">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-calculator"></i> Ringkasan Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotalDisplay">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="shippingCostRow" style="display: none;">
                                <span>Ongkos Kirim:</span>
                                <span id="shippingCostDisplay">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="paymentFeeRow" style="display: none;">
                                <span>Biaya Admin:</span>
                                <span id="paymentFeeDisplay">Rp 0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="h5 text-gelap">Total Pembayaran:</strong>
                                <strong class="h5 text-gelap" id="totalAmount">Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>
                            <button type="submit" class="btn btn-secondary w-100 btn-lg mb-2" id="checkoutBtn" disabled>
                                <i class="fas fa-credit-card"></i> Buat Pesanan
                            </button>
                            <a href="{{ route('frontend.cart.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Meta tags untuk menyimpan data -->
<meta name="checkout-subtotal" content="{{ $subtotal }}">
<meta name="checkout-total-weight" content="{{ $totalWeight ?? 1000 }}">
<meta name="checkout-origin-city" content="{{ config('shop.warehouse_city_id', 209) }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@push('styles')
<style>
    .form-check {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        margin-bottom: 0.25rem;
    }

    .form-check:hover {
        background-color: rgba(204, 132, 0, 0.05);
        border-color: rgba(204, 132, 0, 0.2);
    }

    .form-check-input:checked {
        background-color: #cc8400;
        border-color: #cc8400;
    }

    .form-check-input:checked+.form-check-label {
        color: #cc8400;
        font-weight: 600;
    }

    .table-sm td {
        padding: 0.75rem 0.5rem;
        vertical-align: middle;
        border-top: 1px solid #f1f1f1;
    }

    .table-sm tr:first-child td {
        border-top: none;
    }

    .shipping-services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .shipping-service-card {
        padding: 1rem;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        background: #fff;
        position: relative;
        overflow: hidden;
    }

    .shipping-service-card:hover {
        border-color: #cc8400;
        background-color: rgba(204, 132, 0, 0.05);
        box-shadow: 0 4px 12px rgba(204, 132, 0, 0.15);
        transform: translateY(-2px);
    }

    .shipping-service-card.selected {
        border-color: #cc8400;
        background-color: rgba(204, 132, 0, 0.08);
        box-shadow: 0 6px 20px rgba(204, 132, 0, 0.2);
        transform: translateY(-1px);
    }

    .shipping-service-card.selected::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #FFA500, #cc8400);
        border-radius: 12px 12px 0 0;
    }

    .shipping-service-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    .shipping-service-info {
        flex: 1;
        min-width: 0;
    }

    .shipping-service-name {
        font-weight: 600;
        font-size: 1rem;
        color: #333;
        margin-bottom: 0.5rem;
        transition: color 0.3s ease;
    }

    .shipping-service-card.selected .shipping-service-name {
        color: #cc8400;
    }

    .shipping-service-desc {
        font-size: 0.8rem;
        color: #666;
        line-height: 1.3;
    }

    .shipping-service-price {
        text-align: right;
        flex-shrink: 0;
    }

    .shipping-price-value {
        font-weight: 700;
        font-size: 1.1rem;
        color: #cc8400;
        margin-bottom: 0.25rem;
    }

    .shipping-etd {
        font-size: 0.75rem;
        color: #888;
        font-weight: 500;
    }

    .selected-indicator {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 24px;
        height: 24px;
        background: #cc8400;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .shipping-service-card.selected .selected-indicator {
        opacity: 1;
        transform: scale(1);
    }

    .selected-indicator i {
        color: white;
        font-size: 12px;
    }

    #checkoutBtn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    #checkoutBtn:not(:disabled) {
        background-color: #cc8400;
        border-color: #cc8400;
        color: white;
    }

    #checkoutBtn:not(:disabled):hover {
        background-color: #b8790a;
        border-color: #b8790a;
    }

    /* Minimalist alert styles */
    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }

    .alert-sm i {
        font-size: 0.8rem;
    }

    /* Payment Channel Styles */
    .payment-group {
        margin-bottom: 1.25rem;
    }

    .payment-group-title {
        color: #333;
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.4rem;
        border-bottom: 2px solid #f8f9fa;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .payment-group-title i {
        color: #cc8400;
        font-size: 1rem;
    }

    .payment-channels-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .payment-channel-card {
        position: relative;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fff;
        overflow: hidden;
        cursor: pointer;
    }

    .payment-channel-card:hover {
        border-color: #cc8400;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(204, 132, 0, 0.15);
    }

    .payment-channel-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .payment-channel-label {
        display: block;
        padding: 0.75rem;
        cursor: pointer;
        margin: 0;
        width: 100%;
        height: 100%;
    }

    .payment-channel-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
    }

    .payment-channel-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #f8f9fa;
    }

    .channel-icon {
        width: 32px;
        height: 32px;
        object-fit: contain;
        border-radius: 4px;
    }

    .fallback-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-weight: 700;
        font-size: 0.7rem;
        color: white;
        text-align: center;
    }

    .fallback-icon.default {
        background: linear-gradient(135deg, #cc8400, #ffaa00);
    }

    .payment-channel-info {
        flex: 1;
        min-width: 0;
    }

    .payment-channel-name {
        font-weight: 600;
        font-size: 0.85rem;
        color: #333;
        margin-bottom: 0.15rem;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .payment-channel-fee {
        font-size: 0.75rem;
        color: #666;
        font-weight: 500;
    }

    .payment-channel-fee.free {
        color: #28a745;
        font-weight: 600;
    }

    .payment-channel-check {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        background: #fff;
    }

    .payment-channel-check i {
        font-size: 12px;
        color: white;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    /* Selected State */
    .payment-channel-input:checked+.payment-channel-label {
        background: rgba(204, 132, 0, 0.05);
    }

    .payment-channel-input:checked+.payment-channel-label .payment-channel-card,
    .payment-channel-card:has(.payment-channel-input:checked) {
        border-color: #cc8400;
        background: rgba(204, 132, 0, 0.02);
        box-shadow: 0 4px 12px rgba(204, 132, 0, 0.15);
    }

    .payment-channel-input:checked+.payment-channel-label .payment-channel-name {
        color: #cc8400;
        font-weight: 700;
    }

    .payment-channel-input:checked+.payment-channel-label .payment-channel-check {
        background: #cc8400;
        border-color: #cc8400;
        transform: scale(1.1);
    }

    .payment-channel-input:checked+.payment-channel-label .payment-channel-check i {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .payment-channels-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .shipping-services-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .shipping-service-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .shipping-service-price {
            text-align: left;
            width: 100%;
        }
    }
</style>
@endpush
@push('scripts')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Ambil data dari meta tags dan shop settings
        const checkoutConfig = {
            subtotal: parseFloat($('meta[name="checkout-subtotal"]').attr('content')),
            totalWeight: parseInt($('meta[name="checkout-total-weight"]').attr('content')),
            originCity: parseInt($('meta[name="checkout-origin-city"]').attr('content')),
            csrf: $('meta[name="csrf-token"]').attr('content')
        };
        let shippingCost = 0;
        let paymentFee = 0;

        // Cache DOM elements
        const elements = {
            provinceSelect: $('#destinationProvince'),
            citySelect: $('#destinationCity'),
            courierSelect: $('#courierSelect'),
            addressInput: $('textarea[name="alamat_lengkap"]'),
            paymentInputs: $('input[name="metode_pembayaran"]'),
            shippingSection: $('#shippingServiceSection'),
            shippingServices: $('#shippingServices'),
            selectedService: $('#selectedService'),
            shippingCostInput: $('#shippingCost'),
            selectedShippingData: $('#selectedShippingData'),
            shippingCostRow: $('#shippingCostRow'),
            shippingCostDisplay: $('#shippingCostDisplay'),
            paymentFeeRow: $('#paymentFeeRow'),
            paymentFeeDisplay: $('#paymentFeeDisplay'),
            totalAmount: $('#totalAmount'),
            checkoutBtn: $('#checkoutBtn'),
            checkoutForm: $('#checkoutForm')
        };

        // Initialize
        initializeCheckout();

        function initializeCheckout() {
            console.log('=== CHECKOUT INITIALIZED ===');
            console.log('Config:', checkoutConfig);

            // Setup event handlers
            setupEventHandlers();

            // Initial validation
            validateForm();
        }

        function setupEventHandlers() {
            elements.provinceSelect.on('change', handleProvinceChange);
            elements.citySelect.on('change', handleShippingCalculation);
            elements.courierSelect.on('change', handleShippingCalculation);
            elements.paymentInputs.on('change', handlePaymentChange);
            elements.addressInput.on('input', validateForm);
            elements.checkoutForm.on('submit', handleFormSubmit);

            // Handle dynamic service selection
            $(document).on('click', '.shipping-service-card', handleServiceSelection);
        }

        function handleProvinceChange() {
            const provinceId = $(this).val();
            console.log('Province changed:', provinceId);
            if (provinceId) {
                loadCities(provinceId);
            } else {
                elements.citySelect.html('<option value="">Pilih Kota/Kabupaten</option>');
            }
            resetShipping();
        }

        function handleShippingCalculation() {
            const cityId = elements.citySelect.val();
            const courier = elements.courierSelect.val();
            console.log('Calculating shipping:', {
                cityId,
                courier
            });
            if (cityId && courier) {
                calculateShipping(cityId, courier);
            } else {
                resetShipping();
            }
        }

        function handleServiceSelection() {
            const $card = $(this);
            // Remove selected class from all cards
            $('.shipping-service-card').removeClass('selected');
            // Add selected class to clicked card
            $card.addClass('selected');

            try {
                let serviceData = $card.data('service');
                if (typeof serviceData === 'string') {
                    serviceData = JSON.parse(serviceData);
                }

                if (!serviceData || !serviceData.service || !serviceData.cost) {
                    throw new Error('Invalid service data structure');
                }

                elements.selectedService.val(serviceData.service);
                elements.shippingCostInput.val(serviceData.cost);
                elements.selectedShippingData.val(JSON.stringify(serviceData));

                shippingCost = parseFloat(serviceData.cost) || 0;
                updateTotalDisplay();
                validateForm();

                console.log('✅ Service selected:', serviceData);
                showServiceSelectedFeedback($card, serviceData);
            } catch (error) {
                console.error('Error handling service selection:', error);
                showNotification('error', 'Data layanan pengiriman tidak valid');
                $card.removeClass('selected');
            }
        }

        function handlePaymentChange() {
            updateTotalWithPaymentFee();
            validateForm();
            const selectedChannel = $('input[name="metode_pembayaran"]:checked');
            if (selectedChannel.length > 0) {
                console.log('Payment method selected:', selectedChannel.val());
            }
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            console.log('Form submitting...');
            
            // Collect form data for confirmation
            const formData = {
                namaPenerima: $('input[name="nama_penerima"]').val(),
                teleponPenerima: $('input[name="telepon_penerima"]').val(),
                alamat: $('textarea[name="alamat_lengkap"]').val(),
                provinsi: $('#destinationProvince option:selected').text(),
                kota: $('#destinationCity option:selected').text(),
                kurir: $('#courierSelect option:selected').text() + ' - ' + $('#selectedService').val(),
                metodePembayaran: $('input[name="metode_pembayaran"]:checked').closest('.payment-channel-card').find('.payment-channel-name').text(),
                totalBayar: $('#totalAmount').text()
            };
            
            // Show confirmation dialog
            Swal.fire({
                title: 'Konfirmasi Pesanan',
                html: `
                    <div class="text-left">
                        <h6 class="mb-3">Detail Pengiriman:</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Nama Penerima</strong></td>
                                <td>: ${formData.namaPenerima}</td>
                            </tr>
                            <tr>
                                <td><strong>Telepon</strong></td>
                                <td>: ${formData.teleponPenerima}</td>
                            </tr>
                            <tr>
                                <td><strong>Alamat</strong></td>
                                <td>: ${formData.alamat}</td>
                            </tr>
                            <tr>
                                <td><strong>Kota/Provinsi</strong></td>
                                <td>: ${formData.kota}, ${formData.provinsi}</td>
                            </tr>
                            <tr>
                                <td><strong>Kurir</strong></td>
                                <td>: ${formData.kurir}</td>
                            </tr>
                            <tr>
                                <td><strong>Metode Pembayaran</strong></td>
                                <td>: ${formData.metodePembayaran}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Bayar</strong></td>
                                <td>: ${formData.totalBayar}</td>
                            </tr>
                        </table>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#cc8400',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Buat Pesanan',
                cancelButtonText: 'Periksa Kembali',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    elements.checkoutBtn.html('<span class="spinner-border spinner-border-sm mr-2" role="status" style="width: 1rem; height: 1rem;"></span>Memproses...').prop('disabled', true);
                    $('#checkoutForm').unbind('submit').submit();
                }
            });
        }

        function updateTotalWithPaymentFee() {
            const selectedPayment = $('input[name="metode_pembayaran"]:checked');
            paymentFee = 0; // Manual transfer = gratis
            updateTotalDisplay();
        }

        function updateTotalDisplay() {
            // Show/hide shipping cost
            if (shippingCost > 0) {
                elements.shippingCostDisplay.text('Rp ' + shippingCost.toLocaleString('id-ID'));
                elements.shippingCostRow.show();
            } else {
                elements.shippingCostRow.hide();
            }

            // Hide payment fee for manual transfer
            elements.paymentFeeRow.hide();

            // Update total
            const total = checkoutConfig.subtotal + shippingCost + paymentFee;
            elements.totalAmount.text('Rp ' + total.toLocaleString('id-ID'));
        }

        function loadCities(provinceId) {
            elements.citySelect.html('<option value="">Memuat...</option>').prop('disabled', true);

            $.ajax({
                url: `/api/checkout/cities/${provinceId}`,
                method: 'GET',
                timeout: 10000,
                success: function(response) {
                    if (response.success && response.data) {
                        let options = '<option value="">Pilih Kota/Kabupaten</option>';
                        response.data.forEach(function(city) {
                            options += `<option value="${city.rajaongkir_id}">${city.city_name}</option>`;
                        });
                        elements.citySelect.html(options).prop('disabled', false);
                    } else {
                        throw new Error('Invalid response format');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading cities:', error);
                    showNotification('error', 'Gagal memuat data kota. Silakan refresh halaman.');
                    elements.citySelect.html('<option value="">Pilih Kota/Kabupaten</option>').prop('disabled', false);
                }
            });
        }

        function calculateShipping(destinationId, courier) {
            const requestData = {
                origin: checkoutConfig.originCity,
                destination: destinationId,
                weight: checkoutConfig.totalWeight,
                courier: courier,
                _token: checkoutConfig.csrf
            };

            console.log('Calculating shipping:', requestData);
            elements.shippingServices.html(createLoadingHTML());
            elements.shippingSection.show();

            $.ajax({
                url: '/shipping/calculate',
                method: 'POST',
                data: requestData,
                timeout: 15000,
                cache: false,
                success: function(response) {
                    console.log('Shipping calculation response:', response);
                    if (response.success && response.data && response.data.length > 0) {
                        renderShippingServices(response.data, response.debug);
                    } else {
                        elements.shippingServices.html(createErrorHTML('Tidak ada layanan tersedia untuk kurir dan rute ini'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error calculating shipping:', {
                        xhr,
                        status,
                        error
                    });
                    let errorMessage = 'Gagal menghitung ongkos kirim';
                    if (xhr.status === 422) {
                        errorMessage = 'Data tidak valid. Periksa kembali pilihan Anda.';
                    } else if (status === 'timeout') {
                        errorMessage = 'Koneksi timeout. Silakan coba lagi.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Silakan coba beberapa saat lagi.';
                    }
                    elements.shippingServices.html(createErrorHTML(errorMessage));
                }
            });
        }

        function renderShippingServices(couriers, debugInfo) {
            let html = '';

            // Add debug info if exists
            if (debugInfo && debugInfo.source === 'fallback_dummy_data') {
                html += `
                <div class="alert alert-info alert-sm py-2 px-3 mb-2">
                    <i class="fas fa-info-circle mr-2"></i> 
                    <small><strong>Info:</strong> ${debugInfo.message}</small>
                    ${debugInfo.note ? `<br><small class="text-muted">${debugInfo.note}</small>` : ''}
                </div>
            `;
            }

            let servicesHtml = '';
            let serviceCount = 0;

            if (!Array.isArray(couriers)) {
                console.error('Invalid couriers data:', couriers);
                elements.shippingServices.html(createErrorHTML('Format data pengiriman tidak valid'));
                return;
            }

            couriers.forEach(function(courier) {
                if (!courier || !courier.costs || !Array.isArray(courier.costs)) {
                    return;
                }

                courier.costs.forEach(function(cost) {
                    if (!cost.cost || !Array.isArray(cost.cost)) {
                        return;
                    }

                    cost.cost.forEach(function(detail) {
                        if (!detail || typeof detail.value !== 'number' || detail.value <= 0) {
                            return;
                        }

                        const serviceData = {
                            service: cost.service || 'REG',
                            cost: detail.value,
                            etd: detail.etd || '1-2',
                            description: cost.description || (cost.service + ' Service')
                        };

                        servicesHtml += createServiceCardHTML(cost, detail, serviceData);
                        serviceCount++;
                    });
                });
            });

            if (servicesHtml && serviceCount > 0) {
                html += `<div class="shipping-services-grid">${servicesHtml}</div>`;
                elements.shippingServices.html(html);
                console.log(`✅ Rendered ${serviceCount} shipping services`);
            } else {
                elements.shippingServices.html(createErrorHTML('Tidak ada layanan pengiriman yang tersedia'));
            }
        }

        function createServiceCardHTML(cost, detail, serviceData) {
            const serviceId = `service_${cost.service}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            const price = parseInt(detail.value) || 0;
            const etd = detail.etd || 'N/A';
            return `
            <div class="shipping-service-card" 
                 data-service='${JSON.stringify(serviceData)}' 
                 data-service-id="${serviceId}">
                <div class="selected-indicator">
                    <i class="fas fa-check"></i>
                </div>
                <div class="shipping-service-content">
                    <div class="shipping-service-info">
                        <div class="shipping-service-name">${cost.service}</div>
                        <div class="shipping-service-desc">${cost.description || 'Layanan pengiriman'}</div>
                    </div>
                    <div class="shipping-service-price">
                        <div class="shipping-price-value">Rp ${price.toLocaleString('id-ID')}</div>
                        <div class="shipping-etd">${etd} hari</div>
                    </div>
                </div>
            </div>
        `;
        }

        function createLoadingHTML() {
            return `
            <div class="text-center py-3">
                <div class="spinner-border text-warning mb-2" role="status" style="width: 1.5rem; height: 1.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0 text-muted small" style="margin-top: 8px;">Menghitung ongkos kirim...</p>
            </div>
        `;
        }

        function createErrorHTML(message) {
            return `
            <div class="alert alert-warning alert-sm py-2 px-3 mb-0">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <small>${message}</small>
            </div>
        `;
        }

        function resetShipping() {
            elements.shippingSection.hide();
            elements.shippingServices.html('');
            elements.selectedService.val('');
            elements.shippingCostInput.val('');
            elements.selectedShippingData.val('');
            shippingCost = 0;
            updateTotalDisplay();
            validateForm();
        }

        function showServiceSelectedFeedback($card, serviceData) {
            const $indicator = $('<div class="service-selected-feedback">' +
                '<i class="fas fa-check-circle"></i> Dipilih' +
                '</div>');
            $indicator.css({
                position: 'absolute',
                bottom: '10px',
                left: '50%',
                transform: 'translateX(-50%)',
                background: '#28a745',
                color: 'white',
                padding: '4px 8px',
                borderRadius: '12px',
                fontSize: '0.7rem',
                fontWeight: '500',
                zIndex: '10'
            });
            $card.css('position', 'relative').append($indicator);
            setTimeout(() => {
                $indicator.fadeOut(200, function() {
                    $(this).remove();
                });
            }, 1500);
        }

        function validateForm() {
            const isValid = elements.provinceSelect.val() &&
                elements.citySelect.val() &&
                elements.courierSelect.val() &&
                elements.selectedService.val() &&
                elements.paymentInputs.filter(':checked').length > 0 &&
                elements.addressInput.val().trim() &&
                $('input[name="nama_penerima"]').val().trim() &&
                $('input[name="telepon_penerima"]').val().trim();

            elements.checkoutBtn.prop('disabled', !isValid);

            if (isValid) {
                elements.checkoutBtn.removeClass('btn-secondary').addClass('btn-primary');
                elements.checkoutBtn.css({
                    'background-color': '#cc8400',
                    'border-color': '#cc8400'
                });
            } else {
                elements.checkoutBtn.removeClass('btn-primary').addClass('btn-secondary');
                elements.checkoutBtn.css({
                    'background-color': '',
                    'border-color': ''
                });
            }
        }

        function showNotification(type, message) {
            const icon = type === 'error' ? 'error' : 'info';
            const title = type === 'error' ? 'Terjadi Kesalahan!' : 'Informasi';

            Swal.fire({
                icon: icon,
                title: title,
                text: message,
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
        }
    });
</script>
@endpush
@endsection