<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestRajaOngkirAPI extends Command
{
    protected $signature = 'test:rajaongkir';
    protected $description = 'Test direct API call to RajaOngkir';

    public function handle()
    {
        $this->info('🔍 Testing RajaOngkir API Direct...');
        $this->newLine();

        // Get config
        $apiKey = config('services.rajaongkir.api_key');
        $apiType = config('services.rajaongkir.type', 'starter');
        
        $this->line("API Key: " . ($apiKey ? "✅ Present (" . strlen($apiKey) . " chars)" : "❌ Missing"));
        $this->line("API Type: {$apiType}");
        
        if (empty($apiKey)) {
            $this->error('❌ API Key tidak ditemukan di .env!');
            $this->line('Tambahkan: RAJAONGKIR_API_KEY=your_key_here');
            return;
        }

        // Test direct API call
        $this->info('📡 Testing API call...');
        
        try {
            $url = 'https://api.rajaongkir.com/starter/province';
            
            $this->line("URL: {$url}");
            $this->line("Headers: key={$apiKey}");
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'key' => $apiKey
                ])
                ->get($url);

            $this->line("Status: " . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                
                $this->info('✅ API Response Successful!');
                $this->line("Response structure: " . json_encode(array_keys($data), JSON_PRETTY_PRINT));
                
                if (isset($data['rajaongkir']['results'])) {
                    $provinces = $data['rajaongkir']['results'];
                    $this->info("🎉 Found " . count($provinces) . " provinces!");
                    
                    // Show first 3 provinces
                    $this->line("Sample provinces:");
                    foreach (array_slice($provinces, 0, 3) as $province) {
                    $this->line("  - ID: {$province['province_id']}, Name: {$province['province']}");
                    }
                    
                    // Test cities for first province
                    $this->info('📍 Testing cities API...');
                    $firstProvince = $provinces[0];
                    
                    $citiesResponse = Http::timeout(10)
                        ->withHeaders(['key' => $apiKey])
                        ->get('https://api.rajaongkir.com/starter/city', [
                            'province' => $firstProvince['province_id']
                        ]);
                    
                    if ($citiesResponse->successful()) {
                        $citiesData = $citiesResponse->json();
                        if (isset($citiesData['rajaongkir']['results'])) {
                            $cities = $citiesData['rajaongkir']['results'];
                            $this->info("✅ Cities API works! Found " . count($cities) . " cities in {$firstProvince['province']}");
                            
                            // Show sample cities
                            foreach (array_slice($cities, 0, 2) as $city) {
                                $this->line("  - {$city['city_name']} ({$city['type']})");
                            }
                        }
                    } else {
                        $this->error("❌ Cities API failed: " . $citiesResponse->status());
                        $this->line($citiesResponse->body());
                    }
                    
                } else {
                    $this->error('❌ Invalid response structure');
                    $this->line("Full response: " . $response->body());
                }
                
            } else {
                $this->error('❌ API call failed!');
                $this->line("Status: " . $response->status());
                $this->line("Body: " . $response->body());
                
                if ($response->status() == 401) {
                    $this->error('🔑 API Key tidak valid atau expired!');
                    $this->line('Solusi:');
                    $this->line('1. Login ke https://rajaongkir.com');
                    $this->line('2. Generate API key baru');
                    $this->line('3. Update RAJAONGKIR_API_KEY di .env');
                }
            }
            
        } catch (\Exception $e) {
            $this->error('💥 Exception: ' . $e->getMessage());
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
        }
        
        $this->newLine();
    }
}
