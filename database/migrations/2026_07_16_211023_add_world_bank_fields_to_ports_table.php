<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->string('locode')->nullable()->after('name');
            $table->string('status')->nullable()->after('port_type');
            $table->string('function')->nullable()->after('status');
            $table->double('outflows')->nullable()->after('function');
        });
    }

    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn([
                'locode',
                'status',
                'function',
                'outflows'
            ]);
        });
    }
};