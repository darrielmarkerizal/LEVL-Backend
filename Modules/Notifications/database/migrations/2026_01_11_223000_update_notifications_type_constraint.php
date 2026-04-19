<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        $oldValues = [
            'system', 'assignment', 'assessment', 'grading', 'gamification', 'news', 'custom',
            'course_completed', 'enrollment', 'forum_reply_to_thread', 'forum_reply_to_reply',
        ];

        
        $newValues = array_merge($oldValues, [
            'assignments',
            'forum',
            'achievements',
            'course_updates',
            'promotions',
            'schedule_reminder',
        ]);

        $allowed = implode("','", $newValues);

        
        DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type::text = ANY (ARRAY['$allowed'::character varying]::text[]))");
    }

    
    public function down(): void
    {
        
        
        
    }
};
