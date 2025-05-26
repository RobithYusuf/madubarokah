@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
    <div id="content">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary">Detail Pesanan #{{ $pesanan->id }}</h4>
                <a href="{{ route('admin.pesanan.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div id="alertSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <h5 class="text-primary font-weight-bold">Informasi Pesanan</h5>
                                <hr>
                                <p><strong>ID Pesanan:</strong> #{{ $pesanan->id }}</p>
                                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pesanan->tanggal_transaksi)->format('d M Y H:i') }}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-{{ $pesanan->status_badge }}">
                                        {{ ucfirst($pesanan->status) }}
                                    </span>
                                </p>
                                <p><strong>Metode Pembayaran:</strong> {{ $pesanan->metode_pembayaran ?? 'Belum ditentukan' }}</p>
                                <p><strong>Total Pembayaran:</strong> {{ 'Rp ' . number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <h5 class="text-success font-weight-bold">Informasi Pelanggan</h5>
                                <hr>
                                <p><strong>Nama:</strong> {{ $pesanan->user->nama ?? 'Tidak tersedia' }}</p>
                                <p><strong>Email:</strong> {{ $pesanan->user->email ?? 'Tidak tersedia' }}</p>
                                <p><strong>No. Telepon:</strong> {{ $pesanan->user->no_telp ?? 'Tidak tersedia' }}</p>
                                <p><strong>Alamat:</strong> {{ $pesanan->user->alamat ?? 'Tidak tersedia' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pengiriman (jika ada) -->
                @if($pesanan->pengiriman)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <h5 class="text-info font-weight-bold">Informasi Pengiriman</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Alamat Pengiriman:</strong> {{ $pesanan->pengiriman->alamat_pengiriman }}</p>
                                        <p><strong>Kota:</strong> {{ $pesanan->pengiriman->kota }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Kurir:</strong> {{ $pesanan->pengiriman->kurir }}</p>
                                        <p><strong>No. Resi:</strong> {{ $pesanan->pengiriman->no_resi ?? 'Belum tersedia' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Detail Produk -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Detail Produk</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga Satuan</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pesanan->detailTransaksi as $detail)
                                    <tr>
                                        <td class="d-flex align-items-center">
                                            @if($detail->produk && $detail->produk->gambar)
                                                <img src="{{ asset('storage/' . $detail->produk->gambar) }}" alt="{{ $detail->produk->nama_produk }}" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <span class="font-weight-bold">{{ $detail->produk->nama_produk ?? 'Produk tidak tersedia' }}</span>
                                                @if($detail->produk && $detail->produk->kategori)
                                                <br>
                                                <span class="badge bg-{{ $detail->produk->kategori->warna ?? 'secondary' }} text-white">
                                                    {{ $detail->produk->kategori->nama_kategori ?? '-' }}
                                                </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ 'Rp ' . number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                        <td>{{ $detail->jumlah }}</td>
                                        <td>{{ 'Rp ' . number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right font-weight-bold">Total</td>
                                        <td class="font-weight-bold">{{ 'Rp ' . number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalStatus">
                            <i class="fa fa-edit me-1"></i> Update Status
                        </button>
                        
                        @if($pesanan->status != 'batal')
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalBatal">
                            <i class="fa fa-times me-1"></i> Batalkan Pesanan
                        </button>
                        @endif
                        
                        <a href="#" class="btn btn-info" onclick="window.print()">
                            <i class="fa fa-print me-1"></i> Cetak Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Update Status -->
    <div class="modal fade" id="modalStatus" tabindex="-1" aria-labelledby="modalStatusLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStatusLabel">Update Status Pesanan</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.pesanan.updateStatus', $pesanan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="status" class="form-label">Status Pesanan</label>
                            <select class="form-control custom-select" name="status" id="status" required>
                                <option value="pending" {{ $pesanan->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="dibayar" {{ $pesanan->status == 'dibayar' ? 'selected' : '' }}>Dibayar</option>
                                <option value="dikirim" {{ $pesanan->status == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                                <option value="selesai" {{ $pesanan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="batal" {{ $pesanan->status == 'batal' ? 'selected' : '' }}>Batal</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Batalkan Pesanan -->
    <div class="modal fade" id="modalBatal" tabindex="-1" aria-labelledby="modalBatalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBatalLabel">Konfirmasi Pembatalan</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin membatalkan pesanan ini?</p>
                    <p class="text-danger font-weight-bold">Perhatian: Pembatalan tidak dapat dibatalkan!</p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.pesanan.updateStatus', $pesanan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="batal">
                        <button type="submit" class="btn btn-danger">Ya, Batalkan Pesanan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak, Kembali</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Alert untuk notifikasi
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000);
        </script>
    @endpush
@endsection