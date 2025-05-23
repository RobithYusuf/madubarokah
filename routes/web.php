<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LandingpageController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landingpage
Route::get('/', [LandingpageController::class, 'index'])->name('Landingpage.index');

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');




// Role Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('layouts.app');
    });
    // Kategori
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori/tambah', [KategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/edit/{id}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/hapus/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
    // Produk
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::post('/produk/tambah', [ProdukController::class, 'store'])->name('produk.store');
    Route::put('/produk/edit/{id}', [ProdukController::class, 'update'])->name('produk.update');
    Route::delete('/produk/hapus/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
});

// Role Pembeli
Route::middleware(['auth', 'role:pembeli'])->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
});