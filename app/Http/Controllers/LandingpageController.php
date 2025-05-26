<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class LandingpageController extends Controller
{
    public function index()
    {
        $produks = Produk::latest()->take(6)->get(); // Ambil 6 produk terbaru
        return view('frontend.landingpage', compact('produks')); // Kirim ke view landing-page.blade.php
    }
}
