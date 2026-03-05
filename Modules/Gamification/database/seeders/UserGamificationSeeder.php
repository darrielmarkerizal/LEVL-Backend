<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Gamification\Enums\BadgeType;
use Modules\Gamification\Enums\PointReason;
use Modules\Gamification\Enums\PointSourceType;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Models\UserScopeStat;
use Modules\Schemes\Models\Course;

class UserGamificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding User Gamification Stats...');

        $users = User::all();
        $badges = Badge::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping User Gamification Seeding.');

            return;
        }

        foreach ($users as $user) {
            // 1. Create or Update Gamification Stats
            // Randomize activity level: 0=Inactive, 1=Beginner, 2=Active, 3=Power User
            $activityLevel = rand(0, 3);

            if ($activityLevel === 0) {
                // Inactive user, maybe just initialized stats
                UserGamificationStat::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'total_xp' => 0,
                        'global_level' => 1,
                        'current_streak' => 0,
                        'longest_streak' => 0,
                        'last_activity_date' => null,
                        'stats_updated_at' => now(),
                    ]
                );

                continue;
            }

            // Active users
            $xp = rand(100, 5000) * $activityLevel;
            $level = max(1, (int) ($xp / 500)); // Rough estimation
            $streak = rand(0, 10 * $activityLevel);

            UserGamificationStat::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'total_xp' => $xp,
                    'global_level' => $level,
                    'current_streak' => $streak,
                    'longest_streak' => max($streak, rand($streak, $streak + 5)),
                    'last_activity_date' => now()->subDays(rand(0, 3)),
                    'stats_updated_at' => now(),
                ]
            );

            // 2. Generate Point History (Last 5-10 entries to simulate history)
            // Keep seed data aligned with DB enum/check constraint on points.source_type.
            $validSourceTypes = [
                PointSourceType::Lesson,
                PointSourceType::Assignment,
                PointSourceType::Attempt,
                PointSourceType::System,
            ];

            for ($i = 0; $i < rand(5, 15); $i++) {
                $sourceType = $validSourceTypes[array_rand($validSourceTypes)];
                $reason = PointReason::cases()[array_rand(PointReason::cases())];

                Point::updateOrCreate([
                    'user_id' => $user->id,
                    'source_type' => $sourceType,
                    'source_id' => $i + 1,
                    'reason' => $reason,
                ], [
                    'points' => rand(10, 100),
                    'description' => 'Simulated activity reward',
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            // 3. Assign Badges Based on Level/XP (Logical)
            if ($badges->isNotEmpty()) {
                foreach ($badges as $badge) {
                    $shouldAward = false;

                    if ($badge->type === BadgeType::Habit || $badge->type === BadgeType::Speed) {
                        $shouldAward = $level >= $badge->threshold;
                    } elseif ($badge->type === BadgeType::Quality || $badge->type === BadgeType::Social) {
                        $shouldAward = $activityLevel >= 2 && rand(0, 100) < 70;
                    } elseif ($badge->type === BadgeType::Completion) {
                        $shouldAward = $activityLevel >= 1 && rand(0, 100) < 80;
                    } elseif ($badge->type === BadgeType::Hidden) {
                        $shouldAward = $activityLevel >= 3 && rand(0, 100) < 10;
                    }

                    if ($shouldAward) {
                        UserBadge::firstOrCreate([
                            'user_id' => $user->id,
                            'badge_id' => $badge->id,
                        ], [
                            'earned_at' => now()->subDays(rand(1, 60)),
                        ]);
                    }
                }
            }

            $courses = Course::inRandomOrder()->limit(rand(1, 3))->get();
            foreach ($courses as $course) {
                $courseXp = rand(50, min($xp, 2000));
                $courseLevel = max(1, (int) ($courseXp / 500));

                UserScopeStat::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'scope_type' => 'course',
                        'scope_id' => $course->id,
                    ],
                    [
                        'total_xp' => $courseXp,
                    ]
                );
            }

        }

        $this->command->info('✅ User Gamification Stats & Scope Stats seeded for '.$users->count().' users.');
    }
}
