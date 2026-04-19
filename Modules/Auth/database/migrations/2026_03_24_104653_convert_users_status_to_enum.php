<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        DB::statement("DO $$ BEGIN
            CREATE TYPE user_status AS ENUM ('pending', 'active', 'inactive', 'banned');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check');

        
        DB::statement('ALTER TABLE users ALTER COLUMN status DROP DEFAULT');

        
        
        DB::statement("ALTER TABLE users ALTER COLUMN status TYPE user_status USING status::text::user_status");

        
        DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'pending'::user_status");
    }

    
    public function down(): void
    {
        
        DB::statement('ALTER TABLE users ALTER COLUMN status DROP DEFAULT');

        
        DB::statement("ALTER TABLE users ALTER COLUMN status TYPE VARCHAR(255) USING status::text");

        
        DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'pending'");

        
        DB::statement("
            ALTER TABLE users 
            ADD CONSTRAINT users_status_check 
            CHECK (status IN ('pending', 'active', 'inactive', 'banned'))
        ");

        
        DB::statement('DROP TYPE IF EXISTS user_status');
    }
};
