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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_areas');
    }
};
