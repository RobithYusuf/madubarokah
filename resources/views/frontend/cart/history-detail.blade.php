@extends('layouts.frontend')

@section('title', 'Detail Transaksi - ' . $transaksi->merchant_ref)

@section('content')
<div class="cart-container">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent px-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}" class="text-gelap">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.history.index') }}" class="text-gelap">Riwayat</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Detail Transaksi</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="text-gelap mb-1">Detail Transaksi</h2>
                        <p class="text-muted mb-0">{{ $transaksi->merchant_ref }}</p>
                    </div>
                    <a href="{{ route('frontend.history.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Riwayat
                    </a>
                </div>

                <!-- Status Alert -->
                @php
                    $paymentStatus = $transaksi->pembayaran ? $transaksi->pembayaran->status : 'pending';
                    $alertClass = [
                        'pending' => 'warning',
                        'berhasil' => 'success',
                        'gagal' => 'danger',
                        'expired' => 'secondary',
                        'canceled' => 'secondary'
                    ];
                    $alertIcon = [
                        'pending' => 'clock',
                        'berhasil' => 'check-circle',
                        'gagal' => 'times-circle',
                        'expired' => 'hourglass-end',
                        'canceled' => 'ban'
                    ];
                @endphp
                
                <div class="alert alert-{{ $alertClass[$paymentStatus] ?? 'secondary' }} alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-{{ $alertIcon[$paymentStatus] ?? 'info-circle' }} fa-2x me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-1">Status: {{ ucfirst($paymentStatus) }}</h5>
                            @if($paymentStatus === 'pending')
                                <p class="mb-0">Transaksi menunggu pembayaran. Silakan lakukan pembayaran untuk memproses pesanan.</p>
                            @elseif($paymentStatus === 'berhasil')
                                <p class="mb-0">Pembayaran berhasil! Pesanan Anda sedang diproses.</p>
                            @elseif($paymentStatus === 'gagal')
                                <p class="mb-0">Pembayaran gagal. Silakan hubungi customer service untuk bantuan.</p>
                            @elseif($paymentStatus === 'expired')
                                <p class="mb-0">Transaksi telah kedaluwarsa. Silakan buat pesanan baru.</p>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <!-- Order Details Card -->
                <div class="card-cart shadow-sm mb-4">
                    <div class="card-header bg-gelap">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-receipt me-2"></i>Detail Pesanan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-muted">ID Pesanan</label>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary p-2 me-2 order-id-badge" style="cursor: pointer;" title="Klik untuk menyalin">{{ $transaksi->merchant_ref }}</span>
                                    <small class="text-muted">Klik untuk menyalin</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-muted">Tanggal Pesanan</label>
                                <div>{{ $transaksi->tanggal_transaksi->format('d F Y, H:i') }} WIB</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-muted">Nama Penerima</label>
                                <div>{{ $transaksi->nama_penerima }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-muted">No. Telepon</label>
                                <div>{{ $transaksi->telepon_penerima }}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label font-weight-bold text-muted">Alamat Pengiriman</label>
                            <div>{{ $transaksi->alamat_pengiriman }}</div>
                        </div>

                        @if($transaksi->catatan)
                        <div class="mb-4">
                            <label class="form-label font-weight-bold text-muted">Catatan</label>
                            <div class="fst-italic">{{ $transaksi->catatan }}</div>
                        </div>
                        @endif

                        <!-- Order Items -->
                        <h6 class="mb-3 text-gelap">Item Pesanan</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transaksi->detailTransaksi as $detail)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($detail->produk && $detail->produk->gambar)
                                                <img src="{{ asset('storage/' . $detail->produk->gambar) }}" 
                                                     alt="{{ $detail->produk->nama_produk }}" 
                                                     class="me-2 shadow-sm"
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                @endif
                                                <div>
                                                    <h6 class="mb-0 text-gelap">{{ $detail->produk ? $detail->produk->nama_produk : 'Produk tidak ditemukan' }}</h6>
                                                    @if($detail->produk && $detail->produk->kategori)
                                                    <span class="badge text-white mt-1" 
                                                          style="background-color: {{ $detail->produk->kategori->warna ?? '#6C757D' }}; font-size: 0.7em;">
                                                        {{ $detail->produk->kategori->nama_kategori }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $detail->jumlah }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="text-end font-weight-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Shipping Info -->
                        @if($transaksi->pengiriman)
                        <div class="mt-4">
                            <h6 class="text-gelap">Informasi Pengiriman</h6>
                            <div class="bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong>Kurir:</strong> {{ $transaksi->pengiriman->kurir }} - {{ $transaksi->pengiriman->layanan }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Berat:</strong> {{ $transaksi->pengiriman->weight }} gram
                                        </p>
                                        <p class="mb-0">
                                            <strong>Biaya Pengiriman:</strong> 
                                            @if($transaksi->pengiriman->biaya > 0)
                                                <span class="text-gelap font-weight-bold">Rp {{ number_format($transaksi->pengiriman->biaya, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-success font-weight-bold">GRATIS</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong>Status Pengiriman:</strong> 
                                            @php
                                                $statusBadge = [
                                                    'menunggu_pembayaran' => 'warning',
                                                    'diproses' => 'info',
                                                    'dikirim' => 'primary',
                                                    'selesai' => 'success',
                                                    'dibatalkan' => 'danger'
                                                ];
                                                $shippingStatus = $transaksi->pengiriman->status ?? 'menunggu_pembayaran';
                                            @endphp
                                            <span class="badge bg-{{ $statusBadge[$shippingStatus] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $shippingStatus)) }}
                                            </span>
                                        </p>
                                        @if($transaksi->pengiriman->resi)
                                        <p class="mb-0">
                                            <strong>No. Resi:</strong> 
                                            <span class="font-weight-bold text-primary">{{ $transaksi->pengiriman->resi }}</span>
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="card-cart shadow-sm mb-4">
                    <div class="card-header" style="background-color: #cc8400;">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-calculator me-2"></i>Ringkasan Pembayaran
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $subtotal = $transaksi->detailTransaksi->sum('subtotal');
                            $shippingCost = $transaksi->pengiriman ? $transaksi->pengiriman->biaya : 0;
                            $tax = $transaksi->total_harga - $subtotal - $shippingCost;
                        @endphp

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal Produk:</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
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
                        <div class="d-flex justify-content-between mb-2">
                            <span>Pajak:</span>
                            <span>Rp {{ number_format($tax, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong class="h5 text-gelap">Total Pembayaran:</strong>
                            <strong class="h5 text-gelap">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</strong>
                        </div>

                        @if($transaksi->pembayaran)
                        <div class="pt-3 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Metode Pembayaran:</span>
                                <span class="font-weight-bold">{{ $transaksi->pembayaran->metode }}</span>
                            </div>
                            @if($transaksi->pembayaran->payment_code)
                            <div class="d-flex justify-content-between">
                                <span>Kode Pembayaran:</span>
                                <span class="font-weight-bold text-primary">{{ $transaksi->pembayaran->payment_code }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mb-4">
                    @if($paymentStatus === 'pending')
                        <a href="{{ route('frontend.confirmation.show', $transaksi->id) }}" class="btn btn-warning btn-lg me-3">
                            <i class="fas fa-credit-card me-2"></i>Lanjutkan Pembayaran
                        </a>
                    @endif
                    
                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-lg me-3">
                        <i class="fab fa-whatsapp me-2"></i>Hubungi Customer Service
                    </a>
                    
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Cetak Detail
                    </button>
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

.order-id-badge {
    transition: all 0.3s ease;
}

.order-id-badge:hover {
    background-color: #cc8400 !important;
    transform: scale(1.05);
}

.alert {
    border-radius: 12px;
}

.card-cart {
    border-radius: 12px;
    border: none;
}

.card-cart .card-header {
    border-radius: 12px 12px 0 0;
    font-weight: 600;
}

.card-cart .card-body {
    border-radius: 0 0 12px 12px;
}

.badge {
    font-size: 0.8em;
    padding: 0.5em 0.75em;
}

@media print {
    .btn, .alert .btn-close, .breadcrumb, .fab, .fas:not(.fa-receipt):not(.fa-calculator) {
        display: none !important;
    }
    
    .card-cart {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}

@media (max-width: 768px) {
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .me-3 {
        margin-right: 0.5rem !important;
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Copy order ID to clipboard
    $('.order-id-badge').click(function() {
        const text = $(this).text();
        navigator.clipboard.writeText(text).then(function() {
            // Show temporary feedback
            const badge = $('.order-id-badge');
            const originalText = badge.text();
            const originalBg = badge.css('background-color');
            
            badge.text('Tersalin!').css('background-color', '#28a745');
            
            setTimeout(function() {
                badge.text(originalText).css('background-color', originalBg);
            }, 2000);
        }).catch(function() {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            // Show feedback
            const badge = $('.order-id-badge');
            const originalText = badge.text();
            badge.text('Tersalin!');
            setTimeout(() => badge.text(originalText), 2000);
        });
    });
});
</script>
@endpush

@endsection
