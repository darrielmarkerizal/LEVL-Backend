<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Models\XpSource;

class GamificationDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder can be used for:
     * 1. Fresh migration (php artisan migrate:fresh --seed)
     * 2. Populate existing students with random gamification data
     */
    public function run(): void
    {
        $this->command->info('🎮 Starting Gamification Data Seeder...');

        // Get all users (not just students)
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No users found. Please seed users first.');

            return;
        }

        $this->command->info("Found {$users->count()} users to process.");

        // Separate students and non-students
        $students = $users->filter(function ($user) {
            return $user->hasRole('Student');
        });

        $nonStudents = $users->reject(function ($user) {
            return $user->hasRole('Student');
        });

        // Get available badges and XP sources
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

        // Seed students with random data
        foreach ($students as $student) {
            $this->seedStudentGamification($student, $badges, $xpSources, $levelConfigs);
            $progressBar->advance();
        }

        // Create empty stats for non-students (admins, instructors, etc.)
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

    /**
     * Create empty gamification stats for non-students
     */
    private function seedNonStudentGamification(User $user): void
    {
        // Skip if user already has gamification data
        if (UserGamificationStat::where('user_id', $user->id)->exists()) {
            return;
        }

        // Create empty stats
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

    /**
     * Seed gamification data for a single student
     */
    private function seedStudentGamification(
        User $student,
        $badges,
        $xpSources,
        $levelConfigs
    ): void {
        // Skip if student already has gamification data
        // Remove this check if you want to re-seed existing data
        if (UserGamificationStat::where('user_id', $student->id)->exists()) {
            return;
        }

        // Generate random XP (0 to 50000)
        $totalXp = rand(0, 50000);

        // Calculate level based on XP
        $level = $this->calculateLevel($totalXp, $levelConfigs);

        // Generate random streak data
        $currentStreak = rand(0, 30);
        $longestStreak = max($currentStreak, rand($currentStreak, 60));

        // Create or update gamification stats
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

        // Generate XP transaction history
        if (! $xpSources->isEmpty() && $totalXp > 0) {
            $this->generateXpHistory($student, $totalXp, $xpSources, $level);
        }

        // Award random badges
        if (! $badges->isEmpty()) {
            $this->awardRandomBadges($student, $badges, $level);
        }
    }

    /**
     * Calculate level based on total XP
     */
    private function calculateLevel(int $totalXp, $levelConfigs): int
    {
        if ($levelConfigs->isEmpty()) {
            // Fallback calculation if no level configs
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

        // Use level configs
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

    /**
     * Generate XP transaction history
     */
    private function generateXpHistory(User $student, int $totalXp, $xpSources, int $finalLevel): void
    {
        $remainingXp = $totalXp;
        $currentLevel = 1;

        // Generate 5-20 random transactions
        $transactionCount = rand(5, min(20, (int) ($totalXp / 10)));

        // Use timestamp-based source_id to ensure uniqueness
        $baseTimestamp = now()->timestamp;

        // Valid source types for seeding
        $sourceTypes = ['lesson', 'assignment', 'system'];

        // Generate timestamps spread over last 3 months (90 days)
        $timestamps = $this->generateSpreadTimestamps($transactionCount, 90);

        for ($i = 0; $i < $transactionCount && $remainingXp > 0; $i++) {
            $xpSource = $xpSources->random();

            // Random XP amount (between source amount and 3x source amount)
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

            // Use unique source_id: base timestamp + student id + iteration
            $uniqueSourceId = $baseTimestamp + ($student->id * 1000) + $i;

            // Map XP source code to appropriate reason
            $reason = $this->mapXpSourceToReason($xpSource->code);

            // Determine source type based on XP source
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
                // Skip if duplicate (shouldn't happen with unique source_id)
                continue;
            }
        }
    }

    /**
     * Generate timestamps spread over specified number of days
     */
    private function generateSpreadTimestamps(int $count, int $days): array
    {
        $timestamps = [];
        $now = now();

        // Generate random timestamps and sort them chronologically
        for ($i = 0; $i < $count; $i++) {
            $daysAgo = rand(0, $days);
            $hoursAgo = rand(0, 23);
            $minutesAgo = rand(0, 59);

            $timestamps[] = $now->copy()
                ->subDays($daysAgo)
                ->subHours($hoursAgo)
                ->subMinutes($minutesAgo);
        }

        // Sort chronologically (oldest first)
        usort($timestamps, function ($a, $b) {
            return $a->timestamp <=> $b->timestamp;
        });

        return $timestamps;
    }

    /**
     * Determine source type based on XP source code
     */
    private function determineSourceType(string $xpSourceCode): string
    {
        return match ($xpSourceCode) {
            'lesson_completed' => 'lesson',
            'assignment_submitted', 'perfect_score', 'first_submission' => 'assignment',
            'quiz_passed', 'quiz_completed' => 'attempt',
            default => 'system',
        };
    }

    /**
     * Get activity type for metadata
     */
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

    /**
     * Map XP source code to valid PointReason enum value
     */
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

    /**
     * Calculate level from accumulated XP
     */
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

    /**
     * Award random badges to student
     */
    private function awardRandomBadges(User $student, $badges, int $level): void
    {
        // Award 0-10 random badges based on level
        $maxBadges = min(10, (int) ($level / 2) + rand(0, 5));

        if ($maxBadges === 0) {
            return;
        }

        $badgesToAward = $badges->random(min($maxBadges, $badges->count()));

        // Generate timestamps spread over last 3 months
        $timestamps = $this->generateSpreadTimestamps($badgesToAward->count(), 90);
        $userBadges = [];
        $index = 0;

        foreach ($badgesToAward as $badge) {
            // Skip if already awarded
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

        // Insert all badges at once
        if (! empty($userBadges)) {
            UserBadge::insert($userBadges);
        }
    }
}
