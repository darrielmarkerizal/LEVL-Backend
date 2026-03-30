<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create materialized view for global leaderboard
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_global_leaderboard AS
            SELECT 
                user_id,
                global_level as level,
                total_xp,
                ROW_NUMBER() OVER (ORDER BY total_xp DESC, user_id ASC) as rank
            FROM user_gamification_stats
            ORDER BY total_xp DESC, user_id ASC
        ");

        // Create unique index for fast lookups
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_mv_global_leaderboard_user ON mv_global_leaderboard (user_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_mv_global_leaderboard_rank ON mv_global_leaderboard (rank)');

        // Create materialized view for course leaderboards
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_course_leaderboards AS
            SELECT 
                user_id,
                scope_id as course_id,
                current_level as level,
                total_xp,
                ROW_NUMBER() OVER (PARTITION BY scope_id ORDER BY total_xp DESC, user_id ASC) as rank
            FROM user_scope_stats
            WHERE scope_type = 'course'
            ORDER BY scope_id, total_xp DESC, user_id ASC
        ");

        // Create indexes for fast lookups
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_mv_course_leaderboards_user_course ON mv_course_leaderboards (user_id, course_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_mv_course_leaderboards_course_rank ON mv_course_leaderboards (course_id, rank)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS mv_course_leaderboards');
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS mv_global_leaderboard');
    }
};
