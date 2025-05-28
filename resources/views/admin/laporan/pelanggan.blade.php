@extends('layouts.app')

@section('title', 'Laporan Pelanggan')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Laporan Pelanggan</h1>
        <p class="text-gray-500 mt-2 mb-0">Analisis perilaku dan segmentasi pelanggan</p>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Laporan
        </a>
        <a href="{{ route('admin.user.index') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-users mr-1"></i>Kelola User
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-calendar mr-2"></i>Periode Analisis
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.laporan.pelanggan') }}" id="filterForm">
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

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Pelanggan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $pelangganData->total() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            Pelanggan Aktif
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $pelangganData->where('jumlah_transaksi', '>', 0)->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                            Total Belanja
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($pelangganData->sum('total_belanja'), 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
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
                            Rata-rata Belanja
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            @php 
                                $totalPelangganAktif = $pelangganData->where('jumlah_transaksi', '>', 0)->count();
                                $avgBelanja = $totalPelangganAktif > 0 ? $pelangganData->sum('total_belanja') / $totalPelangganAktif : 0;
                            @endphp
                            Rp {{ number_format($avgBelanja, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
            <i class="fas fa-table mr-2"></i>Data Pelanggan
        </h6>
        <small class="text-muted">
            Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </small>
    </div>
    <div class="card-body">
        @if($pelangganData && $pelangganData->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tabelPelanggan">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Informasi Pelanggan</th>
                        <th width="15%">Kontak</th>
                        <th width="10%">Jumlah Transaksi</th>
                        <th width="15%">Total Belanja</th>
                        <th width="15%">Rata-rata Belanja</th>
                        <th width="12%">Transaksi Terakhir</th>
                        <th width="8%">Segmen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pelangganData as $index => $pelanggan)
                    @php
                        // Segmentasi pelanggan berdasarkan total belanja
                        if ($pelanggan->total_belanja >= 5000000) {
                            $segmen = ['text' => 'VIP', 'class' => 'danger'];
                        } elseif ($pelanggan->total_belanja >= 2000000) {
                            $segmen = ['text' => 'Premium', 'class' => 'warning'];
                        } elseif ($pelanggan->total_belanja >= 500000) {
                            $segmen = ['text' => 'Regular', 'class' => 'success'];
                        } elseif ($pelanggan->jumlah_transaksi > 0) {
                            $segmen = ['text' => 'Basic', 'class' => 'info'];
                        } else {
                            $segmen = ['text' => 'Inactive', 'class' => 'secondary'];
                        }
                        
                        // Status aktivitas
                        $statusAktivitas = $pelanggan->transaksi_terakhir ? 
                            (Carbon\Carbon::parse($pelanggan->transaksi_terakhir)->diffInDays(now()) <= 30 ? 'Aktif' : 'Tidak Aktif') : 
                            'Belum Transaksi';
                    @endphp
                    <tr>
                        <td>{{ $pelangganData->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">{{ $pelanggan->nama }}</span>
                                <small class="text-muted">@{{ $pelanggan->username }}</small>
                                @if($statusAktivitas === 'Aktif')
                                <small class="text-success">
                                    <i class="fas fa-circle"></i> {{ $statusAktivitas }}
                                </small>
                                @elseif($statusAktivitas === 'Tidak Aktif')
                                <small class="text-warning">
                                    <i class="fas fa-circle"></i> {{ $statusAktivitas }}
                                </small>
                                @else
                                <small class="text-muted">
                                    <i class="fas fa-circle"></i> {{ $statusAktivitas }}
                                </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                @if($pelanggan->email)
                                <small class="text-muted">
                                    <i class="fas fa-envelope"></i> {{ $pelanggan->email }}
                                </small>
                                @endif
                                @if($pelanggan->nohp)
                                <small class="text-muted">
                                    <i class="fas fa-phone"></i> {{ $pelanggan->nohp }}
                                </small>
                                @endif
                                @if(!$pelanggan->email && !$pelanggan->nohp)
                                <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column text-center">
                                <span class="badge badge-info badge-lg">
                                    {{ number_format($pelanggan->jumlah_transaksi) }}
                                </span>
                                @if($pelanggan->jumlah_transaksi > 0)
                                <small class="text-muted mt-1">transaksi</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold text-success">
                                    Rp {{ number_format($pelanggan->total_belanja, 0, ',', '.') }}
                                </span>
                                @if($pelanggan->total_belanja > 0)
                                <small class="text-muted">
                                    {{ number_format(($pelanggan->total_belanja / $pelangganData->sum('total_belanja')) * 100, 1) }}% dari total
                                </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="font-weight-bold">
                                Rp {{ number_format($pelanggan->rata_rata_belanja, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @if($pelanggan->transaksi_terakhir)
                            <div class="d-flex flex-column">
                                <span>{{ Carbon\Carbon::parse($pelanggan->transaksi_terakhir)->format('d/m/Y') }}</span>
                                <small class="text-muted">
                                    {{ Carbon\Carbon::parse($pelanggan->transaksi_terakhir)->diffForHumans() }}
                                </small>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $segmen['class'] }}">
                                {{ $segmen['text'] }}
                            </span>
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
                    Menampilkan {{ $pelangganData->firstItem() }} sampai {{ $pelangganData->lastItem() }} 
                    dari {{ $pelangganData->total() }} pelanggan
                </div>
            </div>
            <div class="col-md-6">
                <div class="dataTables_paginate">
                    {{ $pelangganData->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h5>Tidak Ada Data Pelanggan</h5>
                <p>Tidak ada data pelanggan untuk periode yang dipilih</p>
                <button type="button" class="btn btn-primary" onclick="resetFilter()">
                    <i class="fas fa-undo mr-2"></i>Reset Filter
                </button>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Segmentasi Card -->
@if($pelangganData && $pelangganData->count() > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-chart-pie mr-2"></i>Segmentasi Pelanggan
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            @php
                $vip = $pelangganData->where('total_belanja', '>=', 5000000)->count();
                $premium = $pelangganData->where('total_belanja', '>=', 2000000)->where('total_belanja', '<', 5000000)->count();
                $regular = $pelangganData->where('total_belanja', '>=', 500000)->where('total_belanja', '<', 2000000)->count();
                $basic = $pelangganData->where('jumlah_transaksi', '>', 0)->where('total_belanja', '<', 500000)->count();
                $inactive = $pelangganData->where('jumlah_transaksi', 0)->count();
            @endphp
            
            <div class="col-md-2">
                <div class="text-center">
                    <div class="badge badge-danger badge-lg mb-2">{{ $vip }}</div>
                    <h6 class="text-danger">VIP</h6>
                    <small class="text-muted">&ge; Rp 5jt</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="badge badge-warning badge-lg mb-2">{{ $premium }}</div>
                    <h6 class="text-warning">Premium</h6>
                    <small class="text-muted">Rp 2jt - 5jt</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="badge badge-success badge-lg mb-2">{{ $regular }}</div>
                    <h6 class="text-success">Regular</h6>
                    <small class="text-muted">Rp 500rb - 2jt</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="badge badge-info badge-lg mb-2">{{ $basic }}</div>
                    <h6 class="text-info">Basic</h6>
                    <small class="text-muted">&lt; Rp 500rb</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="badge badge-secondary badge-lg mb-2">{{ $inactive }}</div>
                    <h6 class="text-secondary">Inactive</h6>
                    <small class="text-muted">Belum transaksi</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
.badge-lg {
    font-size: 1.2rem;
    padding: 0.5rem 0.8rem;
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
    if ($('#tabelPelanggan').length && $('#tabelPelanggan tbody tr').length > 0) {
        $('#tabelPelanggan').DataTable({
            responsive: true,
            paging: false,
            searching: true,
            ordering: true,
            info: false,
            autoWidth: false,
            columnDefs: [
                { 
                    targets: [0], // No
                    orderable: false
                }
            ],
            order: [[4, 'desc']], // Sort by total belanja
            language: {
                "search": "Cari Pelanggan:",
                "zeroRecords": "Tidak ada pelanggan yang ditemukan",
                "emptyTable": "Tidak ada data tersedia"
            }
        });
    }
});

function resetFilter() {
    document.getElementById('start_date').value = '{{ Carbon\Carbon::now()->subDays(30)->format('Y-m-d') }}';
    document.getElementById('end_date').value = '{{ Carbon\Carbon::now()->format('Y-m-d') }}';
    document.getElementById('filterForm').submit();
}

// Auto submit form when filter changes
$('#filterForm input[type="date"]').on('change', function() {
    $('#filterForm').submit();
});
</script>
@endpush

@endsection