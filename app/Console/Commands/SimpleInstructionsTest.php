<?php

namespace App\Console\Commands;

use App\Services\TripayService;
use App\Models\PaymentChannel;
use Illuminate\Console\Command;

class SimpleInstructionsTest extends Command
{
    protected $signature = 'tripay:simple-test';
    protected $description = 'Simple test for instructions issue';

    public function handle()
    {
        $this->info('Simple Instructions Test');
        $this->line('========================');
        
        try {
            // Test 1: API Connection
            $this->line('1. Testing API...');
            $service = app(TripayService::class);
            $channels = $service->getPaymentChannels();
            
            if (empty($channels)) {
                $this->error('No channels from API');
                return;
            }
            
            $channelCount = count($channels);
            $this->info("API OK - Total channels: " . $channelCount);
            
            // Test 2: Check instructions in API data
            $instructionsCount = 0;
            $this->line('2. Checking instructions in API data...');
            
            foreach ($channels as $channel) {
                $code = $channel['code'] ?? 'unknown';
                $name = $channel['name'] ?? 'unknown';
                
                if (isset($channel['instructions']) && !empty($channel['instructions'])) {
                    $instructionsCount++;
                    $type = gettype($channel['instructions']);
                    $this->line("   - {$code}: {$type}");
                    
                    if (is_array($channel['instructions'])) {
                        $itemCount = count($channel['instructions']);
                        $this->line("     Array with {$itemCount} items");
                    }
                }
            }
            
            $this->info("Channels with instructions in API: " . $instructionsCount);
            
            // Test 3: Check database
            $this->line('3. Checking database...');
            $dbTotal = PaymentChannel::count();
            $dbWithInstructions = PaymentChannel::whereNotNull('instructions')->count();
            
            $this->line("Database total channels: " . $dbTotal);
            $this->line("Database channels with instructions: " . $dbWithInstructions);
            
            // Test 4: Sample database record
            $sampleChannel = PaymentChannel::whereNotNull('instructions')->first();
            if ($sampleChannel) {
                $this->line('4. Sample database record:');
                $this->line("   Code: " . $sampleChannel->code);
                $this->line("   Instructions type: " . gettype($sampleChannel->instructions));
                
                if (is_array($sampleChannel->instructions)) {
                    $count = count($sampleChannel->instructions);
                    $this->line("   Instructions count: " . $count);
                    
                    if ($count > 0) {
                        $this->line("   First instruction: " . $sampleChannel->instructions[0]);
                    }
                }
            } else {
                $this->line('4. No sample found in database');
            }
            
            // Summary
            $this->line('');
            $this->info('SUMMARY:');
            $this->line("API has instructions: " . ($instructionsCount > 0 ? 'YES' : 'NO'));
            $this->line("Database has instructions: " . ($dbWithInstructions > 0 ? 'YES' : 'NO'));
            
            if ($instructionsCount > 0 && $dbWithInstructions == 0) {
                $this->error('ISSUE: API has instructions but database doesn\'t');
                $this->line('Try running sync again.');
            } elseif ($dbWithInstructions > 0) {
                $this->info('SUCCESS: Instructions are in database');
            } else {
                $this->warn('No instructions found anywhere');
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
