<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('import_manager')->after('name');
            }
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'avatar_icon')) {
                $table->string('avatar_icon')->default('🏢')->after('company_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_filter(['role', 'company_name', 'avatar_icon'], function ($col) {
                return Schema::hasColumn('users', $col);
            }));
        });
    }
};
