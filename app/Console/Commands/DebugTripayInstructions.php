<?php

namespace App\Console\Commands;

use App\Services\TripayService;
use App\Models\PaymentChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DebugTripayInstructions extends Command
{
    protected $signature = 'tripay:debug-instructions';
    protected $description = 'Debug Tripay payment channel instructions sync';

    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        parent::__construct();
        $this->tripayService = $tripayService;
    }

    public function handle()
    {
        $this->info('🔍 Debug Tripay Instructions Synchronization');
        $this->newLine();

        // Step 1: Test API Connection
        $this->info('1. Testing Tripay API Connection...');
        try {
            $channels = $this->tripayService->getPaymentChannels();
            if (empty($channels)) {
                $this->error('❌ No payment channels returned from API');
                return;
            }
            
            $totalChannels = count($channels);
            $this->info('✅ API Connection successful');
            $this->info("📊 Found {$totalChannels} payment channels");
            $this->newLine();

            // Step 2: Analyze Instructions Data
            $this->info('2. Analyzing Instructions Data from API...');
            $this->newLine();

            $channelsWithInstructions = 0;
            $channelsWithoutInstructions = 0;
            $instructionsStructure = [];

            foreach ($channels as $index => $channel) {
                $channelCode = $channel['code'] ?? 'unknown';
                $channelName = $channel['name'] ?? 'unknown';
                $channelNumber = $index + 1;
                
                $this->line("📋 Channel {$channelNumber}: {$channelCode} - {$channelName}");
                
                // Check for instructions
                if (isset($channel['instructions'])) {
                    $channelsWithInstructions++;
                    $instructionsType = gettype($channel['instructions']);
                    
                    $this->line("   ✅ Has Instructions (Type: {$instructionsType})");
                    
                    if (is_array($channel['instructions'])) {
                        $count = count($channel['instructions']);
                        $this->line("   📝 Array with {$count} items");
                        
                        // Show first few instructions
                        foreach (array_slice($channel['instructions'], 0, 3) as $i => $instruction) {
                            $instructionNumber = $i + 1;
                            $preview = is_string($instruction) ? Str::limit($instruction, 50) : json_encode($instruction);
                            $this->line("      {$instructionNumber}. {$preview}");
                        }
                        
                        if ($count > 3) {
                            $remaining = $count - 3;
                            $this->line("      ... and {$remaining} more");
                        }
                        
                    } elseif (is_string($channel['instructions'])) {
                        $preview = Str::limit($channel['instructions'], 100);
                        $this->line("   📝 String: {$preview}");
                    } else {
                        $this->line("   ⚠️  Unknown format: " . json_encode($channel['instructions']));
                    }
                    
                    // Track structure types
                    $structureKey = $instructionsType . '_' . (is_array($channel['instructions']) ? count($channel['instructions']) : 'single');
                    $instructionsStructure[$structureKey] = ($instructionsStructure[$structureKey] ?? 0) + 1;
                    
                } else {
                    $channelsWithoutInstructions++;
                    $this->line("   ❌ No Instructions");
                }
                
                $this->newLine();
            }

            // Step 3: Summary
            $this->info('3. Instructions Summary:');
            $this->line("✅ Channels with instructions: {$channelsWithInstructions}");
            $this->line("❌ Channels without instructions: {$channelsWithoutInstructions}");
            $this->newLine();

            if (!empty($instructionsStructure)) {
                $this->info('📊 Instructions Structure Distribution:');
                foreach ($instructionsStructure as $structure => $count) {
                    $this->line("   {$structure}: {$count} channels");
                }
                $this->newLine();
            }

            // Step 4: Test Database Sync (if user confirms)
            if ($this->confirm('Do you want to test syncing one channel to database?')) {
                $this->info('4. Testing Database Sync...');
                
                // Find a channel with instructions
                $testChannel = null;
                foreach ($channels as $channel) {
                    if (isset($channel['instructions']) && !empty($channel['instructions'])) {
                        $testChannel = $channel;
                        break;
                    }
                }
                
                if ($testChannel) {
                    $this->line("🧪 Testing with channel: {$testChannel['code']} - {$testChannel['name']}");
                    
                    // Process instructions like TripayService does
                    $instructions = null;
                    if (isset($testChannel['instructions'])) {
                        if (is_array($testChannel['instructions'])) {
                            $instructions = $testChannel['instructions'];
                            $this->line("✅ Processed as array with " . count($instructions) . " items");
                        } elseif (is_string($testChannel['instructions']) && !empty($testChannel['instructions'])) {
                            $decoded = json_decode($testChannel['instructions'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $instructions = $decoded;
                                $this->line("✅ Processed as decoded JSON array");
                            } else {
                                $instructions = [$testChannel['instructions']];
                                $this->line("✅ Processed as wrapped string");
                            }
                        }
                    }
                    
                    // Test saving to database
                    try {
                        $channelData = [
                            'name' => $testChannel['name'] ?? 'Test Channel',
                            'icon_url' => $testChannel['icon_url'] ?? null,
                            'group' => $testChannel['group'] ?? 'other',
                            'is_active' => $testChannel['active'] ?? true,
                            'fee_flat' => 0,
                            'fee_percent' => 0,
                            'minimum_fee' => 0,
                            'maximum_fee' => 0,
                            'instructions' => $instructions,
                        ];

                        $saved = PaymentChannel::updateOrCreate(
                            ['code' => $testChannel['code']],
                            $channelData
                        );

                        $saved->update([
                            'is_synced' => true,
                            'last_synced_at' => now()
                        ]);

                        $this->line("✅ Successfully saved to database");
                        $this->line("💾 Instructions saved as: " . gettype($saved->instructions));
                        
                        if (is_array($saved->instructions)) {
                            $this->line("📝 Database contains " . count($saved->instructions) . " instruction items");
                        }
                        
                    } catch (\Exception $e) {
                        $this->error("❌ Database save failed: " . $e->getMessage());
                    }
                } else {
                    $this->warn("⚠️  No channel with instructions found for testing");
                }
            }

            // Step 5: Check existing database records
            $this->info('5. Checking Database Records...');
            $dbChannels = PaymentChannel::whereNotNull('instructions')->get();
            
            if ($dbChannels->count() > 0) {
                $this->line("📊 Found {$dbChannels->count()} channels with instructions in database:");
                
                foreach ($dbChannels as $dbChannel) {
                    $instructionsCount = is_array($dbChannel->instructions) ? count($dbChannel->instructions) : 1;
                    $this->line("   {$dbChannel->code}: {$instructionsCount} instructions");
                }
            } else {
                $this->warn("⚠️  No channels with instructions found in database");
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->line('Trace: ' . $e->getTraceAsString());
        }

        $this->newLine();
        $this->info('🏁 Debug completed!');
    }
}
