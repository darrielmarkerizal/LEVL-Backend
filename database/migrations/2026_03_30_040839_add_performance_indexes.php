<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Frequently queried foreign key combinations
        if (! $this->indexExists('submissions', 'idx_submissions_assignment_user')) {
            DB::statement('CREATE INDEX idx_submissions_assignment_user ON submissions(assignment_id, user_id)');
        }

        if (! $this->indexExists('quiz_submissions', 'idx_quiz_submissions_quiz_user')) {
            DB::statement('CREATE INDEX idx_quiz_submissions_quiz_user ON quiz_submissions(quiz_id, user_id)');
        }

        if (! $this->indexExists('lesson_progress', 'idx_lesson_progress_enrollment_lesson')) {
            DB::statement('CREATE INDEX idx_lesson_progress_enrollment_lesson ON lesson_progress(enrollment_id, lesson_id)');
        }

        if (! $this->indexExists('user_badges', 'idx_user_badges_user_badge')) {
            DB::statement('CREATE INDEX idx_user_badges_user_badge ON user_badges(user_id, badge_id)');
        }

        // Timestamp queries for sorting and filtering
        if (! $this->indexExists('enrollments', 'idx_enrollments_enrolled_at')) {
            DB::statement('CREATE INDEX idx_enrollments_enrolled_at ON enrollments(enrolled_at)');
        }

        if (! $this->indexExists('submissions', 'idx_submissions_submitted_at')) {
            DB::statement('CREATE INDEX idx_submissions_submitted_at ON submissions(submitted_at)');
        }

        if (! $this->indexExists('points', 'idx_points_created_at')) {
            DB::statement('CREATE INDEX idx_points_created_at ON points(created_at)');
        }

        if (! $this->indexExists('audit_logs', 'idx_audit_logs_created_at')) {
            DB::statement('CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at)');
        }

        // Partial indexes for published content (most common queries)
        if (! $this->indexExists('assignments', 'idx_assignments_published')) {
            DB::statement("CREATE INDEX idx_assignments_published ON assignments(status) WHERE status = 'published'");
        }

        if (! $this->indexExists('quizzes', 'idx_quizzes_published')) {
            DB::statement("CREATE INDEX idx_quizzes_published ON quizzes(status) WHERE status = 'published'");
        }

        if (! $this->indexExists('courses', 'idx_courses_published')) {
            DB::statement("CREATE INDEX idx_courses_published ON courses(status) WHERE status = 'published'");
        }

        if (! $this->indexExists('lessons', 'idx_lessons_published')) {
            DB::statement("CREATE INDEX idx_lessons_published ON lessons(status) WHERE status = 'published'");
        }

        // Composite indexes for common query patterns
        if (! $this->indexExists('enrollments', 'idx_enrollments_user_status')) {
            DB::statement('CREATE INDEX idx_enrollments_user_status ON enrollments(user_id, status)');
        }

        if (! $this->indexExists('grades', 'idx_grades_user_source')) {
            DB::statement('CREATE INDEX idx_grades_user_source ON grades(user_id, source_type, source_id)');
        }

        // User notifications index (using user_notifications table)
        if (Schema::hasTable('user_notifications') && ! $this->indexExists('user_notifications', 'idx_user_notifications_user_created')) {
            DB::statement('CREATE INDEX idx_user_notifications_user_created ON user_notifications(user_id, created_at DESC)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'submissions' => ['idx_submissions_assignment_user', 'idx_submissions_submitted_at'],
            'quiz_submissions' => ['idx_quiz_submissions_quiz_user'],
            'lesson_progress' => ['idx_lesson_progress_enrollment_lesson'],
            'user_badges' => ['idx_user_badges_user_badge'],
            'enrollments' => ['idx_enrollments_enrolled_at', 'idx_enrollments_user_status'],
            'points' => ['idx_points_created_at'],
            'audit_logs' => ['idx_audit_logs_created_at'],
            'assignments' => ['idx_assignments_published'],
            'quizzes' => ['idx_quizzes_published'],
            'courses' => ['idx_courses_published'],
            'lessons' => ['idx_lessons_published'],
            'grades' => ['idx_grades_user_source'],
            'user_notifications' => ['idx_user_notifications_user_created'],
        ];

        foreach ($indexes as $table => $indexList) {
            foreach ($indexList as $index) {
                if ($this->indexExists($table, $index)) {
                    DB::statement("DROP INDEX IF EXISTS {$index}");
                }
            }
        }
    }

    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $result = DB::selectOne(
            "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
            [$table, $index]
        );

        return $result !== null;
    }
};
