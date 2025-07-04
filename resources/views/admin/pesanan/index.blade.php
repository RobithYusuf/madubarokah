@extends('layouts.app')

@section('title', 'Manajemen Pesanan')

@section('content')

{{-- AWAL BAGIAN HEADER HALAMAN --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Manajemen Pesanan</h1>
        <p class="text-gray-500 mt-2 mb-0">Kelola semua pesanan pelanggan dengan sistem status yang telah disederhanakan.</p>
    </div>
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
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item filter-status" href="#" data-status="all" data-text="Semua">Semua Status</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="pending" data-text="Menunggu Pembayaran">🟡 Menunggu Pembayaran</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="dibayar,berhasil" data-text="Diproses">🔵 Sedang Diproses</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="dikirim" data-text="Dikirim">🚚 Sedang Dikirim</a></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="selesai" data-text="Selesai">✅ Selesai</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item filter-status" href="#" data-status="batal,gagal,expired" data-text="Dibatalkan">❌ Dibatalkan/Gagal</a></li>
                </ul>
            </div>

            @if(config('app.env') === 'local' || config('app.debug') === true)
            <a href="{{ route('admin.pesanan.create-test') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-vial"></i> Test Data
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div id="alertSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if (session('error'))
        <div id="alertError" class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if($pesanans && $pesanans->count() > 0)
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-bordered table-striped table-hover" id="tabelPesanan" style="width: 100%!important;">
                <thead>
                    <tr>
                        <th width="3%">No</th>
                        <th width="12%">Invoice</th>
                        <th width="15%">Pelanggan</th>
                        <th width="10%">Total</th>
                        <th width="15%">Status Pesanan</th>
                        <th width="12%">Pembayaran</th>
                        <th width="13%">Pengiriman</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pesanans as $index => $pesanan)
                    @php
                    $transactionStatus = $pesanan->status;
                    $paymentStatus = $pesanan->pembayaran->status ?? 'pending';
                    $shippingStatus = $pesanan->pengiriman->status ?? 'menunggu_pembayaran';
                    
                    // Determine unified status for display
                    $unifiedStatus = 'pending';
                    $statusLabel = 'Menunggu Pembayaran';
                    $statusColor = 'warning';
                    
                    if (in_array($transactionStatus, ['batal', 'gagal', 'expired'])) {
                        $unifiedStatus = 'canceled';
                        $statusLabel = 'Dibatalkan';
                        $statusColor = 'danger';
                    } elseif ($transactionStatus === 'selesai') {
                        $unifiedStatus = 'completed';
                        $statusLabel = 'Selesai';
                        $statusColor = 'success';
                    } elseif ($transactionStatus === 'dikirim') {
                        $unifiedStatus = 'shipped';
                        $statusLabel = 'Sedang Dikirim';
                        $statusColor = 'primary';
                    } elseif (in_array($transactionStatus, ['dibayar', 'berhasil'])) {
                        $unifiedStatus = 'processing';
                        $statusLabel = 'Sedang Diproses';
                        $statusColor = 'info';
                    }
                    
                    $hasResi = $pesanan->pengiriman && $pesanan->pengiriman->resi;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-primary">{{ $pesanan->merchant_ref }}</span>
                                <small class="text-muted">{{ $pesanan->tanggal_transaksi->format('d/m/y H:i') }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ $pesanan->user->nama ?? '-' }}</span>
                                @if($pesanan->nama_penerima && $pesanan->nama_penerima !== $pesanan->user->nama)
                                <small class="text-muted">📦 {{ $pesanan->nama_penerima }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColor }} text-white px-3 py-2">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td>
                            @if($pesanan->pembayaran)
                            <div class="d-flex flex-column">
                                <small class="fw-bold">{{ $pesanan->pembayaran->metode ?? '-' }}</small>
                                @if($pesanan->pembayaran->waktu_bayar)
                                <small class="text-success">✓ {{ $pesanan->pembayaran->waktu_bayar->format('d/m H:i') }}</small>
                                @else
                                <small class="text-muted">Belum dibayar</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($pesanan->pengiriman)
                            <div class="d-flex flex-column">
                                <small class="fw-bold">{{ $pesanan->pengiriman->kurir }} - {{ $pesanan->pengiriman->layanan }}</small>
                                @if($hasResi)
                                <small class="text-primary">📋 {{ $pesanan->pengiriman->resi }}</small>
                                @else
                                <small class="text-muted">Belum ada resi</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                {{-- Detail Button --}}
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $pesanan->id }}" title="Detail Pesanan">
                                    <i class="fa fa-eye"></i> Detail
                                </button>
                                
                                {{-- Action Buttons Based on Status --}}
                                @if($unifiedStatus === 'pending')
                                    <button class="btn btn-success btn-sm action-button" 
                                            data-action="process_payment" 
                                            data-id="{{ $pesanan->id }}"
                                            title="Konfirmasi pembayaran berhasil">
                                        <i class="fa fa-check"></i> Konfirmasi Bayar
                                    </button>
                                    <button class="btn btn-danger btn-sm action-button" 
                                            data-action="cancel_order" 
                                            data-id="{{ $pesanan->id }}"
                                            title="Batalkan pesanan">
                                        <i class="fa fa-times"></i> Batalkan
                                    </button>
                                    
                                @elseif($unifiedStatus === 'processing')
                                    @if(!$hasResi)
                                        <button class="btn btn-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalInputResi{{ $pesanan->id }}"
                                                title="Input nomor resi dan kirim">
                                            <i class="fa fa-truck"></i> Kirim Pesanan
                                        </button>
                                    @else
                                        <button class="btn btn-primary btn-sm action-button" 
                                                data-action="ship_order" 
                                                data-id="{{ $pesanan->id }}"
                                                data-resi="{{ $pesanan->pengiriman->resi }}"
                                                title="Konfirmasi pengiriman">
                                            <i class="fa fa-truck"></i> Konfirmasi Kirim
                                        </button>
                                    @endif
                                    
                                    {{-- Tambahkan button cancel untuk pesanan yang sudah dibayar --}}
                                    <button class="btn btn-warning btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalCancelOrder{{ $pesanan->id }}"
                                            title="Batalkan pesanan (stok habis/alasan lain)">
                                        <i class="fa fa-times-circle"></i> Batalkan
                                    </button>
                                    
                                @elseif($unifiedStatus === 'shipped')
                                    <button class="btn btn-success btn-sm action-button" 
                                            data-action="complete_order" 
                                            data-id="{{ $pesanan->id }}"
                                            title="Selesaikan pesanan">
                                        <i class="fa fa-check-circle"></i> Selesaikan
                                    </button>
                                    <button class="btn btn-secondary btn-sm copy-resi" 
                                            data-resi="{{ $pesanan->pengiriman->resi }}"
                                            title="Salin nomor resi">
                                        <i class="fa fa-copy"></i> Resi
                                    </button>
                                    
                                @elseif($unifiedStatus === 'completed')
                                    <span class="badge bg-success text-white px-3 py-2">
                                        <i class="fa fa-check-circle"></i> Transaksi Selesai
                                    </span>
                                    
                                @elseif($unifiedStatus === 'canceled')
                                    <span class="badge bg-danger text-white px-3 py-2">
                                        <i class="fa fa-times-circle"></i> Dibatalkan
                                    </span>
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
                @if(config('app.env') === 'local' || config('app.debug') === true)
                <a href="{{ route('admin.pesanan.create-test') }}" class="btn btn-primary mt-2">
                    <i class="fas fa-vial"></i> Generate Test Data
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal untuk Detail Pesanan --}}
@if($pesanans && $pesanans->count() > 0)
@foreach ($pesanans as $pesanan)
{{-- Modal Detail --}}
<div class="modal fade" id="modalDetail{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detail Pesanan {{ $pesanan->merchant_ref }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <h6><strong><i class="fas fa-user-circle mr-2"></i>Informasi Pelanggan</strong></h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Nama</td>
                                <td>: {{ $pesanan->user->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>: {{ $pesanan->user->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>No. HP</td>
                                <td>: {{ $pesanan->user->nohp ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td>: {{ $pesanan->user->alamat ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-3">
                        <h6><strong><i class="fas fa-shipping-fast mr-2"></i>Informasi Pengiriman</strong></h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Penerima</td>
                                <td>: {{ $pesanan->nama_penerima ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Telepon</td>
                                <td>: {{ $pesanan->telepon_penerima ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td>: {{ $pesanan->alamat_pengiriman ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Catatan</td>
                                <td>: {{ $pesanan->catatan ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-3">
                        <h6><strong><i class="fas fa-credit-card mr-2"></i>Informasi Pembayaran</strong></h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Metode</td>
                                <td>: {{ $pesanan->pembayaran->metode ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>: <span class="badge bg-{{ $pesanan->pembayaran && in_array($pesanan->pembayaran->status, ['berhasil', 'dibayar']) ? 'success' : 'warning' }}">{{ ucfirst($pesanan->pembayaran->status ?? 'pending') }}</span></td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td>: <strong>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</strong></td>
                            </tr>
                            @if($pesanan->pembayaran && $pesanan->pembayaran->waktu_bayar)
                            <tr>
                                <td>Waktu Bayar</td>
                                <td>: {{ $pesanan->pembayaran->waktu_bayar->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <hr>

                <h6><strong><i class="fas fa-box mr-2"></i>Detail Produk</strong></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
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
                                    {{ $detail->produk->nama_produk ?? 'Produk tidak ditemukan' }}
                                    @if($detail->produk && $detail->produk->kategori)
                                    <span class="badge text-white ms-1" style="background-color: {{ $detail->produk->kategori->warna ?? '#6C757D' }}">{{ $detail->produk->kategori->nama_kategori }}</span>
                                    @endif
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
                                <td colspan="4" class="text-end">Ongkos Kirim ({{ $pesanan->pengiriman->kurir }} - {{ $pesanan->pengiriman->layanan }}):</td>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Input Resi --}}
@if($pesanan->pengiriman && !$pesanan->pengiriman->resi && in_array($pesanan->status, ['dibayar', 'berhasil']))
<div class="modal fade" id="modalInputResi{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Input Nomor Resi</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-input-resi" data-id="{{ $pesanan->id }}">
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
<div class="modal fade" id="modalCancelOrder{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Batalkan Pesanan</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-cancel-order" data-id="{{ $pesanan->id }}">
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

@endforeach
@endif

@endsection

@push('styles')
<style>
    /* Container styling */
    #content {
        width: 100%;
        min-width: 100%;
    }
    
    .card {
        width: 100%;
    }
    
    .card-body {
        width: 100%;
        overflow-x: auto;
    }
    
    /* Table responsiveness fix */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    #tabelPesanan {
        width: 100% !important;
        table-layout: auto;
    }
    
    .dataTables_wrapper {
        width: 100% !important;
    }
    
    .dataTables_scrollBody {
        overflow-x: auto !important;
    }
    
    /* Fix for zoom out */
    @media screen and (max-width: 1920px) {
        .table-responsive {
            min-width: 100%;
        }
    }
    
    /* Prevent table from being too wide on zoom out */
    .dataTable {
        max-width: 100% !important;
        width: 100% !important;
    }
    
    .gap-1 {
        gap: 0.25rem;
    }
    
    .table td {
        vertical-align: middle !important;
        white-space: nowrap;
    }
    
    .action-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .badge {
        font-size: 0.875rem;
    }
    
    /* DataTables custom styling */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        padding: 0.25rem 0.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table td, .table th {
            font-size: 0.8rem;
            padding: 0.3rem;
        }
        
        .btn-sm {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#tabelPesanan').DataTable({
        "language": {
            "lengthMenu": "Tampilkan _MENU_ pesanan per halaman",
            "zeroRecords": "Tidak ada pesanan ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada pesanan",
            "infoFiltered": "(difilter dari _MAX_ total pesanan)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        },
        "order": [[0, 'asc']], // Sort by number ascending (which reflects the server-side desc order)
        "pageLength": 10,
        "responsive": false, // Disable responsive to prevent auto-hiding columns
        "scrollX": true, // Enable horizontal scrolling
        "autoWidth": false, // Prevent auto width calculation
        "columnDefs": [
            { "width": "3%", "targets": 0 },
            { "width": "12%", "targets": 1 },
            { "width": "15%", "targets": 2 },
            { "width": "10%", "targets": 3 },
            { "width": "15%", "targets": 4 },
            { "width": "12%", "targets": 5 },
            { "width": "13%", "targets": 6 },
            { "width": "20%", "targets": 7 }
        ]
    });

    // Filter status functionality
    $('.filter-status').on('click', function(e) {
        e.preventDefault();
        const status = $(this).data('status');
        const text = $(this).data('text');
        
        $('#filterStatusButton').text('Filter Status: ' + text);
        
        if (status === 'all') {
            table.search('').draw();
        } else {
            // For multiple statuses separated by comma
            const statuses = status.split(',');
            const searchRegex = statuses.map(s => s.trim()).join('|');
            table.search(searchRegex, true, false).draw();
        }
    });

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
    $('.form-input-resi').on('submit', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const resi = $(this).find('input[name="resi"]').val();
        
        if (!resi) {
            Swal.fire('Error', 'Nomor resi harus diisi', 'error');
            return;
        }
        
        // Close modal first
        $('#modalInputResi' + id).modal('hide');
        
        // Update with ship action
        updateOrderStatus(id, 'ship_order', resi);
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
    
    // Handle form cancel order (untuk pesanan yang sudah dibayar)
    $('.form-cancel-order').on('submit', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const reason = $(this).find('select[name="reason"]').val();
        const note = $(this).find('textarea[name="note"]').val();
        
        if (!reason) {
            Swal.fire('Error', 'Alasan pembatalan harus dipilih', 'error');
            return;
        }
        
        // Close modal first
        $('#modalCancelOrder' + id).modal('hide');
        
        // Show confirmation
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
                // Update with cancel action and additional data
                updateOrderStatusWithReason(id, 'cancel_order', reason, note);
            }
        });
    });
    
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

    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('#alertSuccess, #alertError').fadeOut('slow');
    }, 5000);
});
</script>
@endpush