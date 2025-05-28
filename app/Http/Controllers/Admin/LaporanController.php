<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Support\Collection;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Default periode (30 hari terakhir)
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $paymentStatus = $request->get('payment_status', 'all');
        $shippingStatus = $request->get('shipping_status', 'all');

        // Debug log
        \Log::info('Laporan Index Request', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'shipping_status' => $shippingStatus
        ]);

        // Query base untuk transaksi
        $query = Transaksi::with(['user', 'pembayaran', 'pengiriman', 'detailTransaksi.produk.kategori'])
            ->whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Filter berdasarkan status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($paymentStatus !== 'all') {
            $query->whereHas('pembayaran', function($q) use ($paymentStatus) {
                $q->where('status', $paymentStatus);
            });
        }

        if ($shippingStatus !== 'all') {
            $query->whereHas('pengiriman', function($q) use ($shippingStatus) {
                $q->where('status', $shippingStatus);
            });
        }

        // Ambil data transaksi dengan pagination
        $transaksi = $query->orderBy('tanggal_transaksi', 'desc')->paginate(20);

        // Summary data
        $summaryData = $this->getSummaryData($startDate, $endDate, $status, $paymentStatus, $shippingStatus);

        // Chart data
        $chartData = $this->getChartData($startDate, $endDate);

        // Debug log untuk data
        \Log::info('Laporan Data Generated', [
            'transaksi_count' => $transaksi->count(),
            'summary_total' => $summaryData['total_transaksi'],
            'chart_sales_count' => count($chartData['daily_sales']),
            'status_distribution_count' => count($summaryData['status_distribution'])
        ]);

        // Pastikan ada data default jika kosong
        if (empty($chartData['daily_sales'])) {
            $chartData['daily_sales'] = [];
        }

        if (empty($summaryData['status_distribution'])) {
            $summaryData['status_distribution'] = [];
        }

        return view('admin.laporan.index', compact(
            'transaksi', 
            'summaryData', 
            'chartData',
            'startDate', 
            'endDate', 
            'status', 
            'paymentStatus', 
            'shippingStatus'
        ));
    }

    public function transaksi(Request $request)
    {
        return $this->index($request);
    }

    public function penjualan(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        // Data penjualan harian
        $penjualanHarian = DB::table('transaksi')
            ->select(
                DB::raw('DATE(tanggal_transaksi) as tanggal'),
                DB::raw('COUNT(*) as jumlah_transaksi'),
                DB::raw('SUM(total_harga) as total_penjualan'),
                DB::raw('AVG(total_harga) as rata_rata')
            )
            ->whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('status', ['dibayar', 'berhasil', 'dikirim', 'selesai'])
            ->groupBy(DB::raw('DATE(tanggal_transaksi)'))
            ->orderBy('tanggal', 'desc')
            ->get();

        // Top produk
        $topProduk = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id')
            ->join('kategori', 'produk.id_kategori', '=', 'kategori.id')
            ->select(
                'produk.nama_produk',
                'kategori.nama_kategori',
                'kategori.warna',
                DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'),
                DB::raw('SUM(detail_transaksi.subtotal) as total_pendapatan')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('transaksi.status', ['dibayar', 'berhasil', 'dikirim', 'selesai'])
            ->groupBy('produk.id', 'produk.nama_produk', 'kategori.nama_kategori', 'kategori.warna')
            ->orderBy('total_terjual', 'desc')
            ->limit(10)
            ->get();

        // Top kategori
        $topKategori = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id')
            ->join('kategori', 'produk.id_kategori', '=', 'kategori.id')
            ->select(
                'kategori.nama_kategori',
                'kategori.warna',
                DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'),
                DB::raw('SUM(detail_transaksi.subtotal) as total_pendapatan'),
                DB::raw('COUNT(DISTINCT produk.id) as jumlah_produk')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('transaksi.status', ['dibayar', 'berhasil', 'dikirim', 'selesai'])
            ->groupBy('kategori.id', 'kategori.nama_kategori', 'kategori.warna')
            ->orderBy('total_pendapatan', 'desc')
            ->get();

        return view('admin.laporan.penjualan', compact(
            'penjualanHarian', 
            'topProduk', 
            'topKategori',
            'startDate', 
            'endDate'
        ));
    }

    public function produk(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $kategoriId = $request->get('kategori_id', 'all');

        $query = DB::table('produk')
            ->leftJoin('kategori', 'produk.id_kategori', '=', 'kategori.id')
            ->leftJoin('detail_transaksi', 'produk.id', '=', 'detail_transaksi.id_produk')
            ->leftJoin('transaksi', function($join) use ($startDate, $endDate) {
                $join->on('detail_transaksi.id_transaksi', '=', 'transaksi.id')
                     ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                     ->whereIn('transaksi.status', ['dibayar', 'berhasil', 'dikirim', 'selesai']);
            })
            ->select(
                'produk.id',
                'produk.nama_produk',
                'produk.harga',
                'produk.stok',
                'kategori.nama_kategori',
                'kategori.warna',
                DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'),
                DB::raw('COALESCE(SUM(detail_transaksi.subtotal), 0) as total_pendapatan'),
                DB::raw('COUNT(DISTINCT transaksi.id) as jumlah_transaksi')
            )
            ->groupBy('produk.id', 'produk.nama_produk', 'produk.harga', 'produk.stok', 'kategori.nama_kategori', 'kategori.warna');

        if ($kategoriId !== 'all') {
            $query->where('produk.id_kategori', $kategoriId);
        }

        $produkData = $query->orderBy('total_terjual', 'desc')->paginate(20);

        $kategoris = Kategori::all();

        return view('admin.laporan.produk', compact(
            'produkData', 
            'kategoris',
            'startDate', 
            'endDate', 
            'kategoriId'
        ));
    }

    public function pelanggan(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $pelangganData = DB::table('users')
            ->leftJoin('transaksi', function($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'transaksi.id_user')
                     ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                     ->whereIn('transaksi.status', ['dibayar', 'berhasil', 'dikirim', 'selesai']);
            })
            ->where('users.role', 'pembeli')
            ->select(
                'users.id',
                'users.nama',
                'users.username',
                'users.email',
                'users.nohp',
                DB::raw('COUNT(transaksi.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(transaksi.total_harga), 0) as total_belanja'),
                DB::raw('COALESCE(AVG(transaksi.total_harga), 0) as rata_rata_belanja'),
                DB::raw('MAX(transaksi.tanggal_transaksi) as transaksi_terakhir')
            )
            ->groupBy('users.id', 'users.nama', 'users.username', 'users.email', 'users.nohp')
            ->orderBy('total_belanja', 'desc')
            ->paginate(20);

        return view('admin.laporan.pelanggan', compact(
            'pelangganData',
            'startDate', 
            'endDate'
        ));
    }

    public function pengiriman(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $pengirimanData = DB::table('pengiriman')
            ->join('transaksi', 'pengiriman.id_transaksi', '=', 'transaksi.id')
            ->join('users', 'transaksi.id_user', '=', 'users.id')
            ->select(
                'pengiriman.kurir',
                'pengiriman.layanan',
                DB::raw('COUNT(*) as jumlah_penggunaan'),
                DB::raw('SUM(pengiriman.biaya) as total_biaya'),
                DB::raw('AVG(pengiriman.biaya) as rata_rata_biaya'),
                DB::raw('SUM(pengiriman.weight) as total_berat')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('pengiriman.kurir', 'pengiriman.layanan')
            ->orderBy('jumlah_penggunaan', 'desc')
            ->get();

        $statusPengiriman = DB::table('pengiriman')
            ->join('transaksi', 'pengiriman.id_transaksi', '=', 'transaksi.id')
            ->select(
                'pengiriman.status',
                DB::raw('COUNT(*) as jumlah')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('pengiriman.status')
            ->get();

        return view('admin.laporan.pengiriman', compact(
            'pengirimanData',
            'statusPengiriman',
            'startDate', 
            'endDate'
        ));
    }

    private function getSummaryData($startDate, $endDate, $status, $paymentStatus, $shippingStatus)
    {
        try {
            $query = Transaksi::whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            if ($paymentStatus !== 'all') {
                $query->whereHas('pembayaran', function($q) use ($paymentStatus) {
                    $q->where('status', $paymentStatus);
                });
            }

            if ($shippingStatus !== 'all') {
                $query->whereHas('pengiriman', function($q) use ($shippingStatus) {
                    $q->where('status', $shippingStatus);
                });
            }

            $summary = $query->select(
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('COALESCE(SUM(total_harga), 0) as total_pendapatan'),
                DB::raw('COALESCE(AVG(total_harga), 0) as rata_rata_transaksi')
            )->first();

            // Transaksi berhasil
            $transaksiSelesai = Transaksi::whereIn('status', ['dibayar', 'berhasil', 'dikirim', 'selesai'])
                ->whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->count();

            // Status distribution
            $statusDistribution = Transaksi::select('status', DB::raw('COUNT(*) as count'))
                ->whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy('status')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status,
                        'count' => (int) $item->count
                    ];
                });

            return [
                'total_transaksi' => (int) ($summary->total_transaksi ?? 0),
                'total_pendapatan' => (float) ($summary->total_pendapatan ?? 0),
                'rata_rata_transaksi' => (float) ($summary->rata_rata_transaksi ?? 0),
                'transaksi_selesai' => (int) $transaksiSelesai,
                'status_distribution' => $statusDistribution->toArray()
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting summary data: ' . $e->getMessage());
            return [
                'total_transaksi' => 0,
                'total_pendapatan' => 0,
                'rata_rata_transaksi' => 0,
                'transaksi_selesai' => 0,
                'status_distribution' => []
            ];
        }
    }

    private function getChartData($startDate, $endDate)
    {
        try {
            // Data penjualan harian untuk chart
            $dailySales = DB::table('transaksi')
                ->select(
                    DB::raw('DATE(tanggal_transaksi) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COALESCE(SUM(total_harga), 0) as total')
                )
                ->whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->whereIn('status', ['dibayar', 'berhasil', 'dikirim', 'selesai'])
                ->groupBy(DB::raw('DATE(tanggal_transaksi)'))
                ->orderBy('date')
                ->get()
                ->map(function($item) {
                    return [
                        'date' => $item->date,
                        'count' => (int) $item->count,
                        'total' => (float) $item->total
                    ];
                });

            return [
                'daily_sales' => $dailySales->toArray()
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting chart data: ' . $e->getMessage());
            return [
                'daily_sales' => []
            ];
        }
    }

    public function chartPenjualan(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->getChartData($startDate, $endDate);
        
        return response()->json($data);
    }

    public function chartStatus(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $statusData = Transaksi::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('status')
            ->get();

        return response()->json($statusData);
    }

    public function exportTransaksi(Request $request)
    {
        // TODO: Implement Excel export
        return response()->json(['message' => 'Export feature will be implemented']);
    }

    public function exportPenjualan(Request $request)
    {
        // TODO: Implement Excel export
        return response()->json(['message' => 'Export feature will be implemented']);
    }

    public function pdf(Request $request)
    {
        $type = $request->get('type', 'transaksi');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $paymentStatus = $request->get('payment_status', 'all');
        $shippingStatus = $request->get('shipping_status', 'all');
        $kategoriId = $request->get('kategori_id', 'all');

        $data = [
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'paymentStatus' => $paymentStatus,
            'shippingStatus' => $shippingStatus
        ];

        // Generate data berdasarkan type
        switch ($type) {
            case 'transaksi':
                $data = array_merge($data, $this->getTransaksiPdfData($request));
                $data['title'] = 'Laporan Transaksi';
                break;
                
            case 'penjualan':
                $data = array_merge($data, $this->getPenjualanPdfData($request));
                $data['title'] = 'Laporan Penjualan';
                break;
                
            case 'pelanggan':
                $data = array_merge($data, $this->getPelangganPdfData($request));
                $data['title'] = 'Laporan Pelanggan';
                break;
                
            case 'pengiriman':
                $data = array_merge($data, $this->getPengirimanPdfData($request));
                $data['title'] = 'Laporan Pengiriman';
                break;
                
            case 'produk':
                $data = array_merge($data, $this->getProdukPdfData($request));
                $data['title'] = 'Laporan Produk';
                break;
                
            default:
                $data = array_merge($data, $this->getTransaksiPdfData($request));
                $data['title'] = 'Laporan Transaksi';
                break;
        }

        return view('admin.laporan.pdf', $data);
    }

    private function getTransaksiPdfData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $paymentStatus = $request->get('payment_status', 'all');
        $shippingStatus = $request->get('shipping_status', 'all');

        // Query base untuk transaksi
        $query = Transaksi::with(['user', 'pembayaran', 'pengiriman', 'detailTransaksi.produk.kategori'])
            ->whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Filter berdasarkan status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($paymentStatus !== 'all') {
            $query->whereHas('pembayaran', function($q) use ($paymentStatus) {
                $q->where('status', $paymentStatus);
            });
        }

        if ($shippingStatus !== 'all') {
            $query->whereHas('pengiriman', function($q) use ($shippingStatus) {
                $q->where('status', $shippingStatus);
            });
        }

        // Ambil data transaksi (limit untuk PDF)
        $transaksi = $query->orderBy('tanggal_transaksi', 'desc')->limit(200)->get();

        // Summary data
        $summaryData = $this->getSummaryData($startDate, $endDate, $status, $paymentStatus, $shippingStatus);

        return [
            'transaksi' => $transaksi,
            'summaryData' => $summaryData
        ];
    }

    private function getPenjualanPdfData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        // Data penjualan harian
        $penjualanHarian = DB::table('transaksi')
            ->select(
                DB::raw('DATE(tanggal_transaksi) as tanggal'),
                DB::raw('COUNT(*) as jumlah_transaksi'),
                DB::raw('SUM(total_harga) as total_penjualan'),
                DB::raw('AVG(total_harga) as rata_rata')
            )
            ->whereBetween('tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('status', ['dibayar', 'berhasil', 'dikirim', 'selesai'])
            ->groupBy(DB::raw('DATE(tanggal_transaksi)'))
            ->orderBy('tanggal', 'desc')
            ->get();

        // Top produk
        $topProduk = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id')
            ->join('kategori', 'produk.id_kategori', '=', 'kategori.id')
            ->select(
                'produk.nama_produk',
                'kategori.nama_kategori',
                'kategori.warna',
                DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'),
                DB::raw('SUM(detail_transaksi.subtotal) as total_pendapatan')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('transaksi.status', ['dibayar', 'berhasil', 'dikirim', 'selesai'])
            ->groupBy('produk.id', 'produk.nama_produk', 'kategori.nama_kategori', 'kategori.warna')
            ->orderBy('total_terjual', 'desc')
            ->limit(20)
            ->get();

        // Top kategori
        $topKategori = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id')
            ->join('kategori', 'produk.id_kategori', '=', 'kategori.id')
            ->select(
                'kategori.nama_kategori',
                'kategori.warna',
                DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'),
                DB::raw('SUM(detail_transaksi.subtotal) as total_pendapatan'),
                DB::raw('COUNT(DISTINCT produk.id) as jumlah_produk')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('transaksi.status', ['dibayar', 'berhasil', 'dikirim', 'selesai'])
            ->groupBy('kategori.id', 'kategori.nama_kategori', 'kategori.warna')
            ->orderBy('total_pendapatan', 'desc')
            ->get();

        return [
            'penjualanHarian' => $penjualanHarian,
            'topProduk' => $topProduk,
            'topKategori' => $topKategori
        ];
    }

    private function getPelangganPdfData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $pelangganData = DB::table('users')
            ->leftJoin('transaksi', function($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'transaksi.id_user')
                     ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                     ->whereIn('transaksi.status', ['dibayar', 'berhasil', 'dikirim', 'selesai']);
            })
            ->where('users.role', 'pembeli')
            ->select(
                'users.id',
                'users.nama',
                'users.username',
                'users.email',
                'users.nohp',
                DB::raw('COUNT(transaksi.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(transaksi.total_harga), 0) as total_belanja'),
                DB::raw('COALESCE(AVG(transaksi.total_harga), 0) as rata_rata_belanja'),
                DB::raw('MAX(transaksi.tanggal_transaksi) as transaksi_terakhir')
            )
            ->groupBy('users.id', 'users.nama', 'users.username', 'users.email', 'users.nohp')
            ->orderBy('total_belanja', 'desc')
            ->limit(100)
            ->get();

        return [
            'pelangganData' => $pelangganData
        ];
    }

    private function getPengirimanPdfData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $pengirimanData = DB::table('pengiriman')
            ->join('transaksi', 'pengiriman.id_transaksi', '=', 'transaksi.id')
            ->select(
                'pengiriman.kurir',
                'pengiriman.layanan',
                DB::raw('COUNT(*) as jumlah_penggunaan'),
                DB::raw('SUM(pengiriman.biaya) as total_biaya'),
                DB::raw('AVG(pengiriman.biaya) as rata_rata_biaya'),
                DB::raw('SUM(pengiriman.weight) as total_berat')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('pengiriman.kurir', 'pengiriman.layanan')
            ->orderBy('jumlah_penggunaan', 'desc')
            ->get();

        $statusPengiriman = DB::table('pengiriman')
            ->join('transaksi', 'pengiriman.id_transaksi', '=', 'transaksi.id')
            ->select(
                'pengiriman.status',
                DB::raw('COUNT(*) as jumlah')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('pengiriman.status')
            ->get();

        return [
            'pengirimanData' => $pengirimanData,
            'statusPengiriman' => $statusPengiriman
        ];
    }

    private function getProdukPdfData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $kategoriId = $request->get('kategori_id', 'all');

        $query = DB::table('produk')
            ->leftJoin('kategori', 'produk.id_kategori', '=', 'kategori.id')
            ->leftJoin('detail_transaksi', 'produk.id', '=', 'detail_transaksi.id_produk')
            ->leftJoin('transaksi', function($join) use ($startDate, $endDate) {
                $join->on('detail_transaksi.id_transaksi', '=', 'transaksi.id')
                     ->whereBetween('transaksi.tanggal_transaksi', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                     ->whereIn('transaksi.status', ['dibayar', 'berhasil', 'dikirim', 'selesai']);
            })
            ->select(
                'produk.id',
                'produk.nama_produk',
                'produk.harga',
                'produk.stok',
                'kategori.nama_kategori',
                'kategori.warna',
                DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'),
                DB::raw('COALESCE(SUM(detail_transaksi.subtotal), 0) as total_pendapatan'),
                DB::raw('COUNT(DISTINCT transaksi.id) as jumlah_transaksi')
            )
            ->groupBy('produk.id', 'produk.nama_produk', 'produk.harga', 'produk.stok', 'kategori.nama_kategori', 'kategori.warna');

        if ($kategoriId !== 'all') {
            $query->where('produk.id_kategori', $kategoriId);
        }

        $produkData = $query->orderBy('total_terjual', 'desc')->limit(200)->get();

        return [
            'produkData' => $produkData
        ];
    }
}