<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Tripay Callback Routes - tidak memerlukan autentikasi
Route::post('/tripay/callback', [\App\Http\Controllers\Api\TripayCallbackController::class, 'callback']);
Route::get('/tripay/return', [\App\Http\Controllers\Api\TripayCallbackController::class, 'return']);
