<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            // Virtual Account
            [
                'code' => 'BRIVA',
                'name' => 'BRI Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'BNIVA',
                'name' => 'BNI Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'MANDIRIVA',
                'name' => 'Mandiri Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'BCAVA',
                'name' => 'BCA Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'minimum_fee' => 4000,
                'maximum_fee' => 4000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // E-Wallet
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay',
                'group' => 'E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 2.5,
                'minimum_fee' => 0,
                'maximum_fee' => 25000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'group' => 'E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 2.5,
                'minimum_fee' => 0,
                'maximum_fee' => 25000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'OVO',
                'name' => 'OVO',
                'group' => 'E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 2.5,
                'minimum_fee' => 0,
                'maximum_fee' => 25000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Convenience Store
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart',
                'group' => 'Convenience Store',
                'fee_flat' => 5000,
                'fee_percent' => 0,
                'minimum_fee' => 5000,
                'maximum_fee' => 5000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret',
                'group' => 'Convenience Store',
                'fee_flat' => 5000,
                'fee_percent' => 0,
                'minimum_fee' => 5000,
                'maximum_fee' => 5000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // QRIS
            [
                'code' => 'QRIS',
                'name' => 'QRIS',
                'group' => 'QRIS',
                'fee_flat' => 0,
                'fee_percent' => 0.7,
                'minimum_fee' => 0,
                'maximum_fee' => 5000,
                'is_active' => true,
                'instructions' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('payment_channels')->insert($channels);
    }
}
