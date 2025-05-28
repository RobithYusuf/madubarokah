<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_transaksi')->constrained('transaksi')->onDelete('cascade');
            $table->integer('origin_city_id')->nullable();
            $table->integer('destination_province_id')->nullable();
            $table->integer('destination_city_id')->nullable();
            $table->string('kurir');
            $table->string('layanan');
            $table->decimal('biaya', 10, 2);
            $table->string('resi')->nullable();
            $table->enum('status', [
                'menunggu_pembayaran',
                'diproses', 
                'dikirim', 
                'diterima',
                'dibatalkan'
            ])->default('menunggu_pembayaran');
            $table->integer('weight')->comment('in grams');
            $table->string('service_code')->nullable()->comment('REG, OKE, etc');
            $table->string('etd')->nullable()->comment('estimated delivery time');
            $table->text('courier_info')->nullable()->comment('JSON format for full courier details');
            $table->timestamps();
            
            $table->index(['origin_city_id', 'destination_city_id']);
            $table->index('weight');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
