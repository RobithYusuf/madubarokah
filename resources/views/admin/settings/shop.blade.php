@extends('layouts.app')

@section('title', 'Pengaturan Toko')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Pengaturan Toko</h1>
        <p class="text-gray-500 mt-2 mb-0">Kelola informasi dan pengaturan dasar toko Anda</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-cogs mr-2"></i>Pengaturan Toko
                </h6>
            </div>
            <div class="card-body">
                @if (session('success'))
                <script>
                    $(document).ready(function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: '{{ session('success') }}',
                            showConfirmButton: false,
                            timer: 3000,
                            toast: true,
                            position: 'top-end',
                            timerProgressBar: true
                        });
                    });
                </script>
                @endif

                @if (session('warning'))
                <script>
                    $(document).ready(function() {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan!',
                            text: '{{ session('warning') }}',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ffc107'
                        });
                    });
                </script>
                @endif

                @if ($errors->any())
                <script>
                    $(document).ready(function() {
                        let errorMessages = '';
                        @foreach ($errors->all() as $error)
                            errorMessages += '{{ $error }}\n';
                        @endforeach
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessages,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                </script>
                @endif

                <form action="{{ route('admin.settings.shop.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Informasi Toko -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-store mr-2"></i>Informasi Toko
                            </h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Toko <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $settings['name']) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tagline">Tagline/Slogan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tagline" name="tagline"
                                    value="{{ old('tagline', $settings['tagline']) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">Alamat Toko <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="2" required>{{ old('address', $settings['address']) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone">No. Telepon <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ old('phone', $settings['phone']) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email Toko <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $settings['email']) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="logo">Logo Toko</label>
                                <input type="file" class="form-control-file" id="logo" name="logo" accept="image/*">
                                @if(isset($settings['logo']) && $settings['logo'])
                                <small class="text-muted">Logo saat ini: {{ $settings['logo'] }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Sosial Media -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-share-alt mr-2"></i>Sosial Media & Kontak
                            </h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="whatsapp">WhatsApp (dengan kode negara)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fab fa-whatsapp text-success"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                        value="{{ old('whatsapp', $settings['whatsapp'] ?? '') }}" placeholder="628123456789">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="instagram">Instagram</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fab fa-instagram text-danger"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="instagram" name="instagram"
                                        value="{{ old('instagram', $settings['instagram'] ?? '') }}" placeholder="@username">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="facebook">Facebook</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fab fa-facebook text-primary"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="facebook" name="facebook"
                                        value="{{ old('facebook', $settings['facebook'] ?? '') }}" placeholder="username">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Pengaturan Pengiriman -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-shipping-fast mr-2"></i>Pengaturan Pengiriman
                            </h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="warehouse_city_id">Kota Gudang/Origin <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="warehouse_city_id" name="warehouse_city_id"
                                    value="{{ old('warehouse_city_id', $settings['warehouse_city_id']) }}" required>
                                <small class="text-muted">ID kota sesuai RajaOngkir (209 = Kota Kudus)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                            </button>
                            <button type="reset" class="btn btn-secondary ml-2">
                                <i class="fas fa-undo mr-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Info Card -->
<div class="row">
    <div class="col-lg-12">
        <div class="card border-left-info shadow mb-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Informasi Pengaturan
                        </div>
                        <div class="text-dark">
                            <small>
                                <strong>Tips:</strong><br>
                                • Pastikan informasi kontak selalu update<br>
                                • WhatsApp dengan format: 628123456789 (gunakan 62 untuk Indonesia)<br>
                                • Warehouse City ID harus sesuai dengan data RajaOngkir<br>
                                • Logo yang direkomendasikan: 500x500px, format PNG/JPG, maksimal 2MB
                            </small>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- SweetAlert2 CDN (jika belum ada di layout) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Format phone number inputs
        $('#phone, #whatsapp').on('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/\D/g, '');
        });

        // Preview logo before upload
        $('#logo').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // You can add image preview here if needed
                    console.log('Logo selected:', file.name);
                };
                reader.readAsDataURL(file);
            }
        });

        // Validate minimum order vs free shipping
        $('#minimum_order, #free_shipping_minimum').on('input', function() {
            const minOrder = parseFloat($('#minimum_order').val()) || 0;
            const freeShipping = parseFloat($('#free_shipping_minimum').val()) || 0;

            if (freeShipping > 0 && freeShipping < minOrder) {
                $(this).addClass('border-warning');
                if (!$('#shipping-warning').length) {
                    $(this).after('<small id="shipping-warning" class="text-warning">Nilai gratis ongkir sebaiknya lebih besar dari minimum order</small>');
                }
            } else {
                $(this).removeClass('border-warning');
                $('#shipping-warning').remove();
            }
        });
        
        // Form submission confirmation
        $('form').on('submit', function(e) {
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            // Add loading state
            submitBtn.prop('disabled', true)
                     .html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
            
            // Reset button after delay if form doesn't redirect
            setTimeout(() => {
                submitBtn.prop('disabled', false).html(originalText);
            }, 5000);
        });
    });
</script>
@endpush