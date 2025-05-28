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
        // Tambahkan field untuk menyimpan informasi Callback dari Tripay
        Schema::table('pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayaran', 'callback_url')) {
                $table->string('callback_url')->nullable()->after('payment_type');
            }
            if (!Schema::hasColumn('pembayaran', 'payment_type_code')) {
                $table->string('payment_type_code', 20)->nullable()->after('payment_type');
            }
        });

        // Tambahkan payment_status field ke tabel transaksi untuk tracking payment
        Schema::table('transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksi', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('status');
                $table->index('payment_status');
            }
        });
        
        // Tambahkan indeks pada table pembayaran untuk optimalisasi pencarian
        Schema::table('pembayaran', function (Blueprint $table) {
            if (!Schema::hasIndex('pembayaran', 'metode')) {
                $table->index('metode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['callback_url', 'payment_type_code']);
            $table->dropIndex(['metode']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
