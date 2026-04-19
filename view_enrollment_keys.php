<?php



require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Schemes\Models\Course;

echo "=================================================\n";
echo "Enrollment Keys Viewer\n";
echo "=================================================\n\n";


$courses = Course::where('enrollment_type', 'key_based')
    ->with(['instructors'])
    ->get();

if ($courses->isEmpty()) {
    echo "✓ No courses found with key_based enrollment.\n";
    exit(0);
}

echo "Found {$courses->count()} course(s) with key_based enrollment:\n\n";

$viewable = [];
$notViewable = [];

foreach ($courses as $course) {
    $hasHash = ! empty($course->enrollment_key_hash);
    $hasEncrypted = ! empty($course->enrollment_key_encrypted);

    echo str_repeat('=', 80)."\n";
    echo "Course ID: {$course->id}\n";
    echo "Title: {$course->title}\n";
    echo "Code: {$course->code}\n";
    echo "Slug: {$course->slug}\n";
    echo "Status: {$course->status->value}\n";

    
    if ($course->instructors->isNotEmpty()) {
        echo "Instructors:\n";
        foreach ($course->instructors as $instructor) {
            echo "  - {$instructor->name} ({$instructor->username})\n";
        }
    } else {
        echo "Instructors: None assigned\n";
    }

    echo "\nEnrollment Key Status:\n";
    echo '  Has Hash: '.($hasHash ? '✓ Yes' : '✗ No')."\n";
    echo '  Has Encrypted: '.($hasEncrypted ? '✓ Yes' : '✗ No')."\n";

    if ($hasEncrypted) {
        try {
            $decrypted = $course->getDecryptedEnrollmentKey();
            echo "  Enrollment Key: {$decrypted}\n";
            echo "  Viewable: ✓ Yes\n";

            $viewable[] = [
                'id' => $course->id,
                'title' => $course->title,
                'code' => $course->code,
                'key' => $decrypted,
                'instructors' => $course->instructors->pluck('name')->join(', '),
            ];
        } catch (\Exception $e) {
            echo "  Enrollment Key: ⚠ Failed to decrypt\n";
            echo "  Error: {$e->getMessage()}\n";
            echo "  Viewable: ✗ No\n";

            $notViewable[] = [
                'id' => $course->id,
                'title' => $course->title,
                'reason' => 'Decryption failed: '.$e->getMessage(),
            ];
        }
    } elseif ($hasHash) {
        echo "  Enrollment Key: ⚠ Only hashed (cannot be viewed)\n";
        echo "  Viewable: ✗ No\n";
        echo "  Note: Run regenerate_enrollment_keys.php to create viewable keys\n";

        $notViewable[] = [
            'id' => $course->id,
            'title' => $course->title,
            'reason' => 'Only hashed (not encrypted)',
        ];
    } else {
        echo "  Enrollment Key: ✗ Not set\n";
        echo "  Viewable: ✗ No\n";

        $notViewable[] = [
            'id' => $course->id,
            'title' => $course->title,
            'reason' => 'No key set',
        ];
    }

    echo "\n";
}


echo str_repeat('=', 80)."\n";
echo "SUMMARY\n";
echo str_repeat('=', 80)."\n\n";

echo "Total Courses: {$courses->count()}\n";
echo 'Viewable Keys: '.count($viewable)."\n";
echo 'Not Viewable: '.count($notViewable)."\n\n";

if (! empty($viewable)) {
    echo "VIEWABLE ENROLLMENT KEYS:\n";
    echo str_repeat('-', 80)."\n";
    printf("%-5s | %-10s | %-25s | %-15s\n", 'ID', 'Code', 'Title', 'Key');
    echo str_repeat('-', 80)."\n";

    foreach ($viewable as $item) {
        printf(
            "%-5s | %-10s | %-25s | %-15s\n",
            $item['id'],
            $item['code'],
            substr($item['title'], 0, 25),
            $item['key']
        );
    }
    echo str_repeat('-', 80)."\n\n";
}

if (! empty($notViewable)) {
    echo "NOT VIEWABLE (Action Required):\n";
    echo str_repeat('-', 80)."\n";

    foreach ($notViewable as $item) {
        echo "ID: {$item['id']} - {$item['title']}\n";
        echo "  Reason: {$item['reason']}\n";
        echo "  Action: Run 'php regenerate_enrollment_keys.php' to generate viewable keys\n\n";
    }
}

echo "\n✓ Script completed successfully!\n";
echo "\nNote: To regenerate keys for courses with only hashed keys, run:\n";
echo "  php regenerate_enrollment_keys.php\n\n";
