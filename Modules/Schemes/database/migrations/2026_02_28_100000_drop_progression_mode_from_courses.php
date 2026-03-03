<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops the progression_mode column from courses table as the system
     * now enforces sequential access for all courses by default.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('progression_mode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Restores the progression_mode column with default 'sequential' value
     * for rollback purposes.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('progression_mode', ['sequential', 'free'])
                ->default('sequential')
                ->after('enrollment_key');
        });
    }
};
