<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use a more robust approach with exception handling
        try {
            // First, try to convert the column to varchar
            // This will work whether it's currently an enum or already varchar
            DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE varchar(32)');
        } catch (\Exception $e) {
            // If that fails, try with explicit casting
            DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE varchar(32) USING role::text');
        }

        // Now safely drop the enum type if it exists
        DB::statement('DROP TYPE IF EXISTS post_audience_role CASCADE');

        // Normalize the role values
        DB::statement("UPDATE post_audiences SET role = 'admin' WHERE role IN ('Admin', 'Superadmin')");
        DB::statement("UPDATE post_audiences SET role = 'student' WHERE role = 'Student'");
        DB::statement("UPDATE post_audiences SET role = 'instructor' WHERE role = 'Instructor'");

        // Remove duplicates
        DB::statement('DELETE FROM post_audiences a USING post_audiences b WHERE a.id > b.id AND a.post_id = b.post_id AND a.role = b.role');

        // Create new enum type and convert column
        DB::statement("CREATE TYPE post_audience_role AS ENUM ('student', 'instructor', 'admin')");
        DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE post_audience_role USING role::post_audience_role');
    }

    public function down(): void
    {
        // Convert to varchar first
        DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE varchar(32) USING role::text');
        DB::statement('DROP TYPE IF EXISTS post_audience_role CASCADE');

        // Revert the role values
        DB::statement("UPDATE post_audiences SET role = 'Admin' WHERE role = 'admin'");
        DB::statement("UPDATE post_audiences SET role = 'Student' WHERE role = 'student'");
        DB::statement("UPDATE post_audiences SET role = 'Instructor' WHERE role = 'instructor'");

        // Recreate old enum type and convert column
        DB::statement("CREATE TYPE post_audience_role AS ENUM ('Student', 'Instructor', 'Admin', 'Superadmin')");
        DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE post_audience_role USING role::post_audience_role');
    }
};
