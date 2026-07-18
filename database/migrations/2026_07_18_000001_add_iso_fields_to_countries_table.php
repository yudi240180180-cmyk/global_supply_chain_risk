<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (! Schema::hasColumn('countries', 'iso2')) {
                $table->string('iso2', 2)->nullable()->after('code');
            }
            if (! Schema::hasColumn('countries', 'iso3')) {
                $table->string('iso3', 3)->nullable()->after('iso2');
            }
            if (! Schema::hasColumn('countries', 'population')) {
                $table->bigInteger('population')->nullable()->after('iso3');
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['iso2', 'iso3', 'population']);
        });
    }
};
