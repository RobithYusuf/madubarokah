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
    }
};
