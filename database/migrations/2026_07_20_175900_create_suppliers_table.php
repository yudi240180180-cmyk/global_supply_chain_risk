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
    Schema::create('suppliers', function (Blueprint $table) {
        $table->id();

        $table->foreignId('country_id')
            ->constrained()
            ->cascadeOnUpdate()
            ->restrictOnDelete();

        $table->foreignId('port_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        $table->string('company_name');
        $table->string('contact_person')->nullable();

        $table->string('email')->nullable();
        $table->string('phone')->nullable();

        $table->text('address')->nullable();

        $table->enum('supplier_type', [
            'Manufacturer',
            'Distributor',
            'Wholesaler',
            'Trading Company'
        ])->default('Manufacturer');

        $table->enum('risk_level', [
            'Low',
            'Medium',
            'High'
        ])->default('Low');

        $table->decimal('rating', 2, 1)->default(5.0);

        $table->enum('status', [
            'Active',
            'Inactive'
        ])->default('Active');

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
