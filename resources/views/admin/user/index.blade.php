@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
<div id="content">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h4 class="m-0 font-weight-bold text-primary">Manajemen User</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah"><i
                        class="fa fa-plus me-1"></i> Tambah User</button>
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
                <div class="dropdown-divider"></div>
                <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0" id="tabelUser">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>No HP</th>
                            <th>Alamat</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->nama }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->nohp ?? '-' }}</td>
                            <td>{{ $user->alamat ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $user->role == 'admin' ? 'bg-primary' : 'bg-secondary' }} text-white">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#modalEdit{{ $user->id }}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#modalHapus{{ $user->id }}" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit User -->
                        <div class="modal fade" id="modalEdit{{ $user->id }}" tabindex="-1"
                            aria-labelledby="modalEditLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditLabel">Edit User</h5>
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
                                                        <label for="edit_nama{{ $user->id }}" class="form-label">Nama</label>
                                                        <input type="text" class="form-control"
                                                            name="nama" id="edit_nama{{ $user->id }}"
                                                            value="{{ $user->nama }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_username{{ $user->id }}" class="form-label">Username</label>
                                                        <input type="text" class="form-control"
                                                            name="username" id="edit_username{{ $user->id }}"
                                                            value="{{ $user->username }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_password{{ $user->id }}" class="form-label">Password</label>
                                                        <input type="password" class="form-control"
                                                            name="password" id="edit_password{{ $user->id }}"
                                                            placeholder="Kosongkan jika tidak ingin mengubah">
                                                        <small class="text-muted">Minimal 6 karakter</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_nohp{{ $user->id }}" class="form-label">No HP</label>
                                                        <input type="text" class="form-control"
                                                            name="nohp" id="edit_nohp{{ $user->id }}"
                                                            value="{{ $user->nohp }}" maxlength="15">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_role{{ $user->id }}" class="form-label">Role</label>
                                                        <select class="form-control" name="role" id="edit_role{{ $user->id }}" required>
                                                            <option value="pembeli" {{ $user->role == 'pembeli' ? 'selected' : '' }}>Pembeli</option>
                                                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_alamat{{ $user->id }}" class="form-label">Alamat</label>
                                                        <textarea class="form-control" name="alamat" 
                                                            id="edit_alamat{{ $user->id }}" rows="3">{{ $user->alamat }}</textarea>
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

                        <!-- Modal Hapus User -->
                        <div class="modal fade" id="modalHapus{{ $user->id }}" tabindex="-1"
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
                                        <p>Apakah Anda yakin ingin menghapus user "{{ $user->nama }}" ini?</p>
                                        @if($user->id === auth()->id())
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Anda tidak dapat menghapus akun yang sedang digunakan.
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer justify-content-end">
                                        <form method="POST" action="{{ route('admin.user.destroy', $user->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger" 
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>Hapus</button>
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

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">Tambah User</h5>
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
                                <input type="text" class="form-control" name="nama" id="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" id="password" required>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nohp" class="form-label">No HP</label>
                                <input type="text" class="form-control" name="nohp" id="nohp" maxlength="15">
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-control" name="role" id="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="pembeli">Pembeli</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat" id="alamat" rows="3"></textarea>
                            </div>
                        </div>
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
    $(document).ready(function() {
        $('#tabelUser').DataTable({
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
            columnDefs: [
                { 
                    targets: [0, 6], // No dan Aksi
                    orderable: false
                }
            ]
        });
        
        // Modal handlers
        $('#modalTambah').on('hidden.bs.modal', function() {
            $(this).find('form').trigger('reset');
        });
    });

    // Alert auto hide
    setTimeout(function() {
        $('#alertSuccess, #alertError').fadeOut(500, function() {
            $(this).remove();
        });
    }, 3000);
</script>
@endpush
@endsection
