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
    Schema::create('countries', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('code', 3)->unique();
        $table->string('region')->nullable();
        $table->string('subregion')->nullable();
        $table->string('currency_code', 10)->nullable();
        $table->string('currency_name')->nullable();
        $table->string('capital')->nullable();
        $table->decimal('latitude', 10, 6)->nullable();
        $table->decimal('longitude', 10, 6)->nullable();
        $table->json('languages')->nullable();
        $table->string('flag_url')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
