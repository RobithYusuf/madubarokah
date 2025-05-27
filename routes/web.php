<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LandingpageController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================================================
// PUBLIC ROUTES
// ============================================================================
Route::get('/', [LandingpageController::class, 'index'])->name('frontend.home');

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ============================================================================
// ADMIN ROUTES
// ============================================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getChartData'])->name('dashboard.chart-data');

    // Kategori Management
    Route::prefix('kategori')->name('kategori.')->group(function () {
        Route::get('/', [KategoriController::class, 'index'])->name('index');
        Route::post('/tambah', [KategoriController::class, 'store'])->name('store');
        Route::put('/edit/{id}', [KategoriController::class, 'update'])->name('update');
        Route::delete('/hapus/{id}', [KategoriController::class, 'destroy'])->name('destroy');
    });

    // Produk Management
    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [ProdukController::class, 'index'])->name('index');
        Route::post('/tambah', [ProdukController::class, 'store'])->name('store');
        Route::put('/edit/{id}', [ProdukController::class, 'update'])->name('update');
        Route::delete('/hapus/{id}', [ProdukController::class, 'destroy'])->name('destroy');
    });

    // Pesanan Management
    Route::prefix('pesanan')->name('pesanan.')->group(function () {
        Route::get('/', [PesananController::class, 'index'])->name('index');
        Route::get('/create-test', [PesananController::class, 'createTestData'])->name('create-test');
        Route::get('/{id}', [PesananController::class, 'show'])->name('show');
        Route::put('/{id}/status', [PesananController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{id}', [PesananController::class, 'destroy'])->name('destroy');
    });

    // User Management
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/tambah', [UserController::class, 'store'])->name('store');
        Route::put('/edit/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/hapus/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Shipping Management (Admin)
    Route::prefix('shipping')->name('shipping.')->group(function () {
        Route::get('/', [ShippingController::class, 'index'])->name('index');
        Route::post('/sync', [ShippingController::class, 'syncAreas'])->name('sync');
        Route::get('/cities/{province}', [ShippingController::class, 'getCitiesByProvince'])->name('cities');
        Route::post('/calculate', [ShippingController::class, 'calculateCost'])->name('calculate');
        Route::post('/multiple-costs', [ShippingController::class, 'getMultipleCosts'])->name('multiple-costs');
        Route::post('/courier/{id}/status', [ShippingController::class, 'updateCourierStatus'])->name('courier.status');
        Route::delete('/courier/{id}', [ShippingController::class, 'destroyCourier'])->name('courier.destroy');
        Route::get('/provinces', [ShippingController::class, 'getProvinces'])->name('provinces');
    });

    // Payment Management (Admin)
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('index');
        Route::post('/sync', [\App\Http\Controllers\Admin\PaymentController::class, 'syncChannels'])->name('sync');
        Route::post('/channel/{id}/status', [\App\Http\Controllers\Admin\PaymentController::class, 'updateStatus'])->name('channel.status');
        Route::post('/channel/{id}/fee', [\App\Http\Controllers\Admin\PaymentController::class, 'updateFee'])->name('channel.fee');
        Route::delete('/channel/{id}', [\App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('channel.destroy');

        // Legacy route - keep for backward compatibility
        Route::post('/tripay/sync-channels', [CartController::class, 'syncTripayChannels'])->name('tripay.sync-channels');
    });

    // Di dalam admin middleware group
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/shop', [\App\Http\Controllers\Admin\ShopSettingsController::class, 'index'])->name('shop');
        Route::post('/shop', [\App\Http\Controllers\Admin\ShopSettingsController::class, 'update'])->name('shop.update');
    });
});



// ============================================================================
// FRONTEND ROUTES (PEMBELI)
// ============================================================================
Route::middleware(['auth', 'role:pembeli'])->name('frontend.')->group(function () {
    // Cart Management
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'addToCart'])->name('add');
        Route::post('/update', [CartController::class, 'updateCart'])->name('update');
        Route::put('/update/{id}', [CartController::class, 'updateCartItem'])->name('update.item');
        Route::delete('/remove/{id}', [CartController::class, 'removeFromCart'])->name('remove');
        Route::delete('/clear', [CartController::class, 'clearCart'])->name('clear');
        Route::post('/sync', [CartController::class, 'syncCartToDatabase'])->name('sync');
        Route::get('/count', [CartController::class, 'getCartCount'])->name('count');
    });

    // Checkout Process
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CartController::class, 'checkout'])->name('index');
        Route::post('/process', [CartController::class, 'processCheckout'])->name('process');
        Route::get('/confirmation/{transaksi}', [CartController::class, 'confirmation'])->name('confirmation');
    });

    // Transaction History
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/', [CartController::class, 'history'])->name('index');
        Route::get('/{transaksi}', [CartController::class, 'historyDetail'])->name('detail');
    });

    // Shipping calculation for frontend
    Route::post('/shipping/calculate', [ShippingController::class, 'calculateCost'])->name('shipping.calculate');
});

// ============================================================================
// API ROUTES
// ============================================================================
Route::middleware(['auth', 'role:pembeli'])->prefix('api')->name('api.')->group(function () {
    // Checkout API
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/data', [CartController::class, 'getCheckoutData'])->name('data');
        Route::get('/cities/{provinceId}', [CartController::class, 'getCitiesByProvince'])->name('cities');
        // IMPORTANT: Use ShippingController instead of CartController for consistency with admin
        Route::post('/calculate', [ShippingController::class, 'calculateCost'])->name('calculate');
    });
});

// ============================================================================
// SHARED SHIPPING ROUTES (untuk admin dan pembeli)
// ============================================================================
Route::middleware('auth')->prefix('shipping')->name('shipping.')->group(function () {
    Route::get('/cities/{province}', [ShippingController::class, 'getCitiesByProvince'])->name('cities');
    Route::get('/provinces', [ShippingController::class, 'getProvinces'])->name('provinces');
    // This route can be used by both admin and pembeli
    Route::post('/calculate', [ShippingController::class, 'calculateCost'])->name('calculate');
});

// ============================================================================
// BACKWARD COMPATIBILITY ROUTES
// ============================================================================
Route::middleware(['auth', 'role:pembeli'])->name('frontend.')->group(function () {
    // Direct checkout routes for backward compatibility
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/process', [CartController::class, 'processCheckout'])->name('checkout.process');

    // Direct cart routes for backward compatibility  
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
});
