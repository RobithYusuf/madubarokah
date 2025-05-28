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
     * Create transaction with improved logging
     */
    public function createTransaction($data)
    {
        $startTime = microtime(true);
        
        try {
            \Log::info('TripayService: Starting transaction creation', [
                'method' => $data['method'] ?? 'unknown',
                'merchant_ref' => $data['merchant_ref'] ?? 'unknown',
                'amount' => $data['amount'] ?? 0
            ]);
            
            // Add callback URL if not provided with better fallback
            if (!isset($data['callback_url']) || empty($data['callback_url'])) {
                $callbackUrl = config('tripay.callback_url');
                
                // If config is still empty, create fallback URL
                if (empty($callbackUrl)) {
                    $appUrl = config('app.url', 'http://localhost:8000');
                    $callbackUrl = rtrim($appUrl, '/') . '/api/tripay/callback';
                    \Log::warning('TripayService: Using fallback callback URL', [
                        'fallback_url' => $callbackUrl,
                        'app_url' => $appUrl
                    ]);
                }
                
                $data['callback_url'] = $callbackUrl;
                \Log::info('TripayService: Added callback URL', [
                    'callback_url' => $data['callback_url']
                ]);
            }
            
            // Add return URL if not provided with better fallback
            if (!isset($data['return_url']) || empty($data['return_url'])) {
                $returnUrl = config('tripay.return_url');
                
                // If config is still empty, create fallback URL
                if (empty($returnUrl)) {
                    $appUrl = config('app.url', 'http://localhost:8000');
                    $returnUrl = rtrim($appUrl, '/') . '/api/tripay/return';
                    \Log::warning('TripayService: Using fallback return URL', [
                        'fallback_url' => $returnUrl,
                        'app_url' => $appUrl
                    ]);
                }
                
                $data['return_url'] = $returnUrl;
                \Log::info('TripayService: Added return URL', [
                    'return_url' => $data['return_url']
                ]);
            }
            
            // Final validation for callback URL
            if (empty($data['callback_url'])) {
                \Log::error('TripayService: Callback URL masih kosong setelah fallback', [
                    'config_callback' => config('tripay.callback_url'),
                    'app_url' => config('app.url'),
                    'data_callback' => $data['callback_url'] ?? 'not_set'
                ]);
                return [
                    'success' => false,
                    'message' => 'Callback URL tidak dapat dikonfigurasi. Periksa APP_URL di file .env'
                ];
            }
            
            $signature = $this->generateSignature($data);

            $payload = array_merge($data, [
                'signature' => $signature
            ]);
            
            \Log::info('TripayService: Payload prepared', [
                'method' => $payload['method'],
                'merchant_ref' => $payload['merchant_ref'],
                'amount' => $payload['amount'],
                'callback_url' => $payload['callback_url'],
                'return_url' => $payload['return_url'],
                'customer_email' => $payload['customer_email'] ?? 'not_set',
                'signature_length' => strlen($signature)
            ]);

            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->retry(2, 1000)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/transaction/create', $payload);

            $executionTime = microtime(true) - $startTime;
            
            \Log::info('TripayService: API response received', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'execution_time' => round($executionTime, 2) . 's'
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                \Log::info('TripayService: Transaction created successfully', [
                    'merchant_ref' => $payload['merchant_ref'],
                    'tripay_reference' => $responseData['data']['reference'] ?? 'unknown',
                    'response_success' => $responseData['success'] ?? false,
                    'has_qr_string' => isset($responseData['data']['qr_string']),
                    'has_qr_url' => isset($responseData['data']['qr_url']),
                    'has_payment_code' => isset($responseData['data']['pay_code'])
                ]);
                
                return $responseData;
            }

            $errorBody = $response->body();
            \Log::error('TripayService: Create Transaction HTTP Error', [
                'status' => $response->status(),
                'response_body' => $errorBody,
                'payload_method' => $payload['method'],
                'payload_amount' => $payload['amount'],
                'payload_email' => $payload['customer_email'] ?? 'not_set'
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create transaction: ' . $errorBody
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $executionTime = microtime(true) - $startTime;
            \Log::error('TripayService: Connection Exception', [
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2) . 's'
            ]);
            
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            \Log::error('TripayService: Create Transaction Exception', [
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2) . 's',
                'trace' => $e->getTraceAsString()
            ]);
            
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
        $signature = hash_hmac(
            'sha256',
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
