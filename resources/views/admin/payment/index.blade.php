@extends('layouts.app')

@section('title', 'Manajemen Payment Channel')

@section('content')
<div id="content">
    <div class="row">
        <!-- Card Statistics -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Payment Channel</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalChannels }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
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
                                Payment Channel Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeChannels }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h4 class="m-0 font-weight-bold text-primary">Manajemen Payment Channel</h4>
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

            <!-- Info Card -->
            <div class="card shadow mb-4">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-info mb-0">
                                <i class="fas fa-info-circle"></i> Metode Pembayaran
                            </h6>
                            <small class="text-muted">Kelola sinkronisasi dengan Tripay API</small>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-sync-alt"></i> Kelola Sinkronisasi
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" id="syncOnlyBtn">
                                        <i class="fas fa-download text-primary"></i> Sinkron Saja
                                        <small class="d-block text-muted">Tambah/update channel tanpa hapus data lama</small>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" id="syncAndCleanBtn">
                                        <i class="fas fa-sync-alt text-warning"></i> Sinkron + Bersihkan
                                        <small class="d-block text-muted">Hapus data lama, hanya simpan data dari Tripay</small>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" id="resetAllBtn">
                                        <i class="fas fa-trash text-danger"></i> Reset Semua
                                        <small class="d-block text-muted">Hapus semua payment channel</small>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Sync Status Info -->
                    <div class="mt-2 pt-2 border-top">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="text-xs text-uppercase text-muted">Total Channel</div>
                                <div class="h6 mb-0 text-primary">{{ $totalChannels }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-xs text-uppercase text-muted">Channel Aktif</div>
                                <div class="h6 mb-0 text-success">{{ $activeChannels }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-xs text-uppercase text-muted">Dari Sinkron</div>
                                <div class="h6 mb-0 text-info">{{ $paymentChannels->flatten()->where('is_synced', true)->count() }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-xs text-uppercase text-muted">Manual/Lama</div>
                                <div class="h6 mb-0 text-warning">{{ $paymentChannels->flatten()->where('is_synced', false)->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Channel List -->
            @foreach($paymentChannels as $group => $channels)
            <div class="card mb-3">
                <div class="card-header py-2">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-{{ $group == 'Virtual Account' ? 'university' : ($group == 'E-Wallet' ? 'wallet' : ($group == 'Convenience Store' ? 'store' : 'qrcode')) }}"></i>
                        {{ $group }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="8%">Kode</th>
                                    <th width="18%">Nama</th>
                                    <th width="18%">Fee</th>
                                    <th width="8%" class="text-center">Status</th>
                                    <th width="12%">Instructions</th>
                                    <th width="31%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($channels as $index => $channel)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><code>{{ $channel->code }}</code></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($channel->icon_url)
                                            <img src="{{ $channel->icon_url }}" alt="{{ $channel->name }}"
                                                class="me-2" style="width: 24px; height: 24px; object-fit: contain;">
                                            @else
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2"
                                                style="width: 24px; height: 24px; min-width: 24px;">
                                                <i class="fas fa-credit-card text-white" style="font-size: 10px;"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <span>{{ $channel->name }}</span>
                                                @if($channel->is_synced)
                                                    <span class="badge badge-info badge-sm ml-1" title="Data dari sinkronisasi Tripay">
                                                        <i class="fas fa-sync-alt"></i> Synced
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning badge-sm ml-1" title="Data manual atau belum disinkronisasi">
                                                        <i class="fas fa-edit"></i> Manual
                                                    </span>
                                                @endif
                                                @if($channel->last_synced_at)
                                                    <div class="small text-muted">Sync: {{ $channel->last_synced_at->format('d/m/Y H:i') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="d-inline-block me-2">Flat: Rp {{ number_format($channel->fee_flat, 0, ',', '.') }}</span>
                                            <span class="d-inline-block">Persen: {{ $channel->fee_percent }}%</span>
                                            @if($channel->minimum_fee > 0 || $channel->maximum_fee > 0)
                                            <div class="text-muted">
                                                @if($channel->minimum_fee > 0)
                                                <span class="d-inline-block me-2">Min: Rp {{ number_format($channel->minimum_fee, 0, ',', '.') }}</span>
                                                @endif
                                                @if($channel->maximum_fee > 0)
                                                <span class="d-inline-block">Max: Rp {{ number_format($channel->maximum_fee, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input channel-status-toggle"
                                                id="status_{{ $channel->id }}"
                                                data-id="{{ $channel->id }}"
                                                {{ $channel->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="status_{{ $channel->id }}">
                                                {{ $channel->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        @if($channel->instructions && is_array($channel->instructions) && count($channel->instructions) > 0)
                                            @php
                                                $totalSteps = 0;
                                                $instructionMethods = 0;
                                                
                                                // Safely process instructions without evaluating content
                                                foreach($channel->instructions as $instruction) {
                                                    if(is_array($instruction) && isset($instruction['steps']) && is_array($instruction['steps'])) {
                                                        $totalSteps += count($instruction['steps']);
                                                        $instructionMethods++;
                                                    } else {
                                                        $totalSteps += 1;
                                                        $instructionMethods++;
                                                    }
                                                }
                                            @endphp
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#instructionsModal"
                                                    data-channel="{{ $channel->name }}"
                                                    data-instructions='{{ json_encode($channel->instructions, JSON_HEX_APOS | JSON_HEX_QUOT) }}'>
                                                <i class="fas fa-list"></i> 
                                                @if($instructionMethods > 1)
                                                    {{ $instructionMethods }} metode ({{ $totalSteps }} langkah)
                                                @else
                                                    {{ $totalSteps }} langkah
                                                @endif
                                            </button>
                                        @elseif($channel->instructions && is_string($channel->instructions))
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#instructionsModal"
                                                    data-channel="{{ $channel->name }}"
                                                    data-instructions='{{ json_encode([$channel->instructions], JSON_HEX_APOS | JSON_HEX_QUOT) }}'>
                                                <i class="fas fa-file-text"></i> Lihat
                                            </button>
                                        @else
                                            <span class="text-muted small">
                                                <i class="fas fa-minus-circle"></i> Tidak ada
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary edit-fee-btn" title="Edit Fee"
                                                data-id="{{ $channel->id }}"
                                                data-name="{{ $channel->name }}"
                                                data-fee-flat="{{ $channel->fee_flat }}"
                                                data-fee-percent="{{ $channel->fee_percent }}"
                                                data-min-fee="{{ $channel->minimum_fee }}"
                                                data-max-fee="{{ $channel->maximum_fee }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" title="Kalkulator Fee"
                                                data-bs-toggle="modal"
                                                data-bs-target="#feeCalculatorModal"
                                                data-id="{{ $channel->id }}"
                                                data-name="{{ $channel->name }}"
                                                data-fee-flat="{{ $channel->fee_flat }}"
                                                data-fee-percent="{{ $channel->fee_percent }}"
                                                data-min-fee="{{ $channel->minimum_fee }}"
                                                data-max-fee="{{ $channel->maximum_fee }}">
                                                <i class="fas fa-calculator"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-channel-btn" title="Hapus"
                                                data-id="{{ $channel->id }}"
                                                data-name="{{ $channel->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Instructions Modal -->
<div class="modal fade" id="instructionsModal" tabindex="-1" aria-labelledby="instructionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="instructionsModalLabel">Payment Instructions</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Payment Channel:</strong></label>
                    <p id="instructions-channel-name" class="text-primary"></p>
                </div>
                <div>
                    <label class="form-label"><strong>Instructions:</strong></label>
                    <div id="instructions-content" class="border rounded p-3 bg-light">
                        <!-- Instructions will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fee Modal -->
<div class="modal fade" id="editFeeModal" tabindex="-1" aria-labelledby="editFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFeeModalLabel">Edit Fee Payment Channel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editFeeForm">
                    <input type="hidden" id="channel_id" name="channel_id">
                    <div class="form-group mb-3">
                        <label for="channel_name">Nama Channel</label>
                        <input type="text" class="form-control" id="channel_name" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fee_flat">Fee Flat (Rp)</label>
                        <input type="number" class="form-control" id="fee_flat" name="fee_flat" min="0" step="100" required>
                        <small class="form-text text-muted">Biaya tetap yang dikenakan untuk setiap transaksi</small>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fee_percent">Fee Persentase (%)</label>
                        <input type="number" class="form-control" id="fee_percent" name="fee_percent" min="0" max="100" step="0.01" required>
                        <small class="form-text text-muted">Persentase biaya dari total transaksi</small>
                    </div>
                    <div class="form-group mb-3">
                        <label for="minimum_fee">Minimum Fee (Rp)</label>
                        <input type="number" class="form-control" id="minimum_fee" name="minimum_fee" min="0" step="100">
                        <small class="form-text text-muted">Batas minimum fee yang dikenakan (opsional)</small>
                    </div>
                    <div class="form-group mb-3">
                        <label for="maximum_fee">Maximum Fee (Rp)</label>
                        <input type="number" class="form-control" id="maximum_fee" name="maximum_fee" min="0" step="100">
                        <small class="form-text text-muted">Batas maksimum fee yang dikenakan (opsional)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveFeeBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Fee Calculator Modal -->
<div class="modal fade" id="feeCalculatorModal" tabindex="-1" aria-labelledby="feeCalculatorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feeCalculatorModalLabel">Kalkulator Fee</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="calc_channel_name">Payment Channel</label>
                    <input type="text" class="form-control" id="calc_channel_name" readonly>
                </div>
                <div class="form-group mb-3">
                    <label for="transaction_amount">Jumlah Transaksi (Rp)</label>
                    <input type="number" class="form-control" id="transaction_amount" min="1000" step="1000" value="100000">
                </div>
                <div class="card bg-light mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Hasil Perhitungan:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">Fee Flat:</p>
                                <p class="mb-1">Fee Persentase:</p>
                                <p class="mb-1">Total Fee:</p>
                                <p class="mb-1">Total Bayar:</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1" id="calc_fee_flat">Rp 0</p>
                                <p class="mb-1" id="calc_fee_percent">Rp 0</p>
                                <p class="mb-1 font-weight-bold" id="calc_total_fee">Rp 0</p>
                                <p class="mb-1 font-weight-bold text-primary" id="calc_total_amount">Rp 0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="calculateFeeBtn">Hitung</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Instructions Modal Handler
        $('#instructionsModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const channelName = button.data('channel');
            const instructions = button.data('instructions');
            
            $('#instructions-channel-name').text(channelName);
            
            try {
                let instructionsArray = [];
                
                // Parse instructions data
                if (typeof instructions === 'string') {
                    instructionsArray = JSON.parse(instructions);
                } else if (Array.isArray(instructions)) {
                    instructionsArray = instructions;
                } else if (typeof instructions === 'object' && instructions !== null) {
                    instructionsArray = [instructions];
                }
                
                let html = '';
                
                if (instructionsArray.length > 0) {
                    instructionsArray.forEach((instruction, index) => {
                        // Handle complex structure with title and steps
                        if (typeof instruction === 'object' && instruction.title && instruction.steps) {
                            html += '<div class="instruction-section mb-4">';
                            html += '<h6 class="text-primary mb-2"><i class="fas fa-credit-card me-2"></i>' + instruction.title + '</h6>';
                            html += '<ol class="mb-0">';
                            
                            instruction.steps.forEach((step, stepIndex) => {
                                let cleanStep = step.replace(/\{\{pay_code\}\}/g, '[Kode Pembayaran]')
                                                   .replace(/\{\{amount\}\}/g, '[Jumlah Pembayaran]');
                                html += '<li class="mb-1">' + cleanStep + '</li>';
                            });
                            
                            html += '</ol>';
                            html += '</div>';
                        }
                        // Handle simple array of strings
                        else if (typeof instruction === 'string') {
                            if (index === 0) {
                                html += '<ol class="mb-0">';
                            }
                            let cleanStep = instruction.replace(/\{\{pay_code\}\}/g, '[Kode Pembayaran]')
                                                     .replace(/\{\{amount\}\}/g, '[Jumlah Pembayaran]');
                            html += '<li class="mb-2">' + cleanStep + '</li>';
                            
                            if (index === instructionsArray.length - 1) {
                                html += '</ol>';
                            }
                        }
                        // Handle other object types
                        else {
                            html += '<div class="instruction-item mb-2">';
                            html += '<pre class="bg-light p-2 rounded">' + JSON.stringify(instruction, null, 2) + '</pre>';
                            html += '</div>';
                        }
                    });
                } else {
                    html = '<p class="text-muted mb-0">Tidak ada instruksi tersedia</p>';
                }
                
                $('#instructions-content').html(html);
                
            } catch (e) {
                console.error('Error parsing instructions:', e, instructions);
                $('#instructions-content').html(
                    '<div class="alert alert-danger mb-0">' +
                    '<strong>Error:</strong> Tidak dapat memuat instruksi. Format data tidak valid.' +
                    '</div>'
                );
            }
        });

        // Function untuk melakukan sinkronisasi
        function performSync(removeOldData, actionName) {
            // Tampilkan loading state
            Swal.fire({
                title: `Sedang ${actionName}...`,
                html: 'Mohon tunggu, sedang mengambil data dari Tripay API',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Kirim request AJAX
            $.ajax({
                url: '{{ route("admin.payment.sync") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    remove_old_data: removeOldData
                },
                timeout: 30000, // 30 seconds timeout
                success: function(response) {
                    if (response.success) {
                        let message = response.message;
                        if (response.data) {
                            message += `<br><br><strong>Detail:</strong><br>`;
                            message += `• Channel disinkronisasi: ${response.data.synced}<br>`;
                            message += `• Channel dihapus: ${response.data.removed}<br>`;
                            message += `• Total channel sekarang: ${response.data.total_after}`;
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: message,
                            timer: 4000,
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
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
                error: function(xhr, status, error) {
                    let errorMessage = 'Terjadi kesalahan saat sinkronisasi';
                    
                    if (status === 'timeout') {
                        errorMessage = 'Request timeout. Proses mungkin memerlukan waktu lebih lama.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                }
            });
        }

        // Sinkron Saja (tanpa hapus data lama)
        $('#syncOnlyBtn').click(function() {
            Swal.fire({
                title: 'Sinkronisasi Metode Pembayaran?',
                html: `<div class="text-left">
                       <p><strong>Mode:</strong> Sinkron Saja</p>
                       <p>Proses ini akan:</p>
                       <ul class="text-left">
                         <li>✅ Menambah channel baru dari Tripay</li>
                         <li>✅ Update data channel yang sudah ada</li>
                         <li>❌ <strong>TIDAK</strong> menghapus data lama</li>
                       </ul>
                       <p class="text-info"><small><i class="fas fa-info-circle"></i> Data manual/lama akan tetap tersimpan</small></p>
                       </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-download"></i> Ya, Sinkron Saja!',
                cancelButtonText: 'Batal',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    performSync(false, 'Sinkronisasi');
                }
            });
        });

        // Sinkron + Bersihkan (hapus data lama)
        $('#syncAndCleanBtn').click(function() {
            Swal.fire({
                title: 'Sinkron + Bersihkan Data?',
                html: `<div class="text-left">
                       <p><strong>Mode:</strong> Sinkron + Bersihkan</p>
                       <p>Proses ini akan:</p>
                       <ul class="text-left">
                         <li>✅ Menambah channel baru dari Tripay</li>
                         <li>✅ Update data channel yang sudah ada</li>
                         <li>⚠️ <strong>MENGHAPUS</strong> semua data manual/lama</li>
                       </ul>
                       <p class="text-warning"><small><i class="fas fa-exclamation-triangle"></i> Hanya data dari Tripay yang akan tersisa!</small></p>
                       </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Sinkron + Bersihkan!',
                cancelButtonText: 'Batal',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    performSync(true, 'Sinkronisasi + Pembersihan');
                }
            });
        });

        // Reset Semua Data
        $('#resetAllBtn').click(function() {
            Swal.fire({
                title: 'Reset Semua Payment Channel?',
                html: `<div class="text-left">
                       <p><strong>Mode:</strong> Reset Total</p>
                       <p>Proses ini akan:</p>
                       <ul class="text-left">
                         <li>❌ <strong>MENGHAPUS SEMUA</strong> payment channel</li>
                         <li>❌ Data dari Tripay</li>
                         <li>❌ Data manual/lama</li>
                       </ul>
                       <p class="text-danger"><small><i class="fas fa-skull-crossbones"></i> <strong>TIDAK DAPAT DIBATALKAN!</strong> Database akan kosong total.</small></p>
                       </div>`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Ya, Reset Semua!',
                cancelButtonText: 'Batal',
                allowOutsideClick: false,
                input: 'text',
                inputPlaceholder: 'Ketik "RESET" untuk konfirmasi',
                inputValidator: (value) => {
                    if (value !== 'RESET') {
                        return 'Harus mengetik "RESET" untuk melanjutkan!'
                    }
                },
                preConfirm: (inputValue) => {
                    return inputValue === 'RESET';
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    performReset();
                }
            });
        });

        // Function untuk reset semua data
        function performReset() {
            Swal.fire({
                title: 'Sedang Reset...',
                html: 'Mohon tunggu, sedang menghapus semua payment channel',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("admin.payment.reset") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reset Berhasil!',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: true
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
                    let errorMessage = 'Terjadi kesalahan saat reset';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                }
            });
        }

        // Toggle status payment channel
        $('.channel-status-toggle').change(function() {
            const id = $(this).data('id');
            const isActive = $(this).prop('checked');
            const label = $(this).next('label');

            $.ajax({
                url: `/admin/payment/channel/${id}/status`,
                type: 'POST',
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
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                        $(this).prop('checked', !isActive);
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengubah status'
                    });
                    $(this).prop('checked', !isActive);
                }
            });
        });

        // Edit Fee Modal
        $('.edit-fee-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const feeFlat = $(this).data('fee-flat');
            const feePercent = $(this).data('fee-percent');
            const minFee = $(this).data('min-fee');
            const maxFee = $(this).data('max-fee');

            $('#channel_id').val(id);
            $('#channel_name').val(name);
            $('#fee_flat').val(feeFlat);
            $('#fee_percent').val(feePercent);
            $('#minimum_fee').val(minFee);
            $('#maximum_fee').val(maxFee);

            $('#editFeeModal').modal('show');
        });

        // Save Fee
        $('#saveFeeBtn').click(function() {
            const id = $('#channel_id').val();
            const feeFlat = $('#fee_flat').val();
            const feePercent = $('#fee_percent').val();
            const minFee = $('#minimum_fee').val();
            const maxFee = $('#maximum_fee').val();

            $.ajax({
                url: `/admin/payment/channel/${id}/fee`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    fee_flat: feeFlat,
                    fee_percent: feePercent,
                    minimum_fee: minFee,
                    maximum_fee: maxFee
                },
                success: function(response) {
                    if (response.success) {
                        $('#editFeeModal').modal('hide');
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
                        text: 'Terjadi kesalahan saat menyimpan data'
                    });
                }
            });
        });

        // Fee Calculator Modal
        $('#feeCalculatorModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const name = button.data('name');
            const feeFlat = button.data('fee-flat');
            const feePercent = button.data('fee-percent');
            const minFee = button.data('min-fee');
            const maxFee = button.data('max-fee');

            $('#calc_channel_name').val(name);

            $('#calculateFeeBtn').data('fee-flat', feeFlat);
            $('#calculateFeeBtn').data('fee-percent', feePercent);
            $('#calculateFeeBtn').data('min-fee', minFee);
            $('#calculateFeeBtn').data('max-fee', maxFee);

            calculateFee();
        });

        $('#calculateFeeBtn').click(function() {
            calculateFee();
        });

        $('#transaction_amount').on('input', function() {
            calculateFee();
        });

        function calculateFee() {
            const amount = parseFloat($('#transaction_amount').val()) || 0;
            const feeFlat = parseFloat($('#calculateFeeBtn').data('fee-flat')) || 0;
            const feePercent = parseFloat($('#calculateFeeBtn').data('fee-percent')) || 0;
            const minFee = parseFloat($('#calculateFeeBtn').data('min-fee')) || 0;
            const maxFee = parseFloat($('#calculateFeeBtn').data('max-fee')) || 0;

            const percentFee = amount * (feePercent / 100);
            let totalFee = feeFlat + percentFee;

            if (minFee > 0 && totalFee < minFee) {
                totalFee = minFee;
            }

            if (maxFee > 0 && totalFee > maxFee) {
                totalFee = maxFee;
            }

            const totalAmount = amount + totalFee;

            $('#calc_fee_flat').text('Rp ' + formatNumber(feeFlat));
            $('#calc_fee_percent').text('Rp ' + formatNumber(percentFee));
            $('#calc_total_fee').text('Rp ' + formatNumber(totalFee));
            $('#calc_total_amount').text('Rp ' + formatNumber(totalAmount));
        }

        function formatNumber(number) {
            return number.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&,');
        }

        // Delete Payment Channel
        $('.delete-channel-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: 'Hapus Payment Channel?',
                text: `Apakah Anda yakin ingin menghapus payment channel '${name}'? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/payment/channel/${id}`,
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
                                text: 'Terjadi kesalahan saat menghapus payment channel'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
