@extends('layouts.app')

@section('title', 'Manajemen Pesanan')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h4 class="m-0 font-weight-bold text-primary">Manajemen Pesanan</h4>
        <div>
            <!-- Tombol untuk generate test data (hanya untuk development) -->
            @if(app()->environment('local'))
            <a href="{{ route('admin.pesanan.create-test') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-plus"></i> Generate Test Data
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div id="alertSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        @if (session('error'))
        <div id="alertError" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        @if($pesanans && $pesanans->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tabelPesanan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Metode Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pesanans as $index => $pesanan)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>#{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $pesanan->user->nama ?? '-' }}</td>
                        <td>{{ $pesanan->tanggal_transaksi ? $pesanan->tanggal_transaksi->format('d/m/Y H:i') : '-' }}</td>
                        <td>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $pesanan->statusBadge }} text-white">
                                {{ ucfirst($pesanan->status) }}
                            </span>
                        </td>
                        <td>{{ $pesanan->metode_pembayaran ?? '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-info" data-bs-toggle="modal"
                                        data-bs-target="#modalDetail{{ $pesanan->id }}" title="Detail">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button class="btn btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#modalStatus{{ $pesanan->id }}" title="Status">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#modalHapus{{ $pesanan->id }}" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
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
                <p>Pesanan dari pelanggan akan muncul di sini</p>
                @if(app()->environment('local'))
                <a href="{{ route('admin.pesanan.create-test') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Generate Test Data
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modals -->
@if($pesanans && $pesanans->count() > 0)
@foreach ($pesanans as $pesanan)
<!-- Modal Detail Pesanan -->
<div class="modal fade" id="modalDetail{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Informasi Pelanggan</strong></h6>
                        <p><strong>Nama:</strong> {{ $pesanan->user->nama ?? '-' }}</p>
                        <p><strong>Username:</strong> {{ $pesanan->user->username ?? '-' }}</p>
                        <p><strong>Alamat:</strong> {{ $pesanan->user->alamat ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Informasi Pesanan</strong></h6>
                        <p><strong>Tanggal:</strong> {{ $pesanan->tanggal_transaksi ? $pesanan->tanggal_transaksi->format('d/m/Y H:i') : '-' }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $pesanan->statusBadge }} text-white">
                                {{ ucfirst($pesanan->status) }}
                            </span>
                        </p>
                        <p><strong>Metode Bayar:</strong> {{ $pesanan->metode_pembayaran ?? '-' }}</p>
                    </div>
                </div>
                
                <hr>
                <h6><strong>Detail Produk</strong></h6>
                @if($pesanan->detailTransaksi && $pesanan->detailTransaksi->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanan->detailTransaksi as $detail)
                            <tr>
                                <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                <td>
                                    @if($detail->produk && $detail->produk->kategori)
                                    <span class="badge text-white" 
                                          style="background-color: {{ $detail->produk->kategori->warna ?? '#6C757D' }};">
                                        {{ $detail->produk->kategori->nama_kategori }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $detail->jumlah }}</td>
                                <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Total</th>
                                <th>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-muted">Tidak ada detail produk</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modalStatus{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.pesanan.updateStatus', $pesanan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status{{ $pesanan->id }}" class="form-label">Status</label>
                        <select class="form-control" name="status" id="status{{ $pesanan->id }}" required>
                            <option value="pending" {{ $pesanan->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="dibayar" {{ $pesanan->status == 'dibayar' ? 'selected' : '' }}>Dibayar</option>
                            <option value="dikirim" {{ $pesanan->status == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                            <option value="selesai" {{ $pesanan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="batal" {{ $pesanan->status == 'batal' ? 'selected' : '' }}>Batal</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Pesanan -->
<div class="modal fade" id="modalHapus{{ $pesanan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pesanan #{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }} ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('admin.pesanan.destroy', $pesanan->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif

@push('scripts')
<script>
$(document).ready(function() {
    // Hanya inisialisasi DataTable jika ada data dan tabel ada
    if ($('#tabelPesanan').length && $('#tabelPesanan tbody tr').length > 0) {
        try {
            // Pastikan tabel memiliki struktur yang benar
            var table = $('#tabelPesanan');
            var headerCount = table.find('thead th').length;
            var firstRowCount = table.find('tbody tr:first td').length;
            
            console.log('Header count:', headerCount);
            console.log('First row count:', firstRowCount);
            
            if (headerCount === firstRowCount) {
                $('#tabelPesanan').DataTable({
                    responsive: true,
                    processing: false,
                    serverSide: false,
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    lengthMenu: [5, 10, 25, 50],
                    pageLength: 10,
                    order: [[1, 'desc']], // Urutkan berdasarkan ID Transaksi
                    columnDefs: [
                        { 
                            targets: [0, 7], // No dan Aksi
                            orderable: false
                        }
                    ],
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
                    }
                });
                console.log('DataTable initialized successfully');
            } else {
                console.error('Column count mismatch - Header:', headerCount, 'Row:', firstRowCount);
            }
        } catch (error) {
            console.error('DataTable initialization error:', error);
        }
    } else {
        console.log('No data available for DataTable');
    }
});

// Alert auto hide
setTimeout(function() {
    $('#alertSuccess, #alertError').fadeOut();
}, 3000);
</script>
@endpush
@endsection
