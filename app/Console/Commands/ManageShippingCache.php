<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RajaOngkirService;

class ManageShippingCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping:cache
                            {action : The action to perform (status|clear|test)}
                            {--origin= : Origin city ID for clear action}
                            {--destination= : Destination city ID for clear action}
                            {--courier= : Courier code for clear action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage shipping cost cache';

    protected $rajaOngkirService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(RajaOngkirService $rajaOngkirService)
    {
        parent::__construct();
        $this->rajaOngkirService = $rajaOngkirService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'status':
                $this->showCacheStatus();
                break;
            
            case 'clear':
                $this->clearCache();
                break;
            
            case 'test':
                $this->testCache();
                break;
            
            default:
                $this->error("Unknown action: {$action}. Valid actions are: status, clear, test");
                return 1;
        }

        return 0;
    }

    /**
     * Show cache status
     */
    private function showCacheStatus()
    {
        $this->info('Checking shipping cache status...');
        
        $stats = $this->rajaOngkirService->getShippingCacheStats();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Cache Keys', $stats['total_keys']],
                ['Active Cache Entries', $stats['active_cache']],
                ['Expired Cache Entries', $stats['expired_cache']],
                ['Estimated Cache Size', $stats['estimated_size']],
                ['Cache Duration', $stats['cache_duration']],
                ['Next Cleanup', $stats['next_cleanup']],
            ]
        );
        
        if (!empty($stats['couriers_cached'])) {
            $this->info("\nCache entries by courier:");
            foreach ($stats['couriers_cached'] as $courier => $count) {
                $this->line("  - {$courier}: {$count} entries");
            }
        }
        
        // Also show performance stats
        $this->info("\nCourier Performance Stats:");
        $perfStats = $this->rajaOngkirService->getCourierPerformanceStats();
        
        $perfData = [];
        foreach ($perfStats as $courier => $stats) {
            $perfData[] = [
                strtoupper($courier),
                $stats['available'] ? 'Yes' : 'No',
                $stats['avg_response_time'],
                $stats['success_rate'],
                $stats['total_requests'],
                $stats['status']
            ];
        }
        
        $this->table(
            ['Courier', 'Available', 'Avg Response', 'Success Rate', 'Total Requests', 'Status'],
            $perfData
        );
    }

    /**
     * Clear cache
     */
    private function clearCache()
    {
        $origin = $this->option('origin');
        $destination = $this->option('destination');
        $courier = $this->option('courier');
        
        if ($origin || $destination || $courier) {
            if (!$origin || !$destination || !$courier) {
                $this->error('For specific cache clearing, all three options (origin, destination, courier) must be provided.');
                return;
            }
            
            $this->info("Clearing cache for route: {$origin} -> {$destination} via {$courier}");
        } else {
            $this->info('Clearing all shipping cost cache...');
        }
        
        $result = $this->rajaOngkirService->clearShippingCostCache($origin, $destination, $courier);
        
        if ($result['success']) {
            $this->info($result['message']);
        } else {
            $this->error('Failed to clear cache');
        }
    }

    /**
     * Test cache functionality
     */
    private function testCache()
    {
        $this->info('Testing shipping cache functionality...');
        
        // Test parameters
        $origin = 155; // Jakarta
        $destination = 105; // Bekasi
        $weight = 1000; // 1kg
        $courier = 'jne';
        
        $this->info("Test route: Jakarta (155) -> Bekasi (105), 1kg, JNE");
        
        // First call - should hit API
        $this->info("\n1. First call (should hit API):");
        $start = microtime(true);
        $result1 = $this->rajaOngkirService->calculateShippingCost($origin, $destination, $weight, $courier);
        $time1 = microtime(true) - $start;
        
        if ($result1['success']) {
            $this->info("   ✓ Success - Time: " . round($time1, 3) . "s");
            $this->info("   Services found: " . count($result1['data'][0]['costs'] ?? []));
        } else {
            $this->error("   ✗ Failed: " . $result1['message']);
        }
        
        // Second call - should hit cache
        $this->info("\n2. Second call (should hit cache):");
        $start = microtime(true);
        $result2 = $this->rajaOngkirService->calculateShippingCost($origin, $destination, $weight, $courier);
        $time2 = microtime(true) - $start;
        
        if ($result2['success']) {
            $this->info("   ✓ Success - Time: " . round($time2, 3) . "s");
            
            $speedup = round($time1 / $time2, 1);
            $this->info("   Cache speedup: {$speedup}x faster");
            
            if ($time2 < 0.1) {
                $this->info("   ✓ Cache is working correctly!");
            } else {
                $this->warn("   ⚠ Response time higher than expected for cache");
            }
        } else {
            $this->error("   ✗ Failed: " . $result2['message']);
        }
        
        // Test API connectivity
        $this->info("\n3. Testing API connectivity:");
        $apiTest = $this->rajaOngkirService->testApiConnectivity();
        
        if ($apiTest['success']) {
            $this->info("   ✓ API connection successful");
            $this->info("   Response time: " . $apiTest['response_time']);
        } else {
            $this->error("   ✗ API connection failed: " . $apiTest['message']);
        }
    }
}