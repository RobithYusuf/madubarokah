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
        // Create shipping_areas table for Raja Ongkir cache
        Schema::create('shipping_areas', function (Blueprint $table) {
            $table->id();
            $table->integer('rajaongkir_id')->unique()->comment('ID from Raja Ongkir API');
            $table->integer('province_id')->nullable();
            $table->string('province_name')->nullable();
            $table->string('city_name');
            $table->string('type')->comment('Kota/Kabupaten');
            $table->string('postal_code')->nullable();
            $table->timestamps();
            
            $table->index(['province_id', 'rajaongkir_id']);
            $table->index('city_name');
        });

        // Create payment_channels table for Tripay methods
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('BRIVA, QRIS, etc');
            $table->string('name');
            $table->string('group')->comment('Virtual Account, E-Wallet, etc');
            $table->decimal('fee_flat', 10, 2)->default(0);
            $table->decimal('fee_percent', 5, 2)->default(0);
            $table->integer('minimum_fee')->default(0);
            $table->integer('maximum_fee')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('instructions')->nullable()->comment('JSON format');
            $table->timestamps();
            
            $table->index(['group', 'is_active']);
        });

        // Create couriers table for shipping options
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('jne, tiki, pos, etc');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->text('services')->nullable()->comment('JSON format of available services');
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
        Schema::dropIfExists('payment_channels');
        Schema::dropIfExists('shipping_areas');
    }
};
