@extends('layouts.frontend')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="cart-container">
    <div class="container">
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

        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="text-gelap mb-1">
                            <i class="fas fa-history me-2"></i>Riwayat Transaksi
                        </h2>
                        <p class="text-muted mb-0">Lihat semua pesanan yang pernah Anda buat</p>
                    </div>
                    <a href="{{ route('frontend.home') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                    </a>
                </div>

                @if($transaksi->count() > 0)
                    <!-- Transaction List -->
                    @foreach($transaksi as $t)
                    <div class="card-cart shadow-sm mb-3 transaction-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Order Info -->
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <h6 class="mb-1 text-primary">{{ $t->merchant_ref }}</h6>
                                    <small class="text-muted d-flex align-items-center">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $t->tanggal_transaksi->format('d M Y, H:i') }}
                                    </small>
                                </div>

                                <!-- Items Preview -->
                                <div class="col-md-4 mb-3 mb-md-0">
                                    @php
                                        $totalItems = $t->detailTransaksi->sum('jumlah');
                                        $firstItem = $t->detailTransaksi->first();
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        @if($firstItem && $firstItem->produk && $firstItem->produk->gambar)
                                            <img src="{{ asset('storage/' . $firstItem->produk->gambar) }}" 
                                                 alt="{{ $firstItem->produk->nama_produk }}" 
                                                 class="me-2 shadow-sm" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                        @else
                                            <div class="me-2 d-flex align-items-center justify-content-center bg-light rounded" 
                                                 style="width: 50px; height: 50px; border-radius: 8px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-weight-medium text-gelap">
                                                {{ $firstItem && $firstItem->produk ? $firstItem->produk->nama_produk : 'Produk tidak tersedia' }}
                                                @if($t->detailTransaksi->count() > 1)
                                                    <small class="text-muted">+{{ $t->detailTransaksi->count() - 1 }} lainnya</small>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-cube me-1"></i>{{ $totalItems }} item
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total & Status -->
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="text-md-end">
                                        <div class="h6 mb-2 text-gelap">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</div>
                                        <div class="mb-2">
                                            @php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'berhasil' => 'success', 
                                                    'gagal' => 'danger',
                                                    'expired' => 'secondary',
                                                    'canceled' => 'secondary'
                                                ];
                                                $paymentStatus = $t->pembayaran ? $t->pembayaran->status : 'pending';
                                            @endphp
                                            <span class="badge bg-{{ $statusClass[$paymentStatus] ?? 'secondary' }}">
                                                {{ ucfirst($paymentStatus) }}
                                            </span>
                                        </div>
                                        @if($t->pengiriman)
                                            <small class="text-muted">
                                                <i class="fas fa-truck me-1"></i>{{ $t->pengiriman->kurir }} - {{ $t->pengiriman->layanan }}
                                            </small>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-md-2">
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('frontend.history.detail', $t->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </a>
                                        @if($paymentStatus === 'pending')
                                            <a href="{{ route('frontend.confirmation.show', $t->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-credit-card me-1"></i>Bayar
                                            </a>
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
                            <i class="fas fa-shopping-cart me-2"></i>Mulai Berbelanja
                        </a>
                    </div>
                @endif
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

.transaction-card {
    border-radius: 12px;
    border: none;
    transition: all 0.3s ease;
}

.transaction-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(204, 132, 0, 0.15) !important;
}

.transaction-card .card-body {
    padding: 1.5rem;
}

.badge {
    font-size: 0.75em;
    padding: 0.5em 0.75em;
    border-radius: 6px;
}

.font-weight-medium {
    font-weight: 500;
}

.gap-2 > * {
    margin-bottom: 0.5rem;
}

.gap-2 > *:last-child {
    margin-bottom: 0;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 6px;
    transition: all 0.3s ease;
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
@media (max-width: 768px) {
    .transaction-card .card-body {
        padding: 1rem;
    }
    
    .d-flex.flex-column.gap-2 {
        flex-direction: row !important;
        gap: 0.5rem;
    }
    
    .gap-2 > * {
        margin-bottom: 0;
        margin-right: 0.5rem;
        flex: 1;
    }
    
    .gap-2 > *:last-child {
        margin-right: 0;
    }
    
    .text-md-end {
        text-align: left !important;
    }
    
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
}

@media (max-width: 576px) {
    .d-flex.align-items-center img,
    .d-flex.align-items-center .bg-light {
        width: 40px !important;
        height: 40px !important;
    }
    
    .font-weight-medium {
        font-size: 0.9rem;
    }
    
    .h6 {
        margin-bottom: 0.25rem !important;
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
</script>
@endpush

@endsection
