<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        
        
        
        
        DB::statement("DO $$ BEGIN
            CREATE TYPE assignment_status AS ENUM ('draft', 'published', 'archived');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        
        DB::statement("DO $$ BEGIN
            CREATE TYPE review_mode AS ENUM ('immediate', 'manual', 'deferred', 'hidden');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        
        
        
        
        
        DB::statement('ALTER TABLE assignments DROP CONSTRAINT IF EXISTS assignments_status_check');
        
        
        DB::statement('ALTER TABLE assignments ALTER COLUMN status DROP DEFAULT');
        
        
        DB::statement("ALTER TABLE assignments ALTER COLUMN status TYPE assignment_status USING status::text::assignment_status");
        
        
        DB::statement("ALTER TABLE assignments ALTER COLUMN status SET DEFAULT 'draft'::assignment_status");

        
        
        
        
        
        DB::statement('ALTER TABLE assignments ALTER COLUMN review_mode DROP DEFAULT');
        
        
        DB::statement("ALTER TABLE assignments ALTER COLUMN review_mode TYPE review_mode USING review_mode::text::review_mode");
        
        
        DB::statement("ALTER TABLE assignments ALTER COLUMN review_mode SET DEFAULT 'immediate'::review_mode");

        
        
        
        
        
        DB::statement('ALTER TABLE assignments DROP COLUMN IF EXISTS randomization_type');
        
        
        DB::statement('ALTER TABLE assignments DROP COLUMN IF EXISTS question_bank_count');
    }

    
    public function down(): void
    {
        
        
        
        
        DB::statement("ALTER TABLE assignments ADD COLUMN randomization_type VARCHAR(20) NOT NULL DEFAULT 'static'");
        DB::statement("ALTER TABLE assignments ADD COLUMN question_bank_count INTEGER NULL");

        
        
        
        
        DB::statement('ALTER TABLE assignments ALTER COLUMN review_mode DROP DEFAULT');
        DB::statement("ALTER TABLE assignments ALTER COLUMN review_mode TYPE VARCHAR(20) USING review_mode::text");
        DB::statement("ALTER TABLE assignments ALTER COLUMN review_mode SET DEFAULT 'immediate'");

        
        
        
        
        DB::statement('ALTER TABLE assignments ALTER COLUMN status DROP DEFAULT');
        DB::statement("ALTER TABLE assignments ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
        DB::statement("ALTER TABLE assignments ALTER COLUMN status SET DEFAULT 'draft'");
        DB::statement("
            ALTER TABLE assignments 
            ADD CONSTRAINT assignments_status_check 
            CHECK (status IN ('draft', 'published', 'archived'))
        ");

        
        
        
        
        DB::statement('DROP TYPE IF EXISTS assignment_status');
        DB::statement('DROP TYPE IF EXISTS review_mode');
    }
};
