# Retake System Removal - Progress Tracker

**Date:** 2026-03-03  
**Status:** 🔄 In Progress

---

## Files to Clean

### ✅ Migrations (Done)
- [x] `2026_03_03_100000_remove_retake_columns_from_assessments.php` - Created with hasColumn checks
- [x] `2026_03_03_080000_drop_deprecated_submission_and_assignment_columns.php` - Fixed with hasColumn checks

---

## Code Cleanup Tasks

### 1. Models - Remove from $fillable and $casts

#### Assignment Model
- [ ] Remove `max_attempts` from $fillable
- [ ] Remove `cooldown_minutes` from $fillable
- [ ] Remove `retake_enabled` from $fillable
- [ ] Remove `allow_resubmit` from $fillable

#### Quiz Model
- [ ] Remove `max_attempts` from $fillable
- [ ] Remove `cooldown_minutes` from $fillable
- [ ] Remove `retake_enabled` from $fillable
- [ ] Remove `allow_resubmit` from $fillable

#### Submission Model
- [ ] Remove `attempt_number` from $fillable
- [ ] Remove `is_resubmission` from $fillable
- [ ] Remove `previous_submission_id` from $fillable

#### QuizSubmission Model
- [ ] Remove `attempt_number` from $fillable
- [ ] Remove `is_resubmission` from $fillable

---

### 2. Services - Remove Logic

#### SubmissionCreationProcessor
- [ ] Remove max_attempts validation
- [ ] Remove cooldown check
- [ ] Remove retake_enabled check
- [ ] Remove attempt_number increment

#### QuizSubmissionService
- [ ] Remove max_attempts validation
- [ ] Remove cooldown check
- [ ] Remove retake_enabled check
- [ ] Remove attempt_number increment

#### AssignmentService
- [ ] Remove override-related methods
- [ ] Update duplicateAssignment to remove override params

#### AssignmentDuplicator
- [ ] Remove override parameters
- [ ] Remove allow_resubmit from duplication

#### AssignmentFinder
- [ ] Remove 'overrides' from allowedIncludes

#### AssignmentPrerequisiteProcessor
- [ ] Remove AssignmentOverrideProcessor dependency
- [ ] Remove override check logic

---

### 3. Resources - Remove Fields

#### AssignmentResource
- [ ] Remove `max_attempts`
- [ ] Remove `cooldown_minutes`
- [ ] Remove `retake_enabled`
- [ ] Remove `allow_resubmit`

#### QuizResource
- [ ] Remove `max_attempts`
- [ ] Remove `cooldown_minutes`
- [ ] Remove `retake_enabled`
- [ ] Remove `allow_resubmit`

#### SubmissionResource
- [ ] Remove `attempt_number`
- [ ] Remove `is_resubmission`
- [ ] Remove `previous_submission_id`

#### QuizSubmissionResource
- [ ] Remove `attempt_number`
- [ ] Remove `is_resubmission`

---

### 4. FormRequests - Remove Validation

#### AssignmentRequest
- [ ] Remove `max_attempts` validation
- [ ] Remove `cooldown_minutes` validation
- [ ] Remove `retake_enabled` validation
- [ ] Remove `allow_resubmit` validation

#### QuizRequest
- [ ] Remove `max_attempts` validation
- [ ] Remove `cooldown_minutes` validation
- [ ] Remove `retake_enabled` validation
- [ ] Remove `allow_resubmit` validation

---

### 5. Factories - Remove Fields

#### AssignmentFactory
- [ ] Remove `allow_resubmit`
- [ ] Remove `withResubmit()` state

#### SubmissionFactory
- [ ] Remove `is_resubmission`
- [ ] Remove `attempt_number`
- [ ] Remove `previous_submission_id`
- [ ] Remove `resubmission()` state
- [ ] Remove `attempt()` state

---

### 6. Seeders - Remove Fields

#### ComprehensiveAssessmentSeeder
- [ ] Remove `max_attempts` assignments
- [ ] Remove `cooldown_minutes` assignments
- [ ] Remove `retake_enabled` assignments
- [ ] Remove `allow_resubmit` assignments

---

### 7. Delete Files/Classes

- [ ] Delete `AssignmentOverrideProcessor` class (if exists)
- [ ] Delete Override model (if exists)
- [ ] Delete Override-related migrations (already done in migration)

---

### 8. Update PrerequisiteService

- [ ] Update to use highest score from all submissions
- [ ] Remove any retake/attempt checks

---

## Execution Order

1. ✅ Run migrations
2. 🔄 Update Models (remove from $fillable/$casts)
3. 🔄 Update Services (remove logic)
4. 🔄 Update Resources (remove fields)
5. 🔄 Update FormRequests (remove validation)
6. 🔄 Update Factories (remove fields)
7. 🔄 Update Seeders (remove fields)
8. 🔄 Delete unused files
9. ⏳ Run Pint
10. ⏳ Run PHPStan
11. ⏳ Reload Octane

---

## Commands to Run After Cleanup

```bash
# Fix code style
vendor/bin/pint

# Check for errors
vendor/bin/phpstan analyse Modules/Learning
vendor/bin/phpstan analyse Modules/Schemes

# Reload Octane
php artisan octane:reload
```

---

**Last Updated:** 2026-03-03
