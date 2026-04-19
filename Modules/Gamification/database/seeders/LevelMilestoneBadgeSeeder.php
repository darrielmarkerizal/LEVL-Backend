<?php

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Models\Badge;

class LevelMilestoneBadgeSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->command->info('Creating milestone badges for every 10 levels...');

        $milestoneBadges = [
            10 => [
                'code' => 'level_10_milestone',
                'name' => 'Novice Achiever',
                'description' => 'Mencapai level 10 - Menyelesaikan tier Beginner',
                'rarity' => 'common',
                'xp_reward' => 50,
            ],
            20 => [
                'code' => 'level_20_milestone',
                'name' => 'Competent Learner',
                'description' => 'Mencapai level 20 - Menyelesaikan tier Novice',
                'rarity' => 'common',
                'xp_reward' => 100,
            ],
            30 => [
                'code' => 'level_30_milestone',
                'name' => 'Intermediate Master',
                'description' => 'Mencapai level 30 - Menyelesaikan tier Competent',
                'rarity' => 'uncommon',
                'xp_reward' => 150,
            ],
            40 => [
                'code' => 'level_40_milestone',
                'name' => 'Proficient Expert',
                'description' => 'Mencapai level 40 - Menyelesaikan tier Intermediate',
                'rarity' => 'uncommon',
                'xp_reward' => 200,
            ],
            50 => [
                'code' => 'level_50_milestone',
                'name' => 'Advanced Specialist',
                'description' => 'Mencapai level 50 - Menyelesaikan tier Proficient',
                'rarity' => 'rare',
                'xp_reward' => 300,
            ],
            60 => [
                'code' => 'level_60_milestone',
                'name' => 'Expert Champion',
                'description' => 'Mencapai level 60 - Menyelesaikan tier Advanced',
                'rarity' => 'rare',
                'xp_reward' => 400,
            ],
            70 => [
                'code' => 'level_70_milestone',
                'name' => 'Master Virtuoso',
                'description' => 'Mencapai level 70 - Menyelesaikan tier Expert',
                'rarity' => 'epic',
                'xp_reward' => 500,
            ],
            80 => [
                'code' => 'level_80_milestone',
                'name' => 'Grand Master',
                'description' => 'Mencapai level 80 - Menyelesaikan tier Master',
                'rarity' => 'epic',
                'xp_reward' => 700,
            ],
            90 => [
                'code' => 'level_90_milestone',
                'name' => 'Legendary Scholar',
                'description' => 'Mencapai level 90 - Menyelesaikan tier Grand Master',
                'rarity' => 'legendary',
                'xp_reward' => 1000,
            ],
            100 => [
                'code' => 'level_100_milestone',
                'name' => 'Ultimate Legend',
                'description' => 'Mencapai level 100 - Menguasai semua tier!',
                'rarity' => 'legendary',
                'xp_reward' => 2000,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($milestoneBadges as $level => $badgeData) {
            
            $badge = Badge::updateOrCreate(
                ['code' => $badgeData['code']],
                [
                    'name' => $badgeData['name'],
                    'description' => $badgeData['description'],
                    'type' => 'milestone',
                    'category' => 'milestone',
                    'rarity' => $badgeData['rarity'],
                    'xp_reward' => $badgeData['xp_reward'],
                    'threshold' => $level,
                    'is_repeatable' => false,
                    'active' => true,
                ]
            );

            if ($badge->wasRecentlyCreated) {
                $created++;
                $this->command->info("✓ Created badge: {$badge->name} (Level {$level})");
            } else {
                $updated++;
                $this->command->info("✓ Updated badge: {$badge->name} (Level {$level})");
            }

            
            $levelConfig = LevelConfig::where('level', $level)->first();
            if ($levelConfig) {
                $levelConfig->milestone_badge_id = $badge->id;
                $levelConfig->save();
                $this->command->info("  → Linked to Level {$level} config");
            }
        }

        $this->command->info("\n✓ Milestone badges seeding completed!");
        $this->command->info("  Created: {$created} badges");
        $this->command->info("  Updated: {$updated} badges");
        $this->command->info('  Total: '.($created + $updated).' milestone badges');
    }
}
