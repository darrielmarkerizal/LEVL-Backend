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
        
        // Create course_type enum
        DB::statement("DO $$ BEGIN
            CREATE TYPE course_type AS ENUM ('okupasi', 'kluster');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // Create level_tag enum
        DB::statement("DO $$ BEGIN
            CREATE TYPE level_tag AS ENUM ('dasar', 'menengah', 'mahir');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // Create enrollment_type enum
        DB::statement("DO $$ BEGIN
            CREATE TYPE enrollment_type AS ENUM ('auto_accept', 'key_based', 'approval');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // Create course_status enum
        DB::statement("DO $$ BEGIN
            CREATE TYPE course_status AS ENUM ('draft', 'published', 'archived');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // ============================================
        // 2. CONVERT TYPE COLUMN
        // ============================================
        
        // Drop CHECK constraint
        DB::statement('ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_type_check');
        
        // Drop default value
        DB::statement('ALTER TABLE courses ALTER COLUMN type DROP DEFAULT');
        
        // Convert column to ENUM type
        DB::statement("ALTER TABLE courses ALTER COLUMN type TYPE course_type USING type::text::course_type");
        
        // Set new default value
        DB::statement("ALTER TABLE courses ALTER COLUMN type SET DEFAULT 'okupasi'::course_type");

        // ============================================
        // 3. CONVERT LEVEL_TAG COLUMN
        // ============================================
        
        // Drop CHECK constraint
        DB::statement('ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_level_tag_check');
        
        // Drop default value
        DB::statement('ALTER TABLE courses ALTER COLUMN level_tag DROP DEFAULT');
        
        // Convert column to ENUM type
        DB::statement("ALTER TABLE courses ALTER COLUMN level_tag TYPE level_tag USING level_tag::text::level_tag");
        
        // Set new default value
        DB::statement("ALTER TABLE courses ALTER COLUMN level_tag SET DEFAULT 'dasar'::level_tag");

        // ============================================
        // 4. CONVERT ENROLLMENT_TYPE COLUMN
        // ============================================
        
        // Drop CHECK constraint
        DB::statement('ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_enrollment_type_check');
        
        // Drop default value
        DB::statement('ALTER TABLE courses ALTER COLUMN enrollment_type DROP DEFAULT');
        
        // Convert column to ENUM type
        DB::statement("ALTER TABLE courses ALTER COLUMN enrollment_type TYPE enrollment_type USING enrollment_type::text::enrollment_type");
        
        // Set new default value
        DB::statement("ALTER TABLE courses ALTER COLUMN enrollment_type SET DEFAULT 'auto_accept'::enrollment_type");

        // ============================================
        // 5. CONVERT STATUS COLUMN
        // ============================================
        
        // Drop CHECK constraint
        DB::statement('ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_status_check');
        
        // Drop default value
        DB::statement('ALTER TABLE courses ALTER COLUMN status DROP DEFAULT');
        
        // Convert column to ENUM type
        DB::statement("ALTER TABLE courses ALTER COLUMN status TYPE course_status USING status::text::course_status");
        
        // Set new default value
        DB::statement("ALTER TABLE courses ALTER COLUMN status SET DEFAULT 'draft'::course_status");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ============================================
        // 1. REVERT TYPE COLUMN
        // ============================================
        
        DB::statement('ALTER TABLE courses ALTER COLUMN type DROP DEFAULT');
        DB::statement("ALTER TABLE courses ALTER COLUMN type TYPE VARCHAR(255) USING type::text");
        DB::statement("ALTER TABLE courses ALTER COLUMN type SET DEFAULT 'okupasi'");
        DB::statement("
            ALTER TABLE courses 
            ADD CONSTRAINT courses_type_check 
            CHECK (type IN ('okupasi', 'kluster'))
        ");

        // ============================================
        // 2. REVERT LEVEL_TAG COLUMN
        // ============================================
        
        DB::statement('ALTER TABLE courses ALTER COLUMN level_tag DROP DEFAULT');
        DB::statement("ALTER TABLE courses ALTER COLUMN level_tag TYPE VARCHAR(255) USING level_tag::text");
        DB::statement("ALTER TABLE courses ALTER COLUMN level_tag SET DEFAULT 'dasar'");
        DB::statement("
            ALTER TABLE courses 
            ADD CONSTRAINT courses_level_tag_check 
            CHECK (level_tag IN ('dasar', 'menengah', 'mahir'))
        ");

        // ============================================
        // 3. REVERT ENROLLMENT_TYPE COLUMN
        // ============================================
        
        DB::statement('ALTER TABLE courses ALTER COLUMN enrollment_type DROP DEFAULT');
        DB::statement("ALTER TABLE courses ALTER COLUMN enrollment_type TYPE VARCHAR(255) USING enrollment_type::text");
        DB::statement("ALTER TABLE courses ALTER COLUMN enrollment_type SET DEFAULT 'auto_accept'");
        DB::statement("
            ALTER TABLE courses 
            ADD CONSTRAINT courses_enrollment_type_check 
            CHECK (enrollment_type IN ('auto_accept', 'key_based', 'approval'))
        ");

        // ============================================
        // 4. REVERT STATUS COLUMN
        // ============================================
        
        DB::statement('ALTER TABLE courses ALTER COLUMN status DROP DEFAULT');
        DB::statement("ALTER TABLE courses ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
        DB::statement("ALTER TABLE courses ALTER COLUMN status SET DEFAULT 'draft'");
        DB::statement("
            ALTER TABLE courses 
            ADD CONSTRAINT courses_status_check 
            CHECK (status IN ('draft', 'published', 'archived'))
        ");

        // ============================================
        // 5. DROP ENUM TYPES
        // ============================================
        
        DB::statement('DROP TYPE IF EXISTS course_type');
        DB::statement('DROP TYPE IF EXISTS level_tag');
        DB::statement('DROP TYPE IF EXISTS enrollment_type');
        DB::statement('DROP TYPE IF EXISTS course_status');
    }
};
