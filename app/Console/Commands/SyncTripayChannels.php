<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TripayService;

class SyncTripayChannels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tripay:sync-channels {--force : Force sync even if recently synced}';

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
        $this->info('Starting Tripay payment channels synchronization...');
        
        try {
            // Show current config
            $this->info('Tripay Config:');
            $this->line('- Base URL: ' . config('tripay.base_url'));
            $this->line('- Merchant Code: ' . config('tripay.merchant_code'));
            $this->line('- Callback URL: ' . config('tripay.callback_url'));
            $this->line('');

            // Sync channels
            $result = $this->tripayService->syncPaymentChannels();

            if ($result['success']) {
                $this->info('✅ ' . $result['message']);
                $this->info('📊 Synced channels: ' . $result['count']);
                
                // Show synced channels
                $channels = \App\Models\PaymentChannel::where('is_synced', true)->get();
                
                if ($channels->count() > 0) {
                    $this->info('');
                    $this->info('Synced Payment Channels:');
                    
                    $tableData = [];
                    foreach ($channels as $channel) {
                        $tableData[] = [
                            $channel->code,
                            $channel->name,
                            $channel->group,
                            $channel->is_active ? '✅' : '❌',
                            'Rp ' . number_format($channel->fee_flat) . ' + ' . $channel->fee_percent . '%'
                        ];
                    }
                    
                    $this->table([
                        'Code',
                        'Name', 
                        'Group',
                        'Active',
                        'Fee'
                    ], $tableData);
                }
                
                return self::SUCCESS;
            } else {
                $this->error('❌ ' . $result['message']);
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
