<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\UserGamificationStat;

class AwardBadgesFromUATMetricsSeeder extends Seeder
{
    public function run(): void
    {
        if (config('seeding.mode') !== 'uat') {
            return;
        }

        $this->command->info('UAT: awarding badges from measurable progress...');

        $studentIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Student')
            ->where('model_has_roles.model_type', 'Modules\\Auth\\Models\\User')
            ->pluck('users.id')
            ->all();

        $now = now();

        foreach ($studentIds as $userId) {
            $this->awardLevelMilestones((int) $userId, $now);
            $this->awardCourseFinisher((int) $userId, $now);
            $this->awardPerfectScores((int) $userId, $now);
            $this->awardForumHelper((int) $userId, $now);
        }

        $this->command->info('UAT: badge awards completed.');
    }

    private function awardLevelMilestones(int $userId, $now): void
    {
        $stat = UserGamificationStat::query()->where('user_id', $userId)->first();
        $level = $stat?->global_level ?? 1;

        foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $threshold) {
            if ($level < $threshold) {
                continue;
            }
            $badge = Badge::query()->where('code', 'level_'.$threshold.'_milestone')->first();
            if ($badge === null) {
                continue;
            }
            $this->ensureUserBadge($userId, $badge->id, $now);
        }
    }

    private function awardCourseFinisher(int $userId, $now): void
    {
        $completedUnits = DB::table('unit_progress')
            ->join('enrollments', 'unit_progress.enrollment_id', '=', 'enrollments.id')
            ->where('enrollments.user_id', $userId)
            ->where('unit_progress.status', 'completed')
            ->distinct()
            ->count('unit_progress.unit_id');

        $max = min(18, $completedUnits);
        for ($k = 1; $k <= $max; $k++) {
            $badge = Badge::query()->where('code', 'course_finisher_'.$k)->first();
            if ($badge === null) {
                continue;
            }
            $this->ensureUserBadge($userId, $badge->id, $now);
        }
    }

    private function awardPerfectScores(int $userId, $now): void
    {
        $perfectQuiz = DB::table('quiz_submissions')
            ->where('user_id', $userId)
            ->whereRaw('COALESCE(final_score, score) >= 100')
            ->exists();

        if ($perfectQuiz) {
            $badge = Badge::query()->where('code', 'perfect_quiz')->first();
            if ($badge !== null) {
                $this->ensureUserBadge($userId, $badge->id, $now);
            }
        }

        $perfectAssign = DB::table('submissions')
            ->where('user_id', $userId)
            ->where('status', 'graded')
            ->where('score', '>=', 100)
            ->exists();

        if ($perfectAssign) {
            $badge = Badge::query()->where('code', 'perfect_assignment')->first();
            if ($badge !== null) {
                $this->ensureUserBadge($userId, $badge->id, $now);
            }
        }
    }

    private function awardForumHelper(int $userId, $now): void
    {
        $replyCount = DB::table('replies')
            ->where('author_id', $userId)
            ->count();

        if ($replyCount < 5) {
            return;
        }

        $badge = Badge::query()->where('code', 'forum_helper')->first();
        if ($badge !== null) {
            $this->ensureUserBadge($userId, $badge->id, $now);
        }
    }

    private function ensureUserBadge(int $userId, int $badgeId, $now): void
    {
        $row = [
            'user_id' => $userId,
            'badge_id' => $badgeId,
            'earned_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('user_badges', 'badge_version_id')) {
            $row['badge_version_id'] = null;
        }

        DB::table('user_badges')->insertOrIgnore($row);
    }
}
