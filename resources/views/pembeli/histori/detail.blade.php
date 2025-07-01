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

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-gelap mb-1">Detail Transaksi</h2>
                <p class="text-muted mb-0">{{ $transaksi->merchant_ref }}</p>
            </div>
            <a href="{{ route('frontend.history.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Riwayat
            </a>
        </div>

        <!-- Status Alert -->
        @php
            $paymentStatus = $transaksi->pembayaran ? $transaksi->pembayaran->status : 'pending';
            $alertClass = [
                'pending' => 'warning',
                'berhasil' => 'success',
                'dibayar' => 'success',
                'gagal' => 'danger',
                'expired' => 'secondary',
                'canceled' => 'secondary'
            ];
            $alertIcon = [
                'pending' => 'clock',
                'berhasil' => 'check-circle',
                'dibayar' => 'check-circle',
                'gagal' => 'times-circle',
                'expired' => 'hourglass-end',
                'canceled' => 'ban'
            ];
        @endphp
        
       
        <div class="row">
            <!-- Left Column - Order Details -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <!-- Order Details Card -->
                <div class="card-cart shadow-sm mb-4">
                    <div class="card-header bg-gelap">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-receipt mr-2"></i>Detail Pesanan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label small fw-bold text-muted">ID Pesanan</label>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary p-2 mr-2 order-id-badge" style="cursor: pointer;" title="Klik untuk menyalin">{{ $transaksi->merchant_ref }}</span>
                                    <small class="text-muted">Klik untuk menyalin</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Tanggal Pesanan</label>
                                <div>{{ $transaksi->tanggal_transaksi->format('d F Y, H:i') }} WIB</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label small fw-bold text-muted">Nama Penerima</label>
                                <div>{{ $transaksi->nama_penerima }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">No. Telepon</label>
                                <div>{{ $transaksi->telepon_penerima }}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Alamat Pengiriman</label>
                            <div>{{ $transaksi->alamat_pengiriman }}</div>
                        </div>

                        @if($transaksi->catatan)
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Catatan</label>
                            <div class="fst-italic text-muted">{{ $transaksi->catatan }}</div>
                        </div>
                        @endif

                        <!-- Order Items -->
                        <h6 class="mb-3 text-gelap fw-bold border-bottom pb-2">Item Pesanan</h6>
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
                                                     class="mr-2 shadow-sm"
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
                                        <td class="text-end fw-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Shipping Info -->
                @if($transaksi->pengiriman)
                <div class="card-cart shadow-sm mb-4">
                    <div class="card-header bg-primary">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-truck mr-2"></i>Informasi Pengiriman
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Kurir & Layanan</label>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-light text-dark mr-2 p-2">
                                            {{ $transaksi->pengiriman->kurir }}
                                        </span>
                                        <span>{{ $transaksi->pengiriman->layanan }}</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Berat</label>
                                    <div>{{ $transaksi->pengiriman->weight }} gram</div>
                                </div>
                                
                                <div>
                                    <label class="form-label small fw-bold text-muted">Biaya Pengiriman</label>
                                    <div class="h6">
                                        @if($transaksi->pengiriman->biaya > 0)
                                            <span class="text-gelap fw-bold">Rp {{ number_format($transaksi->pengiriman->biaya, 0, ',', '.') }}</span>
                                        @else
                                            <span class="badge bg-success p-2">GRATIS</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Status Pengiriman</label>
                                    <div>
                                        @php
                                            $statusBadge = [
                                                'menunggu_pembayaran' => 'warning',
                                                'diproses' => 'info',
                                                'dikirim' => 'primary',
                                                'diterima' => 'success',
                                                'dibatalkan' => 'danger'
                                            ];
                                            $shippingStatus = $transaksi->pengiriman->status ?? 'menunggu_pembayaran';
                                        @endphp
                                        <span class="badge bg-{{ $statusBadge[$shippingStatus] ?? 'secondary' }} p-2">
                                            <i class="fas fa-shipping-fast me-1"></i>{{ ucfirst(str_replace('_', ' ', $shippingStatus)) }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if($transaksi->pengiriman->resi)
                                <div>
                                    <label class="form-label small fw-bold text-muted">Nomor Resi</label>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info text-white p-2 mr-2 resi-badge" style="cursor: pointer;" title="Klik untuk menyalin">
                                            {{ $transaksi->pengiriman->resi }}
                                        </span>
                                        <small class="text-muted">Klik untuk menyalin</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column - Payment Summary -->
            <div class="col-lg-5">
                <!-- Payment Summary -->
                <div class="card-cart shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header" style="background-color: #cc8400;">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-calculator mr-2"></i>Ringkasan Pembayaran
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
                                    <span class="text-success fw-bold">GRATIS</span>
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
                            <strong class="h5 text-gelap">Total:</strong>
                            <strong class="h5 text-gelap">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</strong>
                        </div>

                        @if($transaksi->pembayaran)
                        <div class="pt-3 border-top">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Metode Pembayaran:</span>
                                <span class="badge bg-light text-dark p-2">{{ $transaksi->pembayaran->metode }}</span>
                            </div>
                            
                            @if($transaksi->pembayaran->payment_code)
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Kode Pembayaran:</span>
                                <span class="badge bg-light text-dark p-2 payment-code-badge" style="cursor: pointer;" title="Klik untuk menyalin">
                                    {{ $transaksi->pembayaran->payment_code }}
                                </span>
                            </div>
                            @endif
                            
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Status Pembayaran:</span>
                                <span class="badge bg-{{ $alertClass[$paymentStatus] ?? 'secondary' }} p-2">
                                    {{ ucfirst($paymentStatus) }}
                                </span>
                            </div>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="mt-4">
                            @if($paymentStatus === 'pending')
                                <a href="{{ route('frontend.confirmation.show', $transaksi->id) }}" class="btn btn-warning w-100 mb-3">
                                    <i class="fas fa-credit-card mr-2"></i>Lanjutkan Pembayaran
                                </a>
                            @endif
                            
                            @if($transaksi->status === 'dikirim')
                                <form action="{{ route('frontend.history.confirmReceipt', $transaksi->id) }}" method="POST" id="confirmReceiptForm">
                                    @csrf
                                    <button type="button" class="btn btn-primary w-100 mb-3" onclick="confirmReceipt()">
                                        <i class="fas fa-check-circle mr-2"></i>Terima Pesanan
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success w-100 mb-3">
                                <i class="fab fa-whatsapp mr-2"></i>Hubungi Customer Service
                            </a>
                            
                            <button class="btn btn-outline-secondary w-100" onclick="window.print()">
                                <i class="fas fa-print mr-2"></i>Cetak Detail
                            </button>
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

.order-id-badge, .resi-badge, .payment-code-badge {
    transition: all 0.3s ease;
    font-family: monospace;
    letter-spacing: 0.5px;
}

.order-id-badge:hover, .resi-badge:hover, .payment-code-badge:hover {
    background-color: #cc8400 !important;
    color: white !important;
    transform: scale(1.05);
}

.alert {
    border-radius: 12px;
}

.card-cart {
    border-radius: 12px;
    border: none;
    overflow: hidden;
}

.card-cart .card-header {
    border-radius: 12px 12px 0 0;
    font-weight: 600;
    padding: 0.75rem 1.25rem;
}

.card-cart .card-body {
    border-radius: 0 0 12px 12px;
    padding: 1.5rem;
}

.badge {
    font-size: 0.8em;
    padding: 0.5em 0.75em;
    border-radius: 6px;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
    padding: 0.6rem 1.2rem;
    transition: all 0.3s ease;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
}

.fw-bold {
    font-weight: 600 !important;
}

.small {
    font-size: 0.875rem;
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

    .sticky-top {
        position: static !important;
    }
}

@media (max-width: 992px) {
    .sticky-top {
        position: static;
    }
}

@media (max-width: 768px) {
    .card-cart .card-body {
        padding: 1rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Copy order ID to clipboard
    $('.order-id-badge').click(function() {
        copyToClipboard($(this), 'ID Pesanan');
    });
    
    // Copy resi to clipboard
    $('.resi-badge').click(function() {
        copyToClipboard($(this), 'Nomor Resi');
    });
    
    // Copy payment code to clipboard
    $('.payment-code-badge').click(function() {
        copyToClipboard($(this), 'Kode Pembayaran');
    });
    
    function copyToClipboard(element, label) {
        const text = element.text().trim();
        navigator.clipboard.writeText(text).then(function() {
            // Show temporary feedback
            const originalText = element.text();
            const originalBg = element.css('background-color');
            const originalColor = element.css('color');
            
            element.text('Tersalin!').css({
                'background-color': '#28a745',
                'color': 'white'
            });
            
            setTimeout(function() {
                element.text(originalText).css({
                    'background-color': originalBg,
                    'color': originalColor
                });
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
            const originalText = element.text();
            element.text('Tersalin!');
            setTimeout(() => element.text(originalText), 2000);
        });
    }
});

// Confirm receipt function
function confirmReceipt() {
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
            document.getElementById('confirmReceiptForm').submit();
        }
    });
}
</script>
@endpush

@endsection