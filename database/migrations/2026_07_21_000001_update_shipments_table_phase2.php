<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('shipment_code')->nullable()->unique()->after('id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('shipment_code');
            $table->integer('quantity')->default(1)->after('container_type');
            $table->timestamp('estimated_departure')->nullable()->after('quantity');
            $table->timestamp('estimated_arrival')->nullable()->after('estimated_departure');
            $table->timestamp('actual_departure')->nullable()->after('estimated_arrival');
            $table->timestamp('actual_arrival')->nullable()->after('actual_departure');

            // Risk scores
            $table->decimal('overall_risk_score', 5, 2)->nullable()->after('actual_arrival');
            $table->enum('risk_level', ['Low', 'Medium', 'High'])->nullable()->after('overall_risk_score');
            $table->decimal('weather_risk', 5, 2)->nullable()->after('risk_level');
            $table->decimal('currency_risk', 5, 2)->nullable()->after('weather_risk');
            $table->decimal('economic_risk', 5, 2)->nullable()->after('currency_risk');
            $table->decimal('news_risk', 5, 2)->nullable()->after('economic_risk');
            $table->decimal('port_congestion_risk', 5, 2)->nullable()->after('news_risk');
            $table->text('recommendation')->nullable()->after('port_congestion_risk');

            // Update status enum to include more states
            // We can't easily modify enum in SQLite so we add a tracking_status
            $table->enum('tracking_status', [
                'Planning',
                'Ready',
                'Loading',
                'Departed',
                'At Sea',
                'Arrived',
                'Completed',
                'Delayed',
                'Cancelled',
            ])->default('Planning')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'shipment_code', 'user_id', 'quantity',
                'estimated_departure', 'estimated_arrival',
                'actual_departure', 'actual_arrival',
                'overall_risk_score', 'risk_level',
                'weather_risk', 'currency_risk', 'economic_risk',
                'news_risk', 'port_congestion_risk', 'recommendation',
                'tracking_status',
            ]);
        });
    }
};
