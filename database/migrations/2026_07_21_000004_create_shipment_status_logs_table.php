<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->decimal('risk_at_log', 5, 2)->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('logged_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_status_logs');
    }
};
