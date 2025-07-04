@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Pesanan</h1>
    <a href="{{ route('admin.pesanan.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    {{-- Informasi Utama --}}
    <div class="col-lg-8">
        {{-- Status Timeline --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status Pesanan</h6>
            </div>
            <div class="card-body">
                @php
                $transactionStatus = $pesanan->status;
                $paymentStatus = $pesanan->pembayaran->status ?? 'pending';
                $shippingStatus = $pesanan->pengiriman->status ?? 'menunggu_pembayaran';
                
                // Determine unified status
                $currentStep = 1;
                if (in_array($transactionStatus, ['batal', 'gagal', 'expired'])) {
                    $isCancelled = true;
                } else {
                    $isCancelled = false;
                    if ($transactionStatus === 'pending') $currentStep = 1;
                    elseif (in_array($transactionStatus, ['dibayar', 'berhasil'])) $currentStep = 2;
                    elseif ($transactionStatus === 'dikirim') $currentStep = 3;
                    elseif ($transactionStatus === 'selesai') $currentStep = 4;
                }
                @endphp
                
                @if($isCancelled)
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> Pesanan ini telah dibatalkan
                    @if($pesanan->catatan && str_contains($pesanan->catatan, 'PEMBATALAN:'))
                        @php
                        $cancellationInfo = null;
                        if (preg_match('/PEMBATALAN: (.+)/', $pesanan->catatan, $matches)) {
                            $cancellationInfo = json_decode($matches[1], true);
                        }
                        @endphp
                        @if($cancellationInfo)
                        <hr>
                        <small>
                            <strong>Alasan:</strong> {{ str_replace('_', ' ', ucfirst($cancellationInfo['reason'] ?? 'Tidak disebutkan')) }}<br>
                            @if(!empty($cancellationInfo['note']))
                            <strong>Catatan:</strong> {{ $cancellationInfo['note'] }}<br>
                            @endif
                            <strong>Dibatalkan oleh:</strong> {{ $cancellationInfo['cancelled_by'] ?? 'System' }}<br>
                            <strong>Waktu:</strong> {{ $cancellationInfo['cancelled_at'] ?? '-' }}
                        </small>
                        @endif
                    @endif
                </div>
                @else
                <div class="timeline-horizontal">
                    <div class="timeline-item {{ $currentStep >= 1 ? 'active' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Pesanan Dibuat</strong><br>
                            <small>{{ $pesanan->tanggal_transaksi->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    
                    <div class="timeline-item {{ $currentStep >= 2 ? 'active' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Pembayaran Terverifikasi</strong><br>
                            <small>{{ $pesanan->pembayaran && $pesanan->pembayaran->waktu_bayar ? $pesanan->pembayaran->waktu_bayar->format('d/m/Y H:i') : 'Menunggu' }}</small>
                        </div>
                    </div>
                    
                    <div class="timeline-item {{ $currentStep >= 3 ? 'active' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Sedang Dikirim</strong><br>
                            <small>{{ $pesanan->pengiriman && $pesanan->pengiriman->resi ? 'Resi: ' . $pesanan->pengiriman->resi : 'Belum dikirim' }}</small>
                        </div>
                    </div>
                    
                    <div class="timeline-item {{ $currentStep >= 4 ? 'active' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Selesai</strong><br>
                            <small>{{ $currentStep >= 4 ? 'Pesanan selesai' : 'Menunggu' }}</small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Detail Produk --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Produk</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Produk</th>
                                <th width="15%">Harga</th>
                                <th width="10%">Qty</th>
                                <th width="20%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanan->detailTransaksi as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($detail->produk && $detail->produk->gambar)
                                        <img src="{{ asset('storage/' . $detail->produk->gambar) }}" 
                                             alt="{{ $detail->produk->nama_produk }}" 
                                             class="img-thumbnail me-2" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                        <div class="img-thumbnail me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; background: #f0f0f0;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <strong>{{ $detail->produk->nama_produk ?? 'Produk tidak ditemukan' }}</strong>
                                            @if($detail->produk && $detail->produk->kategori)
                                            <br>
                                            <span class="badge text-white" style="background-color: {{ $detail->produk->kategori->warna ?? '#6C757D' }}">
                                                {{ $detail->produk->kategori->nama_kategori }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td>{{ $detail->jumlah }}</td>
                                <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal Produk:</strong></td>
                                <td><strong>Rp {{ number_format($pesanan->detailTransaksi->sum('subtotal'), 0, ',', '.') }}</strong></td>
                            </tr>
                            @if($pesanan->pengiriman)
                            <tr>
                                <td colspan="4" class="text-end">
                                    Ongkos Kirim ({{ $pesanan->pengiriman->kurir }} - {{ $pesanan->pengiriman->layanan }}):
                                </td>
                                <td>Rp {{ number_format($pesanan->pengiriman->biaya ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="bg-light">
                                <td colspan="4" class="text-end"><strong>Total Pembayaran:</strong></td>
                                <td><strong>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Informasi Pengiriman --}}
        @if($pesanan->pengiriman)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Pengiriman</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Alamat Pengiriman</h6>
                        <p>
                            <strong>{{ $pesanan->nama_penerima }}</strong><br>
                            {{ $pesanan->telepon_penerima }}<br>
                            {{ $pesanan->alamat_pengiriman }}<br>
                            @if($pesanan->pengiriman->destination_city_id)
                                @php
                                $cityInfo = \App\Models\ShippingArea::where('rajaongkir_id', $pesanan->pengiriman->destination_city_id)->first();
                                @endphp
                                {{ $cityInfo ? $cityInfo->city_name . ', ' . $cityInfo->province_name : '' }}
                            @endif
                        </p>
                        @if($pesanan->catatan && !str_contains($pesanan->catatan, 'PEMBATALAN:'))
                        <p><strong>Catatan:</strong> {{ $pesanan->catatan }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Detail Pengiriman</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Kurir</td>
                                <td>: {{ $pesanan->pengiriman->kurir }}</td>
                            </tr>
                            <tr>
                                <td>Layanan</td>
                                <td>: {{ $pesanan->pengiriman->layanan }}</td>
                            </tr>
                            <tr>
                                <td>Estimasi</td>
                                <td>: {{ $pesanan->pengiriman->etd }} hari</td>
                            </tr>
                            <tr>
                                <td>Berat</td>
                                <td>: {{ number_format($pesanan->pengiriman->weight / 1000, 1) }} kg</td>
                            </tr>
                            <tr>
                                <td>No. Resi</td>
                                <td>: {{ $pesanan->pengiriman->resi ?? 'Belum ada' }}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>: 
                                    @php
                                    $shippingStatusMap = [
                                        'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'color' => 'warning'],
                                        'diproses' => ['label' => 'Diproses', 'color' => 'info'],
                                        'dikirim' => ['label' => 'Dikirim', 'color' => 'primary'],
                                        'diterima' => ['label' => 'Diterima', 'color' => 'success'],
                                        'dibatalkan' => ['label' => 'Dibatalkan', 'color' => 'danger']
                                    ];
                                    $shippingInfo = $shippingStatusMap[$pesanan->pengiriman->status] ?? ['label' => $pesanan->pengiriman->status, 'color' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $shippingInfo['color'] }}">{{ $shippingInfo['label'] }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar Informasi --}}
    <div class="col-lg-4">
        {{-- Informasi Pesanan --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Pesanan</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%">Invoice</td>
                        <td>: <strong>{{ $pesanan->merchant_ref }}</strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: {{ $pesanan->tanggal_transaksi->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: 
                            @php
                            $statusMap = [
                                'pending' => ['label' => 'Menunggu Pembayaran', 'color' => 'warning'],
                                'dibayar' => ['label' => 'Dibayar', 'color' => 'info'],
                                'berhasil' => ['label' => 'Diproses', 'color' => 'info'],
                                'dikirim' => ['label' => 'Dikirim', 'color' => 'primary'],
                                'selesai' => ['label' => 'Selesai', 'color' => 'success'],
                                'batal' => ['label' => 'Dibatalkan', 'color' => 'danger'],
                                'gagal' => ['label' => 'Gagal', 'color' => 'danger'],
                                'expired' => ['label' => 'Expired', 'color' => 'danger']
                            ];
                            $statusInfo = $statusMap[$pesanan->status] ?? ['label' => $pesanan->status, 'color' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $statusInfo['color'] }}">{{ $statusInfo['label'] }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Informasi Pelanggan --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Pelanggan</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="30%">Nama</td>
                        <td>: {{ $pesanan->user->nama }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>: {{ $pesanan->user->email }}</td>
                    </tr>
                    <tr>
                        <td>No. HP</td>
                        <td>: {{ $pesanan->user->nohp ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Informasi Pembayaran --}}
        @if($pesanan->pembayaran)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Pembayaran</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%">Metode</td>
                        <td>: {{ $pesanan->pembayaran->metode }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: 
                            @php
                            $paymentStatusMap = [
                                'pending' => ['label' => 'Menunggu', 'color' => 'warning'],
                                'berhasil' => ['label' => 'Berhasil', 'color' => 'success'],
                                'dibayar' => ['label' => 'Dibayar', 'color' => 'success'],
                                'gagal' => ['label' => 'Gagal', 'color' => 'danger'],
                                'expired' => ['label' => 'Expired', 'color' => 'danger'],
                                'canceled' => ['label' => 'Dibatalkan', 'color' => 'danger'],
                                'refund' => ['label' => 'Refund', 'color' => 'info']
                            ];
                            $paymentInfo = $paymentStatusMap[$pesanan->pembayaran->status] ?? ['label' => $pesanan->pembayaran->status, 'color' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $paymentInfo['color'] }}">{{ $paymentInfo['label'] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td>: <strong>Rp {{ number_format($pesanan->pembayaran->total_bayar, 0, ',', '.') }}</strong></td>
                    </tr>
                    @if($pesanan->pembayaran->waktu_bayar)
                    <tr>
                        <td>Waktu Bayar</td>
                        <td>: {{ $pesanan->pembayaran->waktu_bayar->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                    @if($pesanan->pembayaran->payment_code)
                    <tr>
                        <td>Kode/VA</td>
                        <td>: {{ $pesanan->pembayaran->payment_code }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif

        {{-- Tombol Aksi --}}
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Aksi</h6>
            </div>
            <div class="card-body">
                @if(!$isCancelled)
                    @if($currentStep == 1)
                        <button class="btn btn-success btn-block mb-2 action-button" 
                                data-action="process_payment" 
                                data-id="{{ $pesanan->id }}">
                            <i class="fa fa-check"></i> Konfirmasi Pembayaran
                        </button>
                        <button class="btn btn-danger btn-block action-button" 
                                data-action="cancel_order" 
                                data-id="{{ $pesanan->id }}">
                            <i class="fa fa-times"></i> Batalkan Pesanan
                        </button>
                    @elseif($currentStep == 2)
                        @if(!$pesanan->pengiriman || !$pesanan->pengiriman->resi)
                            <button class="btn btn-primary btn-block mb-2" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalInputResi">
                                <i class="fa fa-truck"></i> Input Resi & Kirim
                            </button>
                        @else
                            <button class="btn btn-primary btn-block mb-2 action-button" 
                                    data-action="ship_order" 
                                    data-id="{{ $pesanan->id }}"
                                    data-resi="{{ $pesanan->pengiriman->resi }}">
                                <i class="fa fa-truck"></i> Konfirmasi Kirim
                            </button>
                        @endif
                        <button class="btn btn-warning btn-block" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalCancelOrder">
                            <i class="fa fa-times-circle"></i> Batalkan Pesanan
                        </button>
                    @elseif($currentStep == 3)
                        <button class="btn btn-success btn-block mb-2 action-button" 
                                data-action="complete_order" 
                                data-id="{{ $pesanan->id }}">
                            <i class="fa fa-check-circle"></i> Selesaikan Pesanan
                        </button>
                        @if($pesanan->pengiriman && $pesanan->pengiriman->resi)
                        <button class="btn btn-info btn-block copy-resi" 
                                data-resi="{{ $pesanan->pengiriman->resi }}">
                            <i class="fa fa-copy"></i> Salin Nomor Resi
                        </button>
                        @endif
                    @elseif($currentStep == 4)
                        <div class="alert alert-success mb-0">
                            <i class="fa fa-check-circle"></i> Transaksi Selesai
                        </div>
                    @endif
                @else
                    <div class="alert alert-danger mb-0">
                        <i class="fa fa-times-circle"></i> Pesanan Dibatalkan
                    </div>
                @endif
                
                <hr>
                <a href="{{ route('admin.pesanan.index') }}" class="btn btn-secondary btn-block">
                    <i class="fa fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Modal Input Resi --}}
@if($pesanan->pengiriman && !$pesanan->pengiriman->resi && in_array($pesanan->status, ['dibayar', 'berhasil']))
<div class="modal fade" id="modalInputResi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Input Nomor Resi</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formInputResi">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Invoice</label>
                        <input type="text" class="form-control" value="{{ $pesanan->merchant_ref }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kurir</label>
                        <input type="text" class="form-control" value="{{ $pesanan->pengiriman->kurir }} - {{ $pesanan->pengiriman->layanan }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Resi <span class="text-danger">*</span></label>
                        <input type="text" name="resi" class="form-control" placeholder="Masukkan nomor resi" required>
                        <small class="text-muted">Pastikan nomor resi sudah benar sebelum mengirim</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-truck"></i> Kirim Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal Cancel Order untuk pesanan yang sudah dibayar --}}
@if(in_array($pesanan->status, ['dibayar', 'berhasil']))
<div class="modal fade" id="modalCancelOrder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Batalkan Pesanan</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCancelOrder">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Perhatian!</strong><br>
                        Pesanan ini sudah dibayar. Pembatalan akan:
                        <ul class="mb-0 mt-2">
                            <li>Mengembalikan stok produk</li>
                            <li>Memerlukan proses refund ke pelanggan</li>
                            <li>Mengubah status menjadi "Dibatalkan"</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Invoice</label>
                        <input type="text" class="form-control" value="{{ $pesanan->merchant_ref }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Total Pembayaran</label>
                        <input type="text" class="form-control" value="Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <select name="reason" class="form-control" required>
                            <option value="">-- Pilih Alasan --</option>
                            <option value="stok_habis">Stok Habis</option>
                            <option value="produk_rusak">Produk Rusak/Cacat</option>
                            <option value="kesalahan_harga">Kesalahan Harga</option>
                            <option value="permintaan_pelanggan">Permintaan Pelanggan</option>
                            <option value="tidak_bisa_kirim">Tidak Bisa Kirim ke Lokasi</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Jelaskan lebih detail mengenai pembatalan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-times-circle"></i> Batalkan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
.timeline-horizontal {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 20px 0;
    position: relative;
}

.timeline-horizontal::before {
    content: '';
    position: absolute;
    top: 35px;
    left: 50px;
    right: 50px;
    height: 2px;
    background: #e0e0e0;
    z-index: 0;
}

.timeline-item {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
}

.timeline-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 20px;
    transition: all 0.3s;
}

.timeline-item.active .timeline-icon {
    background: #4e73df;
    color: white;
    box-shadow: 0 2px 10px rgba(78, 115, 223, 0.3);
}

.timeline-content {
    font-size: 12px;
}

.timeline-item.active .timeline-content strong {
    color: #4e73df;
}

.img-thumbnail {
    border: 1px solid #dee2e6;
}

.action-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Action button handler
    $('.action-button').on('click', function() {
        const action = $(this).data('action');
        const id = $(this).data('id');
        const resi = $(this).data('resi') || null;
        
        let confirmTitle = '';
        let confirmText = '';
        let confirmButton = '';
        
        switch(action) {
            case 'process_payment':
                confirmTitle = 'Konfirmasi Pembayaran';
                confirmText = 'Apakah Anda yakin pembayaran untuk pesanan ini sudah berhasil?';
                confirmButton = 'Ya, Pembayaran Berhasil';
                break;
            case 'ship_order':
                confirmTitle = 'Konfirmasi Pengiriman';
                confirmText = 'Apakah Anda yakin pesanan ini sudah dikirim dengan resi: ' + resi + '?';
                confirmButton = 'Ya, Sudah Dikirim';
                break;
            case 'complete_order':
                confirmTitle = 'Selesaikan Pesanan';
                confirmText = 'Apakah Anda yakin pesanan ini sudah diterima pelanggan?';
                confirmButton = 'Ya, Selesaikan';
                break;
            case 'cancel_order':
                confirmTitle = 'Batalkan Pesanan';
                confirmText = 'Apakah Anda yakin ingin membatalkan pesanan ini? Stok produk akan dikembalikan.';
                confirmButton = 'Ya, Batalkan';
                break;
        }
        
        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmButton,
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                updateOrderStatus(id, action, resi);
            }
        });
    });

    // Handle form input resi
    $('#formInputResi').on('submit', function(e) {
        e.preventDefault();
        const resi = $(this).find('input[name="resi"]').val();
        
        if (!resi) {
            Swal.fire('Error', 'Nomor resi harus diisi', 'error');
            return;
        }
        
        $('#modalInputResi').modal('hide');
        updateOrderStatus({{ $pesanan->id }}, 'ship_order', resi);
    });

    // Handle form cancel order
    $('#formCancelOrder').on('submit', function(e) {
        e.preventDefault();
        const reason = $(this).find('select[name="reason"]').val();
        const note = $(this).find('textarea[name="note"]').val();
        
        if (!reason) {
            Swal.fire('Error', 'Alasan pembatalan harus dipilih', 'error');
            return;
        }
        
        $('#modalCancelOrder').modal('hide');
        
        Swal.fire({
            title: 'Konfirmasi Pembatalan',
            html: `<div class="text-left">
                <p><strong>Apakah Anda yakin ingin membatalkan pesanan ini?</strong></p>
                <p>Alasan: <strong>${$('select[name="reason"] option:selected').text()}</strong></p>
                ${note ? '<p>Catatan: ' + note + '</p>' : ''}
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-circle"></i> Tindakan ini akan:
                    <ul class="mb-0 mt-2">
                        <li>Mengembalikan stok produk</li>
                        <li>Memerlukan proses refund</li>
                        <li>Tidak dapat dibatalkan</li>
                    </ul>
                </div>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                updateOrderStatusWithReason({{ $pesanan->id }}, 'cancel_order', reason, note);
            }
        });
    });

    // Copy resi functionality
    $('.copy-resi').on('click', function() {
        const resi = $(this).data('resi');
        navigator.clipboard.writeText(resi).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Nomor resi berhasil disalin: ' + resi,
                timer: 2000,
                showConfirmButton: false
            });
        });
    });

    // Function to update order status
    function updateOrderStatus(id, action, resi = null) {
        $.ajax({
            url: `/admin/pesanan/${id}/update-order-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                resi: resi
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMessage
                });
            }
        });
    }

    // Function to update order status with reason
    function updateOrderStatusWithReason(id, action, reason, note) {
        $.ajax({
            url: `/admin/pesanan/${id}/update-order-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                cancellation_reason: reason,
                cancellation_note: note
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Memproses pembatalan...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMessage
                });
            }
        });
    }
});
</script>
@endpush