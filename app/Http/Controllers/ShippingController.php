<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingArea;
use App\Models\Courier;
use App\Services\RajaOngkirService;

class ShippingController extends Controller
{
    protected $rajaOngkirService;

    public function __construct(RajaOngkirService $rajaOngkirService)
    {
        $this->rajaOngkirService = $rajaOngkirService;
    }

    /**
     * Calculate shipping cost dengan validasi courier dinamis
     */
    public function calculateCost(Request $request)
    {
        try {
            // Get active couriers untuk validasi dinamis
            $activeCouriers = Courier::where('is_active', true)->pluck('code')->toArray();
            
            // Dynamic validation berdasarkan courier aktif di database
            $request->validate([
                'origin' => 'required|integer',
                'destination' => 'required|integer', 
                'weight' => 'required|integer|min:1',
                'courier' => 'required|string|in:' . implode(',', $activeCouriers)
            ]);

            $origin = $request->origin;
            $destination = $request->destination;
            $weight = $request->weight;
            $courier = $request->courier;

            // Check if we should use fallback (untuk debugging)
            $useFallback = config('services.rajaongkir.use_fallback', false);
            
            if ($useFallback) {
                return $this->getFallbackShippingData($origin, $destination, $weight, $courier);
            }

            // Validate cities exist in database
            $originCity = ShippingArea::where('rajaongkir_id', $origin)->first();
            $destinationCity = ShippingArea::where('rajaongkir_id', $destination)->first();

            if (!$originCity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kota asal tidak ditemukan'
                ], 422);
            }

            if (!$destinationCity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kota tujuan tidak ditemukan'
                ], 422);
            }

            // Check if courier exists in RajaOngkir (some couriers might not be supported)
            $supportedCouriers = ['jne', 'pos', 'tiki'];
            if (!in_array(strtolower($courier), $supportedCouriers)) {
                // Return fallback data untuk courier yang tidak supported
                return $this->getFallbackShippingData($origin, $destination, $weight, $courier);
            }

            // Call RajaOngkir service
            $result = $this->rajaOngkirService->calculateShippingCost(
                $origin,
                $destination, 
                $weight,
                $courier
            );

            if (!$result['success']) {
                // Jika API gagal, gunakan fallback data untuk debugging
                return $this->getFallbackShippingData($origin, $destination, $weight, $courier);
            }

            // Validate result data
            if (!isset($result['data']) || empty($result['data'])) {
                return $this->getFallbackShippingData($origin, $destination, $weight, $courier);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'debug' => [
                    'origin_city' => $originCity->city_name,
                    'destination_city' => $destinationCity->city_name,
                    'weight' => $weight,
                    'courier' => $courier,
                    'source' => 'rajaongkir_api',
                    'cached' => isset($result['execution_time']) && $result['execution_time'] < 0.1,
                    'execution_time' => round($result['execution_time'] ?? 0, 3) . 's'
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Get active couriers untuk error message yang lebih informatif
            $activeCouriers = Courier::where('is_active', true)->pluck('code')->toArray();
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors(),
                'available_couriers' => $activeCouriers,
                'note' => 'Pastikan kurir yang dipilih tersedia dalam daftar kurir aktif'
            ], 422);

        } catch (\Exception $e) {
            // Jika terjadi exception, gunakan fallback
            return $this->getFallbackShippingData(
                $request->origin ?? 155,
                $request->destination ?? 105,
                $request->weight ?? 1000,
                $request->courier ?? 'jne'
            );
        }
    }

    /**
     * Fallback dummy data untuk debugging ketika API bermasalah atau courier tidak supported  
     */
    private function getFallbackShippingData($origin, $destination, $weight, $courier)
    {
        // Base cost calculation (dummy formula)
        $baseDistance = 100; // km dummy
        $baseCost = 10000; // base cost
        $weightCost = ($weight / 1000) * 5000; // per kg
        $distanceCost = $baseDistance * 100; // per km
        
        $services = [];
        
        // Define services berdasarkan courier dengan struktur lengkap
        switch (strtolower($courier)) {
            case 'jne':
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => [['value' => intval($baseCost + $weightCost + $distanceCost), 'etd' => '2-3', 'note' => '']]
                    ],
                    [
                        'service' => 'OKE',
                        'description' => 'Ongkos Kirim Ekonomis',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 0.8), 'etd' => '3-4', 'note' => '']]
                    ],
                    [
                        'service' => 'YES',
                        'description' => 'Yakin Esok Sampai',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 1.5), 'etd' => '1-1', 'note' => '']]
                    ]
                ];
                break;
                
            case 'pos':
                $services = [
                    [
                        'service' => 'Paket Kilat Khusus',
                        'description' => 'Paket Kilat Khusus',
                        'cost' => [['value' => intval($baseCost + $weightCost + ($distanceCost * 0.9)), 'etd' => '2-4', 'note' => '']]
                    ],
                    [
                        'service' => 'Express Next Day',
                        'description' => 'Express Next Day',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 1.3), 'etd' => '1-1', 'note' => '']]
                    ]
                ];
                break;
                
            case 'tiki':
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'Regular Service',
                        'cost' => [['value' => intval($baseCost + $weightCost + ($distanceCost * 1.1)), 'etd' => '3-5', 'note' => '']]
                    ],
                    [
                        'service' => 'ECO',
                        'description' => 'Economy Service',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 0.7), 'etd' => '4-6', 'note' => '']]
                    ]
                ];
                break;
                
            // FIXED: Added proper support for couriers not in RajaOngkir API
            case 'jnt':
            case 'j&t':
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'J&T Express Regular',
                        'cost' => [['value' => intval($baseCost + $weightCost + ($distanceCost * 0.95)), 'etd' => '2-3', 'note' => 'Estimasi - API tidak mendukung kurir ini']]
                    ],
                    [
                        'service' => 'ECO',
                        'description' => 'J&T Economy',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 0.75), 'etd' => '3-5', 'note' => 'Estimasi - API tidak mendukung kurir ini']]
                    ]
                ];
                break;
            
            case 'sicepat':
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'SiCepat Reguler',
                        'cost' => [['value' => intval($baseCost + $weightCost + ($distanceCost * 0.9)), 'etd' => '1-2', 'note' => 'Estimasi - API tidak mendukung kurir ini']]
                    ],
                    [
                        'service' => 'BEST',
                        'description' => 'SiCepat Best',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 1.2), 'etd' => '1-1', 'note' => 'Estimasi - API tidak mendukung kurir ini']]
                    ]
                ];
                break;
                
            case 'ninja':
            case 'ninja express':
                $services = [
                    [
                        'service' => 'STD',
                        'description' => 'Ninja Standard',
                        'cost' => [['value' => intval($baseCost + $weightCost + ($distanceCost * 0.85)), 'etd' => '2-4', 'note' => 'Estimasi - API tidak mendukung kurir ini']]
                    ]
                ];
                break;
            
            case 'anteraja':
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'AnterAja Regular',
                        'cost' => [['value' => intval($baseCost + $weightCost + ($distanceCost * 0.88)), 'etd' => '2-3', 'note' => 'Estimasi - API tidak mendukung kurir ini']]
                    ]
                ];
                break;
                
            default:
                // Generic courier dengan struktur lengkap
                $services = [
                    [
                        'service' => 'REG',
                        'description' => strtoupper($courier) . ' Regular Service',
                        'cost' => [['value' => intval($baseCost + $weightCost + $distanceCost), 'etd' => '2-4', 'note' => 'Estimasi - Kurir tidak dikenal']]
                    ]
                ];
                break;
        }

        $fallbackData = [
            [
                'code' => strtolower($courier),
                'name' => strtoupper($courier),
                'costs' => $services
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $fallbackData,
            'debug' => [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'courier' => $courier,
                'source' => 'fallback_dummy_data',
                'cached' => false,
                'message' => 'Data estimasi - Kurir ini tidak didukung API RajaOngkir atau API bermasalah',
                'note' => 'Untuk data akurat, gunakan kurir JNE/POS/TIKI atau upgrade ke RajaOngkir Pro',
                'api_supported_couriers' => ['jne', 'pos', 'tiki'],
                'estimated_couriers' => ['jnt', 'j&t', 'sicepat', 'ninja', 'anteraja']
            ]
        ]);
    }

    public function getCitiesByProvince($provinceId)
    {
        try {
            if (!is_numeric($provinceId) || $provinceId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid province ID'
                ], 400);
            }

            $cities = $this->rajaOngkirService->getCachedCities($provinceId);
            
            if ($cities->isEmpty()) {
                $cities = ShippingArea::where('province_id', $provinceId)
                    ->orderBy('city_name')
                    ->get();
            }

            $citiesData = $cities->map(function($city) {
                return [
                    'rajaongkir_id' => $city->rajaongkir_id,
                    'city_name' => $city->city_name,
                    'province_name' => $city->province_name,
                    'type' => $city->type ?? 'Kota',
                    'full_name' => $city->city_name . ', ' . $city->province_name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $citiesData,
                'count' => $citiesData->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kota: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        \Log::info('Loading shipping management page');
        
        $provinces = ShippingArea::provinces()->get();
        $totalCities = ShippingArea::count();
        $couriers = Courier::get();
        $apiType = config('services.rajaongkir.type', 'starter');
        
        // Check API configuration
        $apiKey = config('services.rajaongkir.api_key');
        $apiConfigured = !empty($apiKey);
        
        // Check if shipping data is available
        $needsSync = $totalCities == 0;
        $isDataLimited = $totalCities < 100; // Assume full data should have more than 100 cities
        
        \Log::info('Shipping page data', [
            'provinces_count' => $provinces->count(),
            'total_cities' => $totalCities,
            'couriers_count' => $couriers->count(),
            'api_configured' => $apiConfigured,
            'api_key_length' => $apiConfigured ? strlen($apiKey) : 0,
            'api_type' => $apiType,
            'needs_sync' => $needsSync,
            'is_data_limited' => $isDataLimited
        ]);
        
        return view('admin.shipping.index', compact(
            'provinces', 
            'totalCities', 
            'couriers', 
            'apiType',
            'needsSync',
            'isDataLimited',
            'apiConfigured'
        ));
    }

    public function syncAreas(Request $request)
    {
        try {
            \Log::info('=== Sync Areas Request Started ===', [
                'user_id' => auth()->id(),
                'is_ajax' => $request->expectsJson()
            ]);
            
            // Validate API configuration
            $apiKey = config('services.rajaongkir.api_key');
            if (empty($apiKey)) {
                $errorMsg = 'API key RajaOngkir tidak dikonfigurasi. Periksa RAJAONGKIR_API_KEY di file .env';
                \Log::error('Sync failed: No API key', ['message' => $errorMsg]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg,
                        'debug' => [
                            'api_key_configured' => false,
                            'config_check' => 'RAJAONGKIR_API_KEY harus diisi di file .env'
                        ]
                    ], 400);
                }
                
                return redirect()->route('admin.shipping.index')
                    ->with('error', $errorMsg);
            }
            
            \Log::info('Starting RajaOngkir sync process', [
                'api_key_length' => strlen($apiKey),
                'api_type' => config('services.rajaongkir.type', 'starter')
            ]);
            
            $result = $this->rajaOngkirService->syncProvinces();
            
            \Log::info('Sync process completed', [
                'success' => $result['success'],
                'message' => $result['message'],
                'synced' => $result['synced'] ?? 0,
                'errors' => $result['errors'] ?? 0
            ]);
            
            if ($request->expectsJson()) {
                // Return detailed JSON response for AJAX requests
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'] ?? 'Sync completed',
                    'data' => [
                        'synced' => $result['synced'] ?? 0,
                        'errors' => $result['errors'] ?? 0,
                        'provinces_processed' => $result['provinces_processed'] ?? 0
                    ],
                    'debug' => $result['debug'] ?? null
                ], $result['success'] ? 200 : 500);
            }
            
            // Fallback for form submissions
            if ($result['success']) {
                return redirect()->route('admin.shipping.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->route('admin.shipping.index')
                    ->with('error', $result['message']);
            }
            
        } catch (\Exception $e) {
            \Log::error('Critical exception in syncAreas', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMsg = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'debug' => [
                        'exception' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ], 500);
            }
            
            return redirect()->route('admin.shipping.index')
                ->with('error', $errorMsg);
        }
    }

    public function getMultipleCosts(Request $request)
    {
        $activeCouriers = Courier::where('is_active', true)->pluck('code')->toArray();
        
        $request->validate([
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'couriers' => 'array'
        ]);

        $couriers = $request->couriers ?? $activeCouriers;
        
        $result = $this->rajaOngkirService->getMultipleCourierCosts(
            $request->origin,
            $request->destination,
            $request->weight,
            $couriers
        );

        return response()->json($result);
    }

    public function updateCourierStatus(Request $request, $id)
    {
        try {
            $courier = Courier::findOrFail($id);
            $courier->update(['is_active' => $request->boolean('is_active')]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status kurir berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update status kurir'
            ], 500);
        }
    }

    /**
     * Delete courier
     */
    public function destroyCourier($id)
    {
        try {
            $courier = Courier::findOrFail($id);
            $courierName = $courier->name;
            
            $courier->delete();

            return response()->json([
                'success' => true,
                'message' => "Kurir '{$courierName}' berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kurir: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProvinces()
    {
        try {
            $provinces = $this->rajaOngkirService->getCachedProvinces();
            
            if ($provinces->isEmpty()) {
                $provinces = ShippingArea::provinces()->get();
            }

            return response()->json([
                'success' => true,
                'data' => $provinces,
                'count' => $provinces->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data provinsi'
            ], 500);
        }
    }
}
