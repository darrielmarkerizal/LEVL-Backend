<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE points DROP CONSTRAINT IF EXISTS points_source_type_check");
                DB::statement("ALTER TABLE points ADD CONSTRAINT points_source_type_check CHECK (source_type::text IN ('lesson', 'assignment', 'attempt', 'challenge', 'system'))");
            } else {
                $table->enum('source_type', ['lesson', 'assignment', 'attempt', 'challenge', 'system'])->default('system')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE points DROP CONSTRAINT IF EXISTS points_source_type_check");
                DB::statement("ALTER TABLE points ADD CONSTRAINT points_source_type_check CHECK (source_type::text IN ('lesson', 'assignment', 'attempt', 'system'))");
            } else {
                $table->enum('source_type', ['lesson', 'assignment', 'attempt', 'system'])->default('system')->change();
            }
        });
    }
};
