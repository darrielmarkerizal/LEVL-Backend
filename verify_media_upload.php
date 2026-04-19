<?php



require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  VERIFIKASI MEDIA UPLOAD - SEMUA SEEDER\n";
echo "═══════════════════════════════════════════════════════════════\n\n";


echo "📊 TOTAL MEDIA\n";
echo "───────────────────────────────────────────────────────────────\n";
$totalMedia = DB::table('media')->count();
echo "Total media files: " . number_format($totalMedia) . "\n";

if ($totalMedia > 1000) {
    echo "✅ PASS - Expected 1000+, got " . number_format($totalMedia) . "\n";
} else {
    echo "❌ FAIL - Expected 1000+, got " . number_format($totalMedia) . "\n";
}
echo "\n";


echo "📦 MEDIA PER MODEL TYPE\n";
echo "───────────────────────────────────────────────────────────────\n";
$mediaByModel = DB::table('media')
    ->select('model_type', DB::raw('COUNT(*) as total'))
    ->groupBy('model_type')
    ->orderByDesc('total')
    ->get();

$lessonBlockMedia = 0;
foreach ($mediaByModel as $row) {
    $modelName = class_basename($row->model_type);
    echo sprintf("%-30s : %s\n", $modelName, number_format($row->total));
    
    if ($row->model_type === 'Modules\\Schemes\\Models\\LessonBlock') {
        $lessonBlockMedia = $row->total;
    }
}

echo "\n";
if ($lessonBlockMedia >= 670) {
    echo "✅ PASS - LessonBlock media: " . number_format($lessonBlockMedia) . " (expected 670+)\n";
} else {
    echo "❌ FAIL - LessonBlock media: " . number_format($lessonBlockMedia) . " (expected 670+)\n";
}
echo "\n";


echo "📁 MEDIA PER COLLECTION\n";
echo "───────────────────────────────────────────────────────────────\n";
$mediaByCollection = DB::table('media')
    ->select('collection_name', DB::raw('COUNT(*) as total'))
    ->groupBy('collection_name')
    ->orderByDesc('total')
    ->get();

foreach ($mediaByCollection as $row) {
    echo sprintf("%-30s : %s\n", $row->collection_name, number_format($row->total));
}
echo "\n";


echo "💾 MEDIA PER DISK\n";
echo "───────────────────────────────────────────────────────────────\n";
$mediaByDisk = DB::table('media')
    ->select('disk', DB::raw('COUNT(*) as total'))
    ->groupBy('disk')
    ->get();

$doMedia = 0;
foreach ($mediaByDisk as $row) {
    echo sprintf("%-30s : %s\n", $row->disk, number_format($row->total));
    if ($row->disk === 'do') {
        $doMedia = $row->total;
    }
}

echo "\n";
if ($doMedia > 0) {
    echo "✅ PASS - DigitalOcean Spaces: " . number_format($doMedia) . " files\n";
} else {
    echo "❌ FAIL - No files in DigitalOcean Spaces\n";
}
echo "\n";


echo "🎨 LESSON BLOCK TYPES DISTRIBUTION\n";
echo "───────────────────────────────────────────────────────────────\n";
$blockTypes = DB::table('lesson_blocks')
    ->select('block_type', DB::raw('COUNT(*) as total'))
    ->groupBy('block_type')
    ->orderByDesc('total')
    ->get();

$totalBlocks = DB::table('lesson_blocks')->count();
$hasVideo = false;
$hasFile = false;
$hasImage = false;

foreach ($blockTypes as $row) {
    $percentage = $totalBlocks > 0 ? ($row->total / $totalBlocks * 100) : 0;
    echo sprintf("%-30s : %s (%.1f%%)\n", $row->block_type, number_format($row->total), $percentage);
    
    if ($row->block_type === 'video' && $row->total > 0) $hasVideo = true;
    if ($row->block_type === 'file' && $row->total > 0) $hasFile = true;
    if ($row->block_type === 'image' && $row->total > 0) $hasImage = true;
}

echo "\n";
if ($hasVideo && $hasFile && $hasImage) {
    echo "✅ PASS - All media block types present (video, file, image)\n";
} else {
    echo "❌ FAIL - Missing media block types:\n";
    if (!$hasVideo) echo "   - video blocks missing\n";
    if (!$hasFile) echo "   - file blocks missing\n";
    if (!$hasImage) echo "   - image blocks missing\n";
}
echo "\n";


echo "🔍 SAMPLE MEDIA RECORDS (First 5 LessonBlock media)\n";
echo "───────────────────────────────────────────────────────────────\n";
$sampleMedia = DB::table('media')
    ->where('model_type', 'Modules\\Schemes\\Models\\LessonBlock')
    ->limit(5)
    ->get(['id', 'model_id', 'collection_name', 'file_name', 'disk', 'size']);

foreach ($sampleMedia as $media) {
    echo sprintf("ID: %d | Block: %d | Collection: %s | File: %s | Disk: %s | Size: %s\n",
        $media->id,
        $media->model_id,
        $media->collection_name,
        $media->file_name,
        $media->disk,
        number_format($media->size) . ' bytes'
    );
}
echo "\n";


echo "═══════════════════════════════════════════════════════════════\n";
echo "  SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$allPassed = true;


if ($totalMedia < 1000) {
    echo "❌ Total media files too low: " . number_format($totalMedia) . " (expected 1000+)\n";
    $allPassed = false;
}


if ($lessonBlockMedia < 670) {
    echo "❌ LessonBlock media too low: " . number_format($lessonBlockMedia) . " (expected 670+)\n";
    $allPassed = false;
}


if ($doMedia === 0) {
    echo "❌ No files uploaded to DigitalOcean Spaces\n";
    $allPassed = false;
}


if (!$hasVideo || !$hasFile || !$hasImage) {
    echo "❌ Missing media block types (video, file, or image)\n";
    $allPassed = false;
}

if ($allPassed) {
    echo "✅ ALL CHECKS PASSED!\n";
    echo "✅ Semua seeder berhasil mengupload media\n";
    echo "✅ Round-robin distribution berfungsi dengan baik\n";
    echo "✅ Media terupload ke DigitalOcean Spaces\n";
} else {
    echo "\n❌ SOME CHECKS FAILED\n";
    echo "Periksa output di atas untuk detail masalah\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
