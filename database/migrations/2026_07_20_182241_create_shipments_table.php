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
    Schema::create('shipments', function (Blueprint $table) {

        $table->id();

        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();

        $table->foreignId('origin_port_id')
            ->constrained('ports')
            ->cascadeOnDelete();

        $table->foreignId('destination_port_id')
            ->constrained('ports')
            ->cascadeOnDelete();

        $table->string('cargo_name');

        $table->decimal('cargo_weight',12,2);

        $table->integer('container_count');

        $table->enum('container_type',[
            '20FT',
            '40FT',
            '40HC'
        ]);

        $table->decimal('shipping_cost',15,2)->nullable();

        $table->integer('estimated_days')->nullable();

        $table->decimal('distance_km',12,2)->nullable();

        $table->enum('status',[
            'Planning',
            'In Transit',
            'Arrived',
            'Delayed',
            'Cancelled'
        ])->default('Planning');

        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
