<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TripayCallbackController extends Controller
{
    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        $this->tripayService = $tripayService;
    }

    /**
     * Handle callback from Tripay
     */
    public function callback(Request $request)
    {
        // Log raw request untuk debug
        Log::info('Tripay Callback Raw Request', [
            'headers' => $request->headers->all(),
            'body' => $request->getContent(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);

        // Log incoming callback
        Log::info('Tripay Callback Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            // Get callback data
            $callbackData = $request->all();

            // Verify callback signature
            $callbackSignature = $request->header('X-Callback-Signature');
            $json = $request->getContent();
            
            if (!$callbackSignature) {
                Log::error('Tripay Callback: Signature header tidak ditemukan');
                return response()->json(['success' => false, 'message' => 'Signature header tidak ditemukan'], 400);
            }
            
            // Verifikasi signature menggunakan private key
            $privateKey = config('tripay.private_key');
            $calculatedSignature = hash_hmac('sha256', $json, $privateKey);
            
            Log::info('Tripay Signature Verification', [
                'received' => $callbackSignature,
                'calculated' => $calculatedSignature,
                'is_valid' => hash_equals($calculatedSignature, $callbackSignature)
            ]);
            
            if (!hash_equals($calculatedSignature, $callbackSignature)) {
                Log::error('Tripay Callback: Signature tidak valid', [
                    'received' => $callbackSignature,
                    'calculated' => $calculatedSignature
                ]);
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid signature'
                ], 400);
            }

            // Get transaction reference
            $reference = $callbackData['reference'] ?? null;
            $status = $callbackData['status'] ?? null;

            if (!$reference || !$status) {
                Log::error('Tripay Callback: Missing critical fields', [
                    'reference' => $reference,
                    'status' => $status
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Missing critical fields'
                ], 400);
            }

            // Get merchantRef either from callback or from database
            $merchantRef = $callbackData['merchant_ref'] ?? null;
            
            // Jika merchant_ref null, coba cari transaksi berdasarkan reference saja
            if (!$merchantRef) {
                Log::info('Tripay Callback: merchant_ref is null, searching by reference only', [
                    'reference' => $reference
                ]);
                
                // Cari pembayaran berdasarkan reference
                $pembayaran = Pembayaran::where('reference', $reference)->first();
                
                if ($pembayaran) {
                    // Jika pembayaran ditemukan, ambil transaksi
                    $transaksi = Transaksi::find($pembayaran->id_transaksi);
                    
                    if ($transaksi) {
                        // Update status pembayaran
                        $newStatus = $this->mapTripayStatus($status);
                        $pembayaran->update([
                            'status' => $newStatus,
                            'waktu_bayar' => $status === 'PAID' ? now() : null
                        ]);
                        
                        // Update status transaksi - gunakan getTransactionStatus yang sudah disesuaikan
                        $transactionStatus = $this->getTransactionStatus($status);
                        $transaksi->update([
                            'status' => $transactionStatus
                        ]);
                        
                        // Update shipping status if payment is successful
                        if ($status === 'PAID' && $transaksi->pengiriman) {
                        $transaksi->pengiriman->update([
                        'status' => Pengiriman::STATUS_DIPROSES
                        ]);
                        }
                        
                        Log::info('Tripay Callback: Transaction updated by reference', [
                            'reference' => $reference,
                            'new_status' => $newStatus
                        ]);
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'Transaction updated by reference'
                        ]);
                    }
                }
                
                Log::error('Tripay Callback: Transaction not found by reference', [
                    'reference' => $reference
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found by reference'
                ], 404);
            }

            // Find transaction by merchant_ref and reference
            $transaksi = Transaksi::where(function($query) use ($merchantRef, $reference) {
                $query->where('merchant_ref', $merchantRef)
                    ->where('tripay_reference', $reference);
            })->orWhere('merchant_ref', $reference)->first();

            if (!$transaksi) {
                // Coba cari transaksi berdasarkan referensi saja di tabel pembayaran
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
                
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Process callback based on status
            DB::beginTransaction();
            try {
                $this->processCallbackStatus($transaksi, $callbackData);
                DB::commit();

                Log::info('Tripay Callback: Successfully processed', [
                    'merchant_ref' => $merchantRef,
                    'reference' => $reference,
                    'status' => $status
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Callback processed successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Tripay Callback: Processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed'
            ], 500);
        }
    }

    /**
     * Process callback status update
     */
    private function processCallbackStatus($transaksi, $callbackData)
    {
        $status = $callbackData['status'];
        $reference = $callbackData['reference'];

        // Update pembayaran record
        $pembayaran = Pembayaran::where('id_transaksi', $transaksi->id)
            ->where('reference', $reference)
            ->first();

        if (!$pembayaran) {
            // Create if doesn't exist
            $pembayaran = Pembayaran::create([
                'id_transaksi' => $transaksi->id,
                'reference' => $reference,
                'metode' => $transaksi->metode_pembayaran,
                'total_bayar' => $transaksi->total_harga,
                'status' => $this->mapTripayStatus($status),
                'payment_type' => 'tripay',
                'callback_data' => json_encode($callbackData)
            ]);
        } else {
            // Update existing
            $pembayaran->update([
                'status' => $this->mapTripayStatus($status),
                'callback_data' => json_encode($callbackData),
                'waktu_bayar' => $status === 'PAID' ? now() : null
            ]);
        }

        // Update transaction status
        $newTransactionStatus = $this->getTransactionStatus($status);
        if ($newTransactionStatus !== $transaksi->status) {
            Log::info('Transaction status update', [
                'transaksi_id' => $transaksi->id,
                'old_status' => $transaksi->status,
                'new_status' => $newTransactionStatus,
                'tripay_status' => $status,
                'valid_statuses' => ['pending', 'dibayar', 'dikirim', 'selesai', 'batal']
            ]);
            
            $transaksi->update([
                'status' => $newTransactionStatus
            ]);

            // Update shipping status if payment is successful
            if ($status === 'PAID' && $transaksi->pengiriman) {
                $transaksi->pengiriman->update([
                    'status' => Pengiriman::STATUS_DIPROSES
                ]);
            }
        }

        // Log status change
        Log::info('Transaction status updated', [
            'merchant_ref' => $transaksi->merchant_ref,
            'tripay_reference' => $reference,
            'old_status' => $transaksi->status,
            'new_status' => $newTransactionStatus,
            'tripay_status' => $status
        ]);
    }

    /**
     * Map Tripay status to our system status
     */
    private function mapTripayStatus($tripayStatus)
    {
        $statusMap = [
            'UNPAID' => Pembayaran::STATUS_PENDING,
            'PAID' => Pembayaran::STATUS_BERHASIL,
            'FAILED' => Pembayaran::STATUS_GAGAL,
            'EXPIRED' => Pembayaran::STATUS_EXPIRED,
            'REFUND' => Pembayaran::STATUS_REFUND
        ];

        return $statusMap[$tripayStatus] ?? Pembayaran::STATUS_PENDING;
    }

    /**
     * Get transaction status based on payment status
     */
    private function getTransactionStatus($tripayStatus)
    {
        $statusMap = [
            'UNPAID' => Transaksi::STATUS_PENDING,
            'PAID' => Transaksi::STATUS_DIBAYAR,    // Transaksi menggunakan 'dibayar', bukan 'berhasil'
            'FAILED' => Transaksi::STATUS_BATAL,     // Transaksi menggunakan 'batal', bukan 'gagal'
            'EXPIRED' => Transaksi::STATUS_BATAL,    // Transaksi menggunakan 'batal', bukan 'expired'
            'REFUND' => Transaksi::STATUS_BATAL      // Transaksi menggunakan 'batal', bukan 'refund'
        ];

        return $statusMap[$tripayStatus] ?? Transaksi::STATUS_PENDING;
    }

    /**
     * Handle return URL from payment page
     */
    public function return(Request $request)
    {
        $reference = $request->get('reference');
        $merchantRef = $request->get('merchant_ref');

        Log::info('Tripay Return URL accessed', [
            'reference' => $reference,
            'merchant_ref' => $merchantRef,
            'query_params' => $request->all()
        ]);

        if ($merchantRef) {
            $transaksi = Transaksi::where('merchant_ref', $merchantRef)->first();
            
            if ($transaksi) {
                // Check current payment status from Tripay API
                if ($reference) {
                    $tripayTransaction = $this->tripayService->getTransaction($reference);
                    
                    if ($tripayTransaction) {
                        // Update local status based on API response
                        $this->processCallbackStatus($transaksi, [
                            'reference' => $reference,
                            'merchant_ref' => $merchantRef,
                            'status' => $tripayTransaction['status'] ?? 'UNPAID'
                        ]);
                    }
                }

                return redirect()->route('frontend.confirmation.show', $transaksi->id)
                    ->with('info', 'Silakan tunggu konfirmasi pembayaran atau refresh halaman ini.');
            }
        }

        return redirect()->route('frontend.home')
            ->with('error', 'Transaksi tidak ditemukan.');
    }
}
