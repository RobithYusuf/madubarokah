<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\ShippingArea;
use Illuminate\Http\Client\Pool;

class RajaOngkirService
{
    private $apiKey;
    private $baseUrl;
    private $courierTimeouts;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
        $this->baseUrl = 'https://api.rajaongkir.com/starter';
        
        // Track courier timeout history for optimization
        $this->courierTimeouts = Cache::get('courier_timeouts', []);
    }

    /**
     * Calculate shipping cost with improved error handling and timeout management
     */
    public function calculateShippingCost($origin, $destination, $weight, $courier)
    {
        $startTime = microtime(true);
        
        // Generate cache key for this specific request
        $cacheKey = "shipping_cost_{$origin}_{$destination}_{$weight}_{$courier}";
        
        // Try to get from cache first (1 day cache)
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult !== null) {
            \Log::info('RajaOngkir shipping cost retrieved from cache', [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'courier' => $courier,
                'cache_key' => $cacheKey
            ]);
            
            return $cachedResult;
        }
        
        \Log::info('RajaOngkir calculate shipping cost started', [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier
        ]);
        
        try {
            if (empty($this->apiKey)) {
                \Log::error('RajaOngkir API key not configured');
                return [
                    'success' => false,
                    'message' => 'API key RajaOngkir tidak dikonfigurasi'
                ];
            }

            // Validate parameters
            if (!$origin || !$destination || !$weight || !$courier) {
                \Log::error('RajaOngkir invalid parameters', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier
                ]);
                return [
                    'success' => false,
                    'message' => 'Parameter tidak lengkap untuk perhitungan ongkir'
                ];
            }

            // Check if courier frequently times out
            $timeoutKey = "timeout_{$courier}";
            $recentTimeouts = Cache::get($timeoutKey, 0);
            
            if ($recentTimeouts >= 3) {
                \Log::warning("RajaOngkir skipping courier {$courier} due to frequent timeouts");
                return [
                    'success' => false,
                    'message' => "Kurir {$courier} sedang tidak tersedia",
                    'timeout_skip' => true
                ];
            }

            // Dynamic timeout based on courier performance
            $timeout = $this->getCourierTimeout($courier);
            
            $response = Http::timeout($timeout)
                ->connectTimeout(20) // Connection timeout 8 seconds
                ->retry(1, 500) // Retry 1 time with 0.5 second delay
                ->withHeaders([
                    'key' => $this->apiKey,
                    'content-type' => 'application/x-www-form-urlencoded'
                ])
                ->asForm()
                ->post($this->baseUrl . '/cost', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier
                ]);

            $executionTime = microtime(true) - $startTime;
            
            \Log::info('RajaOngkir API response received', [
                'execution_time' => round($executionTime, 2) . 's',
                'status' => $response->status(),
                'success' => $response->successful(),
                'courier' => $courier
            ]);

            // Reset timeout counter on success
            if ($response->successful()) {
                Cache::forget($timeoutKey);
                $this->updateCourierPerformance($courier, $executionTime, true);
            }

            if (!$response->successful()) {
                \Log::error('RajaOngkir API HTTP error', [
                    'status' => $response->status(),
                    'courier' => $courier,
                    'execution_time' => round($executionTime, 2) . 's'
                ]);
                return [
                    'success' => false,
                    'message' => "RajaOngkir API error untuk {$courier}: HTTP " . $response->status()
                ];
            }

            $data = $response->json();
            
            if (!isset($data['rajaongkir'])) {
                \Log::error('RajaOngkir invalid response structure', ['courier' => $courier]);
                return ['success' => false, 'message' => 'Invalid response structure'];
            }

            $rajaongkir = $data['rajaongkir'];

            if (isset($rajaongkir['status']['code']) && $rajaongkir['status']['code'] !== 200) {
                \Log::error('RajaOngkir API status error', [
                    'status_code' => $rajaongkir['status']['code'],
                    'description' => $rajaongkir['status']['description'] ?? 'Unknown error',
                    'courier' => $courier
                ]);
                return [
                    'success' => false,
                    'message' => "RajaOngkir Error untuk {$courier}: " . ($rajaongkir['status']['description'] ?? 'Unknown error')
                ];
            }

            if (!isset($rajaongkir['results']) || empty($rajaongkir['results'])) {
                \Log::warning('RajaOngkir no shipping services available', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'courier' => $courier
                ]);
                return [
                    'success' => false,
                    'message' => "Tidak ada layanan pengiriman tersedia untuk kurir {$courier}"
                ];
            }

            \Log::info('RajaOngkir calculate shipping cost success', [
                'results_count' => count($rajaongkir['results']),
                'execution_time' => round($executionTime, 2) . 's',
                'courier' => $courier
            ]);

            $result = [
                'success' => true,
                'data' => $rajaongkir['results'],
                'execution_time' => $executionTime
            ];
            
            // Cache the successful result for 1 day
            Cache::put($cacheKey, $result, now()->addDays(1));
            
            // Track cache key for easier management
            $cacheKeys = Cache::get('shipping_cache_keys', []);
            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('shipping_cache_keys', $cacheKeys, now()->addDays(7));
            }
            
            return $result;
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $executionTime = microtime(true) - $startTime;
            
            // Increment timeout counter
            $timeoutKey = "timeout_{$courier}";
            $timeouts = Cache::get($timeoutKey, 0) + 1;
            Cache::put($timeoutKey, $timeouts, now()->addHours(1));
            
            $this->updateCourierPerformance($courier, $executionTime, false);
            
            \Log::error('RajaOngkir connection timeout/error', [
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2) . 's',
                'origin' => $origin,
                'destination' => $destination,
                'courier' => $courier,
                'timeout_count' => $timeouts
            ]);
            
            return [
                'success' => false,
                'message' => "Koneksi ke RajaOngkir timeout untuk kurir {$courier}. Silakan coba kurir lain.",
                'fallback_suggested' => true,
                'timeout' => true
            ];
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            \Log::error('RajaOngkir shipping cost error', [
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2) . 's',
                'courier' => $courier
            ]);
            
            return [
                'success' => false,
                'message' => "API Error untuk {$courier}: " . $e->getMessage(),
                'fallback_suggested' => true
            ];
        }
    }

    /**
     * Get provinces from RajaOngkir API
     */
    public function getProvinces()
    {
        $startTime = microtime(true);
        
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false, 
                    'message' => 'API key RajaOngkir tidak dikonfigurasi'
                ];
            }

            $response = Http::timeout(12)
                ->connectTimeout(8)
                ->retry(1, 500)
                ->withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/province');
                
            $executionTime = microtime(true) - $startTime;

            if (!$response->successful()) {
                $errorMsg = 'HTTP Error ' . $response->status();
                if ($response->status() == 401) {
                    $errorMsg .= ' - API key tidak valid';
                } elseif ($response->status() == 403) {
                    $errorMsg .= ' - Akses ditolak';
                } elseif ($response->status() >= 500) {
                    $errorMsg .= ' - Server RajaOngkir sedang bermasalah';
                }
                
                \Log::error('RajaOngkir provinces HTTP error', [
                    'status' => $response->status(),
                    'execution_time' => round($executionTime, 2) . 's'
                ]);
                
                return [
                    'success' => false, 
                    'message' => $errorMsg
                ];
            }

            $data = $response->json();
            
            if (!isset($data['rajaongkir'])) {
                \Log::error('RajaOngkir provinces invalid response structure');
                return [
                    'success' => false, 
                    'message' => 'Format response API tidak valid'
                ];
            }

            $rajaongkir = $data['rajaongkir'];
            
            if (isset($rajaongkir['status']['code']) && $rajaongkir['status']['code'] !== 200) {
                \Log::error('RajaOngkir provinces API status error', [
                    'status_code' => $rajaongkir['status']['code'],
                    'description' => $rajaongkir['status']['description'] ?? 'Unknown error'
                ]);
                return [
                    'success' => false,
                    'message' => 'RajaOngkir API Error: ' . ($rajaongkir['status']['description'] ?? 'Unknown error')
                ];
            }
            
            if (!isset($rajaongkir['results']) || empty($rajaongkir['results'])) {
                \Log::warning('RajaOngkir provinces no data returned');
                return [
                    'success' => false, 
                    'message' => 'Tidak ada data provinsi dari API'
                ];
            }
            
            \Log::info('RajaOngkir provinces retrieved successfully', [
                'count' => count($rajaongkir['results']),
                'execution_time' => round($executionTime, 2) . 's'
            ]);
            
            return [
                'success' => true, 
                'data' => $rajaongkir['results'],
                'execution_time' => $executionTime
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $executionTime = microtime(true) - $startTime;
            \Log::error('RajaOngkir provinces connection error', [
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2) . 's'
            ]);
            
            return [
                'success' => false, 
                'message' => 'Koneksi ke RajaOngkir timeout untuk data provinsi'
            ];
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            \Log::error('RajaOngkir provinces error', [
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2) . 's'
            ]);
            
            return [
                'success' => false, 
                'message' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get cities from RajaOngkir API
     */
    public function getCities($provinceId = null)
    {
        $startTime = microtime(true);
        
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false, 
                    'message' => 'API key RajaOngkir tidak dikonfigurasi'
                ];
            }

            $params = $provinceId ? ['province' => $provinceId] : [];
            
            $response = Http::timeout(15)
                ->connectTimeout(8)
                ->retry(1, 500)
                ->withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/city', $params);
                
            $executionTime = microtime(true) - $startTime;

            if (!$response->successful()) {
                \Log::error('RajaOngkir cities HTTP error', [
                    'status' => $response->status(),
                    'province_id' => $provinceId,
                    'execution_time' => round($executionTime, 2) . 's'
                ]);
                
                return [
                    'success' => false, 
                    'message' => 'HTTP Error ' . $response->status() . ' untuk provinsi ' . $provinceId
                ];
            }

            $data = $response->json();
            
            if (!isset($data['rajaongkir'])) {
                \Log::error('RajaOngkir cities invalid response structure', ['province_id' => $provinceId]);
                return [
                    'success' => false, 
                    'message' => 'Format response city tidak valid'
                ];
            }

            $rajaongkir = $data['rajaongkir'];
            
            if (isset($rajaongkir['status']['code']) && $rajaongkir['status']['code'] !== 200) {
                \Log::error('RajaOngkir cities API status error', [
                    'status_code' => $rajaongkir['status']['code'],
                    'description' => $rajaongkir['status']['description'] ?? 'Unknown error',
                    'province_id' => $provinceId
                ]);
                return [
                    'success' => false,
                    'message' => 'RajaOngkir City API Error: ' . ($rajaongkir['status']['description'] ?? 'Unknown error')
                ];
            }
            
            if (!isset($rajaongkir['results']) || empty($rajaongkir['results'])) {
                \Log::warning('RajaOngkir cities no data returned', ['province_id' => $provinceId]);
                return [
                    'success' => false, 
                    'message' => 'Tidak ada data kota untuk provinsi ' . $provinceId
                ];
            }
            
            \Log::info('RajaOngkir cities retrieved successfully', [
                'province_id' => $provinceId,
                'count' => count($rajaongkir['results']),
                'execution_time' => round($executionTime, 2) . 's'
            ]);
            
            return [
                'success' => true, 
                'data' => $rajaongkir['results'],
                'execution_time' => $executionTime
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $executionTime = microtime(true) - $startTime;
            \Log::error('RajaOngkir cities connection error', [
                'error' => $e->getMessage(),
                'province_id' => $provinceId,
                'execution_time' => round($executionTime, 2) . 's'
            ]);
            
            return [
                'success' => false, 
                'message' => 'Koneksi timeout untuk data kota provinsi ' . $provinceId
            ];
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            \Log::error('RajaOngkir cities error', [
                'error' => $e->getMessage(),
                'province_id' => $provinceId,
                'execution_time' => round($executionTime, 2) . 's'
            ]);
            
            return [
                'success' => false, 
                'message' => 'API Error untuk provinsi ' . $provinceId . ': ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync provinces
     */
    public function syncProvinces()
    {
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        
        try {
            $provincesResult = $this->getProvinces();
            
            if (!$provincesResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data provinsi: ' . $provincesResult['message']
                ];
            }

            $provinces = $provincesResult['data'];
            $totalProvinces = count($provinces);
            
            if ($totalProvinces == 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data provinsi dari API RajaOngkir'
                ];
            }

            $synced = 0;
            $errors = 0;
            $totalCities = 0;

            foreach ($provinces as $index => $province) {
                if (!isset($province['province_id']) || !isset($province['province'])) {
                    $errors++;
                    continue;
                }
                
                $provinceId = $province['province_id'];
                $provinceName = $province['province'];
                
                try {
                    $citiesResult = $this->getCities($provinceId);
                    
                    if ($citiesResult['success'] && !empty($citiesResult['data'])) {
                        $cities = $citiesResult['data'];
                        $citiesCount = 0;
                        
                        foreach ($cities as $city) {
                            try {
                                if (!isset($city['city_id']) || !isset($city['city_name']) || !isset($city['type'])) {
                                    $errors++;
                                    continue;
                                }
                                
                                ShippingArea::updateOrCreate(
                                    ['rajaongkir_id' => $city['city_id']],
                                    [
                                        'province_id' => $provinceId,
                                        'province_name' => $provinceName,
                                        'city_name' => $city['city_name'],
                                        'type' => $city['type'],
                                        'postal_code' => $city['postal_code'] ?? null
                                    ]
                                );
                                $citiesCount++;
                                $synced++;
                                $totalCities++;
                            } catch (\Exception $dbError) {
                                $errors++;
                            }
                        }
                    } else {
                        $errors++;
                    }
                    
                    usleep(300000); // 0.3 second delay
                    
                } catch (\Exception $e) {
                    $errors++;
                }
            }
            
            if ($totalCities > 0) {
                return [
                    'success' => true,
                    'message' => "Sinkronisasi berhasil! {$totalCities} kota/kabupaten dari {$totalProvinces} provinsi." . 
                               ($errors > 0 ? " ({$errors} error diabaikan)" : ""),
                    'synced' => $totalCities,
                    'errors' => $errors,
                    'provinces_processed' => $totalProvinces
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Sinkronisasi gagal. Total error: {$errors}. Periksa API key dan koneksi internet.",
                    'synced' => 0,
                    'errors' => $errors
                ];
            }
            
        } catch (\Exception $e) {
            \Log::error('RajaOngkir sync error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error kritis: ' . $e->getMessage()
            ];
        }
    }

    // Cache methods
    public function getCachedProvinces()
    {
        return Cache::remember('rajaongkir_provinces', 3600, function () {
            return ShippingArea::provinces()->get();
        });
    }

    public function getCachedCities($provinceId)
    {
        return Cache::remember("rajaongkir_cities_{$provinceId}", 3600, function () use ($provinceId) {
            return ShippingArea::citiesByProvince($provinceId)->get();
        });
    }

    public function getMultipleCourierCosts($origin, $destination, $weight, $couriers = ['jne', 'pos', 'tiki'])
    {
        $results = [];
        $errors = [];
        $totalStartTime = microtime(true);
        
        \Log::info('Starting multiple courier cost calculation', [
            'couriers' => $couriers,
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight
        ]);
        
        // Sort couriers by performance (fastest first)
        $sortedCouriers = $this->sortCouriersByPerformance($couriers);
        
        foreach ($sortedCouriers as $courier) {
            try {
                $result = $this->calculateShippingCost($origin, $destination, $weight, $courier);
                
                if ($result['success']) {
                    $results = array_merge($results, $result['data']);
                    \Log::info("Courier {$courier} calculation successful", [
                        'services_count' => count($result['data']),
                        'execution_time' => round($result['execution_time'] ?? 0, 2) . 's'
                    ]);
                } else {
                    $errors[$courier] = $result['message'];
                    
                    // Log different types of errors
                    if (isset($result['timeout_skip'])) {
                        \Log::info("Courier {$courier} skipped due to frequent timeouts");
                    } elseif (isset($result['timeout'])) {
                        \Log::warning("Courier {$courier} timed out", ['message' => $result['message']]);
                    } else {
                        \Log::warning("Courier {$courier} failed", ['message' => $result['message']]);
                    }
                }
                
                // Add small delay between requests to avoid overwhelming API
                if (count($sortedCouriers) > 1) {
                    usleep(200000); // 0.2 second delay
                }
                
            } catch (\Exception $e) {
                $errors[$courier] = 'Error: ' . $e->getMessage();
                \Log::error("Exception in courier {$courier} calculation", [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $totalExecutionTime = microtime(true) - $totalStartTime;
        
        \Log::info('Multiple courier cost calculation completed', [
            'total_execution_time' => round($totalExecutionTime, 2) . 's',
            'successful_couriers' => count($results) > 0 ? 'Yes' : 'No',
            'services_found' => count($results),
            'errors' => $errors
        ]);
        
        // Return results with additional info
        $response = [
            'success' => !empty($results),
            'data' => $results,
            'execution_time' => $totalExecutionTime
        ];
        
        // Add debug info if no results found
        if (empty($results)) {
            $response['debug'] = [
                'message' => 'Tidak ada kurir yang berhasil memberikan tarif',
                'errors' => $errors,
                'attempted_couriers' => $couriers,
                'note' => 'Silakan coba lagi atau hubungi admin jika masalah berlanjut'
            ];
            
            // Generate fallback dummy data
            $response['fallback_data'] = $this->generateFallbackShippingData($weight);
            $response['debug']['source'] = 'fallback_dummy_data';
        }
        
        return $response;
    }
    
    /**
     * Get dynamic timeout for courier based on performance history
     */
    private function getCourierTimeout($courier)
    {
        $performanceKey = "courier_perf_{$courier}";
        $performance = Cache::get($performanceKey, ['avg_time' => 10, 'success_rate' => 100]);
        
        // Base timeout with some buffer
        $baseTimeout = max(8, ceil($performance['avg_time'] * 1.5));
        
        // Adjust based on success rate
        if ($performance['success_rate'] < 50) {
            $baseTimeout += 5; // Give more time for unreliable couriers
        } elseif ($performance['success_rate'] > 90) {
            $baseTimeout = max(8, $baseTimeout - 2); // Less time for reliable couriers
        }
        
        return min($baseTimeout, 15); // Cap at 15 seconds
    }
    
    /**
     * Update courier performance tracking
     */
    private function updateCourierPerformance($courier, $executionTime, $success)
    {
        $performanceKey = "courier_perf_{$courier}";
        $performance = Cache::get($performanceKey, [
            'avg_time' => 10,
            'success_rate' => 100,
            'total_requests' => 0,
            'successful_requests' => 0
        ]);
        
        $performance['total_requests']++;
        if ($success) {
            $performance['successful_requests']++;
        }
        
        // Update average time (only for successful requests)
        if ($success) {
            $performance['avg_time'] = (
                ($performance['avg_time'] * ($performance['successful_requests'] - 1)) + $executionTime
            ) / $performance['successful_requests'];
        }
        
        // Update success rate
        $performance['success_rate'] = ($performance['successful_requests'] / $performance['total_requests']) * 100;
        
        // Store for 24 hours
        Cache::put($performanceKey, $performance, now()->addHours(24));
    }
    
    /**
     * Sort couriers by performance (fastest and most reliable first)
     */
    private function sortCouriersByPerformance($couriers)
    {
        $courierPerformance = [];
        
        foreach ($couriers as $courier) {
            $performanceKey = "courier_perf_{$courier}";
            $performance = Cache::get($performanceKey, [
                'avg_time' => 10,
                'success_rate' => 100
            ]);
            
            // Calculate score: lower is better (faster time + higher success rate)
            $score = $performance['avg_time'] * (100 - $performance['success_rate']) / 100;
            $courierPerformance[$courier] = $score;
        }
        
        // Sort by score (ascending)
        asort($courierPerformance);
        
        return array_keys($courierPerformance);
    }
    
    /**
     * Generate fallback shipping data when API fails
     */
    private function generateFallbackShippingData($weight)
    {
        $baseRate = 15000; // Base rate Rp 15,000
        $weightRate = ceil($weight / 1000) * 5000; // Rp 5,000 per kg
        
        return [
            [
                'code' => 'fallback',
                'name' => 'Kurir Reguler',
                'costs' => [
                    [
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => [
                            [
                                'value' => $baseRate + $weightRate,
                                'etd' => '2-3',
                                'note' => 'Estimasi berdasarkan perhitungan manual'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Clear all courier performance cache
     */
    public function clearPerformanceCache()
    {
        $couriers = ['jne', 'pos', 'tiki'];
        $cleared = 0;
        
        foreach ($couriers as $courier) {
            if (Cache::forget("courier_perf_{$courier}")) {
                $cleared++;
            }
            if (Cache::forget("timeout_{$courier}")) {
                $cleared++;
            }
        }
        
        \Log::info('Courier performance cache cleared', ['cleared_keys' => $cleared]);
        
        return [
            'success' => true,
            'message' => "Cache performance {$cleared} keys berhasil dibersihkan",
            'cleared_keys' => $cleared
        ];
    }
    
    /**
     * Clear shipping cost cache
     */
    public function clearShippingCostCache($origin = null, $destination = null, $courier = null)
    {
        $cleared = 0;
        
        if ($origin && $destination && $courier) {
            // Clear specific cache entries
            $pattern = "shipping_cost_{$origin}_{$destination}_*_{$courier}";
            $keys = Cache::get('shipping_cache_keys', []);
            
            foreach ($keys as $key) {
                if (fnmatch($pattern, $key)) {
                    if (Cache::forget($key)) {
                        $cleared++;
                    }
                }
            }
        } else {
            // Clear all shipping cost cache
            $keys = Cache::get('shipping_cache_keys', []);
            
            foreach ($keys as $key) {
                if (strpos($key, 'shipping_cost_') === 0) {
                    if (Cache::forget($key)) {
                        $cleared++;
                    }
                }
            }
            
            // Clear the keys tracker
            Cache::forget('shipping_cache_keys');
        }
        
        \Log::info('Shipping cost cache cleared', [
            'cleared_keys' => $cleared,
            'origin' => $origin,
            'destination' => $destination,
            'courier' => $courier
        ]);
        
        return [
            'success' => true,
            'message' => "Cache shipping cost {$cleared} keys berhasil dibersihkan",
            'cleared_keys' => $cleared
        ];
    }
    
    /**
     * Get courier performance statistics
     */
    public function getCourierPerformanceStats()
    {
        $couriers = ['jne', 'pos', 'tiki'];
        $stats = [];
        
        foreach ($couriers as $courier) {
            $performanceKey = "courier_perf_{$courier}";
            $timeoutKey = "timeout_{$courier}";
            
            $performance = Cache::get($performanceKey, null);
            $recentTimeouts = Cache::get($timeoutKey, 0);
            
            $stats[$courier] = [
                'available' => $recentTimeouts < 3,
                'avg_response_time' => $performance ? round($performance['avg_time'], 2) . 's' : 'N/A',
                'success_rate' => $performance ? round($performance['success_rate'], 1) . '%' : 'N/A',
                'total_requests' => $performance['total_requests'] ?? 0,
                'recent_timeouts' => $recentTimeouts,
                'recommended_timeout' => $this->getCourierTimeout($courier) . 's',
                'status' => $this->getCourierStatus($courier, $recentTimeouts, $performance)
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get courier status based on performance
     */
    private function getCourierStatus($courier, $recentTimeouts, $performance)
    {
        if ($recentTimeouts >= 3) {
            return 'DISABLED - Too many timeouts';
        }
        
        if (!$performance) {
            return 'NEW - No performance data';
        }
        
        $successRate = $performance['success_rate'];
        $avgTime = $performance['avg_time'];
        
        if ($successRate >= 90 && $avgTime <= 5) {
            return 'EXCELLENT';
        } elseif ($successRate >= 80 && $avgTime <= 10) {
            return 'GOOD';
        } elseif ($successRate >= 60) {
            return 'FAIR';
        } else {
            return 'POOR';
        }
    }
    
    /**
     * Get shipping cache statistics
     */
    public function getShippingCacheStats()
    {
        $cacheKeys = Cache::get('shipping_cache_keys', []);
        $activeCache = 0;
        $expiredCache = 0;
        $totalSize = 0;
        $couriersStats = [];
        
        foreach ($cacheKeys as $key) {
            if (Cache::has($key)) {
                $activeCache++;
                
                // Extract courier from key
                $parts = explode('_', $key);
                $courier = end($parts);
                
                if (!isset($couriersStats[$courier])) {
                    $couriersStats[$courier] = 0;
                }
                $couriersStats[$courier]++;
                
                // Estimate size (rough approximation)
                $data = Cache::get($key);
                if ($data) {
                    $totalSize += strlen(serialize($data));
                }
            } else {
                $expiredCache++;
            }
        }
        
        return [
            'total_keys' => count($cacheKeys),
            'active_cache' => $activeCache,
            'expired_cache' => $expiredCache,
            'cache_hit_rate' => 'Check Laravel cache stats',
            'estimated_size' => $this->formatBytes($totalSize),
            'couriers_cached' => $couriersStats,
            'cache_duration' => '1 day',
            'next_cleanup' => 'Keys expire automatically after 1 day'
        ];
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Test API connectivity
     */
    public function testApiConnectivity()
    {
        $startTime = microtime(true);
        
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'message' => 'API key tidak dikonfigurasi'
                ];
            }
            
            // Test with a simple province request
            $response = Http::timeout(10)
                ->withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/province');
                
            $executionTime = microtime(true) - $startTime;
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['rajaongkir']['status']['code']) && $data['rajaongkir']['status']['code'] === 200) {
                    return [
                        'success' => true,
                        'message' => 'Koneksi RajaOngkir API berhasil',
                        'response_time' => round($executionTime, 2) . 's',
                        'api_status' => 'OK'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'API Error: ' . ($data['rajaongkir']['status']['description'] ?? 'Unknown error'),
                        'response_time' => round($executionTime, 2) . 's'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'HTTP Error: ' . $response->status(),
                    'response_time' => round($executionTime, 2) . 's'
                ];
            }
            
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            return [
                'success' => false,
                'message' => 'Connection Error: ' . $e->getMessage(),
                'response_time' => round($executionTime, 2) . 's'
            ];
        }
    }
}
