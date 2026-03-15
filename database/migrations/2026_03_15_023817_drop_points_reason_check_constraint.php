<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the CHECK constraint on reason column
        DB::statement('ALTER TABLE points DROP CONSTRAINT IF EXISTS points_reason_check');
        
        // Drop the CHECK constraint on source_type column if it exists
        DB::statement('ALTER TABLE points DROP CONSTRAINT IF EXISTS points_source_type_check');
    }

    public function down(): void
    {
        // We don't recreate the constraints in down() because we want to keep the flexibility
        // The original constraints were too restrictive for the new XP system
    }
};
