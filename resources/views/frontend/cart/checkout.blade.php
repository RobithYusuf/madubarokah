@extends('layouts.frontend')

@section('title', 'Checkout')

@section('content')
<div class="cart-container">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent px-0">
                        <li class="breadcrumb-item"><a href="{{ route('Landingpage.index') }}" class="text-gelap">Beranda</a></li>
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
                        <div class="card-body">
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
                                        @foreach($couriers as $courier)
                                        <option value="{{ $courier->code }}">{{ $courier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0">
                                        <small><strong><i class="fas fa-info-circle"></i> Info Paket:</strong><br>
                                            Berat total: <strong id="totalWeightDisplay">{{ $totalWeight }}g</strong><br>
                                            Asal: <strong>Jakarta</strong></small>
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
                            <!-- Tripay Payment Channels -->
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
                                        <input type="radio" name="metode_pembayaran" value="{{ $channel->code }}" id="payment_{{ $channel->code }}" class="payment-channel-input">
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
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="h5 text-gelap">Total Pembayaran:</strong>
                                <strong class="h5 text-gelap" id="totalAmount">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                            </div>

                            <button type="submit" class="btn btn-orange w-100 btn-lg mb-2" id="checkoutBtn" disabled>
                                <i class="fas fa-credit-card"></i> Buat Pesanan
                            </button>

                            <a href="{{ route('frontend.cart.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                            </a>

                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt"></i>
                                    Transaksi Anda aman dan terjamin
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Meta tags untuk menyimpan data (aman dari parsing error) -->
<meta name="checkout-subtotal" content="{{ $subtotal }}">
<meta name="checkout-total-weight" content="{{ $totalWeight }}">
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

    .form-check-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
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
        grid-template-columns: repeat(2, 1fr);
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

    .shipping-service-card.selected .shipping-service-name {
        color: #cc8400;
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

    .service-selected-feedback {
        animation: slideUpFade 0.3s ease-out;
    }

    @keyframes slideUpFade {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    .shipping-service-card {
        user-select: none;
    }

    .shipping-service-card:active {
        transform: translateY(0) scale(0.98);
    }

    @media (max-width: 768px) {
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
        
        .shipping-service-card {
            padding: 0.875rem;
        }
    }

    #checkoutBtn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .alert-info {
        background-color: rgba(255, 165, 0, 0.1);
        border-color: rgba(255, 165, 0, 0.3);
        color: #cc8400;
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #cc8400;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Payment Channel Styles */
    .payment-group {
        margin-bottom: 2rem;
    }

    .payment-group-title {
        color: #333;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
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
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .payment-channel-card {
        position: relative;
        border: 2px solid #e9ecef;
        border-radius: 12px;
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
        padding: 1.25rem;
        cursor: pointer;
        margin: 0;
        width: 100%;
        height: 100%;
    }

    .payment-channel-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
    }

    .payment-channel-icon {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #f8f9fa;
    }

    .channel-icon {
        width: 40px;
        height: 40px;
        object-fit: contain;
        border-radius: 6px;
    }

    .fallback-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.8rem;
        color: white;
        text-align: center;
    }

    .fallback-icon.bri { background: linear-gradient(135deg, #003d7a, #0066cc); }
    .fallback-icon.bca { background: linear-gradient(135deg, #0066cc, #4d94ff); }
    .fallback-icon.mandiri { background: linear-gradient(135deg, #ff8800, #ffaa00); }
    .fallback-icon.bni { background: linear-gradient(135deg, #ff6600, #ff8833); }
    .fallback-icon.gopay { background: linear-gradient(135deg, #00aa5b, #00cc6a); }
    .fallback-icon.ovo { background: linear-gradient(135deg, #4c2882, #663399); }
    .fallback-icon.dana { background: linear-gradient(135deg, #118eea, #2196f3); }
    .fallback-icon.qris { background: linear-gradient(135deg, #ff0000, #ff3333); }
    .fallback-icon.cod { background: linear-gradient(135deg, #28a745, #34ce57); }
    .fallback-icon.transfer { background: linear-gradient(135deg, #6c757d, #868e96); }
    .fallback-icon.default { background: linear-gradient(135deg, #cc8400, #ffaa00); }

    .payment-channel-info {
        flex: 1;
        min-width: 0;
    }

    .payment-channel-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #333;
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }

    .payment-channel-fee {
        font-size: 0.8rem;
        color: #666;
        font-weight: 500;
    }

    .payment-channel-fee.free {
        color: #28a745;
        font-weight: 600;
    }

    .payment-channel-check {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
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
    .payment-channel-input:checked + .payment-channel-label {
        background: rgba(204, 132, 0, 0.05);
    }

    .payment-channel-input:checked + .payment-channel-label .payment-channel-card,
    .payment-channel-card:has(.payment-channel-input:checked) {
        border-color: #cc8400;
        background: rgba(204, 132, 0, 0.02);
        box-shadow: 0 4px 12px rgba(204, 132, 0, 0.15);
    }

    .payment-channel-input:checked + .payment-channel-label .payment-channel-name {
        color: #cc8400;
        font-weight: 700;
    }

    .payment-channel-input:checked + .payment-channel-label .payment-channel-check {
        background: #cc8400;
        border-color: #cc8400;
        transform: scale(1.1);
    }

    .payment-channel-input:checked + .payment-channel-label .payment-channel-check i {
        opacity: 1;
    }

    .payment-channel-card:has(.payment-channel-input:checked) .payment-channel-name {
        color: #cc8400;
        font-weight: 700;
    }

    .payment-channel-card:has(.payment-channel-input:checked) .payment-channel-check {
        background: #cc8400;
        border-color: #cc8400;
        transform: scale(1.1);
    }

    .payment-channel-card:has(.payment-channel-input:checked) .payment-channel-check i {
        opacity: 1;
    }

    /* Manual Payment Specific Styles */
    .payment-channel-card.manual-payment {
        border-style: dashed;
        border-color: #dee2e6;
    }

    .payment-channel-card.manual-payment:hover {
        border-style: solid;
        border-color: #cc8400;
    }

    .payment-channel-card.manual-payment:has(.payment-channel-input:checked) {
        border-style: solid;
        border-color: #cc8400;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .payment-channels-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .payment-channel-label {
            padding: 1rem;
        }
        
        .payment-channel-content {
            gap: 0.75rem;
        }
        
        .payment-channel-icon {
            width: 40px;
            height: 40px;
        }
        
        .fallback-icon {
            width: 32px;
            height: 32px;
            font-size: 0.7rem;
        }
        
        .channel-icon {
            width: 32px;
            height: 32px;
        }
    }

    /* Animation for selection */
    @keyframes paymentSelected {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }

    .payment-channel-card:has(.payment-channel-input:checked) {
        animation: paymentSelected 0.3s ease-out;
    }

    /* Loading state for payment channels */
    .payment-channels-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        color: #666;
    }

    .payment-channels-loading .loading-spinner {
        margin-right: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Ambil data dari meta tags (aman dari parsing error)
        const checkoutConfig = {
            subtotal: parseFloat($('meta[name="checkout-subtotal"]').attr('content')),
            totalWeight: parseInt($('meta[name="checkout-total-weight"]').attr('content')),
            csrf: $('meta[name="csrf-token"]').attr('content'),
            originCity: 155, // Jakarta Selatan
            originName: 'Jakarta',
            routes: {
                cities: '{{ route("api.checkout.cities", ["provinceId" => "PROVINCE_ID"]) }}'.replace('PROVINCE_ID', ''),
                calculateShipping: '{{ route("api.checkout.calculate") }}',
                checkoutData: '{{ route("api.checkout.data") }}'
            }
        };

        let shippingCost = 0;

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
            totalAmount: $('#totalAmount'),
            checkoutBtn: $('#checkoutBtn'),
            checkoutForm: $('#checkoutForm')
        };

        // Initialize
        initializeCheckout();

        function initializeCheckout() {
            // Load initial data dari API untuk memastikan data fresh
            loadCheckoutData();

            // Setup event handlers
            setupEventHandlers();

            // Initial validation
            validateForm();
        }

        function loadCheckoutData() {
            $.ajax({
                url: '{{ route("api.checkout.data") }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Update config dengan data terbaru
                        checkoutConfig.subtotal = response.data.subtotal;
                        checkoutConfig.totalWeight = response.data.totalWeight;

                        // Update display
                        $('#totalWeightDisplay').text(response.data.totalWeight + 'g');
                        $('#subtotalDisplay').text('Rp ' + response.data.subtotal.toLocaleString('id-ID'));
                        updateTotalDisplay();
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('Failed to load fresh checkout data, using cached data');
                }
            });
        }

        function setupEventHandlers() {
            // Simple event handlers like admin - no cache clearing
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
                const serviceData = JSON.parse($card.data('service'));

                elements.selectedService.val(serviceData.service);
                elements.shippingCostInput.val(serviceData.cost);
                elements.selectedShippingData.val(JSON.stringify(serviceData));

                shippingCost = parseFloat(serviceData.cost) || 0;
                updateTotalDisplay();
                validateForm();

                // Show success feedback
                showServiceSelectedFeedback($card, serviceData);
                
            } catch (error) {
                console.error('Error parsing service data:', error);
                showNotification('error', 'Data layanan pengiriman tidak valid');
                $card.removeClass('selected');
            }
        }

        function handlePaymentChange() {
            // Update total dengan payment fee
            updateTotalWithPaymentFee();
            validateForm();
        }

        function handleFormSubmit(e) {
            elements.checkoutBtn.html('<i class="loading-spinner"></i> Memproses...').prop('disabled', true);
            // Form akan submit secara normal
        }

        function updateTotalWithPaymentFee() {
            const selectedPayment = $('input[name="metode_pembayaran"]:checked');
            let paymentFee = 0;
            
            if (selectedPayment.length > 0) {
                const paymentCard = selectedPayment.closest('.payment-channel-card');
                const feeFlat = parseFloat(paymentCard.data('fee-flat')) || 0;
                const feePercent = parseFloat(paymentCard.data('fee-percent')) || 0;
                
                // Calculate fee based on subtotal
                paymentFee = feeFlat + (checkoutConfig.subtotal * feePercent / 100);
            }
            
            // Update total display
            const total = checkoutConfig.subtotal + shippingCost + paymentFee;
            elements.totalAmount.text('Rp ' + total.toLocaleString('id-ID'));
            
            // Show/hide payment fee row
            if (paymentFee > 0) {
                let paymentFeeRow = $('#paymentFeeRow');
                if (paymentFeeRow.length === 0) {
                    const feeRowHtml = `
                        <div class="d-flex justify-content-between mb-2" id="paymentFeeRow">
                            <span>Biaya Admin:</span>
                            <span id="paymentFeeDisplay">Rp ${paymentFee.toLocaleString('id-ID')}</span>
                        </div>
                    `;
                    elements.shippingCostRow.after(feeRowHtml);
                } else {
                    $('#paymentFeeDisplay').text('Rp ' + paymentFee.toLocaleString('id-ID'));
                    paymentFeeRow.show();
                }
            } else {
                $('#paymentFeeRow').hide();
            }
        }

        function loadCities(provinceId) {
            elements.citySelect.html('<option value="">Memuat...</option>').prop('disabled', true);

            // Build URL dengan mengganti placeholder
            const citiesUrl = '{{ route("api.checkout.cities", ["provinceId" => "PROVINCE_ID"]) }}'.replace('PROVINCE_ID', provinceId);

            $.ajax({
                url: citiesUrl,
                method: 'GET',
                timeout: 10000,
                success: function(response) {
                    if (response.success && response.data) {
                        let options = '<option value="">Pilih Kota/Kabupaten</option>';

                        response.data.forEach(function(city) {
                            options += `<option value="${city.rajaongkir_id}">${city.full_name}</option>`;
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

            console.log('Calculating shipping with same logic as admin:', requestData);

            elements.shippingServices.html(createLoadingHTML());
            elements.shippingSection.show();

            $.ajax({
                url: '{{ route("api.checkout.calculate") }}', // Now uses ShippingController same as admin
                method: 'POST',
                data: requestData,
                timeout: 15000, // Same timeout as admin
                cache: false,
                success: function(response) {
                    console.log('Shipping response (same as admin):', response);
                    
                    if (response.success && response.data && response.data.length > 0) {
                        renderShippingServices(response.data, response.debug);
                    } else {
                        elements.shippingServices.html(createErrorHTML('Tidak ada layanan tersedia untuk kurir dan rute ini'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error calculating shipping:', error);
                    console.log('XHR Status:', xhr.status);
                    console.log('Response Text:', xhr.responseText);

                    let errorMessage = 'Gagal menghitung ongkos kirim';
                    if (xhr.status === 422) {
                        errorMessage = 'Data tidak valid. Periksa kembali pilihan Anda.';
                    } else if (status === 'timeout') {
                        errorMessage = 'Koneksi timeout. Silakan coba lagi.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Akses ditolak. Silakan refresh halaman.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Silakan coba beberapa saat lagi.';
                    }

                    elements.shippingServices.html(createErrorHTML(errorMessage));
                }
            });
        }

        function renderShippingServices(couriers, debugInfo) {
            let html = '';
            let servicesHtml = '';

            // Add debug info if using fallback data
            if (debugInfo && debugInfo.source === 'fallback_dummy_data') {
                html += `
                <div class="alert alert-info alert-sm mb-3">
                    <i class="fas fa-info-circle"></i> 
                    <small>${debugInfo.message}</small>
                </div>
            `;
            }

            couriers.forEach(function(courier) {
                if (courier.costs && courier.costs.length > 0) {
                    courier.costs.forEach(function(cost) {
                        if (cost.cost && cost.cost.length > 0) {
                            cost.cost.forEach(function(detail) {
                                const serviceData = {
                                    service: cost.service,
                                    cost: detail.value,
                                    etd: detail.etd
                                };

                                servicesHtml += createServiceOptionHTML(cost, detail, serviceData);
                            });
                        }
                    });
                }
            });

            if (servicesHtml) {
                html += `<div class="shipping-services-grid">${servicesHtml}</div>`;
                elements.shippingServices.html(html);
            } else {
                elements.shippingServices.html(createErrorHTML('Tidak ada layanan pengiriman yang tersedia'));
            }
        }

        function createServiceOptionHTML(cost, detail, serviceData) {
            const serviceId = `service_${cost.service}_${Date.now()}_${Math.floor(Math.random() * 1000)}`;
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
            <div class="text-center py-4">
                <div class="loading-spinner mx-auto mb-3"></div>
                <p class="mb-1 text-muted">Menghitung ongkos kirim...</p>
                <small class="text-muted d-block">Proses ini mungkin memakan waktu beberapa detik</small>
            </div>
        `;
        }

        function createErrorHTML(message) {
            return `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> ${message}
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

        function updateTotalDisplay() {
            // This will be called when shipping changes, so recalculate payment fee too
            updateTotalWithPaymentFee();

            if (shippingCost > 0) {
                elements.shippingCostDisplay.text('Rp ' + shippingCost.toLocaleString('id-ID'));
                elements.shippingCostRow.show();
            } else {
                elements.shippingCostRow.hide();
            }
        }

        function showServiceSelectedFeedback($card, serviceData) {
            // Create a temporary success indicator
            const $indicator = $('<div class="service-selected-feedback">'
                + '<i class="fas fa-check-circle"></i> Dipilih'
                + '</div>');
            
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
                zIndex: '10',
                opacity: '0'
            });
            
            $card.css('position', 'relative').append($indicator);
            
            // Animate in
            $indicator.animate({ opacity: 1 }, 200);
            
            // Remove after delay
            setTimeout(() => {
                $indicator.animate({ opacity: 0 }, 200, function() {
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
                elements.addressInput.val().trim();

            elements.checkoutBtn.prop('disabled', !isValid);

            if (isValid) {
                elements.checkoutBtn.removeClass('btn-secondary').addClass('btn-orange');
            } else {
                elements.checkoutBtn.removeClass('btn-orange').addClass('btn-secondary');
            }
        }

        function showNotification(type, message) {
            // Simple notification - you can enhance this with SweetAlert2 or similar
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
            const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

            $('body').append(alertHtml);

            // Auto remove after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    });
</script>
@endpush
@endsection