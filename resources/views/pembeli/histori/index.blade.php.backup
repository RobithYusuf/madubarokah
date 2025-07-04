@extends('layouts.frontend')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="cart-container">
    <div class="container py-1">
        <div class="row">
            <div class="col-12 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent px-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}" class="text-gelap">Beranda</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Riwayat Transaksi</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Session Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="text-gelap mb-1">
                            <i class="fas fa-history mr-2"></i>Riwayat Transaksi
                        </h2>
                        <p class="text-muted mb-0">Lihat semua pesanan yang pernah Anda buat</p>
                    </div>
                    <a href="{{ route('frontend.home') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
                    </a>
                </div>

                @if($transaksi->count() > 0)
                    <!-- Transaction List -->
                    @foreach($transaksi as $t)
                    <div class="card-cart shadow-sm mb-4 transaction-card">
                        <div class="card-body p-0">
                            <!-- Transaction Header -->
                            <div class="transaction-header p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-receipt text-white mr-2"></i>
                                            <h6 class="mb-0 text-white invoice-number">{{ $t->merchant_ref }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="d-flex align-items-center justify-content-md-end">
                                            <i class="fas fa-calendar-alt text-white mr-2"></i>
                                            <span class="text-white">{{ $t->tanggal_transaksi->format('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Transaction Content -->
                            <div class="transaction-content p-3 py-3">
                                <div class="row align-items-center">
                                    <!-- Product Info -->
                                    <div class="col-lg-5 mb-3 mb-lg-0">
                                        @php
                                            $totalItems = $t->detailTransaksi->sum('jumlah');
                                            $firstItem = $t->detailTransaksi->first();
                                        @endphp
                                        <div class="d-flex">
                                            <div class="product-img-wrapper">
                                                @if($firstItem && $firstItem->produk && $firstItem->produk->gambar)
                                                    <img src="{{ asset('storage/' . $firstItem->produk->gambar) }}" 
                                                         alt="{{ $firstItem->produk->nama_produk }}" 
                                                         class="product-img">
                                                @else
                                                    <div class="product-placeholder">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="product-details ms-3">
                                                <h6 class="product-name mb-1">
                                                    {{ $firstItem && $firstItem->produk ? $firstItem->produk->nama_produk : 'Produk tidak tersedia' }}
                                                </h6>
                                                @if($t->detailTransaksi->count() > 1)
                                                    <span class="product-extras">+{{ $t->detailTransaksi->count() - 1 }} lainnya</span>
                                                @endif
                                                <div class="mt-1">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-cube me-1"></i>{{ $totalItems }} item
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Payment Info -->
                                    <div class="col-lg-4 mb-3 mb-lg-0">
                                        <div class="price-status-container">
                                            <div class="price mb-3">
                                                <h5 class="text-gelap mb-0">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</h5>
                                            </div>
                                            
                                            <div class="status-section">
                                                @php
                                                    $statusClass = [
                                                        'pending' => 'warning',
                                                        'berhasil' => 'success', 
                                                        'dibayar' => 'success',
                                                        'gagal' => 'danger',
                                                        'expired' => 'secondary',
                                                        'canceled' => 'secondary',
                                                        'batal' => 'secondary'
                                                    ];
                                                    $paymentStatus = $t->pembayaran ? $t->pembayaran->status : 'pending';
                                                @endphp
                                                
                                                <div class="status-row">
                                                    <div class="status-label">Pembayaran:</div>
                                                    <div class="status-badge">
                                                        <span class="badge bg-{{ $statusClass[$paymentStatus] ?? 'secondary' }}">
                                                            {{ ucfirst($paymentStatus) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                @if($t->pengiriman)
                                                    @php
                                                        $shippingStatusClass = [
                                                            'menunggu_pembayaran' => 'warning',
                                                            'diproses' => 'info',
                                                            'dikirim' => 'primary',
                                                            'diterima' => 'success',
                                                            'dibatalkan' => 'danger'
                                                        ];
                                                        $shippingStatus = $t->pengiriman->status ?? 'menunggu_pembayaran';
                                                    @endphp
                                                    <div class="status-row mt-2">
                                                        <div class="status-label">Pengiriman:</div>
                                                        <div class="status-badge">
                                                            <span class="badge bg-{{ $shippingStatusClass[$shippingStatus] ?? 'secondary' }}">
                                                                <i class="fas fa-truck me-1"></i>{{ ucfirst(str_replace('_', ' ', $shippingStatus)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Shipping & Actions -->
                                    <div class="col-lg-3">
                                        @if($t->pengiriman)
                                            <div class="shipping-info mb-3">
                                                <div class="courier-label">Kurir:</div>
                                                <div class="courier-value">
                                                    <i class="fas fa-shipping-fast me-1 text-muted"></i>
                                                    <span>{{ $t->pengiriman->kurir }} - {{ $t->pengiriman->layanan }}</span>
                                                </div>
                                                
                                                @if($t->pengiriman->resi)
                                                    <div class="resi-info mt-2">
                                                        <div class="resi-label">Resi:</div>
                                                        <div class="resi-value text-primary">{{ $t->pengiriman->resi }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <div class="action-buttons">
                                            <a href="{{ route('frontend.history.detail', $t->id) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </a>
                                            @if($t->status === 'dikirim')
                                                <form action="{{ route('frontend.history.confirmReceipt', $t->id) }}" method="POST" class="w-100 mb-2" id="confirmReceiptForm-{{ $t->id }}">
                                                    @csrf
                                                    <button type="button" class="btn btn-success btn-sm w-100" onclick="confirmReceipt({{ $t->id }})">
                                                        <i class="fas fa-check-circle me-1"></i>Terima
                                                    </button>
                                                </form>
                                            @endif
                                            @if($paymentStatus === 'pending')
                                                <a href="{{ route('frontend.confirmation.show', $t->id) }}" class="btn btn-warning btn-sm w-100">
                                                    <i class="fas fa-credit-card me-1"></i>Bayar
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Pagination -->
                    @if($transaksi->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $transaksi->links() }}
                    </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-shopping-bag fa-4x text-muted"></i>
                        </div>
                        <h4 class="text-muted mb-3">Belum Ada Transaksi</h4>
                        <p class="text-muted mb-4">
                            Anda belum melakukan pembelian apapun.<br>
                            Mulai berbelanja dan temukan produk madu berkualitas!
                        </p>
                        <a href="{{ route('frontend.home') }}" class="btn btn-orange btn-lg">
                            <i class="fas fa-shopping-cart mr-2"></i>Mulai Berbelanja
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Base styles */
.breadcrumb-item a {
    text-decoration: none;
    transition: all 0.3s ease;
    color: #cc8400;
}

.breadcrumb-item a:hover {
    color: #cc8400 !important;
    transform: translateX(2px);
}

.text-gelap {
    color: #cc8400;
}

.btn-outline-primary {
    border-color: #cc8400;
    color: #cc8400;
}

.btn-outline-primary:hover {
    background-color: #cc8400;
    border-color: #cc8400;
    color: white;
}

.btn-orange {
    background-color: #cc8400;
    border-color: #cc8400;
    color: white;
}

.btn-orange:hover {
    background-color: #b8790a;
    border-color: #b8790a;
    color: white;
}

/* Transaction card styling */
.transaction-card {
    border-radius: 12px;
    border: none;
    transition: all 0.3s ease;
    overflow: hidden;
    margin-bottom: 20px;
}

.transaction-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(204, 132, 0, 0.15) !important;
}

.transaction-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
    background-color: #cc8400;
}

.invoice-number {
    font-family: monospace;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Product styling */
.product-img-wrapper {
    margin: 0 15px 0 0;
    width: 70px;
    height: 70px;
    flex-shrink: 0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

.product-details {
    flex: 1;
}

.product-name {
    font-weight: 600;
    color: #333;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-extras {
    color: #6c757d;
    font-size: 0.8rem;
}

/* Status styling */
.price-status-container {
    padding-left: 20px;
    border-left: 1px dashed rgba(0,0,0,0.1);
    height: 100%;
}

.status-section {
    margin-top: 10px;
}

.status-row {
    display: flex;
    align-items: center;
}

.status-label {
    font-size: 0.85rem;
    color: #555;
    font-weight: 500;
    width: 100px;
    flex-shrink: 0;
}

.status-badge {
    flex: 1;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
    border-radius: 6px;
    font-size: 0.75rem;
}

/* Shipping info */
.shipping-info {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    font-size: 0.85rem;
    margin-bottom: 15px;
}

.courier-label, .resi-label {
    color: #555;
    font-weight: 500;
    margin-bottom: 5px;
}

.courier-value, .resi-value {
    font-weight: 500;
}

.resi-value {
    letter-spacing: 0.5px;
    font-family: monospace;
    font-size: 0.9rem;
}

/* Button styling */
.action-buttons {
    display: flex;
    flex-direction: column;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
    padding: 0.6rem 1.2rem;
    transition: all 0.3s ease;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #212529;
}

/* Pagination Styling */
.pagination {
    justify-content: center;
}

.page-link {
    color: #cc8400;
    border-color: #dee2e6;
    padding: 0.5rem 0.75rem;
}

.page-link:hover {
    color: #b8790a;
    background-color: rgba(204, 132, 0, 0.1);
    border-color: #cc8400;
}

.page-item.active .page-link {
    background-color: #cc8400;
    border-color: #cc8400;
    color: white;
}

/* Responsive Design */
@media (max-width: 992px) {
    .price-status-container {
        padding-left: 0;
        border-left: none;
        margin-top: 15px;
        margin-bottom: 15px;
    }
    
    .action-buttons {
        flex-direction: row;
        gap: 10px;
    }
    
    .action-buttons .btn {
        flex: 1;
        margin-bottom: 0 !important;
    }
}

@media (max-width: 768px) {
    .transaction-header {
        padding: 12px !important;
    }
    
    .transaction-content {
        padding: 12px !important;
    }
    
    .product-img-wrapper {
        width: 60px;
        height: 60px;
    }
}

@media (max-width: 576px) {
    .product-img-wrapper {
        width: 50px;
        height: 50px;
    }
    
    .status-label {
        width: 90px;
        font-size: 0.8rem;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.4em 0.6em;
    }
    
    .shipping-info {
        padding: 10px;
        font-size: 0.8rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Add loading state to detail buttons
    $('.btn-outline-primary').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Loading...');
        
        // Let the page navigate normally, but show loading state briefly
        setTimeout(() => {
            btn.prop('disabled', false).html(originalText);
        }, 1000);
    });
    
    // Add loading state to payment buttons
    $('.btn-warning').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Loading...');
        
        // Let the page navigate normally, but show loading state briefly
        setTimeout(() => {
            btn.prop('disabled', false).html(originalText);
        }, 1000);
    });
    
    // Smooth scroll animation for pagination
    $('.pagination a').click(function(e) {
        $('html, body').animate({
            scrollTop: $('.transaction-card:first').offset().top - 100
        }, 500);
    });
});

// Confirm receipt function
function confirmReceipt(transaksiId) {
    Swal.fire({
        title: 'Konfirmasi Penerimaan',
        text: 'Apakah Anda yakin telah menerima pesanan ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Pesanan Telah Diterima',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('confirmReceiptForm-' + transaksiId).submit();
        }
    });
}
</script>
@endpush

@endsection