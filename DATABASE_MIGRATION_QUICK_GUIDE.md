# Database Migration Quick Guide

## 🚀 Quick Reference untuk Database Optimization

### Priority Matrix

| Priority | Tables | Action | Timeline |
|----------|--------|--------|----------|
| 🔴 HIGH | courses, enrollments, master_data | Migrate to ENUM / Refactor | Week 1-2 |
| 🟡 MEDIUM | assignments, quizzes, posts, notifications | Migrate to ENUM | Week 3-4 |
| 🟢 LOW | announcements, certificates, progress tables | Migrate to ENUM | Month 2 |

---

## 📋 Top 5 Critical Issues

### 1. Master Data Table (CRITICAL)
**Problem**: EAV anti-pattern
**Solution**: Split into dedicated tables
**Impact**: 🔴 Performance, Maintainability

### 2. Courses Table Status Fields
**Problem**: 4 VARCHAR fields yang seharusnya ENUM
**Solution**: Create PostgreSQL ENUMs
**Impact**: 🔴 Type Safety, Performance

### 3. Enrollments Status
**Problem**: VARCHAR status field
**Solution**: Create ENUM
**Impact**: 🔴 Query Performance

### 4. Telescope Tables in Production
**Problem**: Development tables di production
**Solution**: DROP tables
**Impact**: 🟡 Storage, Security

### 5. Device Information Redundancy
**Problem**: Device info di 3 tabel berbeda
**Solution**: Normalize to user_devices table
**Impact**: 🟡 Storage, Consistency

---

## 🛠️ Migration Commands

### Create PostgreSQL ENUM
```sql
-- Course Status
CREATE TYPE course_status AS ENUM ('draft', 'published', 'archived');
ALTER TABLE courses ALTER COLUMN status TYPE course_status USING status::course_status;

-- Course Type
CREATE TYPE course_type AS ENUM ('okupasi', 'klaster', 'kualifikasi');
ALTER TABLE courses ALTER COLUMN type TYPE course_type USING type::course_type;

-- Course Level
CREATE TYPE course_level AS ENUM ('dasar', 'lanjut', 'mahir');
ALTER TABLE courses ALTER COLUMN level_tag TYPE course_level USING level_tag::course_level;

-- Enrollment Type
CREATE TYPE enrollment_type AS ENUM ('auto_accept', 'manual_approval', 'enrollment_key');
ALTER TABLE courses ALTER COLUMN enrollment_type TYPE enrollment_type USING enrollment_type::enrollment_type;

-- Enrollment Status
CREATE TYPE enrollment_status AS ENUM ('pending', 'active', 'completed', 'cancelled', 'expired');
ALTER TABLE enrollments ALTER COLUMN status TYPE enrollment_status USING status::enrollment_status;
```

### Laravel Migration Example
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create ENUM type
        DB::statement("CREATE TYPE course_status AS ENUM ('draft', 'published', 'archived')");
        
        // Alter column
        DB::statement("ALTER TABLE courses ALTER COLUMN status TYPE course_status USING status::course_status");
    }

    public function down(): void
    {
        // Revert to VARCHAR
        DB::statement("ALTER TABLE courses ALTER COLUMN status TYPE VARCHAR(255)");
        
        // Drop ENUM type
        DB::statement("DROP TYPE IF EXISTS course_status");
    }
};
```

### Drop Telescope Tables
```sql
-- Production only!
DROP TABLE IF EXISTS telescope_monitoring CASCADE;
DROP TABLE IF EXISTS telescope_entries_tags CASCADE;
DROP TABLE IF EXISTS telescope_entries CASCADE;
```

---

## 🔍 Audit Queries

### Check Table Sizes
```sql
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size,
    pg_total_relation_size(schemaname||'.'||tablename) AS bytes
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY bytes DESC
LIMIT 20;
```

### Check Row Counts
```sql
SELECT 
    schemaname,
    tablename,
    n_live_tup AS row_count,
    n_dead_tup AS dead_rows
FROM pg_stat_user_tables
WHERE schemaname = 'public'
ORDER BY n_live_tup DESC;
```

### Find Unused Tables (No Rows)
```sql
SELECT 
    schemaname,
    tablename
FROM pg_stat_user_tables
WHERE schemaname = 'public'
AND n_live_tup = 0
ORDER BY tablename;
```

### Check VARCHAR Fields
```sql
SELECT 
    table_name,
    column_name,
    data_type,
    character_maximum_length
FROM information_schema.columns
WHERE table_schema = 'public'
AND data_type = 'character varying'
AND column_name LIKE '%status%'
OR column_name LIKE '%type%'
ORDER BY table_name, column_name;
```

---

## 📊 Before/After Comparison

### Storage Impact
```sql
-- Before: VARCHAR(255)
-- Size per row: ~255 bytes (worst case)

-- After: ENUM
-- Size per row: 4 bytes

-- Savings per row: ~251 bytes
-- For 100,000 rows: ~24 MB saved per column
```

### Performance Impact
```sql
-- Before: VARCHAR comparison
SELECT * FROM courses WHERE status = 'published';
-- Index scan: ~10ms

-- After: ENUM comparison
SELECT * FROM courses WHERE status = 'published'::course_status;
-- Index scan: ~7ms (30% faster)
```

---

## ⚠️ Migration Checklist

### Pre-Migration
- [ ] Backup database
- [ ] Test migration in staging
- [ ] Notify team about downtime
- [ ] Check current data values
- [ ] Verify ENUM values cover all cases

### During Migration
- [ ] Put application in maintenance mode
- [ ] Run migration
- [ ] Verify data integrity
- [ ] Check for errors in logs

### Post-Migration
- [ ] Test critical features
- [ ] Monitor query performance
- [ ] Update documentation
- [ ] Remove maintenance mode
- [ ] Monitor for 24 hours

---

## 🎯 Quick Wins (Can Do Today)

### 1. Drop Telescope Tables (5 minutes)
```sql
-- Production only
DROP TABLE IF EXISTS telescope_monitoring CASCADE;
DROP TABLE IF EXISTS telescope_entries_tags CASCADE;
DROP TABLE IF EXISTS telescope_entries CASCADE;
```
**Impact**: Free up storage, improve security

### 2. Add Indexes on Status Columns (10 minutes)
```sql
CREATE INDEX idx_courses_status ON courses(status);
CREATE INDEX idx_enrollments_status ON enrollments(status);
CREATE INDEX idx_assignments_status ON assignments(status);
CREATE INDEX idx_quizzes_status ON quizzes(status);
```
**Impact**: Faster queries on status filters

### 3. Analyze Table Statistics (5 minutes)
```sql
ANALYZE courses;
ANALYZE enrollments;
ANALYZE assignments;
ANALYZE quizzes;
```
**Impact**: Better query planning

---

## 📚 Resources

- [PostgreSQL ENUM Documentation](https://www.postgresql.org/docs/current/datatype-enum.html)
- [Laravel Enum Casting](https://laravel.com/docs/11.x/eloquent-mutators#enum-casting)
- [Database Normalization Guide](https://en.wikipedia.org/wiki/Database_normalization)

---

## 🆘 Rollback Plan

### If Migration Fails
```sql
-- 1. Restore from backup
pg_restore -d levl_db backup_file.dump

-- 2. Or revert specific table
ALTER TABLE courses ALTER COLUMN status TYPE VARCHAR(255);
DROP TYPE IF EXISTS course_status;
```

### If Performance Degrades
```sql
-- 1. Check query plans
EXPLAIN ANALYZE SELECT * FROM courses WHERE status = 'published';

-- 2. Rebuild indexes
REINDEX TABLE courses;

-- 3. Update statistics
ANALYZE courses;
```

---

**Last Updated**: 24 Maret 2026  
**Version**: 1.0
