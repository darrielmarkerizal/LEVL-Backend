# UNLIMITED ATTEMPTS SYSTEM - COMPLETE DOCUMENTATION

## Overview

Sistem ini memungkinkan student untuk mengerjakan assignment dan quiz **unlimited times** dengan menyimpan **complete history** dari semua attempts. Highest score yang digunakan untuk passing, bukan latest attempt.

---

## Database Schema

### Submissions Table
```sql
- id: bigint (PK)
- assignment_id: bigint (FK)
- user_id: bigint (FK)
- attempt_number: integer (default: 1) ✅ ADDED BACK
- status: enum (draft, submitted, graded, missing)
- state: enum (in_progress, pending_manual_grading, graded, released)
- score: numeric (nullable)
- submitted_at: timestamp (nullable)
- graded_at: timestamp (nullable)
- created_at: timestamp
- updated_at: timestamp

INDEX: (assignment_id, user_id, attempt_number)
```

### Quiz Submissions Table
```sql
- id: bigint (PK)
- quiz_id: bigint (FK)
- user_id: bigint (FK)
- attempt_number: integer (default: 1) ✅ ADDED BACK
- status: enum (draft, submitted, graded)
- grading_status: enum (pending, graded, released)
- score: numeric (nullable)
- final_score: numeric (nullable)
- started_at: timestamp
- submitted_at: timestamp (nullable)
- graded_at: timestamp (nullable)
- created_at: timestamp
- updated_at: timestamp

INDEX: (quiz_id, user_id, attempt_number)
```

---

## Business Rules

### 1. Unlimited Attempts
- Student dapat mengerjakan assignment/quiz **unlimited times**
- Setiap attempt disimpan dengan `attempt_number` yang increment
- History semua attempts **TIDAK PERNAH DIHAPUS**

### 2. Attempt Number Tracking
- `attempt_number` dimulai dari 1
- Setiap kali student start new attempt, `attempt_number` increment
- Formula: `attempt_number = countAttempts(user_id, assignment_id) + 1`

### 3. Validation Rules
**TIDAK BISA start new attempt jika:**
- Ada draft submission (`status = 'draft'`)
- Ada pending grading (`status = 'submitted'`)

**HARUS tunggu sampai:**
- Draft di-submit atau di-delete
- Pending grading selesai (status berubah ke `graded`)

### 4. Passing Criteria
**Highest Score digunakan, bukan Latest:**
- Assignment: `highest_score >= (max_score * 0.6)` atau `highest_score >= passing_grade`
- Quiz: `highest_final_score >= passing_grade`

**Query untuk highest score:**
```php
// Assignment
Submission::where('assignment_id', $assignmentId)
    ->where('user_id', $userId)
    ->whereNotNull('score')
    ->orderByDesc('score')
    ->first();

// Quiz
QuizSubmission::where('quiz_id', $quizId)
    ->where('user_id', $userId)
    ->whereNotNull('final_score')
    ->orderByDesc('final_score')
    ->first();
```

---

## Implementation Details

### Service Layer

#### SubmissionCreationProcessor.php
```php
public function startSubmission(int $assignmentId, int $studentId): Submission
{
    // 1. Check prerequisites
    $accessCheck = $this->prerequisiteService->checkAssignmentAccess($assignment, $studentId);
    
    // 2. Validate no pending submission
    $pendingSubmission = Submission::where('assignment_id', $assignmentId)
        ->where('user_id', $studentId)
        ->whereIn('status', [SubmissionStatus::Draft->value, SubmissionStatus::Submitted->value])
        ->first();
    
    if ($pendingSubmission) {
        if ($pendingSubmission->status === SubmissionStatus::Draft->value) {
            throw SubmissionException::notAllowed('You have a draft submission');
        }
        throw SubmissionException::notAllowed('You have a pending submission awaiting grading');
    }
    
    // 3. Calculate attempt number
    $attemptNumber = $this->repository->countAttempts($studentId, $assignmentId) + 1;
    
    // 4. Create new submission
    return $this->repository->create([
        'assignment_id' => $assignmentId,
        'user_id' => $studentId,
        'attempt_number' => $attemptNumber,
        'status' => SubmissionStatus::Draft->value,
        'state' => SubmissionState::InProgress->value,
    ]);
}
```

#### QuizSubmissionService.php
```php
public function start(Quiz $quiz, int $userId): QuizSubmission
{
    // 1. Check prerequisites
    $accessCheck = $this->prerequisiteService->checkQuizAccess($quiz, $userId);
    
    // 2. Validate no pending submission
    $pendingSubmission = QuizSubmission::where('quiz_id', $quiz->id)
        ->where('user_id', $userId)
        ->whereIn('status', [QuizSubmissionStatus::Draft->value, QuizSubmissionStatus::Submitted->value])
        ->first();
    
    if ($pendingSubmission) {
        if ($pendingSubmission->status === QuizSubmissionStatus::Draft->value) {
            throw ValidationException('You have a draft submission');
        }
        throw ValidationException('You have a pending submission awaiting grading');
    }
    
    // 3. Calculate attempt number
    $attemptNumber = $this->repository->getAttemptCount($quiz->id, $userId) + 1;
    
    // 4. Create new submission
    return $this->repository->create([
        'quiz_id' => $quiz->id,
        'user_id' => $userId,
        'attempt_number' => $attemptNumber,
        'status' => QuizSubmissionStatus::Draft->value,
        'started_at' => now(),
    ]);
}
```

### Repository Layer

#### SubmissionRepository.php
```php
public function countAttempts(int $userId, int $assignmentId): int
{
    return Submission::where('user_id', $userId)
        ->where('assignment_id', $assignmentId)
        ->count();
}
```

#### QuizSubmissionRepository.php
```php
public function getAttemptCount(int $quizId, int $userId): int
{
    return QuizSubmission::where('quiz_id', $quizId)
        ->where('user_id', $userId)
        ->count();
}
```

---

## API Response Examples

### List Submissions with Attempt History
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "attempt_number": 1,
      "status": "graded",
      "score": 75,
      "max_score": 100,
      "is_highest": false,
      "submitted_at": "2024-03-01T14:00:00Z",
      "graded_at": "2024-03-01T16:00:00Z"
    },
    {
      "id": 105,
      "attempt_number": 2,
      "status": "graded",
      "score": 85,
      "max_score": 100,
      "is_highest": true,
      "submitted_at": "2024-03-02T14:00:00Z",
      "graded_at": "2024-03-02T16:00:00Z"
    },
    {
      "id": 110,
      "attempt_number": 3,
      "status": "graded",
      "score": 80,
      "max_score": 100,
      "is_highest": false,
      "submitted_at": "2024-03-03T14:00:00Z",
      "graded_at": "2024-03-03T16:00:00Z"
    }
  ]
}
```

### Get Highest Submission
```json
{
  "success": true,
  "data": {
    "id": 105,
    "attempt_number": 2,
    "status": "graded",
    "score": 85,
    "max_score": 100,
    "is_highest": true,
    "submitted_at": "2024-03-02T14:00:00Z",
    "graded_at": "2024-03-02T16:00:00Z"
  }
}
```

---

## Student Flow Examples

### Example 1: First Attempt
```
1. Student starts assignment
   POST /api/v1/assignments/1/submissions
   → Creates submission with attempt_number=1, status=draft

2. Student saves answers
   POST /api/v1/submissions/101/answers
   → Saves draft answers

3. Student submits
   POST /api/v1/submissions/101/submit
   → Changes status to submitted (pending grading)

4. Instructor grades
   POST /api/v1/submissions/101/grade
   → Changes status to graded, score=75
```

### Example 2: Second Attempt (Retry)
```
1. Student wants to retry
   POST /api/v1/assignments/1/submissions
   → Creates NEW submission with attempt_number=2, status=draft
   → Previous submission (attempt_number=1) tetap ada dengan score=75

2. Student submits
   POST /api/v1/submissions/105/submit
   → Changes status to submitted

3. Instructor grades
   POST /api/v1/submissions/105/grade
   → Changes status to graded, score=85
   → This becomes highest score (85 > 75)
```

### Example 3: Third Attempt (Score Lower)
```
1. Student tries again
   POST /api/v1/assignments/1/submissions
   → Creates NEW submission with attempt_number=3

2. Student submits and gets graded
   → score=80
   → Highest score masih 85 (dari attempt 2)
   → All 3 attempts tersimpan di database
```

---

## Error Scenarios

### Error 1: Draft Exists
```
Request: POST /api/v1/assignments/1/submissions
Response: 400 Bad Request
{
  "success": false,
  "message": "Cannot start new submission",
  "errors": {
    "submission": ["You have a draft submission. Please complete or delete it first."]
  }
}
```

### Error 2: Pending Grading
```
Request: POST /api/v1/assignments/1/submissions
Response: 400 Bad Request
{
  "success": false,
  "message": "Cannot start new submission",
  "errors": {
    "submission": ["You have a pending submission awaiting grading."]
  }
}
```

---

## Migration History

### 1. Initial Removal (WRONG)
File: `2026_03_03_100000_remove_retake_columns_from_assessments.php`
- Removed `attempt_number` from submissions ❌
- Removed `attempt_number` from quiz_submissions ❌
- This was WRONG because we need attempt_number for history tracking

### 2. Add Back (CORRECT)
File: `2026_03_03_081558_add_attempt_number_back_to_submissions.php`
- Added `attempt_number` back to submissions ✅
- Added `attempt_number` back to quiz_submissions ✅
- Added composite index for performance ✅

---

## Testing Checklist

### Unit Tests
- [x] SubmissionCreationProcessor calculates attempt_number correctly
- [x] QuizSubmissionService calculates attempt_number correctly
- [x] Validation prevents draft submission overlap
- [x] Validation prevents pending grading overlap

### Integration Tests
- [x] Student can create multiple attempts
- [x] Each attempt has incremental attempt_number
- [x] Highest score is used for passing
- [x] All attempts are preserved in database

### Seeder Tests
- [x] ComprehensiveAssessmentSeeder creates submissions with attempt_number
- [x] Seeder runs without errors

---

## Summary

✅ **READY FOR PRODUCTION**

Sistem unlimited attempts dengan history tracking sudah **COMPLETE** dan **TESTED**:

1. ✅ Database schema updated (attempt_number added back)
2. ✅ Service layer implements attempt tracking
3. ✅ Repository layer counts attempts correctly
4. ✅ Resources include attempt_number in responses
5. ✅ Validation prevents concurrent attempts
6. ✅ Highest score used for passing (not latest)
7. ✅ Complete history preserved
8. ✅ Seeder works correctly
9. ✅ API documentation updated

**Key Features:**
- Unlimited attempts
- Complete history tracking
- Highest score for passing
- Validation prevents conflicts
- Performance optimized with indexes
