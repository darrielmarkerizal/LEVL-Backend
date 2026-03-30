# Database Optimization - Breaking Changes & Migration Guide

## Overview
Phase 3 optimization consolidated redundant logging tables. This document outlines breaking changes and migration paths.

---

## Breaking Changes

### 1. `audit_logs` Table Removed ⚠️

**What Changed:**
- Table `audit_logs` has been dropped
- Data migrated to `activity_log` (Spatie Activity Log)
- All audit logging now uses Spatie Activity Log

**Affected Code:**
- `Modules\Common\Models\AuditLog` model
- `Modules\Common\Services\AssessmentAuditService`
- `Modules\Common\Services\AuditLogQueryService`
- `Modules\Common\Repositories\AuditRepository`
- `Modules\Operations\Http\Middleware\AuditLogMiddleware`
- `App\Jobs\CreateAuditJob`

**Migration Path:**

#### Option 1: Create AuditLog Facade (Recommended for Quick Fix)
Create a facade that wraps Spatie Activity Log:

```php
<?php

namespace Modules\Common\Models;

use Spatie\Activitylog\Models\Activity;

class AuditLog extends Activity
{
    /**
     * Backward compatibility method
     */
    public static function logAction(
        string $action,
        $subject,
        $actor,
        array $context = []
    ): Activity {
        return activity()
            ->causedBy($actor)
            ->performedOn($subject)
            ->withProperties($context)
            ->log($action);
    }
}
```

#### Option 2: Refactor to Use Spatie Directly (Recommended Long-term)
Replace all `AuditLog::logAction()` calls with:

```php
// Old way
AuditLog::logAction($action, $subject, $actor, $context);

// New way
activity()
    ->causedBy($actor)
    ->performedOn($subject)
    ->withProperties($context)
    ->log($action);
```

---

### 2. `profile_audit_logs` Table Removed ⚠️

**What Changed:**
- Table `profile_audit_logs` has been dropped
- Data migrated to `activity_log` with `log_name='profile_audit'`

**Affected Code:**
- `Modules\Auth\Models\ProfileAuditLog` model
- `Modules\Auth\Repositories\ProfileAuditLogRepository`

**Migration Path:**

Query migrated data:
```php
// Old way
ProfileAuditLog::where('user_id', $userId)->get();

// New way
Activity::where('log_name', 'profile_audit')
    ->where('subject_id', $userId)
    ->where('subject_type', 'Modules\Auth\Models\User')
    ->get();
```

---

### 3. `user_activities` Table Removed ⚠️

**What Changed:**
- Table `user_activities` has been dropped
- Data migrated to `activity_log` with `log_name='user_activity'`

**Migration Path:**

Query migrated data:
```php
// Old way
UserActivity::where('user_id', $userId)->get();

// New way
Activity::where('log_name', 'user_activity')
    ->where('causer_id', $userId)
    ->get();
```

---

### 4. `post_views` Table Removed ⚠️

**What Changed:**
- Table `post_views` has been dropped
- Data migrated to polymorphic `content_reads` table
- `PostView` model no longer exists

**Affected Code:**
- `Modules\Notifications\Models\PostView` model
- `Modules\Notifications\Services\PostService::markAsViewed()`

**Migration Path:**

Already updated in PostService:
```php
// Old way
PostView::firstOrCreate([
    'post_id' => $post->id,
    'user_id' => $userId,
]);

// New way (already implemented)
\Modules\Common\Models\ContentRead::firstOrCreate([
    'readable_type' => 'Modules\Notifications\Models\Post',
    'readable_id' => $post->id,
    'user_id' => $userId,
]);
```

---

### 5. `submission_files` Table Removed ⚠️

**What Changed:**
- Table `submission_files` has been dropped (Phase 1)
- Files now stored directly via Spatie Media Library on `submissions` table

**Affected Code:**
- `Modules\Learning\Models\SubmissionFile` model
- `Modules\Learning\Seeders\SubmissionFileSeeder`
- `Modules\Learning\Seeders\SequentialProgressSeeder`

**Migration Path:**

Already updated in seeders:
```php
// Old way
$submissionFile = SubmissionFile::create(['submission_id' => $submission->id]);
$submissionFile->addMedia($file)->toMediaCollection('file');

// New way (already implemented)
$submission->addMedia($file)->toMediaCollection('files');
```

---

### 6. `grading_rubrics` & `grading_rubric_criteria` Tables Removed ⚠️

**What Changed:**
- Tables dropped (Phase 3) - completely isolated, not used anywhere

**Affected Code:**
- `Modules\Grading\Models\GradingRubric` model (if exists)

**Migration Path:**
- No migration needed - tables were not in use

---

## Seeders Updated

### Fixed Seeders:
1. ✅ `AuditLogSeeder` - Now uses Spatie Activity Log
2. ✅ `SequentialProgressSeeder` - Removed `submission_files` references
3. ✅ `CourseSeederEnhanced` - Updated for polymorphic tagging

### Seeders to Remove (if exist):
- `SubmissionFileSeeder` - Table no longer exists
- `GradingRubricSeeder` - Table no longer exists

---

## Services That Need Refactoring

### High Priority:
1. **AssessmentAuditService** - Replace `AuditLog::logAction()` with Spatie
2. **AuditLogQueryService** - Update queries to use `Activity` model
3. **AuditRepository** - Update to use `Activity` model
4. **AuditLogMiddleware** - Update to use Spatie activity helper
5. **CreateAuditJob** - Update to use Spatie activity helper

### Medium Priority:
6. **ProfileAuditLogRepository** - Update to query `activity_log`
7. **MeilisearchImportAll** - Remove `AuditLog` from import list

---

## Quick Fix Implementation

To minimize immediate breaking changes, create this facade:

```php
<?php
// File: Levl-BE/Modules/Common/app/Models/AuditLog.php

namespace Modules\Common\Models;

use Spatie\Activitylog\Models\Activity;

/**
 * Backward compatibility facade for AuditLog
 * Wraps Spatie Activity Log
 */
class AuditLog extends Activity
{
    protected $table = 'activity_log';

    /**
     * Log an action (backward compatibility)
     */
    public static function logAction(
        string $action,
        $subject = null,
        $actor = null,
        array $context = []
    ): Activity {
        $activity = activity()->log($action);

        if ($subject) {
            $activity->performedOn($subject);
        }

        if ($actor) {
            $activity->causedBy($actor);
        }

        if (!empty($context)) {
            $activity->withProperties($context);
        }

        return $activity;
    }
}
```

This allows existing code to continue working while you gradually refactor to use Spatie directly.

---

## Testing Checklist

After implementing changes:

- [ ] Run all seeders: `php artisan db:seed`
- [ ] Test audit logging in application
- [ ] Test profile audit logs
- [ ] Test post views tracking
- [ ] Test submission file uploads
- [ ] Verify activity_log contains all migrated data
- [ ] Check Meilisearch imports (remove AuditLog if present)

---

## Rollback Instructions

If you need to rollback Phase 3 migrations:

```bash
php artisan migrate:rollback --step=5
```

This will:
1. Restore `post_views` table
2. Restore `audit_logs` table
3. Restore `user_activities` table
4. Restore `profile_audit_logs` table
5. Restore `grading_rubrics` tables
6. Remove polymorphic constraints

---

## Support

For questions or issues with migration, refer to:
- `DATABASE_OPTIMIZATION_PHASE3.md` - Detailed changes
- Spatie Activity Log docs: https://spatie.be/docs/laravel-activitylog
