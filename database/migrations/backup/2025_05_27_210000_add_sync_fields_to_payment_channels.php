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
        Schema::table('payment_channels', function (Blueprint $table) {
            // Tambah field untuk menandai apakah data berasal dari sinkron
            $table->boolean('is_synced')->default(false)->after('is_active')
                  ->comment('Menandai apakah data berasal dari sinkronisasi Tripay');
            $table->timestamp('last_synced_at')->nullable()->after('is_synced')
                  ->comment('Waktu terakhir data disinkronisasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_channels', function (Blueprint $table) {
            $table->dropColumn(['is_synced', 'last_synced_at']);
        });
    }
};
