# Assignment vs Quiz Implementation

## Overview

The `assignments` table now supports two distinct types via the `type` enum field:
- `assignment`: Traditional file upload tasks
- `quiz`: Question-based assessments

## Differences

### Assignment (type='assignment')
- **Purpose**: File upload tasks
- **Description**: Task instructions in `description` field
- **Questions**: NO questions (questions table not used)
- **Submission**: Students upload files
- **Submission Type**: Must be `file` or `mixed`
- **Grading**: Manual grading by instructor
- **Example**: "Create a report about X", "Upload presentation video"

### Quiz (type='quiz')
- **Purpose**: Question-based assessments
- **Questions**: YES - structured questions (multiple_choice, checkbox, essay, true_false)
- **Submission**: Students answer per question
- **Submission Type**: Not relevant (uses questions)
- **Grading**: Auto-grading for MC/checkbox/true_false, manual for essay
- **Features**: Time limits, attempts, randomization, question bank
- **Example**: Exam with 20 multiple choice questions

## Database Schema

```sql
ALTER TABLE assignments ADD COLUMN type ENUM('assignment', 'quiz') DEFAULT 'assignment' AFTER description;
```

## Model Usage

```php
use Modules\Learning\Enums\AssignmentType;
use Modules\Learning\Models\Assignment;

// Create an assignment
$assignment = Assignment::create([
    'type' => AssignmentType::Assignment,
    'title' => 'Weekly Report',
    'description' => 'Submit your weekly progress report',
    'submission_type' => 'file',
    // ... other fields
]);

// Create a quiz
$quiz = Assignment::create([
    'type' => AssignmentType::Quiz,
    'title' => 'Chapter 1 Quiz',
    'description' => 'Answer all questions',
    'max_attempts' => 3,
    'time_limit_minutes' => 60,
    // ... other fields
]);

// Add questions to quiz
$quiz->questions()->create([
    'type' => 'multiple_choice',
    'content' => 'What is 2+2?',
    'options' => ['2', '3', '4', '5'],
    'answer_key' => [2], // index of correct answer
    'weight' => 10,
]);

// Check type
if ($assignment->isAssignment()) {
    // Handle file upload
}

if ($quiz->isQuiz()) {
    // Handle questions
}

// Query scopes
$assignments = Assignment::assignments()->get(); // Only assignments
$quizzes = Assignment::quizzes()->get(); // Only quizzes
$specific = Assignment::ofType(AssignmentType::Quiz)->get();
```

## Validation Rules

### For Assignment (type='assignment')
- `submission_type` must be `file` or `mixed`
- Should NOT have questions
- File upload is required

### For Quiz (type='quiz')
- Must have at least one question
- `submission_type` is not relevant
- Can have time limits, attempts, randomization

## API Endpoints

All existing assignment endpoints support the `type` field:

```http
POST /api/v1/assignments
{
    "type": "quiz",
    "title": "Chapter 1 Quiz",
    "description": "Answer all questions",
    "assignable_type": "Lesson",
    "assignable_slug": "introduction-to-programming",
    "max_attempts": 3,
    "time_limit_minutes": 60
}
```

Filter by type:
```http
GET /api/v1/courses/{slug}/assignments?filter[type]=quiz
GET /api/v1/courses/{slug}/assignments?filter[type]=assignment
```

## Enum Class

Location: `Modules/Learning/app/Enums/AssignmentType.php`

```php
enum AssignmentType: string
{
    case Assignment = 'assignment';
    case Quiz = 'quiz';
    
    public function isAssignment(): bool;
    public function isQuiz(): bool;
}
```

## Translations

English (`lang/en/enums.php`):
```php
'assignment_type' => [
    'assignment' => 'Assignment',
    'quiz' => 'Quiz',
],
```

Indonesian (`lang/id/enums.php`):
```php
'assignment_type' => [
    'assignment' => 'Tugas',
    'quiz' => 'Kuis',
],
```

## Migration

File: `2026_02_28_000001_add_type_to_assignments_table.php`

Run migration:
```bash
php artisan migrate
```

Rollback:
```bash
php artisan migrate:rollback --step=1
```
