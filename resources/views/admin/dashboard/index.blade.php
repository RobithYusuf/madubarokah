@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Dashboard Admin</h1>
        <p class="text-gray-500 mt-2 mb-0">Selamat datang di Panel Admin Toko Madu Barokah</p>
    </div>
    <div class="d-flex">
        <a href="{{ route('frontend.home') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2" target="_blank">
            <i class="fas fa-store fa-sm text-white-50 mr-1"></i> Lihat Toko
        </a>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50 mr-1"></i> Laporan
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">

    <!-- Total Kategori Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-3">
            <div class="card-body p-3">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Kategori</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($totalKategori) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tags fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Produk Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-3">
            <div class="card-body p-3">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Produk</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($totalProduk) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pesanan Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-3">
            <div class="card-body p-3">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Pesanan</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPesanan) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pengguna Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-3">
            <div class="card-body p-3">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Pengguna</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPengguna) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Revenue Row -->
<div class="row">
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-3">
            <div class="card-body p-3">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Revenue</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalRevenue) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-3">
            <div class="card-body p-3">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Revenue Bulan Ini</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($monthlyRevenue) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">

    <!-- Area Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line mr-2"></i>Grafik Penjualan</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Opsi Grafik:</div>
                        <a class="dropdown-item chart-filter" href="#" data-type="monthly">Bulanan</a>
                        <a class="dropdown-item chart-filter" href="#" data-type="yearly">Tahunan</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="exportChart()">Ekspor Data</a>
                    </div>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie mr-2"></i>Distribusi Kategori Produk</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small" id="categoryLegend">
                    @foreach($categoryData['labels'] as $index => $label)
                    <span class="mr-2">
                        <i class="fas fa-circle" style="color: {{ $categoryData['colors'][$index] ?? '#6c757d' }}"></i> {{ $label }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">

    <!-- Recent Orders -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-shopping-cart mr-2"></i>Pesanan Terbaru</h6>
                <a href="{{ route('admin.pesanan.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye fa-sm"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td>{{ $order['invoice'] }}</td>
                                <td>{{ $order['customer'] }}</td>
                                <td>Rp {{ number_format($order['total']) }}</td>
                                <td>
                                    <span class="badge {{ $order['status_badge'] }}">
                                        {{ ucfirst($order['status']) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada pesanan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bolt mr-2"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.produk.index') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-plus fa-sm mr-2"></i>
                            Kelola Produk
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.kategori.index') }}" class="btn btn-success btn-block">
                            <i class="fas fa-tags fa-sm mr-2"></i>
                            Kelola Kategori
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.pesanan.index') }}" class="btn btn-info btn-block">
                            <i class="fas fa-shopping-cart fa-sm mr-2"></i>
                            Kelola Pesanan
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.user.index') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-users fa-sm mr-2"></i>
                            Kelola User
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.shipping.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-shipping-fast fa-sm mr-2"></i>
                            Pengiriman
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('frontend.home') }}" class="btn btn-dark btn-block" target="_blank">
                            <i class="fas fa-store fa-sm mr-2"></i>
                            Lihat Toko
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i>Informasi Sistem</h6>
            </div>
            <div class="card-body">
                <div class="row no-gutters">
                    <div class="col-6">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Server Time</div>
                        <div class="h6 mb-2 font-weight-bold text-gray-800">{{ date('H:i:s') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Tanggal</div>
                        <div class="h6 mb-2 font-weight-bold text-gray-800">{{ date('d/m/Y') }}</div>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col-6">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Admin Login</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">{{ Auth::user()->nama ?? 'Administrator' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Version</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">v1.0.0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    // Data from backend
    const salesData = @json($salesData);
    const categoryData = @json($categoryData);

    let myLineChart;
    let myPieChart;

    $(document).ready(function() {
        initializeCharts();
    });

    function initializeCharts() {
        // Area Chart
        const ctx = document.getElementById("myAreaChart");
        myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.labels,
                datasets: [{
                    label: "Penjualan (Rp)",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: salesData.data,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'date'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value, index, values) {
                                return 'Rp ' + number_format(value);
                            }
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': Rp ' + number_format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        // Pie Chart
        const ctxPie = document.getElementById("myPieChart");
        myPieChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: categoryData.labels,
                datasets: [{
                    data: categoryData.data,
                    backgroundColor: categoryData.colors,
                    hoverBackgroundColor: categoryData.colors.map(color => color + 'CC'),
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
    }

    // Chart filter functionality
    $('.chart-filter').click(function(e) {
        e.preventDefault();
        const type = $(this).data('type');

        $.ajax({
            url: '{{ route("admin.dashboard.chart-data") }}',
            method: 'GET',
            data: {
                type: type
            },
            success: function(response) {
                // Update chart
                myLineChart.data.labels = response.labels;
                myLineChart.data.datasets[0].data = response.data;
                myLineChart.update();
            },
            error: function() {
                alert('Gagal memuat data chart');
            }
        });
    });

    function exportChart() {
        // Simple export functionality
        const canvas = document.getElementById('myAreaChart');
        const url = canvas.toDataURL('image/png');
        const a = document.createElement('a');
        a.href = url;
        a.download = 'sales-chart.png';
        a.click();
    }

    // Number formatting function
    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(',', '').replace(' ', '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>
@endpush