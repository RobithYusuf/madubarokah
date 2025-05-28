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
        // Add Tripay fields to transaksi table
        Schema::table('transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksi', 'tripay_reference')) {
                $table->string('tripay_reference', 100)->nullable()->after('merchant_ref');
            }
            if (!Schema::hasColumn('transaksi', 'callback_url')) {
                $table->text('callback_url')->nullable()->after('return_url');
            }
        });

        // Add Tripay fields to pembayaran table
        Schema::table('pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayaran', 'qr_string')) {
                $table->text('qr_string')->nullable()->after('payment_instructions');
            }
            if (!Schema::hasColumn('pembayaran', 'qr_url')) {
                $table->text('qr_url')->nullable()->after('qr_string');
            }
            if (!Schema::hasColumn('pembayaran', 'callback_data')) {
                $table->json('callback_data')->nullable()->after('qr_url');
            }
            if (!Schema::hasColumn('pembayaran', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('callback_data');
            }
        });

        // Add sync fields to payment_channels table
        Schema::table('payment_channels', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_channels', 'is_synced')) {
                $table->boolean('is_synced')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('payment_channels', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('is_synced');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['tripay_reference', 'callback_url']);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['qr_string', 'qr_url', 'callback_data', 'paid_at']);
        });

        Schema::table('payment_channels', function (Blueprint $table) {
            $table->dropColumn(['is_synced', 'last_synced_at']);
        });
    }
};
