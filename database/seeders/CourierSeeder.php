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
                'is_active' => false,
                'services' => json_encode([
                    // Services yang sesuai dengan API RajaOngkir untuk POS Indonesia
                    'Paket Kilat Khusus' => 'Paket Kilat Khusus',
                    'Express Next Day Barang' => 'Express Next Day Barang',
                    'Express Next Day Dokumen' => 'Express Next Day Dokumen', 
                    'Express Sameday Barang' => 'Express Sameday Barang',
                    'Express Sameday Dokumen' => 'Express Sameday Dokumen',
                    'Pos Nextday Barang' => 'Pos Nextday Barang',
                    'Pos Reguler' => 'Pos Reguler',
                    'Pos Untuk Anda' => 'Pos Untuk Anda'
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
                    'ONS' => 'Over Night Service',
                    'SDS' => 'Same Day Service'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'jnt',
                'name' => 'J&T Express',
                'is_active' => false, // Tidak didukung RajaOngkir Starter
                'services' => json_encode([
                    'REG' => 'Reguler',
                    'YES' => 'Yakin Esok Sampai',
                    'ECO' => 'Economy'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'sicepat',
                'name' => 'SiCepat Ekspres',
                'is_active' => false, // Tidak didukung RajaOngkir Starter
                'services' => json_encode([
                    'REG' => 'Sigesit',
                    'BEST' => 'Sicepat Best',
                    'CARGO' => 'Sicepat Cargo'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'anteraja',
                'name' => 'AnterAja',
                'is_active' => false, // Tidak didukung RajaOngkir Starter
                'services' => json_encode([
                    'REG' => 'Reguler',
                    'SAME_DAY' => 'Same Day',
                    'NEXT_DAY' => 'Next Day'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ninja',
                'name' => 'Ninja Express',
                'is_active' => false, // Tidak didukung RajaOngkir Starter
                'services' => json_encode([
                    'STD' => 'Standard',
                    'ECO' => 'Economy'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Hapus data lama jika ada
        DB::table('couriers')->truncate();
        
        // Insert data baru
        DB::table('couriers')->insert($couriers);
        
        // Log hasil seeding
        \Log::info('CourierSeeder completed', [
            'total_couriers' => count($couriers),
            'active_couriers' => collect($couriers)->where('is_active', true)->count(),
            'rajaongkir_supported' => ['jne', 'pos', 'tiki'] // Starter package
        ]);
    }
}