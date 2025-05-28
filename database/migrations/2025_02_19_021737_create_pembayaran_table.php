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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_transaksi')->constrained('transaksi')->onDelete('cascade');
            $table->string('reference')->unique();
            $table->string('metode');
            $table->string('payment_code')->nullable()->comment('VA number, QRIS code, etc');
            $table->string('payment_url')->nullable();
            $table->string('checkout_url')->nullable();
            $table->decimal('total_bayar', 10, 2);
            $table->enum('status', [
                'pending',
                'berhasil',
                'dibayar',
                'gagal',
                'expired',
                'refund',
                'canceled'
            ])->default('pending');
            $table->enum('payment_type', [
                'direct',
                'redirect',
                'virtual_account',
                'qris',
                'ewallet',
                'retail',
                'cod',
                'manual', // Tambahkan opsi 'manual' di sini
                'tripay'  // Tambahkan juga 'tripay' karena ini digunakan dalam code
            ])->default('direct');
            // Tambahkan kolom yang baru
            $table->string('callback_url')->nullable()->comment('URL for payment callback');
            
            $table->timestamp('waktu_bayar')->nullable();
            $table->timestamp('expired_time')->nullable();
            $table->text('payment_instructions')->nullable()->comment('JSON format');
            $table->text('qr_string')->nullable()->comment('QR code string for QRIS payment');
            $table->text('qr_url')->nullable()->comment('URL to QR code image');
            $table->json('callback_data')->nullable()->comment('JSON data from payment callback');
            
            $table->timestamps();

            $table->index('payment_code');
            $table->index('expired_time');
            $table->index(['status', 'payment_type']);
            $table->index('status');
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
