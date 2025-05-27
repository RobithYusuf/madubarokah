<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentChannel;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        $this->tripayService = $tripayService;
    }

    /**
     * Display payment channels management page
     */
    public function index()
    {
        // Get payment channels grouped by type
        $paymentChannels = PaymentChannel::all()->groupBy('group');

        // Count statistics
        $totalChannels = PaymentChannel::count();
        $activeChannels = PaymentChannel::where('is_active', true)->count();

        return view('admin.payment.index', compact('paymentChannels', 'totalChannels', 'activeChannels'));
    }

    /**
     * Sync payment channels from Tripay API
     */
    public function syncChannels(Request $request)
    {
        try {
            $result = $this->tripayService->syncPaymentChannels();

            if (is_array($result) && isset($result['success'])) {
                if ($result['success']) {
                    // Jika request adalah AJAX, kembalikan response JSON
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Berhasil menyinkronkan ' . $result['count'] . ' payment channel dari Tripay API.',
                            'data' => [
                                'synced' => $result['count']
                            ]
                        ]);
                    }

                    // Jika bukan AJAX, gunakan redirect seperti biasa
                    return redirect()->route('admin.payment.index')
                        ->with('success', 'Berhasil menyinkronkan ' . $result['count'] . ' payment channel dari Tripay API.');
                } else {
                    // Jika request adalah AJAX, kembalikan response JSON
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal menyinkronkan payment channel: ' . $result['message']
                        ], 500);
                    }

                    // Jika bukan AJAX, gunakan redirect seperti biasa
                    return redirect()->route('admin.payment.index')
                        ->with('error', 'Gagal menyinkronkan payment channel: ' . $result['message']);
                }
            } else {
                // Backward compatibility jika return masih berupa integer
                $count = is_numeric($result) ? $result : 0;

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Berhasil menyinkronkan ' . $count . ' payment channel dari Tripay API.'
                    ]);
                }

                return redirect()->route('admin.payment.index')
                    ->with('success', 'Berhasil menyinkronkan ' . $count . ' payment channel dari Tripay API.');
            }
        } catch (\Exception $e) {
            Log::error('Error syncing payment channels: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyinkronkan payment channel: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.payment.index')
                ->with('error', 'Terjadi kesalahan saat menyinkronkan payment channel: ' . $e->getMessage());
        }
    }

    /**
     * Update payment channel status (active/inactive)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $channel = PaymentChannel::findOrFail($id);
            $channel->is_active = $request->is_active;
            $channel->save();

            return response()->json([
                'success' => true,
                'message' => 'Status payment channel berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating payment channel status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status payment channel.'
            ], 500);
        }
    }

    /**
     * Update payment channel fee
     */
    public function updateFee(Request $request, $id)
    {
        try {
            $channel = PaymentChannel::findOrFail($id);

            // Validate request
            $request->validate([
                'fee_flat' => 'required|numeric|min:0',
                'fee_percent' => 'required|numeric|min:0|max:100',
                'minimum_fee' => 'nullable|numeric|min:0',
                'maximum_fee' => 'nullable|numeric|min:0',
            ]);

            // Update channel fee
            $channel->fee_flat = $request->fee_flat;
            $channel->fee_percent = $request->fee_percent;
            $channel->minimum_fee = $request->minimum_fee ?? 0;
            $channel->maximum_fee = $request->maximum_fee ?? 0;
            $channel->save();

            return response()->json([
                'success' => true,
                'message' => 'Fee payment channel berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating payment channel fee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui fee payment channel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete payment channel
     */
    public function destroy($id)
    {
        try {
            $channel = PaymentChannel::findOrFail($id);
            $channelName = $channel->name;

            $channel->delete();

            return response()->json([
                'success' => true,
                'message' => "Payment channel '{$channelName}' berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting payment channel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus payment channel: ' . $e->getMessage()
            ], 500);
        }
    }
}
