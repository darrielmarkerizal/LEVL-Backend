# Quiz API Documentation

## Overview
Quiz adalah assessment berbasis pertanyaan objektif (multiple choice, true/false, etc.) dan essay yang dapat di-grade otomatis atau manual. Berbeda dengan Assignment yang berbasis file upload.

---

## Endpoints

### 1. List Quizzes in Course
```
GET /api/v1/courses/{course_slug}/quizzes
```

**Authorization**: Authenticated users

**Query Parameters**:
- `per_page` (integer, optional): Items per page (default: 15, max: 100)
- `page` (integer, optional): Page number
- `filter[status]` (string, optional): Filter by status (`draft`, `published`, `archived`)
- `sort` (string, optional): Sort field (`order`, `title`, `created_at`)

**Response**:
```json
{
  "success": true,
  "message": "Quizzes retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "JavaScript Fundamentals Quiz",
      "description": "Test your knowledge of JavaScript basics",
      "unit_id": 5,
      "order": 1,
      "passing_grade": 80,
      "auto_grading": true,
      "max_score": 100,
      "time_limit_minutes": 30,
      "randomization_type": "questions",
      "question_bank_count": 10,
      "review_mode": "immediate",
      "status": "published",
      "created_at": "2026-03-06T10:00:00Z",
      "updated_at": "2026-03-06T10:00:00Z"
    }
  ],
  "meta": {
    "pagination": {...}
  }
}
```

---

### 2. Show Quiz Detail
```
GET /api/v1/quizzes/{quiz_id}
```

**Authorization**: User must have `view` permission on quiz

**Response**:
```json
{
  "success": true,
  "message": "Quiz retrieved successfully",
  "data": {
    "id": 1,
    "title": "JavaScript Fundamentals Quiz",
    "description": "Test your knowledge of JavaScript basics including variables, functions, and control structures.",
    "unit_id": 5,
    "order": 1,
    "passing_grade": 80,
    "auto_grading": true,
    "max_score": 100,
    "time_limit_minutes": 30,
    "randomization_type": "questions",
    "question_bank_count": 10,
    "review_mode": "immediate",
    "status": "published",
    "created_by": 3,
    "created_at": "2026-03-06T10:00:00Z",
    "updated_at": "2026-03-06T10:00:00Z",
    "questions_count": 15,
    "unit": {
      "id": 5,
      "title": "JavaScript Basics",
      "slug": "javascript-basics"
    }
  }
}
```

---

### 3. Create Quiz
```
POST /api/v1/quizzes
```

**Authorization**: Admin, Instructor, or Superadmin

**Content-Type**: `application/json` or `multipart/form-data`

**Request Body**:
```json
{
  "title": "JavaScript Fundamentals Quiz",
  "description": "Test your knowledge of JavaScript basics",
  "unit_id": 5,
  "passing_grade": 80,
  "auto_grading": true,
  "max_score": 100,
  "time_limit_minutes": 30,
  "randomization_type": "questions",
  "question_bank_count": 10,
  "review_mode": "immediate",
  "status": "draft",
  "order": 1
}
```

**Response**: Same as Show Quiz Detail

---

### 4. Update Quiz
```
PUT /api/v1/quizzes/{quiz_id}
```

**Authorization**: User must have `update` permission on quiz

**Request Body**: Same as Create Quiz (all fields optional)

**Response**: Same as Show Quiz Detail

---

### 5. Delete Quiz
```
DELETE /api/v1/quizzes/{quiz_id}
```

**Authorization**: User must have `delete` permission on quiz

**Response**:
```json
{
  "success": true,
  "message": "Quiz deleted successfully",
  "data": null
}
```

---

### 6. Publish Quiz
```
PUT /api/v1/quizzes/{quiz_id}/publish
```

**Authorization**: User must have `update` permission on quiz

**Response**: Same as Show Quiz Detail with `status: "published"`

---

### 7. Unpublish Quiz
```
PUT /api/v1/quizzes/{quiz_id}/unpublish
```

**Authorization**: User must have `update` permission on quiz

**Response**: Same as Show Quiz Detail with `status: "draft"`

---

### 8. Archive Quiz
```
PUT /api/v1/quizzes/{quiz_id}/archived
```

**Authorization**: User must have `update` permission on quiz

**Response**: Same as Show Quiz Detail with `status: "archived"`

---

## Question Management

### 9. List Questions
```
GET /api/v1/quizzes/{quiz_id}/questions
```

**Authorization**: User must have `view` permission on quiz

**Response**:
```json
{
  "success": true,
  "message": "Questions retrieved successfully",
  "data": [
    {
      "id": 1,
      "quiz_id": 1,
      "type": "multiple_choice",
      "content": "What is the correct syntax for declaring a variable in JavaScript?",
      "weight": 1.00,
      "max_score": 10.00,
      "order": 1,
      "options": [
        "var x = 5;",
        "variable x = 5;",
        "x := 5;"
      ],
      "answer_key": [0]
    }
  ]
}
```

---

### 10. Show Question Detail
```
GET /api/v1/quizzes/{quiz_id}/questions/{question_id}
```

**Authorization**: Admin, Instructor, or Superadmin with `view` permission

**Response**: Single question object

---

### 11. Add Question
```
POST /api/v1/quizzes/{quiz_id}/questions
```

**Authorization**: User must have `update` permission on quiz

**Request Body (Multiple Choice)**:
```json
{
  "type": "multiple_choice",
  "content": "What is the correct syntax for declaring a variable?",
  "weight": 1.0,
  "max_score": 10,
  "order": 1,
  "options": [
    {"text": "var x = 5;"},
    {"text": "variable x = 5;"},
    {"text": "x := 5;"}
  ],
  "answer_key": [0]
}
```

**Request Body (True/False)**:
```json
{
  "type": "true_false",
  "content": "JavaScript is a compiled language.",
  "weight": 1.0,
  "max_score": 5,
  "order": 2,
  "answer_key": [false]
}
```

**Request Body (Essay)**:
```json
{
  "type": "essay",
  "content": "Explain the difference between var, let, and const in JavaScript.",
  "weight": 2.0,
  "max_score": 20,
  "order": 3
}
```

**Response**: Created question object

---

### 12. Update Question
```
PUT /api/v1/quizzes/{quiz_id}/questions/{question_id}
```

**Authorization**: User must have `update` permission on quiz

**Request Body**: Same as Add Question

**Response**: Updated question object

---

### 13. Delete Question
```
DELETE /api/v1/quizzes/{quiz_id}/questions/{question_id}
```

**Authorization**: User must have `update` permission on quiz

**Response**:
```json
{
  "success": true,
  "message": "Question deleted successfully",
  "data": null
}
```

---

### 14. Reorder Questions
```
POST /api/v1/quizzes/{quiz_id}/questions/reorder
```

**Authorization**: User must have `update` permission on quiz

**Request Body**:
```json
{
  "ids": [3, 1, 2]
}
```

**Description**: Reorders questions by providing an array of question IDs in the desired order. The first ID will have order=1, second will have order=2, etc.

**Response**:
```json
{
  "success": true,
  "message": "Questions reordered successfully",
  "data": null
}
```

---

## Quiz Submission (Taking Quiz)

### 15. Start Quiz
```
POST /api/v1/quizzes/{quiz_id}/submissions/start
```

**Authorization**: Student with `takeQuiz` permission

**Description**: Creates a new quiz submission and starts the timer

**Response**:
```json
{
  "success": true,
  "message": "Quiz started successfully",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "user_id": 10,
    "attempt_number": 1,
    "status": "in_progress",
    "started_at": "2026-03-06T15:00:00Z",
    "expires_at": "2026-03-06T15:30:00Z",
    "time_remaining_seconds": 1800,
    "questions_count": 10
  }
}
```

---

### 16. List Quiz Submissions
```
GET /api/v1/quizzes/{quiz_id}/submissions
```

**Authorization**: Authenticated user

**Query Parameters**:
- `per_page` (integer, optional)
- `filter[status]` (string, optional): `in_progress`, `submitted`, `graded`
- `filter[user_id]` (integer, optional): Filter by user (instructors only)

**Response**: Paginated list of quiz submissions

---

### 17. Get Highest Submission
```
GET /api/v1/quizzes/{quiz_id}/submissions/highest
```

**Authorization**: Authenticated user

**Description**: Returns the submission with the highest score for the authenticated user

**Response**: Single submission object

---

### 18. Show Submission Detail
```
GET /api/v1/quiz-submissions/{submission_id}
```

**Authorization**: User must have `view` permission on submission

**Query Parameters**:
- `include` (string, optional): Comma-separated list of relationships to include (e.g., `quiz,user,answers`)

**Response**:
```json
{
  "success": true,
  "message": "Submission retrieved successfully",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "user_id": 10,
    "attempt_number": 1,
    "status": "graded",
    "score": 85,
    "started_at": "2026-03-06T15:00:00Z",
    "submitted_at": "2026-03-06T15:25:00Z",
    "graded_at": "2026-03-06T15:26:00Z",
    "time_taken_seconds": 1500,
    "quiz": {
      "id": 1,
      "title": "JavaScript Fundamentals Quiz",
      "max_score": 100,
      "passing_grade": 80
    },
    "answers_summary": {
      "total_questions": 10,
      "answered": 10,
      "correct": 8,
      "incorrect": 2
    }
  }
}
```

---

### 19. List Questions in Submission
```
GET /api/v1/quiz-submissions/{submission_id}/questions
```

**Authorization**: User must have `view` permission on submission

**Description**: Returns all questions for this submission (randomized if applicable)

**Response**:
```json
{
  "success": true,
  "message": "Questions retrieved successfully",
  "data": {
    "id": 5,
    "quiz_id": 1,
    "type": "multiple_choice",
    "content": "What is the correct syntax...",
    "weight": 1.0,
    "max_score": 10,
    "order": 1,
    "options": [
      "var x = 5;",
      "variable x = 5;"
    ],
    "user_answer": null
  },
  "meta": {
    "current_question": 1,
    "total_questions": 10,
    "has_next": true,
    "has_previous": false
  }
}
```

---

### 20. Get Question at Order
```
GET /api/v1/quiz-submissions/{submission_id}/questions/{order}
```

**Authorization**: User must have `view` permission on submission

**Description**: Returns a specific question by its order number (1, 2, 3, ...)

**Response**: 
```json
{
  "success": true,
  "message": "Question retrieved successfully",
  "data": {
    "question": {
      "id": 5,
      "type": "multiple_choice",
      "content": "What is the correct syntax...",
      "weight": 1.0,
      "max_score": 10,
      "options": ["var x = 5;", "variable x = 5;"]
    },
    "navigation": {
      "current": 1,
      "total": 10,
      "has_next": true,
      "has_previous": false
    }
  }
}
```

---

### 21. Save Answer
```
POST /api/v1/quiz-submissions/{submission_id}/answers
```

**Authorization**: User must have `update` permission on submission

**Request Body (Multiple Choice)**:
```json
{
  "quiz_question_id": 5,
  "selected_options": ["0"]
}
```

**Request Body (True/False)**:
```json
{
  "quiz_question_id": 6,
  "selected_options": ["true"]
}
```

**Request Body (Essay)**:
```json
{
  "quiz_question_id": 7,
  "content": "The difference between var, let, and const is..."
}
```

**Response**:
```json
{
  "success": true,
  "message": "Answer saved successfully",
  "data": {
    "id": 1,
    "quiz_submission_id": 1,
    "quiz_question_id": 5,
    "content": null,
    "selected_options": ["0"],
    "is_correct": null,
    "score": null,
    "created_at": "2026-03-06T15:10:00Z",
    "updated_at": "2026-03-06T15:10:00Z"
  }
}
```

---

### 22. Submit Quiz
```
POST /api/v1/quiz-submissions/{submission_id}/submit
```

**Authorization**: User must have `update` permission on submission

**Description**: Finalizes the quiz submission and triggers auto-grading (if applicable)

**Response**:
```json
{
  "success": true,
  "message": "Quiz submitted successfully",
  "data": {
    "id": 1,
    "status": "graded",
    "score": 85,
    "submitted_at": "2026-03-06T15:25:00Z",
    "graded_at": "2026-03-06T15:25:00Z",
    "passed": true,
    "answers_summary": {
      "total_questions": 10,
      "answered": 10,
      "correct": 8,
      "incorrect": 2
    }
  }
}
```

---

## Field Descriptions

### Quiz Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title` | string | Yes | Quiz title (max 255 chars) |
| `description` | string | No | Detailed instructions |
| `unit_id` | integer | Yes | ID of the unit |
| `order` | integer | No | Display order (auto-generated if not provided) |
| `passing_grade` | decimal | No | Minimum score to pass (0-100, default: 60) |
| `auto_grading` | boolean | No | Enable auto-grading (default: true) |
| `max_score` | decimal | No | Maximum score (calculated from questions if not provided) |
| `time_limit_minutes` | integer | No | Time limit in minutes |
| `randomization_type` | enum | No | `static`, `random_order`, `bank` |
| `question_bank_count` | integer | No | Number of questions to show (for `bank` type) |
| `review_mode` | enum | No | `immediate`, `after_deadline`, `never` |
| `status` | enum | No | `draft`, `published`, `archived` (default: `draft`) |

### Question Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `type` | enum | Yes | `multiple_choice`, `checkbox`, `true_false`, `essay` |
| `content` | string | Yes | The question text |
| `weight` | decimal | No | Weight for scoring (default: 1.0) |
| `max_score` | decimal | No | Maximum score for this question |
| `order` | integer | No | Display order (auto-generated if not provided) |
| `options` | array | Conditional | Required for `multiple_choice` and `checkbox` |
| `options.*.text` | string | Conditional | Option text |
| `options.*.image` | file | No | Option image (optional) |
| `answer_key` | array | Conditional | Correct answer(s) - indices for MC, boolean for T/F |

---

## Enum Values

### randomization_type
- `static` - Questions always in same order
- `random_order` - Randomize question order
- `bank` - Show random subset from question bank

### review_mode
- `immediate` - Show correct answers immediately after submission
- `after_deadline` - Show after quiz deadline
- `never` - Never show correct answers

### status (Quiz)
- `draft` - Not visible to students
- `published` - Visible to students
- `archived` - Hidden but not deleted

### status (Submission)
- `in_progress` - Student is taking the quiz
- `submitted` - Student has submitted, waiting for grading (essay questions)
- `graded` - All questions graded

### question_type
- `multiple_choice` - Multiple choice with one correct answer
- `checkbox` - Multiple choice with multiple correct answers
- `true_false` - True or false question
- `essay` - Long text answer (manual grading)

---

## Authorization Rules

### View Quiz
- **Published**: All enrolled students + instructors + admins
- **Draft**: Only instructors and admins assigned to the course

### Create/Update/Delete Quiz
- Superadmin: All quizzes
- Admin: Quizzes in courses they're assigned to
- Instructor: Quizzes in their own courses

### Take Quiz
- Students enrolled in the course
- Quiz must be published
- Prerequisites must be met

### Manage Questions
- Same as Create/Update/Delete Quiz

---

## Quiz Flow

1. **Instructor creates quiz** → `POST /quizzes` (status: draft)
2. **Instructor adds questions** → `POST /quizzes/{id}/questions`
3. **Instructor publishes quiz** → `PUT /quizzes/{id}/publish`
4. **Student starts quiz** → `POST /quizzes/{id}/submissions/start`
5. **Student answers questions** → `POST /quiz-submissions/{id}/answers` (multiple times)
6. **Student submits quiz** → `POST /quiz-submissions/{id}/submit`
7. **System auto-grades** (if all objective questions)
8. **Instructor grades essays** (if any essay questions)

---

## Notes

1. **Auto-Grading**: Objective questions (multiple choice, true/false) are graded automatically
2. **Manual Grading**: Essay questions require instructor grading
3. **Unlimited Attempts**: Students can take quiz multiple times
4. **Time Limits**: Quiz expires after time limit (if set)
5. **Randomization**: Questions can be randomized per student
6. **Question Bank**: Show random subset of questions from larger pool
7. **Review Mode**: Control when students can see correct answers
