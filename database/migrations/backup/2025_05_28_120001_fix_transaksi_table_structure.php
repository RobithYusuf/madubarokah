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
        // Remove metode_pembayaran column from transaksi table if it exists
        // since this data is now stored in pembayaran table
        Schema::table('transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi', 'metode_pembayaran')) {
                $table->dropColumn('metode_pembayaran');
            }
        });

        // Ensure tripay_reference column exists
        Schema::table('transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksi', 'tripay_reference')) {
                $table->string('tripay_reference', 100)->nullable()->after('merchant_ref');
            }
        });

        // Add indexes for better performance
        Schema::table('transaksi', function (Blueprint $table) {
            if (!Schema::hasIndex('transaksi', ['id_user', 'status'])) {
                $table->index(['id_user', 'status']);
            }
            if (!Schema::hasIndex('transaksi', ['created_at', 'status'])) {
                $table->index(['created_at', 'status']);
            }
            if (!Schema::hasIndex('transaksi', 'merchant_ref')) {
                $table->index('merchant_ref');
            }
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            if (!Schema::hasIndex('pembayaran', ['status', 'payment_type'])) {
                $table->index(['status', 'payment_type']);
            }
            if (!Schema::hasIndex('pembayaran', 'expired_time')) {
                $table->index('expired_time');
            }
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            if (!Schema::hasIndex('pengiriman', 'status')) {
                $table->index('status');
            }
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (!Schema::hasIndex('detail_transaksi', 'id_transaksi')) {
                $table->index('id_transaksi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the metode_pembayaran field if needed for rollback
        Schema::table('transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksi', 'metode_pembayaran')) {
                $table->string('metode_pembayaran')->nullable()->after('status');
            }
        });

        // Drop indexes
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['id_user', 'status']);
            $table->dropIndex(['created_at', 'status']);
            $table->dropIndex(['merchant_ref']);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex(['status', 'payment_type']);
            $table->dropIndex(['expired_time']);
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropIndex(['id_transaksi']);
        });
    }
};
