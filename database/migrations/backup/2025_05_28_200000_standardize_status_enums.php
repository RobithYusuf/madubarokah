<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbaiki ENUM status pada tabel transaksi
        // Tambahkan status 'berhasil', 'expired', dan 'refund' ke ENUM transaksi
        // untuk menyesuaikan dengan nilai yang mungkin dari Tripay
        DB::statement("ALTER TABLE transaksi MODIFY COLUMN status ENUM(
            'pending', 
            'dibayar', 
            'berhasil', 
            'dikirim', 
            'selesai', 
            'batal',
            'gagal',
            'expired',
            'refund'
        ) NOT NULL DEFAULT 'pending'");

        // 2. Perbaiki ENUM status pada tabel pembayaran
        // Tambahkan status 'expired', 'refund', dan 'dibayar'
        DB::statement("ALTER TABLE pembayaran MODIFY COLUMN status ENUM(
            'pending', 
            'berhasil', 
            'dibayar',
            'gagal',
            'expired',
            'refund',
            'canceled'
        ) NOT NULL DEFAULT 'pending'");

        // 3. Pastikan semua status yang ada valid
        // Sesuaikan status 'dibayar' menjadi 'berhasil' dan sebaliknya jika perlu
        DB::statement("UPDATE transaksi SET status = 'berhasil' WHERE status = 'dibayar'");
        DB::statement("UPDATE pembayaran SET status = 'berhasil' WHERE status = 'dibayar'");
        
        // 4. Tambahkan indeks untuk optimasi performa
        Schema::table('transaksi', function (Blueprint $table) {
            // Pastikan indeks ada
            if (!Schema::hasIndex('transaksi', 'status')) {
                $table->index('status');
            }
        });
        
        Schema::table('pembayaran', function (Blueprint $table) {
            // Pastikan indeks ada
            if (!Schema::hasIndex('pembayaran', 'status')) {
                $table->index('status');
            }
            if (!Schema::hasIndex('pembayaran', 'reference')) {
                $table->index('reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Kembalikan ENUM status transaksi ke semula
        DB::statement("ALTER TABLE transaksi MODIFY COLUMN status ENUM(
            'pending', 
            'dibayar', 
            'dikirim', 
            'selesai', 
            'batal'
        ) NOT NULL DEFAULT 'pending'");

        // 2. Kembalikan ENUM status pembayaran ke semula
        DB::statement("ALTER TABLE pembayaran MODIFY COLUMN status ENUM(
            'pending', 
            'berhasil', 
            'gagal'
        ) NOT NULL DEFAULT 'pending'");

        // 3. Hapus indeks yang ditambahkan
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['reference']);
        });
    }
};
