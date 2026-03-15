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

        // Get all students
        $students = User::whereHas('roles', function ($query) {
            $query->where('name', 'Student');
        })->get();

        if ($students->isEmpty()) {
            $this->command->warn('⚠️  No students found. Please seed users first.');
            return;
        }

        $this->command->info("Found {$students->count()} students to process.");

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

        $progressBar = $this->command->getOutput()->createProgressBar($students->count());
        $progressBar->start();

        foreach ($students as $student) {
            $this->seedStudentGamification($student, $badges, $xpSources, $levelConfigs);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);
        $this->command->info('✅ Gamification data seeding completed!');
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
        if (!$xpSources->isEmpty() && $totalXp > 0) {
            $this->generateXpHistory($student, $totalXp, $xpSources, $level);
        }

        // Award random badges
        if (!$badges->isEmpty()) {
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
        $transactionCount = rand(5, min(20, (int)($totalXp / 10)));
        
        // Use timestamp-based source_id to ensure uniqueness
        $baseTimestamp = now()->timestamp;
        
        // Valid source types for seeding
        $sourceTypes = ['lesson', 'assignment', 'system'];
        
        for ($i = 0; $i < $transactionCount && $remainingXp > 0; $i++) {
            $xpSource = $xpSources->random();
            
            // Random XP amount (between source amount and 3x source amount)
            $xpAmount = rand(
                max(1, (int)($xpSource->xp_amount * 0.5)),
                min($remainingXp, (int)($xpSource->xp_amount * 3))
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

            try {
                Point::create([
                    'user_id' => $student->id,
                    'source_type' => $sourceTypes[array_rand($sourceTypes)],
                    'source_id' => $uniqueSourceId,
                    'points' => $xpAmount,
                    'reason' => $reason,
                    'description' => $xpSource->description,
                    'xp_source_code' => $xpSource->code,
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel,
                    'triggered_level_up' => $triggeredLevelUp,
                    'metadata' => ['seeded' => true],
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);

                $remainingXp -= $xpAmount;
            } catch (\Exception $e) {
                // Skip if duplicate (shouldn't happen with unique source_id)
                continue;
            }
        }
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
        $maxBadges = min(10, (int)($level / 2) + rand(0, 5));
        
        if ($maxBadges === 0) {
            return;
        }

        $badgesToAward = $badges->random(min($maxBadges, $badges->count()));
        $userBadges = [];

        foreach ($badgesToAward as $badge) {
            // Skip if already awarded
            if (UserBadge::where('user_id', $student->id)
                ->where('badge_id', $badge->id)
                ->exists()) {
                continue;
            }

            $userBadges[] = [
                'user_id' => $student->id,
                'badge_id' => $badge->id,
                'earned_at' => now()->subDays(rand(1, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all badges at once
        if (!empty($userBadges)) {
            UserBadge::insert($userBadges);
        }
    }
}
