@extends('layouts.app')

@section('title', 'Daftar Kategori')

@section('content')
    <div id="content">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Manajemen Kategori</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah"><i
                            class="fa fa-plus me-1"></i> Tambah Kategori</button>
                    @if (session('success'))
                        <div id="alertSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <div class="dropdown-divider"></div>
                    <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0" id="tabelKategori">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kategoris as $index => $kategori)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $kategori->nama_kategori }}</td>
                                    <td>{{ $kategori->deskripsi }}</td>
                                    <td>
                                        <!-- Tombol Edit -->
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalEdit{{ $kategori->id }}">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalHapus{{ $kategori->id }}">
                                            <i class="fa fa-trash me-1"></i> Hapus
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Edit Kategori -->
                                <div class="modal fade" id="modalEdit{{ $kategori->id }}" tabindex="-1"
                                    aria-labelledby="modalEditLabel" aria-hidden="true">
                                    <div class="modal-dialog /modal-fullscreen/">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalEditLabel">Edit Kategori</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="{{ route('kategori.update', $kategori->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="mb-3">
                                                        <label for="edit_nama_kategori" class="form-label">Nama Kategori</label>
                                                        <input type="text" class="form-control"
                                                            name="nama_kategori"
                                                            value="{{ $kategori->nama_kategori }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                                        <textarea class="form-control" name="deskripsi" required>{{ $kategori->deskripsi }}</textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Hapus Kategori -->
                                <div class="modal fade" id="modalHapus{{ $kategori->id }}" tabindex="-1"
                                    aria-labelledby="modalHapusLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalHapusLabel">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus kategori ini?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <form method="POST" action="{{ route('kategori.destroy', $kategori->id) }}">
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

    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog /modal-fullscreen/">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('kategori.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" name="nama_kategori" id="nama_kategori">
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
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
        <script>
            $(document).ready(function() {
                $('#tabelKategori').DataTable({
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
            });
        </script>
    @endpush
@endsection
