<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Common\Models\LevelConfig;

echo "🧹 Cleaning level_configs rewards column...\n\n";

$levels = LevelConfig::all();
$cleaned = 0;

foreach ($levels as $level) {
    
    
    if (! empty($level->rewards)) {
        $level->rewards = [];
        $level->save();
        $cleaned++;
    }
}

echo "✅ Cleaned {$cleaned} level configurations\n";
echo "   Rewards are now managed via:\n";
echo "   - bonus_xp column (for XP rewards)\n";
echo "   - milestone_badge_id relation (for badge rewards)\n";
