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
        // 1. CREATE POSTGRESQL ENUM TYPE
        // ============================================
        
        DB::statement("DO $$ BEGIN
            CREATE TYPE enrollment_status AS ENUM ('pending', 'active', 'completed', 'cancelled');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // ============================================
        // 2. CONVERT STATUS COLUMN
        // ============================================
        
        // Drop CHECK constraint
        DB::statement('ALTER TABLE enrollments DROP CONSTRAINT IF EXISTS enrollments_status_check');
        
        // Drop default value temporarily
        DB::statement('ALTER TABLE enrollments ALTER COLUMN status DROP DEFAULT');
        
        // Convert column to ENUM type
        DB::statement("ALTER TABLE enrollments ALTER COLUMN status TYPE enrollment_status USING status::text::enrollment_status");
        
        // Set new default value with ENUM type
        DB::statement("ALTER TABLE enrollments ALTER COLUMN status SET DEFAULT 'active'::enrollment_status");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ============================================
        // 1. REVERT STATUS COLUMN
        // ============================================
        
        DB::statement('ALTER TABLE enrollments ALTER COLUMN status DROP DEFAULT');
        DB::statement("ALTER TABLE enrollments ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
        DB::statement("ALTER TABLE enrollments ALTER COLUMN status SET DEFAULT 'active'");
        DB::statement("
            ALTER TABLE enrollments 
            ADD CONSTRAINT enrollments_status_check 
            CHECK (status IN ('pending', 'active', 'completed', 'cancelled'))
        ");

        // ============================================
        // 2. DROP ENUM TYPE
        // ============================================
        
        DB::statement('DROP TYPE IF EXISTS enrollment_status');
    }
};
