<?php

// Script untuk memeriksa struktur database
// Jalankan dengan: php artisan tinker
// Lalu copy-paste kode ini

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== PENGECEKAN STRUKTUR DATABASE ===\n\n";

// 1. Cek struktur tabel pengiriman
echo "1. STRUKTUR TABEL PENGIRIMAN:\n";
$pengirimanColumns = DB::select("DESCRIBE pengiriman");
foreach($pengirimanColumns as $column) {
    echo "   - {$column->Field}: {$column->Type} (Default: {$column->Default})\n";
}
echo "\n";

// 2. Cek struktur tabel pembayaran  
echo "2. STRUKTUR TABEL PEMBAYARAN:\n";
$pembayaranColumns = DB::select("DESCRIBE pembayaran");
foreach($pembayaranColumns as $column) {
    echo "   - {$column->Field}: {$column->Type} (Default: {$column->Default})\n";
}
echo "\n";

// 3. Cek data terakhir di pengiriman
echo "3. DATA TERAKHIR DI TABEL PENGIRIMAN:\n";
$lastPengiriman = DB::table('pengiriman')->latest()->first();
if($lastPengiriman) {
    echo "   - ID: {$lastPengiriman->id}\n";
    echo "   - Status: {$lastPengiriman->status}\n";
    echo "   - Kurir: {$lastPengiriman->kurir}\n";
} else {
    echo "   - Tidak ada data\n";
}
echo "\n";

// 4. Cek data terakhir di pembayaran
echo "4. DATA TERAKHIR DI TABEL PEMBAYARAN:\n";
$lastPembayaran = DB::table('pembayaran')->latest()->first();
if($lastPembayaran) {
    echo "   - ID: {$lastPembayaran->id}\n";
    echo "   - Status: {$lastPembayaran->status}\n";
    echo "   - Payment Type: " . ($lastPembayaran->payment_type ?? 'NULL') . "\n";
} else {
    echo "   - Tidak ada data\n";
}
echo "\n";

// 5. Test insert pengiriman
echo "5. TEST INSERT PENGIRIMAN:\n";
try {
    DB::table('pengiriman')->insert([
        'id_transaksi' => 99999,
        'kurir' => 'TEST',
        'layanan' => 'TEST',
        'biaya' => 10000,
        'status' => 'menunggu_pembayaran',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "   ✅ Berhasil insert dengan status 'menunggu_pembayaran'\n";
    DB::table('pengiriman')->where('id_transaksi', 99999)->delete();
} catch(Exception $e) {
    echo "   ❌ Gagal: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Test insert pembayaran
echo "6. TEST INSERT PEMBAYARAN:\n";
try {
    DB::table('pembayaran')->insert([
        'id_transaksi' => 99999,
        'reference' => 'TEST-REF',
        'metode' => 'TEST',
        'total_bayar' => 10000,
        'status' => 'pending',
        'payment_type' => 'manual',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "   ✅ Berhasil insert dengan payment_type 'manual'\n";
    DB::table('pembayaran')->where('reference', 'TEST-REF')->delete();
} catch(Exception $e) {
    echo "   ❌ Gagal: " . $e->getMessage() . "\n";
}

echo "\n=== PENGECEKAN SELESAI ===\n";
