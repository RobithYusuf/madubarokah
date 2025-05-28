@extends('layouts.app')

@section('title', 'Manajemen Pengiriman')

@section('content')

{{-- AWAL BAGIAN HEADER HALAMAN --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Manajemen Pengiriman</h1>
        <p class="text-gray-500 mt-2 mb-0">Konfigurasi API, sinkronisasi data wilayah, dan kelola kurir pengiriman.</p>
    </div>
    {{-- Opsional: Tombol aksi cepat jika ada --}}
    {{-- <a href="{{ route('admin.laporan.pengiriman') }}" class="btn btn-sm btn-info shadow-sm">
    <i class="fas fa-chart-bar fa-sm text-white-50"></i> Laporan Pengiriman
    </a> --}}
</div>
{{-- AKHIR BAGIAN HEADER HALAMAN --}}

<div id="content">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Provinsi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $provinces->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Kota/Kabupaten</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalCities) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-city fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            {{-- Judul di dalam card dapat disesuaikan jika perlu --}}
            <h4 class="m-0 font-weight-bold text-primary">Pengaturan Wilayah & Kurir</h4>
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

            @if(!$apiConfigured)
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <h5><i class="fas fa-key"></i> API Key Belum Dikonfigurasi!</h5>
                <p class="mb-1">Harap tambahkan <code>RAJAONGKIR_API_KEY</code> Anda di file <code>.env</code>.</p>
                <p class="mb-0 small">Kunjungi <a href="https://rajaongkir.com" target="_blank">RajaOngkir.com</a> untuk mendapatkan API key.</p>
                <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @elseif($needsSync)
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <h5><i class="fas fa-exclamation-triangle"></i> Sinkronisasi Data Wilayah Diperlukan!</h5>
                <p class="mb-1">Data provinsi dan kota belum tersedia. Lakukan sinkronisasi untuk mengaktifkan fitur pengiriman.</p>
                <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @elseif($isDataLimited)
            <div class="alert alert-info alert-dismissible fade show mb-4" role="alert"> {{-- Diubah menjadi info --}}
                <h5><i class="fas fa-info-circle"></i> Data Wilayah Mungkin Belum Lengkap</h5>
                <p class="mb-1">Terdapat <strong>{{ $totalCities }}</strong> kota/kabupaten. Sinkronisasi ulang dapat memperbarui ke data terlengkap.</p>
                <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @else
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <h5><i class="fas fa-check-circle"></i> Data Wilayah Siap Digunakan</h5>
                <p class="mb-0"><strong>{{ $provinces->count() }}</strong> provinsi dan <strong>{{ number_format($totalCities) }}</strong> kota/kabupaten telah tersinkronisasi.</p>
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

            @if(isset($apiType) && $apiType === 'starter')
            <div class="alert alert-info mb-4">
                <h5><i class="fas fa-star"></i> Info Paket API: Starter</h5>
                <p class="mb-1">Kurir yang didukung untuk paket ini: <strong>JNE, POS Indonesia, TIKI</strong>.</p>
                <p class="mb-0 text-muted small">Untuk kurir lain (J&T, SiCepat, dll.), pertimbangkan upgrade paket API RajaOngkir Anda.</p>
            </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h5 class="card-title text-info">
                                <i class="fas fa-database"></i> Data Wilayah Pengiriman
                            </h5>
                            <p class="card-text mb-3">
                                Jaga data provinsi dan kota tetap terbaru dengan sinkronisasi dari RajaOngkir.
                            </p>
                            <div class="d-flex flex-wrap gap-2 align-items-center"> {{-- Gap disesuaikan --}}
                                <button type="button" class="btn btn-info" id="syncAreasBtn">
                                    <i class="fas fa-sync-alt"></i> Sinkronkan Wilayah
                                </button>
                                <div class="vr mx-1 d-none d-sm-block"></div> {{-- Pemisah vertikal --}}
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#testShippingModal">
                                    <i class="fas fa-shipping-fast"></i> Test Ongkir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Couriers Management -->
            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-3">Manajemen Kurir</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tabelCourier">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Kurir</th>
                                    <th>Status</th>
                                    <th>Layanan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($couriers as $index => $courier)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ strtoupper($courier->code) }}</td>
                                    <td>{{ $courier->name }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input courier-toggle" type="checkbox"
                                                id="courier_{{ $courier->id }}"
                                                data-courier-id="{{ $courier->id }}"
                                                {{ $courier->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="courier_{{ $courier->id }}">
                                                {{ $courier->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        @if($courier->services_list)
                                        @foreach($courier->services_list as $code => $name)
                                        <span class="badge bg-secondary text-white me-1">{{ $code }}</span>
                                        @endforeach
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger delete-courier-btn"
                                            data-id="{{ $courier->id }}"
                                            data-name="{{ $courier->name }}">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Shipping Modal -->
<div class="modal fade" id="testShippingModal" tabindex="-1" aria-labelledby="testShippingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testShippingModalLabel">
                    <i class="fas fa-calculator"></i> Test Kalkulator Ongkir
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="testShippingForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Provinsi Asal</label>
                            <select class="form-control" id="originProvince">
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $province)
                                <option value="{{ $province->province_id }}">{{ $province->province_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota Asal</label>
                            <select class="form-control" id="originCity">
                                <option value="">Pilih Kota</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Provinsi Tujuan</label>
                            <select class="form-control" id="destProvince">
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $province)
                                <option value="{{ $province->province_id }}">{{ $province->province_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota Tujuan</label>
                            <select class="form-control" id="destCity">
                                <option value="">Pilih Kota</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Berat (gram)</label>
                            <input type="number" class="form-control" id="weight" value="1000" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kurir</label>
                            <select class="form-control" id="courier">
                                <option value="">Pilih Kurir</option>
                                @foreach($couriers->where('is_active', true) as $courier)
                                <option value="{{ $courier->code }}">{{ $courier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-warning form-control">
                                <i class="fas fa-calculator"></i> Hitung Ongkir
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Results -->
                <div id="shippingResults" class="mt-4" style="display: none;">
                    <h6>Hasil Perhitungan:</h6>
                    <div id="resultsContainer"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#tabelCourier').DataTable({
            responsive: true,
            paging: true,
            lengthMenu: [5, 10, 25, 50],
            pageLength: 10,
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
            },
            columnDefs: [{
                targets: [0, 5], // No dan Aksi (kolom index 0 dan 5)
                orderable: false
            }]
        });

        // Handle courier toggle
        $('.courier-toggle').change(function() {
            const courierId = $(this).data('courier-id');
            const isActive = $(this).is(':checked');
            const label = $(this).next('label');

            $.ajax({
                url: `/admin/shipping/courier/${courierId}/status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: isActive
                },
                success: function(response) {
                    if (response.success) {
                        label.text(isActive ? 'Aktif' : 'Nonaktif');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Status kurir berhasil diperbarui',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Gagal mengupdate status kurir'
                        });
                        location.reload();
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal mengupdate status kurir'
                    });
                    location.reload();
                }
            });
        });

        // Handle delete courier with SweetAlert
        $('.delete-courier-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: 'Hapus Kurir?',
                text: `Apakah Anda yakin ingin menghapus kurir '${name}'? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/shipping/courier/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan saat menghapus kurir'
                            });
                        }
                    });
                }
            });
        });

        // Handle province change for origin
        $('#originProvince').change(function() {
            const provinceId = $(this).val();
            if (provinceId) {
                loadCities(provinceId, '#originCity');
            } else {
                $('#originCity').html('<option value="">Pilih Kota</option>');
            }
        });

        // Handle province change for destination
        $('#destProvince').change(function() {
            const provinceId = $(this).val();
            if (provinceId) {
                loadCities(provinceId, '#destCity');
            } else {
                $('#destCity').html('<option value="">Pilih Kota</option>');
            }
        });

        // Load cities function
        function loadCities(provinceId, targetSelect) {
            $.ajax({
                url: `/shipping/cities/${provinceId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">Pilih Kota</option>';
                        response.data.forEach(function(city) {
                            options += `<option value="${city.rajaongkir_id}">${city.city_name}</option>`;
                        });
                        $(targetSelect).html(options);
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal memuat data kota'
                    });
                }
            });
        }

        // Handle shipping calculation
        $('#testShippingForm').submit(function(e) {
            e.preventDefault();

            const data = {
                origin: $('#originCity').val(),
                destination: $('#destCity').val(),
                weight: $('#weight').val(),
                courier: $('#courier').val(),
                _token: '{{ csrf_token() }}'
            };

            if (!data.origin || !data.destination || !data.weight || !data.courier) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap!',
                    text: 'Harap lengkapi semua field'
                });
                return;
            }

            $.ajax({
                url: '{{ route("admin.shipping.calculate") }}',
                method: 'POST',
                data: data,
                beforeSend: function() {
                    $('#shippingResults').hide();
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghitung...');
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                        html += '<thead><tr><th>Kurir</th><th>Layanan</th><th>Biaya</th><th>Estimasi</th></tr></thead><tbody>';

                        response.data.forEach(function(courier) {
                            courier.costs.forEach(function(cost) {
                                cost.cost.forEach(function(detail) {
                                    html += `<tr>
                                    <td>${courier.name}</td>
                                    <td>${cost.service} - ${cost.description}</td>
                                    <td>Rp ${parseInt(detail.value).toLocaleString('id-ID')}</td>
                                    <td>${detail.etd} hari</td>
                                </tr>`;
                                });
                            });
                        });

                        html += '</tbody></table></div>';

                        // Show debug info if exists
                        if (response.debug && response.debug.source === 'fallback_dummy_data') {
                            html += '<div class="alert alert-warning mt-2">';
                            html += '<small><i class="fas fa-exclamation-triangle"></i> ' + response.debug.message + '</small>';
                            html += '</div>';
                        }

                        $('#resultsContainer').html(html);
                        $('#shippingResults').show();
                    } else {
                        $('#resultsContainer').html('<div class="alert alert-warning">Tidak ada hasil ditemukan</div>');
                        $('#shippingResults').show();
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Gagal menghitung ongkir';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    $('#resultsContainer').html(`<div class="alert alert-danger">${errorMsg}</div>`);
                    $('#shippingResults').show();
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-calculator"></i> Hitung Ongkir');
                }
            });
        });

        // Auto-hide alerts
        setTimeout(function() {
            $('#alertSuccess, #alertError').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);

        // Handle sync areas with SweetAlert and progress
        $('#syncAreasBtn').click(function() {
            Swal.fire({
                title: 'Sinkronisasi Data Wilayah',
                html: `
                    <p class="mb-3">Proses ini akan mengambil data provinsi dan kota terbaru dari API RajaOngkir.</p>
                    <div class="alert alert-warning text-left">
                        <small>
                            <i class="fas fa-exclamation-triangle"></i> <strong>Perhatian:</strong><br>
                            • Proses ini membutuhkan waktu 2-5 menit<br>
                            • Jangan menutup halaman selama proses berlangsung<br>
                            • Data lama akan diperbarui dengan data terbaru
                        </small>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Sinkronkan!',
                cancelButtonText: 'Batal',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    startSyncProcess();
                }
            });
        });

        function startSyncProcess() {
            let progress = 0;
            let progressInterval;

            Swal.fire({
                title: 'Sedang Sinkronisasi...',
                html: `
                    <div class="mb-3">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: 0%; background-color: #17a2b8;" 
                                 id="syncProgressBar">
                                <span id="syncProgressText">0%</span>
                            </div>
                        </div>
                    </div>
                    <div id="syncStatus" class="text-muted small">
                        <i class="fas fa-spinner fa-spin"></i> Memulai sinkronisasi...
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                showCancelButton: false,
                didOpen: () => {
                    // Start progress animation
                    progressInterval = setInterval(() => {
                        if (progress < 90) {
                            progress += Math.random() * 10;
                            const currentProgress = Math.min(progress, 90);

                            // Update status messages based on progress
                            let statusMessage = '<i class="fas fa-spinner fa-spin"></i> Memulai sinkronisasi...';

                            if (currentProgress > 20) {
                                statusMessage = '<i class="fas fa-download"></i> Mengambil data provinsi dari API...';
                            }
                            if (currentProgress > 40) {
                                statusMessage = '<i class="fas fa-database"></i> Memproses data wilayah...';
                            }
                            if (currentProgress > 60) {
                                statusMessage = '<i class="fas fa-map-marker-alt"></i> Menyimpan kota/kabupaten...';
                            }
                            if (currentProgress > 80) {
                                statusMessage = '<i class="fas fa-check-circle"></i> Menyelesaikan sinkronisasi...';
                            }

                            updateProgress(currentProgress, statusMessage);
                        }
                    }, 800);

                    // Start actual sync process
                    performSync(progressInterval);
                }
            });
        }

        function updateProgress(percent, status = null) {
            const progressBar = $('#syncProgressBar');
            const progressText = $('#syncProgressText');
            const statusDiv = $('#syncStatus');

            const roundedPercent = Math.round(percent);
            progressBar.css('width', roundedPercent + '%');
            progressText.text(roundedPercent + '%');

            if (status) {
                statusDiv.html(status);
            }
        }

        function performSync(progressInterval) {
            $.ajax({
                url: '{{ route("admin.shipping.sync") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                timeout: 300000, // 5 minutes timeout
                success: function(response) {
                    clearInterval(progressInterval);
                    updateProgress(100, '<i class="fas fa-check text-success"></i> Sinkronisasi selesai!');

                    setTimeout(() => {
                        let resultHtml = `
                            <div class="text-left">
                                <p class="mb-2">${response.message}</p>
                                <div class="alert alert-success text-left">
                                    <small>
                                        <i class="fas fa-info-circle"></i> <strong>Hasil:</strong><br>
                                        • Kota/Kabupaten disinkronkan: <strong>${response.data?.synced || 0}</strong><br>
                                        • Provinsi diproses: <strong>${response.data?.provinces_processed || 0}</strong><br>
                                        ${response.data?.errors > 0 ? `• Error diabaikan: <strong>${response.data.errors}</strong><br>` : ''}
                                        Halaman akan dimuat ulang untuk menampilkan data terbaru.
                                    </small>
                                </div>
                            </div>
                        `;

                        Swal.fire({
                            icon: 'success',
                            title: 'Sinkronisasi Berhasil!',
                            html: resultHtml,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            location.reload();
                        });
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    clearInterval(progressInterval);

                    let errorMessage = 'Gagal melakukan sinkronisasi';
                    let debugInfo = '';

                    if (status === 'timeout') {
                        errorMessage = 'Koneksi timeout - Proses sinkronisasi mungkin masih berjalan di background';
                    } else if (xhr.responseJSON) {
                        errorMessage = xhr.responseJSON.message || errorMessage;

                        // Show debug information if available
                        if (xhr.responseJSON.debug) {
                            const debug = xhr.responseJSON.debug;
                            debugInfo = '<div class="alert alert-info text-left mt-2">';
                            debugInfo += '<small><i class="fas fa-bug"></i> <strong>Debug Info:</strong><br>';

                            if (debug.api_key_configured === false) {
                                debugInfo += '• API key tidak dikonfigurasi<br>';
                            }
                            if (debug.exception) {
                                debugInfo += `• Exception: ${debug.exception}<br>`;
                            }
                            if (debug.config_check) {
                                debugInfo += `• ${debug.config_check}<br>`;
                            }

                            debugInfo += '</small></div>';
                        }
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error - Coba lagi nanti';
                    }

                    updateProgress(100, '<i class="fas fa-times text-danger"></i> Sinkronisasi gagal!');

                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Sinkronisasi Gagal',
                            html: `
                                <div class="text-left">
                                    <p><strong>Error:</strong> ${errorMessage}</p>
                                    ${debugInfo}
                                    <div class="alert alert-warning text-left">
                                        <small>
                                            <i class="fas fa-lightbulb"></i> <strong>Saran:</strong><br>
                                            • Periksa koneksi internet<br>
                                            • Pastikan API key RajaOngkir valid di file .env<br>
                                            • Lihat log Laravel di storage/logs/laravel.log<br>
                                            • Coba lagi dalam beberapa menit
                                        </small>
                                    </div>
                                </div>
                            `,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    }, 1000);
                }
            });
        }
    });
</script>
@endpush
@endsection