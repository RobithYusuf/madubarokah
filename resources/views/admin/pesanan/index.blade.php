@extends('layouts.app')

@section('title', 'Manajemen Pesanan')

@section('content')

{{-- AWAL BAGIAN HEADER HALAMAN --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Manajemen Pesanan</h1>
        <p class="text-gray-500 mt-2 mb-0">Kelola semua pesanan pelanggan, perbarui status, dan lacak pengiriman.</p>
    </div>
    {{-- Tombol aksi di header bisa diletakkan di sini jika ada --}}
    <a href="{{ route('admin.laporan.transaksi') }}" class="btn btn-sm btn-outline-info shadow-sm">
        <i class="fas fa-chart-line fa-sm"></i> Laporan Transaksi
    </a>
</div>
{{-- AKHIR BAGIAN HEADER HALAMAN --}}

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h4 class="m-0 font-weight-bold text-primary">Daftar Pesanan Pelanggan</h4>
        <div>
            <div class="btn-group mr-2" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" id="filterStatusButton">
                    Filter Status: Semua
                </button>
                <ul class="dropdown-menu dropdown-menu-end"> {{-- Ditambah dropdown-menu-end --}}
                    <li><a class="dropdown-item filter-status" href="#" data-status="all" data-text="Semua">Semua Status</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="pending" data-text="Pending">Pending</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="dibayar" data-text="Dibayar">Dibayar</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="berhasil" data-text="Berhasil">Berhasil (Diproses)</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="dikirim" data-text="Dikirim">Dikirim</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="selesai" data-text="Selesai">Selesai</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="batal" data-text="Batal">Batal</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="gagal" data-text="Gagal">Gagal</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="expired" data-text="Expired">Expired</a></li>
                </ul>
            </div>

            @if(app()->environment('local'))
            <a href="{{ route('admin.pesanan.create-test') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-vial"></i> Test Data
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
                        <th width="3%">No</th>
                        <th width="15%">Referensi & Pelanggan</th>
                        <th width="10%">Tanggal</th>
                        <th width="10%">Total</th>
                        <th width="12%">Status Transaksi</th> {{-- Diperlebar sedikit --}}
                        <th width="15%">Status Pembayaran</th>
                        <th width="15%">Status Pengiriman</th>
                        <th width="10%">Resi Tracking</th>
                        <th width="10%">Aksi</th> {{-- Dikecilkan sedikit --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pesanans as $index => $pesanan)
                    @php
                    // Logika untuk menentukan status yang dapat diubah (sudah ada)
                    $transactionStatus = $pesanan->status;
                    $paymentStatus = $pesanan->pembayaran->status ?? 'pending';
                    $shippingStatus = $pesanan->pengiriman->status ?? 'menunggu_pembayaran';

                    $transactionFinalStatuses = ['selesai', 'batal', 'gagal', 'expired'];
                    $paymentFinalStatuses = ['refund', 'gagal', 'expired'];
                    $shippingFinalStatuses = ['diterima', 'dibatalkan'];

                    $isTransactionFinal = in_array($transactionStatus, $transactionFinalStatuses);
                    $isPaymentFinal = in_array($paymentStatus, $paymentFinalStatuses);
                    $isShippingFinal = in_array($shippingStatus, $shippingFinalStatuses);

                    $isPaid = in_array($paymentStatus, ['berhasil', 'dibayar']);
                    $canShip = $isPaid && !$isTransactionFinal;
                    $isShipped = in_array($shippingStatus, ['dikirim', 'diterima']);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <small class="text-muted">ID: #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</small>
                                @if($pesanan->merchant_ref)
                                <small class="fw-bold">{{ $pesanan->merchant_ref }}</small> {{-- Diganti font-weight-bold menjadi fw-bold (BS5) --}}
                                @endif
                                @if($pesanan->tripay_reference)
                                <small class="text-info">{{ $pesanan->tripay_reference }}</small>
                                @endif
                                <hr class="my-1">
                                <span class="fw-bold">{{ $pesanan->user->nama ?? '-' }}</span>
                                @if($pesanan->nama_penerima && $pesanan->nama_penerima !== $pesanan->user->nama)
                                <small class="text-muted fst-italic">Penerima: {{ $pesanan->nama_penerima }}</small> {{-- Ditambah fst-italic --}}
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span>{{ $pesanan->tanggal_transaksi ? $pesanan->tanggal_transaksi->format('d/m/y') : '-' }}</span> {{-- Format tahun jadi 2 digit --}}
                                <small class="text-muted">{{ $pesanan->tanggal_transaksi ? $pesanan->tanggal_transaksi->format('H:i') : '' }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                                @if($pesanan->pengiriman && $pesanan->pengiriman->biaya > 0)
                                <small class="text-muted">+Ongkir: {{ number_format($pesanan->pengiriman->biaya, 0, ',', '.') }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($isTransactionFinal)
                            <span class="badge bg-{{ $transactionStatus === 'selesai' ? 'success' : ($transactionStatus === 'batal' ? 'warning' : 'danger') }} text-white">
                                {{ ucfirst($transactionStatus) }}
                            </span>
                            @else
                            <select class="form-control form-control-sm status-dropdown" {{-- Diganti form-control menjadi form-control --}}
                                data-type="transaction"
                                data-id="{{ $pesanan->id }}"
                                data-current="{{ $transactionStatus }}"
                                data-payment-status="{{ $paymentStatus }}"
                                data-shipping-status="{{ $shippingStatus }}">
                                {{-- Opsi status transaksi --}}
                                @if($transactionStatus === 'pending')
                                <option value="pending" selected>Pending</option>
                                <option value="batal">Batal</option>
                                <option value="gagal">Gagal</option>
                                <option value="expired">Expired</option>
                                @if($isPaid) <option value="dibayar">Dibayar</option> @endif
                                @elseif($transactionStatus === 'dibayar')
                                <option value="dibayar" selected>Dibayar</option>
                                <option value="berhasil">Berhasil (Proses)</option>
                                @if($canShip) <option value="dikirim">Dikirim</option> @endif
                                @elseif($transactionStatus === 'berhasil')
                                <option value="berhasil" selected>Berhasil (Proses)</option>
                                @if($canShip) <option value="dikirim">Dikirim</option> @endif
                                @elseif($transactionStatus === 'dikirim')
                                <option value="dikirim" selected>Dikirim</option>
                                @if($shippingStatus === 'diterima') <option value="selesai">Selesai</option> @endif
                                @else {{-- Fallback jika status tidak dikenali --}}
                                <option value="{{ $transactionStatus }}" selected>{{ ucfirst($transactionStatus) }}</option>
                                @endif
                            </select>
                            @endif
                        </td>
                        <td>
                            @if($pesanan->pembayaran)
                            <div class="d-flex flex-column">
                                @if($isPaymentFinal)
                                <span class="badge bg-{{ $paymentStatus === 'refund' ? 'info' : ($paymentStatus === 'gagal' || $paymentStatus === 'expired' ? 'danger' : 'secondary') }} text-white"> {{-- Ditambah secondary --}}
                                    {{ ucfirst($paymentStatus) }}
                                </span>
                                @else
                                <select class="form-control form-control-sm status-dropdown mb-1"
                                    data-type="payment"
                                    data-id="{{ $pesanan->id }}"
                                    data-current="{{ $paymentStatus }}"
                                    data-transaction-status="{{ $transactionStatus }}">
                                    {{-- Opsi status pembayaran --}}
                                    @if($paymentStatus === 'pending')
                                    <option value="pending" selected>Pending</option>
                                    <option value="berhasil">Berhasil</option>
                                    <option value="dibayar">Dibayar</option>
                                    <option value="gagal">Gagal</option>
                                    <option value="expired">Expired</option>
                                    @elseif(in_array($paymentStatus, ['berhasil', 'dibayar']))
                                    <option value="{{ $paymentStatus }}" selected>{{ ucfirst($paymentStatus) }}</option>
                                    @if(!$isShipped && !in_array($transactionStatus, ['batal', 'gagal', 'expired']))
                                    <option value="refund">Refund</option>
                                    @endif
                                    @else
                                    <option value="{{ $paymentStatus }}" selected>{{ ucfirst($paymentStatus) }}</option>
                                    @endif
                                </select>
                                @endif
                                <small class="text-muted">{{ $pesanan->pembayaran->metode ?? '-' }}</small>
                                @if($pesanan->pembayaran->payment_code)
                                <small class="text-info fw-bold">{{ $pesanan->pembayaran->payment_code }}</small>
                                @endif
                                @if($pesanan->pembayaran->waktu_bayar)
                                <small class="text-success fst-italic">{{ $pesanan->pembayaran->waktu_bayar->format('d/m H:i') }}</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($pesanan->pengiriman)
                            <div class="d-flex flex-column">
                                @if($isShippingFinal)
                                <span class="badge bg-{{ $shippingStatus === 'diterima' ? 'success' : ($shippingStatus === 'dibatalkan' ? 'warning' : 'secondary') }} text-white">
                                    {{ ucfirst(str_replace('_', ' ', $shippingStatus)) }}
                                </span>
                                @elseif(!$isPaid)
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
                                    {{-- Opsi status pengiriman --}}
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
                                <small class="text-muted">{{ $pesanan->pengiriman->kurir ?? '-' }} {{ $pesanan->pengiriman->layanan ? '('.$pesanan->pengiriman->layanan.')' : '' }}</small>
                                @if($pesanan->pengiriman->weight)
                                <small class="text-muted">{{ $pesanan->pengiriman->weight }}g</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
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
                                <button class="btn btn-outline-secondary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" {{ !$canShip || $isShippingFinal || !$pesanan->pengiriman->resi ? 'disabled' : ''}}><i class="fas fa-cog"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if($canShip && !$isShippingFinal)
                                    <li><button class="dropdown-item update-resi" type="button" data-id="{{ $pesanan->id }}"><i class="fas fa-save mr-2"></i>Update Resi</button></li>
                                    @endif
                                    @if($pesanan->pengiriman->resi)
                                    <li><button class="dropdown-item copy-resi" type="button" data-resi="{{ $pesanan->pengiriman->resi }}"><i class="fas fa-copy mr-2"></i>Salin Resi</button></li>
                                    {{-- Placeholder untuk Lacak Paket jika ada URL tracker --}}
                                    {{-- <li><a class="dropdown-item" href="#" target="_blank"><i class="fas fa-search-location mr-2"></i>Lacak Paket</a></li> --}}
                                    @endif
                                </ul>
                            </div>
                            @if(!$isPaid && $pesanan->pengiriman)
                            <small class="text-muted">Menunggu pembayaran</small>
                            @elseif($isShippingFinal && $pesanan->pengiriman && !$pesanan->pengiriman->resi)
                            <small class="text-muted">Resi tidak diinput</small>
                            @endif
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-info" data-bs-toggle="modal"
                                    data-bs-target="#modalDetail{{ $pesanan->id }}" title="Detail Pesanan">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @if($pesanan->pengiriman)
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalShipping{{ $pesanan->id }}" title="Detail Pengiriman">
                                    <i class="fa fa-truck"></i>
                                </button>
                                @endif
                                @if(!in_array($transactionStatus, ['selesai', 'dikirim', 'batal'])) {{-- Tidak bisa hapus jika sudah batal --}}
                                <button class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#modalHapus{{ $pesanan->id }}" title="Hapus Pesanan">
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
                <p>Belum ada pesanan yang masuk atau sesuai filter yang dipilih.</p>
                @if(app()->environment('local'))
                <a href="{{ route('admin.pesanan.create-test') }}" class="btn btn-primary mt-2">
                    <i class="fas fa-vial"></i> Generate Test Data
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ... kode alert ... --}}

@if($pesanans && $pesanans->count() > 0)
@foreach ($pesanans as $pesanan)
<div class="modal fade" id="modalDetail{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detail Pesanan #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <h6><strong><i class="fas fa-user-circle mr-2"></i>Informasi Pelanggan</strong></h6>
                        <p class="mb-1"><strong>Nama:</strong> {{ $pesanan->user->nama ?? '-' }}</p>
                        <p class="mb-1"><strong>Username:</strong> {{ $pesanan->user->username ?? '-' }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $pesanan->user->email ?? '-' }}</p>
                        <p class="mb-1"><strong>No. HP:</strong> {{ $pesanan->user->nohp ?? '-' }}</p>
                        <p class="mb-0"><strong>Alamat User:</strong> <small class="text-muted">{{ $pesanan->user->alamat ?? '-' }}</small></p>

                        @if($pesanan->nama_penerima || $pesanan->telepon_penerima || $pesanan->alamat_pengiriman)
                        <hr>
                        <h6><strong><i class="fas fa-map-marked-alt mr-2"></i>Informasi Penerima</strong></h6>
                        @if($pesanan->nama_penerima)
                        <p class="mb-1"><strong>Nama Penerima:</strong> {{ $pesanan->nama_penerima }}</p>
                        @endif
                        @if($pesanan->telepon_penerima)
                        <p class="mb-1"><strong>Telepon:</strong> {{ $pesanan->telepon_penerima }}</p>
                        @endif
                        @if($pesanan->alamat_pengiriman)
                        <p class="mb-0"><strong>Alamat Pengiriman:</strong> <small class="text-muted">{{ $pesanan->alamat_pengiriman }}</small></p>
                        @endif
                        @endif
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <h6><strong><i class="fas fa-file-invoice mr-2"></i>Informasi Pesanan</strong></h6>
                        <p class="mb-1"><strong>Tanggal:</strong> {{ $pesanan->tanggal_transaksi ? $pesanan->tanggal_transaksi->format('d M Y, H:i') : '-' }}</p>
                        <p class="mb-1"><strong>Merchant Ref:</strong> {{ $pesanan->merchant_ref ?? '-' }}</p>
                        @if($pesanan->tripay_reference)
                        <p class="mb-1"><strong>Tripay Ref:</strong> {{ $pesanan->tripay_reference }}</p>
                        @endif
                        <p class="mb-1"><strong>Status:</strong>
                            <span class="badge bg-{{ $pesanan->status === 'selesai' ? 'success' : ($pesanan->status === 'dikirim' ? 'info' : ($pesanan->status === 'dibayar' || $pesanan->status === 'berhasil' ? 'primary' : ($pesanan->status === 'pending' ? 'secondary' : 'danger'))) }} text-white">
                                {{ ucfirst($pesanan->status) }}
                            </span>
                        </p>
                        @if($pesanan->expired_time)
                        <p class="mb-1"><strong>Kedaluwarsa:</strong> {{ $pesanan->expired_time->format('d M Y, H:i') }}</p>
                        @endif
                        @if($pesanan->catatan)
                        <p class="mb-0"><strong>Catatan Pelanggan:</strong> <small class="text-muted fst-italic">"{{ $pesanan->catatan }}"</small></p>
                        @endif
                    </div>
                    <div class="col-lg-4 col-md-12 mb-3"> {{-- Dibuat full width di md --}}
                        @if($pesanan->pembayaran)
                        <h6><strong><i class="fas fa-credit-card mr-2"></i>Informasi Pembayaran</strong></h6>
                        <p class="mb-1"><strong>Metode:</strong> {{ $pesanan->pembayaran->metode ?? '-' }}</p>
                        <p class="mb-1"><strong>Status:</strong>
                            <span class="badge bg-{{ $pesanan->pembayaran->status === 'berhasil' || $pesanan->pembayaran->status === 'dibayar' ? 'success' : ($pesanan->pembayaran->status === 'pending' ? 'warning' : 'danger') }} text-white">
                                {{ ucfirst($pesanan->pembayaran->status) }}
                            </span>
                        </p>
                        <p class="mb-1"><strong>Total Bayar:</strong> Rp {{ number_format($pesanan->pembayaran->total_bayar, 0, ',', '.') }}</p>
                        @if($pesanan->pembayaran->payment_code)
                        <p class="mb-1"><strong>Kode Bayar:</strong> {{ $pesanan->pembayaran->payment_code }}</p>
                        @endif
                        @if($pesanan->pembayaran->waktu_bayar)
                        <p class="mb-0"><strong>Waktu Bayar:</strong> {{ $pesanan->pembayaran->waktu_bayar->format('d M Y, H:i') }}</p>
                        @endif
                        @endif

                        @if($pesanan->pengiriman)
                        <hr class="my-2">
                        <h6><strong><i class="fas fa-shipping-fast mr-2"></i>Informasi Pengiriman</strong></h6>
                        <p class="mb-1"><strong>Kurir:</strong> {{ $pesanan->pengiriman->kurir ?? '-' }} ({{ $pesanan->pengiriman->layanan ?? '-' }})</p>
                        <p class="mb-1"><strong>Berat:</strong> {{ $pesanan->pengiriman->weight ?? 0 }}g</p>
                        <p class="mb-1"><strong>Biaya:</strong> Rp {{ number_format($pesanan->pengiriman->biaya ?? 0, 0, ',', '.') }}</p>
                        <p class="mb-1"><strong>Status:</strong>
                            <span class="badge bg-{{ $pesanan->pengiriman->status === 'diterima' ? 'success' : ($pesanan->pengiriman->status === 'dikirim' ? 'info' : ($pesanan->pengiriman->status === 'diproses' ? 'warning' : 'secondary')) }} text-white">
                                {{ ucfirst(str_replace('_', ' ', $pesanan->pengiriman->status)) }}
                            </span>
                        </p>
                        @if($pesanan->pengiriman->resi)
                        <p class="mb-1"><strong>No. Resi:</strong>
                            <span class="fw-bold text-success">{{ $pesanan->pengiriman->resi }}</span>
                            <button class="btn btn-xs btn-outline-info ms-1 py-0 px-1 copy-resi-modal" data-resi="{{ $pesanan->pengiriman->resi }}">
                                <i class="fas fa-copy fa-xs"></i>
                            </button>
                        </p>
                        @endif
                        @if($pesanan->pengiriman->etd)
                        <p class="mb-0"><strong>Estimasi:</strong> {{ $pesanan->pengiriman->etd }} hari</p>
                        @endif
                        @endif
                    </div>
                </div>

                <hr>
                <h6><strong><i class="fas fa-boxes mr-2"></i>Detail Produk Dipesan</strong></h6>
                @if($pesanan->detailTransaksi && $pesanan->detailTransaksi->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover"> {{-- Ditambah table-hover --}}
                        <thead class="table-light"> {{-- Header tabel lebih terang --}}
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
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
                                <td class="text-end">{{ $detail->jumlah }}</td>
                                <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold table-light"> {{-- Footer tabel lebih terang --}}
                                <td colspan="3"></td>
                                <td class="text-end">Total Pesanan:</td>
                                <td class="text-end">Rp {{ number_format($pesanan->detailTransaksi->sum('subtotal'), 0, ',', '.') }}</td>
                            </tr>
                            @if($pesanan->pengiriman && $pesanan->pengiriman->biaya > 0)
                            <tr class="fw-bold table-light">
                                <td colspan="3"></td>
                                <td class="text-end">Biaya Pengiriman:</td>
                                <td class="text-end">Rp {{ number_format($pesanan->pengiriman->biaya, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="fw-bold table-info"> {{-- Total keseluruhan dibuat lebih menonjol --}}
                                <td colspan="3"></td>
                                <td class="text-end">Grand Total:</td>
                                <td class="text-end">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-muted">Tidak ada detail produk untuk pesanan ini.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@if($pesanan->pengiriman)
<div class="modal fade" id="modalShipping{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detail Pengiriman - Pesanan #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.75;"> {{-- Ditambahkan style inline untuk memastikan warna dan opasitas --}}
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6><strong><i class="fas fa-truck mr-2"></i>Informasi Pengiriman</strong></h6>
                        <p class="mb-1"><strong>Kurir:</strong> {{ $pesanan->pengiriman->kurir ?? '-' }}</p>
                        <p class="mb-1"><strong>Layanan:</strong> {{ $pesanan->pengiriman->layanan ?? '-' }} ({{ $pesanan->pengiriman->service_code ?? '-' }})</p>
                        <p class="mb-1"><strong>Berat Total:</strong> {{ $pesanan->pengiriman->weight ?? 0 }} gram</p>
                        <p class="mb-1"><strong>Biaya Kirim:</strong> Rp {{ number_format($pesanan->pengiriman->biaya ?? 0, 0, ',', '.') }}</p>
                        <p class="mb-0"><strong>Estimasi Pengiriman:</strong> {{ $pesanan->pengiriman->etd ?? '-' }} hari</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6><strong><i class="fas fa-map-pin mr-2"></i>Status & Tracking</strong></h6>
                        <p class="mb-1"><strong>Status Saat Ini:</strong>
                            <span class="badge bg-{{ $pesanan->pengiriman->status === 'diterima' ? 'success' : ($pesanan->pengiriman->status === 'dikirim' ? 'info' : ($pesanan->pengiriman->status === 'diproses' ? 'warning' : 'secondary')) }} text-white">
                                {{ ucfirst(str_replace('_', ' ', $pesanan->pengiriman->status)) }}
                            </span>
                        </p>
                        @if($pesanan->pengiriman->resi)
                        <p class="mb-1"><strong>Nomor Resi:</strong>
                            <span class="fw-bold text-success">{{ $pesanan->pengiriman->resi }}</span>
                            <button class="btn btn-xs btn-outline-info ms-1 py-0 px-1 copy-resi-modal" data-resi="{{ $pesanan->pengiriman->resi }}">
                                <i class="fas fa-copy fa-xs"></i>
                            </button>
                        </p>
                        <div class="alert alert-light p-2 mt-2 border">
                            <small><i class="fas fa-info-circle me-1"></i> <strong>Cara Melacak Paket:</strong><br>
                                1. Salin nomor resi di atas.<br>
                                2. Kunjungi website resmi kurir <strong>{{ strtoupper($pesanan->pengiriman->kurir) }}</strong>.<br>
                                3. Masukkan nomor resi pada kolom pelacakan yang tersedia.</small>
                        </div>
                        @else
                        <p class="mb-1"><strong>Nomor Resi:</strong> <span class="text-muted">Belum tersedia</span></p>
                        @endif

                        <h6 class="mt-3"><strong><i class="fas fa-home mr-2"></i>Alamat Tujuan</strong></h6>
                        <p class="text-muted mb-0"><small>{{ $pesanan->alamat_pengiriman ?? 'Alamat tidak tersedia' }}</small></p>
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

@if(!in_array($pesanan->status, ['selesai', 'dikirim', 'batal']))
<div class="modal fade" id="modalHapus{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus Pesanan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Anda yakin ingin menghapus permanen pesanan <strong>#{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</strong>?</p>
                <div class="alert alert-warning small p-2">
                    <i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait pesanan ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('admin.pesanan.destroy', $pesanan->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt mr-2"></i>Ya, Hapus</button>
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
        /* border: 1px solid #dee2e6; */
        /* Menggunakan style default form-control */
        /* border-radius: 4px; */
        font-size: 0.8rem;
        /* Dikecilkan sedikit lagi */
        min-width: 110px;
        /* Disesuaikan */
        padding-top: 0.25rem;
        /* Disesuaikan paddingnya */
        padding-bottom: 0.25rem;
    }

    .status-dropdown:focus {
        border-color: #86b7fe;
        /* Warna BS5 focus */
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        /* Shadow BS5 focus */
    }

    .status-dropdown:disabled {
        background-color: #e9ecef;
        opacity: 0.65;
    }

    /* Readonly input styling */
    .resi-input[readonly] {
        background-color: #e9ecef;
        opacity: 1;
        cursor: default;
    }

    /* Badge styling untuk status final */
    .badge {
        font-size: 0.7rem;
        /* Dikecilkan sedikit */
        padding: 0.3rem 0.45rem;
        /* Disesuaikan */
    }

    /* Table responsive adjustments */
    .table td,
    .table th {
        /* Digabung */
        vertical-align: middle;
        padding: 0.6rem 0.5rem;
        /* Padding disamakan dan disesuaikan */
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    /* Loading state for dropdowns */
    .status-dropdown.loading {
        opacity: 0.6;
        pointer-events: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 8s2-4 6-4 6 4 6 4-2 4-6 4-6-4-6-4z'/%3e%3ccircle cx='8' cy='8' r='1' fill='%23343a40'/%3e%3c/svg%3e"),
            url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 8s2-4 6-4 6 4 6 4-2 4-6 4-6-4-6-4z'/%3e%3ccircle cx='8' cy='8' r='1' fill='%23343a40'/%3e%3c/svg%3e");
        /* Placeholder loading, sesuaikan jika ada icon library */
        background-repeat: no-repeat;
        background-position: right .75rem center;
        background-size: 16px 12px;
    }

    /* Feedback icons (sudah ada) */

    /* Copy success animation (sudah ada) */

    /* Modal improvements */
    .modal-xl {
        max-width: 1140px;
        /* Ukuran standar BS5 XL */
    }

    .modal-header {
        padding: 0.8rem 1rem;
        /* Padding modal header disesuaikan */
    }

    .modal-body p.mb-1 {
        /* Margin bottom untuk paragraf di modal */
        margin-bottom: 0.4rem !important;
    }

    .btn-xs {
        /* Kelas untuk tombol sangat kecil */
        padding: 0.1rem 0.3rem;
        font-size: 0.65rem;
    }

    /* Status alert styling (sudah ada) */

    /* Responsive table */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.8rem;
            /* Disesuaikan */
        }

        .status-dropdown {
            min-width: 90px;
            /* Disesuaikan */
            font-size: 0.7rem;
            /* Disesuaikan */
        }

        .btn-group-sm>.btn,
        .btn-sm {
            /* Termasuk .btn-sm umum */
            padding: 0.2rem 0.35rem;
            /* Disesuaikan */
            font-size: 0.7rem;
            /* Disesuaikan */
        }

        .card-header .btn-group {
            /* Spasi untuk filter di mobile */
            margin-top: 0.5rem;
            display: block;
            /* Agar full width jika perlu */
        }

        .card-header .btn-secondary {
            /* Tombol test data di mobile */
            margin-top: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var tablePesanan; // Deklarasikan di luar agar bisa diakses

        if ($('#tabelPesanan').length && $('#tabelPesanan tbody tr').length > 0) {
            try {
                tablePesanan = $('#tabelPesanan').DataTable({ // Assign ke variabel
                    responsive: true,
                    // processing: true, // Bisa diaktifkan jika ada banyak data & server-side
                    // serverSide: false, // Sesuaikan jika menggunakan server-side
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    lengthMenu: [10, 25, 50, 100], // Tambah opsi 100
                    pageLength: 10,
                    order: [
                        [2, 'desc']
                    ], // Urutkan berdasarkan kolom Tanggal (index 2) menurun
                    columnDefs: [{
                            targets: [0, 8],
                            orderable: false,
                            searchable: false
                        }, // No & Aksi
                        {
                            targets: [4, 5, 6, 7],
                            orderable: true,
                            searchable: true
                        } // Status bisa diorder & search
                    ],
                    language: { // Diringkas
                        "lengthMenu": "Tampil _MENU_",
                        "zeroRecords": "Pesanan tidak ditemukan",
                        "info": "Hal _PAGE_ dari _PAGES_ (_TOTAL_ total)",
                        "infoEmpty": "Tidak ada pesanan",
                        "infoFiltered": "(dari _MAX_ total)",
                        "search": "Cari:",
                        "paginate": {
                            "first": "<<",
                            "last": ">>",
                            "next": ">",
                            "previous": "<"
                        }
                    }
                });
                console.log('DataTable Pesanan berhasil diinisialisasi.');
            } catch (error) {
                console.error('Error saat inisialisasi DataTable Pesanan:', error);
            }
        }

        // Filter by status
        $('.filter-status').on('click', function(e) {
            e.preventDefault();
            var status = $(this).data('status');
            var statusText = $(this).data('text');

            if (tablePesanan) { // Pastikan DataTable sudah diinisialisasi
                if (status === 'all') {
                    tablePesanan.column(4).search('').draw(); // Kolom status transaksi (index 4)
                } else {
                    // Search dengan regex untuk mencocokkan teks status di dalam tag HTML (misal, badge atau select)
                    // Ini akan mencari teks 'Pending', 'Dibayar', dll. secara case-insensitive
                    tablePesanan.column(4).search('^' + status + '$', true, false, true).draw();
                }
                $('#filterStatusButton').text('Filter: ' + statusText);
            } else {
                console.warn('DataTable Pesanan belum siap untuk filtering.');
            }
        });

        // Validasi logika status (fungsi tetap sama)
        function validateStatusLogic(dropdown, newStatus) {
            const type = dropdown.data('type');
            const tr = dropdown.closest('tr');
            const transactionStatus = tr.find('select[data-type="transaction"]').val() || tr.find('span.badge').text().trim().toLowerCase();
            const paymentStatusCurrent = tr.find('select[data-type="payment"]').val() || tr.find('td:nth-child(6) span.badge').text().trim().toLowerCase(); // Ambil status pembayaran saat ini
            const shippingStatusCurrent = tr.find('select[data-type="shipping"]').val() || tr.find('td:nth-child(7) span.badge').text().trim().toLowerCase(); // Ambil status pengiriman saat ini

            let isValid = true;
            let message = '';

            if (type === 'transaction') {
                if (newStatus === 'dikirim' && !['berhasil', 'dibayar'].includes(paymentStatusCurrent)) {
                    isValid = false;
                    message = 'Pembayaran harus berhasil/dibayar sebelum transaksi dikirim.';
                }
                if (newStatus === 'selesai' && shippingStatusCurrent !== 'diterima') {
                    isValid = false;
                    message = 'Barang harus diterima sebelum transaksi diselesaikan.';
                }
            } else if (type === 'payment') {
                if (newStatus === 'refund' && ['dikirim', 'diterima'].includes(shippingStatusCurrent)) {
                    isValid = false;
                    message = 'Refund tidak bisa dilakukan jika barang sudah dikirim/diterima.';
                }
            } else if (type === 'shipping') {
                if (['diproses', 'dikirim'].includes(newStatus) && !['berhasil', 'dibayar'].includes(paymentStatusCurrent)) {
                    isValid = false;
                    message = 'Pembayaran harus berhasil/dibayar sebelum pengiriman diproses.';
                }
            }

            if (!isValid) {
                showStatusAlert(message);
            }
            return isValid;
        }

        // Fungsi untuk menampilkan alert status (fungsi tetap sama)
        function showStatusAlert(message) {
            $('#statusAlertMessage').text(message);
            $('#statusAlert').addClass('show').fadeIn();
            setTimeout(() => {
                $('#statusAlert').fadeOut().removeClass('show');
            }, 5000);
        }

        // Handle status dropdown changes
        $('#tabelPesanan').on('change', '.status-dropdown', function() { // Delegasi event
            const dropdown = $(this);
            const type = dropdown.data('type');
            const id = dropdown.data('id');
            const newStatus = dropdown.val();
            const currentStatus = dropdown.data('current');

            if (newStatus === currentStatus) return;

            if (!validateStatusLogic(dropdown, newStatus)) {
                dropdown.val(currentStatus); // Kembalikan
                return;
            }

            dropdown.addClass('loading').prop('disabled', true); // Disable saat loading
            let url, dataAjax;

            if (type === 'transaction') {
                url = `/admin/pesanan/${id}/status`;
                dataAjax = {
                    status: newStatus,
                    _method: 'PUT'
                };
            } else if (type === 'payment') {
                url = `/admin/pesanan/${id}/update-payment`;
                dataAjax = {
                    payment_status: newStatus,
                    waktu_bayar: (newStatus === 'berhasil' || newStatus === 'dibayar') ? new Date().toISOString().slice(0, 19).replace('T', ' ') : null,
                    _method: 'PUT'
                };
            } else if (type === 'shipping') {
                url = `/admin/pesanan/${id}/update-shipping`;
                dataAjax = {
                    shipping_status: newStatus,
                    resi: dropdown.closest('tr').find('.resi-input').val(), // Ambil resi saat update status pengiriman
                    _method: 'PUT'
                };
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    ...dataAjax,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    dropdown.removeClass('loading').prop('disabled', false);
                    if (response.success) {
                        dropdown.data('current', newStatus); // Update current status
                        showFeedback(dropdown, 'success');
                        showNotification('success', response.message || 'Status berhasil diperbarui.');
                        setTimeout(() => {
                            location.reload();
                        }, 1200); // Reload untuk konsistensi data
                    } else {
                        dropdown.val(currentStatus); // Kembalikan jika server-side validation gagal
                        showFeedback(dropdown, 'error');
                        showNotification('error', response.message || 'Gagal mengupdate status.');
                    }
                },
                error: function(xhr) {
                    dropdown.removeClass('loading').prop('disabled', false).val(currentStatus);
                    showFeedback(dropdown, 'error');
                    const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                    showNotification('error', errorMsg);
                }
            });
        });

        // Handle resi update
        $('#tabelPesanan').on('click', '.update-resi', function() { // Delegasi event
            const button = $(this);
            const id = button.data('id');
            const resiInput = button.closest('.input-group').find('.resi-input');
            const newResi = resiInput.val().trim();

            if (!newResi) {
                showNotification('warning', 'Nomor resi tidak boleh kosong.');
                resiInput.focus();
                return;
            }

            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $.ajax({
                url: `/admin/pesanan/${id}/update-shipping`,
                method: 'POST',
                data: {
                    resi: newResi,
                    _method: 'PUT',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message || 'Nomor resi berhasil diupdate.');
                        button.html('<i class="fas fa-check"></i>').removeClass('btn-outline-success').addClass('btn-success');
                        // Update copy button data if exists or create one
                        let copyBtn = button.closest('.input-group').find('.copy-resi');
                        if (copyBtn.length === 0) {
                            // Logic to add copy button if needed, or ensure it's always there and just update its data-resi
                        } else {
                            copyBtn.data('resi', newResi).attr('data-resi', newResi); // Update data attribute
                        }
                        setTimeout(() => {
                            button.removeClass('btn-success').addClass('btn-outline-success');
                        }, 2000);
                    } else {
                        showNotification('error', response.message || 'Gagal update resi.');
                        button.html('<i class="fas fa-save"></i>'); // Reset icon
                    }
                },
                error: function() {
                    showNotification('error', 'Terjadi kesalahan saat update resi.');
                    button.html('<i class="fas fa-save"></i>'); // Reset icon
                },
                complete: function() {
                    button.prop('disabled', false);
                    // If not success, don't reset to check, keep it as save icon
                    if (!button.hasClass('btn-success')) {
                        button.html('<i class="fas fa-save"></i>');
                    }
                }
            });
        });

        // Handle copy resi (global dan modal)
        $(document).on('click', '.copy-resi, .copy-resi-modal', function() { // Gabungkan selector
            const button = $(this);
            const resi = button.data('resi');
            if (!resi) return;

            navigator.clipboard.writeText(resi).then(() => {
                const originalHtml = button.html();
                button.html('<i class="fas fa-check"></i> Tercopy!').addClass('btn-success').removeClass('btn-outline-info');
                showNotification('success', `Resi ${resi} disalin!`);
                setTimeout(() => {
                    button.html(originalHtml).removeClass('btn-success').addClass('btn-outline-info');
                }, 2500);
            }).catch(() => {
                showNotification('error', 'Gagal menyalin resi.');
            });
        });

        // Allow Enter key to update resi
        $('#tabelPesanan').on('keypress', '.resi-input', function(e) { // Delegasi event
            if (e.which === 13) { // Enter key
                e.preventDefault(); // Mencegah submit form jika ada
                $(this).closest('.input-group').find('.update-resi').click();
            }
        });

        // Fungsi showFeedback (fungsi tetap sama)
        function showFeedback(element, type) {
            const feedbackIconClass = type === 'success' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
            const feedback = $(`<i class="fas ${feedbackIconClass} status-feedback" style="font-size:0.8em; margin-left: 5px;"></i>`);
            // Hapus feedback lama jika ada
            element.parent().find('.status-feedback').remove();
            element.after(feedback); // Tampilkan setelah dropdown
            setTimeout(() => {
                feedback.remove();
            }, 2500);
        }

        // Fungsi showNotification (fungsi tetap sama)
        function showNotification(type, message) {
            const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : 'warning';
            const title = type === 'success' ? 'Sukses!' : type === 'error' ? 'Error!' : 'Perhatian!';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true
                });
            } else {
                alert(`${title}\n${message}`);
            }
        }

        // Alert auto hide
        setTimeout(function() {
            $('#alertSuccess, #alertError').fadeOut(500, function() {
                $(this).remove();
            });
        }, 4000);

        // Add CSRF token (fungsi tetap sama)
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
</script>
@endpush
@endsection