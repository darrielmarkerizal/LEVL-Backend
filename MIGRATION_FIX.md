# Migration Fix for VPS

## Problem
The `migrate:fresh --seed` command is failing with:
```
SQLSTATE[25P02]: In failed sql transaction: 7 ERROR: current transaction is aborted, commands ignored until end of transaction block
```

## Root Cause
This error occurs when PostgreSQL encounters an error in a transaction and then tries to execute subsequent commands in the same transaction. The transaction is in an "aborted" state and won't accept any more commands until it's rolled back.

## Solutions

### Solution 1: Clean Database Manually (Recommended for VPS)

Run the clean script:
```bash
chmod +x clean_db.sh
./clean_db.sh
```

Then run migrations:
```bash
php artisan migrate --seed
```

### Solution 2: Use PostgreSQL Command Line

Connect to PostgreSQL:
```bash
psql -U your_db_user -d your_db_name
```

Drop all tables:
```sql
DROP SCHEMA public CASCADE;
CREATE SCHEMA public;
GRANT ALL ON SCHEMA public TO your_db_user;
GRANT ALL ON SCHEMA public TO public;
```

Exit psql and run:
```bash
php artisan migrate --seed
```

### Solution 3: Check for Lingering Connections

Sometimes active connections prevent proper cleanup:

```bash
# Check active connections
php artisan tinker --execute="
\$connections = DB::select('SELECT * FROM pg_stat_activity WHERE datname = current_database()');
echo 'Active connections: ' . count(\$connections) . PHP_EOL;
foreach (\$connections as \$conn) {
    echo '  PID: ' . \$conn->pid . ' | State: ' . \$conn->state . ' | Query: ' . substr(\$conn->query, 0, 50) . PHP_EOL;
}
"

# Terminate connections (if needed)
php artisan tinker --execute="
DB::statement(\"
    SELECT pg_terminate_backend(pg_stat_activity.pid)
    FROM pg_stat_activity
    WHERE pg_stat_activity.datname = current_database()
    AND pid <> pg_backend_pid()
\");
echo 'Terminated all other connections' . PHP_EOL;
"
```

### Solution 4: Fix the Migration Order

If the issue persists, there might be a dependency issue in the migrations. Check:

1. The `master_data` table migration is the first one (2024_12_07_140000)
2. No other migrations are trying to run before it
3. The migration file itself is valid

## Prevention

To avoid this in the future:

1. Always use `php artisan migrate:fresh` instead of `migrate:fresh --seed` first
2. Then run `php artisan db:seed` separately
3. This helps identify if the issue is in migrations or seeders

## Testing

After cleaning, test with:
```bash
# Test migrations only
php artisan migrate

# If successful, test seeders
php artisan db:seed

# If both work, you can use migrate:fresh --seed
php artisan migrate:fresh --seed
```
