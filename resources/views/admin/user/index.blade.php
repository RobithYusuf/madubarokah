@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')

{{-- AWAL BAGIAN HEADER HALAMAN --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Manajemen Pengguna</h1>
        <p class="text-gray-500 mt-2 mb-0">Kelola semua akun pengguna (admin dan pembeli) dalam sistem Anda.</p>
    </div>
    {{-- Opsional: Tombol aksi di header bisa ditambahkan di sini jika perlu --}}
    {{-- <a href="{{ route('admin.laporan.pelanggan') }}" class="btn btn-sm btn-outline-info shadow-sm">
    <i class="fas fa-users fa-sm"></i> Laporan Pelanggan
    </a> --}}
</div>
{{-- AKHIR BAGIAN HEADER HALAMAN --}}

<div id="content"
    data-modal-add="<?php echo session('modal_add_error') ? 'true' : 'false'; ?>"
    data-modal-edit="<?php echo session('modal_edit_error') ? session('modal_edit_error') : 'false'; ?>">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h4 class="m-0 font-weight-bold text-primary">Daftar Pengguna Sistem</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah"><i
                        class="fa fa-plus me-1"></i> Tambah Pengguna</button> {{-- Diubah "User" menjadi "Pengguna" --}}
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
                <div class="dropdown-divider mb-3"></div> {{-- Ditambah margin bottom --}}
                <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0" id="tabelUser">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Nama</th>
                            <th width="15%">Username</th>
                            <th width="15%">No HP</th>
                            <th width="25%">Alamat</th>
                            <th width="10%">Role</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->nama }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->nohp ?? '-' }}</td>
                            <td><small>{{ $user->alamat ?? '-' }}</small></td> {{-- Alamat dibuat small --}}
                            <td>
                                @if($user->role == 'admin')
                                <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
                                @else
                                <span class="badge badge-secondary">{{ ucfirst($user->role) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#modalEdit{{ $user->id }}" title="Edit Pengguna"> {{-- Diubah title --}}
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#modalHapus{{ $user->id }}" title="Hapus Pengguna" {{-- Diubah title --}}
                                        {{ $user->id === auth()->id() ? 'disabled' : '' }}> {{-- Tombol hapus dinonaktifkan jika user adalah diri sendiri --}}
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEdit{{ $user->id }}" tabindex="-1"
                            aria-labelledby="modalEditLabel{{ $user->id }}" aria-hidden="true"> {{-- ID Label unik --}}
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditLabel{{ $user->id }}">Edit Pengguna: {{ $user->nama }}</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="{{ route('admin.user.update', $user->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_nama{{ $user->id }}" class="form-label">Nama <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control"
                                                            name="nama" id="edit_nama{{ $user->id }}"
                                                            value="{{ old('nama', $user->nama) }}" required> {{-- Ditambah old() --}}
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_username{{ $user->id }}" class="form-label">Username <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control"
                                                            name="username" id="edit_username{{ $user->id }}"
                                                            value="{{ old('username', $user->username) }}" required> {{-- Ditambah old() --}}
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_password{{ $user->id }}" class="form-label">Password Baru</label>
                                                        <input type="password" class="form-control"
                                                            name="password" id="edit_password{{ $user->id }}"
                                                            placeholder="Kosongkan jika tidak ingin mengubah">
                                                        <small class="text-muted">Minimal 6 karakter. Kosongkan jika tidak ingin diubah.</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_nohp{{ $user->id }}" class="form-label">No HP</label>
                                                        <input type="text" class="form-control"
                                                            name="nohp" id="edit_nohp{{ $user->id }}"
                                                            value="{{ old('nohp', $user->nohp) }}" maxlength="15"> {{-- Ditambah old() --}}
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_role{{ $user->id }}" class="form-label">Role <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="role" id="edit_role{{ $user->id }}" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                            <option value="pembeli" {{ old('role', $user->role) == 'pembeli' ? 'selected' : '' }}>Pembeli</option>
                                                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                                        </select>
                                                        @if($user->id === auth()->id())
                                                        <input type="hidden" name="role" value="{{ $user->role }}"> {{-- Kirim role asli jika disabled --}}
                                                        <small class="text-warning">Role akun sendiri tidak dapat diubah.</small>
                                                        @endif
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_alamat{{ $user->id }}" class="form-label">Alamat</label>
                                                        <textarea class="form-control" name="alamat"
                                                            id="edit_alamat{{ $user->id }}" rows="3">{{ old('alamat', $user->alamat) }}</textarea> {{-- Ditambah old() --}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-end">
                                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modalHapus{{ $user->id }}" tabindex="-1"
                            aria-labelledby="modalHapusLabel{{ $user->id }}" aria-hidden="true"> {{-- ID Label unik --}}
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalHapusLabel{{ $user->id }}">Konfirmasi Hapus Pengguna</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Anda yakin ingin menghapus pengguna <strong>"{{ $user->nama }}"</strong> ({{ $user->username }}) ini?</p>
                                        @if($user->id === auth()->id())
                                        <div class="alert alert-danger small p-2"> {{-- Dibuat danger --}}
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Anda tidak dapat menghapus akun yang sedang Anda gunakan.
                                        </div>
                                        @else
                                        <div class="alert alert-warning small p-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Tindakan ini tidak dapat dibatalkan.
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer justify-content-end">
                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                                        <form method="POST" action="{{ route('admin.user.destroy', $user->id) }}" class="d-inline"> {{-- Ditambah class d-inline --}}
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}><i class="fas fa-trash-alt me-2"></i>Ya, Hapus</button>
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
                <h5 class="modal-title" id="modalTambahLabel">Tambah Pengguna Baru</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.user.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" id="nama" value="{{ old('nama') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" id="username" value="{{ old('username') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" id="password" required>
                                <small class="text-muted">Minimal 6 karakter.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nohp" class="form-label">No HP</label>
                                <input type="text" class="form-control" name="nohp" id="nohp" value="{{ old('nohp') }}" maxlength="15" placeholder="Contoh: 081234567890">
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-control" name="role" id="role" required>
                                    <option value="" selected disabled>Pilih Role</option> {{-- Ditambah placeholder --}}
                                    <option value="pembeli" {{ old('role') == 'pembeli' ? 'selected' : '' }}>Pembeli</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat" id="alamat" rows="3">{{ old('alamat') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i>Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Custom style untuk select role agar sesuai dengan SB Admin theme */
    .form-control select,
    select.form-control {
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        padding-right: 2.5rem;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        color: #6e707e;
        font-size: 0.875rem;
        height: calc(1.5em + 0.75rem + 2px);
        line-height: 1.5;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control select:focus,
    select.form-control:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        outline: 0;
    }

    .form-control select:disabled,
    select.form-control:disabled {
        background-color: #eaecf4;
        opacity: 1;
    }

    /* Badge styling untuk konsistensi SB Admin */
    .badge-primary {
        color: #fff;
        background-color: #4e73df;
    }

    .badge-secondary {
        color: #fff;
        background-color: #858796;
    }

    .badge {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.35rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#tabelUser').DataTable({
            responsive: true,
            paging: true,
            lengthMenu: [10, 25, 50, 100], // Ditambah opsi 100
            pageLength: 10,
            order: [
                [1, 'asc']
            ], // Urutkan berdasarkan Nama (index 1) menaik
            language: { // Diringkas
                "lengthMenu": "Tampil _MENU_",
                "zeroRecords": "Pengguna tidak ditemukan",
                "info": "Hal _PAGE_ dari _PAGES_ (_TOTAL_ total)",
                "infoEmpty": "Tidak ada pengguna",
                "infoFiltered": "(dari _MAX_ total)",
                "search": "Cari:",
                "paginate": {
                    "first": "<<",
                    "last": ">>",
                    "next": ">",
                    "previous": "<"
                }
            },
            columnDefs: [{
                targets: [0, 6], // No dan Aksi
                orderable: false,
                searchable: false // Kolom No dan Aksi tidak perlu dicari
            }]
        });

        // Modal handlers
        $('#modalTambah').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset(); // Menggunakan [0].reset() untuk form DOM asli
        });

        // Handle modal errors dengan data attributes (aman dari auto formatter)
        var contentDiv = document.getElementById('content');
        var showModalAdd = contentDiv.getAttribute('data-modal-add') === 'true';
        var showModalEdit = contentDiv.getAttribute('data-modal-edit');

        if (showModalAdd) {
            var modalTambah = new bootstrap.Modal(document.getElementById('modalTambah'));
            modalTambah.show();
        }

        if (showModalEdit && showModalEdit !== 'false') {
            var modalEditId = "#modalEdit" + showModalEdit;
            if ($(modalEditId).length) {
                var modalInstance = new bootstrap.Modal(document.querySelector(modalEditId));
                modalInstance.show();
            }
        }
    });

    // Alert auto hide
    setTimeout(function() {
        $('#alertSuccess, #alertError').fadeOut(500, function() {
            // Setelah fadeOut, hapus elemen dari DOM agar tidak mengganggu layout
            $(this).remove();
        });
    }, 3500); // Durasi sedikit diperpanjang
</script>
@endpush
@endsection