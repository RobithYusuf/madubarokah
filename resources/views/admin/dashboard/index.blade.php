@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Dashboard Admin</h1>
        <p class="text-gray-500 mt-2 mb-0">Selamat datang di Panel Admin Toko Madu Barokah</p>
    </div>
    <div class="d-flex">
        <a href="{{ route('Landingpage.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2" target="_blank">
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
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalKategori ?? 0 }}</div>
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
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalProduk ?? 0 }}</div>
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
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalPesanan ?? 0 }}</div>
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
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalPengguna ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line mr-2"></i>Grafik Penjualan Bulanan</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Opsi Grafik:</div>
                        <a class="dropdown-item" href="#">Tahun Ini</a>
                        <a class="dropdown-item" href="#">Tahun Lalu</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Ekspor Data</a>
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
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie mr-2"></i>Distribusi Kategori Produk</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> Madu Murni
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Madu Herbal
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-info"></i> Produk Lebah
                    </span>
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
                <a href="#" class="btn btn-sm btn-outline-primary" onclick="alert('Fitur sedang dalam pengembangan')">
                    <i class="fas fa-eye fa-sm"></i> Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>INV-001</td>
                                <td>Ahmad Wijaya</td>
                                <td>Rp 150.000</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                            </tr>
                            <tr>
                                <td>INV-002</td>
                                <td>Siti Rahayu</td>
                                <td>Rp 275.000</td>
                                <td><span class="badge badge-warning">Diproses</span></td>
                            </tr>
                            <tr>
                                <td>INV-003</td>
                                <td>Budi Santoso</td>
                                <td>Rp 89.000</td>
                                <td><span class="badge badge-info">Dikirim</span></td>
                            </tr>
                            <tr>
                                <td>INV-004</td>
                                <td>Dewi Lestari</td>
                                <td>Rp 320.000</td>
                                <td><span class="badge badge-primary">Baru</span></td>
                            </tr>
                            <tr>
                                <td>INV-005</td>
                                <td>Rudi Hermawan</td>
                                <td>Rp 125.000</td>
                                <td><span class="badge badge-secondary">Pending</span></td>
                            </tr>
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
                        <a href="#" onclick="alert('Fitur sedang dalam pengembangan')" class="btn btn-info btn-block">
                            <i class="fas fa-chart-line fa-sm mr-2"></i>
                            Lihat Laporan
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="#" onclick="alert('Fitur sedang dalam pengembangan')" class="btn btn-warning btn-block">
                            <i class="fas fa-cogs fa-sm mr-2"></i>
                            Pengaturan
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="#" onclick="alert('Fitur sedang dalam pengembangan')" class="btn btn-secondary btn-block">
                            <i class="fas fa-users fa-sm mr-2"></i>
                            Kelola User
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="{{ route('Landingpage.index') }}" class="btn btn-dark btn-block" target="_blank">
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
    // Sample data for charts - you can replace this with real data from your backend
    
    // Area Chart Example
    var ctx = document.getElementById("myAreaChart");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
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
                data: [0, 2500000, 1750000, 3200000, 2800000, 4500000, 3900000, 5200000, 4800000, 6100000, 5500000, 7200000],
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

    // Pie Chart Example
    var ctx = document.getElementById("myPieChart");
    var myPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ["Madu Murni", "Madu Herbal", "Produk Lebah"],
            datasets: [{
                data: [55, 30, 15],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
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

    // Auto refresh data setiap 5 menit
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 menit
</script>
@endpush
