<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('id_user', Auth::id())->with('produk')->get();
        $total = $cartItems->sum(function($item) {
            return $item->quantity * $item->produk->harga;
        });
        
        return view('cart.index', compact('cartItems', 'total'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'id_produk' => 'required|exists:produk,id',
            'quantity' => 'integer|min:1'
        ]);

        $quantity = $request->quantity ?? 1;

        $cart = Cart::where('id_user', Auth::id())
                   ->where('id_produk', $request->id_produk)
                   ->first();

        if ($cart) {
            $cart->update(['quantity' => $cart->quantity + $quantity]);
        } else {
            Cart::create([
                'id_user' => Auth::id(),
                'id_produk' => $request->id_produk,
                'quantity' => $quantity
            ]);
        }

        return response()->json(['success' => 'Produk berhasil ditambahkan ke keranjang!']);
    }

    public function updateQuantity(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('id', $id)->where('id_user', Auth::id())->first();
        
        if ($cart) {
            $cart->update(['quantity' => $request->quantity]);
            return redirect()->back()->with('success', 'Jumlah produk berhasil diperbarui');
        }

        return redirect()->back()->with('error', 'Produk tidak ditemukan');
    }

    public function removeFromCart($id)
    {
        Cart::where('id', $id)->where('id_user', Auth::id())->delete();
        return redirect()->back()->with('success', 'Produk dihapus dari keranjang');
    }

    public function checkout()
    {
        $cartItems = Cart::where('id_user', Auth::id())->with('produk')->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('frontend.cart.index')->with('error', 'Keranjang kosong');
        }

        $total = $cartItems->sum(function($item) {
            return $item->quantity * $item->produk->harga;
        });

        return view('cart.checkout', compact('cartItems', 'total'));
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'metode_pembayaran' => 'required|in:COD,Transfer Bank,E-Wallet'
        ]);

        $cartItems = Cart::where('id_user', Auth::id())->with('produk')->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('frontend.cart.index')->with('error', 'Keranjang kosong');
        }

        DB::beginTransaction();
        try {
            // Hitung total
            $total = $cartItems->sum(function($item) {
                return $item->quantity * $item->produk->harga;
            });

            // Buat transaksi
            $transaksi = Transaksi::create([
                'id_user' => Auth::id(),
                'tanggal_transaksi' => now(),
                'total_harga' => $total,
                'status' => 'pending',
                'metode_pembayaran' => $request->metode_pembayaran
            ]);

            // Buat detail transaksi untuk setiap item di cart
            foreach ($cartItems as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->quantity,
                    'harga_satuan' => $item->produk->harga,
                    'subtotal' => $item->quantity * $item->produk->harga
                ]);

                // Kurangi stok produk
                $item->produk->decrement('stok', $item->quantity);
            }

            // Hapus items dari cart setelah checkout berhasil
            Cart::where('id_user', Auth::id())->delete();

            DB::commit();

            return redirect()->route('frontend.cart.index')
                           ->with('success', 'Pesanan berhasil dibuat! ID Transaksi: #' . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT));
        
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pesanan');
        }
    }

    public function clearCart()
    {
        Cart::where('id_user', Auth::id())->delete();
        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }
}
