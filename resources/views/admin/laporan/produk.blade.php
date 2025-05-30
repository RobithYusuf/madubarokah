@extends('layouts.app')

@section('title', 'Laporan Produk')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Laporan Produk</h1>
        <p class="text-gray-500 mt-2 mb-0">Analisis performa dan penjualan produk</p>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Laporan
        </a>
        <a href="{{ route('admin.produk.index') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-cog mr-1"></i>Kelola Produk
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
        <form method="GET" action="{{ route('admin.laporan.produk') }}" id="filterForm">
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select class="form-control" id="kategori_id" name="kategori_id">
                            <option value="all" {{ $kategoriId === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                            @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ $kategoriId == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-block">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-2"></i>Filter
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

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Produk
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $produkData->total() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
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
                            Produk Terjual
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $produkData->where('total_terjual', '>', 0)->count() }}
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
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Stok
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($produkData->sum('stok')) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-warehouse fa-2x text-gray-300"></i>
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
                            Total Penjualan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($produkData->sum('total_terjual')) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-table mr-2"></i>Detail Produk
        </h6>
        <small class="text-muted">
            Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </small>
    </div>
    <div class="card-body">
        @if($produkData && $produkData->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tabelProduk">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Nama Produk</th>
                        <th width="12%">Kategori</th>
                        <th width="10%">Harga</th>
                        <th width="8%">Stok</th>
                        <th width="10%">Terjual</th>
                        <th width="8%">Transaksi</th>
                        <th width="15%">Total Pendapatan</th>
                        <th width="7%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produkData as $index => $produk)
                    @php
                        $persentaseTerjual = $produk->stok > 0 ? ($produk->total_terjual / ($produk->stok + $produk->total_terjual)) * 100 : 0;
                        $statusStok = $produk->stok <= 5 ? 'danger' : ($produk->stok <= 20 ? 'warning' : 'success');
                        $statusPenjualan = $produk->total_terjual > 0 ? 'success' : 'secondary';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">{{ $produk->nama_produk }}</span>
                                @if($produk->total_terjual > 0)
                                <small class="text-success">
                                    <i class="fas fa-fire"></i> {{ number_format($persentaseTerjual, 1) }}% terjual
                                </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($produk->nama_kategori)
                            <span class="badge text-white" 
                                  style="background-color: {{ $produk->warna ?? '#6C757D' }};">
                                {{ $produk->nama_kategori }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="font-weight-bold">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $statusStok }}">
                                {{ number_format($produk->stok) }}
                            </span>
                            @if($produk->stok <= 5)
                            <small class="text-danger d-block">
                                <i class="fas fa-exclamation-triangle"></i> Stok rendah
                            </small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold text-{{ $statusPenjualan }}">
                                    {{ number_format($produk->total_terjual) }}
                                </span>
                                @if($produk->total_terjual > 0 && $produk->jumlah_transaksi > 0)
                                <small class="text-muted">
                                    ~{{ number_format($produk->total_terjual / $produk->jumlah_transaksi, 1) }}/transaksi
                                </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ number_format($produk->jumlah_transaksi) }}</span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold text-success">
                                    Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}
                                </span>
                                @if($produk->total_terjual > 0)
                                <small class="text-muted">
                                    ~Rp {{ number_format($produk->total_pendapatan / $produk->total_terjual, 0, ',', '.') }}/unit
                                </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                @if($produk->total_terjual > 0)
                                <span class="badge badge-success">Aktif</span>
                                @else
                                <span class="badge badge-secondary">Belum Terjual</span>
                                @endif
                                @if($produk->stok <= 5)
                                <span class="badge badge-danger mt-1">Stok Rendah</span>
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
                <i class="fas fa-box fa-3x mb-3"></i>
                <h5>Tidak Ada Data Produk</h5>
                <p>Tidak ada produk ditemukan untuk filter yang dipilih</p>
                <button type="button" class="btn btn-primary" onclick="resetFilter()">
                    <i class="fas fa-undo mr-2"></i>Reset Filter
                </button>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
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
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($('#tabelProduk').length && $('#tabelProduk tbody tr').length > 0) {
        $('#tabelProduk').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            columnDefs: [
                { 
                    targets: [0], // No
                    orderable: false
                },
                {
                    targets: [5, 7], // Terjual, Total Pendapatan
                    orderSequence: ['desc', 'asc']
                }
            ],
            order: [[5, 'desc']], // Sort by total terjual
            language: {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Tidak ada produk yang ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "search": "Cari Produk:",
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
    document.getElementById('kategori_id').value = 'all';
    document.getElementById('filterForm').submit();
}

// Auto submit form when filter changes
$('#filterForm select, #filterForm input[type="date"]').on('change', function() {
    $('#filterForm').submit();
});
</script>
@endpush

@endsection