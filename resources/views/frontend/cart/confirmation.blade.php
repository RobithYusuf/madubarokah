@extends('layouts.frontend')

@section('title', 'Konfirmasi Pembayaran')

@section('content')
<div class="confirmation-container">
    <div class="container">
        <!-- Loading Overlay for Tripay Processing -->
        <div class="tripay-loading-overlay" id="tripayLoadingOverlay" style="display: none;">
            <div class="loading-content">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="text-primary">Memproses Pembayaran...</h5>
                <p class="text-muted mb-0">Mohon tunggu, sedang menghubungkan ke gateway pembayaran</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Success Alert with SweetAlert -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        @if($isTripayPayment)
                            // Show loading first for Tripay
                            document.getElementById('tripayLoadingOverlay').style.display = 'flex';
                            
                            setTimeout(function() {
                                document.getElementById('tripayLoadingOverlay').style.display = 'none';
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Pesanan Berhasil Dibuat!',
                                    text: 'Pembayaran otomatis telah disiapkan. Ikuti petunjuk pembayaran di bawah.',
                                    showConfirmButton: true,
                                    confirmButtonText: 'Lanjut ke Pembayaran',
                                    confirmButtonColor: '#28a745',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                });
                            }, 1500);
                        @else
                            Swal.fire({
                                icon: 'success',
                                title: 'Pesanan Berhasil Dibuat!',
                                text: 'Silakan lakukan pembayaran manual sesuai instruksi di bawah.',
                                showConfirmButton: true,
                                confirmButtonText: 'Lihat Instruksi',
                                confirmButtonColor: '#cc8400',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            });
                        @endif
                    });
                </script>

                <!-- Main Content Grid -->
                <div class="row g-4">
                    <!-- Left Column: Order Summary -->
                    <div class="col-lg-7">
                        <!-- Order Header -->
                        <div class="confirmation-card order-header-card">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <div class="order-icon">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Pesanan #{{ $transaksi->merchant_ref }}</h5>
                                        <p class="text-muted mb-0">{{ $transaksi->tanggal_transaksi->format('d F Y, H:i') }} WIB</p>
                                    </div>
                                </div>
                                <div class="order-status">
                                    @php
                                        $paymentStatus = $transaksi->pembayaran ? $transaksi->pembayaran->status : 'pending';
                                    @endphp
                                    <span class="status-badge status-{{ $paymentStatus }}">
                                        {{ ucfirst($paymentStatus) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="confirmation-card order-items-card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Item Pesanan ({{ $transaksi->detailTransaksi->sum('jumlah') }})</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="order-items-list">
                                    @foreach($transaksi->detailTransaksi as $detail)
                                    <div class="order-item">
                                        <div class="item-image">
                                            @if($detail->produk && $detail->produk->gambar)
                                            <img src="{{ asset('storage/' . $detail->produk->gambar) }}" 
                                                 alt="{{ $detail->produk->nama_produk }}">
                                            @else
                                            <div class="no-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="item-details">
                                            <h6 class="item-name">{{ $detail->produk ? $detail->produk->nama_produk : 'Produk tidak ditemukan' }}</h6>
                                            @if($detail->produk && $detail->produk->kategori)
                                            <span class="item-category" style="background-color: {{ $detail->produk->kategori->warna ?? '#6C757D' }}">
                                                {{ $detail->produk->kategori->nama_kategori }}
                                            </span>
                                            @endif
                                            <div class="item-quantity">{{ $detail->jumlah }}x Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="item-subtotal">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Info -->
                        @if($transaksi->pengiriman)
                        <div class="confirmation-card shipping-card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Informasi Pengiriman</h6>
                            </div>
                            <div class="card-body">
                                <div class="shipping-grid">
                                    <div class="shipping-info">
                                        <label>Kurir & Layanan:</label>
                                        <span>{{ $transaksi->pengiriman->kurir }} - {{ $transaksi->pengiriman->layanan }}</span>
                                    </div>
                                    <div class="shipping-info">
                                        <label>Berat Total:</label>
                                        <span>{{ $transaksi->pengiriman->weight }} gram</span>
                                    </div>
                                    <div class="shipping-info">
                                        <label>Alamat Tujuan:</label>
                                        <span>{{ $transaksi->alamat_pengiriman }}</span>
                                    </div>
                                    <div class="shipping-info">
                                        <label>Biaya Kirim:</label>
                                        <span class="shipping-cost">
                                            @if($transaksi->pengiriman->biaya > 0)
                                                Rp {{ number_format($transaksi->pengiriman->biaya, 0, ',', '.') }}
                                            @else
                                                <span class="text-success font-weight-bold">GRATIS</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Column: Payment Section -->
                    <div class="col-lg-5">
                        <!-- Payment Summary -->
                        <div class="confirmation-card payment-summary-card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Pembayaran</h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $subtotal = $transaksi->detailTransaksi->sum('subtotal');
                                    $shippingCost = $transaksi->pengiriman ? $transaksi->pengiriman->biaya : 0;
                                    $tax = $transaksi->total_harga - $subtotal - $shippingCost;
                                @endphp

                                <div class="payment-row">
                                    <span>Subtotal Produk:</span>
                                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                
                                <div class="payment-row">
                                    <span>Ongkos Kirim:</span>
                                    <span>
                                        @if($shippingCost > 0)
                                            Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                        @else
                                            <span class="text-success font-weight-bold">GRATIS</span>
                                        @endif
                                    </span>
                                </div>
                                
                                @if($tax > 0)
                                <div class="payment-row">
                                    <span>Pajak & Biaya Lain:</span>
                                    <span>Rp {{ number_format($tax, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                
                                <div class="payment-divider"></div>
                                <div class="payment-row payment-total">
                                    <span>Total Pembayaran:</span>
                                    <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Instructions -->
                        <div class="confirmation-card payment-instructions-card">
                            <div class="card-header {{ $isTripayPayment ? 'tripay-header' : 'manual-header' }}">
                                <h6 class="mb-0">
                                    <i class="fas fa-{{ $isTripayPayment ? 'mobile-alt' : 'university' }} me-2"></i>
                                    {{ $isTripayPayment ? 'Pembayaran Otomatis' : 'Pembayaran Manual' }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Payment Timer -->
                                <div class="payment-timer">
                                    <i class="fas fa-clock me-2"></i>
                                    <span>Batas Waktu: <strong id="paymentTimer">{{ $transaksi->expired_time->format('d F Y, H:i') }} WIB</strong></span>
                                </div>

                                @if($isTripayPayment)
                                    <!-- Tripay Payment Instructions -->
                                    <div class="tripay-instructions">
                                        @if($transaksi->pembayaran->qr_string || $transaksi->pembayaran->qr_url)
                                            <!-- QRIS Payment -->
                                            <div class="qris-payment">
                                                <div class="qr-code-container">
                                                    @if($transaksi->pembayaran->qr_url)
                                                        <img src="{{ $transaksi->pembayaran->qr_url }}" alt="QR Code" class="qr-code-image">
                                                    @elseif($transaksi->pembayaran->qr_string)
                                                        <div id="qrcode"></div>
                                                        <script>
                                                            document.addEventListener('DOMContentLoaded', function() {
                                                                if (typeof QRCode !== 'undefined') {
                                                                    new QRCode(document.getElementById("qrcode"), {
                                                                        text: "{{ $transaksi->pembayaran->qr_string }}",
                                                                        width: 200,
                                                                        height: 200
                                                                    });
                                                                }
                                                            });
                                                        </script>
                                                    @endif
                                                    <p class="qr-instruction">Scan QR Code untuk membayar</p>
                                                </div>
                                                <div class="qris-steps">
                                                    <h6><i class="fas fa-mobile-alt me-2"></i>Cara Pembayaran QRIS</h6>
                                                    <ol>
                                                        <li>Buka aplikasi e-wallet atau mobile banking</li>
                                                        <li>Pilih menu Scan QR atau QRIS</li>
                                                        <li>Scan QR Code di samping</li>
                                                        <li>Konfirmasi pembayaran</li>
                                                        <li>Pembayaran otomatis terkonfirmasi</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        @elseif($transaksi->pembayaran->payment_code)
                                            <!-- Virtual Account -->
                                            <div class="va-payment">
                                                <div class="payment-code-container">
                                                    <label>Kode Pembayaran:</label>
                                                    <div class="payment-code-display">
                                                        <span class="payment-code">{{ $transaksi->pembayaran->payment_code }}</span>
                                                        <button class="btn-copy" onclick="copyPaymentCode()">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="auto-payment-notice">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span>Status pembayaran akan diperbarui otomatis setelah Anda melakukan pembayaran</span>
                                        </div>
                                    </div>
                                @else
                                    <!-- Manual Payment Instructions -->
                                    <div class="manual-instructions">
                                        <div class="payment-methods">
                                            <div class="payment-method">
                                                <h6><i class="fas fa-university me-2"></i>Transfer Bank</h6>
                                                <div class="bank-details">
                                                    <div class="bank-item">
                                                        <strong>Bank BCA</strong><br>
                                                        No. Rekening: 1234567890<br>
                                                        A.n: {{ config('shop.name', 'Madu Barokah') }}
                                                    </div>
                                                    <div class="bank-item">
                                                        <strong>Bank Mandiri</strong><br>
                                                        No. Rekening: 9876543210<br>
                                                        A.n: {{ config('shop.name', 'Madu Barokah') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="payment-method">
                                                <h6><i class="fas fa-wallet me-2"></i>E-Wallet</h6>
                                                <div class="ewallet-details">
                                                    <strong>OVO / DANA / GoPay</strong><br>
                                                    No. HP: {{ config('shop.phone', '08123456789') }}<br>
                                                    A.n: {{ config('shop.name', 'Madu Barokah') }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="manual-payment-notice">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <div>
                                                <strong>Penting:</strong>
                                                <ul class="mb-0">
                                                    <li>Transfer sesuai total pembayaran</li>
                                                    <li>Simpan bukti transfer</li>
                                                    <li>Konfirmasi via WhatsApp setelah transfer</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="confirmation-card action-buttons-card">
                            <div class="card-body">
                                <div class="action-buttons">
                                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-action">
                                        <i class="fab fa-whatsapp me-2"></i>Konfirmasi WhatsApp
                                    </a>
                                    <a href="{{ route('frontend.history.index') }}" class="btn btn-primary btn-action">
                                        <i class="fas fa-history me-2"></i>Lihat Riwayat
                                    </a>
                                    <a href="{{ route('frontend.home') }}" class="btn btn-outline-primary btn-action">
                                        <i class="fas fa-home me-2"></i>Kembali Beranda
                                    </a>
                                    <button class="btn btn-outline-secondary btn-action" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Cetak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ============ CONFIRMATION PAGE STYLES ============ */

.confirmation-container {
    padding-top: 120px;
    min-height: 100vh;
    background: linear-gradient(135deg, #fff8e1 0%, #fff3c4 100%);
}

/* Loading Overlay */
.tripay-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}

.loading-content {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 165, 0, 0.2);
}

/* Main Cards */
.confirmation-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(204, 132, 0, 0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.confirmation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(204, 132, 0, 0.15);
}

.confirmation-card .card-header {
    background: linear-gradient(135deg, #cc8400, #ffaa00);
    color: white;
    padding: 1rem 1.5rem;
    border: none;
    font-weight: 600;
}

.confirmation-card .card-body {
    padding: 1.5rem;
}

/* Order Header Card */
.order-header-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
}

.order-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.2rem;
}

.order-status .status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending {
    background: #ffc107;
    color: #856404;
}

.status-berhasil {
    background: #28a745;
    color: white;
}

.status-gagal {
    background: #dc3545;
    color: white;
}

/* Order Items */
.order-items-list {
    max-height: 400px;
    overflow-y: auto;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.2s ease;
}

.order-item:hover {
    background-color: rgba(204, 132, 0, 0.02);
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    overflow: hidden;
    margin-right: 1rem;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.item-details {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 1rem;
    font-weight: 600;
    color: #cc8400;
    margin-bottom: 0.25rem;
}

.item-category {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    color: white;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.item-quantity {
    font-size: 0.875rem;
    color: #6c757d;
}

.item-subtotal {
    font-size: 1rem;
    font-weight: 700;
    color: #cc8400;
    text-align: right;
    flex-shrink: 0;
}

/* Shipping Grid */
.shipping-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.shipping-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.shipping-info label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
    margin: 0;
}

.shipping-info span {
    font-weight: 600;
    color: #333;
}

.shipping-cost {
    color: #cc8400 !important;
    font-size: 1.1em;
}

/* Payment Summary */
.payment-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
    font-size: 0.95rem;
}

.payment-row:last-child {
    border-bottom: none;
}

.payment-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #cc8400, transparent);
    margin: 1rem 0;
}

.payment-total {
    font-size: 1.1rem;
    font-weight: 700;
    color: #cc8400;
    padding: 1rem 0 0.5rem 0;
    border-top: 2px solid #cc8400;
    border-bottom: none;
}

/* Payment Instructions */
.tripay-header {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
}

.manual-header {
    background: linear-gradient(135deg, #ffc107, #ffb300) !important;
    color: #333 !important;
}

.payment-timer {
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    text-align: center;
    font-size: 0.95rem;
    color: #856404;
}

/* QRIS Payment */
.qris-payment {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 1.5rem;
    align-items: start;
}

.qr-code-container {
    text-align: center;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 12px;
    border: 2px dashed #dee2e6;
}

.qr-code-image {
    max-width: 180px;
    border-radius: 8px;
}

.qr-instruction {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0;
}

.qris-steps h6 {
    color: #28a745;
    margin-bottom: 1rem;
}

.qris-steps ol {
    padding-left: 1.2rem;
    margin-bottom: 0;
}

.qris-steps li {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Virtual Account */
.payment-code-container label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 0.5rem;
    display: block;
}

.payment-code-display {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 1rem;
    gap: 1rem;
}

.payment-code {
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    font-weight: 700;
    color: #0066cc;
    flex: 1;
    letter-spacing: 2px;
}

.btn-copy {
    background: #0066cc;
    color: white;
    border: none;
    border-radius: 8px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-copy:hover {
    background: #0052a3;
    transform: scale(1.05);
}

/* Payment Methods */
.payment-methods {
    display: grid;
    gap: 1.5rem;
}

.payment-method h6 {
    color: #cc8400;
    margin-bottom: 1rem;
    border-bottom: 2px solid rgba(204, 132, 0, 0.2);
    padding-bottom: 0.5rem;
}

.bank-details {
    display: grid;
    gap: 1rem;
}

.bank-item, .ewallet-details {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #cc8400;
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Payment Notices */
.auto-payment-notice, .manual-payment-notice {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid rgba(40, 167, 69, 0.3);
    border-radius: 12px;
    padding: 1rem;
    margin-top: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-size: 0.9rem;
    line-height: 1.4;
}

.manual-payment-notice {
    background: rgba(255, 193, 7, 0.1);
    border-color: rgba(255, 193, 7, 0.3);
}

.manual-payment-notice i {
    color: #856404;
    margin-top: 0.1rem;
}

.auto-payment-notice i {
    color: #28a745;
    margin-top: 0.1rem;
}

.manual-payment-notice ul {
    margin: 0.5rem 0 0 0;
    padding-left: 1.2rem;
}

.manual-payment-notice li {
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

/* Action Buttons */
.action-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.btn-action {
    padding: 0.75rem 1rem;
    border-radius: 12px;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    text-decoration: none;
}

.btn-success.btn-action {
    background: #28a745;
    color: white;
}

.btn-success.btn-action:hover {
    background: #218838;
    color: white;
}

.btn-primary.btn-action {
    background: #0066cc;
    color: white;
}

.btn-primary.btn-action:hover {
    background: #0052a3;
    color: white;
}

.btn-outline-primary.btn-action {
    border-color: #cc8400;
    color: #cc8400;
}

.btn-outline-primary.btn-action:hover {
    background: #cc8400;
    color: white;
    border-color: #cc8400;
}

.btn-outline-secondary.btn-action {
    border-color: #6c757d;
    color: #6c757d;
}

.btn-outline-secondary.btn-action:hover {
    background: #6c757d;
    color: white;
    border-color: #6c757d;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .qris-payment {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .qr-code-container {
        order: -1;
    }
}

@media (max-width: 768px) {
    .confirmation-container {
        padding-top: 100px;
    }
    
    .confirmation-card .card-header,
    .confirmation-card .card-body {
        padding: 1rem;
    }
    
    .order-header-card .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-icon {
        margin-right: 0.75rem;
        width: 40px;
        height: 40px;
    }
    
    .shipping-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        grid-template-columns: 1fr;
    }
    
    .payment-code {
        font-size: 1rem;
        letter-spacing: 1px;
    }
    
    .item-image {
        width: 50px;
        height: 50px;
    }
    
    .item-name {
        font-size: 0.9rem;
    }
    
    .item-subtotal {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .confirmation-card {
        border-radius: 12px;
        margin-bottom: 1rem;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
    }
    
    .item-image {
        margin-right: 0;
        align-self: center;
    }
    
    .item-details {
        text-align: center;
    }
    
    .item-subtotal {
        text-align: center;
        font-size: 1rem;
    }
}

/* Print Styles */
@media print {
    .confirmation-container {
        padding-top: 0;
        background: white;
    }
    
    .confirmation-card {
        box-shadow: none;
        border: 1px solid #dee2e6;
        page-break-inside: avoid;
    }
    
    .action-buttons-card,
    .tripay-loading-overlay {
        display: none !important;
    }
    
    .confirmation-card .card-header {
        background: #f8f9fa !important;
        color: #333 !important;
    }
}
</style>
@endpush

@push('scripts')
<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

<script>
$(document).ready(function() {
    // Payment timer countdown
    const expiredTime = new Date('{{ $transaksi->expired_time->format('Y-m-d H:i:s') }}').getTime();
    
    function updateTimer() {
        const now = new Date().getTime();
        const distance = expiredTime - now;
        
        if (distance > 0) {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            let timerText = '';
            if (days > 0) timerText += days + 'h ';
            if (hours > 0) timerText += hours + 'j ';
            timerText += minutes + 'm ' + seconds + 's';
            
            $('#paymentTimer').html('<strong>' + timerText + '</strong>');
        } else {
            $('#paymentTimer').html('<strong class="text-danger">EXPIRED</strong>');
            clearInterval(timerInterval);
        }
    }
    
    // Update timer immediately and then every second
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);
    
    // Auto refresh page every 30 seconds for Tripay payments to check status
    @if($isTripayPayment && $transaksi->pembayaran->status === 'pending')
    let refreshInterval = setInterval(function() {
        // Check if payment status has changed
        $.get(window.location.pathname + '?check_status=1')
            .done(function(response) {
                if (typeof response === 'string' && response.includes('status-berhasil')) {
                    clearInterval(refreshInterval);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil!',
                        text: 'Terima kasih, pembayaran Anda telah berhasil diproses.',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                }
            })
            .fail(function() {
                console.log('Failed to check payment status');
            });
    }, 15000); // Check every 15 seconds
    @endif
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // Add animation on card hover
    $('.confirmation-card').on('mouseenter', function() {
        $(this).addClass('animate__animated animate__pulse animate__faster');
    }).on('mouseleave', function() {
        $(this).removeClass('animate__animated animate__pulse animate__faster');
    });
    
    // Smooth transitions for action buttons
    $('.btn-action').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        
        if (!$btn.attr('href') || $btn.attr('href') === '#') {
            return;
        }
        
        // Add loading state for external links
        if ($btn.attr('target') === '_blank') {
            $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Membuka...');
            setTimeout(() => {
                $btn.html(originalText);
            }, 2000);
        }
    });
});

// Copy payment code function
function copyPaymentCode() {
    const paymentCode = '{{ $transaksi->pembayaran->payment_code ?? "" }}';
    const button = event.target.closest('.btn-copy');
    
    if (!paymentCode) {
        showNotification('error', 'Kode pembayaran tidak tersedia');
        return;
    }
    
    navigator.clipboard.writeText(paymentCode).then(function() {
        // Show success feedback
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.style.background = '#28a745';
        
        showNotification('success', 'Kode pembayaran berhasil disalin!');
        
        setTimeout(function() {
            button.innerHTML = originalIcon;
            button.style.background = '#0066cc';
        }, 2000);
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = paymentCode;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        showNotification('success', 'Kode pembayaran berhasil disalin!');
    });
}

// Show notification function
function showNotification(type, message) {
    const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';
    const color = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#cc8400';
    
    Swal.fire({
        icon: icon,
        title: type === 'success' ? 'Berhasil!' : type === 'error' ? 'Error!' : 'Info',
        text: message,
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        position: 'top-end',
        timerProgressBar: true,
        confirmButtonColor: color
    });
}

// Print function with custom styles
function printOrder() {
    window.print();
}

// Check if page loaded from back button and refresh if needed
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        // Page was loaded from back/forward cache
        location.reload();
    }
});
</script>
@endpush

@endsection
