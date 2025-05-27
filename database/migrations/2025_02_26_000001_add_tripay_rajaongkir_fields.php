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
        // Add email field to users table for Tripay requirement
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->after('username');
            $table->index('email');
        });

        // Add Tripay fields to transaksi table
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('merchant_ref')->unique()->nullable()->after('id');
            $table->timestamp('expired_time')->nullable()->after('tanggal_transaksi');
            $table->string('callback_url')->nullable();
            $table->string('return_url')->nullable();
            $table->decimal('fee_merchant', 10, 2)->default(0);
            $table->decimal('fee_customer', 10, 2)->default(0);
            $table->text('callback_data')->nullable()->comment('JSON callback from Tripay');
            
            $table->index('merchant_ref');
            $table->index('expired_time');
        });

        // Add Tripay fields to pembayaran table
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('payment_code')->nullable()->after('metode')->comment('VA number, QRIS code, etc');
            $table->string('payment_url')->nullable();
            $table->string('checkout_url')->nullable();
            $table->timestamp('expired_time')->nullable();
            $table->text('payment_instructions')->nullable()->comment('JSON format');
            $table->string('callback_signature')->nullable();
            $table->enum('payment_type', ['direct', 'redirect'])->default('direct');
            
            $table->index('payment_code');
            $table->index('expired_time');
        });

        // Add Raja Ongkir fields to pengiriman table
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->integer('origin_province_id')->nullable()->after('id_transaksi');
            $table->integer('origin_city_id')->nullable();
            $table->integer('origin_subdistrict_id')->nullable();
            $table->integer('destination_province_id')->nullable();
            $table->integer('destination_city_id')->nullable();
            $table->integer('destination_subdistrict_id')->nullable();
            $table->integer('weight')->comment('in grams');
            $table->string('service_code')->nullable()->comment('REG, OKE, etc');
            $table->string('etd')->nullable()->comment('estimated delivery time');
            $table->text('courier_info')->nullable()->comment('JSON format for full courier details');
            
            $table->index(['origin_city_id', 'destination_city_id']);
            $table->index('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropColumn('email');
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['merchant_ref']);
            $table->dropIndex(['expired_time']);
            $table->dropColumn([
                'merchant_ref', 'expired_time', 'callback_url', 
                'return_url', 'fee_merchant', 'fee_customer', 'callback_data'
            ]);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex(['payment_code']);
            $table->dropIndex(['expired_time']);
            $table->dropColumn([
                'payment_code', 'payment_url', 'checkout_url', 
                'expired_time', 'payment_instructions', 'callback_signature', 'payment_type'
            ]);
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropIndex(['origin_city_id', 'destination_city_id']);
            $table->dropIndex(['weight']);
            $table->dropColumn([
                'origin_province_id', 'origin_city_id', 'origin_subdistrict_id',
                'destination_province_id', 'destination_city_id', 'destination_subdistrict_id',
                'weight', 'service_code', 'etd', 'courier_info'
            ]);
        });
    }
};
