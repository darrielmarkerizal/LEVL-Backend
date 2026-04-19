<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Models\Badge;

echo "🔗 Linking Milestone Badges to Level Configs...\n\n";


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
        
        $badge = Badge::where('code', $badgeCode)->first();

        if (! $badge) {
            echo "❌ Badge not found: {$badgeCode}\n";
            $errors++;

            continue;
        }

        
        $levelConfig = LevelConfig::where('level', $level)->first();

        if (! $levelConfig) {
            echo "❌ Level config not found: Level {$level}\n";
            $errors++;

            continue;
        }

        
        $levelConfig->milestone_badge_id = $badge->id;
        $levelConfig->save();

        echo "✅ Level {$level} → {$badge->name} (ID: {$badge->id})\n";
        $linked++;

    } catch (\Exception $e) {
        echo "❌ Error linking level {$level}: {$e->getMessage()}\n";
        $errors++;
    }
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Summary:\n";
echo "   ✅ Successfully linked: {$linked} milestone badges\n";
echo "   ❌ Errors: {$errors}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($linked > 0) {
    echo "\n✨ Milestone badges are now linked to level configs!\n";
    echo "   You can verify by checking: GET /api/v1/levels\n";
}
