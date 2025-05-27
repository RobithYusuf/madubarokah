@extends('layouts.frontend')

@section('title', 'Detail Transaksi - ' . $transaksi->merchant_ref)

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
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
            
            <div class="alert alert-{{ $alertClass[$paymentStatus] ?? 'secondary' }} alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-{{ $alertIcon[$paymentStatus] ?? 'info-circle' }} mr-2"></i>Status: {{ ucfirst($paymentStatus) }}
                </h5>
                @if($paymentStatus === 'pending')
                    <p class="mb-0">Transaksi menunggu pembayaran. Silakan lakukan pembayaran untuk memproses pesanan.</p>
                @elseif($paymentStatus === 'berhasil')
                    <p class="mb-0">Pembayaran berhasil! Pesanan Anda sedang diproses.</p>
                @elseif($paymentStatus === 'gagal')
                    <p class="mb-0">Pembayaran gagal. Silakan hubungi customer service untuk bantuan.</p>
                @elseif($paymentStatus === 'expired')
                    <p class="mb-0">Transaksi telah kedaluwarsa. Silakan buat pesanan baru.</p>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Order Details Card -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt mr-2"></i>Detail Pesanan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>ID Pesanan:</strong><br>
                            <span class="badge badge-secondary p-2">{{ $transaksi->merchant_ref }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Tanggal Pesanan:</strong><br>
                            {{ $transaksi->tanggal_transaksi->format('d F Y, H:i') }} WIB
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Nama Penerima:</strong><br>
                            {{ $transaksi->nama_penerima }}
                        </div>
                        <div class="col-md-6">
                            <strong>No. Telepon:</strong><br>
                            {{ $transaksi->telepon_penerima }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <strong>Alamat Pengiriman:</strong><br>
                        {{ $transaksi->alamat_pengiriman }}
                    </div>

                    @if($transaksi->catatan)
                    <div class="mb-4">
                        <strong>Catatan:</strong><br>
                        <em>{{ $transaksi->catatan }}</em>
                    </div>
                    @endif

                    <!-- Order Items -->
                    <h6 class="mb-3">Item Pesanan:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Subtotal</th>
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
                                                 class="img-thumbnail mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            @endif
                                            <span>{{ $detail->produk ? $detail->produk->nama_produk : 'Produk tidak ditemukan' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $detail->jumlah }}</td>
                                    <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Shipping Info -->
                    @if($transaksi->pengiriman)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6>Informasi Pengiriman:</h6>
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
                                            Rp {{ number_format($transaksi->pengiriman->biaya, 0, ',', '.') }}
                                        @else
                                            <span class="text-success">GRATIS</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Status Pengiriman:</strong> 
                                        <span class="badge badge-{{ $transaksi->pengiriman->status_badge }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaksi->pengiriman->status)) }}
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
            <div class="card shadow mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator mr-2"></i>Ringkasan Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $subtotal = $transaksi->detailTransaksi->sum('subtotal');
                        $shippingCost = $transaksi->pengiriman ? $transaksi->pengiriman->biaya : 0;
                        $tax = $transaksi->total_harga - $subtotal - $shippingCost;
                    @endphp

                    <div class="d-flex justify-content-between">
                        <span>Subtotal Produk:</span>
                        <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <span>Ongkos Kirim:</span>
                        <span>
                            @if($shippingCost > 0)
                                Rp {{ number_format($shippingCost, 0, ',', '.') }}
                            @else
                                <span class="text-success">GRATIS</span>
                            @endif
                        </span>
                    </div>
                    
                    @if($tax > 0)
                    <div class="d-flex justify-content-between">
                        <span>Pajak:</span>
                        <span>Rp {{ number_format($tax, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total Pembayaran:</strong>
                        <strong class="text-success h5">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</strong>
                    </div>

                    @if($transaksi->pembayaran)
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between">
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
            <div class="text-center mt-4">
                @if($paymentStatus === 'pending')
                    <a href="{{ route('frontend.checkout.confirmation', $transaksi->id) }}" class="btn btn-warning btn-lg mr-3">
                        <i class="fas fa-credit-card mr-2"></i>Lanjutkan Pembayaran
                    </a>
                @endif
                
                <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-lg mr-3">
                    <i class="fab fa-whatsapp mr-2"></i>Hubungi Customer Service
                </a>
                
                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print mr-2"></i>Cetak Detail
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, .alert .btn-close {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}

.badge {
    font-size: 0.8em;
    padding: 0.5em 0.75em;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Copy order ID to clipboard
    $('.badge').click(function() {
        const text = $(this).text();
        navigator.clipboard.writeText(text).then(function() {
            // Show temporary tooltip
            const badge = $('.badge');
            const originalText = badge.text();
            badge.text('Tersalin!').addClass('badge-success').removeClass('badge-secondary');
            
            setTimeout(function() {
                badge.text(originalText).addClass('badge-secondary').removeClass('badge-success');
            }, 2000);
        });
    });
});
</script>
@endpush
@endsection
