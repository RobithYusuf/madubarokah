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

    public function confirmReceipt(Request $request, $transaksiId)
    {
        try {
            $transaksi = Transaksi::with(['pengiriman'])
                ->where('id', $transaksiId)
                ->where('id_user', Auth::id())
                ->firstOrFail();

            // Validasi: hanya bisa konfirmasi jika status transaksi adalah 'dikirim'
            if ($transaksi->status !== 'dikirim') {
                return redirect()->back()->with('error', 'Pesanan hanya dapat dikonfirmasi jika sudah dalam status dikirim.');
            }

            // Update status transaksi menjadi 'selesai'
            $transaksi->update(['status' => 'selesai']);

            // Update status pengiriman menjadi 'diterima'
            if ($transaksi->pengiriman) {
                $transaksi->pengiriman->update(['status' => 'diterima']);
            }

            return redirect()->route('frontend.history.detail', $transaksiId)
                ->with('success', 'Pesanan berhasil dikonfirmasi. Terima kasih telah berbelanja di toko kami!');

        } catch (\Exception $e) {
            \Log::error('Error confirming receipt: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengkonfirmasi pesanan.');
        }
    }
}
