<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'archived' value to publish_status enum
        DB::statement("ALTER TYPE publish_status ADD VALUE IF NOT EXISTS 'archived'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PostgreSQL doesn't support removing enum values directly
        // You would need to recreate the enum type if you want to remove a value
        // For now, we'll leave it as is since removing enum values is complex
    }
};
