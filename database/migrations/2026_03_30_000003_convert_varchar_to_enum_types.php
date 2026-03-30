<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert varchar columns with CHECK constraints to native PostgreSQL enum types.
     * This improves storage efficiency, query performance, and data integrity.
     */
    public function up(): void
    {
        // ─── Content Module ─────────────────────────────────────────────
        $this->createEnumAndConvert('content_status', [
            'draft', 'submitted', 'in_review', 'approved', 'rejected', 'scheduled', 'published', 'archived',
        ], [
            ['table' => 'announcements', 'column' => 'status', 'default' => 'draft', 'check' => 'announcements_status_check'],
            ['table' => 'news', 'column' => 'status', 'default' => 'draft', 'check' => 'news_status_check'],
        ]);

        $this->createEnumAndConvert('priority', [
            'low', 'normal', 'high',
        ], [
            ['table' => 'announcements', 'column' => 'priority', 'default' => 'normal', 'check' => 'announcements_priority_check'],
            ['table' => 'notifications', 'column' => 'priority', 'default' => 'normal', 'check' => 'notifications_priority_check'],
        ]);

        $this->createEnumAndConvert('target_type', [
            'all', 'role', 'course',
        ], [
            ['table' => 'announcements', 'column' => 'target_type', 'default' => 'all', 'check' => 'announcements_target_type_check'],
        ]);

        // ─── Schemes Module ─────────────────────────────────────────────
        $this->createEnumAndConvert('publish_status', [
            'draft', 'published',
        ], [
            ['table' => 'lessons', 'column' => 'status', 'default' => 'draft', 'check' => 'lessons_status_check'],
            ['table' => 'units', 'column' => 'status', 'default' => 'draft', 'check' => 'units_status_check'],
        ]);

        $this->createEnumAndConvert('content_type', [
            'markdown', 'video', 'link',
        ], [
            ['table' => 'lessons', 'column' => 'content_type', 'default' => 'markdown', 'check' => 'lessons_content_type_check'],
        ]);

        $this->createEnumAndConvert('block_type', [
            'text', 'image', 'video', 'file', 'link', 'youtube', 'drive', 'embed',
        ], [
            ['table' => 'lesson_blocks', 'column' => 'block_type', 'default' => 'text', 'check' => 'lesson_blocks_block_type_check'],
        ]);

        // ─── Enrollments Module ─────────────────────────────────────────
        $this->createEnumAndConvert('progress_status', [
            'not_started', 'in_progress', 'completed',
        ], [
            ['table' => 'course_progress', 'column' => 'status', 'default' => 'not_started', 'check' => 'course_progress_status_check'],
            ['table' => 'lesson_progress', 'column' => 'status', 'default' => 'not_started', 'check' => 'lesson_progress_status_check'],
            ['table' => 'unit_progress', 'column' => 'status', 'default' => 'not_started', 'check' => 'unit_progress_status_check'],
        ]);

        // ─── Learning Module ────────────────────────────────────────────
        $this->createEnumAndConvert('submission_status', [
            'draft', 'submitted', 'graded', 'late', 'missing',
        ], [
            ['table' => 'submissions', 'column' => 'status', 'default' => 'draft', 'check' => 'submissions_status_check'],
        ]);

        $this->createEnumAndConvert('quiz_submission_status', [
            'draft', 'submitted', 'graded', 'late', 'missing',
        ], [
            ['table' => 'quiz_submissions', 'column' => 'status', 'default' => 'draft', 'check' => 'quiz_submissions_status_check'],
        ]);

        $this->createEnumAndConvert('quiz_grading_status', [
            'pending', 'partially_graded', 'waiting_for_grading', 'graded',
        ], [
            ['table' => 'quiz_submissions', 'column' => 'grading_status', 'default' => 'pending', 'check' => 'quiz_submissions_grading_status_check'],
        ]);

        $this->createEnumAndConvert('quiz_status', [
            'draft', 'published', 'archived',
        ], [
            ['table' => 'quizzes', 'column' => 'status', 'default' => 'draft', 'check' => 'quizzes_status_check'],
        ]);

        $this->createEnumAndConvert('randomization_type', [
            'static', 'random_order', 'bank',
        ], [
            ['table' => 'quizzes', 'column' => 'randomization_type', 'default' => 'static', 'check' => null],
        ]);

        $this->createEnumAndConvert('submission_type', [
            'text', 'file', 'mixed',
        ], [
            ['table' => 'assignments', 'column' => 'submission_type', 'default' => 'text', 'check' => 'assignments_submission_type_check'],
        ]);

        // ─── Gamification Module ────────────────────────────────────────
        $this->createEnumAndConvert('badge_type', [
            'completion', 'quality', 'speed', 'habit', 'social', 'milestone', 'hidden',
        ], [
            ['table' => 'badges', 'column' => 'type', 'default' => 'completion', 'check' => 'badges_type_check'],
        ]);

        $this->createEnumAndConvert('badge_rarity', [
            'common', 'uncommon', 'rare', 'epic', 'legendary',
        ], [
            ['table' => 'badges', 'column' => 'rarity', 'default' => 'common', 'check' => 'badges_rarity_check'],
        ]);

        // ─── Grading Module ─────────────────────────────────────────────
        $this->createEnumAndConvert('grade_status', [
            'pending', 'graded', 'reviewed',
        ], [
            ['table' => 'grades', 'column' => 'status', 'default' => 'graded', 'check' => 'grades_status_check'],
        ]);

        $this->createEnumAndConvert('grade_source_type', [
            'assignment', 'attempt',
        ], [
            ['table' => 'grades', 'column' => 'source_type', 'default' => null, 'check' => 'grades_source_type_check'],
        ]);

        $this->createEnumAndConvert('grade_review_status', [
            'pending', 'approved', 'rejected',
        ], [
            ['table' => 'grade_reviews', 'column' => 'status', 'default' => 'pending', 'check' => 'grade_reviews_status_check'],
        ]);

        $this->createEnumAndConvert('grading_scope_type', [
            'exercise', 'assignment',
        ], [
            ['table' => 'grading_rubrics', 'column' => 'scope_type', 'default' => null, 'check' => 'grading_rubrics_scope_type_check'],
        ]);

        // ─── Notifications Module ───────────────────────────────────────
        $this->createEnumAndConvert('notification_type', [
            'system', 'assignment', 'assessment', 'grading', 'gamification', 'news', 'custom',
            'course_completed', 'course_updates', 'assignments', 'forum', 'achievements', 'enrollment',
            'forum_reply_to_thread', 'forum_reply_to_reply', 'forum_reaction_thread', 'forum_reaction_reply',
        ], [
            ['table' => 'notifications', 'column' => 'type', 'default' => 'system', 'check' => 'notifications_type_check'],
            ['table' => 'notification_preferences', 'column' => 'category', 'default' => null, 'check' => 'notification_preferences_category_check'],
        ]);

        $this->createEnumAndConvert('notification_channel', [
            'in_app', 'email', 'push',
        ], [
            ['table' => 'notifications', 'column' => 'channel', 'default' => 'in_app', 'check' => 'notifications_channel_check'],
            ['table' => 'notification_preferences', 'column' => 'channel', 'default' => null, 'check' => 'notification_preferences_channel_check'],
            ['table' => 'post_notifications', 'column' => 'channel', 'default' => null, 'check' => 'post_notifications_channel_check'],
        ]);

        $this->createEnumAndConvert('notification_frequency', [
            'immediate', 'daily', 'weekly', 'never',
        ], [
            ['table' => 'notification_preferences', 'column' => 'frequency', 'default' => 'immediate', 'check' => 'notification_preferences_frequency_check'],
        ]);

        $this->createEnumAndConvert('post_category', [
            'announcement', 'information', 'gamification', 'warning', 'system', 'award',
        ], [
            ['table' => 'posts', 'column' => 'category', 'default' => null, 'check' => 'posts_category_check'],
        ]);

        $this->createEnumAndConvert('post_status', [
            'draft', 'scheduled', 'published',
        ], [
            ['table' => 'posts', 'column' => 'status', 'default' => 'draft', 'check' => 'posts_status_check'],
        ]);

        $this->createEnumAndConvert('reaction_type', [
            'like', 'helpful', 'solved',
        ], [
            ['table' => 'reactions', 'column' => 'type', 'default' => null, 'check' => 'reactions_type_check'],
        ]);

        $this->createEnumAndConvert('read_status', [
            'unread', 'read',
        ], [
            ['table' => 'user_notifications', 'column' => 'status', 'default' => 'unread', 'check' => 'user_notifications_status_check'],
        ]);

        $this->createEnumAndConvert('post_audience_role', [
            'Student', 'Instructor', 'Admin', 'Superadmin',
        ], [
            ['table' => 'post_audiences', 'column' => 'role', 'default' => null, 'check' => 'post_audiences_role_check'],
        ]);

        // ─── Auth Module ────────────────────────────────────────────────
        $this->createEnumAndConvert('active_status', [
            'active', 'inactive',
        ], [
            ['table' => 'categories', 'column' => 'status', 'default' => 'active', 'check' => 'categories_status_check'],
        ]);

        $this->createEnumAndConvert('certificate_status', [
            'active', 'revoked', 'expired',
        ], [
            ['table' => 'certificates', 'column' => 'status', 'default' => 'active', 'check' => 'certificates_status_check'],
        ]);

        $this->createEnumAndConvert('profile_visibility', [
            'public', 'private', 'friends_only',
        ], [
            ['table' => 'profile_privacy_settings', 'column' => 'profile_visibility', 'default' => 'public', 'check' => 'profile_privacy_settings_profile_visibility_check'],
        ]);

        // ─── Other ──────────────────────────────────────────────────────
        $this->createEnumAndConvert('setting_type', [
            'string', 'number', 'boolean', 'json',
        ], [
            ['table' => 'system_settings', 'column' => 'type', 'default' => 'string', 'check' => 'system_settings_type_check'],
        ]);
    }

    public function down(): void
    {
        // List of all enum types created in this migration
        $enumTypes = [
            'content_status', 'priority', 'target_type', 'publish_status', 'content_type',
            'block_type', 'progress_status', 'submission_status', 'quiz_submission_status',
            'quiz_grading_status', 'quiz_status', 'randomization_type', 'submission_type',
            'badge_type', 'badge_rarity', 'grade_status', 'grade_source_type',
            'grade_review_status', 'grading_scope_type', 'notification_type',
            'notification_channel', 'notification_frequency', 'post_category', 'post_status', 'reaction_type',
            'read_status', 'post_audience_role', 'active_status', 'certificate_status',
            'profile_visibility', 'setting_type',
        ];

        // Converting back to varchar would require knowing original CHECK constraints
        // which is complex. Instead, we just note that rollback is not automatically supported.
        // In practice, enum types are backward-compatible with varchar comparisons.

        foreach ($enumTypes as $type) {
            DB::statement("DROP TYPE IF EXISTS public.{$type} CASCADE");
        }
    }

    /**
     * Create a PostgreSQL enum type and convert column(s) to use it.
     */
    private function createEnumAndConvert(string $enumName, array $values, array $columns): void
    {
        // Check if enum type already exists
        $exists = DB::selectOne("SELECT 1 FROM pg_type WHERE typname = ?", [$enumName]);

        if (! $exists) {
            $quotedValues = implode(', ', array_map(fn ($v) => "'{$v}'", $values));
            DB::statement("CREATE TYPE public.{$enumName} AS ENUM ({$quotedValues})");
        }

        foreach ($columns as $col) {
            $table = $col['table'];
            $column = $col['column'];
            $default = $col['default'];
            $check = $col['check'] ?? null;
            $skipIfNotExists = $col['skip_if_not_exists'] ?? false;

            // Skip if table doesn't exist (e.g., dropped in previous migration)
            $tableExists = DB::selectOne(
                "SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?",
                [$table]
            );

            if (! $tableExists) {
                if ($skipIfNotExists) {
                    continue;
                }
                // Table should exist, skip silently
                continue;
            }

            // Check if column is already the enum type
            $colInfo = DB::selectOne(
                "SELECT udt_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ? AND column_name = ?",
                [$table, $column]
            );

            if (! $colInfo) {
                continue;
            }

            // Skip if already converted to this enum type
            if ($colInfo->udt_name === $enumName) {
                continue;
            }

            // Drop the CHECK constraint if it exists
            if ($check) {
                DB::statement("ALTER TABLE public.{$table} DROP CONSTRAINT IF EXISTS {$check}");
            }

            // Find and drop partial indexes that reference this column in their WHERE clause
            $partialIndexes = DB::select(
                "SELECT indexname, indexdef FROM pg_indexes WHERE tablename = ? AND indexdef LIKE ?",
                [$table, "%{$column}%WHERE%"]
            );
            // Also catch indexes where WHERE comes before the column reference
            $partialIndexes2 = DB::select(
                "SELECT indexname, indexdef FROM pg_indexes WHERE tablename = ? AND indexdef LIKE ?",
                [$table, "%WHERE%{$column}%"]
            );
            $allPartialIndexes = collect(array_merge($partialIndexes, $partialIndexes2))
                ->unique('indexname')
                ->values()
                ->all();

            foreach ($allPartialIndexes as $idx) {
                DB::statement("DROP INDEX IF EXISTS public.{$idx->indexname}");
            }

            // Drop default before type change (Postgres can't auto-cast varchar default to enum)
            DB::statement("ALTER TABLE public.{$table} ALTER COLUMN {$column} DROP DEFAULT");

            // Convert column type
            DB::statement("ALTER TABLE public.{$table} ALTER COLUMN {$column} TYPE public.{$enumName} USING {$column}::text::public.{$enumName}");

            // Re-set default with the enum type
            if ($default !== null) {
                DB::statement("ALTER TABLE public.{$table} ALTER COLUMN {$column} SET DEFAULT '{$default}'::public.{$enumName}");
            }

            // Recreate partial indexes with enum-compatible predicates
            foreach ($allPartialIndexes as $idx) {
                // Remove ::text cast from WHERE clause to avoid IMMUTABLE function requirement
                // Enum types can be compared directly without casting
                $newDef = preg_replace(
                    "/\({$column}\)::text\s*=\s*'([^']+)'::text/",
                    "{$column} = '$1'::{$enumName}",
                    $idx->indexdef
                );
                // Also handle cases where column is not wrapped in parentheses
                $newDef = preg_replace(
                    "/{$column}::text\s*=\s*'([^']+)'::text/",
                    "{$column} = '$1'::{$enumName}",
                    $newDef
                );
                DB::statement($newDef);
            }
        }
    }
};
