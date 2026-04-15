#!/bin/bash

echo "=== Cleaning PostgreSQL Database ==="
echo ""
echo "This will:"
echo "1. Drop all tables"
echo "2. Drop all sequences"  
echo "3. Drop all views"
echo "4. Drop all functions"
echo "5. Drop all types"
echo ""

read -p "Are you sure? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Aborted."
    exit 1
fi

php artisan tinker --execute="
try {
    echo 'Dropping all tables...' . PHP_EOL;
    
    // Get all tables
    \$tables = DB::select(\"
        SELECT tablename 
        FROM pg_tables 
        WHERE schemaname = 'public'
    \");
    
    foreach (\$tables as \$table) {
        DB::statement('DROP TABLE IF EXISTS ' . \$table->tablename . ' CASCADE');
        echo '  ✓ Dropped: ' . \$table->tablename . PHP_EOL;
    }
    
    echo PHP_EOL . 'Dropping all sequences...' . PHP_EOL;
    
    // Get all sequences
    \$sequences = DB::select(\"
        SELECT sequence_name 
        FROM information_schema.sequences 
        WHERE sequence_schema = 'public'
    \");
    
    foreach (\$sequences as \$seq) {
        DB::statement('DROP SEQUENCE IF EXISTS ' . \$seq->sequence_name . ' CASCADE');
        echo '  ✓ Dropped: ' . \$seq->sequence_name . PHP_EOL;
    }
    
    echo PHP_EOL . 'Dropping all views...' . PHP_EOL;
    
    // Get all views
    \$views = DB::select(\"
        SELECT table_name 
        FROM information_schema.views 
        WHERE table_schema = 'public'
    \");
    
    foreach (\$views as \$view) {
        DB::statement('DROP VIEW IF EXISTS ' . \$view->table_name . ' CASCADE');
        echo '  ✓ Dropped: ' . \$view->table_name . PHP_EOL;
    }
    
    echo PHP_EOL . '✅ Database cleaned successfully!' . PHP_EOL;
    
} catch (Exception \$e) {
    echo PHP_EOL . '❌ Error: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "Now you can run: php artisan migrate:fresh --seed"
