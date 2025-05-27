<?php

namespace App\Services;

use App\Models\PaymentChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    private $apiKey;
    private $privateKey;
    private $merchantCode;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('tripay.api_key');
        $this->privateKey = config('tripay.private_key');
        $this->merchantCode = config('tripay.merchant_code');
        $this->baseUrl = config('tripay.base_url');
    }

    /**
     * Get payment channels from Tripay API
     */
    public function getPaymentChannels()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/merchant/payment-channel');

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Return data channels jika ada
                if (isset($responseData['success']) && $responseData['success'] && isset($responseData['data'])) {
                    return $responseData['data'];
                } else {
                    Log::warning('Tripay API returned unsuccessful response');
                    return [];
                }
            }

            Log::error('Tripay API Error: Failed to get payment channels', [
                'status' => $response->status()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Tripay API Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync payment channels with database
     */
    public function syncPaymentChannels()
    {
        try {
            $channels = $this->getPaymentChannels();
            $synced = 0;
            $skipped = 0;

            if (empty($channels)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data payment channel dari API Tripay',
                    'count' => 0
                ];
            }

            foreach ($channels as $channel) {
                // Validasi struktur data channel
                if (!is_array($channel) || !isset($channel['code'])) {
                    $skipped++;
                    continue;
                }

                // Ambil fee data dengan validasi
                $feeFlat = 0;
                $feePercent = 0;
                
                if (isset($channel['fee_customer']) && is_array($channel['fee_customer'])) {
                    $feeFlat = $channel['fee_customer']['flat'] ?? 0;
                    $feePercent = $channel['fee_customer']['percent'] ?? 0;
                } elseif (isset($channel['fee_flat'])) {
                    $feeFlat = $channel['fee_flat'];
                } elseif (isset($channel['fee_percent'])) {
                    $feePercent = $channel['fee_percent'];
                }

                // Proses instructions
                $instructions = null;
                if (isset($channel['instructions'])) {
                    if (is_array($channel['instructions'])) {
                        $instructions = json_encode($channel['instructions']);
                    } elseif (is_string($channel['instructions'])) {
                        $instructions = $channel['instructions'];
                    }
                }

                $channelData = [
                    'name' => $channel['name'] ?? 'Unknown',
                    'icon_url' => $channel['icon_url'] ?? null,
                    'group' => $channel['group'] ?? 'other',
                    'is_active' => $channel['active'] ?? true,
                    'fee_flat' => $feeFlat,
                    'fee_percent' => $feePercent,
                    'minimum_fee' => $channel['minimum_fee'] ?? 0,
                    'maximum_fee' => $channel['maximum_fee'] ?? 0,
                    'instructions' => $instructions,
                ];

                try {
                    PaymentChannel::updateOrCreate(
                        ['code' => $channel['code']],
                        $channelData
                    );
                    $synced++;
                } catch (\Exception $e) {
                    Log::error('Failed to save payment channel: ' . $channel['code'] . ' - ' . $e->getMessage());
                    $skipped++;
                }
            }

            return [
                'success' => true,
                'message' => "Berhasil sinkronisasi {$synced} payment channels" . ($skipped > 0 ? ", {$skipped} dilewati" : ""),
                'count' => $synced
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in syncPaymentChannels: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'count' => 0
            ];
        }
    }

    /**
     * Create transaction
     */
    public function createTransaction($data)
    {
        try {
            $signature = $this->generateSignature($data);
            
            $payload = array_merge($data, [
                'signature' => $signature
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl . '/transaction/create', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Tripay Create Transaction Error', [
                'status' => $response->status(),
                'response' => $response->body(),
                'payload' => $payload
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Tripay Create Transaction Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate signature for API request
     */
    private function generateSignature($data)
    {
        $signature = hash_hmac('sha256', 
            $this->merchantCode . $data['merchant_ref'] . $data['amount'], 
            $this->privateKey
        );
        
        return $signature;
    }

    /**
     * Get transaction detail
     */
    public function getTransaction($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/transaction/detail', [
                'reference' => $reference
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Tripay Get Transaction Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify callback signature
     */
    public function verifyCallbackSignature($data)
    {
        $signature = isset($data['signature']) ? $data['signature'] : '';
        unset($data['signature']);

        $payload = json_encode($data);
        $calculatedSignature = hash_hmac('sha256', $payload, $this->privateKey);

        return hash_equals($calculatedSignature, $signature);
    }
}
