@extends('layouts.app')

@section('title', 'Daftar produk')

@section('content')
<div id="content">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Manajemen Produk</h1>
            <p class="text-gray-500 mt-2 mb-0">Kelola semua produk madu dan suplemen yang dijual di toko Anda</p>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h4 class="m-0 font-weight-bold text-primary">Manajemen Produk</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah"><i
                        class="fa fa-plus me-1"></i> Tambah produk</button>
                @if (session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: '{{ session('success') }}',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    });
                </script>
                @endif
                @if (session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: '{{ session('error') }}',
                            showConfirmButton: true
                        });
                    });
                </script>
                @endif
                <div class="dropdown-divider"></div>
                <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0"
                    id="tabelproduk">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Kategori</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Berat (gr)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produks as $index => $produk)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if ($produk->gambar)
                                <img src="{{ asset('storage/' . $produk->gambar) }}"
                                    alt="{{ $produk->nama_produk }}" class="img-thumbnail" width="50">
                                @else
                                <span>Tidak ada gambar</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-white" 
                                      style="background-color: {{ $produk->kategori->warna ?? '#6C757D' }}; color: white;">
                                    {{ $produk->kategori->nama_kategori ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $produk->nama_produk }}</td>
                            <td>{{ $produk->deskripsi }}</td>
                            <td>{{ 'Rp ' . number_format($produk->harga, 0, ',', '.') }}</td>
                            <td>{{ $produk->stok }}</td>
                            <td>{{ $produk->berat ?? 500 }} gr</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#modalEdit{{ $produk->id }}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="confirmDelete({{ $produk->id }}, '{{ $produk->nama_produk }}')" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $produk->id }}" action="{{ route('admin.produk.destroy', $produk->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Produk -->
                        <div class="modal fade" id="modalEdit{{ $produk->id }}" tabindex="-1"
                            aria-labelledby="modalEditLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditLabel">Edit Produk</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('admin.produk.update', $produk->id) }}" method="POST"
                                            enctype="multipart/form-data" id="formEditProduk">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="id" id="edit_id">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_nama_produk" class="form-label">Nama
                                                            Produk</label>
                                                        <input type="text" class="form-control"
                                                            name="nama_produk" id="edit_nama_produk"
                                                            value="{{ old('nama_produk', $produk->nama_produk ?? '') }}"
                                                            required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_id_kategori"
                                                            class="form-label">Kategori</label>
                                                        <select class="form-control custom-select" name="id_kategori"
                                                            id="edit_id_kategori">
                                                            <option value="">Pilih Kategori</option>
                                                            @foreach ($kategoris as $kategori)
                                                            <option value="{{ $kategori->id }}"
                                                                {{ old('id_kategori', $produk->id_kategori ?? '') == $kategori->id ? 'selected' : '' }}>
                                                                {{ $kategori->nama_kategori ?? '-' }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_deskripsi"
                                                            class="form-label">Deskripsi</label>
                                                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi">{{ old('deskripsi', $produk->deskripsi ?? '') }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_stok" class="form-label">Stok</label>
                                                        <input type="number" class="form-control" name="stok"
                                                            id="edit_stok" min="0"
                                                            value="{{ old('stok', $produk->stok ?? '') }}"
                                                            required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_berat" class="form-label">Berat (gram)</label>
                                                        <input type="number" class="form-control" name="berat"
                                                            id="edit_berat" min="1"
                                                            value="{{ old('berat', $produk->berat ?? 500) }}"
                                                            required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_harga" class="form-label">Harga</label>
                                                        <input type="text" class="form-control" name="harga"
                                                            id="edit_harga"
                                                            value="{{ old('harga', number_format($produk->harga ?? 0, 0, ',', '.')) }}"
                                                            required onkeyup="formatRupiah(this)">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_gambar" class="form-label">Gambar</label>
                                                        @if (isset($produk->gambar))
                                                        <div class="mt-2">
                                                            <img src="{{ asset('storage/' . $produk->gambar) }}"
                                                                alt="Gambar Produk" width="100">
                                                        </div>
                                                        @endif
                                                        <br>
                                                        <input type="file" class="form-control" name="gambar"
                                                            id="edit_gambar" accept="image/*">
                                                        <small class="text-muted ">Kosongkan jika tidak ingin
                                                            mengubah gambar.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-end">
                                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>


                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">Tambah Produk</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_produk" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" name="nama_produk" id="nama_produk"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="id_kategori" class="form-label">Kategori</label>
                                <select class="form-control custom-select" name="id_kategori" id="id_kategori">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori ?? '-' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" class="form-control" name="stok" id="stok"
                                    min="0" value="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="berat" class="form-label">Berat (gram)</label>
                                <input type="number" class="form-control" name="berat" id="berat"
                                    min="1" value="500" required>
                            </div>
                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga</label>
                                <input type="text" class="form-control" name="harga" id="harga" required
                                    onkeyup="formatRupiah(this)">
                            </div>
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar</label>
                                <input type="file" class="form-control" name="gambar" id="gambar"
                                    accept="image/*">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#tabelproduk').DataTable({
            responsive: true, // Membuat tabel responsive
            paging: true, // Mengaktifkan pagination
            lengthMenu: [5, 10, 25, 50], // Opsi jumlah data per halaman
            pageLength: 5, // Jumlah data default per halaman
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
        // Modal handlers
        $('#modalTambah').on('hidden.bs.modal', function() {
            $(this).find('form').trigger('reset');
        });
    });

    // Input format rupiah
    function formatRupiah(input) {
        let angka = input.value.replace(/\D/g, ""); // Menghapus semua karakter non-angka
        let rupiah = new Intl.NumberFormat("id-ID").format(angka); // Format ke Rupiah
        input.value = rupiah; // Set nilai input dengan format Rupiah
    }

    // Validasi stok
    function validasiStok(input) {
        if (input.value < 0) {
            input.value = 0; // Jika negatif, atur kembali ke 0
        }
    }

    // Fungsi konfirmasi hapus dengan SweetAlert
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Apakah Anda yakin ingin menghapus produk "${nama}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection