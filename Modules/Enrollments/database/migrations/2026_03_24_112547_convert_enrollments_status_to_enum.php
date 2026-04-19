<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        
        
        
        DB::statement("DO $$ BEGIN
            CREATE TYPE enrollment_status AS ENUM ('pending', 'active', 'completed', 'cancelled');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        
        
        
        
        
        DB::statement('ALTER TABLE enrollments DROP CONSTRAINT IF EXISTS enrollments_status_check');
        
        
        DB::statement('ALTER TABLE enrollments ALTER COLUMN status DROP DEFAULT');
        
        
        DB::statement("ALTER TABLE enrollments ALTER COLUMN status TYPE enrollment_status USING status::text::enrollment_status");
        
        
        DB::statement("ALTER TABLE enrollments ALTER COLUMN status SET DEFAULT 'active'::enrollment_status");
    }

    
    public function down(): void
    {
        
        
        
        
        DB::statement('ALTER TABLE enrollments ALTER COLUMN status DROP DEFAULT');
        DB::statement("ALTER TABLE enrollments ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
        DB::statement("ALTER TABLE enrollments ALTER COLUMN status SET DEFAULT 'active'");
        DB::statement("
            ALTER TABLE enrollments 
            ADD CONSTRAINT enrollments_status_check 
            CHECK (status IN ('pending', 'active', 'completed', 'cancelled'))
        ");

        
        
        
        
        DB::statement('DROP TYPE IF EXISTS enrollment_status');
    }
};
