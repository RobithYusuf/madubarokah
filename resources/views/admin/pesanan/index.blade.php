@extends('layouts.app')

@section('title', 'Manajemen Pesanan')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h4 class="m-0 font-weight-bold text-primary">Manajemen Pesanan</h4>
        <div>
            <!-- Filter Status -->
            <div class="btn-group mr-2" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    Filter Status
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item filter-status" href="#" data-status="all">Semua Status</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="pending">Pending</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="dibayar">Dibayar</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="dikirim">Dikirim</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="selesai">Selesai</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="batal">Batal</a></li>
                </ul>
            </div>
            
            <!-- Tombol untuk generate test data (hanya untuk development) -->
            @if(app()->environment('local'))
            <a href="{{ route('admin.pesanan.create-test') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-plus"></i> Generate Test Data
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div id="alertSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        
        @if (session('error'))
        <div id="alertError" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        
        @if($pesanans && $pesanans->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tabelPesanan">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="12%">Referensi & Pelanggan</th>
                        <th width="10%">Tanggal</th>
                        <th width="10%">Total</th>
                        <th width="8%">Status Transaksi</th>
                        <th width="15%">Status Pembayaran</th>
                        <th width="15%">Status Pengiriman</th>
                        <th width="10%">Resi Tracking</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pesanans as $index => $pesanan)
                    @php
                        // Logika untuk menentukan status yang dapat diubah
                        $transactionStatus = $pesanan->status;
                        $paymentStatus = $pesanan->pembayaran->status ?? 'pending';
                        $shippingStatus = $pesanan->pengiriman->status ?? 'menunggu_pembayaran';
                        
                        // Status final yang tidak bisa diubah
                        $transactionFinalStatuses = ['selesai', 'batal', 'gagal', 'expired'];
                        $paymentFinalStatuses = ['refund', 'gagal', 'expired'];
                        $shippingFinalStatuses = ['diterima', 'dibatalkan'];
                        
                        $isTransactionFinal = in_array($transactionStatus, $transactionFinalStatuses);
                        $isPaymentFinal = in_array($paymentStatus, $paymentFinalStatuses);
                        $isShippingFinal = in_array($shippingStatus, $shippingFinalStatuses);
                        
                        // Status yang dapat dibayar
                        $isPaid = in_array($paymentStatus, ['berhasil', 'dibayar']);
                        
                        // Cek apakah barang bisa dikirim (harus sudah bayar)
                        $canShip = $isPaid && !$isTransactionFinal;
                        
                        // Cek apakah sudah dikirim
                        $isShipped = in_array($shippingStatus, ['dikirim', 'diterima']);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <small class="text-muted">ID: #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</small>
                                @if($pesanan->merchant_ref)
                                <small class="font-weight-bold">{{ $pesanan->merchant_ref }}</small>
                                @endif
                                @if($pesanan->tripay_reference)
                                <small class="text-info">{{ $pesanan->tripay_reference }}</small>
                                @endif
                                <hr class="my-1">
                                <span class="font-weight-bold">{{ $pesanan->user->nama ?? '-' }}</span>
                                @if($pesanan->nama_penerima && $pesanan->nama_penerima !== $pesanan->user->nama)
                                <small class="text-muted">Penerima: {{ $pesanan->nama_penerima }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span>{{ $pesanan->tanggal_transaksi ? $pesanan->tanggal_transaksi->format('d/m/Y') : '-' }}</span>
                                <small class="text-muted">{{ $pesanan->tanggal_transaksi ? $pesanan->tanggal_transaksi->format('H:i') : '' }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                                @if($pesanan->pengiriman && $pesanan->pengiriman->biaya > 0)
                                <small class="text-muted">Ongkir: {{ number_format($pesanan->pengiriman->biaya, 0, ',', '.') }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($isTransactionFinal)
                                <!-- Status final tidak bisa diubah -->
                                <span class="badge bg-{{ $transactionStatus === 'selesai' ? 'success' : ($transactionStatus === 'batal' ? 'warning' : 'danger') }} text-white">
                                    {{ ucfirst($transactionStatus) }}
                                </span>
                            @else
                                <select class="form-control form-control-sm status-dropdown" 
                                        data-type="transaction" 
                                        data-id="{{ $pesanan->id }}"
                                        data-current="{{ $transactionStatus }}"
                                        data-payment-status="{{ $paymentStatus }}"
                                        data-shipping-status="{{ $shippingStatus }}">
                                    @if($transactionStatus === 'pending')
                                        <option value="pending" selected>Pending</option>
                                        <option value="batal">Batal</option>
                                        <option value="gagal">Gagal</option>
                                        <option value="expired">Expired</option>
                                        @if($isPaid)
                                            <option value="dibayar">Dibayar</option>
                                        @endif
                                    @elseif($transactionStatus === 'dibayar')
                                        <option value="dibayar" selected>Dibayar</option>
                                        <option value="berhasil">Berhasil</option>
                                        @if($canShip)
                                            <option value="dikirim">Dikirim</option>
                                        @endif
                                    @elseif($transactionStatus === 'berhasil')
                                        <option value="berhasil" selected>Berhasil</option>
                                        @if($canShip)
                                            <option value="dikirim">Dikirim</option>
                                        @endif
                                    @elseif($transactionStatus === 'dikirim')
                                        <option value="dikirim" selected>Dikirim</option>
                                        @if($isShipped)
                                            <option value="selesai">Selesai</option>
                                        @endif
                                    @endif
                                </select>
                            @endif
                        </td>
                        <td>
                            @if($pesanan->pembayaran)
                            <div class="d-flex flex-column">
                                @if($isPaymentFinal)
                                    <!-- Status pembayaran final -->
                                    <span class="badge bg-{{ $paymentStatus === 'refund' ? 'info' : 'danger' }} text-white">
                                        {{ ucfirst($paymentStatus) }}
                                    </span>
                                @else
                                    <select class="form-control form-control-sm status-dropdown mb-1" 
                                            data-type="payment" 
                                            data-id="{{ $pesanan->id }}"
                                            data-current="{{ $paymentStatus }}"
                                            data-transaction-status="{{ $transactionStatus }}">
                                        @if($paymentStatus === 'pending')
                                            <option value="pending" selected>Pending</option>
                                            <option value="berhasil">Berhasil</option>
                                            <option value="dibayar">Dibayar</option>
                                            <option value="gagal">Gagal</option>
                                            <option value="expired">Expired</option>
                                        @elseif(in_array($paymentStatus, ['berhasil', 'dibayar']))
                                            <option value="{{ $paymentStatus }}" selected>{{ ucfirst($paymentStatus) }}</option>
                                            @if(!$isShipped)
                                                <option value="refund">Refund</option>
                                            @endif
                                        @endif
                                    </select>
                                @endif
                                <small class="text-muted">{{ $pesanan->pembayaran->metode ?? '-' }}</small>
                                @if($pesanan->pembayaran->payment_code)
                                <small class="text-info font-weight-bold">{{ $pesanan->pembayaran->payment_code }}</small>
                                @endif
                                @if($pesanan->pembayaran->waktu_bayar)
                                <small class="text-success">{{ $pesanan->pembayaran->waktu_bayar->format('d/m H:i') }}</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">Tidak ada data pembayaran</span>
                            @endif
                        </td>
                        <td>
                            @if($pesanan->pengiriman)
                            <div class="d-flex flex-column">
                                @if($isShippingFinal)
                                    <!-- Status pengiriman final -->
                                    <span class="badge bg-{{ $shippingStatus === 'diterima' ? 'success' : 'warning' }} text-white">
                                        {{ ucfirst(str_replace('_', ' ', $shippingStatus)) }}
                                    </span>
                                @elseif(!$isPaid)
                                    <!-- Tidak bisa diubah jika belum bayar -->
                                    <span class="badge bg-secondary text-white">
                                        {{ ucfirst(str_replace('_', ' ', $shippingStatus)) }}
                                    </span>
                                    <small class="text-muted">Menunggu pembayaran</small>
                                @else
                                    <select class="form-control form-control-sm status-dropdown mb-1" 
                                            data-type="shipping" 
                                            data-id="{{ $pesanan->id }}"
                                            data-current="{{ $shippingStatus }}"
                                            data-payment-status="{{ $paymentStatus }}">
                                        @if($shippingStatus === 'menunggu_pembayaran' && $isPaid)
                                            <option value="diproses" selected>Diproses</option>
                                            <option value="dibatalkan">Dibatalkan</option>
                                        @elseif($shippingStatus === 'diproses')
                                            <option value="diproses" selected>Diproses</option>
                                            <option value="dikirim">Dikirim</option>
                                            <option value="dibatalkan">Dibatalkan</option>
                                        @elseif($shippingStatus === 'dikirim')
                                            <option value="dikirim" selected>Dikirim</option>
                                            <option value="diterima">Diterima</option>
                                        @else
                                            <option value="{{ $shippingStatus }}" selected>{{ ucfirst(str_replace('_', ' ', $shippingStatus)) }}</option>
                                        @endif
                                    </select>
                                @endif
                                <small class="text-muted">{{ $pesanan->pengiriman->kurir ?? '-' }} - {{ $pesanan->pengiriman->layanan ?? '-' }}</small>
                                @if($pesanan->pengiriman->weight)
                                <small class="text-muted">Berat: {{ $pesanan->pengiriman->weight }}g</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">Tidak ada data pengiriman</span>
                            @endif
                        </td>
                        <td>
                            @if($pesanan->pengiriman)
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control resi-input" 
                                       data-id="{{ $pesanan->id }}"
                                       value="{{ $pesanan->pengiriman->resi ?? '' }}" 
                                       placeholder="No. Resi"
                                       maxlength="50"
                                       {{ !$canShip || $isShippingFinal ? 'readonly' : '' }}>
                                <div class="input-group-append">
                                    @if($pesanan->pengiriman->resi)
                                    <button class="btn btn-outline-info btn-sm copy-resi" 
                                            data-resi="{{ $pesanan->pengiriman->resi }}" 
                                            title="Copy Resi">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    @endif
                                    @if($canShip && !$isShippingFinal)
                                    <button class="btn btn-outline-success btn-sm update-resi" 
                                            data-id="{{ $pesanan->id }}" 
                                            title="Update Resi">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @if(!$isPaid && $pesanan->pengiriman)
                            <small class="text-muted">Menunggu pembayaran</small>
                            @endif
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-info" data-bs-toggle="modal"
                                        data-bs-target="#modalDetail{{ $pesanan->id }}" title="Detail">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @if($pesanan->pengiriman)
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#modalShipping{{ $pesanan->id }}" title="Detail Pengiriman">
                                    <i class="fa fa-truck"></i>
                                </button>
                                @endif
                                @if(!in_array($transactionStatus, ['selesai', 'dikirim']))
                                <button class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#modalHapus{{ $pesanan->id }}" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <h5>Belum Ada Pesanan</h5>
                <p>Pesanan dari pelanggan akan muncul di sini</p>
                @if(app()->environment('local'))
                <a href="{{ route('admin.pesanan.create-test') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Generate Test Data
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Alert untuk validasi status -->
<div id="statusAlert" class="alert alert-warning alert-dismissible fade" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: none;">
    <strong>Peringatan!</strong> <span id="statusAlertMessage"></span>
    <button type="button" class="close" onclick="$('#statusAlert').fadeOut()">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<!-- Modals -->
@if($pesanans && $pesanans->count() > 0)
@foreach ($pesanans as $pesanan)
<!-- Modal Detail Pesanan -->
<div class="modal fade" id="modalDetail{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6><strong>Informasi Pelanggan</strong></h6>
                        <p><strong>Nama:</strong> {{ $pesanan->user->nama ?? '-' }}</p>
                        <p><strong>Username:</strong> {{ $pesanan->user->username ?? '-' }}</p>
                        <p><strong>Email:</strong> {{ $pesanan->user->email ?? '-' }}</p>
                        <p><strong>No. HP:</strong> {{ $pesanan->user->nohp ?? '-' }}</p>
                        <p><strong>Alamat User:</strong> {{ $pesanan->user->alamat ?? '-' }}</p>
                        
                        @if($pesanan->nama_penerima || $pesanan->telepon_penerima || $pesanan->alamat_pengiriman)
                        <hr>
                        <h6><strong>Informasi Penerima</strong></h6>
                        @if($pesanan->nama_penerima)
                        <p><strong>Nama Penerima:</strong> {{ $pesanan->nama_penerima }}</p>
                        @endif
                        @if($pesanan->telepon_penerima)
                        <p><strong>Telepon:</strong> {{ $pesanan->telepon_penerima }}</p>
                        @endif
                        @if($pesanan->alamat_pengiriman)
                        <p><strong>Alamat Pengiriman:</strong> {{ $pesanan->alamat_pengiriman }}</p>
                        @endif
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6><strong>Informasi Pesanan</strong></h6>
                        <p><strong>Tanggal:</strong> {{ $pesanan->tanggal_transaksi ? $pesanan->tanggal_transaksi->format('d/m/Y H:i') : '-' }}</p>
                        <p><strong>Merchant Ref:</strong> {{ $pesanan->merchant_ref ?? '-' }}</p>
                        @if($pesanan->tripay_reference)
                        <p><strong>Tripay Ref:</strong> {{ $pesanan->tripay_reference }}</p>
                        @endif
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $pesanan->status === 'selesai' ? 'success' : ($pesanan->status === 'dikirim' ? 'info' : ($pesanan->status === 'dibayar' ? 'warning' : ($pesanan->status === 'pending' ? 'secondary' : 'danger'))) }} text-white">
                                {{ ucfirst($pesanan->status) }}
                            </span>
                        </p>
                        @if($pesanan->expired_time)
                        <p><strong>Expired:</strong> {{ $pesanan->expired_time->format('d/m/Y H:i') }}</p>
                        @endif
                        @if($pesanan->catatan)
                        <p><strong>Catatan:</strong> {{ $pesanan->catatan }}</p>
                        @endif
                    </div>
                    <div class="col-md-4">
                        @if($pesanan->pembayaran)
                        <h6><strong>Informasi Pembayaran</strong></h6>
                        <p><strong>Metode:</strong> {{ $pesanan->pembayaran->metode ?? '-' }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $pesanan->pembayaran->status === 'berhasil' || $pesanan->pembayaran->status === 'dibayar' ? 'success' : ($pesanan->pembayaran->status === 'pending' ? 'warning' : 'danger') }} text-white">
                                {{ ucfirst($pesanan->pembayaran->status) }}
                            </span>
                        </p>
                        <p><strong>Total Bayar:</strong> Rp {{ number_format($pesanan->pembayaran->total_bayar, 0, ',', '.') }}</p>
                        @if($pesanan->pembayaran->payment_code)
                        <p><strong>Kode Bayar:</strong> {{ $pesanan->pembayaran->payment_code }}</p>
                        @endif
                        @if($pesanan->pembayaran->waktu_bayar)
                        <p><strong>Waktu Bayar:</strong> {{ $pesanan->pembayaran->waktu_bayar->format('d/m/Y H:i') }}</p>
                        @endif
                        @endif
                        
                        @if($pesanan->pengiriman)
                        <hr>
                        <h6><strong>Informasi Pengiriman</strong></h6>
                        <p><strong>Kurir:</strong> {{ $pesanan->pengiriman->kurir ?? '-' }}</p>
                        <p><strong>Layanan:</strong> {{ $pesanan->pengiriman->layanan ?? '-' }}</p>
                        <p><strong>Berat:</strong> {{ $pesanan->pengiriman->weight ?? 0 }}g</p>
                        <p><strong>Biaya:</strong> Rp {{ number_format($pesanan->pengiriman->biaya ?? 0, 0, ',', '.') }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $pesanan->pengiriman->status === 'diterima' ? 'success' : ($pesanan->pengiriman->status === 'dikirim' ? 'info' : ($pesanan->pengiriman->status === 'diproses' ? 'warning' : 'secondary')) }} text-white">
                                {{ ucfirst(str_replace('_', ' ', $pesanan->pengiriman->status)) }}
                            </span>
                        </p>
                        @if($pesanan->pengiriman->resi)
                        <p><strong>No. Resi:</strong> 
                            <span class="font-weight-bold text-success">{{ $pesanan->pengiriman->resi }}</span>
                            <button class="btn btn-sm btn-outline-info ml-2 copy-resi" data-resi="{{ $pesanan->pengiriman->resi }}">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </p>
                        @endif
                        @if($pesanan->pengiriman->etd)
                        <p><strong>Estimasi:</strong> {{ $pesanan->pengiriman->etd }} hari</p>
                        @endif
                        @endif
                    </div>
                </div>
                
                <hr>
                <h6><strong>Detail Produk</strong></h6>
                @if($pesanan->detailTransaksi && $pesanan->detailTransaksi->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanan->detailTransaksi as $detail)
                            <tr>
                                <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                <td>
                                    @if($detail->produk && $detail->produk->kategori)
                                    <span class="badge text-white" 
                                          style="background-color: {{ $detail->produk->kategori->warna ?? '#6C757D' }};">
                                        {{ $detail->produk->kategori->nama_kategori }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $detail->jumlah }}</td>
                                <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Total</th>
                                <th>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-muted">Tidak ada detail produk</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pengiriman -->
@if($pesanan->pengiriman)
<div class="modal fade" id="modalShipping{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengiriman - Pesanan #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Informasi Pengiriman</strong></h6>
                        <p><strong>Kurir:</strong> {{ $pesanan->pengiriman->kurir ?? '-' }}</p>
                        <p><strong>Layanan:</strong> {{ $pesanan->pengiriman->layanan ?? '-' }}</p>
                        <p><strong>Kode Layanan:</strong> {{ $pesanan->pengiriman->service_code ?? '-' }}</p>
                        <p><strong>Berat Total:</strong> {{ $pesanan->pengiriman->weight ?? 0 }} gram</p>
                        <p><strong>Biaya Kirim:</strong> Rp {{ number_format($pesanan->pengiriman->biaya ?? 0, 0, ',', '.') }}</p>
                        <p><strong>Estimasi Pengiriman:</strong> {{ $pesanan->pengiriman->etd ?? '-' }} hari</p>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Status & Tracking</strong></h6>
                        <p><strong>Status Saat Ini:</strong> 
                            <span class="badge bg-{{ $pesanan->pengiriman->status === 'diterima' ? 'success' : ($pesanan->pengiriman->status === 'dikirim' ? 'info' : ($pesanan->pengiriman->status === 'diproses' ? 'warning' : 'secondary')) }} text-white">
                                {{ ucfirst(str_replace('_', ' ', $pesanan->pengiriman->status)) }}
                            </span>
                        </p>
                        @if($pesanan->pengiriman->resi)
                        <p><strong>Nomor Resi:</strong> 
                            <span class="font-weight-bold text-success">{{ $pesanan->pengiriman->resi }}</span>
                            <button class="btn btn-sm btn-outline-info ml-2 copy-resi" data-resi="{{ $pesanan->pengiriman->resi }}">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Cara Melacak Paket:</strong><br>
                            <small>
                                1. Copy nomor resi dengan tombol Copy<br>
                                2. Kunjungi website {{ $pesanan->pengiriman->kurir }} untuk tracking<br>
                                3. Masukkan nomor resi untuk melihat status terkini
                            </small>
                        </div>
                        @else
                        <p><strong>Nomor Resi:</strong> <span class="text-muted">Belum tersedia</span></p>
                        @endif
                        
                        <p><strong>Alamat Tujuan:</strong></p>
                        <p class="text-muted">{{ $pesanan->alamat_pengiriman ?? 'Alamat tidak tersedia' }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal Hapus Pesanan -->
@if(!in_array($pesanan->status, ['selesai', 'dikirim']))
<div class="modal fade" id="modalHapus{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pesanan #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }} ini?</p>
                <div class="alert alert-warning">
                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait pesanan ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('admin.pesanan.destroy', $pesanan->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach
@endif

@push('styles')
<style>
/* Status dropdown styling */
.status-dropdown {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 0.875rem;
    min-width: 120px;
}

.status-dropdown:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.status-dropdown:disabled {
    background-color: #e9ecef;
    opacity: 0.65;
}

/* Readonly input styling */
.resi-input[readonly] {
    background-color: #e9ecef;
    opacity: 1;
}

/* Badge styling untuk status final */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
}

/* Table responsive adjustments */
.table td {
    vertical-align: middle;
    padding: 0.5rem;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}

/* Loading state for dropdowns */
.status-dropdown.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Success/error feedback */
.status-feedback {
    position: absolute;
    top: 0;
    right: 0;
    transform: translateY(-50%);
    z-index: 10;
}

.feedback-success {
    color: #28a745;
    animation: fadeInOut 2s ease-in-out;
}

.feedback-error {
    color: #dc3545;
    animation: fadeInOut 2s ease-in-out;
}

@keyframes fadeInOut {
    0% { opacity: 0; }
    50% { opacity: 1; }
    100% { opacity: 0; }
}

/* Copy success animation */
.copy-success {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

/* Modal improvements */
.modal-xl {
    max-width: 1200px;
}

/* Status alert styling */
#statusAlert {
    min-width: 300px;
    max-width: 500px;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .status-dropdown {
        min-width: 100px;
        font-size: 0.75rem;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($('#tabelPesanan').length && $('#tabelPesanan tbody tr').length > 0) {
        try {
            var table = $('#tabelPesanan').DataTable({
                responsive: true,
                processing: false,
                serverSide: false,
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                lengthMenu: [5, 10, 25, 50],
                pageLength: 10,
                order: [[2, 'desc']], // Sort by date
                columnDefs: [
                    { 
                        targets: [0, 8], // No dan Aksi
                        orderable: false
                    },
                    {
                        targets: [4, 5, 6, 7], // Status columns
                        orderable: false
                    }
                ],
                language: {
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Tidak ada data yang ditemukan",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data tersedia",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "search": "Cari:",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });

            // Filter by status
            $('.filter-status').on('click', function(e) {
                e.preventDefault();
                var status = $(this).data('status');
                
                if (status === 'all') {
                    table.column(4).search('').draw();
                } else {
                    table.column(4).search(status, true, false).draw();
                }
                
                $('.dropdown-toggle').text('Filter: ' + $(this).text());
            });

            console.log('DataTable initialized successfully');
        } catch (error) {
            console.error('DataTable initialization error:', error);
        }
    }

    // Validasi logika status
    function validateStatusLogic(dropdown, newStatus) {
        const type = dropdown.data('type');
        const transactionStatus = dropdown.data('transaction-status') || dropdown.closest('tr').find('[data-type="transaction"]').val();
        const paymentStatus = dropdown.data('payment-status') || dropdown.closest('tr').find('[data-type="payment"]').val();
        const shippingStatus = dropdown.data('shipping-status') || dropdown.closest('tr').find('[data-type="shipping"]').val();

        let isValid = true;
        let message = '';

        // Validasi berdasarkan tipe status
        if (type === 'transaction') {
            // Logika transaksi
            if (newStatus === 'dikirim' && !['berhasil', 'dibayar'].includes(paymentStatus)) {
                isValid = false;
                message = 'Transaksi tidak dapat dikirim sebelum pembayaran berhasil!';
            }
            if (newStatus === 'selesai' && shippingStatus !== 'diterima') {
                isValid = false;
                message = 'Transaksi tidak dapat diselesaikan sebelum barang diterima!';
            }
        } else if (type === 'payment') {
            // Logika pembayaran
            if (newStatus === 'refund' && ['dikirim', 'diterima'].includes(shippingStatus)) {
                isValid = false;
                message = 'Pembayaran tidak dapat direfund setelah barang dikirim!';
            }
        } else if (type === 'shipping') {
            // Logika pengiriman
            if (['diproses', 'dikirim'].includes(newStatus) && !['berhasil', 'dibayar'].includes(paymentStatus)) {
                isValid = false;
                message = 'Pengiriman tidak dapat diproses sebelum pembayaran berhasil!';
            }
        }

        if (!isValid) {
            showStatusAlert(message);
        }

        return isValid;
    }

    // Fungsi untuk menampilkan alert status
    function showStatusAlert(message) {
        $('#statusAlertMessage').text(message);
        $('#statusAlert').addClass('show').fadeIn();
        
        setTimeout(() => {
            $('#statusAlert').fadeOut();
        }, 5000);
    }

    // Handle status dropdown changes dengan validasi
    $('.status-dropdown').on('change', function() {
        const dropdown = $(this);
        const type = dropdown.data('type');
        const id = dropdown.data('id');
        const newStatus = dropdown.val();
        const currentStatus = dropdown.data('current');
        
        if (newStatus === currentStatus) {
            return;
        }

        // Validasi logika status
        if (!validateStatusLogic(dropdown, newStatus)) {
            dropdown.val(currentStatus); // Kembalikan ke status sebelumnya
            return;
        }
        
        // Add loading state
        dropdown.addClass('loading');
        
        // Prepare AJAX data dengan URL yang benar
        let url, data;
        
        if (type === 'transaction') {
            url = `/admin/pesanan/${id}/status`;
            data = { 
                status: newStatus,
                _method: 'PUT'
            };
        } else if (type === 'payment') {
            url = `/admin/pesanan/${id}/update-payment`;
            data = { 
                payment_status: newStatus,
                waktu_bayar: (newStatus === 'berhasil' || newStatus === 'dibayar') ? new Date().toISOString().slice(0, 16) : null,
                _method: 'PUT'
            };
        } else if (type === 'shipping') {
            url = `/admin/pesanan/${id}/update-shipping`;
            data = { 
                shipping_status: newStatus,
                resi: dropdown.closest('tr').find('.resi-input').val(),
                _method: 'PUT'
            };
        }
        
        // Send AJAX request
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                ...data,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                dropdown.removeClass('loading');
                dropdown.data('current', newStatus);
                
                // Show success feedback
                showFeedback(dropdown, 'success');
                
                // Show success notification
                showNotification('success', 'Status berhasil diperbarui');
                
                // Auto-refresh after 1 second untuk update logika status
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                dropdown.removeClass('loading');
                dropdown.val(currentStatus);
                
                showFeedback(dropdown, 'error');
                
                const errorMsg = xhr.responseJSON?.message || 'Gagal mengupdate status';
                console.error('Status update error:', errorMsg);
                
                showNotification('error', errorMsg);
            }
        });
    });
    
    // Handle resi update
    $('.update-resi').on('click', function() {
        const button = $(this);
        const id = button.data('id');
        const resiInput = button.closest('.input-group').find('.resi-input');
        const newResi = resiInput.val().trim();
        
        if (!newResi) {
            showNotification('warning', 'Masukkan nomor resi terlebih dahulu');
            return;
        }
        
        // Add loading state
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/admin/pesanan/${id}/update-shipping`,
            method: 'POST',
            data: {
                resi: newResi,
                _method: 'PUT',
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                button.prop('disabled', false);
                button.html('<i class="fas fa-check"></i>');
                
                showNotification('success', 'Nomor resi berhasil diupdate');
                
                // Add copy button if not exists
                const copyBtn = button.siblings('.copy-resi');
                if (copyBtn.length === 0) {
                    const newCopyBtn = `<button class="btn btn-outline-info btn-sm copy-resi" data-resi="${newResi}" title="Copy Resi">
                        <i class="fas fa-copy"></i>
                    </button>`;
                    button.before(newCopyBtn);
                } else {
                    copyBtn.data('resi', newResi);
                }
                
                // Change button color to green temporarily
                button.removeClass('btn-outline-success').addClass('btn-success');
                setTimeout(() => {
                    button.removeClass('btn-success').addClass('btn-outline-success');
                }, 2000);
            },
            error: function(xhr) {
                button.prop('disabled', false);
                button.html('<i class="fas fa-check"></i>');
                
                const errorMsg = xhr.responseJSON?.message || 'Gagal mengupdate resi';
                showNotification('error', errorMsg);
            }
        });
    });
    
    // Handle copy resi
    $(document).on('click', '.copy-resi', function() {
        const button = $(this);
        const resi = button.data('resi');
        
        navigator.clipboard.writeText(resi).then(function() {
            const originalClass = button.attr('class');
            const originalHtml = button.html();
            
            button.removeClass('btn-outline-info').addClass('btn-success copy-success');
            button.html('<i class="fas fa-check"></i>');
            
            showNotification('success', `Nomor resi ${resi} berhasil disalin`);
            
            setTimeout(() => {
                button.attr('class', originalClass);
                button.html(originalHtml);
            }, 2000);
        }).catch(function() {
            showNotification('error', 'Gagal menyalin nomor resi');
        });
    });
    
    // Allow Enter key to update resi
    $('.resi-input').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('.input-group').find('.update-resi').click();
        }
    });
});

// Helper function to show feedback
function showFeedback(element, type) {
    const feedback = $(`<i class="fas fa-${type === 'success' ? 'check' : 'times'} status-feedback feedback-${type}"></i>`);
    element.parent().append(feedback);
    
    setTimeout(() => {
        feedback.remove();
    }, 2000);
}

// Helper function to show notifications
function showNotification(type, message) {
    const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : 'warning';
    const title = type === 'success' ? 'Berhasil!' : type === 'error' ? 'Error!' : 'Peringatan!';
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: icon,
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        alert(`${title}\n${message}`);
    }
}

// Alert auto hide
setTimeout(function() {
    $('#alertSuccess, #alertError').fadeOut();
}, 5000);

// Add CSRF token to all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>
@endpush
@endsection