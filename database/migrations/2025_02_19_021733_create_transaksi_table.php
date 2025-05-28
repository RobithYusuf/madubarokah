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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_ref')->unique()->nullable();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->string('tripay_reference')->nullable();
            $table->timestamp('tanggal_transaksi')->useCurrent();
            $table->timestamp('expired_time')->nullable();
            $table->decimal('total_harga', 10, 2);
            $table->enum('status', [
                'pending',
                'dibayar',
                'berhasil',
                'dikirim',
                'selesai',
                'batal',
                'gagal',
                'expired',
                'refund'
            ])->default('pending');

            $table->string('nama_penerima')->nullable();  // Kolom yang hilang
            $table->string('telepon_penerima')->nullable(); // Kolom yang hilang
            $table->text('alamat_pengiriman')->nullable(); // Kolom yang hilang
            $table->text('catatan')->nullable(); // Kolom yang hilang

            $table->string('callback_url')->nullable();
            $table->string('return_url')->nullable();
            $table->decimal('fee_merchant', 10, 2)->default(0);
            $table->decimal('fee_customer', 10, 2)->default(0);
            $table->text('callback_data')->nullable()->comment('JSON callback from Tripay');
            $table->timestamps();

            $table->index('merchant_ref');
            $table->index('tripay_reference'); // Tambahkan index untuk kolom baru
            $table->index('expired_time');
            $table->index(['id_user', 'status']);
            $table->index(['created_at', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
