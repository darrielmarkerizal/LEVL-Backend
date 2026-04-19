<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            
            DB::statement('ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_category_check');

            
            DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_category_check CHECK (category::text IN ('system', 'assignment', 'assessment', 'grading', 'gamification', 'news', 'custom', 'course_completed', 'course_updates', 'assignments', 'forum', 'achievements', 'enrollment', 'forum_reply_to_thread', 'forum_reply_to_reply'))");
        }
    }

    
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            
            DB::statement('ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_category_check');
            DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_category_check CHECK (category::text IN ('system', 'assignment', 'assessment', 'grading', 'gamification', 'news', 'custom', 'course_completed', 'enrollment', 'forum_reply_to_thread', 'forum_reply_to_reply'))");
        }
    }
};
