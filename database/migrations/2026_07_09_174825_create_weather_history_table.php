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
    Schema::create('weather_history', function (Blueprint $table) {
        $table->id();
        $table->foreignId('country_id')->constrained()->cascadeOnDelete();
        $table->decimal('temperature', 6, 2)->nullable();
        $table->decimal('rainfall', 8, 2)->nullable();
        $table->decimal('wind_speed', 6, 2)->nullable();
        $table->decimal('storm_risk', 5, 2)->nullable();
        $table->string('weather_condition')->nullable();
        $table->timestamp('fetched_at');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_history');
    }
};
