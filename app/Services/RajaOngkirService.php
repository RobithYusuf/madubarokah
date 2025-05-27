<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\ShippingArea;

class RajaOngkirService
{
    private $apiKey;
    private $baseUrl;
    private $type;
    private $isKomerce;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
        $this->type = config('services.rajaongkir.type', 'starter');
        
        // Determine if this is Komerce API or Original RajaOngkir
        $this->isKomerce = in_array($this->type, ['komerce']);
        
        // Set base URL based on type
        if ($this->isKomerce) {
            $this->baseUrl = 'https://rajaongkir.komerce.id/api/v1';
        } elseif ($this->type === 'pro') {
            $this->baseUrl = 'https://pro.rajaongkir.com/api';
        } else {
            // starter, basic - use original RajaOngkir
            $this->baseUrl = 'https://api.rajaongkir.com/starter';
        }
    }

    /**
     * Calculate shipping cost - hybrid untuk Original dan Komerce API
     */
    public function calculateShippingCost($origin, $destination, $weight, $courier)
    {
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'message' => 'API key RajaOngkir tidak dikonfigurasi'
                ];
            }

            if ($this->isKomerce) {
                return $this->calculateKomerceShipping($origin, $destination, $weight, $courier);
            } else {
                return $this->calculateOriginalShipping($origin, $destination, $weight, $courier);
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }


    /**
     * Calculate shipping untuk Komerce API
     */
    private function calculateKomerceShipping($origin, $destination, $weight, $courier)
    {
        $requestData = [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier
        ];

        $response = Http::timeout(5)
            ->withHeaders([
                'key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
            ->post($this->baseUrl . '/cost', $requestData);

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Komerce API failed: HTTP ' . $response->status()
            ];
        }

        $data = $response->json();
        
        if (isset($data['data']['costs']) && !empty($data['data']['costs'])) {
            // Transform Komerce format to standard format
            $processedResults = [
                [
                    'code' => strtolower($courier),
                    'name' => strtoupper($courier),
                    'costs' => []
                ]
            ];

            foreach ($data['data']['costs'] as $cost) {
                $processedResults[0]['costs'][] = [
                    'service' => $cost['service'] ?? 'REG',
                    'description' => $cost['description'] ?? 'Regular Service',
                    'cost' => [
                        [
                            'value' => $cost['cost'] ?? 0,
                            'etd' => $cost['etd'] ?? '1-2'
                        ]
                    ]
                ];
            }

            return [
                'success' => true,
                'data' => $processedResults
            ];
        }

        return [
            'success' => false,
            'message' => 'No shipping costs found in Komerce API response'
        ];
    }

    /**
     * Calculate shipping untuk RajaOngkir Original API
     */
    private function calculateOriginalShipping($origin, $destination, $weight, $courier)
    {
        $response = Http::timeout(5)
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

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Original RajaOngkir API failed: HTTP ' . $response->status()
            ];
        }

        $data = $response->json();

        // Validate original RajaOngkir response structure
        if (!isset($data['rajaongkir'])) {
            return [
                'success' => false,
                'message' => 'Invalid response structure from RajaOngkir'
            ];
        }

        $rajaongkir = $data['rajaongkir'];

        // Check for API errors
        if (isset($rajaongkir['status']['code']) && $rajaongkir['status']['code'] !== 200) {
            return [
                'success' => false,
                'message' => 'RajaOngkir Error: ' . ($rajaongkir['status']['description'] ?? 'Unknown error')
            ];
        }

        // Check results
        if (!isset($rajaongkir['results']) || empty($rajaongkir['results'])) {
            return [
                'success' => false,
                'message' => 'Tidak ada layanan pengiriman tersedia untuk rute ini'
            ];
        }

        // Process and validate results
        $processedResults = [];
        foreach ($rajaongkir['results'] as $result) {
            if (!isset($result['costs']) || empty($result['costs'])) {
                continue;
            }

            $validCosts = [];
            foreach ($result['costs'] as $cost) {
                if (isset($cost['cost']) && !empty($cost['cost'])) {
                    $validCostDetails = [];
                    foreach ($cost['cost'] as $detail) {
                        if (isset($detail['value']) && $detail['value'] > 0) {
                            $validCostDetails[] = $detail;
                        }
                    }
                    
                    if (!empty($validCostDetails)) {
                        $cost['cost'] = $validCostDetails;
                        $validCosts[] = $cost;
                    }
                }
            }

            if (!empty($validCosts)) {
                $result['costs'] = $validCosts;
                $processedResults[] = $result;
            }
        }

        if (empty($processedResults)) {
            return [
                'success' => false,
                'message' => 'Tidak ada layanan pengiriman dengan tarif valid'
            ];
        }

        return [
            'success' => true,
            'data' => $processedResults
        ];
    }

    /**
     * Get provinces - hybrid
     */
    public function getProvinces()
    {
        try {
            if ($this->isKomerce) {
                $url = $this->baseUrl . '/destination/provinces';
                $headers = ['key' => $this->apiKey, 'Accept' => 'application/json'];
            } else {
                $url = $this->baseUrl . '/province';
                $headers = ['key' => $this->apiKey];
            }

            $response = Http::withHeaders($headers)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($this->isKomerce && isset($data['data'])) {
                    return ['success' => true, 'data' => $data['data']];
                } elseif (!$this->isKomerce && isset($data['rajaongkir']['results'])) {
                    return ['success' => true, 'data' => $data['rajaongkir']['results']];
                }
            }

            return ['success' => false, 'message' => 'Failed to fetch provinces'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'API Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get cities - hybrid
     */
    public function getCities($provinceId = null)
    {
        try {
            if ($this->isKomerce) {
                $url = $this->baseUrl . '/destination/cities';
                $headers = ['key' => $this->apiKey, 'Accept' => 'application/json'];
                $params = $provinceId ? ['province' => $provinceId] : [];
            } else {
                $url = $this->baseUrl . '/city';
                $headers = ['key' => $this->apiKey];
                $params = $provinceId ? ['province' => $provinceId] : [];
            }

            $response = Http::withHeaders($headers)->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($this->isKomerce && isset($data['data'])) {
                    return ['success' => true, 'data' => $data['data']];
                } elseif (!$this->isKomerce && isset($data['rajaongkir']['results'])) {
                    return ['success' => true, 'data' => $data['rajaongkir']['results']];
                }
            }

            return ['success' => false, 'message' => 'Failed to fetch cities'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'API Error: ' . $e->getMessage()];
        }
    }

    /**
     * Sync provinces dengan optimized process dan time limit handling
     */
    public function syncProvinces()
    {
        // Increase time limit for this process
        set_time_limit(300); // 5 minutes
        ini_set('max_execution_time', 300);
        
        try {
            $result = $this->getProvinces();
            
            if (!$result['success']) {
                return $result;
            }

            $synced = 0;
            $errors = 0;
            $provinces = $result['data'];
            $totalProvinces = count($provinces);
            
            \Log::info("Starting province sync - Total provinces: {$totalProvinces}");

            foreach ($provinces as $index => $province) {
                $provinceId = $province['province_id'] ?? $province['id'] ?? null;
                $provinceName = $province['province_name'] ?? $province['name'] ?? null;
                
                if (!$provinceId || !$provinceName) {
                    $errors++;
                    continue;
                }
                
                \Log::info("Processing province {$index}/{$totalProvinces}: {$provinceName}");
                
                try {
                    // Get cities for this province with retry mechanism
                    $citiesResult = $this->getCitiesWithRetry($provinceId, 3);
                    
                    if ($citiesResult['success'] && !empty($citiesResult['data'])) {
                        $citiesCount = 0;
                        
                        foreach ($citiesResult['data'] as $city) {
                            $cityId = $city['city_id'] ?? $city['id'] ?? null;
                            $cityName = $city['city_name'] ?? $city['name'] ?? null;
                            
                            if ($cityId && $cityName) {
                                ShippingArea::updateOrCreate(
                                    ['rajaongkir_id' => $cityId],
                                    [
                                        'province_id' => $provinceId,
                                        'province_name' => $provinceName,
                                        'city_name' => $cityName,
                                        'type' => $city['type'] ?? 'Kota',
                                        'postal_code' => $city['postal_code'] ?? null
                                    ]
                                );
                                $citiesCount++;
                                $synced++;
                            }
                        }
                        
                        \Log::info("Province {$provinceName}: {$citiesCount} cities synced");
                    } else {
                        $errors++;
                        \Log::warning("Failed to get cities for province: {$provinceName}");
                    }
                    
                    // Small delay to prevent API rate limiting
                    usleep(100000); // 0.1 second
                    
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error("Error processing province {$provinceName}: " . $e->getMessage());
                    continue;
                }
            }

            \Log::info("Sync completed - Synced: {$synced}, Errors: {$errors}");
            
            if ($synced > 0) {
                return [
                    'success' => true,
                    'message' => "Berhasil sinkronisasi {$synced} kota/kabupaten" . ($errors > 0 ? " (dengan {$errors} error)" : ""),
                    'synced' => $synced,
                    'errors' => $errors
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data yang berhasil disinkronkan',
                    'synced' => 0,
                    'errors' => $errors
                ];
            }
            
        } catch (\Exception $e) {
            \Log::error('Sync provinces error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error during sync: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get cities with retry mechanism
     */
    private function getCitiesWithRetry($provinceId, $maxRetries = 3)
    {
        $attempt = 1;
        
        while ($attempt <= $maxRetries) {
            try {
                $result = $this->getCities($provinceId);
                
                if ($result['success']) {
                    return $result;
                }
                
                \Log::warning("Attempt {$attempt} failed for province {$provinceId}: " . ($result['message'] ?? 'Unknown error'));
                
            } catch (\Exception $e) {
                \Log::warning("Attempt {$attempt} exception for province {$provinceId}: " . $e->getMessage());
            }
            
            $attempt++;
            
            if ($attempt <= $maxRetries) {
                // Wait before retry (exponential backoff)
                sleep(pow(2, $attempt - 1)); // 2, 4, 8 seconds
            }
        }
        
        return [
            'success' => false,
            'message' => "Failed after {$maxRetries} attempts"
        ];
    }

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
        
        foreach ($couriers as $courier) {
            $result = $this->calculateShippingCost($origin, $destination, $weight, $courier);
            
            if ($result['success']) {
                $results = array_merge($results, $result['data']);
            }
        }

        return [
            'success' => !empty($results),
            'data' => $results
        ];
    }
}
