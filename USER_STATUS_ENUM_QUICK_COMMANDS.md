# User Status ENUM - Quick Commands

## 🚀 Quick Start (Copy & Paste)

### 1. Fix Test Files
```bash
cd Levl-BE
php fix_user_status_tests.php
```

### 2. Backup Database
```bash
# PostgreSQL
pg_dump levl_db > backup_user_status_$(date +%Y%m%d_%H%M%S).sql

# Or check current data
psql levl_db -c "SELECT status, COUNT(*) FROM users GROUP BY status;"
```

### 3. Run Migration
```bash
cd Levl-BE
php artisan migrate --path=Modules/Auth/database/migrations/2026_03_24_104653_convert_users_status_to_enum.php
```

### 4. Verify
```bash
# Check ENUM created
psql levl_db -c "SELECT typname FROM pg_type WHERE typname = 'user_status';"

# Check column type
psql levl_db -c "SELECT column_name, data_type, udt_name FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'status';"

# Test query
psql levl_db -c "SELECT id, name, status FROM users LIMIT 5;"
```

### 5. Run Tests
```bash
cd Levl-BE
php artisan test
```

## 🔄 Rollback (If Needed)
```bash
cd Levl-BE
php artisan migrate:rollback --step=1
```

## 📊 Check Status
```sql
-- Current status distribution
SELECT status, COUNT(*) as count 
FROM users 
GROUP BY status 
ORDER BY count DESC;

-- Check for invalid values (before migration)
SELECT DISTINCT status 
FROM users 
WHERE status NOT IN ('pending', 'active', 'inactive', 'banned');
```

## 🧪 Test Queries (After Migration)
```sql
-- Test ENUM query
SELECT * FROM users WHERE status = 'active'::user_status LIMIT 5;

-- Test invalid value (should fail)
INSERT INTO users (name, email, password, status) 
VALUES ('Test', 'test@test.com', 'password', 'invalid');
-- Expected: ERROR: invalid input value for enum user_status: "invalid"
```

## 📝 One-Liner Complete Process
```bash
cd Levl-BE && \
php fix_user_status_tests.php && \
pg_dump levl_db > backup_$(date +%Y%m%d_%H%M%S).sql && \
php artisan migrate --path=Modules/Auth/database/migrations/2026_03_24_104653_convert_users_status_to_enum.php && \
php artisan test && \
echo "✅ Migration complete!"
```

---

**Quick Reference**: [USER_STATUS_ENUM_MIGRATION.md](USER_STATUS_ENUM_MIGRATION.md)  
**Full Guide**: [USER_STATUS_ENUM_IMPLEMENTATION_SUMMARY.md](USER_STATUS_ENUM_IMPLEMENTATION_SUMMARY.md)
