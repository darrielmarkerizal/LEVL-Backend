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
        // Step 1: Create PostgreSQL ENUM type if not exists
        DB::statement("DO $$ BEGIN
            CREATE TYPE user_status AS ENUM ('pending', 'active', 'inactive', 'banned');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // Step 2: Drop the old CHECK constraint
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check');

        // Step 3: Drop default value temporarily
        DB::statement('ALTER TABLE users ALTER COLUMN status DROP DEFAULT');

        // Step 4: Convert column to ENUM type
        // Using USING clause to cast existing VARCHAR values to ENUM
        DB::statement("ALTER TABLE users ALTER COLUMN status TYPE user_status USING status::text::user_status");

        // Step 5: Set new default value with ENUM type
        DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'pending'::user_status");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop default value
        DB::statement('ALTER TABLE users ALTER COLUMN status DROP DEFAULT');

        // Step 2: Convert back to VARCHAR
        DB::statement("ALTER TABLE users ALTER COLUMN status TYPE VARCHAR(255) USING status::text");

        // Step 3: Set VARCHAR default value
        DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'pending'");

        // Step 4: Re-add CHECK constraint
        DB::statement("
            ALTER TABLE users 
            ADD CONSTRAINT users_status_check 
            CHECK (status IN ('pending', 'active', 'inactive', 'banned'))
        ");

        // Step 5: Drop ENUM type
        DB::statement('DROP TYPE IF EXISTS user_status');
    }
};
