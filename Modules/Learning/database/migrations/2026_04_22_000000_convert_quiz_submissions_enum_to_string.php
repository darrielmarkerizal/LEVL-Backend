<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE quiz_submissions DROP CONSTRAINT IF EXISTS quiz_submissions_status_check');
        DB::statement('ALTER TABLE quiz_submissions ALTER COLUMN status DROP DEFAULT');
        DB::statement("ALTER TABLE quiz_submissions ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
        DB::statement("ALTER TABLE quiz_submissions ALTER COLUMN status SET DEFAULT 'draft'");

        DB::statement('ALTER TABLE quiz_submissions DROP CONSTRAINT IF EXISTS quiz_submissions_grading_status_check');
        DB::statement('ALTER TABLE quiz_submissions ALTER COLUMN grading_status DROP DEFAULT');
        DB::statement("ALTER TABLE quiz_submissions ALTER COLUMN grading_status TYPE VARCHAR(255) USING grading_status::text");
        DB::statement("ALTER TABLE quiz_submissions ALTER COLUMN grading_status SET DEFAULT 'pending'");
    }

    public function down(): void
    {
    }
};
