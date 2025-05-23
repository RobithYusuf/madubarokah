<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('id_user', Auth::id())->with('produk')->get();
        return view('cart.index', compact('cartItems'));
    }

    public function addToCart(Request $request)
    {
        $cart = Cart::updateOrCreate(
            [
                'id_user' => Auth::id(),
                'id_produk' => $request->id_produk
            ],
            ['quantity' => \DB::raw('quantity + 1')]
        );

        return response()->json(['success' => 'Produk berhasil ditambahkan ke keranjang!']);
    }

    public function removeFromCart($id)
    {
        Cart::where('id', $id)->where('id_user', Auth::id())->delete();
        return redirect()->back()->with('success', 'Produk dihapus dari keranjang');
    }
}
