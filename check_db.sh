#!/bin/bash

echo "Checking database connection and state..."

# Check if we can connect to the database
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo 'Database connection: OK' . PHP_EOL;
    echo 'Driver: ' . \$pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . PHP_EOL;
    echo 'Server version: ' . \$pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
    
    // Check if master_data table exists
    \$exists = DB::select(\"SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'master_data')\");
    echo 'master_data table exists: ' . (\$exists[0]->exists ? 'YES' : 'NO') . PHP_EOL;
    
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
"
