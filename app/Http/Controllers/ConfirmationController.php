<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
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

    public function show($transaksiId)
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk', 'pengiriman', 'pembayaran'])
            ->where('id', $transaksiId)
            ->where('id_user', Auth::id())
            ->firstOrFail();

        // Check if this is a Tripay transaction and get latest status
        if ($transaksi->tripay_reference && $transaksi->pembayaran && $transaksi->pembayaran->payment_type === 'tripay') {
            try {
                $tripayTransaction = $this->tripayService->getTransaction($transaksi->tripay_reference);
                
                if ($tripayTransaction) {
                    // Update local status if different
                    $currentStatus = $this->mapTripayStatus($tripayTransaction['status']);
                    
                    if ($currentStatus !== $transaksi->pembayaran->status) {
                        $transaksi->pembayaran->update([
                            'status' => $currentStatus,
                            'paid_at' => $tripayTransaction['status'] === 'PAID' ? now() : null
                        ]);
                        
                        $transaksi->update([
                            'status' => $currentStatus
                        ]);
                        
                        // Refresh the model
                        $transaksi->refresh();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to get latest transaction status from Tripay', [
                    'tripay_reference' => $transaksi->tripay_reference,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $whatsappNumber = config('shop.whatsapp');
        $whatsappMessage = "Halo, saya ingin konfirmasi pembayaran untuk pesanan {$transaksi->merchant_ref} dengan total Rp " . number_format($transaksi->total_harga, 0, ',', '.');
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($whatsappMessage);

        // Determine payment type for view logic
        $isTripayPayment = $transaksi->pembayaran && $transaksi->pembayaran->payment_type === 'tripay';
        $isManualPayment = !$isTripayPayment;

        return view('frontend.cart.confirmation', compact(
            'transaksi', 
            'whatsappUrl', 
            'isTripayPayment', 
            'isManualPayment'
        ));
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
}
