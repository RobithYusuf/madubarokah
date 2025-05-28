<?php

namespace App\Console\Commands;

use App\Services\TripayService;
use App\Models\PaymentChannel;
use Illuminate\Console\Command;

class TestInstructionsEndpoint extends Command
{
    protected $signature = 'tripay:test-instructions';
    protected $description = 'Test instructions endpoint from Tripay API';

    public function handle()
    {
        $this->info('Testing Tripay Instructions Endpoint');
        $this->line('=====================================');
        
        try {
            $service = app(TripayService::class);
            
            // Test 1: Get payment channels
            $this->line('1. Getting payment channels...');
            $channels = $service->getPaymentChannels();
            
            if (empty($channels)) {
                $this->error('No channels from API');
                return;
            }
            
            $channelCount = count($channels);
            $this->info("Found {$channelCount} payment channels");
            $this->newLine();
            
            // Test 2: Test instructions endpoint for each channel
            $this->line('2. Testing instructions endpoint for each channel...');
            $this->newLine();
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($channels as $channel) {
                $code = $channel['code'] ?? 'unknown';
                $name = $channel['name'] ?? 'unknown';
                
                $this->line("Testing: {$code} - {$name}");
                
                $instructions = $service->getPaymentInstructions($code);
                
                if ($instructions && !empty($instructions)) {
                    $successCount++;
                    
                    if (is_array($instructions)) {
                        $count = count($instructions);
                        $this->line("  ✅ SUCCESS - {$count} instructions found");
                        
                        // Show first instruction as sample
                        if ($count > 0 && isset($instructions[0])) {
                            $sample = is_string($instructions[0]) ? 
                                (strlen($instructions[0]) > 50 ? substr($instructions[0], 0, 50) . '...' : $instructions[0]) : 
                                'Non-string instruction';
                            $this->line("     Sample: {$sample}");
                        }
                    } else {
                        $this->line("  ✅ SUCCESS - Single instruction");
                        $sample = is_string($instructions) ? 
                            (strlen($instructions) > 50 ? substr($instructions, 0, 50) . '...' : $instructions) : 
                            'Non-string instruction';
                        $this->line("     Content: {$sample}");
                    }
                } else {
                    $failCount++;
                    $this->line("  ❌ No instructions available");
                }
                
                $this->newLine();
            }
            
            // Test 3: Summary
            $this->info('SUMMARY:');
            $this->line("Total channels: {$channelCount}");
            $this->line("Channels with instructions: {$successCount}");
            $this->line("Channels without instructions: {$failCount}");
            $this->newLine();
            
            if ($successCount > 0) {
                $this->info('✅ Instructions endpoint is working!');
                
                // Test sync with instructions
                if ($this->confirm('Do you want to sync channels with instructions to database?')) {
                    $this->line('');
                    $this->line('3. Syncing channels with instructions...');
                    
                    $syncResult = $service->syncPaymentChannels();
                    
                    if (is_array($syncResult) && ($syncResult['success'] ?? false)) {
                        $this->info("✅ Sync successful: {$syncResult['message']}");
                        
                        // Verify database
                        $dbWithInstructions = PaymentChannel::whereNotNull('instructions')->count();
                        $this->line("Channels with instructions in database: {$dbWithInstructions}");
                        
                        if ($dbWithInstructions > 0) {
                            $this->info('🎉 SUCCESS! Instructions are now in database');
                            $this->line('');
                            $this->line('Next step: Check admin panel Payment Channel page');
                        }
                    } else {
                        $this->error('Sync failed: ' . ($syncResult['message'] ?? 'Unknown error'));
                    }
                }
            } else {
                $this->warn('⚠️  No instructions found from API');
                $this->line('This might be expected if channels don\'t have instructions configured');
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
