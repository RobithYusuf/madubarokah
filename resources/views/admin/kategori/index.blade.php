@extends('layouts.app')

@section('title', 'Daftar Kategori')

@section('content')

{{-- TAMBAHKAN BAGIAN INI UNTUK JUDUL DAN DESKRIPSI --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Manajemen Kategori</h1>
        <p class="text-gray-500 mt-2 mb-0">Kelola daftar kategori produk Anda di sini. Anda dapat menambah, mengubah, dan menghapus kategori.</p>
    </div>
    {{-- Opsional: Anda bisa menambahkan tombol aksi di sini jika perlu --}}
    {{-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
    </a> --}}
</div>
{{-- AKHIR BAGIAN TAMBAHAN --}}

<div id="content">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            {{-- Judul di dalam card bisa dipertahankan atau disesuaikan --}}
            <h4 class="m-0 font-weight-bold text-primary">Daftar Kategori Produk</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah"><i
                        class="fa fa-plus me-1"></i> Tambah Kategori</button>
                @if (session('success'))
                <div id="alertSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                <div class="dropdown-divider"></div>
                <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0" id="tabelKategori">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Warna Label</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kategoris as $index => $kategori)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    {{ $kategori->nama_kategori }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="color-preview"
                                        style="width: 20px; height: 20px; background-color: {{ $kategori->warna ?? '#6C757D' }}; border-radius: 4px; margin-right: 8px; border: 1px solid #ddd;"></div>
                                    <span class="badge text-white"
                                        style="background-color: {{ $kategori->warna ?? '#6C757D' }}; color: white;">
                                        {{ $kategori->warna ?? '#6C757D' }}
                                    </span>
                                </div>
                            </td>
                            <td>{{ $kategori->deskripsi }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#modalEdit{{ $kategori->id }}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#modalHapus{{ $kategori->id }}" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEdit{{ $kategori->id }}" tabindex="-1"
                            aria-labelledby="modalEditLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditLabel">Edit Kategori</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="{{ route('admin.kategori.update', $kategori->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label for="edit_nama_kategori" class="form-label">Nama Kategori</label>
                                                <input type="text" class="form-control"
                                                    name="nama_kategori"
                                                    value="{{ $kategori->nama_kategori }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_warna" class="form-label">Warna Label Kategori</label>
                                                <div class="d-flex align-items-center mb-2">
                                                    <input type="color" class="form-control form-control-color me-2" name="warna" value="{{ $kategori->warna ?? '#6C757D' }}" style="width: 60px; height: 40px;" onchange="updateColorPreview(this, 'edit{{ $kategori->id }}')" required>
                                                    <input type="text" class="form-control" value="{{ $kategori->warna ?? '#6C757D' }}" id="colorText_edit{{ $kategori->id }}" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" onchange="updateColorPicker(this, 'edit{{ $kategori->id }}')" style="width: 100px;">
                                                    <div class="color-preview ms-2" id="colorPreview_edit{{ $kategori->id }}" style="width: 40px; height: 40px; background-color: {{ $kategori->warna ?? '#6C757D' }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    <span class="badge color-quick"
                                                        style="background-color: #FF6B6B; cursor: pointer; width: 25px; height: 25px;"
                                                        onclick="setQuickColor('#FF6B6B', 'edit{{ $kategori->id }}')" title="Merah Muda"></span>
                                                    <span class="badge color-quick"
                                                        style="background-color: #4ECDC4; cursor: pointer; width: 25px; height: 25px;"
                                                        onclick="setQuickColor('#4ECDC4', 'edit{{ $kategori->id }}')" title="Tosca"></span>
                                                    <span class="badge color-quick"
                                                        style="background-color: #45B7D1; cursor: pointer; width: 25px; height: 25px;"
                                                        onclick="setQuickColor('#45B7D1', 'edit{{ $kategori->id }}')" title="Biru Langit"></span>
                                                    <span class="badge color-quick"
                                                        style="background-color: #F39C12; cursor: pointer; width: 25px; height: 25px;"
                                                        onclick="setQuickColor('#F39C12', 'edit{{ $kategori->id }}')" title="Oranye"></span>
                                                    <span class="badge color-quick"
                                                        style="background-color: #8E44AD; cursor: pointer; width: 25px; height: 25px;"
                                                        onclick="setQuickColor('#8E44AD', 'edit{{ $kategori->id }}')" title="Ungu"></span>
                                                    <span class="badge color-quick"
                                                        style="background-color: #E74C3C; cursor: pointer; width: 25px; height: 25px;"
                                                        onclick="setQuickColor('#E74C3C', 'edit{{ $kategori->id }}')" title="Merah"></span>
                                                    <span class="badge color-quick"
                                                        style="background-color: #2ECC71; cursor: pointer; width: 25px; height: 25px;"
                                                        onclick="setQuickColor('#2ECC71', 'edit{{ $kategori->id }}')" title="Hijau"></span>
                                                    <span class="badge color-quick"
                                                        style="background-color: #34495E; cursor: pointer; width: 25px; height: 25px;"
                                                        onclick="setQuickColor('#34495E', 'edit{{ $kategori->id }}')" title="Abu Gelap"></span>
                                                </div>
                                                <small class="text-muted">Pilih warna untuk kategori ini (format: #RRGGBB) atau klik warna cepat di atas</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                                <textarea class="form-control" name="deskripsi" required>{{ $kategori->deskripsi }}</textarea>
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

                        <div class="modal fade" id="modalHapus{{ $kategori->id }}" tabindex="-1"
                            aria-labelledby="modalHapusLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalHapusLabel">Konfirmasi Hapus</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Apakah Anda yakin ingin menghapus kategori ini?</p>
                                    </div>
                                    <div class="modal-footer justify-content-end">
                                        <form method="POST" action="{{ route('admin.kategori.destroy', $kategori->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Hapus</button>
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

<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">Tambah Kategori</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.kategori.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" id="nama_kategori">
                    </div>
                    <div class="mb-3">
                        <label for="warna" class="form-label">Warna Kategori</label>
                        <div class="d-flex align-items-center mb-2">
                            <input type="color" class="form-control form-control-color me-2" name="warna" id="warna" value="#6C757D" style="width: 60px; height: 40px;" onchange="updateColorPreview(this, 'add')" required>
                            <input type="text" class="form-control" value="#6C757D" id="colorText_add" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" onchange="updateColorPicker(this, 'add')" style="width: 100px;">
                            <div class="color-preview ms-2" id="colorPreview_add" style="width: 40px; height: 40px; background-color: #6C757D; border-radius: 4px; border: 1px solid #ddd;"></div>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge color-quick"
                                style="background-color: #FF6B6B; cursor: pointer; width: 25px; height: 25px;"
                                onclick="setQuickColor('#FF6B6B', 'add')" title="Merah Muda"></span>
                            <span class="badge color-quick"
                                style="background-color: #4ECDC4; cursor: pointer; width: 25px; height: 25px;"
                                onclick="setQuickColor('#4ECDC4', 'add')" title="Tosca"></span>
                            <span class="badge color-quick"
                                style="background-color: #45B7D1; cursor: pointer; width: 25px; height: 25px;"
                                onclick="setQuickColor('#45B7D1', 'add')" title="Biru Langit"></span>
                            <span class="badge color-quick"
                                style="background-color: #F39C12; cursor: pointer; width: 25px; height: 25px;"
                                onclick="setQuickColor('#F39C12', 'add')" title="Oranye"></span>
                            <span class="badge color-quick"
                                style="background-color: #8E44AD; cursor: pointer; width: 25px; height: 25px;"
                                onclick="setQuickColor('#8E44AD', 'add')" title="Ungu"></span>
                            <span class="badge color-quick"
                                style="background-color: #E74C3C; cursor: pointer; width: 25px; height: 25px;"
                                onclick="setQuickColor('#E74C3C', 'add')" title="Merah"></span>
                            <span class="badge color-quick"
                                style="background-color: #2ECC71; cursor: pointer; width: 25px; height: 25px;"
                                onclick="setQuickColor('#2ECC71', 'add')" title="Hijau"></span>
                            <span class="badge color-quick"
                                style="background-color: #34495E; cursor: pointer; width: 25px; height: 25px;"
                                onclick="setQuickColor('#34495E', 'add')" title="Abu Gelap"></span>
                        </div>
                        <small class="text-muted">Pilih warna untuk kategori ini (format: #RRGGBB) atau klik warna cepat di atas</small>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>
                    </div>
                    <div class="modal-footer justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
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
        if (alert) {
            alert.style.transition = "opacity 0.5s ease-out";
            alert.style.opacity = "0"; // Fade out

            setTimeout(() => {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close(); // Tutup alert setelah fade-out
            }, 500); // Tunggu animasi selesai sebelum ditutup
        }
    }, 3000); // 3 detik sebelum mulai menghilang

    // DataTable initialization
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

    // Color picker functions
    function updateColorPreview(colorInput, suffix) {
        const color = colorInput.value;
        const textInput = document.getElementById('colorText_' + suffix);
        const preview = document.getElementById('colorPreview_' + suffix);

        textInput.value = color;
        preview.style.backgroundColor = color;

        // Update the actual form input value
        colorInput.value = color;
        // textInput.setAttribute('name', 'warna'); // Ini tidak perlu karena input color sudah punya name="warna"
    }

    function updateColorPicker(textInput, suffix) {
        const color = textInput.value;

        // Validate hex color format
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            const colorInput = textInput.closest('.d-flex').querySelector('input[type="color"]'); // Disesuaikan selectornya
            const preview = document.getElementById('colorPreview_' + suffix);

            if (colorInput) colorInput.value = color; // Pastikan colorInput ada
            if (preview) preview.style.backgroundColor = color; // Pastikan preview ada
            textInput.style.borderColor = '';
        } else {
            textInput.style.borderColor = 'red';
        }
    }

    function setQuickColor(color, suffix) {
        const colorInput = document.querySelector(`#modal${suffix === 'add' ? 'Tambah' : 'Edit' + suffix.replace('edit', '')} input[type="color"][name="warna"]`);
        const textInput = document.getElementById('colorText_' + suffix);
        const preview = document.getElementById('colorPreview_' + suffix);

        if (colorInput && textInput && preview) {
            colorInput.value = color;
            textInput.value = color;
            preview.style.backgroundColor = color;
            textInput.style.borderColor = '';
        }
    }

    // Sync color inputs on modal show for Edit
    $('[id^="modalEdit"]').on('shown.bs.modal', function() {
        const modalInstance = this; // Simpan referensi ke modal
        const modalId = modalInstance.id;
        const suffix = 'edit' + modalId.replace('modalEdit', ''); // Buat suffix yang benar untuk elemen edit
        const colorInput = modalInstance.querySelector('input[type="color"][name="warna"]');
        const textInput = modalInstance.querySelector('#colorText_' + suffix); // Gunakan selector yang benar

        if (colorInput && textInput) {
            // Set initial values from color input to text input and preview
            // Ini mungkin tidak perlu jika value sudah di-set dari backend dengan benar
            // updateColorPreview(colorInput, suffix); // Panggil ini untuk sinkronisasi awal jika perlu

            textInput.addEventListener('input', function() {
                updateColorPicker(this, suffix);
            });
            colorInput.addEventListener('input', function() {
                 updateColorPreview(this, suffix);
            });
        }
    });

    // Sync color input for Add modal (jika belum ada event listener)
    const modalTambahNode = document.getElementById('modalTambah');
    if (modalTambahNode) {
        const colorInputAdd = modalTambahNode.querySelector('input[type="color"][name="warna"]');
        const textInputAdd = modalTambahNode.querySelector('#colorText_add');

        if (colorInputAdd && textInputAdd) {
             colorInputAdd.addEventListener('input', function() {
                 updateColorPreview(this, 'add');
             });
             textInputAdd.addEventListener('input', function() {
                updateColorPicker(this, 'add');
             });
        }
    }


    // Reset form on modal close
    $('#modalTambah').on('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        if (form) {
            form.reset();
            // Reset warna secara manual karena form.reset() mungkin tidak mengembalikan value input color ke default awal
            const defaultColor = '#6C757D';
            const colorInput = form.querySelector('input[type="color"][name="warna"]');
            const textInput = form.querySelector('#colorText_add');
            const preview = form.querySelector('#colorPreview_add');

            if(colorInput) colorInput.value = defaultColor;
            if(textInput) textInput.value = defaultColor;
            if(preview) preview.style.backgroundColor = defaultColor;
        }
    });
</script>
@endpush
@endsection