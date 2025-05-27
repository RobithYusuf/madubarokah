<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $couriers = [
            [
                'code' => 'jne',
                'name' => 'Jalur Nugraha Ekakurir (JNE)',
                'is_active' => true,
                'services' => json_encode([
                    'OKE' => 'Ongkos Kirim Ekonomis',
                    'REG' => 'Layanan Reguler',
                    'YES' => 'Yakin Esok Sampai'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pos',
                'name' => 'POS Indonesia',
                'is_active' => true,
                'services' => json_encode([
                    'Paket Kilat Khusus' => 'Paket Kilat Khusus',
                    'Express Next Day Barang' => 'Express Next Day Barang'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'tiki',
                'name' => 'Citra Van Titipan Kilat (TIKI)',
                'is_active' => true,
                'services' => json_encode([
                    'REG' => 'Regular Service',
                    'ECO' => 'Economy Service',
                    'ONS' => 'Over Night Service'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'jnt',
                'name' => 'J&T Express',
                'is_active' => true,
                'services' => json_encode([
                    'REG' => 'Reguler',
                    'YES' => 'Yakin Esok Sampai'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'sicepat',
                'name' => 'SiCepat Ekspres',
                'is_active' => true,
                'services' => json_encode([
                    'REG' => 'Sigesit',
                    'BEST' => 'Sicepat Best'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'anteraja',
                'name' => 'AnterAja',
                'is_active' => true,
                'services' => json_encode([
                    'REG' => 'Reguler',
                    'SAME_DAY' => 'Same Day'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('couriers')->insert($couriers);
    }
}
