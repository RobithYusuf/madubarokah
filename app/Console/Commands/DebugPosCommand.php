<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RajaOngkirService;
use App\Models\Courier;
use App\Models\ShippingArea;

class DebugPosCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'debug:pos 
                           {--origin=155 : Origin city ID (default: Kudus)}
                           {--destination=23 : Destination city ID (default: Jakarta Pusat)}
                           {--weight=1000 : Package weight in grams}
                           {--clear-cache : Clear POS performance cache}';

    /**
     * The console command description.
     */
    protected $description = 'Debug POS Indonesia courier issues with RajaOngkir API';

    protected $rajaOngkirService;

    public function __construct(RajaOngkirService $rajaOngkirService)
    {
        parent::__construct();
        $this->rajaOngkirService = $rajaOngkirService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Debug POS Indonesia - RajaOngkir API');
        $this->newLine();

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            $this->clearPosCache();
        }

        // Get options
        $origin = $this->option('origin');
        $destination = $this->option('destination');
        $weight = $this->option('weight');

        // Show test configuration
        $this->showTestConfiguration($origin, $destination, $weight);

        // Test 1: Check API Configuration
        $this->info('📋 Test 1: API Configuration');
        $this->checkApiConfiguration();

        // Test 2: Check Database Courier
        $this->info('📋 Test 2: Database Courier Configuration');
        $this->checkDatabaseCourier();

        // Test 3: Check Shipping Areas
        $this->info('📋 Test 3: Shipping Areas Check');
        $this->checkShippingAreas($origin, $destination);

        // Test 4: Test API Connectivity
        $this->info('📋 Test 4: API Connectivity Test');
        $this->testApiConnectivity();

        // Test 5: Test POS Specific Call
        $this->info('📋 Test 5: POS Specific API Call');
        $this->testPosSpecificCall($origin, $destination, $weight);

        // Test 6: Performance Stats
        $this->info('📋 Test 6: Courier Performance Stats');
        $this->showPerformanceStats();

        $this->newLine();
        $this->info('✅ Debug selesai! Periksa log Laravel untuk detail lebih lanjut.');
    }

    private function clearPosCache()
    {
        $result = $this->rajaOngkirService->clearPerformanceCache();
        
        if ($result['success']) {
            $this->info('✅ Cache POS berhasil dibersihkan');
        } else {
            $this->error('❌ Gagal membersihkan cache POS');
        }
        $this->newLine();
    }

    private function showTestConfiguration($origin, $destination, $weight)
    {
        $originCity = ShippingArea::where('rajaongkir_id', $origin)->first();
        $destinationCity = ShippingArea::where('rajaongkir_id', $destination)->first();

        $this->table(['Parameter', 'Value'], [
            ['Origin ID', $origin],
            ['Origin City', $originCity->city_name ?? 'Unknown'],
            ['Destination ID', $destination],
            ['Destination City', $destinationCity->city_name ?? 'Unknown'],
            ['Weight', $weight . ' gram'],
            ['Courier', 'POS Indonesia']
        ]);
        $this->newLine();
    }

    private function checkApiConfiguration()
    {
        $apiKey = config('services.rajaongkir.api_key');
        $apiType = config('services.rajaongkir.type');
        
        if (empty($apiKey)) {
            $this->error('❌ API Key tidak dikonfigurasi');
            $this->warn('💡 Set RAJAONGKIR_API_KEY di file .env');
        } else {
            $this->info("✅ API Key: " . substr($apiKey, 0, 10) . "..." . substr($apiKey, -5));
        }

        $this->info("✅ API Type: " . ($apiType ?? 'starter'));
        $this->newLine();
    }

    private function checkDatabaseCourier()
    {
        $posCourier = Courier::where('code', 'pos')->first();
        
        if (!$posCourier) {
            $this->error('❌ POS courier tidak ditemukan di database');
            return;
        }

        // Handle services field - bisa berupa array atau JSON string
        $services = $posCourier->services;
        
        // Jika services berupa string JSON, decode dulu
        if (is_string($services)) {
            $services = json_decode($services, true);
        }
        
        // Jika services masih null atau bukan array, set sebagai array kosong
        if (!is_array($services)) {
            $services = [];
        }

        $this->table(['Field', 'Value'], [
            ['Code', $posCourier->code],
            ['Name', $posCourier->name],
            ['Is Active', $posCourier->is_active ? '✅ Aktif' : '❌ Nonaktif'],
            ['Services Count', count($services)],
            ['Services Type', is_string($posCourier->services) ? 'JSON String' : 'Array'],
        ]);

        if (!empty($services)) {
            $this->info('📦 Available Services:');
            foreach ($services as $code => $name) {
                $this->line("   • {$code}: {$name}");
            }
        } else {
            $this->warn('⚠️  Tidak ada services yang dikonfigurasi');
        }

        if (!$posCourier->is_active) {
            $this->warn('⚠️  POS courier tidak aktif - aktifkan terlebih dahulu');
        }

        $this->newLine();
    }

    private function checkShippingAreas($origin, $destination)
    {
        $originArea = ShippingArea::where('rajaongkir_id', $origin)->first();
        $destinationArea = ShippingArea::where('rajaongkir_id', $destination)->first();

        $this->table(['Area', 'Status', 'Details'], [
            [
                'Origin', 
                $originArea ? '✅ Found' : '❌ Not Found',
                $originArea ? "{$originArea->city_name}, {$originArea->province_name}" : 'N/A'
            ],
            [
                'Destination', 
                $destinationArea ? '✅ Found' : '❌ Not Found',
                $destinationArea ? "{$destinationArea->city_name}, {$destinationArea->province_name}" : 'N/A'
            ]
        ]);

        if (!$originArea || !$destinationArea) {
            $this->warn('⚠️  Jalankan sinkronisasi data wilayah terlebih dahulu');
        }

        $this->newLine();
    }

    private function testApiConnectivity()
    {
        $connectivity = $this->rajaOngkirService->testApiConnectivity();
        
        if ($connectivity['success']) {
            $this->info("✅ API connectivity OK ({$connectivity['response_time']})");
        } else {
            $this->error("❌ API connectivity failed: {$connectivity['message']}");
        }
        $this->newLine();
    }

    private function testPosSpecificCall($origin, $destination, $weight)
    {
        $this->info('🔄 Calling RajaOngkir API for POS...');
        
        $result = $this->rajaOngkirService->calculateShippingCost(
            $origin,
            $destination,
            $weight,
            'pos'
        );

        if ($result['success']) {
            $this->info('✅ POS API call berhasil!');
            
            if (isset($result['data']) && !empty($result['data'])) {
                $costs = $result['data'][0]['costs'] ?? [];
                $this->info("📦 Ditemukan " . count($costs) . " layanan POS:");
                
                foreach ($costs as $service) {
                    $serviceName = $service['service'];
                    $description = $service['description'];
                    $cost = $service['cost'][0]['value'] ?? 0;
                    $etd = $service['cost'][0]['etd'] ?? 'N/A';
                    
                    $this->line("   • {$serviceName} ({$description}): Rp " . number_format($cost) . " - {$etd} hari");
                }
            } else {
                $this->warn('⚠️  API berhasil tapi tidak ada data layanan');
            }
            
            if (isset($result['execution_time'])) {
                $this->info("⏱️  Execution time: " . round($result['execution_time'], 2) . "s");
            }
        } else {
            $this->error("❌ POS API call gagal: {$result['message']}");
            
            if (isset($result['fallback_suggested'])) {
                $this->warn('💡 Menggunakan fallback data');
            }
        }
        $this->newLine();
    }

    private function showPerformanceStats()
    {
        $stats = $this->rajaOngkirService->getCourierPerformanceStats();
        
        if (isset($stats['pos'])) {
            $posStats = $stats['pos'];
            
            $this->table(['Metric', 'Value'], [
                ['Available', $posStats['available'] ? '✅ Yes' : '❌ No'],
                ['Status', $posStats['status']],
                ['Avg Response Time', $posStats['avg_response_time']],
                ['Success Rate', $posStats['success_rate']],
                ['Total Requests', $posStats['total_requests']],
                ['Recent Timeouts', $posStats['recent_timeouts']],
                ['Recommended Timeout', $posStats['recommended_timeout']]
            ]);
        } else {
            $this->warn('⚠️  Tidak ada data performance untuk POS');
        }
    }
}