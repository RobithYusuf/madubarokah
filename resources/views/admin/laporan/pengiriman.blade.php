@extends('layouts.app')

@section('title', 'Laporan Pengiriman')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Laporan Pengiriman</h1>
        <p class="text-gray-500 mt-2 mb-0">Analisis performa kurir dan biaya pengiriman</p>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Laporan
        </a>
        <a href="{{ route('admin.shipping.index') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-shipping-fast mr-1"></i>Kelola Pengiriman
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
        <form method="GET" action="{{ route('admin.laporan.pengiriman') }}" id="filterForm">
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
                            Total Pengiriman
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $pengirimanData->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
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
                            Total Biaya Kirim
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ number_format($pengirimanData->sum('total_biaya'), 0, ',', '.') }}
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
                            Rata-rata Biaya
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Rp {{ $pengirimanData->count() > 0 ? number_format($pengirimanData->avg('rata_rata_biaya'), 0, ',', '.') : 0 }}
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
                            Total Berat
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($pengirimanData->sum('total_berat') / 1000, 1) }} kg
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-weight fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Performa Kurir -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-truck mr-2"></i>Performa Kurir & Layanan
                </h6>
            </div>
            <div class="card-body">
                @if($pengirimanData && $pengirimanData->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tabelKurir">
                        <thead>
                            <tr>
                                <th width="15%">Kurir</th>
                                <th width="20%">Layanan</th>
                                <th width="12%">Penggunaan</th>
                                <th width="18%">Total Biaya</th>
                                <th width="15%">Rata-rata Biaya</th>
                                <th width="12%">Total Berat</th>
                                <th width="8%">Popularitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengirimanData as $data)
                            @php
                                $totalPenggunaan = $pengirimanData->sum('jumlah_penggunaan');
                                $popularitas = $totalPenggunaan > 0 ? ($data->jumlah_penggunaan / $totalPenggunaan) * 100 : 0;
                                
                                // Determine badge color for courier
                                $kurirColors = [
                                    'jne' => 'primary',
                                    'tiki' => 'success', 
                                    'pos' => 'warning',
                                    'j&t' => 'danger',
                                    'sicepat' => 'info',
                                    'anteraja' => 'dark'
                                ];
                                $badgeColor = $kurirColors[strtolower($data->kurir)] ?? 'secondary';
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge badge-{{ $badgeColor }}">
                                        {{ strtoupper($data->kurir) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ $data->layanan }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold">{{ number_format($data->jumlah_penggunaan) }}</span>
                                        <small class="text-muted">kali digunakan</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-success">
                                            Rp {{ number_format($data->total_biaya, 0, ',', '.') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ number_format(($data->total_biaya / $pengirimanData->sum('total_biaya')) * 100, 1) }}% dari total
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-bold">
                                        Rp {{ number_format($data->rata_rata_biaya, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold">{{ number_format($data->total_berat / 1000, 1) }} kg</span>
                                        <small class="text-muted">{{ number_format($data->total_berat) }}g</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold">{{ number_format($popularitas, 1) }}%</span>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-{{ $badgeColor }}" 
                                                 style="width: {{ $popularitas }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-truck fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Data Pengiriman</h5>
                    <p class="text-muted">Tidak ada data pengiriman untuk periode yang dipilih</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Pengiriman -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie mr-2"></i>Status Pengiriman
                </h6>
            </div>
            <div class="card-body">
                @if($statusPengiriman && $statusPengiriman->count() > 0)
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4">
                    @foreach($statusPengiriman as $status)
                    @php
                        $statusColors = [
                            'menunggu_pembayaran' => 'secondary',
                            'diproses' => 'warning',
                            'dikirim' => 'info',
                            'diterima' => 'success',
                            'dibatalkan' => 'danger'
                        ];
                        $badgeColor = $statusColors[$status->status] ?? 'secondary';
                        $percentage = ($status->jumlah / $statusPengiriman->sum('jumlah')) * 100;
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge badge-{{ $badgeColor }}">
                                {{ ucfirst(str_replace('_', ' ', $status->status)) }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="font-weight-bold">{{ number_format($status->jumlah) }}</span>
                            <small class="text-muted">({{ number_format($percentage, 1) }}%)</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-pie fa-3x text-gray-300 mb-3"></i>
                    <h6 class="text-muted">Tidak Ada Data Status</h6>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Chart Biaya vs Penggunaan -->
@if($pengirimanData && $pengirimanData->count() > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-chart-bar mr-2"></i>Analisis Biaya vs Penggunaan Kurir
        </h6>
    </div>
    <div class="card-body">
        <div class="chart-area">
            <canvas id="costUsageChart"></canvas>
        </div>
    </div>
</div>
@endif

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

.progress {
    background-color: #e9ecef;
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($('#tabelKurir').length && $('#tabelKurir tbody tr').length > 0) {
        $('#tabelKurir').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            lengthMenu: [10, 25, 50],
            pageLength: 10,
            order: [[2, 'desc']], // Sort by penggunaan
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

    // Initialize Charts
    initializeCharts();
});

function initializeCharts() {
    // Status Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = @json($statusPengiriman);
        
        const colors = {
            'menunggu_pembayaran': '#6c757d',
            'diproses': '#ffc107',
            'dikirim': '#17a2b8',
            'diterima': '#28a745',
            'dibatalkan': '#dc3545'
        };

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())),
                datasets: [{
                    data: statusData.map(item => item.jumlah),
                    backgroundColor: statusData.map(item => colors[item.status] || '#6c757d'),
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
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }

    // Cost vs Usage Chart
    const costUsageCtx = document.getElementById('costUsageChart');
    if (costUsageCtx) {
        const pengirimanData = @json($pengirimanData);
        
        new Chart(costUsageCtx, {
            type: 'bar',
            data: {
                labels: pengirimanData.map(item => `${item.kurir.toUpperCase()} - ${item.layanan}`),
                datasets: [{
                    label: 'Jumlah Penggunaan',
                    data: pengirimanData.map(item => item.jumlah_penggunaan),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Rata-rata Biaya (Ribuan Rp)',
                    data: pengirimanData.map(item => item.rata_rata_biaya / 1000),
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Kurir & Layanan'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Jumlah Penggunaan'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Rata-rata Biaya (Ribuan Rp)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                if (context.datasetIndex === 1) {
                                    return 'Rp ' + (context.parsed.y * 1000).toLocaleString('id-ID');
                                }
                                return '';
                            }
                        }
                    }
                }
            }
        });
    }
}

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