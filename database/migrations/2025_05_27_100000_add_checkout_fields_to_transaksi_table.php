<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Hanya tambahkan field yang belum ada untuk informasi penerima
            $table->string('nama_penerima')->nullable()->after('status');
            $table->string('telepon_penerima')->nullable()->after('nama_penerima');
            $table->text('alamat_pengiriman')->nullable()->after('telepon_penerima');
            $table->text('catatan')->nullable()->after('alamat_pengiriman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn([
                'nama_penerima',
                'telepon_penerima', 
                'alamat_pengiriman',
                'catatan'
            ]);
        });
    }
};
