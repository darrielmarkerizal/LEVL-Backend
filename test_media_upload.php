<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Media Upload to DigitalOcean Spaces\n";
echo "==========================================\n\n";

// Test 1: Check DO credentials
echo "1. Checking DO credentials...\n";
$doKey = env('DO_ACCESS_KEY_ID');
$doSecret = env('DO_SECRET_ACCESS_KEY');
$doBucket = env('DO_BUCKET');
$doEndpoint = env('DO_ENDPOINT');

echo "   DO_ACCESS_KEY_ID: " . ($doKey ? '✓ Set' : '✗ Not set') . "\n";
echo "   DO_SECRET_ACCESS_KEY: " . ($doSecret ? '✓ Set' : '✗ Not set') . "\n";
echo "   DO_BUCKET: " . ($doBucket ?: '✗ Not set') . "\n";
echo "   DO_ENDPOINT: " . ($doEndpoint ?: '✗ Not set') . "\n\n";

// Test 2: Test Storage connection
echo "2. Testing Storage connection...\n";
try {
    $disk = \Storage::disk('do');
    $testFile = 'test-' . time() . '.txt';
    $disk->put($testFile, 'Hello from Laravel');
    echo "   ✓ File uploaded: {$testFile}\n";
    
    $exists = $disk->exists($testFile);
    echo "   ✓ File exists: " . ($exists ? 'Yes' : 'No') . "\n";
    
    $disk->delete($testFile);
    echo "   ✓ File deleted\n\n";
} catch (\Exception $e) {
    echo "   ✗ Storage test failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Check UAT fixtures
echo "3. Checking UAT fixture files...\n";
\App\Support\UATMediaFixtures::ensureFilesExist();
$paths = \App\Support\UATMediaFixtures::paths();

foreach ($paths as $type => $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    echo "   " . ($exists ? '✓' : '✗') . " {$type}: {$path} (" . ($exists ? number_format($size) . ' bytes' : 'not found') . ")\n";
}
echo "\n";

// Test 4: Test media upload with Spatie Media Library
echo "4. Testing Spatie Media Library upload...\n";
try {
    // Create a test lesson block
    $lesson = \Modules\Schemes\Models\Lesson::first();
    if (!$lesson) {
        echo "   ✗ No lesson found. Please run seeders first.\n";
        exit(1);
    }
    
    $block = \Modules\Schemes\Models\LessonBlock::create([
        'lesson_id' => $lesson->id,
        'block_type' => 'image',
        'content' => 'Test block for media upload',
        'order' => 999,
        'slug' => 'test-media-upload-' . time(),
    ]);
    
    echo "   ✓ Created test lesson block (ID: {$block->id})\n";
    
    // Upload media
    $imagePath = $paths['image'];
    if (!file_exists($imagePath)) {
        echo "   ✗ Image file not found: {$imagePath}\n";
        exit(1);
    }
    
    $media = $block->addMedia($imagePath)
        ->preservingOriginal()
        ->toMediaCollection('media', 'do');
    
    echo "   ✓ Media uploaded (ID: {$media->id})\n";
    echo "   ✓ Media URL: {$media->getUrl()}\n";
    
    // Cleanup
    $block->delete();
    echo "   ✓ Test block deleted\n\n";
    
} catch (\Exception $e) {
    echo "   ✗ Media library test failed: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
}

echo "✅ All tests passed!\n";
echo "Media upload is working correctly.\n";
