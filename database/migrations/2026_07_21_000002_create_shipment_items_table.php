<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_weight', 10, 2)->nullable(); // kg
            $table->decimal('total_weight', 10, 2)->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->string('hs_code')->nullable(); // Harmonized System code
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
