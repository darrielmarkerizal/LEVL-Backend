<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Models\XpSource;

class GamificationDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎮 Starting Gamification Data Seeder...');

        if (UserGamificationStat::count() > 0) {
            $this->command->info('ℹ️  Gamification stats already seeded. Skipping to prevent XP inflation.');

            return;
        }

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No users found. Please seed users first.');

            return;
        }

        $this->command->info("Found {$users->count()} users to process.");

        $students = $users->filter(function ($user) {
            return $user->hasRole('Student');
        });

        $nonStudents = $users->reject(function ($user) {
            return $user->hasRole('Student');
        });

        $badges = Badge::where('active', true)->get();
        $xpSources = XpSource::where('is_active', true)->get();
        $levelConfigs = LevelConfig::orderBy('level')->get();

        if ($badges->isEmpty()) {
            $this->command->warn('⚠️  No badges found. Please seed badges first.');
        }

        if ($xpSources->isEmpty()) {
            $this->command->warn('⚠️  No XP sources found. Please seed XP sources first.');
        }

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($students as $student) {
            $this->seedStudentGamification($student, $badges, $xpSources, $levelConfigs);
            $progressBar->advance();
        }

        foreach ($nonStudents as $user) {
            $this->seedNonStudentGamification($user);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);
        $this->command->info('✅ Gamification data seeding completed!');
        $this->command->info("   - Students with data: {$students->count()}");
        $this->command->info("   - Non-students with empty stats: {$nonStudents->count()}");
    }

    private function seedNonStudentGamification(User $user): void
    {

        if (UserGamificationStat::where('user_id', $user->id)->exists()) {
            return;
        }

        UserGamificationStat::create([
            'user_id' => $user->id,
            'total_xp' => 0,
            'global_level' => 1,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_activity_date' => now(),
            'stats_updated_at' => now(),
        ]);
    }

    private function seedStudentGamification(
        User $student,
        $badges,
        $xpSources,
        $levelConfigs
    ): void {

        if (UserGamificationStat::where('user_id', $student->id)->exists()) {
            return;
        }

        $totalXp = rand(0, 50000);

        $level = $this->calculateLevel($totalXp, $levelConfigs);

        $currentStreak = rand(0, 30);
        $longestStreak = max($currentStreak, rand($currentStreak, 60));

        $stat = UserGamificationStat::updateOrCreate(
            ['user_id' => $student->id],
            [
                'total_xp' => $totalXp,
                'global_level' => $level,
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
                'last_activity_date' => now()->subDays(rand(0, 7)),
                'stats_updated_at' => now(),
            ]
        );

        if (! $xpSources->isEmpty() && $totalXp > 0) {
            $this->generateXpHistory($student, $totalXp, $xpSources, $level);
        }

        if (! $badges->isEmpty()) {
            $this->awardRandomBadges($student, $badges, $level);
        }
    }

    private function calculateLevel(int $totalXp, $levelConfigs): int
    {
        if ($levelConfigs->isEmpty()) {

            $level = 1;
            $xpForNextLevel = 100;
            $accumulatedXp = 0;

            while ($accumulatedXp + $xpForNextLevel <= $totalXp) {
                $accumulatedXp += $xpForNextLevel;
                $level++;
                $xpForNextLevel = (int) (100 * pow(1.1, $level - 1));
            }

            return $level;
        }

        $level = 1;
        foreach ($levelConfigs as $config) {
            if ($totalXp >= $config->xp_required) {
                $level = $config->level;
            } else {
                break;
            }
        }

        return $level;
    }

    private function generateXpHistory(User $student, int $totalXp, $xpSources, int $finalLevel): void
    {
        $remainingXp = $totalXp;
        $currentLevel = 1;

        $transactionCount = rand(5, min(20, (int) ($totalXp / 10)));

        $baseTimestamp = now()->timestamp;

        $sourceTypes = ['lesson', 'assignment', 'system'];

        $timestamps = $this->generateSpreadTimestamps($transactionCount, 90);

        for ($i = 0; $i < $transactionCount && $remainingXp > 0; $i++) {
            $xpSource = $xpSources->random();

            $xpAmount = rand(
                max(1, (int) ($xpSource->xp_amount * 0.5)),
                min($remainingXp, (int) ($xpSource->xp_amount * 3))
            );

            $oldLevel = $currentLevel;
            $newLevel = $this->calculateLevelFromXp($totalXp - $remainingXp + $xpAmount);
            $triggeredLevelUp = $newLevel > $oldLevel;

            if ($triggeredLevelUp) {
                $currentLevel = $newLevel;
            }

            $uniqueSourceId = $baseTimestamp + ($student->id * 1000) + $i;

            $reason = $this->mapXpSourceToReason($xpSource->code);

            $sourceType = $this->determineSourceType($xpSource->code);

            try {
                Point::create([
                    'user_id' => $student->id,
                    'source_type' => $sourceType,
                    'source_id' => $uniqueSourceId,
                    'points' => $xpAmount,
                    'reason' => $reason,
                    'description' => $xpSource->description,
                    'xp_source_code' => $xpSource->code,
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel,
                    'triggered_level_up' => $triggeredLevelUp,
                    'metadata' => [
                        'seeded' => true,
                        'source_name' => $xpSource->name,
                        'activity_type' => $this->getActivityType($xpSource->code),
                    ],
                    'created_at' => $timestamps[$i],
                    'updated_at' => $timestamps[$i],
                ]);

                $remainingXp -= $xpAmount;
            } catch (\Exception $e) {

                continue;
            }
        }
    }

    private function generateSpreadTimestamps(int $count, int $days): array
    {
        $timestamps = [];
        $now = now();

        for ($i = 0; $i < $count; $i++) {
            $daysAgo = rand(0, $days);
            $hoursAgo = rand(0, 23);
            $minutesAgo = rand(0, 59);

            $timestamps[] = $now->copy()
                ->subDays($daysAgo)
                ->subHours($hoursAgo)
                ->subMinutes($minutesAgo);
        }

        usort($timestamps, function ($a, $b) {
            return $a->timestamp <=> $b->timestamp;
        });

        return $timestamps;
    }

    private function determineSourceType(string $xpSourceCode): string
    {
        return match ($xpSourceCode) {
            'lesson_completed' => 'lesson',
            'assignment_submitted', 'perfect_score', 'first_submission' => 'assignment',
            'quiz_passed', 'quiz_completed' => 'attempt',
            default => 'system',
        };
    }

    private function getActivityType(string $xpSourceCode): string
    {
        return match ($xpSourceCode) {
            'lesson_completed' => 'learning',
            'assignment_submitted', 'perfect_score', 'first_submission' => 'assignment',
            'quiz_passed', 'quiz_completed' => 'assessment',
            'forum_post_created', 'forum_reply_created', 'forum_liked' => 'social',
            'daily_login', 'streak_7_days', 'streak_30_days' => 'engagement',
            'level_up_bonus' => 'reward',
            default => 'other',
        };
    }

    private function mapXpSourceToReason(string $xpSourceCode): string
    {
        return match ($xpSourceCode) {
            'lesson_completed' => 'lesson_completed',
            'assignment_submitted' => 'assignment_submitted',
            'quiz_passed' => 'quiz_passed',
            'perfect_score' => 'perfect_score',
            'first_submission' => 'first_submission',
            'forum_post_created' => 'forum_post',
            'forum_reply_created' => 'forum_reply',
            'forum_liked' => 'reaction_received',
            'daily_login', 'streak_7_days', 'streak_30_days' => 'daily_streak',
            'level_up_bonus' => 'bonus',
            default => 'completion',
        };
    }

    private function calculateLevelFromXp(int $xp): int
    {
        $level = 1;
        $xpForNextLevel = 100;
        $accumulatedXp = 0;

        while ($accumulatedXp + $xpForNextLevel <= $xp) {
            $accumulatedXp += $xpForNextLevel;
            $level++;
            $xpForNextLevel = (int) (100 * pow(1.1, $level - 1));
        }

        return $level;
    }

    private function awardRandomBadges(User $student, $badges, int $level): void
    {
        $facts = $this->studentLearningFacts($student);

        $eligibleBadges = $badges->filter(
            fn (Badge $badge) => $this->studentQualifiesForBadge($badge, $facts, $level)
        )->values();

        if ($eligibleBadges->isEmpty()) {
            return;
        }

        $maxBadges = min($eligibleBadges->count(), (int) ($level / 2) + rand(0, 5));
        if ($maxBadges === 0) {
            return;
        }

        $badgesToAward = $eligibleBadges->random($maxBadges);
        if (! $badgesToAward instanceof \Illuminate\Support\Collection) {
            $badgesToAward = collect([$badgesToAward]);
        }

        $timestamps = $this->generateSpreadTimestamps($badgesToAward->count(), 90);
        $userBadges = [];
        $index = 0;

        foreach ($badgesToAward as $badge) {
            if (UserBadge::where('user_id', $student->id)
                ->where('badge_id', $badge->id)
                ->exists()) {
                continue;
            }

            $earnedAt = $timestamps[$index];

            $userBadges[] = [
                'user_id' => $student->id,
                'badge_id' => $badge->id,
                'earned_at' => $earnedAt,
                'created_at' => $earnedAt,
                'updated_at' => $earnedAt,
            ];

            $index++;
        }

        if (! empty($userBadges)) {
            UserBadge::insert($userBadges);
        }
    }

    private function studentLearningFacts(User $student): array
    {
        return [
            'lessons_completed' => DB::table('lesson_progress')
                ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
                ->where('enrollments.user_id', $student->id)
                ->where('lesson_progress.status', 'completed')
                ->count(),
            'quizzes_passed' => DB::table('quiz_submissions')
                ->join('quizzes', 'quiz_submissions.quiz_id', '=', 'quizzes.id')
                ->where('quiz_submissions.user_id', $student->id)
                ->whereIn('quiz_submissions.status', ['graded', 'submitted'])
                ->whereColumn('quiz_submissions.final_score', '>=', 'quizzes.passing_grade')
                ->count(),
            'perfect_quiz_scores' => DB::table('quiz_submissions')
                ->where('user_id', $student->id)
                ->where('final_score', '>=', 100)
                ->count(),
            'assignments_submitted' => DB::table('submissions')
                ->where('user_id', $student->id)
                ->whereIn('status', ['submitted', 'graded'])
                ->count(),
            'courses_completed' => DB::table('enrollments')
                ->where('user_id', $student->id)
                ->where('status', 'completed')
                ->count(),
            'forum_posts' => DB::table('threads')
                ->where('author_id', $student->id)
                ->count() + DB::table('replies')
                ->where('author_id', $student->id)
                ->count(),
        ];
    }

    private function studentQualifiesForBadge(Badge $badge, array $facts, int $level): bool
    {
        $code = strtolower((string) $badge->code);
        $threshold = max(1, (int) $badge->threshold);

        return match (true) {
            str_contains($code, 'lesson') => $facts['lessons_completed'] >= $threshold,
            str_contains($code, 'quiz') && str_contains($code, 'perfect') => $facts['perfect_quiz_scores'] >= $threshold,
            str_contains($code, 'quiz') => $facts['quizzes_passed'] >= $threshold,
            str_contains($code, 'assignment') || str_contains($code, 'submission') => $facts['assignments_submitted'] >= $threshold,
            str_contains($code, 'course') && str_contains($code, 'complete') => $facts['courses_completed'] >= $threshold,
            str_contains($code, 'forum') || str_contains($code, 'post') || str_contains($code, 'reply') => $facts['forum_posts'] >= $threshold,
            str_contains($code, 'level') || str_contains($code, 'milestone') => $level >= $threshold,
            default => array_sum($facts) > 0,
        };
    }
}
