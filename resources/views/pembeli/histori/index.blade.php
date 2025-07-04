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
                    @php
                    // Determine unified status - same logic as admin
                    $transactionStatus = $t->status;
                    $paymentStatus = $t->pembayaran ? $t->pembayaran->status : 'pending';
                    $shippingStatus = $t->pengiriman ? $t->pengiriman->status : 'menunggu_pembayaran';
                    
                    $unifiedStatus = 'pending';
                    $statusLabel = '🟡 Menunggu Pembayaran';
                    $statusColor = 'warning';
                    $statusIcon = 'clock';
                    
                    if (in_array($transactionStatus, ['batal', 'gagal', 'expired'])) {
                        $unifiedStatus = 'canceled';
                        $statusLabel = '❌ Dibatalkan';
                        $statusColor = 'danger';
                        $statusIcon = 'times-circle';
                    } elseif ($transactionStatus === 'selesai') {
                        $unifiedStatus = 'completed';
                        $statusLabel = '✅ Selesai';
                        $statusColor = 'success';
                        $statusIcon = 'check-circle';
                    } elseif ($transactionStatus === 'dikirim') {
                        $unifiedStatus = 'shipped';
                        $statusLabel = '🚚 Sedang Dikirim';
                        $statusColor = 'primary';
                        $statusIcon = 'truck';
                    } elseif (in_array($transactionStatus, ['dibayar', 'berhasil'])) {
                        $unifiedStatus = 'processing';
                        $statusLabel = '📦 Sedang Diproses';
                        $statusColor = 'info';
                        $statusIcon = 'box';
                    }
                    
                    $hasResi = $t->pengiriman && $t->pengiriman->resi;
                    @endphp
                    <div class="card-cart shadow-sm mb-4 transaction-card">
                        <div class="card-body p-0">
                            <!-- Transaction Header -->
                            <div class="transaction-header p-3 bg-{{ $statusColor }}">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-receipt text-white mr-2"></i>
                                            <h6 class="mb-0 text-white invoice-number">{{ $t->merchant_ref }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="d-flex align-items-center justify-content-md-end">
                                            <span class="badge bg-white text-{{ $statusColor }} px-3 py-2">
                                                {{ $statusLabel }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Transaction Content -->
                            <div class="transaction-content p-3 py-3">
                                <div class="row align-items-center">
                                    <!-- Product Info -->
                                    <div class="col-lg-4 mb-3 mb-lg-0">
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
                                                    <span class="product-extras">+{{ $t->detailTransaksi->count() - 1 }} produk lainnya</span>
                                                @endif
                                                <div class="mt-1">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-cube me-1"></i>{{ $totalItems }} item
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Order Info -->
                                    <div class="col-lg-4 mb-3 mb-lg-0">
                                        <div class="order-info">
                                            <div class="mb-2">
                                                <small class="text-muted d-block">Tanggal Pesanan</small>
                                                <span class="fw-bold">{{ $t->tanggal_transaksi->format('d F Y, H:i') }}</span>
                                            </div>
                                            <div class="mb-2">
                                                <small class="text-muted d-block">Total Pembayaran</small>
                                                <h5 class="text-gelap mb-0">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</h5>
                                            </div>
                                            @if($t->pembayaran)
                                            <div class="mb-2">
                                                <small class="text-muted d-block">Metode Pembayaran</small>
                                                <span class="fw-bold">{{ $t->pembayaran->metode }}</span>
                                                @if($t->pembayaran->waktu_bayar)
                                                <span class="badge bg-success ms-2">Lunas</span>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Shipping & Actions -->
                                    <div class="col-lg-4">
                                        @if($t->pengiriman)
                                            <div class="shipping-info mb-3">
                                                <small class="text-muted d-block">Pengiriman</small>
                                                <div class="fw-bold">
                                                    {{ $t->pengiriman->kurir }} - {{ $t->pengiriman->layanan }}
                                                </div>
                                                
                                                @if($hasResi)
                                                    <div class="mt-2">
                                                        <small class="text-muted d-block">Nomor Resi</small>
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-primary fw-bold">{{ $t->pengiriman->resi }}</span>
                                                            <button class="btn btn-sm btn-link copy-resi" data-resi="{{ $t->pengiriman->resi }}">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <div class="action-buttons">
                                            <a href="{{ route('frontend.history.detail', $t->id) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                                <i class="fas fa-eye me-1"></i>Lihat Detail
                                            </a>
                                            
                                            @if($unifiedStatus === 'pending')
                                                <a href="{{ route('frontend.confirmation.show', $t->id) }}" class="btn btn-warning btn-sm w-100 mb-2">
                                                    <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                                </a>
                                            @elseif($unifiedStatus === 'shipped')
                                                <form action="{{ route('frontend.history.confirmReceipt', $t->id) }}" method="POST" class="w-100 mb-2" id="confirmReceiptForm-{{ $t->id }}">
                                                    @csrf
                                                    <button type="button" class="btn btn-success btn-sm w-100" onclick="confirmReceipt({{ $t->id }})">
                                                        <i class="fas fa-check-circle me-1"></i>Pesanan Diterima
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <a href="https://wa.me/{{ $whatsappNumber ?? '6281234567890' }}?text=Halo, saya ingin bertanya tentang pesanan {{ $t->merchant_ref }}" 
                                               target="_blank" 
                                               class="btn btn-outline-success btn-sm w-100">
                                                <i class="fab fa-whatsapp me-1"></i>Hubungi Penjual
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Timeline -->
                                <div class="status-timeline mt-4 pt-3 border-top">
                                    <div class="timeline-wrapper">
                                        <div class="timeline-item {{ $unifiedStatus != 'canceled' ? 'active' : '' }}">
                                            <div class="timeline-icon">
                                                <i class="fas fa-shopping-cart"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <small class="text-muted">Pesanan Dibuat</small>
                                                <div class="small">{{ $t->created_at->format('d/m H:i') }}</div>
                                            </div>
                                        </div>

                                        @if($unifiedStatus != 'canceled')
                                            <div class="timeline-item {{ in_array($unifiedStatus, ['processing', 'shipped', 'completed']) ? 'active' : '' }}">
                                                <div class="timeline-icon">
                                                    <i class="fas fa-credit-card"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <small class="text-muted">Pembayaran</small>
                                                    @if($t->pembayaran && $t->pembayaran->waktu_bayar)
                                                        <div class="small">{{ $t->pembayaran->waktu_bayar->format('d/m H:i') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="timeline-item {{ in_array($unifiedStatus, ['shipped', 'completed']) ? 'active' : '' }}">
                                                <div class="timeline-icon">
                                                    <i class="fas fa-truck"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <small class="text-muted">Dikirim</small>
                                                    @if($hasResi)
                                                        <div class="small">Resi: {{ $t->pengiriman->resi }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="timeline-item {{ $unifiedStatus === 'completed' ? 'active' : '' }}">
                                                <div class="timeline-icon">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <small class="text-muted">Selesai</small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="timeline-item active canceled">
                                                <div class="timeline-icon">
                                                    <i class="fas fa-times-circle"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <small class="text-muted">Dibatalkan</small>
                                                    <div class="small">{{ $t->updated_at->format('d/m H:i') }}</div>
                                                </div>
                                            </div>
                                        @endif
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

/* Status Timeline Styling */
.status-timeline {
    position: relative;
}

.timeline-wrapper {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding: 0 20px;
}

.timeline-wrapper::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 40px;
    right: 40px;
    height: 2px;
    background-color: #e0e0e0;
    z-index: 0;
}

.timeline-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    flex: 1;
}

.timeline-item.active .timeline-icon {
    background-color: #28a745;
    color: white;
}

.timeline-item.active.canceled .timeline-icon {
    background-color: #dc3545;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e0e0e0;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 16px;
}

.timeline-content {
    text-align: center;
}

/* Order info styling */
.order-info {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
}

/* Shipping info */
.shipping-info {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
}

/* Button styling */
.action-buttons {
    display: flex;
    flex-direction: column;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.copy-resi {
    padding: 0.25rem 0.5rem;
    margin-left: 0.5rem;
    color: #cc8400;
}

.copy-resi:hover {
    color: #b8790a;
}

/* Badge styling */
.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
    border-radius: 6px;
    font-size: 0.75rem;
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
    .timeline-wrapper {
        flex-direction: column;
        padding: 0;
    }
    
    .timeline-wrapper::before {
        top: 40px;
        bottom: 40px;
        left: 20px;
        right: auto;
        width: 2px;
        height: auto;
    }
    
    .timeline-item {
        flex-direction: row;
        justify-content: flex-start;
        margin-bottom: 20px;
    }
    
    .timeline-icon {
        margin-bottom: 0;
        margin-right: 15px;
    }
    
    .timeline-content {
        text-align: left;
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
    
    .order-info {
        margin-top: 15px;
    }
}

@media (max-width: 576px) {
    .product-img-wrapper {
        width: 50px;
        height: 50px;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.4em 0.6em;
    }
    
    .shipping-info {
        padding: 10px;
        font-size: 0.875rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Copy resi functionality
    $('.copy-resi').on('click', function() {
        const resi = $(this).data('resi');
        navigator.clipboard.writeText(resi).then(function() {
            // Show temporary feedback
            const btn = $(this);
            const originalHtml = btn.html();
            btn.html('<i class="fas fa-check"></i>');
            setTimeout(() => {
                btn.html(originalHtml);
            }, 2000);
        }.bind(this));
    });
    
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