<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\ShippingArea;

class RajaOngkirService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
        $this->baseUrl = 'https://api.rajaongkir.com/starter';
    }

    /**
     * Calculate shipping cost
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

            $response = Http::timeout(10)
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
                    'message' => 'RajaOngkir API error: HTTP ' . $response->status()
                ];
            }

            $data = $response->json();

            if (!isset($data['rajaongkir'])) {
                return ['success' => false, 'message' => 'Invalid response structure'];
            }

            $rajaongkir = $data['rajaongkir'];

            if (isset($rajaongkir['status']['code']) && $rajaongkir['status']['code'] !== 200) {
                return [
                    'success' => false,
                    'message' => 'RajaOngkir Error: ' . ($rajaongkir['status']['description'] ?? 'Unknown error')
                ];
            }

            if (!isset($rajaongkir['results']) || empty($rajaongkir['results'])) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada layanan pengiriman tersedia untuk rute ini'
                ];
            }

            return [
                'success' => true,
                'data' => $rajaongkir['results']
            ];
            
        } catch (\Exception $e) {
            \Log::error('RajaOngkir shipping cost error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get provinces from RajaOngkir API
     */
    public function getProvinces()
    {
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false, 
                    'message' => 'API key RajaOngkir tidak dikonfigurasi'
                ];
            }

            $response = Http::timeout(15)
                ->withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/province');

            if (!$response->successful()) {
                $errorMsg = 'HTTP Error ' . $response->status();
                if ($response->status() == 401) {
                    $errorMsg .= ' - API key tidak valid';
                } elseif ($response->status() == 403) {
                    $errorMsg .= ' - Akses ditolak';
                }
                
                return [
                    'success' => false, 
                    'message' => $errorMsg
                ];
            }

            $data = $response->json();
            
            if (!isset($data['rajaongkir'])) {
                return [
                    'success' => false, 
                    'message' => 'Format response API tidak valid'
                ];
            }

            $rajaongkir = $data['rajaongkir'];
            
            if (isset($rajaongkir['status']['code']) && $rajaongkir['status']['code'] !== 200) {
                return [
                    'success' => false,
                    'message' => 'RajaOngkir API Error: ' . ($rajaongkir['status']['description'] ?? 'Unknown error')
                ];
            }
            
            if (!isset($rajaongkir['results']) || empty($rajaongkir['results'])) {
                return [
                    'success' => false, 
                    'message' => 'Tidak ada data provinsi dari API'
                ];
            }
            
            return ['success' => true, 'data' => $rajaongkir['results']];

        } catch (\Exception $e) {
            \Log::error('RajaOngkir provinces error: ' . $e->getMessage());
            
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
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false, 
                    'message' => 'API key RajaOngkir tidak dikonfigurasi'
                ];
            }

            $params = $provinceId ? ['province' => $provinceId] : [];
            
            $response = Http::timeout(20)
                ->withHeaders(['key' => $this->apiKey])
                ->get($this->baseUrl . '/city', $params);

            if (!$response->successful()) {
                return [
                    'success' => false, 
                    'message' => 'HTTP Error ' . $response->status() . ' untuk provinsi ' . $provinceId
                ];
            }

            $data = $response->json();
            
            if (!isset($data['rajaongkir'])) {
                return [
                    'success' => false, 
                    'message' => 'Format response city tidak valid'
                ];
            }

            $rajaongkir = $data['rajaongkir'];
            
            if (isset($rajaongkir['status']['code']) && $rajaongkir['status']['code'] !== 200) {
                return [
                    'success' => false,
                    'message' => 'RajaOngkir City API Error: ' . ($rajaongkir['status']['description'] ?? 'Unknown error')
                ];
            }
            
            if (!isset($rajaongkir['results']) || empty($rajaongkir['results'])) {
                return [
                    'success' => false, 
                    'message' => 'Tidak ada data kota untuk provinsi ' . $provinceId
                ];
            }
            
            return ['success' => true, 'data' => $rajaongkir['results']];

        } catch (\Exception $e) {
            \Log::error('RajaOngkir cities error: ' . $e->getMessage());
            
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
