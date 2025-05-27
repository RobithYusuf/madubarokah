@extends('layouts.frontend')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="container my-5">
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
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Order Info -->
                            <div class="col-md-3">
                                <h6 class="mb-1 text-primary">{{ $t->merchant_ref }}</h6>
                                <small class="text-muted">{{ $t->tanggal_transaksi->format('d M Y, H:i') }}</small>
                            </div>

                            <!-- Items Preview -->
                            <div class="col-md-4">
                                @php
                                    $totalItems = $t->detailTransaksi->sum('jumlah');
                                    $firstItem = $t->detailTransaksi->first();
                                @endphp
                                <div class="d-flex align-items-center">
                                    @if($firstItem && $firstItem->produk && $firstItem->produk->gambar)
                                        <img src="{{ asset('storage/' . $firstItem->produk->gambar) }}" 
                                             alt="{{ $firstItem->produk->nama_produk }}" 
                                             class="img-thumbnail mr-2" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <div class="font-weight-medium">
                                            {{ $firstItem && $firstItem->produk ? $firstItem->produk->nama_produk : 'Produk tidak tersedia' }}
                                            @if($t->detailTransaksi->count() > 1)
                                                <small class="text-muted">+{{ $t->detailTransaksi->count() - 1 }} lainnya</small>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $totalItems }} item</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Total & Status -->
                            <div class="col-md-3">
                                <div class="text-right">
                                    <div class="h6 mb-1">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</div>
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
                                        <span class="badge badge-{{ $statusClass[$paymentStatus] ?? 'secondary' }}">
                                            {{ ucfirst($paymentStatus) }}
                                        </span>
                                    </div>
                                    @if($t->pengiriman)
                                        <small class="text-muted">{{ $t->pengiriman->kurir }} - {{ $t->pengiriman->layanan }}</small>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="col-md-2">
                                <div class="text-right">
                                    <a href="{{ route('frontend.history.detail', $t->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye mr-1"></i>Detail
                                    </a>
                                    @if($paymentStatus === 'pending')
                                        <a href="{{ route('frontend.checkout.confirmation', $t->id) }}" class="btn btn-warning btn-sm mt-1">
                                            <i class="fas fa-credit-card mr-1"></i>Bayar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $transaksi->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-shopping-bag fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Belum Ada Transaksi</h4>
                    <p class="text-muted mb-4">Anda belum melakukan pembelian apapun. Mulai berbelanja sekarang!</p>
                    <a href="{{ route('frontend.home') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-cart mr-2"></i>Mulai Belanja
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.75em;
    padding: 0.5em 0.75em;
}

.img-thumbnail {
    border-radius: 8px;
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
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
        
        // Let the page navigate normally, but show loading state briefly
        setTimeout(() => {
            btn.prop('disabled', false).html(originalText);
        }, 1000);
    });
});
</script>
@endpush
@endsection
