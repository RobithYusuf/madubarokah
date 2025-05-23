@extends('layouts.app')

@section('title', 'Daftar produk')

@section('content')
    <div id="content">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Manajemen Produk</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah"><i
                            class="fa fa-plus me-1"></i> Tambah produk</button>
                    @if (session('success'))
                        <div id="alertSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
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
                                                alt="{{ $produk->nama_produk }}" class="img-thumbnail" width="100">
                                        @else
                                            <span>Tidak ada gambar</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i
                                                class="fa fa-tag text-{{ $produk->kategori->warna ?? 'secondary' }} me-1"></i>
                                            <span class="badge bg-{{ $produk->kategori->warna ?? 'secondary' }}">
                                                {{ $produk->kategori->nama_kategori ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ $produk->nama_produk }}</td>
                                    <td>{{ $produk->deskripsi }}</td>
                                    <td>{{ 'Rp ' . number_format($produk->harga, 0, ',', '.') }}</td>
                                    <td>{{ $produk->stok }}</td>
                                    <td>
                                        <!-- Tombol Edit -->
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalEdit{{ $produk->id }}">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalHapus{{ $produk->id }}">
                                            <i class="fa fa-trash me-1"></i> Hapus
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Edit Produk -->
                                <div class="modal fade" id="modalEdit{{ $produk->id }}" tabindex="-1"
                                    aria-labelledby="modalEditLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalEditLabel">Edit Produk</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('produk.update', $produk->id) }}" method="POST"
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
                                                                <select class="form-control select2" name="id_kategori"
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
                                                    <button type="submit" class="btn btn-primary">Simpan
                                                        Perubahan</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Hapus produk -->
                                <div class="modal fade" id="modalHapus{{ $produk->id }}" tabindex="-1"
                                    aria-labelledby="modalHapusLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalHapusLabel">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus produk " {{ $produk->nama_produk }} "
                                                    ini?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <form method="POST" action="{{ route('produk.destroy', $produk->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Batal</button>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
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
                                    <select class="form-control select2" name="id_kategori" id="id_kategori">
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
                // Munculkan dropdown kategori
                $('#modalTambah').on('shown.bs.modal', function() {
                    $('#id_kategori').select2({
                        theme: 'bootstrap-5',
                        placeholder: "Pilih Kategori",
                        allowClear: true,
                        dropdownParent: $('#modalTambah') // Penting untuk Select2 dalam modal
                    });
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

            // Alert Untuk Berhasil Simpan data
            setTimeout(function() {
                var alert = document.getElementById('alertSuccess');
                alert.style.transition = "opacity 0.5s ease-out";
                alert.style.opacity = "0"; // Fade out

                setTimeout(() => {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close(); // Tutup alert setelah fade-out
                }, 500); // Tunggu animasi selesai sebelum ditutup
            }, 3000); // 3 detik sebelum mulai menghilang
        </script>
    @endpush
@endsection
