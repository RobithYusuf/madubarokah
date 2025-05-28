<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Pembayaran;
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
        // Log incoming callback
        Log::info('Tripay Callback Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            // Get callback data
            $callbackData = $request->all();

            // Verify callback signature
            if (!$this->tripayService->verifyCallbackSignature($callbackData)) {
                Log::error('Tripay Callback: Invalid signature', [
                    'data' => $callbackData
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 400);
            }

            // Get transaction reference
            $reference = $callbackData['reference'] ?? null;
            $merchantRef = $callbackData['merchant_ref'] ?? null;
            $status = $callbackData['status'] ?? null;

            if (!$reference || !$merchantRef || !$status) {
                Log::error('Tripay Callback: Missing required fields', [
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef,
                    'status' => $status
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields'
                ], 400);
            }

            // Find transaction
            $transaksi = Transaksi::where('merchant_ref', $merchantRef)
                ->where('tripay_reference', $reference)
                ->first();

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
                'paid_at' => $status === 'PAID' ? now() : null
            ]);
        }

        // Update transaction status
        $newTransactionStatus = $this->getTransactionStatus($status);
        if ($newTransactionStatus !== $transaksi->status) {
            $transaksi->update([
                'status' => $newTransactionStatus
            ]);

            // Update shipping status if payment is successful
            if ($status === 'PAID' && $transaksi->pengiriman) {
                $transaksi->pengiriman->update([
                    'status' => 'diproses'
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
            'UNPAID' => 'pending',
            'PAID' => 'berhasil',
            'FAILED' => 'gagal',
            'EXPIRED' => 'expired',
            'REFUND' => 'refund'
        ];

        return $statusMap[$tripayStatus] ?? 'pending';
    }

    /**
     * Get transaction status based on payment status
     */
    private function getTransactionStatus($tripayStatus)
    {
        $statusMap = [
            'UNPAID' => 'pending',
            'PAID' => 'berhasil',
            'FAILED' => 'gagal',
            'EXPIRED' => 'expired',
            'REFUND' => 'refund'
        ];

        return $statusMap[$tripayStatus] ?? 'pending';
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
