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
        Schema::table('pengiriman', function (Blueprint $table) {
            // Ubah kolom status ENUM untuk menambah nilai 'menunggu_pembayaran'
            $table->enum('status', [
                'menunggu_pembayaran',
                'diproses', 
                'dikirim', 
                'diterima',
                'dibatalkan'
            ])->default('menunggu_pembayaran')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            // Kembalikan ke ENUM lama
            $table->enum('status', [
                'diproses', 
                'dikirim', 
                'diterima'
            ])->default('diproses')->change();
        });
    }
};
