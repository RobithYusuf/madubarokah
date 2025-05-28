@extends('layouts.app')

@section('title', 'Laporan Transaksi')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Laporan Transaksi</h1>
        <p class="text-gray-500 mt-2 mb-0">Analisis dan monitoring transaksi penjualan</p>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('admin.laporan.penjualan') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-chart-line mr-1"></i>Laporan Penjualan
        </a>
        <a href="{{ route('admin.laporan.produk') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-box mr-1"></i>Laporan Produk
        </a>
        <a href="{{ route('admin.laporan.pelanggan') }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-users mr-1"></i>Laporan Pelanggan
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-2"></i>Filter Laporan
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.laporan.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $startDate }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_date">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $endDate }}" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status">Status Transaksi</label>
                        <select class="form-control" id="status" name="status">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="dibayar" {{ $status === 'dibayar' ? 'selected' : '' }}>Dibayar</option>
                            <option value="berhasil" {{ $status === 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                            <option value="dikirim" {{ $status === 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                            <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="batal" {{ $status === 'batal' ? 'selected' : '' }}>Batal</option>
                            <option value="gagal" {{ $status === 'gagal' ? 'selected' : '' }}>Gagal</option>
                            <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="payment_status">Status Pembayaran</label>
                        <select class="form-control" id="payment_status" name="payment_status">
                            <option value="all" {{ $paymentStatus === 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="pending" {{ $paymentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="berhasil" {{ $paymentStatus === 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                            <option value="dibayar" {{ $paymentStatus === 'dibayar' ? 'selected' : '' }}>Dibayar</option>
                            <option value="gagal" {{ $paymentStatus === 'gagal' ? 'selected' : '' }}>Gagal</option>
                            <option value="expired" {{ $paymentStatus === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="refund" {{ $paymentStatus === 'refund' ? 'selected' : '' }}>Refund</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="shipping_status">Status Pengiriman</label>
                        <select class="form-control" id="shipping_status" name="shipping_status">
                            <option value="all" {{ $shippingStatus === 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="menunggu_pembayaran" {{ $shippingStatus === 'menunggu_pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                            <option value="diproses" {{ $shippingStatus === 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="dikirim" {{ $shippingStatus === 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                            <option value="diterima" {{ $shippingStatus === 'diterima' ? 'selected' : '' }}>Diterima</option>
                            <option value="dibatalkan" {{ $shippingStatus === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Filter Data
                    </button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="resetFilter()">
                        <i class="fas fa-undo mr-2"></i>Reset Filter
                    </button>
                    <button type="button" class="btn btn-success ml-2" onclick="showPdfModal()">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Transaksi
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($summaryData['total_transaksi']) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Pendapatan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($summaryData['total_pendapatan'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Rata-rata Transaksi
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($summaryData['rata_rata_transaksi'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Transaksi Selesai
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($summaryData['transaksi_selesai']) }}
                        </div>
                        <div class="text-xs text-muted">
                            {{ $summaryData['total_transaksi'] > 0 ? number_format(($summaryData['transaksi_selesai'] / $summaryData['total_transaksi']) * 100, 1) : 0 }}% dari total
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Tren Penjualan Harian</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Distribusi Status</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-table mr-2"></i>Detail Transaksi
        </h6>
        <small class="text-muted">
            Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </small>
    </div>
    <div class="card-body">
        @if($transaksi && $transaksi->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tabelLaporan">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="12%">ID & Referensi</th>
                        <th width="12%">Pelanggan</th>
                        <th width="10%">Tanggal</th>
                        <th width="10%">Total</th>
                        <th width="10%">Status Transaksi</th>
                        <th width="12%">Status Pembayaran</th>
                        <th width="12%">Status Pengiriman</th>
                        <th width="10%">Metode Bayar</th>
                        <th width="7%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksi as $index => $item)
                    <tr>
                        <td>{{ $transaksi->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <small class="text-muted">ID: #{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</small>
                                @if($item->merchant_ref)
                                <small class="font-weight-bold">{{ $item->merchant_ref }}</small>
                                @endif
                                @if($item->tripay_reference)
                                <small class="text-info">{{ $item->tripay_reference }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">{{ $item->user->nama ?? '-' }}</span>
                                <small class="text-muted">{{ $item->user->username ?? '-' }}</small>
                                @if($item->nama_penerima && $item->nama_penerima !== $item->user->nama)
                                <small class="text-info">Penerima: {{ $item->nama_penerima }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span>{{ $item->tanggal_transaksi ? $item->tanggal_transaksi->format('d/m/Y') : '-' }}</span>
                                <small class="text-muted">{{ $item->tanggal_transaksi ? $item->tanggal_transaksi->format('H:i') : '' }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</span>
                                @if($item->pengiriman && $item->pengiriman->biaya > 0)
                                <small class="text-muted">+Ongkir: {{ number_format($item->pengiriman->biaya, 0, ',', '.') }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ 
                                $item->status === 'selesai' ? 'success' : 
                                ($item->status === 'dikirim' ? 'info' : 
                                ($item->status === 'dibayar' || $item->status === 'berhasil' ? 'warning' : 
                                ($item->status === 'pending' ? 'secondary' : 'danger'))) 
                            }} text-white">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td>
                            @if($item->pembayaran)
                            <div class="d-flex flex-column">
                                <span class="badge bg-{{ 
                                    in_array($item->pembayaran->status, ['berhasil', 'dibayar']) ? 'success' : 
                                    ($item->pembayaran->status === 'pending' ? 'warning' : 
                                    ($item->pembayaran->status === 'refund' ? 'info' : 'danger')) 
                                }} text-white">
                                    {{ ucfirst($item->pembayaran->status) }}
                                </span>
                                @if($item->pembayaran->waktu_bayar)
                                <small class="text-success mt-1">{{ $item->pembayaran->waktu_bayar->format('d/m H:i') }}</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->pengiriman)
                            <div class="d-flex flex-column">
                                <span class="badge bg-{{ 
                                    $item->pengiriman->status === 'diterima' ? 'success' : 
                                    ($item->pengiriman->status === 'dikirim' ? 'info' : 
                                    ($item->pengiriman->status === 'diproses' ? 'warning' : 
                                    ($item->pengiriman->status === 'dibatalkan' ? 'danger' : 'secondary'))) 
                                }} text-white">
                                    {{ ucfirst(str_replace('_', ' ', $item->pengiriman->status)) }}
                                </span>
                                <small class="text-muted mt-1">{{ $item->pengiriman->kurir ?? '-' }}</small>
                                @if($item->pengiriman->resi)
                                <small class="text-info">{{ $item->pengiriman->resi }}</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->pembayaran)
                            <div class="d-flex flex-column">
                                <small class="font-weight-bold">{{ $item->pembayaran->metode ?? '-' }}</small>
                                @if($item->pembayaran->payment_code)
                                <small class="text-info">{{ $item->pembayaran->payment_code }}</small>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.pesanan.show', $item->id) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="dataTables_info">
                    Menampilkan {{ $transaksi->firstItem() }} sampai {{ $transaksi->lastItem() }} 
                    dari {{ $transaksi->total() }} data
                </div>
            </div>
            <div class="col-md-6">
                <div class="dataTables_paginate">
                    {{ $transaksi->links() }}
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="fas fa-chart-line fa-3x mb-3"></i>
                <h5>Tidak Ada Data Transaksi</h5>
                <p>Tidak ada transaksi ditemukan untuk periode dan filter yang dipilih</p>
                <button type="button" class="btn btn-primary" onclick="resetFilter()">
                    <i class="fas fa-undo mr-2"></i>Reset Filter
                </button>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Export PDF -->
<div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="pdfModalLabel">
                    <i class="fas fa-file-pdf mr-2"></i>Export Laporan PDF
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="pdfExportForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pdf_start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="pdf_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pdf_end_date">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="pdf_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pdf_status">Status Transaksi</label>
                                <select class="form-control" id="pdf_status" name="status">
                                    <option value="all">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="dibayar">Dibayar</option>
                                    <option value="berhasil">Berhasil</option>
                                    <option value="dikirim">Dikirim</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="batal">Batal</option>
                                    <option value="gagal">Gagal</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pdf_payment_status">Status Pembayaran</label>
                                <select class="form-control" id="pdf_payment_status" name="payment_status">
                                    <option value="all">Semua</option>
                                    <option value="pending">Pending</option>
                                    <option value="berhasil">Berhasil</option>
                                    <option value="dibayar">Dibayar</option>
                                    <option value="gagal">Gagal</option>
                                    <option value="expired">Expired</option>
                                    <option value="refund">Refund</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pdf_shipping_status">Status Pengiriman</label>
                                <select class="form-control" id="pdf_shipping_status" name="shipping_status">
                                    <option value="all">Semua</option>
                                    <option value="menunggu_pembayaran">Menunggu Pembayaran</option>
                                    <option value="diproses">Diproses</option>
                                    <option value="dikirim">Dikirim</option>
                                    <option value="diterima">Diterima</option>
                                    <option value="dibatalkan">Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Info:</strong> Laporan akan dibuka di tab baru dan dapat langsung dicetak ke PDF menggunakan browser.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="button" class="btn btn-success" onclick="generatePdfReport()">
                    <i class="fas fa-file-pdf mr-2"></i>Buat Laporan PDF
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.chart-area {
    position: relative;
    height: 300px;
    width: 100%;
}

.chart-pie {
    position: relative;
    height: 200px;
    width: 100%;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
}

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

.btn-group-sm > .btn {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .badge {
        font-size: 0.65rem;
        padding: 0.25rem 0.4rem;
    }
}
</style>
@endpush

@push('scripts')
<!-- Chart.js -->

<script>
// Define chart data globally to avoid JSON issues
window.chartDataSales = {!! json_encode($chartData['daily_sales'] ?? []) !!};
window.statusDistribution = {!! json_encode($summaryData['status_distribution'] ?? []) !!};

$(document).ready(function() {
    // Initialize DataTable
    if ($('#tabelLaporan').length && $('#tabelLaporan tbody tr').length > 0) {
        $('#tabelLaporan').DataTable({
            responsive: true,
            paging: false,
            searching: true,
            ordering: true,
            info: false,
            autoWidth: false,
            columnDefs: [
                { 
                    targets: [0, 9], // No dan Aksi
                    orderable: false
                }
            ],
            language: {
                "search": "Cari:",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "emptyTable": "Tidak ada data tersedia"
            }
        });
    }

    // Initialize Charts with delay to ensure DOM is ready
    setTimeout(function() {
        initializeCharts();
    }, 100);
});

function initializeCharts() {
    try {
        // Sales Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx && window.chartDataSales && window.chartDataSales.length > 0) {
            const chartData = window.chartDataSales;

            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: chartData.map(function(item) {
                        if (item.date) {
                            const date = new Date(item.date);
                            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' });
                        }
                        return '';
                    }),
                    datasets: [{
                        label: 'Penjualan Harian',
                        data: chartData.map(function(item) {
                            return parseFloat(item.total || 0);
                        }),
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4,
                            hoverRadius: 6
                        }
                    }
                }
            });
        } else {
            console.log('Sales chart data not available or empty');
            // Tambahkan fallback jika elemen ada tapi data kosong
            if (salesCtx) {
                salesCtx.parentNode.innerHTML =
                    '<div class="text-center py-4"><i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i><h6 class="text-muted">Tidak ada data untuk Tren Penjualan.</h6></div>';
            }
        }

        // Status Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && window.statusDistribution && window.statusDistribution.length > 0) {
            const statusData = window.statusDistribution;

            const colors = {
                'pending': '#6c757d',
                'dibayar': '#ffc107',
                'berhasil': '#28a745', // Digabung dengan selesai
                'dikirim': '#17a2b8',
                'selesai': '#28a745',
                'batal': '#dc3545',  // Digabung dengan gagal
                'gagal': '#dc3545',
                'expired': '#fd7e14'
            };

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusData.map(function(item) {
                        if (item.status) {
                            return item.status.charAt(0).toUpperCase() + item.status.slice(1);
                        }
                        return '';
                    }),
                    datasets: [{
                        data: statusData.map(function(item) {
                            return parseInt(item.count || 0);
                        }),
                        backgroundColor: statusData.map(function(item) {
                            return colors[item.status] || '#6c757d'; // Default color
                        }),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    }
                }
            });
        } else {
            console.log('Status chart data not available or empty');
            // Tambahkan fallback jika elemen ada tapi data kosong
            if (statusCtx) {
                statusCtx.parentNode.innerHTML =
                    '<div class="text-center py-4"><i class="fas fa-chart-pie fa-3x text-gray-300 mb-3"></i><h6 class="text-muted">Tidak ada data untuk Distribusi Status.</h6></div>';
            }
        }
    } catch (error) {
        console.error('Error initializing charts:', error);
        // Tampilkan pesan fallback jika charts gagal dimuat karena error
        if (document.getElementById('salesChart')) {
            document.getElementById('salesChart').parentNode.innerHTML =
                '<div class="text-center py-4"><i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i><h6 class="text-muted">Chart Tren Penjualan tidak dapat dimuat.</h6></div>';
        }
        if (document.getElementById('statusChart')) {
            document.getElementById('statusChart').parentNode.innerHTML =
                '<div class="text-center py-4"><i class="fas fa-chart-pie fa-3x text-gray-300 mb-3"></i><h6 class="text-muted">Chart Distribusi Status tidak dapat dimuat.</h6></div>';
        }
    }
}

function resetFilter() {
    try {
        document.getElementById('start_date').value = '{{ Carbon\Carbon::now()->subDays(30)->format('Y-m-d') }}';
        document.getElementById('end_date').value = '{{ Carbon\Carbon::now()->format('Y-m-d') }}';
        document.getElementById('status').value = 'all';
        document.getElementById('payment_status').value = 'all';
        document.getElementById('shipping_status').value = 'all';
        document.getElementById('filterForm').submit();
    } catch (error) {
        console.error('Error resetting filter:', error);
        alert('Terjadi kesalahan saat reset filter');
    }
}

function showPdfModal() {
    // Set default values from current filter
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    const paymentStatus = document.getElementById('payment_status').value;
    const shippingStatus = document.getElementById('shipping_status').value;
    
    // Set values in modal
    document.getElementById('pdf_start_date').value = startDate;
    document.getElementById('pdf_end_date').value = endDate;
    document.getElementById('pdf_status').value = status;
    document.getElementById('pdf_payment_status').value = paymentStatus;
    document.getElementById('pdf_shipping_status').value = shippingStatus;
    
    // Show modal
    $('#pdfModal').modal('show');
}

function generatePdfReport() {
    try {
        const startDate = document.getElementById('pdf_start_date').value;
        const endDate = document.getElementById('pdf_end_date').value;
        const status = document.getElementById('pdf_status').value;
        const paymentStatus = document.getElementById('pdf_payment_status').value;
        const shippingStatus = document.getElementById('pdf_shipping_status').value;
        
        if (!startDate || !endDate) {
            alert('Harap pilih tanggal mulai dan tanggal akhir');
            return;
        }
        
        const params = new URLSearchParams({
            type: 'transaksi',
            start_date: startDate,
            end_date: endDate,
            status: status,
            payment_status: paymentStatus,
            shipping_status: shippingStatus,
            print: '1'
        });
        
        const pdfUrl = '{{ route("admin.laporan.pdf") }}';
        window.open(pdfUrl + '?' + params.toString(), '_blank');
        
        // Close modal
        $('#pdfModal').modal('hide');
        
        // Show notification
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Laporan PDF Dibuat',
                text: 'Laporan telah dibuka di tab baru',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Terjadi kesalahan saat membuat laporan PDF');
    }
}

// Auto submit form when filter changes
$(document).ready(function() {
    $('#filterForm select, #filterForm input[type="date"]').on('change', function() {
        try {
            $('#filterForm').submit();
        } catch (error) {
            console.error('Error submitting form:', error);
        }
    });
});

// Debug function to check data
function debugChartData() {
    console.log('Sales Data:', window.chartDataSales);
    console.log('Status Data:', window.statusDistribution);
}

// Initialize modal events
$(document).ready(function() {
    // Set date limits
    const today = new Date().toISOString().split('T')[0];
    const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    
    document.getElementById('pdf_start_date').setAttribute('max', today);
    document.getElementById('pdf_end_date').setAttribute('max', today);
    
    // Validate date range in modal
    $('#pdf_start_date, #pdf_end_date').on('change', function() {
        const startDate = document.getElementById('pdf_start_date').value;
        const endDate = document.getElementById('pdf_end_date').value;
        
        if (startDate && endDate && startDate > endDate) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            this.value = '';
        }
    });
});
</script>
@endpush

@endsection