<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Laporan PDF' }} - {{ config('app.name') }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            /* Reduced base font size for PDF */
            line-height: 1.2;
            /* Slightly reduced line-height */
            color: #333;
            background: white;
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
            /* Main page padding */
        }

        /* Kop Surat */
        .letterhead {
            border-bottom: 2px solid #2c3e50;
            /* Thinner border */
            padding-bottom: 10px;
            /* Reduced padding */
            margin-bottom: 15px;
            /* Reduced margin */
            position: relative;
        }

        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            /* Reduced margin */
        }

        .company-info h1 {
            font-size: 20px;
            /* Slightly smaller company name */
            font-weight: bold;
            color: #2c3e50;
            letter-spacing: 0.5px;
            /* Reduced letter-spacing */
            margin-bottom: 2px;
            /* Reduced margin */
        }

        .company-info .tagline {
            font-size: 9px;
            /* Smaller tagline */
            color: #7f8c8d;
            font-style: italic;
        }

        .company-contact {
            text-align: right;
            font-size: 8px;
            /* Smaller contact info */
            color: #5d6d7e;
            line-height: 1.1;
            /* Tighter line height */
        }

        .report-title {
            text-align: center;
            margin: 10px 0 8px 0;
            /* Reduced margins */
        }

        .report-title h2 {
            font-size: 14px;
            /* Smaller report title */
            font-weight: bold;
            color: #34495e;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Info Period - More Compact */
        .info-period {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 6px 10px;
            /* Reduced padding */
            border-radius: 3px;
            /* Smaller border-radius */
            margin-bottom: 12px;
            /* Reduced margin */
            text-align: center;
            font-size: 9px;
            /* Smaller font size */
            border-left: 3px solid #3498db;
            /* Thinner border */
        }

        .info-period strong {
            color: #2c3e50;
        }

        /* Summary Cards - Even More Compact */
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            /* Reduced margin */
            gap: 6px;
            /* Reduced gap */
        }

        .summary-card {
            flex: 1;
            background: #ffffff;
            padding: 8px;
            /* Reduced padding */
            border-radius: 3px;
            /* Smaller border-radius */
            text-align: center;
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            /* Lighter shadow */
        }

        .summary-card h3 {
            /* Title of the card */
            font-size: 8px;
            /* Further reduced font size */
            color: #6c757d;
            margin-bottom: 3px;
            /* Reduced margin */
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .summary-card .value {
            font-size: 14px;
            /* Reduced value font size */
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 2px;
            /* Reduced margin */
        }

        .summary-card .label {
            /* Sub-label if any */
            font-size: 7px;
            /* Further reduced font size */
            color: #95a5a6;
        }

        /* Table Styling - Even More Compact */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            /* Reduced margin */
            font-size: 9px;
            /* Reduced table font size */
        }

        .data-table th,
        .data-table td {
            padding: 4px 6px;
            /* Reduced padding */
            text-align: left;
            border: 1px solid #e0e0e0;
            /* Lighter border */
            vertical-align: middle;
        }

        .data-table th {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            font-weight: 600;
            text-align: center;
            font-size: 8px;
            /* Reduced header font size */
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
            /* Lighter even row color */
        }

        .data-table tbody tr:hover {
            background-color: #eef6fc;
            /* Lighter hover */
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-success {
            color: #27ae60;
            font-weight: 600;
        }

        .text-danger {
            color: #e74c3c;
            font-weight: 600;
        }

        .text-warning {
            color: #f39c12;
            font-weight: 600;
        }

        .text-info {
            color: #3498db;
            font-weight: 600;
        }

        .text-muted {
            color: #95a5a6;
        }

        .text-primary {
            color: #2c3e50;
            font-weight: 600;
        }

        /* Badge Styling - More Compact */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            /* Reduced padding */
            border-radius: 8px;
            /* Smaller radius */
            font-size: 7px;
            /* Reduced font size */
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.1px;
        }

        .badge-success {
            background-color: #27ae60;
        }

        .badge-danger {
            background-color: #e74c3c;
        }

        .badge-warning {
            background-color: #f39c12;
        }

        .badge-info {
            background-color: #3498db;
        }

        .badge-secondary {
            background-color: #95a5a6;
        }

        .badge-primary {
            background-color: #2c3e50;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            /* Reduced margin */
            text-align: center;
            font-size: 7px;
            /* Reduced font size */
            color: #95a5a6;
            border-top: 1px solid #e0e0e0;
            /* Lighter border */
            padding-top: 8px;
            /* Reduced padding */
            page-break-inside: avoid;
        }

        .footer .generated-info {
            margin-bottom: 3px;
            /* Reduced margin */
        }

        .footer .company-footer {
            font-weight: 600;
            color: #7f8c8d;
        }

        /* Print Button */
        .no-print {
            display: block;
            margin-bottom: 10px;
            /* Reduced margin */
        }

        .print-btn,
        .close-btn {
            /* Combined for brevity */
            color: white;
            border: none;
            padding: 7px 14px;
            /* Reduced padding */
            border-radius: 3px;
            /* Smaller radius */
            cursor: pointer;
            font-size: 10px;
            /* Reduced font size */
            font-weight: 600;
            margin-right: 6px;
            /* Reduced margin */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            /* Lighter shadow */
            transition: all 0.2s ease;
            /* Faster transition */
        }

        .print-btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .close-btn {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        }

        .print-btn:hover {
            background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.3);
            /* Adjusted shadow */
        }

        .close-btn:hover {
            background: linear-gradient(135deg, #7f8c8d 0%, #5d6d7e 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(149, 165, 166, 0.3);
            /* Adjusted shadow */
        }

        /* Section Headers */
        .section-header {
            background: #ecf0f1;
            padding: 6px 10px;
            /* Reduced padding */
            margin: 12px 0 8px 0;
            /* Reduced margins */
            border-left: 3px solid #3498db;
            /* Thinner border */
            font-weight: 600;
            color: #2c3e50;
            font-size: 10px;
            /* Reduced font size */
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Responsive untuk mobile (less relevant for PDF, but kept for completeness) */
        @media (max-width: 768px) {
            body {
                padding: 5mm;
                font-size: 9px;
                /* Adjust if viewed on small screens before print */
            }

            .company-header {
                flex-direction: column;
                text-align: center;
            }

            .company-contact {
                text-align: center;
                margin-top: 8px;
            }

            .summary-cards {
                flex-direction: column;
                gap: 4px;
            }

            .data-table {
                font-size: 8px;
            }

            .data-table th,
            .data-table td {
                padding: 3px 5px;
            }
        }

        /* Print Specific Styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                /* Ensure no extra margins from browser */
                padding: 10mm 8mm;
                /* Top/Bottom, Left/Right for print */
                font-size: 9px;
                /* Consistent font size for actual print */
            }

            .letterhead,
            .summary-cards,
            .info-period,
            .section-header {
                page-break-inside: avoid;
            }

            .data-table {
                page-break-inside: auto;
            }

            .data-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .footer {
                font-size: 7px;
                padding-top: 5px;
            }
        }

        /* A4 Page Settings */
        @page {
            size: A4;
            margin: 12mm 8mm;
            /* Top/Bottom, Left/Right for @page rule */
        }

        /* Number formatting */
        .currency::before {
            content: "Rp ";
            font-weight: normal;
        }

        .number {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        /* Status indicators - More Compact */
        .status-pending,
        .status-success,
        .status-danger {
            padding: 1px 4px;
            /* Reduced padding */
            border-radius: 2px;
            /* Smaller radius */
            font-size: 7px;
            /* Reduced font size */
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="letterhead">
        <div class="company-header">
            <div class="company-info">
                <h1>{{ config('shop.name', config('app.name', 'MADUBAROKAH')) }}</h1>
                <div class="tagline">{{ config('shop.tagline', 'Sistem Informasi Manajemen Bisnis') }}</div>
            </div>
            <div class="company-contact">
                <div>Email: {{ config('shop.email', 'info@madubarokah.com') }}</div>
                <div>Telp: {{ config('shop.phone', '(021) 1234-5678') }}</div>
                <div>Website: {{-- Assuming you might add website to config/shop.php --}}
                    {{ config('shop.website', 'www.madubarokah.com') }}
                </div>
            </div>
        </div>

        <div class="report-title">
            <h2>{{ $title ?? 'Laporan Transaksi' }}</h2>
        </div>
    </div>

    <div class="info-period">
        <strong>Periode:</strong> {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @if(isset($status) && $status !== 'all')
        | <strong>Status:</strong> {{ ucfirst($status) }}
        @endif
        @if(isset($paymentStatus) && $paymentStatus !== 'all')
        | <strong>Pembayaran:</strong> {{ ucfirst($paymentStatus) }}
        @endif
        @if(isset($shippingStatus) && $shippingStatus !== 'all')
        | <strong>Pengiriman:</strong> {{ ucfirst(str_replace('_', ' ', $shippingStatus)) }}
        @endif
    </div>

    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="print-btn">
            📄 Cetak PDF
        </button>
        <button onclick="window.close()" class="close-btn">
            ✕ Tutup
        </button>
    </div>

    @if($type === 'transaksi')
    @include('admin.laporan.pdf.transaksi')
    @elseif($type === 'penjualan')
    @include('admin.laporan.pdf.penjualan')
    @elseif($type === 'pelanggan')
    @include('admin.laporan.pdf.pelanggan')
    @elseif($type === 'pengiriman')
    @include('admin.laporan.pdf.pengiriman')
    @elseif($type === 'produk')
    @include('admin.laporan.pdf.produk')
    @endif

    <div class="footer">
        <div class="generated-info">
            Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} WIB
        </div>
        <div class="company-footer">
            {{ config('shop.name', config('app.name', 'MADUBAROKAH')) }} - Laporan Sistem
        </div>
    </div>

    <script>
        window.onload = function() {
            // Auto print jika parameter print=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        }

        // Print function (alternative, window.print() is usually sufficient)
        // function printReport() {
        //     const printWindow = window.open('', '_blank');
        //     printWindow.document.write(document.documentElement.outerHTML);
        //     printWindow.document.close(); 
        //     // Ensure CSS is loaded before printing
        //     printWindow.onload = function() {
        //        printWindow.print();
        //        // printWindow.close(); // Optional: close after print dialog
        //     };
        // }
    </script>
</body>

</html>