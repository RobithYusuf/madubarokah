<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Toko Madu Barokah - Madu Asli dan Berkualitas">
    <meta name="author" content="Madu Barokah">

    <title>Toko Madu Barokah - @yield('title')</title>

    <!-- Fonts -->
    <link href="{{ asset('assets/sbadmin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700,800&display=swap" rel="stylesheet">

    <!-- Core CSS -->
    <link href="{{ asset('assets/sbadmin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">

    <!-- Custom CSS for better layout -->
    <style>
        #wrapper {
            min-height: 100vh;
        }
        #content-wrapper {
            min-height: 100vh;
            width: 100%;
        }
        .sticky-footer {
            margin-top: auto;
        }
        .container-fluid {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            max-width: 100%;
        }
        /* Remove gaps */
        .sidebar {
            z-index: 1000;
        }
        #content {
            width: 100%;
            padding: 0;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
        }
        /* Footer alignment */
        .sticky-footer .container-fluid {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .sticky-footer {
            flex-shrink: 0;
            height: auto;
            min-height: 50px;
        }
    </style>

    @stack('styles') {{-- Untuk tambahan CSS di halaman lain --}}
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        @include('partials.sidebar')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                @include('partials.topbar')

                <!-- Begin Page Content -->
                <div class="container-fluid px-4 py-3">
                    @yield('content')
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            @include('partials.footer')

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    @include('partials.logout-modal')

        <!-- Core JavaScript-->    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/sbadmin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/sbadmin/js/sb-admin-2.min.js') }}"></script>

    <!-- Charts -->
    <script src="{{ asset('assets/sbadmin/vendor/chart.js/Chart.min.js') }}"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    
    <!-- Common Functions -->
    <script>
        // Format rupiah untuk input harga
        function formatRupiah(input) {
            let angka = input.value.replace(/\D/g, ""); 
            let rupiah = new Intl.NumberFormat("id-ID").format(angka);
            input.value = rupiah;
        }
        
        // Auto-hide alerts after 3 seconds
        $(document).ready(function() {
            // Setup DataTables default options
            $.extend(true, $.fn.dataTable.defaults, {
                responsive: true,
                language: {
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Halaman _PAGE_ dari _PAGES_",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(dari _MAX_ total data)",
                    "search": "Cari:",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "»",
                        "previous": "«"
                    }
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50]
            });
            
            // Setup default select2 options
            if($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap',
                    placeholder: "Pilih opsi",
                    allowClear: true
                });
            }
            
            // Auto-hide alerts
            setTimeout(function() {
                $('.alert-success, .alert-danger').fadeOut(500, function() {
                    $(this).remove();
                });
            }, 3000);
        });
    </script>
    
    @stack('scripts') {{-- Untuk tambahan script di halaman lain --}}
</body>

</html>
