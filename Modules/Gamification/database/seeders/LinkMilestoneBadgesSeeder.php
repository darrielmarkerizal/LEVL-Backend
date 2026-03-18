<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Models\Badge;

class LinkMilestoneBadgesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Linking milestone badges to level configs...');

        // Milestone badge mapping: level => badge_code
        $milestones = [
            10 => 'level_10_milestone',
            20 => 'level_20_milestone',
            30 => 'level_30_milestone',
            40 => 'level_40_milestone',
            50 => 'level_50_milestone',
            60 => 'level_60_milestone',
            70 => 'level_70_milestone',
            80 => 'level_80_milestone',
            90 => 'level_90_milestone',
            100 => 'level_100_milestone',
        ];

        $linked = 0;
        $errors = 0;

        foreach ($milestones as $level => $badgeCode) {
            try {
                // Find the badge
                $badge = Badge::where('code', $badgeCode)->first();

                if (! $badge) {
                    $this->command->warn("Badge not found: {$badgeCode}");
                    $errors++;

                    continue;
                }

                // Find the level config
                $levelConfig = LevelConfig::where('level', $level)->first();

                if (! $levelConfig) {
                    $this->command->warn("Level config not found: Level {$level}");
                    $errors++;

                    continue;
                }

                // Link the badge to the level
                $levelConfig->milestone_badge_id = $badge->id;
                $levelConfig->save();

                $linked++;
            } catch (\Exception $e) {
                $this->command->error("Error linking level {$level}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->command->info("✅ Successfully linked {$linked} milestone badges");

        if ($errors > 0) {
            $this->command->warn("⚠️  {$errors} errors occurred");
        }
    }
}
