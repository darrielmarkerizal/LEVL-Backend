<?php

/**
 * Script to fix User status raw strings in test files
 * 
 * This script replaces raw string status values with UserStatus enum
 * in all test files.
 * 
 * Usage: php fix_user_status_tests.php
 */

$files = [
    'Levl-BE/Modules/Auth/tests/Feature/Auth/EmailVerificationTest.php',
    'Levl-BE/Modules/Auth/tests/Feature/Auth/RefreshTokenTest.php',
    'Levl-BE/Modules/Auth/tests/Feature/Auth/LogoutTest.php',
    'Levl-BE/Modules/Auth/tests/Feature/Account/AccountRestoreTest.php',
    'Levl-BE/Modules/Auth/tests/Feature/BulkOperations/BulkDeactivateTest.php',
    'Levl-BE/Modules/Auth/tests/Feature/BulkOperations/BulkActivateTest.php',
    'Levl-BE/Modules/Auth/tests/Integration/AccountDeletionFlowTest.php',
    'Levl-BE/Modules/Auth/tests/Feature/UserManagement/UpdateUserStatusTest.php',
    'Levl-BE/tests/Feature/Api/PaginationFilteringTest.php',
    'Levl-BE/tests/Feature/Api/AuthModuleTest.php',
];

$replacements = [
    // User::factory()->create(['status' => 'active'])
    "/User::factory\(\)->create\(\['status' => 'active'\]\)/" => "User::factory()->create(['status' => UserStatus::Active])",
    "/User::factory\(\)->create\(\['status' => 'inactive'\]\)/" => "User::factory()->create(['status' => UserStatus::Inactive])",
    "/User::factory\(\)->create\(\['status' => 'pending'\]\)/" => "User::factory()->create(['status' => UserStatus::Pending])",
    "/User::factory\(\)->create\(\['status' => 'banned'\]\)/" => "User::factory()->create(['status' => UserStatus::Banned])",
    
    // User::factory()->count(3)->create(['status' => 'active'])
    "/User::factory\(\)->count\(\d+\)->create\(\['status' => 'active'\]\)/" => "User::factory()->count($1)->create(['status' => UserStatus::Active])",
    "/User::factory\(\)->count\(\d+\)->create\(\['status' => 'inactive'\]\)/" => "User::factory()->count($1)->create(['status' => UserStatus::Inactive])",
    "/User::factory\(\)->count\(\d+\)->create\(\['status' => 'pending'\]\)/" => "User::factory()->count($1)->create(['status' => UserStatus::Pending])",
    
    // Multiple attributes
    "/'email_verified_at' => now\(\), 'status' => 'active'/" => "'email_verified_at' => now(), 'status' => UserStatus::Active",
    "/'password' => bcrypt\(\$password\), 'status' => 'active'/" => "'password' => bcrypt(\$password), 'status' => UserStatus::Active",
    
    // In arrays
    "/'status' => 'active',/" => "'status' => UserStatus::Active,",
    "/'status' => 'inactive',/" => "'status' => UserStatus::Inactive,",
    "/'status' => 'pending',/" => "'status' => UserStatus::Pending,",
    "/'status' => 'banned',/" => "'status' => UserStatus::Banned,",
];

$useStatements = [
    'use Modules\Auth\Enums\UserStatus;',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Apply replacements
    foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Add use statement if UserStatus is used and not already imported
    if (strpos($content, 'UserStatus::') !== false && strpos($content, 'use Modules\Auth\Enums\UserStatus;') === false) {
        // Find the last use statement
        if (preg_match('/^use .+;$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $lastUsePos = $matches[0][1] + strlen($matches[0][0]);
            $content = substr_replace($content, "\nuse Modules\Auth\Enums\UserStatus;", $lastUsePos, 0);
        } else {
            // No use statements, add after <?php
            $content = preg_replace('/^<\?php\n/', "<?php\n\nuse Modules\Auth\Enums\UserStatus;\n", $content);
        }
    }
    
    // Only write if content changed
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "✅ Fixed: $file\n";
    } else {
        echo "⏭️  No changes: $file\n";
    }
}

echo "\n✅ Done! All test files have been updated.\n";
echo "⚠️  Please review the changes and run tests to ensure everything works.\n";
