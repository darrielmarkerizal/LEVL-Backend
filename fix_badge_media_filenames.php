<?php

/**
 * Script to fix badge media filenames that have incorrect extensions
 * Run: php fix_badge_media_filenames.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Modules\Gamification\Models\Badge;

echo "🔧 Fixing badge media filenames...\n\n";

$badges = Badge::with('media')->get();
$fixed = 0;
$skipped = 0;

foreach ($badges as $badge) {
    $media = $badge->getFirstMedia('icon');
    
    if (!$media) {
        echo "⏭️  Badge {$badge->code}: No media found\n";
        $skipped++;
        continue;
    }
    
    // Check if filename has incorrect pattern like "svg.svg+xml"
    if (str_contains($media->file_name, 'svg.svg+xml') || str_contains($media->file_name, '.svg+xml')) {
        $oldFileName = $media->file_name;
        $newFileName = $badge->code . '.svg';
        
        // Update the media record
        $media->file_name = $newFileName;
        $media->name = $badge->name;
        $media->mime_type = 'image/svg+xml';
        $media->save();
        
        echo "✅ Badge {$badge->code}: Fixed filename\n";
        echo "   Old: {$oldFileName}\n";
        echo "   New: {$newFileName}\n\n";
        $fixed++;
    } else {
        echo "⏭️  Badge {$badge->code}: Filename OK ({$media->file_name})\n";
        $skipped++;
    }
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✨ Summary:\n";
echo "   Fixed: {$fixed}\n";
echo "   Skipped: {$skipped}\n";
echo "   Total: " . ($fixed + $skipped) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
echo "Note: The actual files in storage are still accessible.\n";
echo "The URLs will now work correctly.\n";
