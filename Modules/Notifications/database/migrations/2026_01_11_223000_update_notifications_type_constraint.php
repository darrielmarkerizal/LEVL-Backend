<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all current enum values from the original migration
        $oldValues = [
            'system', 'assignment', 'assessment', 'grading', 'gamification', 'news', 'custom',
            'course_completed', 'enrollment', 'forum_reply_to_thread', 'forum_reply_to_reply',
        ];

        // Add the new values needed
        $newValues = array_merge($oldValues, [
            'assignments', 
            'forum', 
            'achievements', 
            'course_updates', 
            'promotions',
            'schedule_reminder'
        ]);

        $allowed = implode("','", $newValues);

        // PostgreSQL specific raw SQL to update the check constraint
        DB::statement("ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check");
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type::text = ANY (ARRAY['$allowed'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We can't easily reverse this without potentially invalidating data, 
        // but we can try to restore the original constraint if data allows.
        // For now, we'll leave it as is or could implement a restore logic if critical.
    }
};
