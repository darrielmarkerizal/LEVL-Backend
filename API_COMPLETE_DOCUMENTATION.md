# Complete API Documentation - Learning & Schemes Modules

**Base URL:** `/api/v1`

**Authentication:** Bearer Token (except endpoints marked as Public)

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## Table of Contents

### Schemes Module
1. [Courses API](#courses-api)
2. [Units API](#units-api)
3. [Lessons API](#lessons-api)
4. [Lesson Blocks API](#lesson-blocks-api)
5. [Lesson Completion API](#lesson-completion-api)
6. [Progress API](#progress-api)

### Learning Module
7. [Assignments API](#assignments-api)
8. [Submissions API](#submissions-api)
9. [Quizzes API](#quizzes-api)
10. [Quiz Submissions API](#quiz-submissions-api)
11. [Assessments API](#assessments-api)

---

# SCHEMES MODULE

## Courses API

### 1. List Courses
**GET** `/courses`

**Access:** Public

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | PostgreSQL FTS in title, code, short_desc |
| filter[type] | string | No | Filter: `okupasi`, `kluster` |
| filter[level_tag] | string | No | Filter: `dasar`, `menengah`, `mahir` |
| filter[status] | string | No | Filter: `published`, `draft`, `archived` |
| filter[category_id] | integer | No | Filter by category ID |
| filter[instructor_id] | integer | No | Filter by instructor ID |
| filter[enrollment_type] | string | No | Filter: `auto_accept`, `key_based`, `approval` |
| sort | string | No | Sort field: `title`, `created_at`, `published_at` (prefix with - for desc) |
| per_page | integer | No | Items per page (default: 15, max: 100) |
| page | integer | No | Page number |

### 2. Show Course
**GET** `/courses/{slug}`

**Access:** Public

### 3. Create Course
**POST** `/courses`

**Access:** Admin, Instructor, Superadmin

**Request Body (JSON):**
```json
{
  "code": "string (required, max:50, unique)",
  "title": "string (required, max:255)",
  "short_desc": "string (required)",
  "type": "okupasi|kluster (required)",
  "level_tag": "dasar|menengah|mahir (required)",
  "enrollment_type": "auto_accept|key_based|approval (required)",
  "status": "draft|published|archived (required)",
  "category_id": "integer (required, exists:categories,id)",
  "tags": ["array (optional)"],
  "outcomes": ["array (optional)"],
  "thumbnail": "file|base64 (optional)"
}
```

### 4. Update Course
**PUT** `/courses/{slug}`

**Access:** Admin, Instructor (owner), Superadmin

**Request Body:** Same as Create (all fields optional)

### 5. Delete Course
**DELETE** `/courses/{slug}`

**Access:** Admin, Instructor (owner), Superadmin

### 6. Publish Course
**PUT** `/courses/{slug}/publish`

**Access:** Admin, Instructor (owner), Superadmin

### 7. Unpublish Course
**PUT** `/courses/{slug}/unpublish`

**Access:** Admin, Instructor (owner), Superadmin

### 8. Generate Enrollment Key
**POST** `/courses/{slug}/enrollment-key/generate`

**Access:** Admin, Instructor (owner), Superadmin

### 9. Update Enrollment Key
**PUT** `/courses/{slug}/enrollment-key`

**Access:** Admin, Instructor (owner), Superadmin

**Request Body:**
```json
{
  "enrollment_key": "string (required, max:50)"
}
```

### 10. Remove Enrollment Key
**DELETE** `/courses/{slug}/enrollment-key`

**Access:** Admin, Instructor (owner), Superadmin

---

## Units API

### 1. List All Units (Global)
**GET** `/units`

**Access:** Authenticated

**Authorization:**
- Superadmin: Can see all units
- Admin/Instructor: Only units from courses they manage
- Student: All published units

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | PostgreSQL FTS in title, description, slug |
| filter[status] | string | No | Filter: `published`, `draft` |
| filter[course_slug] | string | No | Filter by course slug |
| sort | string | No | Sort: `order`, `title`, `created_at` (prefix with - for desc) |
| per_page | integer | No | Items per page |
| page | integer | No | Page number |
| include | string | No | Relations: `course`, `lessons` |

### 2. Show Unit (Global)
**GET** `/units/{unit_slug}`

**Access:** Authenticated

**Description:** Show unit details without course context

### 3. List Units
**GET** `/courses/{course_slug}/units`

**Access:** Authenticated

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| filter[status] | string | No | Filter: `published`, `draft` |
| per_page | integer | No | Items per page |
| page | integer | No | Page number |

### 2. Show Unit
**GET** `/courses/{course_slug}/units/{unit_slug}`

**Access:** Authenticated

### 3. Get Unit Contents (Mixed)
**GET** `/courses/{course_slug}/units/{unit_slug}/contents`

**Access:** Authenticated

**Description:** Returns flat list of lessons, assignments, quizzes sorted by order

### 4. Create Unit
**POST** `/courses/{course_slug}/units`

**Access:** Admin, Instructor, Superadmin

**Request Body:**
```json
{
  "code": "string (required, max:50, unique)",
  "title": "string (required, max:255)",
  "description": "string (optional)",
  "order": "integer (required, min:1)",
  "status": "draft|published (required)"
}
```

### 5. Update Unit
**PUT** `/courses/{course_slug}/units/{unit_slug}`

**Access:** Admin, Instructor, Superadmin

**Request Body:** Same as Create (all fields optional)

### 6. Delete Unit
**DELETE** `/courses/{course_slug}/units/{unit_slug}`

**Access:** Admin, Instructor, Superadmin

### 7. Reorder Units
**PUT** `/courses/{course_slug}/units/reorder`

**Access:** Admin, Instructor, Superadmin

**Request Body:**
```json
{
  "units": [
    {"id": 1, "order": 1},
    {"id": 2, "order": 2}
  ]
}
```

### 8. Publish Unit
**PUT** `/courses/{course_slug}/units/{unit_slug}/publish`

**Access:** Admin, Instructor, Superadmin

### 9. Unpublish Unit
**PUT** `/courses/{course_slug}/units/{unit_slug}/unpublish`

**Access:** Admin, Instructor, Superadmin

---

## Lessons API

### 1. List All Lessons (Global)
**GET** `/lessons`

**Access:** Authenticated

**Authorization:**
- Superadmin: Can see all lessons
- Admin/Instructor: Only lessons from courses they manage
- Student: All published lessons

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | PostgreSQL FTS in title, description, markdown_content, slug |
| filter[status] | string | No | Filter: `published`, `draft` |
| filter[content_type] | string | No | Filter: `markdown`, `video`, `link` |
| filter[unit_slug] | string | No | Filter by unit slug |
| filter[course_slug] | string | No | Filter by course slug |
| sort | string | No | Sort: `order`, `title`, `created_at` (prefix with - for desc) |
| per_page | integer | No | Items per page |
| page | integer | No | Page number |
| include | string | No | Relations: `unit`, `unit.course`, `blocks` |

### 2. Show Lesson (Global)
**GET** `/lessons/{lesson_slug}`

**Access:** Authenticated

**Description:** Show lesson details without course/unit context

### 3. List Lessons
**GET** `/courses/{course_slug}/units/{unit_slug}/lessons`

**Access:** Authenticated

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| filter[status] | string | No | Filter: `published`, `draft` |
| filter[content_type] | string | No | Filter: `markdown`, `video`, `link` |
| per_page | integer | No | Items per page |
| page | integer | No | Page number |

### 2. Show Lesson
**GET** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}`

**Access:** Authenticated

### 3. Create Lesson
**POST** `/courses/{course_slug}/units/{unit_slug}/lessons`

**Access:** Admin, Instructor, Superadmin

**Request Body:**
```json
{
  "title": "string (required, max:255)",
  "description": "string (optional)",
  "markdown_content": "string (optional)",
  "content_type": "markdown|video|link (required)",
  "content_url": "url (optional)",
  "order": "integer (required, min:1)",
  "duration_minutes": "integer (optional, min:1)",
  "status": "draft|published (required)"
}
```

### 4. Update Lesson
**PUT** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}`

**Access:** Admin, Instructor, Superadmin

**Request Body:** Same as Create (all fields optional)

### 5. Delete Lesson
**DELETE** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}`

**Access:** Admin, Instructor, Superadmin

### 6. Publish Lesson
**PUT** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/publish`

**Access:** Admin, Instructor, Superadmin

### 7. Unpublish Lesson
**PUT** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/unpublish`

**Access:** Admin, Instructor, Superadmin

---

## Lesson Blocks API

### 1. List Lesson Blocks
**GET** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks`

**Access:** Authenticated

### 2. Show Lesson Block
**GET** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}`

**Access:** Authenticated

### 3. Create Lesson Block
**POST** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks`

**Access:** Admin, Instructor, Superadmin

**Request Body:**
```json
{
  "block_type": "text|video|file|image|embed (required)",
  "content": "string (required)",
  "order": "integer (required, min:1)",
  "media_file": "file (optional)"
}
```

**Form Data (Multipart):**
```
block_type: text|video|file|image|embed
content: string
order: integer
media_file: file
```

### 4. Update Lesson Block
**PUT** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}`

**Access:** Admin, Instructor, Superadmin

**Request Body:** Same as Create (all fields optional)

### 5. Delete Lesson Block
**DELETE** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}`

**Access:** Admin, Instructor, Superadmin

---

## Lesson Completion API

### 1. Mark Lesson Complete
**POST** `/lessons/{lesson_slug}/complete`

**Access:** Authenticated (Students)

### 2. Mark Lesson Incomplete
**DELETE** `/lessons/{lesson_slug}/complete`

**Access:** Authenticated (Students)

---

## Progress API

### 1. Get Course Progress
**GET** `/courses/{course_slug}/progress`

**Access:** Authenticated

### 2. Complete Lesson (Legacy)
**POST** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/complete`

**Access:** Authenticated

**Note:** Use `/lessons/{lesson_slug}/complete` instead

### 3. Uncomplete Lesson (Legacy)
**POST** `/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/uncomplete`

**Access:** Authenticated

**Note:** Use `DELETE /lessons/{lesson_slug}/complete` instead

---

# LEARNING MODULE

## Assignments API

### 1. List Course Assignments
**GET** `/courses/{course_slug}/assignments`

**Access:** Authenticated

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| filter[status] | string | No | Filter: `published`, `draft`, `archived` |
| filter[type] | string | No | Filter: `assignment`, `project`, `practice` |
| filter[lesson_id] | integer | No | Filter by lesson |
| per_page | integer | No | Items per page |
| page | integer | No | Page number |

### 2. List Incomplete Assignments
**GET** `/courses/{course_slug}/assignments/incomplete`

**Access:** Authenticated

**Query Parameters:** Same as List Assignments

### 3. Show Assignment
**GET** `/assignments/{assignment}`

**Access:** Authenticated

### 4. Check Prerequisites
**GET** `/assignments/{assignment}/prerequisites/check`

**Access:** Authenticated

### 5. Check Deadline
**GET** `/assignments/{assignment}/deadline/check`

**Access:** Authenticated

### 6. Check Attempts
**GET** `/assignments/{assignment}/attempts/check`

**Access:** Authenticated

### 7. My Submissions
**GET** `/assignments/{assignment}/submissions/me`

**Access:** Authenticated

### 8. Highest Submission
**GET** `/assignments/{assignment}/submissions/highest`

**Access:** Authenticated

### 9. Show Submission Detail
**GET** `/assignments/{assignment}/submissions/{submission}`

**Access:** Authenticated

### 10. Create Assignment
**POST** `/assignments`

**Access:** Admin, Instructor, Superadmin

**Request Body:**
```json
{
  "lesson_id": "integer (optional)",
  "assignable_type": "string (required)",
  "assignable_id": "integer (required)",
  "title": "string (required, max:255)",
  "description": "string (optional)",
  "type": "assignment|project|practice (required)",
  "submission_type": "file|text|both (required)",
  "max_score": "integer (required, min:1)",
  "max_attempts": "integer (optional, min:1)",
  "cooldown_minutes": "integer (optional, min:0)",
  "retake_enabled": "boolean (optional)",
  "review_mode": "immediate|after_deadline|manual (required)",
  "randomization_type": "static|dynamic (required)",
  "status": "draft|published|archived (required)",
  "allow_resubmit": "boolean (optional)"
}
```

### 11. Update Assignment
**PUT** `/assignments/{assignment}`

**Access:** Admin, Instructor (owner), Superadmin

**Request Body:** Same as Create (all fields optional)

### 12. Delete Assignment
**DELETE** `/assignments/{assignment}`

**Access:** Admin, Instructor (owner), Superadmin

### 13. Publish Assignment
**PUT** `/assignments/{assignment}/publish`

**Access:** Admin, Instructor (owner), Superadmin

### 14. Unpublish Assignment
**PUT** `/assignments/{assignment}/unpublish`

**Access:** Admin, Instructor (owner), Superadmin

### 15. Archive Assignment
**PUT** `/assignments/{assignment}/archived`

**Access:** Admin, Instructor (owner), Superadmin

### 16. List Overrides
**GET** `/assignments/{assignment}/overrides`

**Access:** Admin, Instructor, Superadmin

### 17. Grant Override
**POST** `/assignments/{assignment}/overrides`

**Access:** Admin, Instructor, Superadmin

**Request Body:**
```json
{
  "user_id": "integer (required, exists:users,id)",
  "type": "deadline|attempts|prerequisite (required)",
  "value": {
    "additional_attempts": "integer (optional)",
    "extended_deadline": "datetime (optional)",
    "bypassed_prerequisites": ["array of assignment IDs (optional)"],
    "expires_at": "datetime (optional)"
  },
  "reason": "string (optional)"
}
```

### 18. Duplicate Assignment
**POST** `/assignments/{assignment}/duplicate`

**Access:** Admin, Instructor (owner), Superadmin

**Request Body:**
```json
{
  "title": "string (optional)",
  "assignable_type": "string (optional)",
  "assignable_id": "integer (optional)"
}
```

### 19. List All Submissions
**GET** `/assignments/{assignment}/submissions`

**Access:** Admin, Instructor, Superadmin

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| filter[user_id] | integer | No | Filter by user |
| filter[status] | string | No | Filter: `draft`, `submitted`, `graded` |
| filter[state] | string | No | Filter: `pending_manual_grading`, `graded`, `released` |
| per_page | integer | No | Items per page |
| page | integer | No | Page number |

### 20. Search Submissions
**GET** `/submissions/search`

**Access:** Admin, Instructor, Superadmin

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | Search in user name, assignment title |
| filter[assignment_id] | integer | No | Filter by assignment |
| filter[user_id] | integer | No | Filter by user |
| filter[status] | string | No | Filter by status |
| per_page | integer | No | Items per page |

---

## Submissions API

### 1. Create Submission
**POST** `/assignments/{assignment}/submissions`

**Access:** Authenticated (Students)

**Request Body:**
```json
{
  "answers": [
    {
      "question_id": "integer (required)",
      "content": "string (optional)",
      "selected_options": ["array (optional)"],
      "files": ["array of files (optional)"]
    }
  ]
}
```

**Form Data (Multipart):**
```
answers[0][question_id]: integer
answers[0][content]: string
answers[0][files][]: file
```

### 2. Start Submission
**POST** `/assignments/{assignment}/submissions/start`

**Access:** Authenticated (Students)

### 3. List Questions
**GET** `/submissions/{submission}/questions`

**Access:** Authenticated

### 4. Update Submission
**PUT** `/submissions/{submission}`

**Access:** Authenticated (owner)

**Request Body:**
```json
{
  "answers": [
    {
      "question_id": "integer",
      "content": "string",
      "files": ["files"]
    }
  ]
}
```

### 5. Save Answer
**POST** `/submissions/{submission}/answers`

**Access:** Authenticated (owner)

**Request Body:**
```json
{
  "question_id": "integer (required)",
  "content": "string (optional)",
  "selected_options": ["array (optional)"],
  "files": ["array of files (optional)"]
}
```

### 6. Submit Submission
**POST** `/submissions/{submission}/submit`

**Access:** Authenticated (owner)

### 7. Grade Submission
**POST** `/submissions/{submission}/grade`

**Access:** Admin, Instructor, Superadmin

**Request Body:**
```json
{
  "answers": [
    {
      "answer_id": "integer (required)",
      "score": "numeric (required, min:0)",
      "feedback": "string (optional)"
    }
  ],
  "overall_feedback": "string (optional)",
  "is_draft": "boolean (optional)",
  "release_immediately": "boolean (optional)"
}
```

---

## Quizzes API

### 1. List Course Quizzes
**GET** `/courses/{course_slug}/quizzes`

**Access:** Authenticated

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| filter[status] | string | No | Filter: `published`, `draft`, `archived` |
| filter[lesson_id] | integer | No | Filter by lesson |
| per_page | integer | No | Items per page |
| page | integer | No | Page number |

### 2. Show Quiz
**GET** `/quizzes/{quiz}`

**Access:** Authenticated

### 3. List Quiz Questions
**GET** `/quizzes/{quiz}/questions`

**Access:** Authenticated

### 4. My Quiz Submissions
**GET** `/quizzes/{quiz}/submissions/me`

**Access:** Authenticated

### 5. Highest Quiz Submission
**GET** `/quizzes/{quiz}/submissions/highest`

**Access:** Authenticated

### 6. Start Quiz Submission
**POST** `/quizzes/{quiz}/submissions/start`

**Access:** Authenticated

### 7. Create Quiz
**POST** `/quizzes`

**Access:** Admin, Instructor, Superadmin

**Request Body:**
```json
{
  "assignable_type": "string (required)",
  "assignable_id": "integer (required)",
  "lesson_id": "integer (optional)",
  "title": "string (required, max:255)",
  "description": "string (optional)",
  "passing_grade": "integer (required, min:0, max:100)",
  "auto_grading": "boolean (required)",
  "max_score": "integer (required, min:1)",
  "max_attempts": "integer (optional, min:1)",
  "cooldown_minutes": "integer (optional, min:0)",
  "time_limit_minutes": "integer (optional, min:1)",
  "retake_enabled": "boolean (optional)",
  "randomization_type": "static|dynamic (required)",
  "review_mode": "immediate|after_deadline|manual (required)",
  "status": "draft|published|archived (required)"
}
```

### 8. Update Quiz
**PUT** `/quizzes/{quiz}`

**Access:** Admin, Instructor (owner), Superadmin

**Request Body:** Same as Create (all fields optional)

### 9. Delete Quiz
**DELETE** `/quizzes/{quiz}`

**Access:** Admin, Instructor (owner), Superadmin

### 10. Publish Quiz
**PUT** `/quizzes/{quiz}/publish`

**Access:** Admin, Instructor (owner), Superadmin

### 11. Unpublish Quiz
**PUT** `/quizzes/{quiz}/unpublish`

**Access:** Admin, Instructor (owner), Superadmin

### 12. Archive Quiz
**PUT** `/quizzes/{quiz}/archived`

**Access:** Admin, Instructor (owner), Superadmin

### 13. Show Quiz Question
**GET** `/quizzes/{quiz}/questions/{question}`

**Access:** Admin, Instructor, Superadmin

### 14. Add Quiz Question
**POST** `/quizzes/{quiz}/questions`

**Access:** Admin, Instructor (owner), Superadmin

**Request Body:**
```json
{
  "type": "multiple_choice|true_false|checkbox|essay (required)",
  "content": "string (required)",
  "weight": "numeric (required, min:0)",
  "order": "integer (required, min:1)",
  "max_score": "numeric (required, min:0)",
  "options": ["array (required for multiple_choice, true_false, checkbox)"],
  "answer_key": ["array (required for multiple_choice, true_false, checkbox)"]
}
```

### 15. Update Quiz Question
**PUT** `/quizzes/{quiz}/questions/{question}`

**Access:** Admin, Instructor (owner), Superadmin

**Request Body:** Same as Add Question (all fields optional)

### 16. Delete Quiz Question
**DELETE** `/quizzes/{quiz}/questions/{question}`

**Access:** Admin, Instructor (owner), Superadmin

### 17. Reorder Quiz Questions
**POST** `/quizzes/{quiz}/questions/reorder`

**Access:** Admin, Instructor (owner), Superadmin

**Request Body:**
```json
{
  "questions": [
    {"id": 1, "order": 1},
    {"id": 2, "order": 2}
  ]
}
```

### 18. List Quiz Submissions
**GET** `/quizzes/{quiz}/submissions`

**Access:** Admin, Instructor, Superadmin

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| filter[user_id] | integer | No | Filter by user |
| filter[status] | string | No | Filter: `in_progress`, `submitted`, `graded` |
| per_page | integer | No | Items per page |

---

## Quiz Submissions API

### 1. List Quiz Submission Questions
**GET** `/quiz-submissions/{submission}/questions`

**Access:** Authenticated (owner)

### 2. Get Question at Order
**GET** `/quiz-submissions/{submission}/questions/{order}`

**Access:** Authenticated (owner)

**Description:** Returns single question by order index with navigation metadata

### 3. Save Quiz Answer
**POST** `/quiz-submissions/{submission}/answers`

**Access:** Authenticated (owner)

**Request Body:**
```json
{
  "quiz_question_id": "integer (required)",
  "content": "string (optional, for essay)",
  "selected_options": ["array (optional, for multiple_choice/checkbox/true_false)"]
}
```

### 4. Submit Quiz
**POST** `/quiz-submissions/{submission}/submit`

**Access:** Authenticated (owner)

### 5. Show Quiz Submission
**GET** `/quiz-submissions/{submission}`

**Access:** Authenticated (owner)

---

## Assessments API

### 1. List Mixed Assessments
**GET** `/courses/{course_slug}/assessments`

**Access:** Admin, Instructor, Superadmin

**Description:** Returns combined list of quizzes and assignments

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| filter[type] | string | No | Filter: `quiz`, `assignment` |
| filter[status] | string | No | Filter: `published`, `draft`, `archived` |
| per_page | integer | No | Items per page |
| page | integer | No | Page number |

---

## Error Responses

All endpoints return standard error responses:

**400 Bad Request** - Validation errors
**401 Unauthorized** - Missing or invalid token
**403 Forbidden** - Insufficient permissions
**404 Not Found** - Resource not found
**422 Unprocessable Entity** - Business logic errors
**500 Internal Server Error** - Server errors

---

## Rate Limiting

- **Authenticated:** 60 requests/minute
- **Unauthenticated:** 30 requests/minute

**Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1709308800
```

---

## Pagination

All list endpoints support pagination:

**Query Parameters:**
- `per_page`: Items per page (default: 15, max: 100)
- `page`: Page number (default: 1)

**Meta Response:**
```json
{
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 67
  }
}
```

---

## Sorting

Endpoints with sorting support:

**Query Parameters:**
- `sort`: Field name (e.g., `title`, `created_at`, `-created_at` for descending)
- Prefix with `-` for descending order (e.g., `sort=-created_at`)
- Default order is ascending unless prefixed with `-`

---

## File Uploads

**Supported Formats:**
- Images: jpg, jpeg, png, gif, webp
- Documents: pdf, doc, docx, xls, xlsx
- Videos: mp4, webm, mov
- Archives: zip, rar

**Max File Size:** 10MB (configurable)

**Upload Methods:**
1. **Multipart Form Data** (recommended for files)
2. **Base64 Encoded** (for small files in JSON)

---

## Response Format

All responses follow this structure:

```json
{
  "success": true|false,
  "message": "string",
  "data": {},
  "meta": {},
  "errors": {}
}
```
