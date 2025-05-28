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
        $paymentChannels = PaymentChannel::orderBy('group')->orderBy('name')->get()->groupBy('group');

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
            // Check if we should remove old data
            $removeOldData = $request->input('remove_old_data', false);
            
            // Log sync start
            $totalChannelsBefore = PaymentChannel::count();
            $syncedChannelsBefore = PaymentChannel::where('is_synced', true)->count();
            
            Log::info('=== SYNC PAYMENT CHANNELS STARTED ===', [
                'total_channels_before' => $totalChannelsBefore,
                'synced_channels_before' => $syncedChannelsBefore,
                'remove_old_data' => $removeOldData,
                'user_id' => auth()->id()
            ]);

            // If remove old data mode, mark all existing channels as not synced
            if ($removeOldData) {
                Log::info('Marking all existing channels as not synced before sync');
                PaymentChannel::query()->update(['is_synced' => false]);
            }

            // Sync from Tripay
            $syncResult = $this->tripayService->syncPaymentChannels();
            
            // Handle sync result
            $synced = 0;
            $syncMessage = '';
            
            if (is_array($syncResult)) {
                $synced = (int) ($syncResult['count'] ?? 0);
                $syncMessage = (string) ($syncResult['message'] ?? '');
                $syncSuccess = (bool) ($syncResult['success'] ?? false);
                
                if (!$syncSuccess) {
                    throw new \Exception($syncMessage ?: 'Sinkronisasi gagal dari Tripay API');
                }
            } elseif (is_numeric($syncResult)) {
                $synced = (int) $syncResult;
            } else {
                throw new \Exception('Format hasil sinkronisasi tidak dikenal: ' . gettype($syncResult));
            }

            // Remove old channels if requested
            $removedCount = 0;
            if ($removeOldData) {
                $oldChannels = PaymentChannel::where('is_synced', false)->get();
                $removedCount = $oldChannels->count();
                
                if ($removedCount > 0) {
                    Log::info('Removing old payment channels', [
                        'channels_to_remove' => $oldChannels->pluck('name', 'code')->toArray()
                    ]);
                    
                    PaymentChannel::where('is_synced', false)->delete();
                    Log::info('Successfully removed old payment channels', ['count' => $removedCount]);
                }
            }
            
            $totalChannelsAfter = PaymentChannel::count();
            
            Log::info('=== SYNC PAYMENT CHANNELS COMPLETED ===', [
                'synced_channels' => $synced,
                'removed_channels' => $removedCount,
                'total_channels_after' => $totalChannelsAfter
            ]);

            // Build response message
            $message = "Berhasil sinkronisasi {$synced} payment channel";
            if ($syncMessage) {
                $message = $syncMessage;
            }
            if ($removeOldData && $removedCount > 0) {
                $message .= " dan menghapus {$removedCount} channel lama";
            }

            // Return response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'synced' => $synced,
                        'removed' => $removedCount,
                        'total_after' => $totalChannelsAfter
                    ]
                ]);
            }

            return redirect()->route('admin.payment.index')->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Payment Channel Sync Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal sinkronisasi payment channels: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.payment.index')
                ->with('error', 'Gagal sinkronisasi payment channels: ' . $e->getMessage());
        }
    }

    /**
     * Reset all payment channels
     */
    public function resetChannels(Request $request)
    {
        try {
            $totalChannels = PaymentChannel::count();
            
            Log::info('=== RESET PAYMENT CHANNELS STARTED ===', [
                'total_channels_before' => $totalChannels,
                'user_id' => auth()->id()
            ]);
            
            PaymentChannel::truncate();
            
            Log::info('=== RESET PAYMENT CHANNELS COMPLETED ===', [
                'removed_channels' => $totalChannels
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$totalChannels} payment channel",
                    'data' => [
                        'removed' => $totalChannels
                    ]
                ]);
            }

            return redirect()->route('admin.payment.index')
                ->with('success', "Berhasil menghapus {$totalChannels} payment channel");
                
        } catch (\Exception $e) {
            Log::error('Payment Channel Reset Error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal reset payment channels: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.payment.index')
                ->with('error', 'Gagal reset payment channels: ' . $e->getMessage());
        }
    }

    /**
     * Update payment channel status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $channel = PaymentChannel::findOrFail($id);
            $channel->update(['is_active' => $request->is_active]);

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
     * Update payment channel fee
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
                'message' => "Payment channel {$channelName} berhasil dihapus"
            ]);
        } catch (\Exception $e) {
            Log::error('Payment Channel Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus payment channel: ' . $e->getMessage()
            ], 500);
        }
    }
}
