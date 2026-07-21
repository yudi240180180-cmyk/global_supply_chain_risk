<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->decimal('ocean_freight', 15, 2)->default(0);
            $table->decimal('insurance', 15, 2)->default(0);
            $table->decimal('import_tax', 15, 2)->default(0);
            $table->decimal('currency_adjustment', 15, 2)->default(0);
            $table->decimal('handling_fee', 15, 2)->default(0);
            $table->decimal('port_charges', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('currency_code')->default('USD');
            $table->decimal('cargo_value', 15, 2)->nullable(); // total value for insurance calc
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_costs');
    }
};
