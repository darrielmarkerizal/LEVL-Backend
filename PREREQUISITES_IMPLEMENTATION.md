# Prerequisites & Security Implementation

## Overview
Implemented strict sequential prerequisites system where students must complete all content in previous units before accessing next units.

## Prerequisites Logic

### Unit Access Rules
- **Unit 1**: Always accessible (no prerequisites)
- **Unit 2+**: Requires 100% completion of previous unit
  - ALL published lessons must be completed
  - ALL published assignments must be passed (score >= passing_grade)
  - ALL published quizzes must be passed (score >= passing_grade)

### Implementation
- `PrerequisiteService.checkUnitAccess()`: Enforces unit-level prerequisites
- `PrerequisiteService.getUnitIncompleteness()`: Returns detailed missing items with `passing_required` flag
- Unit `is_locked` field reflects prerequisite status in API responses

## Security Validations

### Lesson Completion
- **File**: `LessonCompletionService.php`
- **Validation**: Cannot complete lesson if `is_locked: true`
- **Error**: ValidationException with message about prerequisites

### Assignment Submission
- **File**: `SubmissionCreationProcessor.php`
- **Validation**: Cannot create or start submission if assignment `is_locked: true`
- **Methods**: `create()` and `startSubmission()`
- **Error**: ValidationException with message about prerequisites

### Quiz Submission
- **File**: `QuizSubmissionService.php`
- **Validation**: Cannot start quiz if `is_locked: true`
- **Method**: `start()`
- **Error**: ValidationException with message about prerequisites

## Student Question Access

### Quiz Questions
- **Endpoint**: `GET /quiz-submissions/{submission_id}/questions`
- **Pagination**: 1 question per page (students only)
- **Security**: Must start quiz first to access questions
- **Response**: Includes `has_next`, `has_prev` metadata

### Assignment Questions
- **Endpoint**: `GET /submissions/{submission_id}/questions`
- **Pagination**: 1 question per page (students only)
- **Security**: Must start assignment first to access questions
- **Response**: Includes `has_next`, `has_prev` metadata

### Answer Key Protection
- Students never see `answer_key` field in question responses
- Auto-grading handled by backend
- `QuizQuestionResource` conditionally hides answer_key based on user role

## Quiz Flow Security

### Start Quiz
- **Endpoint**: `POST /quizzes/{id}/submissions/start`
- **Validation**: Cannot start if already has draft submission (returns 422 with existing submission_id)
- **Validation**: Cannot start if quiz is locked (returns 422 with prerequisite message)

### Direct Questions Access (Blocked)
- **Endpoint**: `GET /quizzes/{id}/questions`
- **Security**: Students get 403 error - must start quiz first
- **Admin/Instructor**: Can access directly for preview

## Testing Checklist

1. ✅ Unit 1 accessible without prerequisites
2. ✅ Unit 2 locked until Unit 1 is 100% complete
3. ✅ Lesson completion blocked when locked
4. ✅ Assignment submission blocked when locked
5. ✅ Quiz submission blocked when locked
6. ✅ Students cannot start quiz twice
7. ✅ Students cannot access questions without starting
8. ✅ Students get 1 question per page
9. ✅ Students never see answer_key

## Modified Files
- `Modules/Schemes/app/Services/PrerequisiteService.php`
- `Modules/Schemes/app/Services/LessonCompletionService.php`
- `Modules/Learning/app/Services/Support/SubmissionCreationProcessor.php`
- `Modules/Learning/app/Services/QuizSubmissionService.php`
- `Modules/Learning/app/Http/Controllers/SubmissionController.php`
- `Modules/Learning/app/Http/Controllers/QuizSubmissionController.php`
- `Modules/Learning/app/Http/Controllers/QuizController.php`
