<?php

/**
 * Force clean the database by dropping the public schema and recreating it
 * This is more aggressive than migrate:fresh and works when transactions are stuck
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Force Cleaning Database ===\n\n";
echo "⚠️  WARNING: This will drop ALL database objects!\n";
echo "Press Ctrl+C to cancel, or Enter to continue...";
fgets(STDIN);

try {
    echo "\n1. Dropping public schema...\n";
    DB::statement('DROP SCHEMA IF EXISTS public CASCADE');
    echo "   ✓ Schema dropped\n";
    
    echo "\n2. Recreating public schema...\n";
    DB::statement('CREATE SCHEMA public');
    echo "   ✓ Schema created\n";
    
    echo "\n3. Granting permissions...\n";
    $dbUser = config('database.connections.pgsql.username');
    DB::statement("GRANT ALL ON SCHEMA public TO {$dbUser}");
    DB::statement('GRANT ALL ON SCHEMA public TO public');
    echo "   ✓ Permissions granted\n";
    
    echo "\n✅ Database cleaned successfully!\n";
    echo "\nNow run: php artisan migrate --seed\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nTry running this SQL manually in psql:\n";
    echo "  DROP SCHEMA public CASCADE;\n";
    echo "  CREATE SCHEMA public;\n";
    echo "  GRANT ALL ON SCHEMA public TO " . config('database.connections.pgsql.username') . ";\n";
    echo "  GRANT ALL ON SCHEMA public TO public;\n";
    exit(1);
}
