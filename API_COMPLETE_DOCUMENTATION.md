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
- `filter[status]` (string, optional): Filter by status (`published`, `draft`)
- `filter[type]` (string, optional): Filter by course type
- `filter[level_tag]` (string, optional): Filter by level
- `filter[category_id]` (integer, optional): Filter by category ID
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

---

### Get Course Details (Public)

**Endpoint:** `GET /courses/{slug}`

**Access:** Public

**Path Parameters:**
- `slug` (string, required): Course slug

**Response:** Returns single course with full details including units, tags, admins


---

## Units & Lessons (Student)

### List All Units (Global)

**Endpoint:** `GET /units`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `search` (string, optional): Search in title, code, description
- `filter[status]` (string, optional): Filter by status (`published`, `draft`)
- `filter[course_id]` (integer, optional): Filter by course ID

**Response:** Paginated list of units across all courses user has access to

---

### Get Unit Details (Global)

**Endpoint:** `GET /units/{slug}`

**Access:** Authenticated users (must have access to unit's course)

**Path Parameters:**
- `slug` (string, required): Unit slug

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "UNIT01",
    "slug": "introduction-unit",
    "title": "Introduction Unit",
    "description": "Getting started with the course",
    "order": 1,
    "status": "published",
    "course": {
      "id": 1,
      "title": "Introduction to Programming",
      "slug": "introduction-to-programming"
    },
    "lessons": [
      {
        "id": 1,
        "title": "First Lesson",
        "slug": "first-lesson",
        "order": 1
      }
    ]
  }
}
```

---

### List Units in Course

**Endpoint:** `GET /courses/{course_slug}/units`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[status]` (string, optional): Filter by status

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

---

### List All Lessons (Global)

**Endpoint:** `GET /lessons`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `search` (string, optional): Search in title, content
- `filter[status]` (string, optional): Filter by status
- `filter[unit_id]` (integer, optional): Filter by unit ID

**Response:** Paginated list of lessons across all courses

---

### Get Lesson Details (Global)

**Endpoint:** `GET /lessons/{slug}`

**Access:** Authenticated users (must have access)

**Path Parameters:**
- `slug` (string, required): Lesson slug

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "slug": "first-lesson",
    "title": "First Lesson",
    "content": "Lesson content in markdown",
    "order": 1,
    "status": "published",
    "is_completed": false,
    "unit": {
      "id": 1,
      "title": "Introduction Unit",
      "slug": "introduction-unit"
    },
    "blocks": [
      {
        "id": 1,
        "type": "text",
        "content": "Block content",
        "order": 1
      }
    ]
  }
}
```


---

### List Lessons in Unit

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/lessons`

**Access:** Authenticated users

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `filter[status]` (string, optional): Filter by status

**Response:** Paginated list of lessons in the unit

---

### Get Lesson Details in Course Context

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}`

**Access:** Authenticated users (must have access)

**Response:** Lesson details with completion status and blocks

---

### Mark Lesson Complete (Global)

**Endpoint:** `POST /lessons/{lesson_slug}/complete`

**Access:** Authenticated users

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Lesson marked as complete"
}
```

---

### Mark Lesson Incomplete (Global)

**Endpoint:** `DELETE /lessons/{lesson_slug}/complete`

**Access:** Authenticated users

**Request Body:** None

**Response:**
```json
{
  "success": true,
  "message": "Lesson marked as incomplete"
}
```

---

### List Lesson Blocks

**Endpoint:** `GET /courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks`

**Access:** Authenticated users

**Query Parameters:**
- `filter[type]` (string, optional): Filter by block type

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
- `filter[status]` (string, optional): Filter by status (`published`, `draft`, `archived`)
- `filter[type]` (string, optional): Filter by type (`assignment`, `quiz`)
- `filter[assignable_type]` (string, optional): Filter by scope (`Course`, `Unit`, `Lesson`)

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

**Response:** Paginated list of quizzes in the course


---

### Get Quiz Details

**Endpoint:** `GET /quizzes/{quiz_id}`

**Access:** Authenticated users (must have view permission)

**Response:**
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
    "time_limit_minutes": 30,
    "randomization_type": "random_order",
    "review_mode": "after_deadline",
    "status": "published",
    "questions_count": 10
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

**Response:**
```json
{
  "success": true,
  "message": "Quiz submission started",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "user_id": 5,
    "status": "in_progress",
    "started_at": "2024-01-15T10:00:00+00:00",
    "expires_at": "2024-01-15T10:30:00+00:00"
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

**Request Body:**
```json
{
  "question_id": 1,
  "answer": "Answer value"
}
```

**Response:** Saved answer details

---

### Submit Quiz

**Endpoint:** `POST /quiz-submissions/{submission_id}/submit`

**Access:** Authenticated users (must own submission)

**Request Body:** None (or optional answers array)

**Response:**
```json
{
  "success": true,
  "message": "Quiz submitted successfully",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "status": "submitted",
    "score": 85,
    "passed": true,
    "submitted_at": "2024-01-15T10:25:00+00:00"
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
- `filter[status]` (string, optional): Filter by status (`pending`, `active`, `completed`, `withdrawn`, `cancelled`, `declined`, `expelled`)
- `filter[course_id]` (integer, optional): Filter by course ID

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
- `filter[type]` (string, optional): Filter by type (`daily`, `weekly`, `monthly`, `special`)
- `filter[status]` (string, optional): Filter by status (`active`, `upcoming`, `expired`)

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
- `per_page` (integer, optional): Items per page (default: 10, max: 100)
- `page` (integer, optional): Page number
- `course_slug` (string, optional): Filter by course slug (global leaderboard if omitted)

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
      "level": 8
    },
    {
      "rank": 2,
      "user": {
        "id": 10,
        "name": "Jane Smith",
        "avatar_url": "https://example.com/avatar2.jpg"
      },
      "total_xp": 2300,
      "level": 7
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
      "level": 5
    }
  }
}
```

---

### Get My Rank

**Endpoint:** `GET /user/rank`

**Access:** Authenticated users

**Response:**
```json
{
  "success": true,
  "data": {
    "rank": 15,
    "total_xp": 1250,
    "level": 5,
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
type: online (nullable, string: online|hybrid|in_person)
level_tag: beginner (nullable, string: beginner|intermediate|advanced)
enrollment_type: auto_accept (nullable, string: auto_accept|approval_required|key_based)
status: draft (nullable, string: draft|published)
category_id: 1 (nullable, integer, exists:categories)
instructor_id: 2 (nullable, integer, exists:users)
admin_ids: [3,4,5] (nullable, array of user IDs)
tags: [1,2,3] (nullable, array of tag IDs)
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
- `status`: nullable, in:draft,published

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
- `order`: nullable, integer, min:1, unique within unit
- `status`: nullable, in:draft,published
- `duration_minutes`: nullable, integer, min:1

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
  "assignable_type": "Lesson",
  "assignable_slug": "lesson-slug",
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
- `assignable_type`: required, in:Course,Unit,Lesson
- `assignable_slug`: required, string (must exist)
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
- `filter[state]` (string, optional): Filter by state
- `filter[user_id]` (integer, optional): Filter by user

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
- `filters[user_id]` (integer, optional): Filter by user
- `filters[status]` (string, optional): Filter by status
- `filters[state]` (string, optional): Filter by state

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
  "assignable_type": "lesson",
  "assignable_id": 1,
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
- `assignable_type`: required, in:lesson,unit,course
- `assignable_id`: required, integer
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

**Response:** Created quiz details


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
- `filter[status]` (string, optional): Filter by status

**Response:** Paginated list of quiz submissions

---

## Grading Management

### Get Grading Queue

**Endpoint:** `GET /grading`

**Access:** Superadmin, Admin, Instructor

**Query Parameters:**
- `per_page` (integer, optional): Items per page (default: 15)
- `page` (integer, optional): Page number
- `filter[state]` (string, optional): Filter by state (`pending_grading`, `grading_in_progress`, `graded_unreleased`)
- `filter[assignment_id]` (integer, optional): Filter by assignment
- `filter[course_id]` (integer, optional): Filter by course
- `filter[user_id]` (integer, optional): Filter by student
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
- `filter[user_id]` (integer, optional): Filter by user
- `include` (string, optional): Comma-separated relations (`user`, `course`)

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

### Sorting
Use `sort` parameter:
- `sort=created_at` (ascending)
- `sort=-created_at` (descending, note the minus sign)

### Including Relations
Use `include` parameter:
- `include=user,course`
- `include=answers.question`

### Search
Use `search` parameter for full-text search:
- `search=programming`

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
