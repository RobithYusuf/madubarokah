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
        Schema::table('pembayaran', function (Blueprint $table) {
            // Ubah kolom payment_type ENUM untuk menambah nilai 'manual'
            $table->enum('payment_type', [
                'manual',
                'direct', 
                'redirect'
            ])->default('manual')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Kembalikan ke ENUM lama
            $table->enum('payment_type', [
                'direct', 
                'redirect'
            ])->default('direct')->change();
        });
    }
};
