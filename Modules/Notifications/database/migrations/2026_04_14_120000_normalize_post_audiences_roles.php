<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE varchar(32) USING role::text');
        DB::statement('DROP TYPE IF EXISTS post_audience_role');

        DB::statement("UPDATE post_audiences SET role = 'admin' WHERE role IN ('Admin', 'Superadmin')");
        DB::statement("UPDATE post_audiences SET role = 'student' WHERE role = 'Student'");
        DB::statement("UPDATE post_audiences SET role = 'instructor' WHERE role = 'Instructor'");

        DB::statement('DELETE FROM post_audiences a USING post_audiences b WHERE a.id > b.id AND a.post_id = b.post_id AND a.role = b.role');

        DB::statement("CREATE TYPE post_audience_role AS ENUM ('student', 'instructor', 'admin')");
        DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE post_audience_role USING role::post_audience_role');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE varchar(32) USING role::text');
        DB::statement('DROP TYPE IF EXISTS post_audience_role');

        DB::statement("UPDATE post_audiences SET role = 'Admin' WHERE role = 'admin'");
        DB::statement("UPDATE post_audiences SET role = 'Student' WHERE role = 'student'");
        DB::statement("UPDATE post_audiences SET role = 'Instructor' WHERE role = 'instructor'");

        DB::statement("CREATE TYPE post_audience_role AS ENUM ('Student', 'Instructor', 'Admin', 'Superadmin')");
        DB::statement('ALTER TABLE post_audiences ALTER COLUMN role TYPE post_audience_role USING role::post_audience_role');
    }
};
