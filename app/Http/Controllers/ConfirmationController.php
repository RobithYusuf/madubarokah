<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\PaymentChannel;
use App\Models\Pembayaran;
use App\Services\TripayService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConfirmationController extends Controller
{
    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        $this->tripayService = $tripayService;
    }

    public function show(Request $request, $transaksiId)
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk.kategori', 'pengiriman', 'pembayaran'])
            ->where('id', $transaksiId)
            ->where('id_user', Auth::id())
            ->firstOrFail();

        // Get payment channel information
        $paymentChannel = null;
        if ($transaksi->pembayaran) {
            $paymentChannel = PaymentChannel::where('code', $transaksi->pembayaran->metode)
                ->where('is_active', true)
                ->first();
        }

        // Check if this is a Tripay transaction and get latest status
        $statusUpdated = false;
        $checkStatus = $request->has('check_status');
        
        if ($transaksi->tripay_reference && $transaksi->pembayaran && $transaksi->pembayaran->payment_type === 'tripay') {
            try {
                $tripayTransaction = $this->tripayService->getTransaction($transaksi->tripay_reference);
                
                if ($tripayTransaction) {
                    // Update local status if different
                    $currentStatus = $this->mapTripayStatus($tripayTransaction['status']);
                    
                    if ($currentStatus !== $transaksi->pembayaran->status) {
                        $statusUpdated = true;
                        
                        $transaksi->pembayaran->update([
                            'status' => $currentStatus,
                            'waktu_bayar' => $tripayTransaction['status'] === 'PAID' ? now() : null
                        ]);
                        
                        // Update transaction status according to payment status
                        $transactionStatus = $this->getTransactionStatus($tripayTransaction['status']);
                        $transaksi->update([
                            'status' => $transactionStatus
                        ]);
                        
                        // Refresh the model
                        $transaksi->refresh();
                        
                        Log::info('Transaction status updated via check_status', [
                            'tripay_reference' => $transaksi->tripay_reference,
                            'old_status' => $transaksi->pembayaran->status,
                            'new_status' => $currentStatus,
                            'tripay_status' => $tripayTransaction['status']
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to get latest transaction status from Tripay', [
                    'tripay_reference' => $transaksi->tripay_reference,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $whatsappNumber = config('shop.whatsapp', '628123456789');
        $whatsappMessage = "Halo, saya ingin konfirmasi pembayaran untuk pesanan {$transaksi->merchant_ref} dengan total Rp " . number_format($transaksi->total_harga, 0, ',', '.');
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($whatsappMessage);

        // Determine payment type based on payment channel
        $isTripayPayment = false;
        $isManualPayment = true;
        
        if ($paymentChannel && $paymentChannel->is_synced) {
            // This is a Tripay synced channel
            $isTripayPayment = true;
            $isManualPayment = false;
        } elseif ($transaksi->pembayaran && $transaksi->pembayaran->payment_type === 'tripay') {
            // Fallback: check payment record
            $isTripayPayment = true;
            $isManualPayment = false;
        }
        
        // Get payment instructions from payment channel
        $paymentInstructions = [];
        if ($paymentChannel && $paymentChannel->instructions) {
            $rawInstructions = is_array($paymentChannel->instructions) 
                ? $paymentChannel->instructions 
                : json_decode($paymentChannel->instructions, true) ?? [];
            
            // Process placeholders in instructions
            $paymentInstructions = $this->processInstructionPlaceholders($rawInstructions, $transaksi);
        }
        
        \Log::info('Payment type determination', [
            'merchant_ref' => $transaksi->merchant_ref,
            'payment_method' => $transaksi->pembayaran->metode ?? 'unknown',
            'payment_channel_synced' => $paymentChannel ? $paymentChannel->is_synced : false,
            'payment_type_db' => $transaksi->pembayaran->payment_type ?? 'unknown',
            'is_tripay_payment' => $isTripayPayment,
            'has_instructions' => !empty($paymentInstructions),
            'instructions_count' => count($paymentInstructions),
            'instructions_preview' => !empty($paymentInstructions) ? array_slice($paymentInstructions, 0, 1) : []
        ]);

        // Get order info from session if available
        $orderInfo = session('order_info', []);
        
        // If this is an AJAX request for check_status, return minimal response
        if ($request->ajax() && $request->has('check_status')) {
            return response()->json([
                'success' => true,
                'status' => $transaksi->pembayaran ? $transaksi->pembayaran->status : 'pending',
                'transaction_status' => $transaksi->status,
                'updated' => $statusUpdated
            ]);
        }
        
        return view('pembeli.checkout.confirmation', compact(
            'transaksi', 
            'whatsappUrl', 
            'isTripayPayment', 
            'isManualPayment',
            'paymentChannel',
            'paymentInstructions',
            'orderInfo'
        ));
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
     * Process placeholders in payment instructions
     */
    private function processInstructionPlaceholders($instructions, $transaksi)
    {
        if (!is_array($instructions)) {
            return [];
        }

        $paymentCode = $transaksi->pembayaran->payment_code ?? 'XXXXXX';
        $amount = 'Rp ' . number_format($transaksi->total_harga, 0, ',', '.');

        $processedInstructions = [];
        
        foreach ($instructions as $instruction) {
            if (isset($instruction['steps']) && is_array($instruction['steps'])) {
                $processedSteps = [];
                foreach ($instruction['steps'] as $step) {
                    $processedStep = str_replace(
                        ['{{pay_code}}', '{{amount}}'],
                        [$paymentCode, $amount],
                        $step
                    );
                    $processedSteps[] = $processedStep;
                }
                $instruction['steps'] = $processedSteps;
            }
            $processedInstructions[] = $instruction;
        }

        return $processedInstructions;
    }
}
