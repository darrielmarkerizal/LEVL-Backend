# Production Deployment Checklist - Unlimited Attempts System

## Status: ✅ READY FOR DEPLOYMENT

## Migration Safety
The migration `2026_03_03_081558_add_attempt_number_back_to_submissions.php` is now **production-safe**:
- ✅ Checks if `attempt_number` column exists before adding
- ✅ Checks if indexes exist before creating
- ✅ Safe to run on databases where column already exists
- ✅ Safe to run on fresh databases
- ✅ Proper rollback with existence checks

## Pre-Deployment Steps

### 1. Code Quality Checks
```bash
vendor/bin/pint
vendor/bin/phpstan analyse Modules/Learning
composer test
```

### 2. Backup Database (Production)
```bash
pg_dump -U postgres -d your_database > backup_before_attempt_number_$(date +%Y%m%d_%H%M%S).sql
```

## Deployment Steps

### 1. Pull Latest Code
```bash
git pull origin main
```

### 2. Run Migration
```bash
php artisan migrate
```

**Expected Output:**
- If column exists: Migration will skip column creation, only create missing indexes
- If column missing: Migration will add column and indexes
- No errors should occur

### 3. Reload Octane
```bash
php artisan octane:reload
```

### 4. Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Post-Deployment Verification

### 1. Check Database Schema
```sql
-- Verify attempt_number column exists
SELECT column_name, data_type, column_default 
FROM information_schema.columns 
WHERE table_name = 'submissions' 
AND column_name = 'attempt_number';

-- Verify indexes exist
SELECT indexname, indexdef 
FROM pg_indexes 
WHERE tablename IN ('submissions', 'quiz_submissions')
AND indexname LIKE '%attempt_number%';
```

### 2. Test API Endpoints

#### Test Assignment Submission (Student)
```bash
# Start new attempt
POST /api/student/assignments/{id}/submissions/start
Authorization: Bearer {student_token}

# Expected: Returns submission with attempt_number = (previous_count + 1)
```

#### Test Quiz Submission (Student)
```bash
# Start new quiz attempt
POST /api/student/quizzes/{id}/submissions/start
Authorization: Bearer {student_token}

# Expected: Returns submission with attempt_number = (previous_count + 1)
```

#### Test Submission History (Student)
```bash
# Get all attempts
GET /api/student/assignments/{id}/submissions
Authorization: Bearer {student_token}

# Expected: Returns array with attempt_number for each submission
```

### 3. Verify Existing Data
```sql
-- Check if existing submissions have attempt_number
SELECT id, assignment_id, user_id, attempt_number, status
FROM submissions
WHERE attempt_number IS NULL
LIMIT 10;

-- Should return 0 rows (all should have attempt_number = 1 by default)
```

### 4. Test Multiple Attempts Flow
1. Student starts assignment → `attempt_number = 1`
2. Student submits → Status changes to submitted/graded
3. Student starts again → `attempt_number = 2`
4. Verify both attempts visible in history
5. Verify highest score is used for progression

## Rollback Plan (If Issues Occur)

### Option 1: Rollback Migration
```bash
php artisan migrate:rollback --step=1
php artisan octane:reload
```

### Option 2: Restore Database Backup
```bash
psql -U postgres -d your_database < backup_before_attempt_number_YYYYMMDD_HHMMSS.sql
php artisan octane:reload
```

## Known Issues & Solutions

### Issue: Migration fails with "column already exists"
**Solution:** This should NOT happen with the updated migration (it checks existence first)

### Issue: Existing submissions have NULL attempt_number
**Solution:** Run data migration script:
```sql
UPDATE submissions 
SET attempt_number = 1 
WHERE attempt_number IS NULL;

UPDATE quiz_submissions 
SET attempt_number = 1 
WHERE attempt_number IS NULL;
```

### Issue: Students see duplicate attempts
**Solution:** Check if `countAttempts()` is working correctly:
```sql
-- Verify attempt numbers are sequential per user+assignment
SELECT assignment_id, user_id, attempt_number, status, created_at
FROM submissions
WHERE user_id = {test_user_id}
ORDER BY assignment_id, attempt_number;
```

## Success Criteria

✅ Migration runs without errors
✅ All existing submissions have `attempt_number = 1`
✅ New submissions get correct `attempt_number` (previous_count + 1)
✅ Students can view all their attempts in history
✅ API responses include `attempt_number` field
✅ No performance degradation (indexes working)
✅ No Octane state leaks (service is stateless)

## Files Modified

### Migration
- `Modules/Learning/database/migrations/2026_03_03_081558_add_attempt_number_back_to_submissions.php`

### Services
- `Modules/Learning/app/Services/Support/SubmissionCreationProcessor.php`
- `Modules/Learning/app/Services/QuizSubmissionService.php`

### Resources
- `Modules/Learning/app/Http/Resources/SubmissionResource.php`
- `Modules/Learning/app/Http/Resources/SubmissionListResource.php`
- `Modules/Learning/app/Http/Resources/QuizSubmissionResource.php`

## Documentation
- `UNLIMITED_ATTEMPTS_SYSTEM.md` - Complete system documentation
- `DOKUMENTASI_API_LEARNING_SCHEMES_LENGKAP.md` - API documentation

## Contact & Support
If issues occur during deployment, check:
1. Laravel logs: `storage/logs/laravel.log`
2. PostgreSQL logs
3. Octane worker status: `php artisan octane:status`

---

**Deployment Date:** _____________
**Deployed By:** _____________
**Verification Completed:** ☐ Yes ☐ No
**Issues Encountered:** _____________
