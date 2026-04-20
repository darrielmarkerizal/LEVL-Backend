<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE submissions SET state = 'pending_manual_grading' WHERE state::text = 'submitted'");
        DB::statement("UPDATE submissions SET status = 'submitted' WHERE status::text = 'missing'");

        DB::statement(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'submission_status') THEN
        IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'submission_status_new') THEN
            CREATE TYPE submission_status_new AS ENUM ('draft', 'submitted', 'graded', 'late');
        END IF;

        ALTER TABLE submissions ALTER COLUMN status DROP DEFAULT;
        ALTER TABLE submissions
            ALTER COLUMN status TYPE submission_status_new
            USING status::text::submission_status_new;
        ALTER TABLE submissions ALTER COLUMN status SET DEFAULT 'draft'::submission_status_new;

        DROP TYPE submission_status;
        ALTER TYPE submission_status_new RENAME TO submission_status;
    END IF;
END $$;
SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'submission_status') THEN
        IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'submission_status_old') THEN
            CREATE TYPE submission_status_old AS ENUM ('draft', 'submitted', 'graded', 'late', 'missing');
        END IF;

        ALTER TABLE submissions ALTER COLUMN status DROP DEFAULT;
        ALTER TABLE submissions
            ALTER COLUMN status TYPE submission_status_old
            USING status::text::submission_status_old;
        ALTER TABLE submissions ALTER COLUMN status SET DEFAULT 'draft'::submission_status_old;

        DROP TYPE submission_status;
        ALTER TYPE submission_status_old RENAME TO submission_status;
    END IF;
END $$;
SQL);
    }
};
