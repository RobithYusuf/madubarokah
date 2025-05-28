@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Laporan Penjualan</h1>
        <p class="text-gray-500 mt-2 mb-0">Analisis performa penjualan produk dan kategori</p>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Laporan
        </a>
        <button type="button" class="btn btn-success btn-sm" onclick="exportPenjualan()">
            <i class="fas fa-download mr-1"></i>Export Excel
        </button>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-calendar mr-2"></i>Periode Laporan
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.laporan.penjualan') }}" id="filterForm">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $startDate }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="end_date">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $endDate }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-block">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-2"></i>Filter Data
                            </button>
                            <button type="button" class="btn btn-secondary ml-2" onclick="resetFilter()">
                                <i class="fas fa-undo mr-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Penjualan Harian -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-chart-line mr-2"></i>Penjualan Harian
        </h6>
    </div>
    <div class="card-body">
        @if($penjualanHarian && $penjualanHarian->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tabelPenjualanHarian">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah Transaksi</th>
                        <th>Total Penjualan</th>
                        <th>Rata-rata per Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalTransaksi = 0;
                        $totalPenjualan = 0;
                    @endphp
                    @foreach($penjualanHarian as $item)
                    @php 
                        $totalTransaksi += $item->jumlah_transaksi;
                        $totalPenjualan += $item->total_penjualan;
                    @endphp
                    <tr>
                        <td>{{ Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ number_format($item->jumlah_transaksi) }}</td>
                        <td>Rp {{ number_format($item->total_penjualan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->rata_rata, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light font-weight-bold">
                        <th>Total</th>
                        <th>{{ number_format($totalTransaksi) }}</th>
                        <th>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</th>
                        <th>Rp {{ $totalTransaksi > 0 ? number_format($totalPenjualan / $totalTransaksi, 0, ',', '.') : 0 }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
            <h5 class="text-muted">Tidak Ada Data Penjualan</h5>
            <p class="text-muted">Tidak ada data penjualan untuk periode yang dipilih</p>
        </div>
        @endif
    </div>
</div>

<div class="row">
    <!-- Top Produk -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-trophy mr-2"></i>Top 10 Produk Terlaris
                </h6>
            </div>
            <div class="card-body">
                @if($topProduk && $topProduk->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Produk</th>
                                <th width="15%">Kategori</th>
                                <th width="12%">Terjual</th>
                                <th width="20%">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProduk as $index => $produk)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $produk->nama_produk }}</div>
                                </td>
                                <td>
                                    <span class="badge text-white" 
                                          style="background-color: {{ $produk->warna ?? '#6C757D' }};">
                                        {{ $produk->nama_kategori }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ number_format($produk->total_terjual) }}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-success">
                                        Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-box fa-3x text-gray-300 mb-3"></i>
                    <h6 class="text-muted">Tidak Ada Data Produk</h6>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Kategori -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-tags mr-2"></i>Performa Kategori
                </h6>
            </div>
            <div class="card-body">
                @if($topKategori && $topKategori->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th width="15%">Produk</th>
                                <th width="15%">Terjual</th>
                                <th width="25%">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topKategori as $kategori)
                            <tr>
                                <td>
                                    <span class="badge text-white d-inline-block mb-1" 
                                          style="background-color: {{ $kategori->warna ?? '#6C757D' }};">
                                        {{ $kategori->nama_kategori }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ number_format($kategori->jumlah_produk) }}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ number_format($kategori->total_terjual) }}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-success">
                                        Rp {{ number_format($kategori->total_pendapatan, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-tags fa-3x text-gray-300 mb-3"></i>
                    <h6 class="text-muted">Tidak Ada Data Kategori</h6>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($('#tabelPenjualanHarian').length && $('#tabelPenjualanHarian tbody tr').length > 0) {
        $('#tabelPenjualanHarian').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            lengthMenu: [10, 25, 50],
            pageLength: 10,
            order: [[0, 'desc']], // Sort by date
            language: {
                "lengthMenu": "Tampilkan *MENU* data per halaman",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan *START* sampai *END* dari *TOTAL* data",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari *MAX* total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    }
});

function resetFilter() {
    document.getElementById('start_date').value = '{{ Carbon\Carbon::now()->subDays(30)->format('Y-m-d') }}';
    document.getElementById('end_date').value = '{{ Carbon\Carbon::now()->format('Y-m-d') }}';
    document.getElementById('filterForm').submit();
}

function exportPenjualan() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate
    });
    
    window.open(`{{ route('admin.laporan.export.penjualan') }}?${params}`, '_blank');
}

// Auto submit form when filter changes
$('#filterForm input[type="date"]').on('change', function() {
    $('#filterForm').submit();
});
</script>
@endpush

@endsection