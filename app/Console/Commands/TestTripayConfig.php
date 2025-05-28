<?php

namespace App\Console\Commands;

use App\Services\TripayService;
use Illuminate\Console\Command;

class TestTripayConfig extends Command
{
    protected $signature = 'tripay:test-config';
    protected $description = 'Test Tripay configuration and connection';

    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        parent::__construct();
        $this->tripayService = $tripayService;
    }

    public function handle()
    {
        $this->info('Testing Tripay Configuration...');
        $this->newLine();

        // Test 1: Check environment variables
        $this->info('1. Checking environment variables:');
        $this->checkEnvVar('APP_URL', config('app.url'));
        $this->checkEnvVar('TRIPAY_API_KEY', config('tripay.api_key'));
        $this->checkEnvVar('TRIPAY_PRIVATE_KEY', config('tripay.private_key'));
        $this->checkEnvVar('TRIPAY_MERCHANT_CODE', config('tripay.merchant_code'));
        $this->checkEnvVar('TRIPAY_BASE_URL', config('tripay.base_url'));
        $this->checkEnvVar('TRIPAY_CALLBACK_URL', config('tripay.callback_url'));
        $this->checkEnvVar('TRIPAY_RETURN_URL', config('tripay.return_url'));
        $this->newLine();

        // Test 2: Test API connection
        $this->info('2. Testing API connection:');
        try {
            $channels = $this->tripayService->getPaymentChannels();
            if (!empty($channels)) {
                $this->info('✓ API connection successful');
                $this->info('✓ Found ' . count($channels) . ' payment channels');
            } else {
                $this->warn('⚠ API connected but no payment channels found');
            }
        } catch (\Exception $e) {
            $this->error('✗ API connection failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Test callback URL format
        $this->info('3. Testing callback URL format:');
        $callbackUrl = config('tripay.callback_url');
        if ($this->isValidUrl($callbackUrl)) {
            $this->info('✓ Callback URL format is valid: ' . $callbackUrl);
        } else {
            $this->error('✗ Callback URL format is invalid: ' . $callbackUrl);
        }

        $returnUrl = config('tripay.return_url');
        if ($this->isValidUrl($returnUrl)) {
            $this->info('✓ Return URL format is valid: ' . $returnUrl);
        } else {
            $this->error('✗ Return URL format is invalid: ' . $returnUrl);
        }

        $this->newLine();
        $this->info('Tripay configuration test completed!');
    }

    private function checkEnvVar($key, $value)
    {
        if (!empty($value)) {
            $this->info("✓ {$key}: {$value}");
        } else {
            $this->error("✗ {$key}: NOT SET or EMPTY");
        }
    }

    private function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
