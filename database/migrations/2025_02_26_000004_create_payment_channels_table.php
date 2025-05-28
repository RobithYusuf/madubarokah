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
            $table->string('icon_url')->nullable()->comment('URL to payment method icon');
            
            // Tambahkan kolom is_synced (yang dicari oleh aplikasi)
            $table->boolean('is_synced')->default(false)->comment('Flag to indicate if channel has been synced');
            $table->timestamp('last_synced_at')->nullable()->comment('Last time this channel was synced with provider');
            $table->timestamps();
            
            $table->index(['group', 'is_active']);
            $table->index('is_synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_channels');
    }
};
