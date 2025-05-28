<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TripayService;

class TestTripayConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tripay:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to Tripay API';

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
        $this->info('Testing Tripay API connection...');
        $this->line('');

        // Show configuration
        $this->info('Configuration:');
        $this->line('- Base URL: ' . config('tripay.base_url'));
        $this->line('- Merchant Code: ' . config('tripay.merchant_code'));
        $this->line('- API Key: ' . substr(config('tripay.api_key'), 0, 10) . '...');
        $this->line('- Private Key: ' . substr(config('tripay.private_key'), 0, 10) . '...');
        $this->line('- Callback URL: ' . config('tripay.callback_url'));
        $this->line('- Return URL: ' . config('tripay.return_url'));
        $this->line('');

        try {
            // Test get payment channels
            $this->info('🔍 Testing payment channels endpoint...');
            $channels = $this->tripayService->getPaymentChannels();

            if (empty($channels)) {
                $this->error('❌ No payment channels returned or API error');
                $this->info('💡 Check your API credentials and internet connection');
                return self::FAILURE;
            }

            $this->info('✅ Successfully connected to Tripay API');
            $this->info('📊 Available payment channels: ' . count($channels));
            $this->line('');

            // Show sample channels
            $this->info('Sample Payment Channels:');
            $sampleChannels = array_slice($channels, 0, 5);
            
            $tableData = [];
            foreach ($sampleChannels as $channel) {
                $feeFlat = 0;
                $feePercent = 0;

                if (isset($channel['fee_customer']) && is_array($channel['fee_customer'])) {
                    $feeFlat = $channel['fee_customer']['flat'] ?? 0;
                    $feePercent = $channel['fee_customer']['percent'] ?? 0;
                }

                $tableData[] = [
                    $channel['code'] ?? 'N/A',
                    $channel['name'] ?? 'N/A',
                    $channel['group'] ?? 'N/A',
                    ($channel['active'] ?? false) ? '✅' : '❌',
                    'Rp ' . number_format($feeFlat) . ' + ' . $feePercent . '%'
                ];
            }
            
            $this->table([
                'Code',
                'Name',
                'Group', 
                'Active',
                'Fee'
            ], $tableData);

            if (count($channels) > 5) {
                $this->info('... and ' . (count($channels) - 5) . ' more channels');
            }

            $this->line('');
            $this->info('🎉 Connection test successful!');
            $this->info('💡 You can now run: php artisan tripay:sync-channels');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Connection test failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->line('');
            $this->info('💡 Troubleshooting tips:');
            $this->line('- Check your internet connection');
            $this->line('- Verify API credentials in .env file');
            $this->line('- Make sure TRIPAY_BASE_URL is correct');
            $this->line('- For sandbox: https://tripay.co.id/api-sandbox');
            $this->line('- For production: https://tripay.co.id/api');

            return self::FAILURE;
        }
    }
}
