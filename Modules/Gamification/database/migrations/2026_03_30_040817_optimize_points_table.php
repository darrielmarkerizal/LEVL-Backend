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
        Schema::table('points', function (Blueprint $table) {
            // Drop triggered_level_up column (computed value, not primordial data)
            if (Schema::hasColumn('points', 'triggered_level_up')) {
                $table->dropColumn('triggered_level_up');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            // Recreate triggered_level_up column
            $table->boolean('triggered_level_up')->default(false)->after('new_level');
        });
    }
};
