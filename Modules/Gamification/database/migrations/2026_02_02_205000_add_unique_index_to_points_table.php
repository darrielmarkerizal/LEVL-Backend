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
            // First drop existing non-unique index if exists (just in case)
            // $table->dropIndex(['user_id', 'source_type', 'source_id', 'reason']);
            
            // Add unique index for strict duplicate prevention
            $table->unique(['user_id', 'source_type', 'source_id', 'reason'], 'points_unique_transaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            $table->dropUnique('points_unique_transaction');
        });
    }
};
