<?php

/**
 * Script to regenerate enrollment keys for existing courses
 * 
 * This script will:
 * 1. Find all courses with key_based enrollment that have hash but no encrypted key
 * 2. Generate new enrollment keys
 * 3. Encrypt them so they can be viewed by authorized users
 * 
 * IMPORTANT: This will generate NEW keys. The old keys cannot be recovered from hashes.
 * Make sure to communicate the new keys to course instructors.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Schemes\Models\Course;
use Illuminate\Support\Str;

echo "=================================================\n";
echo "Enrollment Key Regeneration Script\n";
echo "=================================================\n\n";

// Find courses with key_based enrollment
$courses = Course::where('enrollment_type', 'key_based')
    ->whereNotNull('enrollment_key_hash')
    ->get();

if ($courses->isEmpty()) {
    echo "✓ No courses found with key_based enrollment.\n";
    exit(0);
}

echo "Found {$courses->count()} course(s) with key_based enrollment:\n\n";

$regenerated = [];
$skipped = [];

foreach ($courses as $course) {
    $hasEncrypted = !empty($course->enrollment_key_encrypted);
    
    echo "Course ID: {$course->id}\n";
    echo "  Title: {$course->title}\n";
    echo "  Code: {$course->code}\n";
    echo "  Has Hash: " . (!empty($course->enrollment_key_hash) ? 'Yes' : 'No') . "\n";
    echo "  Has Encrypted: " . ($hasEncrypted ? 'Yes' : 'No') . "\n";
    
    if ($hasEncrypted) {
        // Already has encrypted key, try to decrypt and show it
        try {
            $decrypted = $course->getDecryptedEnrollmentKey();
            echo "  Current Key: {$decrypted}\n";
            echo "  Status: ✓ Already encrypted (no action needed)\n";
            $skipped[] = [
                'id' => $course->id,
                'title' => $course->title,
                'key' => $decrypted,
            ];
        } catch (\Exception $e) {
            echo "  Status: ⚠ Has encrypted key but failed to decrypt\n";
            echo "  Error: {$e->getMessage()}\n";
        }
    } else {
        // Generate new key
        echo "  Status: Generating new key...\n";
        
        // Ask for confirmation
        echo "  ⚠ WARNING: This will generate a NEW key. Old key cannot be recovered.\n";
        echo "  Continue? (y/n): ";
        
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) !== 'y') {
            echo "  Status: ✗ Skipped by user\n";
            $skipped[] = [
                'id' => $course->id,
                'title' => $course->title,
                'reason' => 'Skipped by user',
            ];
        } else {
            // Generate new key
            $newKey = strtoupper(Str::random(12));
            
            // Update course (this will automatically encrypt)
            $course->enrollment_key = $newKey;
            $course->save();
            
            echo "  New Key: {$newKey}\n";
            echo "  Status: ✓ Successfully regenerated and encrypted\n";
            
            $regenerated[] = [
                'id' => $course->id,
                'title' => $course->title,
                'code' => $course->code,
                'new_key' => $newKey,
            ];
        }
    }
    
    echo "\n";
}

// Summary
echo "=================================================\n";
echo "Summary\n";
echo "=================================================\n\n";

if (!empty($regenerated)) {
    echo "Regenerated Keys ({count($regenerated)}):\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s | %-10s | %-30s | %-15s\n", "ID", "Code", "Title", "New Key");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($regenerated as $item) {
        printf(
            "%-5s | %-10s | %-30s | %-15s\n",
            $item['id'],
            $item['code'],
            substr($item['title'], 0, 30),
            $item['new_key']
        );
    }
    echo str_repeat("-", 80) . "\n\n";
    
    echo "⚠ IMPORTANT: Please communicate these new keys to the course instructors!\n\n";
}

if (!empty($skipped)) {
    echo "Skipped Courses (" . count($skipped) . "):\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($skipped as $item) {
        echo "ID: {$item['id']} - {$item['title']}\n";
        if (isset($item['key'])) {
            echo "  Current Key: {$item['key']}\n";
        }
        if (isset($item['reason'])) {
            echo "  Reason: {$item['reason']}\n";
        }
        echo "\n";
    }
}

echo "\n✓ Script completed successfully!\n";
