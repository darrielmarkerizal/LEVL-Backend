<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // 1. CREATE POSTGRESQL ENUM TYPES
        // ============================================
        
        // Create assignment_status enum
        DB::statement("DO $$ BEGIN
            CREATE TYPE assignment_status AS ENUM ('draft', 'published', 'archived');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // Create review_mode enum
        DB::statement("DO $$ BEGIN
            CREATE TYPE review_mode AS ENUM ('immediate', 'manual', 'deferred', 'hidden');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // ============================================
        // 2. CONVERT STATUS COLUMN
        // ============================================
        
        // Drop CHECK constraint
        DB::statement('ALTER TABLE assignments DROP CONSTRAINT IF EXISTS assignments_status_check');
        
        // Drop default value
        DB::statement('ALTER TABLE assignments ALTER COLUMN status DROP DEFAULT');
        
        // Convert column to ENUM type
        DB::statement("ALTER TABLE assignments ALTER COLUMN status TYPE assignment_status USING status::text::assignment_status");
        
        // Set new default value
        DB::statement("ALTER TABLE assignments ALTER COLUMN status SET DEFAULT 'draft'::assignment_status");

        // ============================================
        // 3. CONVERT REVIEW_MODE COLUMN
        // ============================================
        
        // Drop default value
        DB::statement('ALTER TABLE assignments ALTER COLUMN review_mode DROP DEFAULT');
        
        // Convert column to ENUM type
        DB::statement("ALTER TABLE assignments ALTER COLUMN review_mode TYPE review_mode USING review_mode::text::review_mode");
        
        // Set new default value
        DB::statement("ALTER TABLE assignments ALTER COLUMN review_mode SET DEFAULT 'immediate'::review_mode");

        // ============================================
        // 4. REMOVE RANDOMIZATION_TYPE COLUMN
        // ============================================
        
        // Drop the randomization_type column
        DB::statement('ALTER TABLE assignments DROP COLUMN IF EXISTS randomization_type');
        
        // Drop the question_bank_count column (related to randomization)
        DB::statement('ALTER TABLE assignments DROP COLUMN IF EXISTS question_bank_count');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ============================================
        // 1. RESTORE RANDOMIZATION_TYPE COLUMN
        // ============================================
        
        DB::statement("ALTER TABLE assignments ADD COLUMN randomization_type VARCHAR(20) NOT NULL DEFAULT 'static'");
        DB::statement("ALTER TABLE assignments ADD COLUMN question_bank_count INTEGER NULL");

        // ============================================
        // 2. REVERT REVIEW_MODE COLUMN
        // ============================================
        
        DB::statement('ALTER TABLE assignments ALTER COLUMN review_mode DROP DEFAULT');
        DB::statement("ALTER TABLE assignments ALTER COLUMN review_mode TYPE VARCHAR(20) USING review_mode::text");
        DB::statement("ALTER TABLE assignments ALTER COLUMN review_mode SET DEFAULT 'immediate'");

        // ============================================
        // 3. REVERT STATUS COLUMN
        // ============================================
        
        DB::statement('ALTER TABLE assignments ALTER COLUMN status DROP DEFAULT');
        DB::statement("ALTER TABLE assignments ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
        DB::statement("ALTER TABLE assignments ALTER COLUMN status SET DEFAULT 'draft'");
        DB::statement("
            ALTER TABLE assignments 
            ADD CONSTRAINT assignments_status_check 
            CHECK (status IN ('draft', 'published', 'archived'))
        ");

        // ============================================
        // 4. DROP ENUM TYPES
        // ============================================
        
        DB::statement('DROP TYPE IF EXISTS assignment_status');
        DB::statement('DROP TYPE IF EXISTS review_mode');
    }
};
