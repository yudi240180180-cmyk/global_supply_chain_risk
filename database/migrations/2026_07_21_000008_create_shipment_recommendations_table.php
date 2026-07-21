<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->enum('recommendation_type', ['proceed', 'delay', 'reroute', 'cancel', 'monitor']);
            $table->string('title');
            $table->text('message');
            $table->json('risk_factors')->nullable(); // JSON array of risk factor details
            $table->integer('delay_hours')->nullable(); // if recommendation is delay
            $table->timestamp('generated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_recommendations');
    }
};
