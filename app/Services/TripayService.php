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
     * Get payment instructions for specific channel
     */
    public function getPaymentInstructions($channelCode)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/payment/instruction', [
                'code' => $channelCode
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['success']) && $responseData['success'] && isset($responseData['data'])) {
                    return $responseData['data'];
                }
            }
            
            Log::debug('Instructions response for ' . $channelCode, [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting instructions for channel: ' . $channelCode . ' - ' . $e->getMessage());
            return null;
        }
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
     * Sync payment channels with database including instructions
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

                // Ambil instructions dari endpoint yang benar
                $instructions = null;
                $instructionsData = $this->getPaymentInstructions($channel['code']);
                
                if ($instructionsData && !empty($instructionsData)) {
                    if (is_array($instructionsData)) {
                        $instructions = $instructionsData;
                        \Log::info('Instructions fetched successfully', [
                            'channel' => $channel['code'],
                            'instructions_count' => count($instructionsData)
                        ]);
                    } elseif (is_string($instructionsData)) {
                        $instructions = [$instructionsData];
                        \Log::info('Instructions fetched as string', [
                            'channel' => $channel['code']
                        ]);
                    }
                } else {
                    \Log::debug('No instructions available for channel', [
                        'channel' => $channel['code']
                    ]);
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
                    $result = PaymentChannel::updateOrCreate(
                        ['code' => $channel['code']],
                        $channelData
                    );

                    // Tandai sebagai synced dan set waktu sync
                    $result->update([
                        'is_synced' => true,
                        'last_synced_at' => now()
                    ]);

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
     * Create transaction with correct data formatting
     */
    public function createTransaction($data)
    {
        try {
            // Ensure data types are correct
            $cleanData = [
                'method' => $data['method'],
                'merchant_ref' => $data['merchant_ref'],
                'amount' => (int) $data['amount'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'order_items' => [],
                'expired_time' => (int) $data['expired_time']
            ];
            
            // Process order_items with correct data types
            if (isset($data['order_items']) && is_array($data['order_items'])) {
                foreach ($data['order_items'] as $item) {
                    $cleanData['order_items'][] = [
                        'sku' => $item['sku'],
                        'name' => $item['name'],
                        'price' => (int) $item['price'],
                        'quantity' => (int) $item['quantity'],
                        'product_url' => $item['product_url'] ?? '',
                        'image_url' => $item['image_url'] ?? ''
                    ];
                }
            }
            
            // Add callback URL
            $callbackUrl = $data['callback_url'] ?? config('tripay.callback_url');
            if (empty($callbackUrl)) {
                $appUrl = config('app.url', 'http://127.0.0.1:8000');
                $callbackUrl = rtrim($appUrl, '/') . '/api/tripay/callback';
            }
            $cleanData['callback_url'] = $callbackUrl;
            
            // Add return URL
            $returnUrl = $data['return_url'] ?? config('tripay.return_url');
            if (empty($returnUrl)) {
                $appUrl = config('app.url', 'http://127.0.0.1:8000');
                $returnUrl = rtrim($appUrl, '/') . '/confirmation';
            }
            $cleanData['return_url'] = $returnUrl;
            
            // Generate signature
            $signature = $this->generateSignature($cleanData);
            $cleanData['signature'] = $signature;
            
            // Send to Tripay API
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . '/transaction/create', $cleanData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['success']) && $responseData['success']) {
                    return $responseData;
                }
            }

            $errorData = $response->json();
            return [
                'success' => false,
                'message' => $errorData['message'] ?? 'Failed to create transaction',
                'error_details' => $errorData
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate signature for API request
     */
    private function generateSignature($data)
    {
        $amount = (int) $data['amount'];
        $merchantRef = $data['merchant_ref'];
        
        $signatureString = $this->merchantCode . $merchantRef . $amount;
        $signature = hash_hmac('sha256', $signatureString, $this->privateKey);

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
