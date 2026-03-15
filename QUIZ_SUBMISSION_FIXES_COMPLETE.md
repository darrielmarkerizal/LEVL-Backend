# Quiz Submission Fixes - Complete Summary

## Issues Fixed

### 1. SubmissionPolicy Relation Error
**Problem**: Policy tried to load `assignment.lesson.unit.course` but Assignment model has `unit` directly, not `lesson`.

**Fixed in**: `Modules/Learning/app/Policies/SubmissionPolicy.php`
- Changed `assignment.lesson.unit.course` to `assignment.unit.course` in `view()` method (line 29)
- Changed `assignment.lesson.unit.course` to `assignment.unit.course` in `grade()` method (line 120)

### 2. Missing attempt_number Field
**Problem**: `QuizSubmissionResource` referenced `attempt_number` but it wasn't in model's fillable/casts.

**Fixed in**: `Modules/Learning/app/Models/QuizSubmission.php`
- Added `attempt_number` to `$fillable` array
- Added `attempt_number` to `$casts` array with type `integer`

**Note**: Migration already exists (`2026_03_03_081558_add_attempt_number_back_to_submissions.php`)

### 3. Missing time_spent_seconds Calculation
**Problem**: `time_spent_seconds` was null because it wasn't calculated on submission.

**Fixed in**: `Modules/Learning/app/Services/QuizSubmissionService.php`
- Added calculation in `submit()` method: `now()->diffInSeconds($submission->started_at)`
- Updates `time_spent_seconds` field when quiz is submitted

### 4. Validation Already Implemented
**Status**: ✅ Already working correctly

The following validations were already properly implemented in `QuizSubmissionService`:
- Prevent starting quiz if locked (prerequisite check)
- Prevent starting quiz if draft exists
- Prevent starting quiz if pending grading
- Prevent saving answer if not in draft status
- Prevent submitting if not in draft status

## Files Modified

1. `Levl-BE/Modules/Learning/app/Models/QuizSubmission.php`
   - Added `attempt_number` to fillable and casts

2. `Levl-BE/Modules/Learning/app/Policies/SubmissionPolicy.php`
   - Fixed relation path from `assignment.lesson.unit.course` to `assignment.unit.course`

3. `Levl-BE/Modules/Learning/app/Services/QuizSubmissionService.php`
   - Added `time_spent_seconds` calculation on submit

## Testing Recommendations

Test the following scenarios:

1. **Start Quiz**
   - Verify `attempt_number` is set correctly
   - Verify `started_at` is set
   - Verify cannot start if draft exists
   - Verify cannot start if pending grading

2. **Submit Quiz**
   - Verify `time_spent_seconds` is calculated
   - Verify `duration` accessor works
   - Verify `submitted_at` is set

3. **View Submission**
   - Student can view their own submission
   - Instructor can view submissions in their course
   - Admin can view all submissions

4. **Grade Submission**
   - Instructor can grade submissions in their course
   - Admin can grade all submissions

## API Response Example

After fixes, quiz submission response should include:

```json
{
  "id": 1,
  "status": "submitted",
  "grading_status": "graded",
  "score": "82.00",
  "final_score": "82.00",
  "attempt_number": 1,
  "time_spent_seconds": 1234,
  "duration": 1234,
  "started_at": "2026-03-15T10:00:00+00:00",
  "submitted_at": "2026-03-15T10:20:34+00:00",
  "is_passed": true
}
```

## Related Models

- **Quiz**: Has `unit` relation (not `lesson`)
- **Assignment**: Has `unit` relation (not `lesson`)
- **Submission**: For Assignment (file upload), has `assignment` relation
- **QuizSubmission**: For Quiz (questions), has `quiz` relation

## Status: ✅ Complete

All issues have been resolved and diagnostics are clean.
