<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE badges DROP CONSTRAINT IF EXISTS badges_type_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding the original constraint might fail if new data is present, so we leave it empty.
    }
};
