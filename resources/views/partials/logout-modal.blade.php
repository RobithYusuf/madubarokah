<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Konfirmasi Logout
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-question-circle fa-3x text-warning"></i>
                </div>
                <h6 class="mb-3">Apakah Anda yakin ingin keluar?</h6>
                <p class="text-muted mb-0">
                    Anda akan keluar dari sistem dan harus login kembali untuk mengakses dashboard.
                </p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button class="btn btn-secondary px-4" type="button" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Batal
                </button>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        Ya, Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
