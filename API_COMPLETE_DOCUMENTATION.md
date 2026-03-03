# Complete API Documentation - All Modules

**Base URL:** `/api/v1`

**Authentication:** All endpoints (except public course listing) require Bearer token authentication via `Authorization: Bearer {token}` header.

**Response Format:** All responses follow this structure:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {},
  "meta": {}
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {},
  "meta": {}
}
```

---

## Table of Contents

1. [Student APIs](#student-apis)
   - [Courses](#courses-student)
   - [Units & Lessons](#units--lessons-student)
   - [Assignments & Quizzes](#assignments--quizzes-student)
   - [Submissions](#submissions-student)
   - [Progress Tracking](#progress-tracking)
   - [Enrollments](#enrollments-student)
   - [Gamification](#gamification-student)
   - [Dashboard](#dashboard-student)

2. [Management APIs](#management-apis)
   - [Course Management](#course-management)
   - [Unit Management](#unit-management)
   - [Lesson Management](#lesson-management)
   - [Assignment Management](#assignment-management)
   - [Quiz Management](#quiz-management)
   - [Grading](#grading-management)
   - [Enrollment Management](#enrollment-management)

---

# STUDENT APIs

## Courses (Student)

### List All Courses (Public)

**Endpoint:** `GET /courses`

**Access:** Public (no authentication required)

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number (default: 1)
- `filter[status]` (string, optional): Filter by status
  - Values: `published`, `draft`, `archived`
- `filter[type]` (string, optional): Filter by course type
  - Values: Get from `GET /master-data/course-types` (e.g., `online`, `hybrid`, `in_person`)
- `filter[level_tag]` (string, optional): Filter by level
  - Values: Get from `GET /master-data/level-tags` (e.g., `beginner`, `intermediate`, `advanced`)
- `filter[category_id]` (integer, optional): Filter by category ID
  - Values: Get category IDs from `GET /categories`
- `filter[enrollment_type]` (string, optional): Filter by enrollment type
  - Values: Get from `GET /master-data/enrollment-types` (e.g., `auto_accept`, `approval_required`, `key_based`)
- `search` (string, optional): Search in title, code, description

**Response:**
```json
{
  "success": true,
  "message": "Courses retrieved successfully",
  "data": [
    {
      "id": 1,
      "code": "CS101",
      "slug": "introduction-to-programming",
      "title": "Introduction to Programming",
      "short_desc": "Learn programming basics",
      "type": "online",
      "level_tag": "beginner",
      "enrollment_type": "auto_accept",
      "status": "published",
      "published_at": "2024-01-01T00:00:00+00:00",
      "created_at": "2024-01-01T00:00:00+00:00",
      "updated_at": "2024-01-01T00:00:00+00:00",
      "thumbnail": "https://example.com/thumbnail.jpg",
      "banner": "https://example.com/banner.jpg",
      "category": {
        "id": 1,
        "name": "Computer Science"
      },
      "instructor": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "enrollments_count": 150
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4
  }
}
```

**Example Requests:**
```
GET /courses
GET /courses?filter[status]=published
GET /courses?filter[type]=online&filter[level_tag]=beginner
GET /courses?filter[category_id]=1&per_page=20
GET /courses?search=programming&filter[status]=published
```

---

### Get Course Details (Public)

**Endpoint:** `GET /courses/{slug}`

**Access:** Public

**Path Parameters:**
- `slug` (string, required): Course slug

**Response:** Returns single course with full details including units, tags, admins


---

## Units & Lessons (Student)

**IMPORTANT:** Students can ONLY access units and lessons through the course hierarchy and MUST have an active enrollment in the course.

### List Units in Course

**Endpoint:** `GET /courses/{course_slug}/units`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[status]` (string, optional): Filter by status
  - Values: `draft`, `published`

**Response:** Paginated list of units in the specified course


---

### Get Unit Details in Course

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}`

**Access:** Authenticated users (must have access)

**Response:** Unit details with lessons

---

### Get Unit Contents

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/contents`

**Access:** Authenticated users

**Response:** Returns all content within the unit (lessons, blocks, assignments)



### List Lessons in Unit

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/lessons`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[status]` (string, optional): Filter by status
  - Values: `draft`, `published`

**Response:** Paginated list of lessons in the unit

---

### Get Lesson Details in Course Context

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}`

**Access:** Authenticated users (must have access)

**Response:** Lesson details with completion status and blocks

---

### Mark Lesson Complete

**Endpoint:** `POST /lessons/{lesson_slug}/complete`

**Access:** Authenticated users with active enrollment

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Lesson marked as complete"
}
```

**Note:** This endpoint validates enrollment before marking completion.

---

### Mark Lesson Incomplete

**Endpoint:** `DELETE /lessons/{lesson_slug}/complete`

**Access:** Authenticated users with active enrollment

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Lesson marked as incomplete"
}
```

**Note:** This endpoint validates enrollment before marking incomplete.

---

### List Lesson Blocks

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks`

**Access:** Authenticated users

**Query Parameters:**
- `filter[type]` (string, optional): Filter by block type
  - Values: `text`, `video`, `image`, `code`, `file`, `embed`

**Response:** List of lesson blocks (text, video, image, code, etc.)

---

### Get Lesson Block Details

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}`

**Access:** Authenticated users

**Response:** Single block details with media


---

## Assignments & Quizzes (Student)

### List Course Assignments

**Endpoint:** `GET /courses/{course_slug}/assignments`

**Access:** Authenticated users (must have viewAssignments permission)

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filter[status]` (string, optional): Filter by status
  - Values: `draft`, `published`, `archived`
- `filter[type]` (string, optional): Filter by type
  - Values: Get from `GET /master-data/assignment-status` (e.g., `assignment`, `quiz`)
- `filter[assignable_type]` (string, optional): Filter by scope
  - Values: `Course`, `Unit`, `Lesson`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Week 1 Assignment",
      "description": "Complete the programming exercises",
      "submission_type": "file",
      "max_score": 100,
      "max_attempts": 3,
      "status": "published",
      "is_available": true,
      "questions_count": 5,
      "created_at": "2024-01-01T00:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 10
  }
}
```

---

### List Incomplete Assignments

**Endpoint:** `GET /courses/{course_slug}/assignments/incomplete`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)

**Response:** Paginated list of assignments the user hasn't completed

---

### Get Assignment Details

**Endpoint:** `GET /assignments/{assignment_id}`

**Access:** Authenticated users (must have view permission)

**Path Parameters:**
- `assignment_id` (integer, required): Assignment ID

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Week 1 Assignment",
    "description": "Complete the programming exercises",
    "submission_type": "file",
    "max_score": 100,
    "max_attempts": 3,
    "cooldown_minutes": 60,
    "retake_enabled": true,
    "review_mode": "manual",
    "status": "published",
    "allow_resubmit": true,
    "is_available": true,
    "lesson_slug": "lesson-1",
    "unit_slug": "unit-1",
    "course_slug": "course-1",
    "questions_count": 5,
    "attachments": [
      {
        "id": 1,
        "url": "https://example.com/file.pdf",
        "file_name": "instructions.pdf",
        "mime_type": "application/pdf",
        "size": 102400
      }
    ],
    "prerequisites": [
      {
        "id": 2,
        "title": "Previous Assignment"
      }
    ]
  }
}
```


---

### Check Assignment Prerequisites

**Endpoint:** `GET /assignments/{assignment_id}/prerequisites/check`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "can_access": true,
    "missing_prerequisites": [],
    "message": "All prerequisites met"
  }
}
```

---

### Check Assignment Attempts

**Endpoint:** `GET /assignments/{assignment_id}/attempts/check`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "can_attempt": true,
    "attempts_used": 1,
    "max_attempts": 3,
    "remaining_attempts": 2,
    "cooldown_remaining_minutes": 0,
    "has_override": false
  }
}
```

---

### Get My Submissions for Assignment

**Endpoint:** `GET /assignments/{assignment_id}/submissions/me`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "status": "graded",
      "state": "released",
      "score": 85,
      "attempt_number": 1,
      "is_highest": true,
      "submitted_at": "2024-01-15T10:00:00+00:00",
      "graded_at": "2024-01-16T14:00:00+00:00"
    }
  ]
}
```

---

### Get Highest Scoring Submission

**Endpoint:** `GET /assignments/{assignment_id}/submissions/highest`

**Access:** Authenticated users

**Response:** Returns the submission with the highest score for the authenticated user

---

### Get Submission Details

**Endpoint:** `GET /assignments/{assignment_id}/submissions/{submission_id}`

**Access:** Authenticated users (must own submission or be instructor)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "assignment_id": 1,
    "user_id": 5,
    "status": "graded",
    "state": "released",
    "score": 85,
    "attempt_number": 1,
    "is_late": false,
    "is_resubmission": false,
    "submitted_at": "2024-01-15T10:00:00+00:00",
    "graded_at": "2024-01-16T14:00:00+00:00",
    "answers": [
      {
        "id": 1,
        "question_id": 1,
        "answer": "Student answer text",
        "score": 10,
        "feedback": "Good work"
      }
    ],
    "grade": {
      "id": 1,
      "score": 85,
      "feedback": "Well done overall"
    }
  }
}
```


---

## Submissions (Student)

### Start New Submission

**Endpoint:** `POST /assignments/{assignment_id}/submissions/start`

**Access:** Authenticated users

**Request Body:** None (or empty JSON object)

**Response:**
```json
{
  "success": true,
  "message": "Submission started",
  "data": {
    "id": 1,
    "assignment_id": 1,
    "user_id": 5,
    "status": "in_progress",
    "attempt_number": 1,
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Create Submission (Legacy)

**Endpoint:** `POST /assignments/{assignment_id}/submissions`

**Access:** Authenticated users

**Request Body:**
```json
{
  "answer_text": "Optional text answer"
}
```

**Response:** Returns created submission

---

### Update Submission

**Endpoint:** `PUT /submissions/{submission_id}`

**Access:** Authenticated users (must own submission)

**Request Body:**
```json
{
  "answer_text": "Updated answer text"
}
```

**Response:** Returns updated submission

---

### Get Submission Questions

**Endpoint:** `GET /submissions/{submission_id}/questions`

**Access:** Authenticated users (must have access)

**Query Parameters:**
- `per_page` (integer, optional): Items per page (for pagination)
- `page` (integer, optional): Page number

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "multiple_choice",
      "content": "What is 2 + 2?",
      "options": [
        {"text": "3"},
        {"text": "4"},
        {"text": "5"}
      ],
      "max_score": 10,
      "order": 1
    }
  ]
}
```


---

### Save Answer to Question

**Endpoint:** `POST /submissions/{submission_id}/answers`

**Access:** Authenticated users (must own submission and submission must be in_progress)

**Request Body:**
```json
{
  "question_id": 1,
  "answer": "Answer value (format depends on question type)"
}
```

**Answer Formats by Question Type:**
- **Multiple Choice:** Integer (option index, e.g., `0`, `1`, `2`)
- **Checkbox:** Array of integers (e.g., `[0, 2, 3]`)
- **Essay:** String (text answer)
- **File Upload:** Use multipart/form-data with file in `answer` field

**Response:**
```json
{
  "success": true,
  "message": "Answer saved",
  "data": {
    "id": 1,
    "question_id": 1,
    "answer": "Student answer",
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Submit Answers (Final Submission)

**Endpoint:** `POST /submissions/{submission_id}/submit`

**Access:** Authenticated users (must own submission)

**Request Body:**
```json
{
  "answers": [
    {
      "question_id": 1,
      "answer": "Answer value"
    },
    {
      "question_id": 2,
      "answer": [0, 2]
    }
  ]
}
```

**Note:** `answers` array is optional. If provided, it will save all answers before submitting.

**Response:**
```json
{
  "success": true,
  "message": "Submission submitted successfully",
  "data": {
    "id": 1,
    "assignment_id": 1,
    "status": "submitted",
    "state": "pending_grading",
    "score": null,
    "submitted_at": "2024-01-15T10:30:00+00:00"
  }
}
```

---

## Quizzes (Student)

### List Course Quizzes

**Endpoint:** `GET /courses/{course_slug}/quizzes`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[status]` (string, optional): Filter by status
  - Values: `draft`, `published`, `archived`

**Response (Student):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Week 1 Quiz",
      "passing_grade": 70,
      "max_score": 100,
      "auto_grading": true,
      "is_locked": false,
      "unit_slug": "unit-1",
      "questions_count": 10,
      "scope_type": "lesson",
      "created_at": "2024-01-15T10:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 5
  }
}
```

**Response (Instructor):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Week 1 Quiz",
      "passing_grade": 70,
      "max_score": 100,
      "status": "published",
      "status_label": "Published",
      "auto_grading": true,
      "unit_slug": "unit-1",
      "questions_count": 10,
      "available_from": "2024-01-15T00:00:00+00:00",
      "deadline_at": "2024-01-22T23:59:59+00:00",
      "scope_type": "lesson",
      "created_at": "2024-01-15T10:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 5
  }
}
```


---

### Get Quiz Details

**Endpoint:** `GET /quizzes/{quiz_id}`

**Access:** Authenticated users (must have view permission)

**Important Notes:**
- **Students**: Questions array is NOT included in the response. Students must call `/quizzes/{quiz_id}/submissions/start` first to begin the quiz and get questions.
- **Instructors**: Full quiz details including questions are visible.

**Response (Student):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Week 1 Quiz",
    "description": "Test your knowledge",
    "passing_grade": 70,
    "max_score": 100,
    "max_attempts": 2,
    "time_limit_minutes": 30,
    "retake_enabled": true,
    "auto_grading": true,
    "review_mode": "after_deadline",
    "is_locked": false,
    "lesson_slug": "lesson-1",
    "unit_slug": "unit-1",
    "questions_count": 10,
    "scope_type": "lesson",
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

**Response (Instructor):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Week 1 Quiz",
    "description": "Test your knowledge",
    "passing_grade": 70,
    "auto_grading": true,
    "max_score": 100,
    "max_attempts": 2,
    "cooldown_minutes": 30,
    "time_limit_minutes": 30,
    "retake_enabled": true,
    "randomization_type": "random_order",
    "question_bank_count": 10,
    "review_mode": "after_deadline",
    "status": "published",
    "available_from": "2024-01-15T00:00:00+00:00",
    "deadline_at": "2024-01-22T23:59:59+00:00",
    "tolerance_minutes": 30,
    "late_penalty_percent": 10,
    "scope_type": "lesson",
    "assignable_type": "Lesson",
    "assignable_id": 1,
    "lesson_id": 1,
    "created_by": 2,
    "creator": {
      "id": 2,
      "name": "Instructor Name"
    },
    "questions_count": 10,
    "questions": [
      {
        "id": 1,
        "type": "multiple_choice",
        "content": "What is 2 + 2?",
        "options": ["3", "4", "5"],
        "weight": 1,
        "max_score": 10,
        "order": 1
      }
    ],
    "created_at": "2024-01-15T10:00:00+00:00",
    "updated_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Get Quiz Questions

**Endpoint:** `GET /quizzes/{quiz_id}/questions`

**Access:** Authenticated users (must have view permission)

**Response:** List of questions (content visible based on quiz settings and user progress)

---

### Get My Quiz Submissions

**Endpoint:** `GET /quizzes/{quiz_id}/submissions/me`

**Access:** Authenticated users

**Response:** List of user's submissions for the quiz

---

### Get Highest Quiz Submission

**Endpoint:** `GET /quizzes/{quiz_id}/submissions/highest`

**Access:** Authenticated users

**Response:** Returns the submission with the highest score

---

### Start Quiz Submission

**Endpoint:** `POST /quizzes/{quiz_id}/submissions/start`

**Access:** Authenticated users (must have takeQuiz permission)

**Request Body:** None

**Important Notes:**
- This endpoint MUST be called before students can see quiz questions
- Creates a new quiz submission in `draft` status
- Returns submission details but NOT the questions yet
- Students must then call `/quiz-submissions/{submission_id}/questions` to get the questions

**Response:**
```json
{
  "success": true,
  "message": "Quiz submission started",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "user_id": 5,
    "status": "draft",
    "grading_status": "pending",
    "attempt_number": 1,
    "started_at": "2024-01-15T10:00:00+00:00",
    "time_spent_seconds": 0
  }
}
```

---

### Get Quiz Submission Questions

**Endpoint:** `GET /quiz-submissions/{submission_id}/questions`

**Access:** Authenticated users (must own submission)

**Response:** List of questions for this submission (randomized if applicable)

---

### Get Question at Order

**Endpoint:** `GET /quiz-submissions/{submission_id}/questions/{order}`

**Access:** Authenticated users (must own submission)

**Path Parameters:**
- `order` (integer, required): Question order number (1-based)

**Response:** Single question at the specified order


---

### Save Quiz Answer

**Endpoint:** `POST /quiz-submissions/{submission_id}/answers`

**Access:** Authenticated users (must own submission)

**Important Notes:**
- Answers are saved as DRAFT while the quiz is in progress
- Students can update answers multiple times before submitting
- Only when `/quiz-submissions/{submission_id}/submit` is called will answers be finalized

**Request Body:**
```json
{
  "quiz_question_id": 1,
  "content": "Text answer for essay questions",
  "selected_options": [0, 2]
}
```

**Validation Rules:**
- `quiz_question_id`: required, integer, exists in quiz questions
- `content`: nullable, string (for essay/short answer questions)
- `selected_options`: nullable, array (for multiple choice/checkbox questions)

**Response:**
```json
{
  "success": true,
  "message": "Answer saved",
  "data": {
    "id": 1,
    "quiz_submission_id": 1,
    "quiz_question_id": 1,
    "content": "Text answer",
    "selected_options": [0, 2],
    "score": null,
    "is_auto_graded": false
  }
}
```

---

### Submit Quiz

**Endpoint:** `POST /quiz-submissions/{submission_id}/submit`

**Access:** Authenticated users (must own submission)

**Request Body:** None (or optional answers array)

**Important Notes:**
- This finalizes the quiz submission
- If `auto_grading` is `true` AND there are NO essay questions:
  - Quiz is graded immediately
  - `final_score` is calculated and returned
  - `grading_status` is set to `graded`
  - `status` is set to `graded`
- If `auto_grading` is `false` OR there are essay questions:
  - Quiz is marked as submitted but not graded
  - `final_score` is `null`
  - `grading_status` is set to `waiting_for_grading` or `partially_graded`
  - `status` is set to `submitted`
  - Instructor must manually grade essay questions

**Response (Auto-graded, no essays):**
```json
{
  "success": true,
  "message": "Quiz submitted successfully",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "status": "graded",
    "grading_status": "graded",
    "score": 85,
    "final_score": 85,
    "attempt_number": 1,
    "is_passed": true,
    "submitted_at": "2024-01-15T10:25:00+00:00",
    "time_spent_seconds": 1500
  }
}
```

**Response (Manual grading required):**
```json
{
  "success": true,
  "message": "Quiz submitted successfully",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "status": "submitted",
    "grading_status": "waiting_for_grading",
    "score": null,
    "final_score": null,
    "attempt_number": 1,
    "submitted_at": "2024-01-15T10:25:00+00:00",
    "time_spent_seconds": 1500
  }
}
```

**Response (Partially graded - has essays):**
```json
{
  "success": true,
  "message": "Quiz submitted successfully",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "status": "submitted",
    "grading_status": "partially_graded",
    "score": 60,
    "final_score": null,
    "attempt_number": 1,
    "submitted_at": "2024-01-15T10:25:00+00:00",
    "time_spent_seconds": 1500
  }
}
```

---

### Get Quiz Submission Details

**Endpoint:** `GET /quiz-submissions/{submission_id}`

**Access:** Authenticated users (must own submission)

**Response:** Full submission details with answers and scores

---

## Progress Tracking

### Get Course Progress

**Endpoint:** `GET /courses/{course_slug}/progress`

**Access:** Authenticated users (enrolled in course)

**Query Parameters:**
- `user_id` (integer, optional): View another user's progress (requires instructor/admin role)

**Response:**
```json
{
  "success": true,
  "data": {
    "course_id": 1,
    "user_id": 5,
    "enrollment_id": 10,
    "total_lessons": 20,
    "completed_lessons": 15,
    "completion_percentage": 75,
    "units": [
      {
        "id": 1,
        "title": "Introduction",
        "total_lessons": 5,
        "completed_lessons": 5,
        "completion_percentage": 100
      }
    ],
    "recent_activity": [
      {
        "lesson_id": 15,
        "lesson_title": "Advanced Topics",
        "completed_at": "2024-01-15T10:00:00+00:00"
      }
    ]
  }
}
```

---

### Complete Lesson (Course Context)

**Endpoint:** `POST /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/complete`

**Access:** Authenticated users (enrolled in course)

**Request Body:** None

**Response:** Updated progress data


---

### Uncomplete Lesson (Course Context)

**Endpoint:** `POST /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/uncomplete`

**Access:** Authenticated users (enrolled in course)

**Request Body:** None

**Response:** Updated progress data

---

## Enrollments (Student)

### Get My Enrollments

**Endpoint:** `GET /enrollments`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[status]` (string, optional): Filter by status
  - Values: `pending`, `active`, `completed`, `withdrawn`, `cancelled`, `declined`, `expelled`
- `filter[course_slug]` (string, optional): Filter by course slug
  - Values: Get course slugs from `GET /courses`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "user_id": 5,
      "status": "active",
      "enrolled_at": "2024-01-01T00:00:00+00:00",
      "completed_at": null,
      "course": {
        "id": 1,
        "title": "Introduction to Programming",
        "slug": "introduction-to-programming"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 5
  }
}
```

---

### Get Enrollment Details

**Endpoint:** `GET /enrollments/{enrollment_id}`

**Access:** Authenticated users (must own enrollment or be instructor)

**Query Parameters:**
- `include` (string, optional): Comma-separated relations (`user`, `course`)

**Response:** Single enrollment with full details

---

### Check Enrollment Status

**Endpoint:** `GET /courses/{course_slug}/enrollment-status`

**Access:** Authenticated students

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "active",
    "enrollment": {
      "id": 1,
      "status": "active",
      "enrolled_at": "2024-01-01T00:00:00+00:00"
    }
  }
}
```

**If not enrolled:**
```json
{
  "success": true,
  "data": {
    "status": "not_enrolled",
    "enrollment": null
  }
}
```


---

### Enroll in Course

**Endpoint:** `POST /courses/{course_slug}/enroll`

**Access:** Authenticated students only

**Rate Limit:** 5 requests per minute (enrollment throttle)

**Request Body:**
```json
{
  "enrollment_key": "optional-key-for-key-based-courses"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Enrollment successful",
  "data": {
    "id": 1,
    "course_id": 1,
    "user_id": 5,
    "status": "active",
    "enrolled_at": "2024-01-15T10:00:00+00:00"
  }
}
```

**Note:** Response status depends on course enrollment_type:
- `auto_accept`: Status will be `active`
- `approval_required`: Status will be `pending`
- `key_based`: Requires valid `enrollment_key` in request

---

### Cancel Enrollment

**Endpoint:** `POST /courses/{course_slug}/cancel`

**Access:** Authenticated users (must own enrollment)

**Rate Limit:** 5 requests per minute

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Enrollment cancelled",
  "data": {
    "id": 1,
    "status": "cancelled"
  }
}
```

**Note:** Can only cancel enrollments in `pending` status

---

### Withdraw from Course

**Endpoint:** `POST /courses/{course_slug}/withdraw`

**Access:** Authenticated users (must own enrollment)

**Rate Limit:** 5 requests per minute

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Withdrawn from course",
  "data": {
    "id": 1,
    "status": "withdrawn",
    "withdrawn_at": "2024-01-15T10:00:00+00:00"
  }
}
```

**Note:** Can only withdraw from `active` enrollments

---

## Gamification (Student)

### Get Gamification Summary

**Endpoint:** `GET /user/gamification-summary`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "total_xp": 1250,
    "global_level": 5,
    "current_level_xp": 250,
    "xp_to_next_level": 250,
    "progress_to_next_level": 50,
    "badges_count": 8,
    "challenges_completed": 12,
    "rank": 15
  }
}
```


---

### Get My Level

**Endpoint:** `GET /user/level`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "level": 5,
    "total_xp": 1250,
    "current_level_xp": 250,
    "xp_to_next_level": 250,
    "progress": 50
  }
}
```

---

### Get Unit Levels

**Endpoint:** `GET /user/levels/{course_slug}`

**Access:** Authenticated users

**Path Parameters:**
- `course_slug` (string, required): Course slug

**Response:**
```json
{
  "success": true,
  "data": {
    "course_id": 1,
    "units": [
      {
        "unit_id": 1,
        "unit_title": "Introduction",
        "level": 3,
        "xp": 450,
        "progress": 75
      }
    ]
  }
}
```

---

### Get My Badges

**Endpoint:** `GET /user/badges`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "badge_id": 1,
      "badge_name": "First Steps",
      "badge_description": "Complete your first lesson",
      "icon_url": "https://example.com/badge.png",
      "earned_at": "2024-01-10T10:00:00+00:00"
    }
  ]
}
```

---

### Get Points History

**Endpoint:** `GET /user/points-history`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "points": 50,
      "reason": "Completed lesson",
      "source_type": "Lesson",
      "source_id": 5,
      "created_at": "2024-01-15T10:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50
  }
}
```

---

### Get My Milestones

**Endpoint:** `GET /user/milestones`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "achievements": [
      {
        "title": "10 Lessons Completed",
        "description": "You've completed 10 lessons",
        "achieved": true,
        "achieved_at": "2024-01-12T10:00:00+00:00"
      }
    ]
  }
}
```


---

### List Challenges

**Endpoint:** `GET /challenges`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[type]` (string, optional): Filter by type
  - Values: `daily`, `weekly`, `monthly`, `special`
- `filter[status]` (string, optional): Filter by status
  - Values: `active`, `upcoming`, `expired`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Complete 5 Lessons",
      "description": "Complete 5 lessons this week",
      "type": "weekly",
      "points_reward": 100,
      "criteria_type": "lesson_completion",
      "criteria_target": 5,
      "start_at": "2024-01-15T00:00:00+00:00",
      "end_at": "2024-01-22T00:00:00+00:00",
      "badge": {
        "id": 1,
        "name": "Week Warrior",
        "icon_url": "https://example.com/badge.png"
      },
      "user_progress": {
        "current": 3,
        "target": 5,
        "percentage": 60,
        "status": "in_progress",
        "expires_at": "2024-01-22T00:00:00+00:00"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 10
  }
}
```

---

### Get Challenge Details

**Endpoint:** `GET /challenges/{challenge_id}`

**Access:** Authenticated users

**Response:** Single challenge with user progress

---

### Get My Challenges

**Endpoint:** `GET /user/challenges`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)

**Response:** Paginated list of challenges assigned to the user

---

### Get Completed Challenges

**Endpoint:** `GET /user/challenges/completed`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "challenge_id": 1,
      "challenge_name": "Complete 5 Lessons",
      "completed_at": "2024-01-18T10:00:00+00:00",
      "claimed_at": "2024-01-18T10:05:00+00:00",
      "points_earned": 100
    }
  ]
}
```

---

### Claim Challenge Reward

**Endpoint:** `POST /challenges/{challenge_id}/claim`

**Access:** Authenticated users

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Reward claimed successfully",
  "data": {
    "message": "Reward claimed successfully",
    "rewards": {
      "points": 100,
      "badge": {
        "id": 1,
        "name": "Week Warrior"
      }
    }
  }
}
```

**Error Response (if not eligible):**
```json
{
  "success": false,
  "message": "Challenge not completed or already claimed"
}
```


---

### Get Leaderboard

**Endpoint:** `GET /leaderboards`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15, max: 100)
- `page` (integer, optional): Page number (default: 1)
- `filter[course_slug]` (string, optional): Filter by course slug (global leaderboard if omitted)
- `filter[period]` (string, optional): Time period filter
  - Values: `today`, `this_week`, `this_month`, `this_year`, `all_time` (default)

**Note:** Leaderboard is ALWAYS sorted by total XP (descending). No custom sorting allowed.

**Example Requests:**
```
GET /leaderboards
GET /leaderboards?filter[period]=today
GET /leaderboards?filter[period]=this_week&per_page=20
GET /leaderboards?filter[course_slug]=laravel-basics
GET /leaderboards?filter[course_slug]=laravel-basics&filter[period]=this_month
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "rank": 1,
      "user": {
        "id": 5,
        "name": "John Doe",
        "avatar_url": "https://example.com/avatar.jpg"
      },
      "total_xp": 2500,
      "level": 8,
      "badges_count": 12
    },
    {
      "rank": 2,
      "user": {
        "id": 10,
        "name": "Jane Smith",
        "avatar_url": "https://example.com/avatar2.jpg"
      },
      "total_xp": 2300,
      "level": 7,
      "badges_count": 10
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 150,
    "my_rank": {
      "rank": 15,
      "user": {
        "id": 25,
        "name": "Current User",
        "avatar_url": "https://example.com/my-avatar.jpg"
      },
      "total_xp": 1250,
      "level": 5,
      "badges_count": 8
    }
  }
}
```

---

### Get My Rank

**Endpoint:** `GET /gamification/rank`

**Access:** Authenticated users

**Query Parameters:**
- `filter[period]` (string, optional): Time period filter
  - Values: `today`, `this_week`, `this_month`, `this_year`, `all_time` (default)

**Example Requests:**
```
GET /gamification/rank
GET /gamification/rank?filter[period]=this_week
```

**Response:**
```json
{
  "success": true,
  "data": {
    "rank": 15,
    "total_xp": 1250,
    "level": 5,
    "badges_count": 8,
    "surrounding": [
      {
        "rank": 14,
        "user": {
          "id": 20,
          "name": "User Above"
        },
        "total_xp": 1280
      },
      {
        "rank": 16,
        "user": {
          "id": 30,
          "name": "User Below"
        },
        "total_xp": 1220
      }
    ]
  }
}
```

---

## Dashboard (Student)

### Get Student Dashboard

**Endpoint:** `GET /dashboard`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "enrollments": {
      "active": 3,
      "completed": 5,
      "total": 8
    },
    "progress": {
      "total_lessons_completed": 45,
      "total_assignments_completed": 12,
      "average_score": 85.5
    },
    "recent_courses": [
      {
        "id": 1,
        "title": "Introduction to Programming",
        "slug": "introduction-to-programming",
        "progress_percentage": 75,
        "last_accessed": "2024-01-15T10:00:00+00:00"
      }
    ],
    "upcoming_deadlines": [
      {
        "assignment_id": 5,
        "assignment_title": "Week 3 Assignment",
        "course_title": "Introduction to Programming",
        "deadline_at": "2024-01-20T23:59:59+00:00"
      }
    ],
    "gamification": {
      "total_xp": 1250,
      "level": 5,
      "rank": 15,
      "badges_count": 8
    }
  }
}
```

---

## Search

### Global Search

**Endpoint:** `GET /search`

**Access:** Public (no authentication required)

**Query Parameters:**
- `q` (string, required): Search query
- `type` (string, optional): Filter by type (`courses`, `lessons`, `units`, `announcements`, `news`)
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number

**Example Requests:**
```
GET /search?q=programming
GET /search?q=laravel&type=courses
GET /search?q=introduction&type=lessons&per_page=20
```

**Response:**
```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": 1,
        "title": "Introduction to Programming",
        "slug": "introduction-to-programming",
        "type": "course",
        "excerpt": "Learn programming basics..."
      }
    ],
    "lessons": [
      {
        "id": 5,
        "title": "Variables and Data Types",
        "slug": "variables-data-types",
        "type": "lesson",
        "excerpt": "Understanding variables..."
      }
    ],
    "total_results": 15
  },
  "meta": {
    "query": "programming",
    "current_page": 1,
    "per_page": 15
  }
}
```

---

### Search Autocomplete

**Endpoint:** `GET /search/autocomplete`

**Access:** Public

**Query Parameters:**
- `q` (string, required): Search query (minimum 2 characters)
- `limit` (integer, optional): Max results (default: 10, max: 20)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "title": "Introduction to Programming",
      "type": "course",
      "slug": "introduction-to-programming"
    },
    {
      "title": "Programming Basics",
      "type": "lesson",
      "slug": "programming-basics"
    }
  ]
}
```

---

### Get Search History

**Endpoint:** `GET /search/history`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "query": "programming",
      "results_count": 15,
      "searched_at": "2024-01-15T10:00:00+00:00"
    }
  ]
}
```

---

### Clear Search History

**Endpoint:** `DELETE /search/history`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "message": "Search history cleared"
}
```

---

## Notifications

### Get My Notifications

**Endpoint:** `GET /notifications`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[read]` (boolean, optional): Filter by read status (`true`, `false`)
- `filter[type]` (string, optional): Filter by notification type

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "type": "assignment_graded",
      "title": "Assignment Graded",
      "message": "Your Week 1 Assignment has been graded",
      "data": {
        "assignment_id": 1,
        "score": 85
      },
      "read_at": null,
      "created_at": "2024-01-15T10:00:00+00:00"
    }
  ],
  "meta": {
    "unread_count": 5
  }
}
```

---

### Mark Notification as Read

**Endpoint:** `POST /notifications/{notification_id}/read`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

---

### Mark All Notifications as Read

**Endpoint:** `POST /notifications/read-all`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

---

### Get Notification Preferences

**Endpoint:** `GET /notification-preferences`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "email_notifications": true,
    "push_notifications": false,
    "assignment_graded": true,
    "course_announcement": true,
    "enrollment_approved": true,
    "challenge_completed": false
  }
}
```

---

### Update Notification Preferences

**Endpoint:** `PUT /notification-preferences`

**Access:** Authenticated users

**Request Body:**
```json
{
  "email_notifications": true,
  "push_notifications": true,
  "assignment_graded": true,
  "course_announcement": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Notification preferences updated",
  "data": {
    "email_notifications": true,
    "push_notifications": true,
    "assignment_graded": true,
    "course_announcement": false
  }
}
```

---

### Reset Notification Preferences

**Endpoint:** `POST /notification-preferences/reset`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "message": "Notification preferences reset to defaults"
}
```

---

## Announcements

### List Announcements

**Endpoint:** `GET /announcements`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filter[course_slug]` (string, optional): Filter by course slug
  - Values: Get course slugs from `GET /courses`
- `filter[status]` (string, optional): Filter by status
  - Values: `draft`, `published`, `archived`
- `filter[priority]` (string, optional): Filter by priority
  - Values: `low`, `normal`, `high`, `urgent`
- `search` (string, optional): Search in title and content
- `include` (string, optional): Include relations
  - Values: `author`, `course`
- `sort` (string, optional): Sort field

**Available Filters:**
- `filter[course_slug]`: string - Get course slugs from `GET /courses`
- `filter[status]`: `draft`, `published`, `archived`
- `filter[priority]`: `low`, `normal`, `high`, `urgent`
- `search`: Full-text search

**Available Sorts:**
- `sort=published_at`, `sort=-published_at` (default: `-published_at`)
- `sort=created_at`, `sort=-created_at`
- `sort=priority`, `sort=-priority`

**Available Includes:**
- `include=author` - Announcement author details
- `include=course` - Course details

**Example Requests:**
```
GET /announcements
GET /announcements?filter[status]=published
GET /announcements?filter[course_id]=1&filter[priority]=high
GET /announcements?search=exam&include=author,course
GET /announcements?sort=-priority&per_page=20
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Midterm Exam Schedule",
      "content": "The midterm exam will be held on...",
      "status": "published",
      "priority": "high",
      "published_at": "2024-01-15T10:00:00+00:00",
      "author": {
        "id": 2,
        "name": "Instructor Name"
      },
      "course": {
        "id": 1,
        "title": "Introduction to Programming"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 10
  }
}
```

---

### Get Announcement Details

**Endpoint:** `GET /announcements/{announcement_id}`

**Access:** Authenticated users

**Query Parameters:**
- `include` (string, optional): Include relations (`author`, `course`)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Midterm Exam Schedule",
    "content": "The midterm exam will be held on...",
    "status": "published",
    "priority": "high",
    "published_at": "2024-01-15T10:00:00+00:00",
    "is_read": false,
    "author": {
      "id": 2,
      "name": "Instructor Name",
      "email": "instructor@example.com"
    },
    "course": {
      "id": 1,
      "title": "Introduction to Programming",
      "slug": "introduction-to-programming"
    }
  }
}
```

---

### Mark Announcement as Read

**Endpoint:** `POST /announcements/{announcement_id}/read`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "message": "Announcement marked as read"
}
```

---

### Create Announcement (Management)

**Endpoint:** `POST /announcements`

**Access:** Superadmin, Admin, Instructor

**Request Body:**
```json
{
  "title": "Important Update",
  "content": "Please note the following changes...",
  "course_slug": "introduction-to-programming",
  "status": "draft",
  "priority": "normal",
  "published_at": "2024-01-20T10:00:00+00:00"
}
```

**Validation Rules:**
- `title`: required, string, max:255
- `content`: required, string
- `course_slug`: nullable, string - Get course slugs from `GET /courses`
- `status`: nullable, in:draft,published,archived (values: `draft`, `published`, `archived`)
- `priority`: nullable, in:low,normal,high,urgent (values: `low`, `normal`, `high`, `urgent`)
- `published_at`: nullable, date

**Response:**
```json
{
  "success": true,
  "message": "Announcement created successfully",
  "data": {
    "id": 1,
    "title": "Important Update",
    "status": "draft",
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Update Announcement (Management)

**Endpoint:** `PUT /announcements/{announcement_id}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** Same as create (all fields optional)

**Response:** Updated announcement details

---

### Delete Announcement (Management)

**Endpoint:** `DELETE /announcements/{announcement_id}`

**Access:** Superadmin, Admin, Instructor (must have delete permission)

**Response:**
```json
{
  "success": true,
  "message": "Announcement deleted successfully"
}
```

---

## News

### List News Articles

**Endpoint:** `GET /news`

**Access:** Public

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[status]` (string, optional): Filter by status (`draft`, `published`)
- `filter[category]` (string, optional): Filter by category
- `search` (string, optional): Search in title and content
- `include` (string, optional): Include relations (`author`)
- `sort` (string, optional): Sort field

**Available Filters:**
- `filter[status]`: `draft`, `published`
- `filter[category]`: string (category name)
- `search`: Full-text search

**Available Sorts:**
- `sort=published_at`, `sort=-published_at` (default: `-published_at`)
- `sort=views_count`, `sort=-views_count`
- `sort=created_at`, `sort=-created_at`

**Available Includes:**
- `include=author` - Article author details

**Example Requests:**
```
GET /news
GET /news?filter[status]=published&sort=-views_count
GET /news?filter[category]=technology&include=author
GET /news?search=laravel&per_page=20
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "New Features in Laravel 11",
      "slug": "new-features-laravel-11",
      "excerpt": "Discover the latest features...",
      "category": "technology",
      "status": "published",
      "views_count": 1250,
      "published_at": "2024-01-15T10:00:00+00:00",
      "author": {
        "id": 2,
        "name": "Author Name"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50
  }
}
```

---

### Get News Article Details

**Endpoint:** `GET /news/{slug}`

**Access:** Public

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "New Features in Laravel 11",
    "slug": "new-features-laravel-11",
    "content": "Full article content...",
    "category": "technology",
    "status": "published",
    "views_count": 1251,
    "published_at": "2024-01-15T10:00:00+00:00",
    "author": {
      "id": 2,
      "name": "Author Name",
      "email": "author@example.com"
    }
  }
}
```

---

## Tags & Categories

### List Tags

**Endpoint:** `GET /tags`

**Access:** Public

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[name]` (string, optional): Partial match on name
- `filter[slug]` (string, optional): Partial match on slug
- `filter[description]` (string, optional): Partial match on description
- `sort` (string, optional): Sort field

**Available Filters:**
- `filter[name]`: string (partial match)
- `filter[slug]`: string (partial match)
- `filter[description]`: string (partial match)

**Available Sorts:**
- `sort=name`, `sort=-name` (default: `name`)
- `sort=slug`, `sort=-slug`
- `sort=created_at`, `sort=-created_at`
- `sort=updated_at`, `sort=-updated_at`

**Example Requests:**
```
GET /tags
GET /tags?filter[name]=programming
GET /tags?sort=name&per_page=20
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Programming",
      "slug": "programming",
      "description": "Programming related content",
      "created_at": "2024-01-01T00:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 25
  }
}
```

---

### Get Tag Details

**Endpoint:** `GET /tags/{slug}`

**Access:** Public

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Programming",
    "slug": "programming",
    "description": "Programming related content",
    "courses_count": 15,
    "created_at": "2024-01-01T00:00:00+00:00"
  }
}
```

---

### Create Tag (Management)

**Endpoint:** `POST /tags`

**Access:** Superadmin, Admin, Instructor

**Request Body:**
```json
{
  "name": "Web Development",
  "description": "Web development topics"
}
```

**Validation Rules:**
- `name`: required, string, max:255, unique
- `description`: nullable, string

**Response:**
```json
{
  "success": true,
  "message": "Tag created successfully",
  "data": {
    "id": 2,
    "name": "Web Development",
    "slug": "web-development",
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Update Tag (Management)

**Endpoint:** `PUT /tags/{slug}`

**Access:** Superadmin, Admin, Instructor

**Request Body:** Same as create (all fields optional)

**Response:** Updated tag details

---

### Delete Tag (Management)

**Endpoint:** `DELETE /tags/{slug}`

**Access:** Superadmin, Admin, Instructor

**Response:**
```json
{
  "success": true,
  "message": "Tag deleted successfully"
}
```

---

### List Categories

**Endpoint:** `GET /categories`

**Access:** Public

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[name]` (string, optional): Partial match on name
- `search` (string, optional): Full-text search
- `sort` (string, optional): Sort field

**Available Filters:**
- `filter[name]`: string (partial match)
- `search`: Full-text search in name and description

**Available Sorts:**
- `sort=name`, `sort=-name` (default: `name`)
- `sort=created_at`, `sort=-created_at`

**Example Requests:**
```
GET /categories
GET /categories?filter[name]=computer
GET /categories?search=science&sort=name
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Computer Science",
      "slug": "computer-science",
      "description": "Computer science courses",
      "courses_count": 25,
      "created_at": "2024-01-01T00:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 10
  }
}
```

---

### Get Category Details

**Endpoint:** `GET /categories/{category_id}`

**Access:** Public

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Computer Science",
    "slug": "computer-science",
    "description": "Computer science courses",
    "courses_count": 25,
    "created_at": "2024-01-01T00:00:00+00:00"
  }
}
```

---

### Create Category (Management)

**Endpoint:** `POST /categories`

**Access:** Superadmin only

**Request Body:**
```json
{
  "name": "Data Science",
  "description": "Data science and analytics courses"
}
```

**Validation Rules:**
- `name`: required, string, max:255, unique
- `description`: nullable, string

**Response:**
```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "id": 2,
    "name": "Data Science",
    "slug": "data-science",
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Update Category (Management)

**Endpoint:** `PUT /categories/{category_id}`

**Access:** Superadmin only

**Request Body:** Same as create (all fields optional)

**Response:** Updated category details

---

### Delete Category (Management)

**Endpoint:** `DELETE /categories/{category_id}`

**Access:** Superadmin only

**Response:**
```json
{
  "success": true,
  "message": "Category deleted successfully"
}
```

---

## Badges & Gamification Management

### List Badges

**Endpoint:** `GET /badges`

**Access:** Public

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[id]` (integer, optional): Exact match on ID
- `filter[code]` (string, optional): Partial match on code
- `search` (string, optional): Full-text search
- `sort` (string, optional): Sort field

**Available Filters:**
- `filter[id]`: integer (exact match)
- `filter[code]`: string (partial match)
- `search`: Full-text search in name, code, description

**Available Sorts:**
- `sort=id`, `sort=-id`
- `sort=code`, `sort=-code`
- `sort=name`, `sort=-name`
- `sort=type`, `sort=-type`
- `sort=threshold`, `sort=-threshold`
- `sort=created_at`, `sort=-created_at` (default: `-created_at`)
- `sort=updated_at`, `sort=-updated_at`

**Example Requests:**
```
GET /badges
GET /badges?filter[code]=first
GET /badges?search=completion&sort=name
GET /badges?sort=-threshold&per_page=20
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "FIRST_LESSON",
      "name": "First Steps",
      "description": "Complete your first lesson",
      "type": "achievement",
      "threshold": 1,
      "icon_url": "https://example.com/badge.png",
      "created_at": "2024-01-01T00:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50
  }
}
```

---

### Get Badge Details

**Endpoint:** `GET /badges/{badge_id}`

**Access:** Public

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "FIRST_LESSON",
    "name": "First Steps",
    "description": "Complete your first lesson",
    "type": "achievement",
    "threshold": 1,
    "icon_url": "https://example.com/badge.png",
    "earned_by_count": 1250,
    "created_at": "2024-01-01T00:00:00+00:00"
  }
}
```

---

### Create Badge (Management)

**Endpoint:** `POST /badges`

**Access:** Superadmin only

**Request Body (multipart/form-data):**
```
code: FIRST_LESSON (required, string, unique)
name: First Steps (required, string)
description: Complete your first lesson (nullable, string)
type: achievement (required, string)
threshold: 1 (nullable, integer)
icon: (file, optional, image)
```

**Validation Rules:**
- `code`: required, string, max:50, unique
- `name`: required, string, max:255
- `description`: nullable, string
- `type`: required, string
- `threshold`: nullable, integer, min:0
- `icon`: nullable, file, image, max:2MB

**Response:**
```json
{
  "success": true,
  "message": "Badge created successfully",
  "data": {
    "id": 1,
    "code": "FIRST_LESSON",
    "name": "First Steps",
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Update Badge (Management)

**Endpoint:** `PUT /badges/{badge_id}`

**Access:** Superadmin only

**Request Body:** Same as create (all fields optional)

**Response:** Updated badge details

---

### Delete Badge (Management)

**Endpoint:** `DELETE /badges/{badge_id}`

**Access:** Superadmin only

**Response:**
```json
{
  "success": true,
  "message": "Badge deleted successfully"
}
```

---

### List Level Configs

**Endpoint:** `GET /level-configs`

**Access:** Public

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[id]` (integer, optional): Exact match on ID
- `filter[level]` (integer, optional): Exact match on level
- `search` (string, optional): Full-text search
- `sort` (string, optional): Sort field

**Available Filters:**
- `filter[id]`: integer (exact match)
- `filter[level]`: integer (exact match)
- `search`: Full-text search in name

**Available Sorts:**
- `sort=id`, `sort=-id`
- `sort=level`, `sort=-level` (default: `level`)
- `sort=name`, `sort=-name`
- `sort=xp_required`, `sort=-xp_required`
- `sort=created_at`, `sort=-created_at`
- `sort=updated_at`, `sort=-updated_at`

**Example Requests:**
```
GET /level-configs
GET /level-configs?filter[level]=5
GET /level-configs?sort=xp_required
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "level": 1,
      "name": "Beginner",
      "xp_required": 0,
      "created_at": "2024-01-01T00:00:00+00:00"
    },
    {
      "id": 2,
      "level": 2,
      "name": "Novice",
      "xp_required": 100,
      "created_at": "2024-01-01T00:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 20
  }
}
```

---

### Get Level Config Details

**Endpoint:** `GET /level-configs/{level_config_id}`

**Access:** Public

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "level": 5,
    "name": "Intermediate",
    "xp_required": 500,
    "created_at": "2024-01-01T00:00:00+00:00"
  }
}
```

---

### Create Level Config (Management)

**Endpoint:** `POST /level-configs`

**Access:** Superadmin only

**Request Body:**
```json
{
  "level": 10,
  "name": "Expert",
  "xp_required": 5000
}
```

**Validation Rules:**
- `level`: required, integer, min:1, unique
- `name`: required, string, max:255
- `xp_required`: required, integer, min:0

**Response:**
```json
{
  "success": true,
  "message": "Level config created successfully",
  "data": {
    "id": 10,
    "level": 10,
    "name": "Expert",
    "xp_required": 5000,
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Update Level Config (Management)

**Endpoint:** `PUT /level-configs/{level_config_id}`

**Access:** Superadmin only

**Request Body:** Same as create (all fields optional)

**Response:** Updated level config details

---

### Delete Level Config (Management)

**Endpoint:** `DELETE /level-configs/{level_config_id}`

**Access:** Superadmin only

**Response:**
```json
{
  "success": true,
  "message": "Level config deleted successfully"
}
```

---

### List Challenges (Management)

**Endpoint:** `GET /management/challenges`

**Access:** Superadmin only

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[id]` (integer, optional): Exact match on ID
- `filter[title]` (string, optional): Partial match on title
- `search` (string, optional): Full-text search
- `sort` (string, optional): Sort field

**Available Filters:**
- `filter[id]`: integer (exact match)
- `filter[title]`: string (partial match)
- `search`: Full-text search in title and description

**Available Sorts:**
- `sort=id`, `sort=-id`
- `sort=title`, `sort=-title`
- `sort=type`, `sort=-type`
- `sort=points_reward`, `sort=-points_reward`
- `sort=start_at`, `sort=-start_at`
- `sort=end_at`, `sort=-end_at`
- `sort=created_at`, `sort=-created_at` (default: `-created_at`)
- `sort=updated_at`, `sort=-updated_at`

**Example Requests:**
```
GET /management/challenges
GET /management/challenges?filter[title]=weekly
GET /management/challenges?sort=-points_reward
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Complete 5 Lessons",
      "description": "Complete 5 lessons this week",
      "type": "weekly",
      "points_reward": 100,
      "start_at": "2024-01-15T00:00:00+00:00",
      "end_at": "2024-01-22T00:00:00+00:00",
      "created_at": "2024-01-10T00:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 30
  }
}
```

---

### Get Challenge Details (Management)

**Endpoint:** `GET /management/challenges/{challenge_id}`

**Access:** Superadmin only

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Complete 5 Lessons",
    "description": "Complete 5 lessons this week",
    "type": "weekly",
    "points_reward": 100,
    "criteria_type": "lesson_completion",
    "criteria_target": 5,
    "start_at": "2024-01-15T00:00:00+00:00",
    "end_at": "2024-01-22T00:00:00+00:00",
    "badge_id": 1,
    "created_at": "2024-01-10T00:00:00+00:00"
  }
}
```

---

### Create Challenge (Management)

**Endpoint:** `POST /management/challenges`

**Access:** Superadmin only

**Request Body:**
```json
{
  "title": "Complete 10 Lessons",
  "description": "Complete 10 lessons this month",
  "type": "monthly",
  "points_reward": 200,
  "criteria_type": "lesson_completion",
  "criteria_target": 10,
  "start_at": "2024-02-01T00:00:00+00:00",
  "end_at": "2024-02-29T23:59:59+00:00",
  "badge_id": 2
}
```

**Validation Rules:**
- `title`: required, string, max:255
- `description`: nullable, string
- `type`: required, in:daily,weekly,monthly,special
- `points_reward`: required, integer, min:0
- `criteria_type`: required, string
- `criteria_target`: required, integer, min:1
- `start_at`: required, date
- `end_at`: required, date, after:start_at
- `badge_id`: nullable, integer, exists:badges,id

**Response:**
```json
{
  "success": true,
  "message": "Challenge created successfully",
  "data": {
    "id": 2,
    "title": "Complete 10 Lessons",
    "type": "monthly",
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Update Challenge (Management)

**Endpoint:** `PUT /management/challenges/{challenge_id}`

**Access:** Superadmin only

**Request Body:** Same as create (all fields optional)

**Response:** Updated challenge details

---

### Delete Challenge (Management)

**Endpoint:** `DELETE /management/challenges/{challenge_id}`

**Access:** Superadmin only

**Response:**
```json
{
  "success": true,
  "message": "Challenge deleted successfully"
}
```

---

## Activity & Audit Logs

### List Activity Logs

**Endpoint:** `GET /activity-logs`

**Access:** Superadmin, Admin

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "log_name": "default",
      "description": "created",
      "subject_type": "Course",
      "subject_id": 1,
      "causer_type": "User",
      "causer_id": 2,
      "properties": {},
      "created_at": "2024-01-15T10:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 500
  }
}
```

---

### Get Activity Log Details

**Endpoint:** `GET /activity-logs/{id}`

**Access:** Superadmin, Admin

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "log_name": "default",
    "description": "created",
    "subject_type": "Course",
    "subject_id": 1,
    "causer_type": "User",
    "causer_id": 2,
    "properties": {
      "attributes": {
        "title": "New Course"
      }
    },
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### List Audit Logs

**Endpoint:** `GET /audit-logs`

**Access:** Superadmin, Admin

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filter[action]` (string, optional): Exact match on action
- `filter[actions]` (string, optional): Multiple actions (comma-separated)
- `filter[actor_id]` (integer, optional): Exact match on actor ID
- `search` (string, optional): Full-text search
- `sort` (string, optional): Sort field

**Available Filters:**
- `filter[action]`: string (exact match - single action)
- `filter[actions]`: string (comma-separated actions)
- `filter[actor_id]`: integer (exact match)
- `search`: Full-text search in auditable type and action

**Available Sorts:**
- `sort=created_at`, `sort=-created_at` (default: `-created_at`)
- `sort=id`, `sort=-id`
- `sort=action`, `sort=-action`
- `sort=actor_id`, `sort=-actor_id`

**Example Requests:**
```
GET /audit-logs
GET /audit-logs?filter[action]=created
GET /audit-logs?filter[actions]=created,updated,deleted
GET /audit-logs?filter[actor_id]=2&sort=-created_at
GET /audit-logs?search=course
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "auditable_type": "Course",
      "auditable_id": 1,
      "action": "created",
      "old_values": null,
      "new_values": {
        "title": "New Course",
        "status": "draft"
      },
      "actor": {
        "id": 2,
        "name": "Admin User"
      },
      "created_at": "2024-01-15T10:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1000
  }
}
```

---

### Get Audit Log Details

**Endpoint:** `GET /audit-logs/{id}`

**Access:** Superadmin, Admin

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "auditable_type": "Course",
    "auditable_id": 1,
    "action": "updated",
    "old_values": {
      "title": "Old Title",
      "status": "draft"
    },
    "new_values": {
      "title": "New Title",
      "status": "published"
    },
    "actor": {
      "id": 2,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Get Available Audit Actions

**Endpoint:** `GET /audit-logs/meta/actions`

**Access:** Superadmin, Admin

**Response:**
```json
{
  "success": true,
  "data": [
    "created",
    "updated",
    "deleted",
    "published",
    "unpublished",
    "enrolled",
    "graded"
  ]
}
```

---

## Master Data

### Get Master Data Types

**Endpoint:** `GET /master-data/types`

**Access:** Public

**Response:**
```json
{
  "success": true,
  "data": [
    "user-status",
    "roles",
    "course-status",
    "course-types",
    "enrollment-types",
    "level-tags",
    "content-types",
    "enrollment-status",
    "progress-status",
    "assignment-status",
    "submission-status",
    "submission-types",
    "content-status",
    "priorities",
    "target-types",
    "challenge-types",
    "challenge-assignment-status",
    "challenge-criteria-types",
    "badge-types",
    "point-source-types",
    "point-reasons",
    "notification-types",
    "notification-channels",
    "notification-frequencies",
    "grade-status",
    "grade-source-types",
    "category-status",
    "setting-types"
  ]
}
```

**Note:** Type names use dash format (e.g., `course-types`, NOT `course_types` or `courses_types`)

---

### List Master Data Items

**Endpoint:** `GET /master-data/{type}`

**Access:** Public

**Path Parameters:**
- `type` (string, required): Master data type (use dash format, e.g., `course-types`)

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)

**Example Requests:**
```
GET /master-data/course-types
GET /master-data/level-tags
GET /master-data/enrollment-types
GET /master-data/assignment-status
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "value": "online",
      "label": "Online"
    },
    {
      "value": "hybrid",
      "label": "Hybrid"
    },
    {
      "value": "in_person",
      "label": "In Person"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 3
  }
}
```

---

### Get All Master Data Items (No Pagination)

**Endpoint:** `GET /master-data/{type}/all`

**Access:** Public

**Path Parameters:**
- `type` (string, required): Master data type (use dash format)

**Example Requests:**
```
GET /master-data/course-types/all
GET /master-data/level-tags/all
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "value": "online",
      "label": "Online"
    },
    {
      "value": "hybrid",
      "label": "Hybrid"
    },
    {
      "value": "in_person",
      "label": "In Person"
    }
  ]
}
```

---

### Get Master Data Item Details

**Endpoint:** `GET /master-data/{type}/{id}`

**Access:** Public

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "type": "course_types",
    "key": "online",
    "value": "Online",
    "order": 1,
    "created_at": "2024-01-01T00:00:00+00:00"
  }
}
```

---

### Create Master Data Item (Management)

**Endpoint:** `POST /master-data/{type}`

**Access:** Superadmin only

**Request Body:**
```json
{
  "key": "in_person",
  "value": "In Person",
  "order": 3
}
```

**Validation Rules:**
- `key`: required, string, max:255, unique within type
- `value`: required, string, max:255
- `order`: nullable, integer, min:0

**Response:**
```json
{
  "success": true,
  "message": "Master data item created successfully",
  "data": {
    "id": 3,
    "type": "course_types",
    "key": "in_person",
    "value": "In Person",
    "order": 3,
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Update Master Data Item (Management)

**Endpoint:** `PUT /master-data/{type}/{id}`

**Access:** Superadmin only

**Request Body:** Same as create (all fields optional)

**Response:** Updated master data item details

---

### Delete Master Data Item (Management)

**Endpoint:** `DELETE /master-data/{type}/{id}`

**Access:** Superadmin only

**Response:**
```json
{
  "success": true,
  "message": "Master data item deleted successfully"
}
```

---

# MANAGEMENT APIs

## Course Management

### Create Course

**Endpoint:** `POST /courses`

**Access:** Superadmin, Admin, Instructor (with create permission)


**Request Body (multipart/form-data):**
```
code: CS101 (required, string, max:50, unique)
title: Introduction to Programming (required, string, max:255)
short_desc: Learn programming basics (nullable, string)
type: online (nullable, string - get values from GET /master-data/course-types)
level_tag: beginner (nullable, string - get values from GET /master-data/level-tags)
enrollment_type: auto_accept (nullable, string - get values from GET /master-data/enrollment-types)
status: draft (nullable, string: draft|published)
category_id: 1 (nullable, integer - get from GET /categories)
instructor_id: 2 (nullable, integer - user ID with instructor role)
admin_ids: [3,4,5] (nullable, array of user IDs)
tags: [1,2,3] (nullable, array of tag IDs from GET /tags)
thumbnail: (file, optional, image, max:5MB)
banner: (file, optional, image, max:5MB)
```

**Response:**
```json
{
  "success": true,
  "message": "Course created successfully",
  "data": {
    "id": 1,
    "code": "CS101",
    "slug": "introduction-to-programming",
    "title": "Introduction to Programming",
    "status": "draft",
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Update Course

**Endpoint:** `PUT /courses/{slug}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** Same as create (all fields optional)

**Response:** Updated course details

---

### Delete Course

**Endpoint:** `DELETE /courses/{slug}`

**Access:** Superadmin, Admin, Instructor (must have delete permission)

**Response:**
```json
{
  "success": true,
  "message": "Course deleted successfully",
  "data": []
}
```

---

### Publish Course

**Endpoint:** `PUT /courses/{slug}/publish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Course published successfully",
  "data": {
    "id": 1,
    "status": "published",
    "published_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Unpublish Course

**Endpoint:** `PUT /courses/{slug}/unpublish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** None

**Response:** Course with status changed to `draft`

---

### Generate Enrollment Key

**Endpoint:** `POST /courses/{slug}/enrollment-key/generate`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Enrollment key generated",
  "data": {
    "course": {
      "id": 1,
      "enrollment_type": "key_based"
    },
    "enrollment_key": "ABC123XYZ"
  }
}
```


---

### Update Enrollment Key

**Endpoint:** `PUT /courses/{slug}/enrollment-key`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:**
```json
{
  "enrollment_type": "key_based",
  "enrollment_key": "NEWKEY123"
}
```

**Response:** Course with updated enrollment settings

---

### Remove Enrollment Key

**Endpoint:** `DELETE /courses/{slug}/enrollment-key`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** None

**Response:** Course with enrollment_type changed to `auto_accept`

---

## Unit Management

### Create Unit

**Endpoint:** `POST /courses/{course_slug}/units`

**Access:** Superadmin, Admin, Instructor (must have update permission on course)

**Request Body:**
```json
{
  "code": "UNIT01",
  "title": "Introduction Unit",
  "description": "Getting started",
  "order": 1,
  "status": "draft"
}
```

**Validation Rules:**
- `code`: required, string, max:50, unique
- `title`: required, string, max:255
- `description`: nullable, string
- `order`: nullable, integer, min:1, unique within course
- `status`: nullable, in:draft,published (values: `draft`, `published`)

**Response:**
```json
{
  "success": true,
  "message": "Unit created successfully",
  "data": {
    "id": 1,
    "code": "UNIT01",
    "slug": "introduction-unit",
    "title": "Introduction Unit",
    "order": 1,
    "status": "draft"
  }
}
```

---

### Update Unit

**Endpoint:** `PUT /courses/{course_slug}/units/{unit_slug}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** Same as create (all fields optional)

**Response:** Updated unit details

---

### Delete Unit

**Endpoint:** `DELETE /courses/{course_slug}/units/{unit_slug}`

**Access:** Superadmin, Admin, Instructor (must have delete permission)

**Response:**
```json
{
  "success": true,
  "message": "Unit deleted successfully",
  "data": []
}
```

---

### Publish Unit

**Endpoint:** `PUT /courses/{course_slug}/units/{unit_slug}/publish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Unit with status changed to `published`

---

### Unpublish Unit

**Endpoint:** `PUT /courses/{course_slug}/units/{unit_slug}/unpublish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Unit with status changed to `draft`


---

### Reorder Units

**Endpoint:** `PUT /courses/{course_slug}/units/reorder`

**Access:** Superadmin, Admin, Instructor (must have update permission on course)

**Request Body:**
```json
{
  "units": [
    {"id": 1, "order": 1},
    {"id": 2, "order": 2},
    {"id": 3, "order": 3}
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Units reordered successfully",
  "data": []
}
```

---

### Get Unit Content Order

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/content-order`

**Access:** Authenticated users (must have view permission on unit)

**Purpose:** Get the current order of all content (lessons, assignments, quizzes) within a unit.

**Response:**
```json
{
  "success": true,
  "message": "Content order retrieved successfully",
  "data": [
    {
      "type": "lesson",
      "id": 1,
      "title": "Introduction to Variables",
      "order": 1,
      "status": "published"
    },
    {
      "type": "lesson",
      "id": 2,
      "title": "Data Types",
      "order": 2,
      "status": "published"
    },
    {
      "type": "assignment",
      "id": 1,
      "title": "Variables Practice",
      "order": 3,
      "status": "published"
    },
    {
      "type": "quiz",
      "id": 1,
      "title": "Variables Quiz",
      "order": 4,
      "status": "published"
    }
  ]
}
```

---

### Reorder Unit Content

**Endpoint:** `PUT /courses/{course_slug}/units/{unit_slug}/content-order`

**Access:** Superadmin, Admin, Instructor (must have update permission on unit)

**Purpose:** Reorder all content (lessons, assignments, quizzes) within a unit in a single request.

**Request Body:**
```json
{
  "content": [
    {
      "type": "lesson",
      "id": 1,
      "order": 1
    },
    {
      "type": "quiz",
      "id": 1,
      "order": 2
    },
    {
      "type": "lesson",
      "id": 2,
      "order": 3
    },
    {
      "type": "assignment",
      "id": 1,
      "order": 4
    }
  ]
}
```

**Validation Rules:**
- `content`: required, array, min:1
- `content.*.type`: required, string, in:lesson,assignment,quiz
- `content.*.id`: required, integer
- `content.*.order`: required, integer, min:1

**Response:**
```json
{
  "success": true,
  "message": "Content reordered successfully",
  "data": [
    {
      "type": "lesson",
      "id": 1,
      "title": "Introduction to Variables",
      "order": 1,
      "status": "published"
    },
    {
      "type": "quiz",
      "id": 1,
      "title": "Variables Quiz",
      "order": 2,
      "status": "published"
    },
    {
      "type": "lesson",
      "id": 2,
      "title": "Data Types",
      "order": 3,
      "status": "published"
    },
    {
      "type": "assignment",
      "id": 1,
      "title": "Variables Practice",
      "order": 4,
      "status": "published"
    }
  ]
}
```

---

### List All Units (Global - Management Only)

**Endpoint:** `GET /units`

**Access:** Authenticated users (Admin/Instructor for cross-course access)

**Purpose:** Management convenience endpoint for viewing units across all courses. Students can only access units they have enrollment for.

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filter[status]` (string, optional): Filter by status
  - Values: `draft`, `published`
- `filter[course_slug]` (string, optional): Filter by course slug
  - Values: Get course slugs from `GET /courses`
- `search` (string, optional): Search in title, code, description

**Authorization:**
- Students: Only see units from courses with active/completed enrollment
- Admin/Instructor: See units from courses they manage
- Superadmin: See all units

**Response:** Paginated list of units with course information

---

### Get Unit Details (Global - Management Only)

**Endpoint:** `GET /units/{unit_slug}`

**Access:** Authenticated users (enrollment check applies for students)

**Purpose:** Direct unit access without course context. Students must have active enrollment in the parent course.

**Response:** Unit details with available includes based on user permissions

---

## Lesson Management

### Create Lesson

**Endpoint:** `POST /courses/{course_slug}/units/{unit_slug}/lessons`

**Access:** Superadmin, Admin, Instructor (must have update permission on unit)

**Request Body:**
```json
{
  "title": "First Lesson",
  "content": "Lesson content in markdown",
  "order": 1,
  "status": "draft",
  "duration_minutes": 30
}
```

**Validation Rules:**
- `title`: required, string, max:255
- `content`: nullable, string (markdown content, NOT sanitized at input)
- `order`: nullable, integer, min:1 (auto-assigned if not provided - will be placed after all existing content in the unit)
- `status`: nullable, in:draft,published
- `duration_minutes`: nullable, integer, min:1

**Note:** If `order` is not provided, the system will automatically assign the next available order number after all existing content (lessons, assignments, quizzes) in the unit.

**Response:**
```json
{
  "success": true,
  "message": "Lesson created successfully",
  "data": {
    "id": 1,
    "slug": "first-lesson",
    "title": "First Lesson",
    "order": 1,
    "status": "draft"
  }
}
```

---

### Update Lesson

**Endpoint:** `PUT /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** Same as create (all fields optional)

**Response:** Updated lesson details

---

### Delete Lesson

**Endpoint:** `DELETE /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}`

**Access:** Superadmin, Admin, Instructor (must have delete permission)

**Response:**
```json
{
  "success": true,
  "message": "Lesson deleted successfully",
  "data": []
}
```

---

### Publish Lesson

**Endpoint:** `PUT /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/publish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Lesson with status changed to `published`

---

### Unpublish Lesson

**Endpoint:** `PUT /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/unpublish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Lesson with status changed to `draft`

---

### List All Lessons (Global - Management Only)

**Endpoint:** `GET /lessons`

**Access:** Authenticated users (Admin/Instructor for cross-course access)

**Purpose:** Management convenience endpoint for viewing lessons across all courses. Students can only access lessons they have enrollment for.

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filter[status]` (string, optional): Filter by status
  - Values: `draft`, `published`
- `filter[unit_slug]` (string, optional): Filter by unit slug
- `search` (string, optional): Search in title, content

**Authorization:**
- Students: Only see lessons from courses with active/completed enrollment
- Admin/Instructor: See lessons from courses they manage
- Superadmin: See all lessons

**Response:** Paginated list of lessons with unit and course information

---

### Get Lesson Details (Global - Management Only)

**Endpoint:** `GET /lessons/{lesson_slug}`

**Access:** Authenticated users (enrollment check applies for students)

**Purpose:** Direct lesson access without course/unit context. Students must have active enrollment in the parent course.

**Response:** Lesson details with completion status and available includes based on user permissions

---

### Create Lesson Block

**Endpoint:** `POST /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks`

**Access:** Superadmin, Admin, Instructor (must have update permission on lesson)

**Request Body (multipart/form-data):**
```
type: text (required, string: text|video|image|code|file|embed)
content: Block content (nullable, string)
order: 1 (nullable, integer)
media: (file, optional, for image/video/file types)
```

**Response:**
```json
{
  "success": true,
  "message": "Lesson block created successfully",
  "data": {
    "id": 1,
    "type": "text",
    "content": "Block content",
    "order": 1
  }
}
```


---

### Update Lesson Block

**Endpoint:** `PUT /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** Same as create (all fields optional)

**Response:** Updated block details

---

### Delete Lesson Block

**Endpoint:** `DELETE /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}`

**Access:** Superadmin, Admin, Instructor (must have delete permission)

**Response:**
```json
{
  "success": true,
  "message": "Lesson block deleted successfully",
  "data": []
}
```

---

## Assignment Management

### List All Assessments (Course)

**Endpoint:** `GET /courses/{course_slug}/assessments`

**Access:** Superadmin, Admin, Instructor

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[type]` (string, optional): Filter by type (`assignment`, `quiz`)
- `filter[status]` (string, optional): Filter by status

**Response:** Paginated list of all assessments (assignments + quizzes) in the course

---

### Create Assignment

**Endpoint:** `POST /assignments`

**Access:** Superadmin, Admin, Instructor

**Request Body (multipart/form-data):**
```json
{
  "type": "assignment",
  "title": "Week 1 Assignment",
  "description": "Complete the programming exercises",
  "unit_id": 1,
  "order": 3,
  "submission_type": "file",
  "max_score": 100,
  "status": "draft",
  "allow_resubmit": true,
  "time_limit_minutes": 60,
  "max_attempts": 3,
  "cooldown_minutes": 60,
  "retake_enabled": true,
  "review_mode": "manual",
  "attachments": [file1, file2]
}
```

**Validation Rules:**
- `type`: required, enum (assignment, quiz)
- `title`: required, string, max:255
- `description`: nullable, string
- `unit_id`: required, integer, exists:units,id
- `order`: nullable, integer, min:1 (auto-assigned if not provided - will be placed after all existing content in the unit)
- `submission_type`: required, enum (file, text, mixed, online) - assignment must use file/mixed
- `max_score`: nullable, integer, min:1, max:1000
- `status`: nullable, enum (draft, published, archived)
- `allow_resubmit`: nullable, boolean
- `time_limit_minutes`: nullable, integer, min:1
- `max_attempts`: nullable, integer, min:1
- `cooldown_minutes`: nullable, integer, min:0
- `retake_enabled`: nullable, boolean
- `review_mode`: nullable, enum (manual, immediate, after_deadline, never) - assignment must use manual
- `randomization_type`: nullable, enum (static, random_order, bank) - NOT allowed for assignments
- `question_bank_count`: nullable, integer, min:0 - NOT allowed for assignments
- `attachments`: nullable, array, max:5 files
- `attachments.*`: file, mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,webp, max:10240KB

**Note:** If `order` is not provided, the system will automatically assign the next available order number after all existing content (lessons, assignments, quizzes) in the unit.

**Response:**
```json
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "id": 1,
    "title": "Week 1 Assignment",
    "type": "assignment",
    "status": "draft",
    "order": 3,
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```


---

### Update Assignment

**Endpoint:** `PUT /assignments/{assignment_id}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body (multipart/form-data):**
```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "max_score": 120,
  "attachments": [new_file],
  "delete_attachments": [1, 2]
}
```

**Validation Rules:** Same as create, all fields optional
- `delete_attachments`: nullable, array of media IDs to delete
- `delete_attachments.*`: integer, exists:media,id

**Response:** Updated assignment details

---

### Delete Assignment

**Endpoint:** `DELETE /assignments/{assignment_id}`

**Access:** Superadmin, Admin, Instructor (must have delete permission)

**Response:**
```json
{
  "success": true,
  "message": "Assignment deleted successfully",
  "data": []
}
```

---

### Publish Assignment

**Endpoint:** `PUT /assignments/{assignment_id}/publish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Assignment with status changed to `published`

---

### Unpublish Assignment

**Endpoint:** `PUT /assignments/{assignment_id}/unpublish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Assignment with status changed to `draft`

---

### Archive Assignment

**Endpoint:** `PUT /assignments/{assignment_id}/archived`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Assignment with status changed to `archived`

---

### Duplicate Assignment

**Endpoint:** `POST /assignments/{assignment_id}/duplicate`

**Access:** Superadmin, Admin, Instructor (must have duplicate permission)

**Request Body:**
```json
{
  "title": "Copy of Week 1 Assignment",
  "assignable_type": "Lesson",
  "assignable_slug": "new-lesson-slug"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Assignment duplicated successfully",
  "data": {
    "id": 2,
    "title": "Copy of Week 1 Assignment",
    "status": "draft"
  }
}
```

---

### List Assignment Overrides

**Endpoint:** `GET /assignments/{assignment_id}/overrides`

**Access:** Superadmin, Admin, Instructor (must have viewOverrides permission)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "student_id": 5,
      "type": "attempt_limit",
      "reason": "Student needs extra attempts",
      "value": {"max_attempts": 5},
      "granted_by": 2,
      "created_at": "2024-01-15T10:00:00+00:00"
    }
  ]
}
```


---

### Grant Assignment Override

**Endpoint:** `POST /assignments/{assignment_id}/overrides`

**Access:** Superadmin, Admin, Instructor (must have grantOverride permission)

**Request Body:**
```json
{
  "student_id": 5,
  "type": "attempt_limit",
  "reason": "Student needs extra attempts due to technical issues",
  "value": {
    "max_attempts": 5
  }
}
```

**Override Types:**
- `attempt_limit`: Override max_attempts (value: `{"max_attempts": integer}`)
- `deadline_extension`: Extend deadline (value: `{"extended_deadline": "2024-01-30T23:59:59Z"}`)
- `prerequisite_bypass`: Bypass prerequisites (value: `{}`)

**Response:**
```json
{
  "success": true,
  "message": "Override granted successfully",
  "data": {
    "id": 1,
    "type": "attempt_limit",
    "student_id": 5,
    "reason": "Student needs extra attempts due to technical issues"
  }
}
```

---

### List Assignment Submissions

**Endpoint:** `GET /assignments/{assignment_id}/submissions`

**Access:** Superadmin, Admin, Instructor

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[status]` (string, optional): Filter by status
  - Values: `in_progress`, `submitted`, `graded`, `returned`
- `filter[state]` (string, optional): Filter by state
  - Values: `pending_grading`, `grading_in_progress`, `graded_unreleased`, `released`
- `filter[user_id]` (integer, optional): Filter by user
  - Values: User ID

**Response:** Paginated list of all submissions for the assignment

---

### Search Submissions

**Endpoint:** `GET /submissions/search`

**Access:** Superadmin, Admin, Instructor

**Query Parameters:**
- `query` (string, optional): Search term
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filters[assignment_id]` (integer, optional): Filter by assignment
  - Values: Get assignment IDs from `GET /courses/{slug}/assignments`
- `filters[user_id]` (integer, optional): Filter by user
  - Values: User ID
- `filters[status]` (string, optional): Filter by status
  - Values: `in_progress`, `submitted`, `graded`, `returned`
- `filters[state]` (string, optional): Filter by state
  - Values: `pending_grading`, `grading_in_progress`, `graded_unreleased`, `released`

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "assignment_id": 1,
        "user_id": 5,
        "status": "graded",
        "score": 85
      }
    ],
    "meta": {
      "total": 50,
      "per_page": 15,
      "current_page": 1,
      "last_page": 4
    }
  }
}
```

---

## Quiz Management

### Create Quiz

**Endpoint:** `POST /quizzes`

**Access:** Superadmin, Admin, Instructor

**Request Body:**
```json
{
  "unit_id": 1,
  "order": 4,
  "title": "Week 1 Quiz",
  "description": "Test your knowledge",
  "passing_grade": 70,
  "auto_grading": true,
  "max_score": 100,
  "max_attempts": 2,
  "cooldown_minutes": 30,
  "time_limit_minutes": 30,
  "retake_enabled": true,
  "randomization_type": "random_order",
  "question_bank_count": 10,
  "review_mode": "after_deadline"
}
```

**Validation Rules:**
- `unit_id`: required, integer, exists:units,id
- `order`: nullable, integer, min:1 (auto-assigned if not provided - will be placed after all existing content in the unit)
- `title`: required, string, max:255
- `description`: nullable, string
- `passing_grade`: nullable, numeric, min:0, max:100
- `auto_grading`: nullable, boolean
- `max_score`: nullable, numeric, min:1
- `max_attempts`: nullable, integer, min:1
- `cooldown_minutes`: nullable, integer, min:0
- `time_limit_minutes`: nullable, integer, min:1
- `retake_enabled`: nullable, boolean
- `randomization_type`: nullable, in:static,random_order,bank
- `question_bank_count`: nullable, integer, min:1
- `review_mode`: nullable, in:immediate,after_deadline,never

**Note:** If `order` is not provided, the system will automatically assign the next available order number after all existing content (lessons, assignments, quizzes) in the unit.

**Response:**
```json
{
  "success": true,
  "message": "Quiz created successfully",
  "data": {
    "id": 1,
    "title": "Week 1 Quiz",
    "status": "draft",
    "order": 4,
    "created_at": "2024-01-15T10:00:00+00:00"
  }
}
```


---

### Update Quiz

**Endpoint:** `PUT /quizzes/{quiz_id}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** Same as create (all fields optional)

**Response:** Updated quiz details

---

### Delete Quiz

**Endpoint:** `DELETE /quizzes/{quiz_id}`

**Access:** Superadmin, Admin, Instructor (must have delete permission)

**Response:**
```json
{
  "success": true,
  "message": "Quiz deleted successfully",
  "data": []
}
```

---

### Publish Quiz

**Endpoint:** `PUT /quizzes/{quiz_id}/publish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Quiz with status changed to `published`

---

### Unpublish Quiz

**Endpoint:** `PUT /quizzes/{quiz_id}/unpublish`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Quiz with status changed to `draft`

---

### Archive Quiz

**Endpoint:** `PUT /quizzes/{quiz_id}/archived`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:** Quiz with status changed to `archived`

---

### Get Quiz Question Details

**Endpoint:** `GET /quizzes/{quiz_id}/questions/{question_id}`

**Access:** Superadmin, Admin, Instructor (must have view permission)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "quiz_id": 1,
    "type": "multiple_choice",
    "content": "What is 2 + 2?",
    "options": [
      {"text": "3"},
      {"text": "4"},
      {"text": "5"}
    ],
    "answer_key": [1],
    "weight": 1,
    "max_score": 10,
    "order": 1
  }
}
```

---

### Add Quiz Question

**Endpoint:** `POST /quizzes/{quiz_id}/questions`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body (multipart/form-data):**
```json
{
  "type": "multiple_choice",
  "content": "What is 2 + 2?",
  "options": [
    {"text": "3"},
    {"text": "4"},
    {"text": "5"}
  ],
  "answer_key": [1],
  "weight": 1,
  "order": 1,
  "max_score": 10
}
```

**Validation Rules:**
- `type`: required, enum (multiple_choice, true_false, short_answer, essay, checkbox)
- `content`: required, string
- `options`: nullable, array (required for multiple_choice, checkbox, true_false)
- `options.*.text`: nullable, string
- `options.*.image`: nullable, file, image
- `answer_key`: nullable, array (correct answer indices or text)
- `weight`: nullable, numeric, min:0.01
- `order`: nullable, integer, min:0
- `max_score`: nullable, numeric, min:0

**Response:**
```json
{
  "success": true,
  "message": "Question created successfully",
  "data": {
    "id": 1,
    "type": "multiple_choice",
    "content": "What is 2 + 2?",
    "order": 1
  }
}
```


---

### Update Quiz Question

**Endpoint:** `PUT /quizzes/{quiz_id}/questions/{question_id}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:** Same as add question (all fields optional)

**Response:** Updated question details

---

### Delete Quiz Question

**Endpoint:** `DELETE /quizzes/{quiz_id}/questions/{question_id}`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Response:**
```json
{
  "success": true,
  "message": "Question deleted successfully",
  "data": []
}
```

---

### Reorder Quiz Questions

**Endpoint:** `POST /quizzes/{quiz_id}/questions/reorder`

**Access:** Superadmin, Admin, Instructor (must have update permission)

**Request Body:**
```json
{
  "ids": [3, 1, 2, 4]
}
```

**Validation Rules:**
- `ids`: required, array of question IDs in desired order
- `ids.*`: integer

**Response:**
```json
{
  "success": true,
  "message": "Questions reordered successfully",
  "data": []
}
```

---

### List Quiz Submissions

**Endpoint:** `GET /quizzes/{quiz_id}/submissions`

**Access:** Superadmin, Admin, Instructor (must have viewSubmissions permission)

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[user_id]` (integer, optional): Filter by user
  - Values: User ID
- `filter[status]` (string, optional): Filter by status
  - Values: `in_progress`, `submitted`, `graded`

**Response:** Paginated list of quiz submissions

---

## Grading Management

### Get Grading Queue

**Endpoint:** `GET /grading`

**Access:** Superadmin, Admin, Instructor

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filter[state]` (string, optional): Filter by state
  - Values: `pending_grading`, `grading_in_progress`, `graded_unreleased`
- `filter[assignment_id]` (integer, optional): Filter by assignment
  - Values: Get assignment IDs from `GET /courses/{slug}/assignments`
- `filter[course_slug]` (string, optional): Filter by course slug
  - Values: Get course slugs from `GET /courses`
- `filter[user_id]` (integer, optional): Filter by student
  - Values: User ID
- `sort` (string, optional): Sort field (e.g., `submitted_at`, `-submitted_at` for desc)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "assignment_title": "Week 1 Assignment",
      "user_id": 5,
      "user_name": "John Doe",
      "status": "submitted",
      "state": "pending_grading",
      "submitted_at": "2024-01-15T10:00:00+00:00",
      "course": {
        "id": 1,
        "title": "Introduction to Programming"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50
  }
}
```


---

### Get Submission for Grading

**Endpoint:** `GET /grading/{submission_id}`

**Access:** Superadmin, Admin, Instructor

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "assignment": {
      "id": 1,
      "title": "Week 1 Assignment",
      "max_score": 100,
      "instructions": "Complete all questions"
    },
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "answers": [
      {
        "id": 1,
        "question_id": 1,
        "question": {
          "id": 1,
          "content": "Question text",
          "max_score": 10
        },
        "answer": "Student answer"
      }
    ],
    "submitted_at": "2024-01-15T10:00:00+00:00",
    "state": "pending_grading"
  }
}
```

---

### Manual Grade Submission

**Endpoint:** `POST /submissions/{submission_id}/grades`

**Access:** Superadmin, Admin, Instructor (must have grade permission)

**Request Body:**
```json
{
  "grades": [
    {
      "question_id": 1,
      "score": 8,
      "feedback": "Good answer, minor issues"
    },
    {
      "question_id": 2,
      "score": 10,
      "feedback": "Perfect!"
    }
  ],
  "feedback": "Overall good work, keep it up"
}
```

**Validation Rules:**
- `grades`: required, array, min:1
- `grades.*.question_id`: required, integer, exists:questions,id
- `grades.*.score`: required, numeric, min:0
- `grades.*.feedback`: nullable, string
- `feedback`: nullable, string (overall feedback)

**Response:**
```json
{
  "success": true,
  "message": "Submission graded successfully",
  "data": {
    "id": 1,
    "submission_id": 1,
    "score": 85,
    "feedback": "Overall good work, keep it up",
    "graded_at": "2024-01-16T10:00:00+00:00",
    "grader": {
      "id": 2,
      "name": "Instructor Name"
    }
  }
}
```

---

### Save Draft Grade

**Endpoint:** `PUT /submissions/{submission_id}/grades/draft`

**Access:** Superadmin, Admin, Instructor

**Request Body:**
```json
{
  "grades": [
    {
      "question_id": 1,
      "score": 8,
      "feedback": "Draft feedback"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Draft grade saved",
  "data": {
    "submission_id": 1
  }
}
```

---

### Get Draft Grade

**Endpoint:** `GET /submissions/{submission_id}/grades/draft`

**Access:** Superadmin, Admin, Instructor

**Response:**
```json
{
  "success": true,
  "data": {
    "submission_id": 1,
    "grades": [
      {
        "question_id": 1,
        "score": 8,
        "feedback": "Draft feedback"
      }
    ],
    "saved_at": "2024-01-16T09:00:00+00:00"
  }
}
```

**If no draft exists:**
```json
{
  "success": true,
  "data": null
}
```


---

### Override Grade

**Endpoint:** `PATCH /submissions/{submission_id}/grades`

**Access:** Superadmin, Admin, Instructor

**Request Body:**
```json
{
  "score": 95,
  "reason": "Adjusted for partial credit on question 3"
}
```

**Validation Rules:**
- `score`: required, numeric, min:0
- `reason`: required, string

**Response:**
```json
{
  "success": true,
  "message": "Grade overridden successfully",
  "data": {
    "submission_id": 1,
    "score": 95,
    "grade": {
      "id": 1,
      "score": 95,
      "is_override": true,
      "override_reason": "Adjusted for partial credit on question 3"
    }
  }
}
```

---

### Release Grade

**Endpoint:** `PATCH /submissions/{submission_id}/grades/release`

**Access:** Superadmin, Admin, Instructor

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Grade released successfully",
  "data": {
    "submission_id": 1,
    "state": "released",
    "grade": {
      "id": 1,
      "score": 85,
      "released_at": "2024-01-16T10:00:00+00:00"
    }
  }
}
```

---

### Return to Queue

**Endpoint:** `PATCH /submissions/{submission_id}/grades/return-to-queue`

**Access:** Superadmin, Admin, Instructor

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Submission returned to queue",
  "data": {
    "submission_id": 1,
    "state": "pending_grading"
  }
}
```

---

### Get Grading Status

**Endpoint:** `GET /submissions/{submission_id}/grades/status`

**Access:** Superadmin, Admin, Instructor

**Response:**
```json
{
  "success": true,
  "data": {
    "submission_id": 1,
    "state": "graded_unreleased",
    "has_grade": true,
    "is_released": false,
    "graded_at": "2024-01-16T10:00:00+00:00",
    "grader": {
      "id": 2,
      "name": "Instructor Name"
    }
  }
}
```

---

### Bulk Release Grades

**Endpoint:** `POST /grading/bulk-release`

**Access:** Superadmin, Admin, Instructor

**Request Body:**
```json
{
  "submission_ids": [1, 2, 3, 4, 5],
  "async": false
}
```

**Validation Rules:**
- `submission_ids`: required, array, min:1
- `submission_ids.*`: integer, exists:submissions,id
- `async`: nullable, boolean (if true, queues job for background processing)

**Response (sync):**
```json
{
  "success": true,
  "message": "Grades released successfully",
  "data": {
    "async": false
  }
}
```

**Response (async):**
```json
{
  "success": true,
  "message": "Bulk release queued for processing",
  "data": {
    "async": true
  }
}
```


---

### Bulk Apply Feedback

**Endpoint:** `POST /grading/bulk-feedback`

**Access:** Superadmin, Admin, Instructor

**Request Body:**
```json
{
  "submission_ids": [1, 2, 3],
  "feedback": "Great work on this assignment!",
  "async": false
}
```

**Validation Rules:**
- `submission_ids`: required, array, min:1
- `submission_ids.*`: integer, exists:submissions,id
- `feedback`: required, string
- `async`: nullable, boolean

**Response (sync):**
```json
{
  "success": true,
  "message": "Feedback applied successfully",
  "data": {
    "async": false
  }
}
```

**Response (async):**
```json
{
  "success": true,
  "message": "Bulk feedback queued for processing",
  "data": {
    "async": true
  }
}
```

---

## Enrollment Management

### List Course Enrollments

**Endpoint:** `GET /courses/{course_slug}/enrollments`

**Access:** Superadmin, Admin, Instructor (must have viewByCourse permission)

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filter[status]` (string, optional): Filter by status
  - Values: `pending`, `active`, `completed`, `withdrawn`, `cancelled`, `declined`, `expelled`
- `filter[user_id]` (integer, optional): Filter by user
  - Values: User ID
- `include` (string, optional): Comma-separated relations
  - Values: `user`, `course`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "user_id": 5,
      "status": "active",
      "enrolled_at": "2024-01-01T00:00:00+00:00",
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50
  }
}
```

---

### Get Enrollment Details (Course Context)

**Endpoint:** `GET /courses/{course_slug}/enrollments/{enrollment_id}`

**Access:** Superadmin, Admin, Instructor (must have view permission)

**Query Parameters:**
- `include` (string, optional): Comma-separated relations

**Response:** Single enrollment with full details

---

### Approve Enrollment

**Endpoint:** `POST /enrollments/{enrollment_id}/approve`

**Access:** Superadmin, Admin, Instructor (must have approve permission)

**Rate Limit:** 5 requests per minute

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Enrollment approved",
  "data": {
    "id": 1,
    "status": "active",
    "approved_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Decline Enrollment

**Endpoint:** `POST /enrollments/{enrollment_id}/decline`

**Access:** Superadmin, Admin, Instructor (must have decline permission)

**Rate Limit:** 5 requests per minute

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Enrollment declined",
  "data": {
    "id": 1,
    "status": "declined"
  }
}
```


---

### Remove Enrollment (Expel)

**Endpoint:** `POST /enrollments/{enrollment_id}/remove`

**Access:** Superadmin, Admin, Instructor (must have remove permission)

**Rate Limit:** 5 requests per minute

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Student expelled from course",
  "data": {
    "id": 1,
    "status": "expelled",
    "expelled_at": "2024-01-15T10:00:00+00:00"
  }
}
```

---

### Bulk Approve Enrollments

**Endpoint:** `POST /enrollments/approve/bulk`

**Access:** Superadmin, Admin, Instructor (must have approve permission on each enrollment)

**Rate Limit:** 5 requests per minute

**Request Body:**
```json
{
  "enrollment_ids": [1, 2, 3, 4, 5]
}
```

**Validation Rules:**
- `enrollment_ids`: required, array, min:1, max:100
- `enrollment_ids.*`: integer, exists:enrollments,id

**Response:**
```json
{
  "success": true,
  "message": "Bulk action completed",
  "data": {
    "processed": [
      {
        "id": 1,
        "status": "active"
      },
      {
        "id": 2,
        "status": "active"
      }
    ],
    "failed": [
      {
        "id": 3,
        "reason": "Already approved"
      }
    ]
  }
}
```

---

### Bulk Decline Enrollments

**Endpoint:** `POST /enrollments/decline/bulk`

**Access:** Superadmin, Admin, Instructor (must have decline permission on each enrollment)

**Rate Limit:** 5 requests per minute

**Request Body:**
```json
{
  "enrollment_ids": [1, 2, 3]
}
```

**Response:** Same structure as bulk approve

---

### Bulk Remove Enrollments

**Endpoint:** `POST /enrollments/remove/bulk`

**Access:** Superadmin, Admin, Instructor (must have remove permission on each enrollment)

**Rate Limit:** 5 requests per minute

**Request Body:**
```json
{
  "enrollment_ids": [1, 2, 3]
}
```

**Response:** Same structure as bulk approve

---

### Get Course Completion Rate

**Endpoint:** `GET /courses/{course_slug}/reports/completion-rate`

**Access:** Superadmin, Admin, Instructor

**Response:**
```json
{
  "success": true,
  "data": {
    "course_id": 1,
    "total_enrollments": 100,
    "active_enrollments": 80,
    "completed_enrollments": 15,
    "completion_rate": 15,
    "average_progress": 65.5
  }
}
```

---

### Get Enrollment Funnel

**Endpoint:** `GET /reports/enrollment-funnel`

**Access:** Superadmin, Admin, Instructor

**Response:**
```json
{
  "success": true,
  "data": {
    "pending": 25,
    "active": 150,
    "completed": 30,
    "withdrawn": 10,
    "cancelled": 5,
    "declined": 8,
    "expelled": 2
  }
}
```

---

### Export Enrollments CSV

**Endpoint:** `GET /courses/{course_slug}/exports/enrollments-csv`

**Access:** Superadmin, Admin, Instructor

**Response:** CSV file download with enrollment data

**CSV Columns:**
- Enrollment ID
- Student Name
- Student Email
- Status
- Enrolled At
- Completed At
- Progress Percentage

---

## Common Query Parameters

### Pagination
All list endpoints support:
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 15, max varies by endpoint)

### Filtering
Use `filter[field]` syntax:
- `filter[status]=published`
- `filter[type]=assignment`
- `filter[user_id]=5`

**Available Filter Fields by Endpoint:**

#### Courses (`GET /courses`)
- `filter[status]`: `draft`, `published`, `archived`
- `filter[type]`: Get values from `GET /master-data/course-types`
  - Common values: `online`, `hybrid`, `in_person`
- `filter[level_tag]`: Get values from `GET /master-data/level-tags`
  - Common values: `beginner`, `intermediate`, `advanced`
- `filter[category_id]`: integer - Get category IDs from `GET /categories`
- `filter[enrollment_type]`: Get values from `GET /master-data/enrollment-types`
  - Common values: `auto_accept`, `approval_required`, `key_based`
- `search`: Full-text search in title, code, description

#### Units (`GET /courses/{slug}/units`)
- `filter[status]`: `draft`, `published`
- `search`: Full-text search in title, code, description

**Note:** Units can only be accessed through course hierarchy. Students must have active enrollment.

#### Lessons (`GET /courses/{slug}/units/{slug}/lessons`)
- `filter[status]`: `draft`, `published`
- `search`: Full-text search in title, content

**Note:** Lessons can only be accessed through course/unit hierarchy. Students must have active enrollment.

#### Assignments (`GET /courses/{slug}/assignments`)
- `filter[status]`: `draft`, `published`, `archived`
- `filter[type]`: Get values from `GET /master-data/assignment-status`
  - Common values: `assignment`, `quiz`
- `filter[assignable_type]`: `Course`, `Unit`, `Lesson`

#### Quizzes (`GET /courses/{slug}/quizzes`)
- `filter[status]`: `draft`, `published`, `archived`
- `filter[assignable_type]`: `Course`, `Unit`, `Lesson`

#### Enrollments (`GET /enrollments`, `GET /courses/{slug}/enrollments`)
- `filter[status]`: `pending`, `active`, `completed`, `withdrawn`, `cancelled`, `declined`, `expelled`
- `filter[course_slug]`: string - Get course slugs from `GET /courses`
- `filter[user_id]`: integer - User ID

#### Submissions (`GET /assignments/{id}/submissions`)
- `filter[status]`: `in_progress`, `submitted`, `graded`, `returned`
- `filter[state]`: `pending_grading`, `grading_in_progress`, `graded_unreleased`, `released`
- `filter[user_id]`: integer - User ID

#### Challenges (`GET /challenges`)
- `filter[type]`: `daily`, `weekly`, `monthly`, `special`
- `filter[status]`: `active`, `upcoming`, `expired`

#### Leaderboard (`GET /leaderboards`)
- `filter[course_slug]`: string (course slug)
- `filter[period]`: `today`, `this_week`, `this_month`, `this_year`, `all_time`

#### Grading Queue (`GET /grading`)
- `filter[state]`: `pending_grading`, `grading_in_progress`, `graded_unreleased`
- `filter[assignment_id]`: integer (assignment ID)
- `filter[course_slug]`: string - Get course slugs from `GET /courses`
- `filter[user_id]`: integer (student ID)

### Sorting
Use `sort` parameter:
- `sort=created_at` (ascending)
- `sort=-created_at` (descending, note the minus sign)

**Available Sort Fields by Endpoint:**

#### Courses (`GET /courses`)
- `sort=title`, `sort=-title`
- `sort=code`, `sort=-code`
- `sort=created_at`, `sort=-created_at`
- `sort=published_at`, `sort=-published_at`
- Default: `-created_at`

#### Units (`GET /courses/{slug}/units`)
- `sort=order`, `sort=-order`
- `sort=title`, `sort=-title`
- `sort=created_at`, `sort=-created_at`
- Default: `order`

#### Lessons (`GET /courses/{slug}/units/{slug}/lessons`)
- `sort=order`, `sort=-order`
- `sort=title`, `sort=-title`
- `sort=created_at`, `sort=-created_at`
- Default: `order`

#### Assignments/Quizzes
- `sort=title`, `sort=-title`
- `sort=created_at`, `sort=-created_at`
- `sort=max_score`, `sort=-max_score`
- Default: `-created_at`

#### Enrollments
- `sort=enrolled_at`, `sort=-enrolled_at`
- `sort=completed_at`, `sort=-completed_at`
- Default: `-enrolled_at`

#### Submissions
- `sort=submitted_at`, `sort=-submitted_at`
- `sort=score`, `sort=-score`
- `sort=attempt_number`, `sort=-attempt_number`
- Default: `-submitted_at`

#### Grading Queue
- `sort=submitted_at`, `sort=-submitted_at`
- `sort=assignment_id`, `sort=-assignment_id`
- Default: `submitted_at`

#### Leaderboard
- **No custom sorting allowed** - Always sorted by `total_xp` (descending)

### Including Relations
Use `include` parameter:
- `include=user,course`
- `include=answers.question`

**Available Relations by Endpoint:**

#### Courses
- `include=instructor` - Course instructor details
- `include=category` - Course category
- `include=tags` - Course tags
- `include=admins` - Course administrators
- `include=units` - Course units
- `include=enrollments` - Course enrollments
- `include=quizzes` - Course quizzes
- `include=assignments` - Course assignments
- `include=lessons` - All lessons in course

#### Units
- `include=course` - Parent course
- `include=lessons` - Unit lessons

#### Lessons
- `include=unit` - Parent unit
- `include=unit.course` - Parent unit with course
- `include=blocks` - Lesson blocks

#### Assignments/Quizzes
- `include=lesson` - Parent lesson
- `include=unit` - Parent unit
- `include=course` - Parent course
- `include=questions` - Assignment/quiz questions
- `include=submissions` - All submissions
- `include=attachments` - Assignment attachments (media)

#### Enrollments
- `include=user` - Enrolled user
- `include=course` - Enrolled course
- `include=course.instructor` - Course with instructor

#### Submissions
- `include=assignment` - Parent assignment
- `include=user` - Submitter
- `include=answers` - Submission answers
- `include=answers.question` - Answers with questions
- `include=grade` - Submission grade

### Search
Use `search` parameter for full-text search:
- `search=programming`

**Search Behavior:**
- Uses PostgreSQL Full-Text Search
- Searches across multiple fields (title, description, content, code)
- Case-insensitive
- Supports partial word matching
- Returns ranked results

**Endpoints with Search:**
- `GET /courses?search=laravel`
- `GET /courses/{slug}/units?search=introduction`
- `GET /courses/{slug}/units/{unit_slug}/lessons?search=variables`

### Example Combined Queries

```
# Courses: Published, beginner level, sorted by title
GET /courses?filter[status]=published&filter[level_tag]=beginner&sort=title

# Units: In specific course, published only
GET /courses/laravel-basics/units?filter[status]=published&sort=order

# Enrollments: Active students in course
GET /courses/laravel-basics/enrollments?filter[status]=active&include=user

# Enrollments: Filter by course slug
GET /enrollments?filter[course_slug]=laravel-basics&filter[status]=active

# Submissions: Pending grading, sorted by submission date
GET /grading?filter[state]=pending_grading&sort=submitted_at

# Grading: Filter by course slug
GET /grading?filter[course_slug]=laravel-basics&filter[state]=pending_grading

# Leaderboard: This week's top performers
GET /leaderboards?filter[period]=this_week&per_page=20

# Assignments: Published assignments with questions
GET /courses/laravel-basics/assignments?filter[status]=published&include=questions

# Announcements: Filter by course and priority
GET /announcements?filter[course_slug]=laravel-basics&filter[priority]=high

# Search courses with pagination
GET /courses?search=programming&per_page=20&page=2
```

---

## Error Codes

### HTTP Status Codes
- `200 OK`: Successful request
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

### Validation Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "max_score": ["The max score must be at least 1."]
  }
}
```

---

## Rate Limiting

### Default API Rate Limit
- 60 requests per minute per user

### Enrollment Rate Limit
- 5 requests per minute for enrollment state changes
- Applies to: enroll, cancel, withdraw, approve, decline, remove, bulk operations

### Rate Limit Headers
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1642345678
```

---

## File Upload Guidelines

### Supported File Types
**Assignment Attachments:**
- Documents: pdf, doc, docx, xls, xlsx, ppt, pptx
- Archives: zip
- Images: jpg, jpeg, png, webp

**Course Media:**
- Images: jpg, jpeg, png, webp, gif
- Max size: 5MB

**Lesson Block Media:**
- Images: jpg, jpeg, png, webp
- Videos: mp4, webm
- Max size: varies by type

### Upload Format
Use `multipart/form-data` content type for file uploads.

**Example:**
```
POST /assignments
Content-Type: multipart/form-data

title=Week 1 Assignment
description=Complete exercises
attachments[]=@file1.pdf
attachments[]=@file2.pdf
```

---

## Authentication

### Bearer Token
Include in Authorization header:
```
Authorization: Bearer {your-access-token}
```

### Token Expiration
Tokens expire based on configuration. Refresh tokens before expiration.

### Roles
- **Student**: Access to learning content, submissions, progress
- **Instructor**: Manage courses, grade submissions, view reports
- **Admin**: Full course management, user management
- **Superadmin**: System-wide access, all permissions

---

## Best Practices

1. **Always check prerequisites** before allowing assignment attempts
2. **Validate enrollment status** before accessing course content
3. **Use pagination** for large datasets
4. **Include relations** only when needed to reduce payload size
5. **Handle rate limits** gracefully with exponential backoff
6. **Cache course/unit/lesson data** on client side
7. **Use bulk operations** for batch updates when available
8. **Check attempt limits** before starting submissions
9. **Save draft grades** periodically during grading
10. **Use async mode** for bulk operations with large datasets

---

## Changelog

**Version 1.0** (Current)
- Initial API release
- All modules documented
- Student and Management APIs complete

---

**End of Documentation**
