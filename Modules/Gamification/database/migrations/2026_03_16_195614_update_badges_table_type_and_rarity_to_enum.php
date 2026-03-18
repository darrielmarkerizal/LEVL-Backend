<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration updates the badge type enum to include all 7 badge types:
     * - completion (was: achievement)
     * - quality (new)
     * - speed (new)
     * - habit (new)
     * - social (new)
     * - milestone (existing)
     * - hidden (was: special)
     */
    public function up(): void
    {
        // Step 1: Update existing data to match new enum values
        // Map old values to new values
        DB::table('badges')->where('type', 'achievement')->update(['type' => 'completion']);

        // Step 2: Drop the old type enum constraint
        DB::statement('ALTER TABLE badges DROP CONSTRAINT IF EXISTS badges_type_check');

        // Step 3: Change type column to VARCHAR temporarily to allow new values
        DB::statement('ALTER TABLE badges ALTER COLUMN type TYPE VARCHAR(50)');

        // Step 4: Create new enum constraint for type with all 7 values
        DB::statement("
            ALTER TABLE badges 
            ADD CONSTRAINT badges_type_check 
            CHECK (type IN ('completion', 'quality', 'speed', 'habit', 'social', 'milestone', 'hidden'))
        ");

        // Step 5: Set default type to 'completion' for any NULL values
        DB::table('badges')->whereNull('type')->update(['type' => 'completion']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Revert type data to old values
        // Map new values back to old values
        DB::table('badges')->where('type', 'completion')->update(['type' => 'achievement']);
        DB::table('badges')->where('type', 'hidden')->update(['type' => 'achievement']);
        DB::table('badges')->whereIn('type', ['quality', 'speed', 'habit', 'social'])->update(['type' => 'achievement']);

        // Step 2: Drop new type constraint
        DB::statement('ALTER TABLE badges DROP CONSTRAINT IF EXISTS badges_type_check');

        // Step 3: Restore old type enum constraint
        DB::statement("
            ALTER TABLE badges 
            ADD CONSTRAINT badges_type_check 
            CHECK (type IN ('achievement', 'milestone', 'completion'))
        ");
    }
};
