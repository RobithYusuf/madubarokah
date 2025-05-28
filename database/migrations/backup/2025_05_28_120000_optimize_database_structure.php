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
        // Remove redundant metode_pembayaran field from transaksi table
        // since it's already stored in pembayaran table
        Schema::table('transaksi', function (Blueprint $table) {
            // Check if column exists before dropping
            if (Schema::hasColumn('transaksi', 'metode_pembayaran')) {
                $table->dropColumn('metode_pembayaran');
            }
        });

        // Add indexes for better performance
        Schema::table('transaksi', function (Blueprint $table) {
            $table->index(['id_user', 'status']);
            $table->index(['created_at', 'status']);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->index(['status', 'payment_type']);
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->index('id_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the metode_pembayaran field if needed
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('metode_pembayaran')->nullable()->after('status');
        });

        // Drop indexes
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['id_user', 'status']);
            $table->dropIndex(['created_at', 'status']);
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
