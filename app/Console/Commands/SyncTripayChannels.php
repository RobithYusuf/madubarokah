<?php

namespace App\Console\Commands;

use App\Services\TripayService;
use Illuminate\Console\Command;

class SyncTripayChannels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tripay:sync-channels 
                            {--force : Force sync even if channels exist}
                            {--active-only : Only sync active channels}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync payment channels from Tripay API';

    protected $tripayService;

    /**
     * Create a new command instance.
     */
    public function __construct(TripayService $tripayService)
    {
        parent::__construct();
        $this->tripayService = $tripayService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Tripay payment channels synchronization...');
        $this->newLine();

        try {
            // Show current config
            $this->line('📋 Current Tripay Configuration:');
            $this->line('   Base URL: ' . config('tripay.base_url'));
            $this->line('   Merchant Code: ' . config('tripay.merchant_code'));
            $this->line('   API Key: ' . (config('tripay.api_key') ? '✓ Set' : '✗ Not set'));
            $this->line('   Private Key: ' . (config('tripay.private_key') ? '✓ Set' : '✗ Not set'));
            $this->newLine();

            // Check API configuration
            if (!config('tripay.api_key') || !config('tripay.private_key')) {
                $this->error('❌ Tripay API configuration incomplete!');
                $this->line('Please check your .env file for:');
                $this->line('- TRIPAY_API_KEY');
                $this->line('- TRIPAY_PRIVATE_KEY');
                $this->line('- TRIPAY_MERCHANT_CODE');
                return Command::FAILURE;
            }

            // Start sync process
            $this->info('📡 Fetching payment channels from Tripay API...');
            
            $bar = $this->output->createProgressBar(3);
            $bar->setFormat('verbose');
            
            $bar->start();
            $bar->setMessage('Connecting to Tripay API...');
            $bar->advance();

            $result = $this->tripayService->syncPaymentChannels();
            
            $bar->setMessage('Processing channels...');
            $bar->advance();
            
            $bar->setMessage('Saving to database...');
            $bar->advance();
            
            $bar->finish();
            $this->newLine(2);

            if ($result['success']) {
                $this->info("✅ {$result['message']}");
                
                // Show summary
                $this->displaySummary();
                
                return Command::SUCCESS;
            } else {
                $this->error("❌ Sync failed: {$result['message']}");
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error("❌ Exception occurred: {$e->getMessage()}");
            $this->line("Stack trace: {$e->getTraceAsString()}");
            return Command::FAILURE;
        }
    }

    private function displaySummary()
    {
        $this->newLine();
        $this->info('📊 Payment Channels Summary:');
        
        $channels = \App\Models\PaymentChannel::selectRaw('
            `group`,
            COUNT(*) as total,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
        ')
        ->groupBy('group')
        ->orderBy('group')
        ->get();
        
        $this->table(
            ['Group', 'Total', 'Active'],
            $channels->map(function($channel) {
                return [
                    $channel->group,
                    $channel->total,
                    $channel->active . ($channel->active > 0 ? ' ✓' : ' ✗')
                ];
            })->toArray()
        );

        $this->newLine();
        $this->line('💡 Tips:');
        $this->line('- Use php artisan tinker to test payment channel methods');
        $this->line('- Check admin panel to enable/disable specific channels');
        $this->line('- Test checkout process with different payment methods');
        $this->newLine();
    }
}
