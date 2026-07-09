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
    Schema::create('country_economics_history', function (Blueprint $table) {
        $table->id();
        $table->foreignId('country_id')->constrained()->cascadeOnDelete();
        $table->decimal('gdp', 20, 2)->nullable();
        $table->decimal('inflation', 8, 3)->nullable();
        $table->bigInteger('population')->nullable();
        $table->decimal('exports', 20, 2)->nullable();
        $table->decimal('imports', 20, 2)->nullable();
        $table->year('data_year')->nullable();
        $table->timestamp('fetched_at');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_economics_history');
    }
};
