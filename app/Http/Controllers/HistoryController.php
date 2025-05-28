<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk', 'pengiriman', 'pembayaran'])
            ->where('id_user', Auth::id())
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(10);

        return view('pembeli.histori.index', compact('transaksi'));
    }

    public function detail($transaksiId)
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk', 'pengiriman', 'pembayaran'])
            ->where('id', $transaksiId)
            ->where('id_user', Auth::id())
            ->firstOrFail();

        $whatsappNumber = config('shop.whatsapp');
        $whatsappMessage = "Halo, saya ingin menanyakan status pesanan {$transaksi->merchant_ref}";
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($whatsappMessage);

        return view('pembeli.histori.detail', compact('transaksi', 'whatsappUrl'));
    }
}
