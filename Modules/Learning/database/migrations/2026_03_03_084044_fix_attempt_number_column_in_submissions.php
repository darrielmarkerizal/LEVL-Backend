<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $submissionsHasColumn = DB::selectOne(
            "SELECT EXISTS (
                SELECT 1 FROM information_schema.columns 
                WHERE table_name = 'submissions' 
                AND column_name = 'attempt_number'
            ) as exists"
        )->exists;

        if (! $submissionsHasColumn) {
            DB::statement('ALTER TABLE submissions ADD COLUMN attempt_number INTEGER NOT NULL DEFAULT 1');
            DB::statement('CREATE INDEX submissions_assignment_id_user_id_attempt_number_index ON submissions (assignment_id, user_id, attempt_number)');
        }

        $quizSubmissionsHasColumn = DB::selectOne(
            "SELECT EXISTS (
                SELECT 1 FROM information_schema.columns 
                WHERE table_name = 'quiz_submissions' 
                AND column_name = 'attempt_number'
            ) as exists"
        )->exists;

        if (! $quizSubmissionsHasColumn) {
            DB::statement('ALTER TABLE quiz_submissions ADD COLUMN attempt_number INTEGER NOT NULL DEFAULT 1');
            DB::statement('CREATE INDEX quiz_submissions_quiz_id_user_id_attempt_number_index ON quiz_submissions (quiz_id, user_id, attempt_number)');
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS submissions_assignment_id_user_id_attempt_number_index');
        DB::statement('ALTER TABLE submissions DROP COLUMN IF EXISTS attempt_number');
        
        DB::statement('DROP INDEX IF EXISTS quiz_submissions_quiz_id_user_id_attempt_number_index');
        DB::statement('ALTER TABLE quiz_submissions DROP COLUMN IF EXISTS attempt_number');
    }
};
