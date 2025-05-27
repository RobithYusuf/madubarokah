@extends('layouts.frontend')

@section('title', 'Konfirmasi Pembayaran')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-check-circle mr-2"></i>Pesanan Berhasil Dibuat!
                </h5>
                <p class="mb-0">Terima kasih atas pesanan Anda. Silakan lakukan pembayaran untuk memproses pesanan.</p>
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
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="card shadow mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card mr-2"></i>Petunjuk Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-clock mr-2"></i>Batas Waktu Pembayaran</h6>
                        <p class="mb-0">Silakan lakukan pembayaran sebelum <strong>{{ $transaksi->expired_time->format('d F Y, H:i') }} WIB</strong></p>
                    </div>

                    <h6>Metode Pembayaran:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-primary">
                                    <i class="fas fa-university mr-2"></i>Transfer Bank
                                </h6>
                                <p class="small mb-2">
                                    <strong>Bank BCA</strong><br>
                                    No. Rekening: 1234567890<br>
                                    A.n: {{ config('shop.name') }}
                                </p>
                                <p class="small mb-2">
                                    <strong>Bank Mandiri</strong><br>
                                    No. Rekening: 9876543210<br>
                                    A.n: {{ config('shop.name') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-success">
                                    <i class="fas fa-wallet mr-2"></i>E-Wallet
                                </h6>
                                <p class="small mb-2">
                                    <strong>OVO / DANA / GoPay</strong><br>
                                    No. HP: {{ config('shop.phone') }}<br>
                                    A.n: {{ config('shop.name') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle mr-2"></i>Penting!</h6>
                        <ul class="mb-0 small">
                            <li>Transfer sesuai dengan jumlah total pembayaran</li>
                            <li>Simpan bukti transfer untuk konfirmasi</li>
                            <li>Hubungi kami via WhatsApp untuk konfirmasi pembayaran</li>
                            <li>Pesanan akan diproses setelah pembayaran dikonfirmasi</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mt-4">
                <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-lg mr-3">
                    <i class="fab fa-whatsapp mr-2"></i>Konfirmasi via WhatsApp
                </a>
                <a href="{{ route('frontend.history.index') }}" class="btn btn-primary mr-3">
                    <i class="fas fa-history mr-2"></i>Lihat Riwayat Transaksi
                </a>
                <a href="{{ route('frontend.home') }}" class="btn btn-outline-primary">
                    <i class="fas fa-home mr-2"></i>Kembali ke Beranda
                </a>
            </div>

            <!-- Save Order Info -->
            <div class="text-center mt-3">
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="fas fa-print mr-2"></i>Cetak Detail Pesanan
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
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-hide success alert after 10 seconds
    setTimeout(function() {
        $('.alert-success').fadeOut(500);
    }, 10000);

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
