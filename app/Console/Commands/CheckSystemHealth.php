<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PaymentChannel;
use App\Services\TripayService;
use App\Services\RajaOngkirService;

class CheckSystemHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:check-health {--fix : Automatically fix found issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check system health for Tripay & RajaOngkir integration';

    protected $tripayService;
    protected $rajaOngkirService;

    public function __construct(TripayService $tripayService, RajaOngkirService $rajaOngkirService)
    {
        parent::__construct();
        $this->tripayService = $tripayService;
        $this->rajaOngkirService = $rajaOngkirService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 System Health Check - Tripay & RajaOngkir Integration');
        $this->line('');

        $issues = [];
        $fixes = [];

        // 1. Check Configuration
        $this->info('📋 1. Checking Configuration...');
        $configIssues = $this->checkConfiguration();
        if (!empty($configIssues)) {
            $issues = array_merge($issues, $configIssues);
        }

        // 2. Check Database Tables
        $this->info('📋 2. Checking Database Tables...');
        $dbIssues = $this->checkDatabaseTables();
        if (!empty($dbIssues)) {
            $issues = array_merge($issues, $dbIssues);
        }

        // 3. Check User Emails
        $this->info('📋 3. Checking User Emails...');
        $emailIssues = $this->checkUserEmails();
        if (!empty($emailIssues)) {
            $issues = array_merge($issues, $emailIssues);
        }

        // 4. Check Tripay Connection
        $this->info('📋 4. Checking Tripay Connection...');
        $tripayIssues = $this->checkTripayConnection();
        if (!empty($tripayIssues)) {
            $issues = array_merge($issues, $tripayIssues);
        }

        // 5. Check RajaOngkir Connection
        $this->info('📋 5. Checking RajaOngkir Connection...');
        $rajaOngkirIssues = $this->checkRajaOngkirConnection();
        if (!empty($rajaOngkirIssues)) {
            $issues = array_merge($issues, $rajaOngkirIssues);
        }

        // 6. Check Payment Channels
        $this->info('📋 6. Checking Payment Channels...');
        $channelIssues = $this->checkPaymentChannels();
        if (!empty($channelIssues)) {
            $issues = array_merge($issues, $channelIssues);
        }

        $this->line('');

        // Summary
        if (empty($issues)) {
            $this->info('🎉 All systems are healthy! No issues found.');
            return self::SUCCESS;
        }

        $this->warn('⚠️  Found ' . count($issues) . ' issue(s):');
        foreach ($issues as $issue) {
            $this->line('  ❌ ' . $issue);
        }

        // Auto-fix if requested
        if ($this->option('fix')) {
            $this->line('');
            $this->info('🔧 Auto-fixing issues...');
            $this->autoFixIssues($issues);
        } else {
            $this->line('');
            $this->info('💡 Run with --fix to automatically resolve issues');
            $this->info('💡 Or check FIX_CHECKOUT_ISSUES.md for manual fixes');
        }

        return self::SUCCESS;
    }

    private function checkConfiguration()
    {
        $issues = [];

        // Check APP_URL
        if (empty(config('app.url')) || config('app.url') === 'http://localhost') {
            $issues[] = 'APP_URL not properly set (needed for Tripay callbacks)';
        }

        // Check Tripay config
        if (empty(config('tripay.api_key'))) {
            $issues[] = 'TRIPAY_API_KEY not configured';
        }
        if (empty(config('tripay.private_key'))) {
            $issues[] = 'TRIPAY_PRIVATE_KEY not configured';
        }
        if (empty(config('tripay.merchant_code'))) {
            $issues[] = 'TRIPAY_MERCHANT_CODE not configured';
        }

        // Check callback URLs
        $callbackUrl = config('tripay.callback_url');
        if (empty($callbackUrl) || strpos($callbackUrl, 'localhost') !== false) {
            $issues[] = 'Tripay callback URL may not be accessible from internet';
        }

        $this->line('  ' . (empty($issues) ? '✅' : '❌') . ' Configuration');
        return $issues;
    }

    private function checkDatabaseTables()
    {
        $issues = [];

        // Check if Tripay fields exist
        $tables = [
            'transaksi' => ['tripay_reference', 'callback_url'],
            'pembayaran' => ['qr_string', 'qr_url', 'callback_data', 'paid_at'],
            'payment_channels' => ['is_synced', 'last_synced_at']
        ];

        foreach ($tables as $table => $fields) {
            foreach ($fields as $field) {
                if (!\Schema::hasColumn($table, $field)) {
                    $issues[] = "Missing field '{$field}' in table '{$table}'";
                }
            }
        }

        $this->line('  ' . (empty($issues) ? '✅' : '❌') . ' Database Tables');
        return $issues;
    }

    private function checkUserEmails()
    {
        $issues = [];
        
        $invalidUsers = User::all()->filter(function ($user) {
            return !filter_var($user->email, FILTER_VALIDATE_EMAIL);
        });

        if ($invalidUsers->count() > 0) {
            $issues[] = $invalidUsers->count() . ' user(s) have invalid email addresses';
        }

        $this->line('  ' . (empty($issues) ? '✅' : '❌') . ' User Emails');
        return $issues;
    }

    private function checkTripayConnection()
    {
        $issues = [];
        
        try {
            $channels = $this->tripayService->getPaymentChannels();
            if (empty($channels)) {
                $issues[] = 'Cannot connect to Tripay API or no channels returned';
            }
        } catch (\Exception $e) {
            $issues[] = 'Tripay API connection failed: ' . $e->getMessage();
        }

        $this->line('  ' . (empty($issues) ? '✅' : '❌') . ' Tripay Connection');
        return $issues;
    }

    private function checkRajaOngkirConnection()
    {
        $issues = [];
        
        try {
            $result = $this->rajaOngkirService->getProvinces();
            if (!$result['success']) {
                $issues[] = 'RajaOngkir API error: ' . $result['message'];
            }
        } catch (\Exception $e) {
            $issues[] = 'RajaOngkir API connection failed: ' . $e->getMessage();
        }

        $this->line('  ' . (empty($issues) ? '✅' : '❌') . ' RajaOngkir Connection');
        return $issues;
    }

    private function checkPaymentChannels()
    {
        $issues = [];
        
        $syncedChannels = PaymentChannel::where('is_synced', true)->count();
        $totalChannels = PaymentChannel::count();
        
        if ($syncedChannels === 0) {
            $issues[] = 'No payment channels synced from Tripay';
        }
        
        $qrisChannel = PaymentChannel::where('code', 'QRIS2')->where('is_active', true)->first();
        if (!$qrisChannel) {
            $issues[] = 'QRIS payment channel not found or inactive';
        }

        $this->line('  ' . (empty($issues) ? '✅' : '❌') . ' Payment Channels (' . $syncedChannels . '/' . $totalChannels . ' synced)');
        return $issues;
    }

    private function autoFixIssues($issues)
    {
        $fixed = 0;

        foreach ($issues as $issue) {
            if (strpos($issue, 'user(s) have invalid email') !== false) {
                $this->call('fix:user-emails');
                $fixed++;
            }
            
            if (strpos($issue, 'No payment channels synced') !== false) {
                $this->call('tripay:sync-channels');
                $fixed++;
            }
            
            if (strpos($issue, 'Missing field') !== false) {
                $this->line('🔧 Running migrations...');
                $this->call('migrate');
                $fixed++;
            }
        }

        if ($fixed > 0) {
            $this->line('');
            $this->info("✅ Auto-fixed {$fixed} issue(s)");
            $this->info('🔄 Run system:check-health again to verify fixes');
        } else {
            $this->warn('⚠️  Some issues require manual intervention');
            $this->info('💡 Check FIX_CHECKOUT_ISSUES.md for manual fixes');
        }
    }
}
