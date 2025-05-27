<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentChannel;
use App\Services\TripayService;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        $this->tripayService = $tripayService;
    }

    /**
     * Menampilkan halaman manajemen payment channel
     */
    public function index()
    {
        // Ambil semua payment channel dan kelompokkan berdasarkan group
        $paymentChannels = PaymentChannel::orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        // Hitung total payment channel dan yang aktif
        $totalChannels = PaymentChannel::count();
        $activeChannels = PaymentChannel::where('is_active', true)->count();

        return view('admin.payment.index', compact(
            'paymentChannels',
            'totalChannels',
            'activeChannels'
        ));
    }

    /**
     * Sinkronisasi payment channel dari Tripay API
     */
    public function syncChannels()
    {
        try {
            $synced = $this->tripayService->syncPaymentChannels();

            return redirect()->route('admin.payment.index')
                ->with('success', "Berhasil sinkronisasi {$synced} payment channel dari Tripay");
        } catch (\Exception $e) {
            Log::error('Payment Channel Sync Error: ' . $e->getMessage());
            return redirect()->route('admin.payment.index')
                ->with('error', 'Gagal sinkronisasi payment channels: ' . $e->getMessage());
        }
    }

    /**
     * Update status aktif/nonaktif payment channel
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $channel = PaymentChannel::findOrFail($id);
            $channel->update([
                'is_active' => $request->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => "Payment channel {$channel->name} berhasil " . ($request->is_active ? 'diaktifkan' : 'dinonaktifkan')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update fee payment channel
     */
    public function updateFee(Request $request, $id)
    {
        $request->validate([
            'fee_flat' => 'required|numeric|min:0',
            'fee_percent' => 'required|numeric|min:0|max:100',
            'minimum_fee' => 'nullable|numeric|min:0',
            'maximum_fee' => 'nullable|numeric|min:0'
        ]);

        try {
            $channel = PaymentChannel::findOrFail($id);
            $channel->update([
                'fee_flat' => $request->fee_flat,
                'fee_percent' => $request->fee_percent,
                'minimum_fee' => $request->minimum_fee,
                'maximum_fee' => $request->maximum_fee
            ]);

            return response()->json([
                'success' => true,
                'message' => "Fee untuk {$channel->name} berhasil diperbarui"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah fee: ' . $e->getMessage()
            ], 500);
        }
    }
}
