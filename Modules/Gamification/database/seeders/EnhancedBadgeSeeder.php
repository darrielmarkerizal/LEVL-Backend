<?php

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Gamification\Models\Badge;

class EnhancedBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            
            [
                'code' => 'first_lesson',
                'name' => 'First Lesson',
                'description' => 'Complete your first lesson',
                'type' => 'completion',
                'category' => 'learning',
                'rarity' => 'common',
                'xp_reward' => 50,
                'threshold' => 1,
                'is_repeatable' => false,
            ],
            [
                'code' => 'lesson_streak_7',
                'name' => '7-Day Streak',
                'description' => 'Complete lessons for 7 days in a row',
                'type' => 'achievement',
                'category' => 'habit',
                'rarity' => 'uncommon',
                'xp_reward' => 100,
                'threshold' => 7,
                'is_repeatable' => true,
                'max_awards_per_user' => 10,
            ],

            
            [
                'code' => 'perfect_score',
                'name' => 'Perfect Score',
                'description' => 'Get 100% on any quiz or assignment',
                'type' => 'achievement',
                'category' => 'assessment',
                'rarity' => 'uncommon',
                'xp_reward' => 100,
                'threshold' => 1,
                'is_repeatable' => true,
                'max_awards_per_user' => 50,
            ],
            [
                'code' => 'quiz_master',
                'name' => 'Quiz Master',
                'description' => 'Complete 10 quizzes with perfect scores',
                'type' => 'achievement',
                'category' => 'assessment',
                'rarity' => 'rare',
                'xp_reward' => 200,
                'threshold' => 10,
                'is_repeatable' => false,
            ],

            
            [
                'code' => 'course_complete',
                'name' => 'Course Completed',
                'description' => 'Complete your first course',
                'type' => 'completion',
                'category' => 'milestone',
                'rarity' => 'rare',
                'xp_reward' => 300,
                'threshold' => 1,
                'is_repeatable' => true,
                'max_awards_per_user' => 100,
            ],
            [
                'code' => 'course_master_5',
                'name' => 'Course Master',
                'description' => 'Complete 5 courses',
                'type' => 'milestone',
                'category' => 'milestone',
                'rarity' => 'epic',
                'xp_reward' => 500,
                'threshold' => 5,
                'is_repeatable' => false,
            ],

            
            [
                'code' => 'speed_runner',
                'name' => 'Speed Runner',
                'description' => 'Complete a course in less than 3 days',
                'type' => 'achievement',
                'category' => 'speed',
                'rarity' => 'rare',
                'xp_reward' => 250,
                'threshold' => 1,
                'is_repeatable' => true,
                'max_awards_per_user' => 20,
            ],
            [
                'code' => 'first_submission',
                'name' => 'First Blood',
                'description' => 'Be the first to submit an assignment',
                'type' => 'achievement',
                'category' => 'speed',
                'rarity' => 'rare',
                'xp_reward' => 200,
                'threshold' => 1,
                'is_repeatable' => true,
                'max_awards_per_user' => 50,
            ],

            
            [
                'code' => 'helpful_member',
                'name' => 'Helpful Member',
                'description' => 'Get 10 likes on forum posts',
                'type' => 'achievement',
                'category' => 'social',
                'rarity' => 'uncommon',
                'xp_reward' => 100,
                'threshold' => 10,
                'is_repeatable' => false,
            ],
            [
                'code' => 'forum_hero',
                'name' => 'Forum Hero',
                'description' => 'Have 5 replies marked as accepted answers',
                'type' => 'achievement',
                'category' => 'social',
                'rarity' => 'rare',
                'xp_reward' => 200,
                'threshold' => 5,
                'is_repeatable' => false,
            ],

            
            [
                'code' => 'early_bird',
                'name' => 'Early Bird',
                'description' => 'Complete 5 lessons before 6 AM',
                'type' => 'achievement',
                'category' => 'habit',
                'rarity' => 'uncommon',
                'xp_reward' => 150,
                'threshold' => 5,
                'is_repeatable' => false,
            ],
            [
                'code' => 'night_owl',
                'name' => 'Night Owl',
                'description' => 'Submit 5 assignments after midnight',
                'type' => 'achievement',
                'category' => 'habit',
                'rarity' => 'uncommon',
                'xp_reward' => 150,
                'threshold' => 5,
                'is_repeatable' => false,
            ],
            [
                'code' => 'weekend_warrior',
                'name' => 'Weekend Warrior',
                'description' => 'Complete 10 lessons on weekends',
                'type' => 'achievement',
                'category' => 'habit',
                'rarity' => 'rare',
                'xp_reward' => 200,
                'threshold' => 10,
                'is_repeatable' => false,
            ],
            [
                'code' => 'dedication_30',
                'name' => 'Dedication',
                'description' => 'Login for 30 consecutive days',
                'type' => 'achievement',
                'category' => 'habit',
                'rarity' => 'epic',
                'xp_reward' => 500,
                'threshold' => 30,
                'is_repeatable' => true,
                'max_awards_per_user' => 5,
            ],

            
            [
                'code' => 'legendary_learner',
                'name' => 'Legendary Learner',
                'description' => 'Complete 25 courses with perfect scores',
                'type' => 'milestone',
                'category' => 'milestone',
                'rarity' => 'legendary',
                'xp_reward' => 1000,
                'threshold' => 25,
                'is_repeatable' => false,
            ],
            [
                'code' => 'master_of_all',
                'name' => 'Master of All',
                'description' => 'Earn all other badges',
                'type' => 'milestone',
                'category' => 'milestone',
                'rarity' => 'legendary',
                'xp_reward' => 2000,
                'threshold' => 1,
                'is_repeatable' => false,
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::updateOrCreate(
                ['code' => $badgeData['code']],
                $badgeData
            );
        }

        $this->command->info('Enhanced badges seeded successfully!');
    }
}
