<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TripayCallbackController extends Controller
{
    /**
     * Handle callback from Tripay
     */
    public function callback(Request $request)
    {
        // Log EVERYTHING for debugging
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent(),
            'ip' => $request->ip()
        ];
        
        Log::channel('daily')->info('=== TRIPAY CALLBACK RECEIVED ===', $logData);
        
        // Also log to PHP error log for immediate visibility
        error_log('TRIPAY CALLBACK: ' . json_encode($logData));

        try {
            // Get and validate signature
            $callbackSignature = $request->header('X-Callback-Signature');
            if (!$callbackSignature) {
                Log::error('Tripay Callback: Missing signature header');
                return response()->json(['success' => false, 'message' => 'Missing signature'], 400);
            }
            
            // Get raw JSON for signature verification
            $json = $request->getContent();
            $privateKey = config('tripay.private_key');
            $calculatedSignature = hash_hmac('sha256', $json, $privateKey);
            
            // Compare signatures
            if (!hash_equals($calculatedSignature, $callbackSignature)) {
                Log::error('Tripay Callback: Invalid signature', [
                    'received' => $callbackSignature,
                    'calculated' => $calculatedSignature
                ]);
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
            }
            
            // Parse callback data
            $callbackData = json_decode($json, true);
            $reference = $callbackData['reference'] ?? null;
            $merchantRef = $callbackData['merchant_ref'] ?? null;
            $status = $callbackData['status'] ?? null;
            
            Log::info('Tripay Callback Data', [
                'reference' => $reference,
                'merchant_ref' => $merchantRef,
                'status' => $status,
                'payment_method' => $callbackData['payment_method'] ?? null,
                'total_amount' => $callbackData['total_amount'] ?? null
            ]);
            
            // Find transaction
            $transaksi = null;
            
            // Try find by merchant_ref first
            if ($merchantRef) {
                $transaksi = Transaksi::where('merchant_ref', $merchantRef)->first();
            }
            
            // If not found, try by reference in pembayaran table
            if (!$transaksi && $reference) {
                $pembayaran = Pembayaran::where('reference', $reference)->first();
                if ($pembayaran) {
                    $transaksi = Transaksi::find($pembayaran->id_transaksi);
                }
            }
            
            if (!$transaksi) {
                Log::error('Tripay Callback: Transaction not found', [
                    'merchant_ref' => $merchantRef,
                    'reference' => $reference
                ]);
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }
            
            // Process payment update
            DB::beginTransaction();
            try {
                // Update or create payment record
                $pembayaran = Pembayaran::updateOrCreate(
                    ['id_transaksi' => $transaksi->id],
                    [
                        'reference' => $reference,
                        'metode' => $callbackData['payment_method'] ?? $callbackData['payment_method_code'] ?? 'UNKNOWN',
                        'total_bayar' => $callbackData['total_amount'] ?? $transaksi->total_harga,
                        'status' => $this->mapTripayStatus($status),
                        'waktu_bayar' => $status === 'PAID' ? now() : null,
                        'payment_type' => 'tripay',
                        'callback_data' => json_encode($callbackData)
                    ]
                );
                
                // Update transaction status
                if ($status === 'PAID') {
                    $transaksi->update(['status' => 'dibayar']);
                    
                    // Update shipping status
                    if ($transaksi->pengiriman) {
                        $transaksi->pengiriman->update(['status' => 'diproses']);
                    }
                    
                    Log::info('Payment confirmed for transaction', [
                        'merchant_ref' => $transaksi->merchant_ref,
                        'reference' => $reference
                    ]);
                } elseif (in_array($status, ['FAILED', 'EXPIRED'])) {
                    $transaksi->update(['status' => 'batal']);
                    
                    // Return stock
                    foreach ($transaksi->detailTransaksi as $detail) {
                        if ($detail->produk) {
                            $detail->produk->increment('stok', $detail->jumlah);
                        }
                    }
                    
                    Log::info('Payment failed/expired for transaction', [
                        'merchant_ref' => $transaksi->merchant_ref,
                        'status' => $status
                    ]);
                }
                
                DB::commit();
                
                // Return success response
                return response()->json([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Tripay Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Map Tripay status to our system
     */
    private function mapTripayStatus($tripayStatus)
    {
        $map = [
            'UNPAID' => 'pending',
            'PAID' => 'berhasil',
            'FAILED' => 'gagal',
            'EXPIRED' => 'expired',
            'REFUND' => 'refund'
        ];
        
        return $map[$tripayStatus] ?? 'pending';
    }
}