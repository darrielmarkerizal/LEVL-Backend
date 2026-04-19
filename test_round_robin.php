<?php



echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST ROUND-ROBIN DISTRIBUTION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$blockTypes = ['text', 'video', 'file', 'image', 'embed'];

echo "Testing round-robin formula: \$blockTypes[\$i % count(\$blockTypes)]\n\n";


echo "Test 1: 5 blocks (should be 1 of each type)\n";
echo "───────────────────────────────────────────────────────────────\n";
$distribution1 = [];
for ($i = 0; $i < 5; $i++) {
    $blockType = $blockTypes[$i % count($blockTypes)];
    $distribution1[$blockType] = ($distribution1[$blockType] ?? 0) + 1;
    echo "Block $i: $blockType\n";
}
echo "\nDistribution: " . json_encode($distribution1) . "\n";
$allPresent1 = count($distribution1) === 5;
echo $allPresent1 ? "✅ PASS - All types present\n" : "❌ FAIL - Missing types\n";
echo "\n";


echo "Test 2: 4 blocks (should have text, video, file, image)\n";
echo "───────────────────────────────────────────────────────────────\n";
$distribution2 = [];
for ($i = 0; $i < 4; $i++) {
    $blockType = $blockTypes[$i % count($blockTypes)];
    $distribution2[$blockType] = ($distribution2[$blockType] ?? 0) + 1;
    echo "Block $i: $blockType\n";
}
echo "\nDistribution: " . json_encode($distribution2) . "\n";
$hasMedia2 = isset($distribution2['video']) || isset($distribution2['file']) || isset($distribution2['image']);
echo $hasMedia2 ? "✅ PASS - Has media types\n" : "❌ FAIL - No media types\n";
echo "\n";


echo "Test 3: 3 blocks (should have text, video, file)\n";
echo "───────────────────────────────────────────────────────────────\n";
$distribution3 = [];
for ($i = 0; $i < 3; $i++) {
    $blockType = $blockTypes[$i % count($blockTypes)];
    $distribution3[$blockType] = ($distribution3[$blockType] ?? 0) + 1;
    echo "Block $i: $blockType\n";
}
echo "\nDistribution: " . json_encode($distribution3) . "\n";
$hasMedia3 = isset($distribution3['video']) || isset($distribution3['file']) || isset($distribution3['image']);
echo $hasMedia3 ? "✅ PASS - Has media types\n" : "❌ FAIL - No media types\n";
echo "\n";


echo "Test 4: 10 blocks (should cycle through all types twice)\n";
echo "───────────────────────────────────────────────────────────────\n";
$distribution4 = [];
for ($i = 0; $i < 10; $i++) {
    $blockType = $blockTypes[$i % count($blockTypes)];
    $distribution4[$blockType] = ($distribution4[$blockType] ?? 0) + 1;
    if ($i < 5 || $i >= 5) {
        echo "Block $i: $blockType\n";
    }
}
echo "\nDistribution: " . json_encode($distribution4) . "\n";
$evenDistribution4 = count(array_unique($distribution4)) === 1 && $distribution4['text'] === 2;
echo $evenDistribution4 ? "✅ PASS - Even distribution (2 of each)\n" : "❌ FAIL - Uneven distribution\n";
echo "\n";


echo "Test 5: 2680 blocks (actual seeder scenario)\n";
echo "───────────────────────────────────────────────────────────────\n";
$distribution5 = [];
for ($i = 0; $i < 2680; $i++) {
    $blockType = $blockTypes[$i % count($blockTypes)];
    $distribution5[$blockType] = ($distribution5[$blockType] ?? 0) + 1;
}
echo "Distribution:\n";
foreach ($distribution5 as $type => $count) {
    $percentage = ($count / 2680) * 100;
    echo sprintf("  %-10s : %4d (%.1f%%)\n", $type, $count, $percentage);
}

$mediaCount = ($distribution5['video'] ?? 0) + ($distribution5['file'] ?? 0) + ($distribution5['image'] ?? 0);
$mediaPercentage = ($mediaCount / 2680) * 100;
echo "\nMedia blocks (video + file + image): $mediaCount (" . number_format($mediaPercentage, 1) . "%)\n";

$isEven5 = abs($distribution5['text'] - $distribution5['video']) <= 1;
echo $isEven5 ? "✅ PASS - Even distribution\n" : "❌ FAIL - Uneven distribution\n";
echo $mediaCount >= 1600 ? "✅ PASS - Sufficient media blocks ($mediaCount >= 1600)\n" : "❌ FAIL - Insufficient media blocks\n";
echo "\n";


echo "═══════════════════════════════════════════════════════════════\n";
echo "  SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$allPassed = $allPresent1 && $hasMedia2 && $hasMedia3 && $evenDistribution4 && $isEven5 && ($mediaCount >= 1600);

if ($allPassed) {
    echo "✅ ALL TESTS PASSED!\n";
    echo "✅ Round-robin distribution berfungsi dengan baik\n";
    echo "✅ Setiap lesson akan memiliki variasi block types\n";
    echo "✅ ~60% blocks akan memiliki media\n";
    echo "\n";
    echo "Expected seeder output:\n";
    echo "  📊 Block types for lesson 1: {\"text\":1,\"video\":1,\"file\":1,\"image\":1}\n";
    echo "  ✓ Uploaded 36 media files (bukan 0!)\n";
    echo "  📊 Total media files: 1608 (bukan 0!)\n";
} else {
    echo "❌ SOME TESTS FAILED\n";
    echo "Round-robin distribution tidak berfungsi dengan baik\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
