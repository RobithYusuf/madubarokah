<?php

namespace App\Console\Commands;

use App\Services\TripayService;
use App\Models\PaymentChannel;
use Illuminate\Console\Command;

class QuickTestInstructions extends Command
{
    protected $signature = 'tripay:quick-test';
    protected $description = 'Quick test for instructions synchronization';

    public function handle()
    {
        $this->info('🚀 Quick Test: Instructions Synchronization');
        $this->newLine();

        try {
            // 1. Test API
            $this->line('1. Testing API connection...');
            $service = app(TripayService::class);
            $channels = $service->getPaymentChannels();
            
            if (empty($channels)) {
                $this->error('❌ No channels from API');
                return;
            }
            
            $totalChannels = count($channels);
            $this->info("✅ API OK - {$totalChannels} channels");

            // 2. Count instructions in API
            $apiWithInstructions = 0;
            foreach ($channels as $channel) {
                if (isset($channel['instructions']) && !empty($channel['instructions'])) {
                    $apiWithInstructions++;
                }
            }
            
            $this->line("📝 API channels with instructions: {$apiWithInstructions}");

            // 3. Test database
            $dbTotal = PaymentChannel::count();
            $dbWithInstructions = PaymentChannel::whereNotNull('instructions')->count();
            
            $this->line("💾 Database total channels: {$dbTotal}");
            $this->line("💾 Database channels with instructions: {$dbWithInstructions}");

            // 4. Test model casting
            $testChannel = PaymentChannel::whereNotNull('instructions')->first();
            if ($testChannel) {
                $instructionsType = gettype($testChannel->instructions);
                $this->line("🔧 Instructions casting test: {$instructionsType}");
                
                if (is_array($testChannel->instructions)) {
                    $this->info("✅ Instructions properly cast to array");
                } else {
                    $this->warn("⚠️  Instructions not cast to array properly");
                }
            }

            // 5. Quick sync test
            if ($this->confirm('Run quick sync test?', false)) {
                $this->line('🔄 Running sync...');
                $result = $service->syncPaymentChannels();
                
                if (is_array($result) && ($result['success'] ?? false)) {
                    $this->info("✅ Sync successful: {$result['message']}");
                } else {
                    $this->warn("⚠️  Sync result: " . (is_string($result) ? $result : json_encode($result)));
                }

                // Re-check database
                $newDbWithInstructions = PaymentChannel::whereNotNull('instructions')->count();
                $this->line("💾 Database channels with instructions after sync: {$newDbWithInstructions}");
                
                if ($newDbWithInstructions > $dbWithInstructions) {
                    $this->info("✅ Instructions sync improved!");
                } elseif ($newDbWithInstructions == $dbWithInstructions) {
                    $this->line("➡️  No change in instructions count");
                } else {
                    $this->warn("⚠️  Instructions count decreased");
                }
            }

            // 6. Summary
            $this->newLine();
            $this->info('📊 Quick Test Summary:');
            $this->line("API channels with instructions: {$apiWithInstructions}");
            $this->line("DB channels with instructions: " . PaymentChannel::whereNotNull('instructions')->count());
            
            if ($apiWithInstructions > 0 && PaymentChannel::whereNotNull('instructions')->count() == 0) {
                $this->error('❌ ISSUE: API has instructions but DB doesn\'t. Check sync process.');
            } elseif (PaymentChannel::whereNotNull('instructions')->count() > 0) {
                $this->info('✅ Instructions are being synced to database.');
            } else {
                $this->warn('⚠️  No instructions found in both API and DB.');
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🏁 Quick test completed!');
    }
}
