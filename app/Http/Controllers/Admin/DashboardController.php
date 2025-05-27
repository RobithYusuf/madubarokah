<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Total counts
        $totalKategori = Kategori::count();
        $totalProduk = Produk::count();
        $totalPesanan = Transaksi::count();
        $totalPengguna = User::where('role', 'pembeli')->count();

        // Chart data - Penjualan bulanan (12 bulan terakhir)
        $salesData = $this->getMonthlySalesData();
        
        // Pie chart data - Distribusi kategori produk
        $categoryData = $this->getCategoryDistribution();
        
        // Pesanan terbaru (5 terakhir)
        $recentOrders = $this->getRecentOrders();

        // Revenue data
        $totalRevenue = Transaksi::where('status', 'selesai')->sum('total_harga');
        $monthlyRevenue = Transaksi::where('status', 'selesai')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_harga');

        return view('admin.dashboard.index', compact(
            'totalKategori',
            'totalProduk', 
            'totalPesanan',
            'totalPengguna',
            'salesData',
            'categoryData',
            'recentOrders',
            'totalRevenue',
            'monthlyRevenue'
        ));
    }

    private function getMonthlySalesData()
    {
        $months = [];
        $salesData = [];
        
        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            
            $monthlySales = Transaksi::where('status', 'selesai')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_harga');
                
            $salesData[] = (float) $monthlySales;
        }
        
        return [
            'labels' => $months,
            'data' => $salesData
        ];
    }

    private function getCategoryDistribution()
    {
        $categories = Kategori::withCount('produk')->get();
        
        $labels = [];
        $data = [];
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'];
        
        foreach ($categories as $index => $category) {
            $labels[] = $category->nama_kategori;
            $data[] = $category->produk_count;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels))
        ];
    }

    private function getRecentOrders()
    {
        return Transaksi::with(['user', 'detailTransaksi.produk'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaksi) {
                return [
                    'id' => $transaksi->id,
                    'invoice' => 'INV-' . str_pad($transaksi->id, 3, '0', STR_PAD_LEFT),
                    'customer' => $transaksi->user->nama,
                    'total' => $transaksi->total_harga,
                    'status' => $transaksi->status,
                    'created_at' => $transaksi->created_at,
                    'status_badge' => $this->getStatusBadge($transaksi->status)
                ];
            });
    }

    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => 'badge-warning',
            'dibayar' => 'badge-info', 
            'dikirim' => 'badge-primary',
            'selesai' => 'badge-success',
            'batal' => 'badge-danger'
        ];
        
        return $badges[$status] ?? 'badge-secondary';
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'monthly');
        
        if ($type === 'yearly') {
            return response()->json($this->getYearlySalesData());
        }
        
        return response()->json($this->getMonthlySalesData());
    }

    private function getYearlySalesData()
    {
        $currentYear = Carbon::now()->year;
        $years = [];
        $salesData = [];
        
        // Get last 5 years
        for ($i = 4; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $years[] = $year;
            
            $yearlySales = Transaksi::where('status', 'selesai')
                ->whereYear('created_at', $year)
                ->sum('total_harga');
                
            $salesData[] = (float) $yearlySales;
        }
        
        return [
            'labels' => $years,
            'data' => $salesData
        ];
    }
}