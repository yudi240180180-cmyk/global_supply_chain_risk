<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->integer('sequence'); // 1 = origin, last = destination
            $table->foreignId('port_id')->constrained()->restrictOnDelete();
            $table->timestamp('eta')->nullable(); // Estimated Time Arrival at this port
            $table->timestamp('etd')->nullable(); // Estimated Time Departure from this port
            $table->decimal('distance_from_prev_km', 10, 2)->nullable();
            $table->decimal('risk_score', 5, 2)->nullable();
            $table->string('route_type')->default('recommended'); // recommended / alternative_1 / alternative_2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_routes');
    }
};
