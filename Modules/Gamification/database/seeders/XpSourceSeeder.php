<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Gamification\Models\XpSource;

class XpSourceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding XP Sources...');

        $sources = $this->getXpSources();
        $count = 0;

        foreach ($sources as $source) {
            try {
                XpSource::updateOrCreate(
                    ['code' => $source['code']],
                    $source
                );
                $count++;
            } catch (\Exception $e) {
                $this->command->error("Failed to seed XP source {$source['code']}: {$e->getMessage()}");
            }
        }

        $this->command->info("✅ Successfully seeded {$count} XP sources.");
    }

    private function getXpSources(): array
    {
        return [
            // Learning Activities
            [
                'code' => 'lesson_completed',
                'name' => 'Lesson Completed',
                'description' => 'Complete a lesson',
                'xp_amount' => 50,
                'cooldown_seconds' => 10,
                'daily_limit' => null,
                'daily_xp_cap' => 5000,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            [
                'code' => 'assignment_submitted',
                'name' => 'Assignment Submitted',
                'description' => 'Submit an assignment',
                'xp_amount' => 100,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                '
allow_multiple' => false,
                'is_active' => true,
            ],
            [
                'code' => 'quiz_passed',
                'name' => 'Quiz Passed',
                'description' => 'Pass a quiz',
                'xp_amount' => 80,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                'allow_multiple' => false,
                'is_active' => true,
            ],
            [
                'code' => 'unit_completed',
                'name' => 'Unit Completed',
                'description' => 'Complete a unit',
                'xp_amount' => 200,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                'allow_multiple' => false,
                'is_active' => true,
            ],
            [
                'code' => 'course_completed',
                'name' => 'Course Completed',
                'description' => 'Complete a course',
                'xp_amount' => 500,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                'allow_multiple' => false,
                'is_active' => true,
            ],
            
            // Engagement Activities
            [
                'code' => 'daily_login',
                'name' => 'Daily Login',
                'description' => 'Login to the platform',
                'xp_amount' => 10,
                'cooldown_seconds' => 86400, // 24 hours
                'daily_limit' => 1,
                'daily_xp_cap' => 10,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            [
                'code' => 'streak_7_days',
                'name' => '7 Day Streak',
                'description' => 'Maintain 7 day login streak',
                'xp_amount' => 200,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            [
                'code' => 'streak_30_days',
                'name' => '30 Day Streak',
                'description' => 'Maintain 30 day login streak',
                'xp_amount' => 1000,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            
            // Social Activities
            [
                'code' => 'forum_post_created',
                'name' => 'Forum Post Created',
                'description' => 'Create a forum post',
                'xp_amount' => 20,
                'cooldown_seconds' => 60,
                'daily_limit' => 10,
                'daily_xp_cap' => 200,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            [
                'code' => 'forum_reply_created',
                'name' => 'Forum Reply Created',
                'description' => 'Reply to a forum post',
                'xp_amount' => 10,
                'cooldown_seconds' => 30,
                'daily_limit' => 20,
                'daily_xp_cap' => 200,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            [
                'code' => 'forum_liked',
                'name' => 'Forum Post Liked',
                'description' => 'Receive a like on forum post',
                'xp_amount' => 5,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => 100,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            
            // Quality Activities
            [
                'code' => 'perfect_score',
                'name' => 'Perfect Score',
                'description' => 'Get 100% on assignment or quiz',
                'xp_amount' => 50,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            [
                'code' => 'first_submission',
                'name' => 'First Submission',
                'description' => 'Be the first to submit assignment',
                'xp_amount' => 30,
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                'allow_multiple' => true,
                'is_active' => true,
            ],
            
            // System Rewards
            [
                'code' => 'level_up_bonus',
                'name' => 'Level Up Bonus',
                'description' => 'Bonus XP for leveling up',
                'xp_amount' => 0, // Dynamic based on level
                'cooldown_seconds' => 0,
                'daily_limit' => null,
                'daily_xp_cap' => null,
                'allow_multiple' => true,
                'is_active' => true,
            ],
        ];
    }
}
