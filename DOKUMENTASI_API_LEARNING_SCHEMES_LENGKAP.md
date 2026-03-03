# DOKUMENTASI API LENGKAP - MODUL LEARNING & SCHEMES

## Daftar Isi

1. [Informasi Umum](#informasi-umum)
2. [Autentikasi](#autentikasi)
3. [Format Response](#format-response)
4. [Enum Values](#enum-values)
5. [API Schemes Module - Student](#api-schemes-student)
6. [API Schemes Module - Manajemen](#api-schemes-manajemen)
7. [API Learning Module - Student](#api-learning-student)
8. [API Learning Module - Manajemen](#api-learning-manajemen)

---

## Informasi Umum

### Base URL
```
https://your-domain.com/api/v1
```

### Headers yang Diperlukan
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

### Pagination
Semua endpoint yang mengembalikan list data mendukung pagination dengan parameter:
- `page`: Nomor halaman (default: 1)
- `per_page`: Jumlah item per halaman (default: 15, max: 100)

### Filtering & Sorting
Parameter umum untuk filtering:
- `filter[status]`: Filter berdasarkan status
- `filter[search]`: Pencarian teks
- `sort`: Field untuk sorting (prefix `-` untuk descending)
- `include`: Relasi yang ingin di-load (comma-separated)

---

## Autentikasi

Semua endpoint (kecuali public course list) memerlukan autentikasi menggunakan Bearer Token.

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```


---

## Format Response

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Data retrieved",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  },
  "links": {
    "first": "https://api.example.com/endpoint?page=1",
    "last": "https://api.example.com/endpoint?page=5",
    "prev": null,
    "next": "https://api.example.com/endpoint?page=2"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error detail"]
  }
}
```

---

## Enum Values

### CourseStatus
- `draft`: Draft (belum dipublikasi)
- `published`: Published (sudah dipublikasi)
- `archived`: Archived (diarsipkan)

### CourseType
- `free`: Gratis
- `paid`: Berbayar

### EnrollmentType
- `auto_accept`: Otomatis diterima
- `key_based`: Memerlukan kunci enrollment
- `approval`: Memerlukan persetujuan admin

### LevelTag
- `beginner`: Pemula
- `intermediate`: Menengah
- `advanced`: Lanjutan

### AssignmentStatus / QuizStatus
- `draft`: Draft
- `published`: Published
- `archived`: Archived

### SubmissionStatus
- `draft`: Draft (belum submit)
- `submitted`: Submitted (menunggu penilaian)
- `graded`: Graded (sudah dinilai)
- `missing`: Missing (tidak dikerjakan)

### QuizSubmissionStatus
- `in_progress`: Sedang dikerjakan
- `submitted`: Sudah submit
- `graded`: Sudah dinilai

### SubmissionType
- `text`: Jawaban teks
- `file`: Upload file
- `mixed`: Kombinasi teks dan file

### QuestionType / QuizQuestionType
- `multiple_choice`: Pilihan ganda (satu jawaban)
- `checkbox`: Checkbox (multiple jawaban)
- `essay`: Essay
- `file_upload`: Upload file

### ReviewMode
- `immediate`: Langsung tampil setelah submit
- `manual`: Manual review oleh instruktur
- `deferred`: Ditunda (tampil setelah deadline)
- `hidden`: Disembunyikan

### RandomizationType
- `static`: Urutan tetap
- `random_order`: Urutan acak
- `bank`: Random dari bank soal



---

# API SCHEMES MODULE - STUDENT

## 1. Courses (Public & Enrolled)

### 1.1 List All Courses (Public)
```http
GET /api/v1/courses
```

**Query Parameters:**
- `page`: integer (default: 1)
- `per_page`: integer (default: 15, max: 100)
- `filter[status]`: string (`published`, `draft`, `archived`)
- `filter[type]`: string (`free`, `paid`)
- `filter[level_tag]`: string (`beginner`, `intermediate`, `advanced`)
- `filter[search]`: string (cari di title, code, short_desc)
- `filter[category_id]`: integer
- `sort`: string (contoh: `title`, `-created_at`)
- `include`: string (contoh: `category,instructor,tags`)

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
      "type": "free",
      "level_tag": "beginner",
      "enrollment_type": "auto_accept",
      "status": "published",
      "enrollment_status": null,
      "published_at": "2024-01-15T10:00:00Z",
      "created_at": "2024-01-10T08:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z",
      "thumbnail": "https://example.com/storage/thumbnails/course-1.jpg",
      "banner": "https://example.com/storage/banners/course-1.jpg",
      "category": {
        "id": 1,
        "name": "Programming"
      },
      "instructor": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "creator": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "admins_count": 2,
      "enrollments_count": 150
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

### 1.2 Get Course Detail (Public)
```http
GET /api/v1/courses/{slug}
```

**Path Parameters:**
- `slug`: string (course slug)

**Query Parameters:**
- `include`: string (contoh: `units,lessons,quizzes,assignments,tags,admins`)

**Response:**
```json
{
  "success": true,
  "message": "Course retrieved successfully",
  "data": {
    "id": 1,
    "code": "CS101",
    "slug": "introduction-to-programming",
    "title": "Introduction to Programming",
    "short_desc": "Learn programming basics",
    "type": "free",
    "level_tag": "beginner",
    "enrollment_type": "auto_accept",
    "status": "published",
    "enrollment_status": "active",
    "published_at": "2024-01-15T10:00:00Z",
    "created_at": "2024-01-10T08:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z",
    "thumbnail": "https://example.com/storage/thumbnails/course-1.jpg",
    "banner": "https://example.com/storage/banners/course-1.jpg",
    "category": {
      "id": 1,
      "name": "Programming"
    },
    "instructor": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "units": [
      {
        "id": 1,
        "course_id": 1,
        "code": "UNIT-01",
        "slug": "getting-started",
        "title": "Getting Started",
        "description": "Introduction to the course",
        "order": 1,
        "status": "published",
        "created_at": "2024-01-10T09:00:00Z",
        "updated_at": "2024-01-15T10:00:00Z"
      }
    ]
  }
}
```

### 1.3 List My Enrolled Courses
```http
GET /api/v1/my-courses
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`: integer (default: 1)
- `per_page`: integer (default: 15)
- `filter[status]`: string (`active`, `completed`, `dropped`)
- `filter[search]`: string
- `sort`: string

**Response:**
```json
{
  "success": true,
  "message": "Enrolled courses retrieved successfully",
  "data": [
    {
      "id": 1,
      "code": "CS101",
      "slug": "introduction-to-programming",
      "title": "Introduction to Programming",
      "enrollment_status": "active",
      "progress_percentage": 45.5,
      "enrolled_at": "2024-02-01T10:00:00Z"
    }
  ],
  "meta": { ... }
}
```



## 2. Units

### 2.1 List All Units (Global)
```http
GET /api/v1/units
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`: integer
- `per_page`: integer
- `filter[status]`: string (`draft`, `published`)
- `filter[course_id]`: integer
- `filter[search]`: string
- `include`: string (contoh: `course,lessons`)

**Response:**
```json
{
  "success": true,
  "message": "Units retrieved successfully",
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "code": "UNIT-01",
      "slug": "getting-started",
      "title": "Getting Started",
      "description": "Introduction to the course",
      "order": 1,
      "status": "published",
      "created_at": "2024-01-10T09:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z",
      "lessons": [
        {
          "id": 1,
          "unit_id": 1,
          "code": "LESSON-01",
          "slug": "welcome",
          "title": "Welcome to the Course",
          "order": 1,
          "status": "published",
          "is_locked": false,
          "is_completed": false
        }
      ]
    }
  ],
  "meta": { ... }
}
```

### 2.2 Get Unit Detail (Global)
```http
GET /api/v1/units/{slug}
Authorization: Bearer {token}
```

**Path Parameters:**
- `slug`: string (unit slug)

**Query Parameters:**
- `include`: string (contoh: `course,lessons`)

**Response:**
```json
{
  "success": true,
  "message": "Unit retrieved successfully",
  "data": {
    "id": 1,
    "course_id": 1,
    "code": "UNIT-01",
    "slug": "getting-started",
    "title": "Getting Started",
    "description": "Introduction to the course",
    "order": 1,
    "status": "published",
    "created_at": "2024-01-10T09:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z",
    "lessons": [ ... ]
  }
}
```

### 2.3 List Units in Course
```http
GET /api/v1/courses/{course_slug}/units
Authorization: Bearer {token}
```

**Path Parameters:**
- `course_slug`: string

**Query Parameters:**
- `page`: integer
- `per_page`: integer
- `filter[status]`: string

**Response:**
```json
{
  "success": true,
  "message": "Units retrieved successfully",
  "data": [ ... ],
  "meta": { ... }
}
```

### 2.4 Get Unit Detail in Course
```http
GET /api/v1/courses/{course_slug}/units/{unit_slug}
Authorization: Bearer {token}
```

**Path Parameters:**
- `course_slug`: string
- `unit_slug`: string

**Query Parameters:**
- `include`: string

**Response:**
```json
{
  "success": true,
  "message": "Unit retrieved successfully",
  "data": { ... }
}
```

### 2.5 Get Unit Contents (Lessons + Assessments)
```http
GET /api/v1/courses/{course_slug}/units/{unit_slug}/contents
Authorization: Bearer {token}
```

**Path Parameters:**
- `course_slug`: string
- `unit_slug`: string

**Response:**
```json
{
  "success": true,
  "message": "Unit contents retrieved successfully",
  "data": {
    "lessons": [
      {
        "id": 1,
        "type": "lesson",
        "title": "Welcome to the Course",
        "slug": "welcome",
        "order": 1,
        "is_locked": false,
        "is_completed": true
      }
    ],
    "assignments": [
      {
        "id": 1,
        "type": "assignment",
        "title": "First Assignment",
        "order": 2,
        "is_locked": false,
        "submission_status": "graded",
        "highest_score": 85
      }
    ],
    "quizzes": [
      {
        "id": 1,
        "type": "quiz",
        "title": "Quiz 1",
        "order": 3,
        "is_locked": false,
        "submission_status": "graded",
        "highest_score": 90
      }
    ]
  }
}
```



## 3. Lessons

### 3.1 List All Lessons (Global)
```http
GET /api/v1/lessons
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`, `per_page`, `filter[status]`, `filter[unit_id]`, `filter[search]`, `include`

**Response:** Similar to units list

### 3.2 Get Lesson Detail (Global)
```http
GET /api/v1/lessons/{slug}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "unit_id": 1,
    "code": "LESSON-01",
    "slug": "welcome",
    "title": "Welcome to the Course",
    "content": "Markdown content here...",
    "order": 1,
    "status": "published",
    "is_locked": false,
    "is_completed": true,
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

### 3.3 List Lessons in Unit
```http
GET /api/v1/courses/{course_slug}/units/{unit_slug}/lessons
Authorization: Bearer {token}
```

### 3.4 Get Lesson Detail in Unit
```http
GET /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}
Authorization: Bearer {token}
```

### 3.5 Mark Lesson as Complete
```http
POST /api/v1/lessons/{lesson_slug}/complete
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Lesson marked as complete",
  "data": {
    "lesson_id": 1,
    "user_id": 10,
    "completed_at": "2024-03-03T14:30:00Z"
  }
}
```

### 3.6 Mark Lesson as Incomplete
```http
DELETE /api/v1/lessons/{lesson_slug}/complete
Authorization: Bearer {token}
```



## 4. Lesson Blocks

### 4.1 List Lesson Blocks
```http
GET /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "lesson_id": 1,
      "slug": "intro-text",
      "type": "text",
      "content": "Welcome to this lesson...",
      "order": 1,
      "media_url": null
    },
    {
      "id": 2,
      "lesson_id": 1,
      "slug": "video-tutorial",
      "type": "video",
      "content": null,
      "order": 2,
      "media_url": "https://example.com/videos/tutorial.mp4"
    }
  ]
}
```

### 4.2 Get Block Detail
```http
GET /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}
Authorization: Bearer {token}
```

## 5. Progress

### 5.1 Get Course Progress
```http
GET /api/v1/courses/{course_slug}/progress
Authorization: Bearer {token}
```

**Query Parameters:**
- `user_id`: integer (optional, untuk admin/instructor melihat progress student lain)

**Response:**
```json
{
  "success": true,
  "data": {
    "course_id": 1,
    "user_id": 10,
    "progress_percentage": 65.5,
    "completed_lessons": 15,
    "total_lessons": 25,
    "completed_assignments": 3,
    "total_assignments": 5,
    "completed_quizzes": 2,
    "total_quizzes": 3,
    "units_progress": [
      {
        "unit_id": 1,
        "unit_title": "Getting Started",
        "progress_percentage": 100,
        "is_unlocked": true,
        "completed_lessons": 5,
        "total_lessons": 5
      },
      {
        "unit_id": 2,
        "unit_title": "Advanced Topics",
        "progress_percentage": 40,
        "is_unlocked": true,
        "completed_lessons": 2,
        "total_lessons": 5
      }
    ]
  }
}
```



---

# API LEARNING MODULE - STUDENT

## 6. Assignments

### 6.1 List Assignments in Course
```http
GET /api/v1/courses/{course_slug}/assignments
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`, `per_page`
- `filter[status]`: string (`draft`, `published`, `archived`)
- `filter[unit_id]`: integer
- `filter[submission_type]`: string (`text`, `file`, `mixed`)
- `include`: string (contoh: `unit,questions,submissions`)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "First Assignment",
      "description": "Complete the following tasks...",
      "submission_type": "file",
      "max_score": 100,
      "passing_grade": 60,
      "review_mode": "manual",
      "unit_slug": "getting-started",
      "course_slug": "introduction-to-programming",
      "is_locked": false,
      "submission_status": "graded",
      "highest_score": 85,
      "attempt_count": 2,
      "attachments": [
        {
          "id": 1,
          "file_name": "assignment-instructions.pdf",
          "url": "https://example.com/storage/assignments/file.pdf",
          "mime_type": "application/pdf",
          "size": 245678
        }
      ],
      "created_at": "2024-01-15T10:00:00Z"
    }
  ],
  "meta": { ... }
}
```

### 6.2 List Incomplete Assignments
```http
GET /api/v1/courses/{course_slug}/assignments/incomplete
Authorization: Bearer {token}
```

**Response:** Similar to 6.1, hanya assignment yang belum selesai

### 6.3 Get Assignment Detail
```http
GET /api/v1/assignments/{assignment_id}
Authorization: Bearer {token}
```

**Query Parameters:**
- `include`: string (contoh: `questions,submissions,prerequisites`)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "First Assignment",
    "description": "Complete the following tasks...",
    "submission_type": "file",
    "max_score": 100,
    "passing_grade": 60,
    "review_mode": "manual",
    "unit_slug": "getting-started",
    "course_slug": "introduction-to-programming",
    "is_locked": false,
    "attachments": [ ... ],
    "created_at": "2024-01-15T10:00:00Z"
  }
}
```

### 6.4 Check Assignment Prerequisites
```http
GET /api/v1/assignments/{assignment_id}/prerequisites/check
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "can_access": true,
    "is_locked": false,
    "missing_prerequisites": [],
    "message": "You can access this assignment"
  }
}
```

Atau jika belum memenuhi:
```json
{
  "success": true,
  "data": {
    "can_access": false,
    "is_locked": true,
    "missing_prerequisites": [
      {
        "type": "unit",
        "title": "Unit 1: Getting Started",
        "reason": "Unit must be 100% complete"
      }
    ],
    "message": "Prerequisites not met"
  }
}
```



## 7. Submissions (Assignment)

### 7.1 Start New Submission
```http
POST /api/v1/assignments/{assignment_id}/submissions
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:** (empty atau optional metadata)
```json
{}
```

**Response:**
```json
{
  "success": true,
  "message": "Submission started successfully",
  "data": {
    "id": 101,
    "assignment_id": 1,
    "user_id": 10,
    "status": "draft",
    "attempt_number": 1,
    "score": null,
    "created_at": "2024-03-03T14:00:00Z"
  }
}
```

**Error Response (jika ada draft atau pending grading):**
```json
{
  "success": false,
  "message": "Cannot start new submission",
  "errors": {
    "submission": ["You have a draft submission. Please complete or delete it first."]
  }
}
```

### 7.2 List My Submissions for Assignment
```http
GET /api/v1/assignments/{assignment_id}/submissions
Authorization: Bearer {token}
```

**Response (untuk Student):**
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "assignment_id": 1,
      "attempt_number": 1,
      "status": "graded",
      "score": 85,
      "max_score": 100,
      "is_highest": true,
      "submitted_at": "2024-03-01T15:30:00Z",
      "graded_at": "2024-03-02T10:00:00Z"
    },
    {
      "id": 105,
      "assignment_id": 1,
      "attempt_number": 2,
      "status": "graded",
      "score": 75,
      "max_score": 100,
      "is_highest": false,
      "submitted_at": "2024-03-03T14:00:00Z",
      "graded_at": "2024-03-03T16:00:00Z"
    }
  ]
}
```

### 7.3 Get Highest Submission
```http
GET /api/v1/assignments/{assignment_id}/submissions/highest
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 101,
    "assignment_id": 1,
    "attempt_number": 1,
    "status": "graded",
    "score": 85,
    "max_score": 100,
    "feedback": "Good work!",
    "submitted_at": "2024-03-01T15:30:00Z",
    "graded_at": "2024-03-02T10:00:00Z"
  }
}
```

### 7.4 Get Submission Detail
```http
GET /api/v1/assignments/{assignment_id}/submissions/{submission_id}
Authorization: Bearer {token}
```

**Query Parameters:**
- `include`: string (contoh: `answers,questions`)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 101,
    "assignment_id": 1,
    "user_id": 10,
    "attempt_number": 1,
    "status": "graded",
    "score": 85,
    "max_score": 100,
    "feedback": "Good work! Minor improvements needed.",
    "submitted_at": "2024-03-01T15:30:00Z",
    "graded_at": "2024-03-02T10:00:00Z",
    "graded_by": {
      "id": 5,
      "name": "John Doe"
    },
    "answers": [
      {
        "id": 201,
        "question_id": 1,
        "answer_text": "My answer here...",
        "answer_file_url": "https://example.com/storage/submissions/file.pdf",
        "score": 8,
        "max_score": 10
      }
    ]
  }
}
```



### 7.5 Save Answer (Draft)
```http
POST /api/v1/submissions/{submission_id}/answers
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "question_id": 1,
  "answer": "My answer text here..."
}
```

Atau untuk file upload:
```http
POST /api/v1/submissions/{submission_id}/answers
Authorization: Bearer {token}
Content-Type: multipart/form-data

question_id=1
answer_file=@/path/to/file.pdf
```

**Response:**
```json
{
  "success": true,
  "message": "Answer saved successfully",
  "data": {
    "id": 201,
    "submission_id": 101,
    "question_id": 1,
    "answer_text": "My answer text here...",
    "answer_file_url": null,
    "created_at": "2024-03-03T14:15:00Z"
  }
}
```

### 7.6 Submit Answers (Final)
```http
POST /api/v1/submissions/{submission_id}/submit
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "answers": [
    {
      "question_id": 1,
      "answer": "Final answer for question 1"
    },
    {
      "question_id": 2,
      "answer": "Final answer for question 2"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Submission submitted successfully",
  "data": {
    "id": 101,
    "assignment_id": 1,
    "status": "submitted",
    "submitted_at": "2024-03-03T14:30:00Z",
    "message": "Your submission has been received and is awaiting grading."
  }
}
```

### 7.7 Update Submission (Before Submit)
```http
PUT /api/v1/submissions/{submission_id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "notes": "Additional notes..."
}
```



## 8. Quizzes

### 8.1 List Quizzes in Course
```http
GET /api/v1/courses/{course_slug}/quizzes
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`, `per_page`
- `filter[status]`: string
- `filter[unit_id]`: integer
- `include`: string

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Quiz 1: Introduction",
      "description": "Test your knowledge...",
      "passing_grade": 70,
      "max_score": 100,
      "time_limit_minutes": 30,
      "auto_grading": true,
      "review_mode": "immediate",
      "is_locked": false,
      "unit_slug": "getting-started",
      "questions_count": 10,
      "submission_status": "graded",
      "highest_score": 90,
      "attempt_count": 2,
      "created_at": "2024-01-15T10:00:00Z"
    }
  ],
  "meta": { ... }
}
```

### 8.2 Get Quiz Detail
```http
GET /api/v1/quizzes/{quiz_id}
Authorization: Bearer {token}
```

**Query Parameters:**
- `include`: string (contoh: `questions,submissions`)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Quiz 1: Introduction",
    "description": "Test your knowledge...",
    "passing_grade": 70,
    "max_score": 100,
    "time_limit_minutes": 30,
    "auto_grading": true,
    "review_mode": "immediate",
    "is_locked": false,
    "unit_slug": "getting-started",
    "questions_count": 10,
    "attachments": [
      {
        "id": 1,
        "name": "reference-material.pdf",
        "url": "https://example.com/storage/quizzes/file.pdf",
        "mime_type": "application/pdf",
        "size": 123456
      }
    ],
    "created_at": "2024-01-15T10:00:00Z"
  }
}
```

**Note:** Student TIDAK bisa melihat questions sebelum start quiz

### 8.3 List Questions (Hanya untuk yang sudah start)
```http
GET /api/v1/quizzes/{quiz_id}/questions
Authorization: Bearer {token}
```

**Response (Error jika belum start):**
```json
{
  "success": false,
  "message": "You must start the quiz first to view questions"
}
```



## 9. Quiz Submissions

### 9.1 Start Quiz
```http
POST /api/v1/quizzes/{quiz_id}/submissions/start
Authorization: Bearer {token}
```

**Request Body:** (empty)
```json
{}
```

**Response:**
```json
{
  "success": true,
  "message": "Quiz started successfully",
  "data": {
    "id": 501,
    "quiz_id": 1,
    "user_id": 10,
    "status": "in_progress",
    "attempt_number": 1,
    "started_at": "2024-03-03T14:00:00Z",
    "expires_at": "2024-03-03T14:30:00Z",
    "time_limit_minutes": 30,
    "questions_count": 10
  }
}
```

**Error Response (jika ada draft atau pending):**
```json
{
  "success": false,
  "message": "Cannot start new quiz attempt",
  "errors": {
    "quiz": ["You have an in-progress submission. Please complete it first."]
  }
}
```

### 9.2 List My Quiz Submissions
```http
GET /api/v1/quizzes/{quiz_id}/submissions
Authorization: Bearer {token}
```

**Response (untuk Student):**
```json
{
  "success": true,
  "data": [
    {
      "id": 501,
      "quiz_id": 1,
      "attempt_number": 1,
      "status": "graded",
      "final_score": 90,
      "max_score": 100,
      "is_highest": true,
      "started_at": "2024-03-01T14:00:00Z",
      "submitted_at": "2024-03-01T14:25:00Z",
      "graded_at": "2024-03-01T14:25:00Z"
    },
    {
      "id": 505,
      "quiz_id": 1,
      "attempt_number": 2,
      "status": "graded",
      "final_score": 85,
      "max_score": 100,
      "is_highest": false,
      "started_at": "2024-03-03T14:00:00Z",
      "submitted_at": "2024-03-03T14:28:00Z",
      "graded_at": "2024-03-03T14:28:00Z"
    }
  ]
}
```

### 9.3 Get Highest Quiz Submission
```http
GET /api/v1/quizzes/{quiz_id}/submissions/highest
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 501,
    "quiz_id": 1,
    "attempt_number": 1,
    "status": "graded",
    "final_score": 90,
    "max_score": 100,
    "started_at": "2024-03-01T14:00:00Z",
    "submitted_at": "2024-03-01T14:25:00Z",
    "graded_at": "2024-03-01T14:25:00Z"
  }
}
```

### 9.4 Get Quiz Submission Detail
```http
GET /api/v1/quiz-submissions/{submission_id}
Authorization: Bearer {token}
```

**Query Parameters:**
- `include`: string (contoh: `answers,questions`)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 501,
    "quiz_id": 1,
    "user_id": 10,
    "attempt_number": 1,
    "status": "graded",
    "final_score": 90,
    "max_score": 100,
    "started_at": "2024-03-01T14:00:00Z",
    "submitted_at": "2024-03-01T14:25:00Z",
    "graded_at": "2024-03-01T14:25:00Z",
    "time_taken_minutes": 25,
    "answers": [
      {
        "quiz_question_id": 1,
        "selected_option": "A",
        "is_correct": true,
        "score": 10
      }
    ]
  }
}
```



### 9.5 List Questions in Submission (Pagination - 1 per page)
```http
GET /api/v1/quiz-submissions/{submission_id}/questions
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`: integer (default: 1) - Student hanya bisa lihat 1 soal per halaman

**Response (untuk Student):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "quiz_id": 1,
    "question_text": "What is the capital of France?",
    "question_type": "multiple_choice",
    "points": 10,
    "order": 1,
    "options": [
      {
        "key": "A",
        "text": "London"
      },
      {
        "key": "B",
        "text": "Paris"
      },
      {
        "key": "C",
        "text": "Berlin"
      },
      {
        "key": "D",
        "text": "Madrid"
      }
    ],
    "my_answer": "B",
    "attachments": []
  },
  "meta": {
    "current_question": 1,
    "total_questions": 10,
    "has_next": true,
    "has_previous": false
  }
}
```

**Note:** Student TIDAK melihat `answer_key` atau `is_correct` sampai quiz selesai dan review_mode mengizinkan

### 9.6 Get Question at Specific Order
```http
GET /api/v1/quiz-submissions/{submission_id}/questions/{order}
Authorization: Bearer {token}
```

**Path Parameters:**
- `order`: integer (1-based, contoh: 1, 2, 3...)

**Response:**
```json
{
  "success": true,
  "data": {
    "question": {
      "id": 1,
      "question_text": "What is the capital of France?",
      "question_type": "multiple_choice",
      "points": 10,
      "order": 1,
      "options": [ ... ],
      "my_answer": "B"
    },
    "navigation": {
      "current": 1,
      "total": 10,
      "has_next": true,
      "has_previous": false,
      "next_url": "/api/v1/quiz-submissions/501/questions/2",
      "previous_url": null
    }
  }
}
```

### 9.7 Save Quiz Answer
```http
POST /api/v1/quiz-submissions/{submission_id}/answers
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body (Multiple Choice):**
```json
{
  "quiz_question_id": 1,
  "selected_option": "B"
}
```

**Request Body (Checkbox - multiple answers):**
```json
{
  "quiz_question_id": 2,
  "selected_options": ["A", "C", "D"]
}
```

**Request Body (Essay):**
```json
{
  "quiz_question_id": 3,
  "answer_text": "My essay answer here..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Answer saved successfully",
  "data": {
    "quiz_question_id": 1,
    "selected_option": "B",
    "saved_at": "2024-03-03T14:15:00Z"
  }
}
```

### 9.8 Submit Quiz (Final)
```http
POST /api/v1/quiz-submissions/{submission_id}/submit
Authorization: Bearer {token}
```

**Request Body:** (empty)
```json
{}
```

**Response (Auto-grading):**
```json
{
  "success": true,
  "message": "Quiz submitted successfully",
  "data": {
    "id": 501,
    "quiz_id": 1,
    "status": "graded",
    "final_score": 90,
    "max_score": 100,
    "submitted_at": "2024-03-03T14:28:00Z",
    "graded_at": "2024-03-03T14:28:00Z",
    "passed": true,
    "message": "Congratulations! You passed the quiz."
  }
}
```



---

# API SCHEMES MODULE - MANAJEMEN

**Role Required:** Superadmin, Admin, atau Instructor

## 10. Course Management

### 10.1 Create Course
```http
POST /api/v1/courses
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
code=CS101
title=Introduction to Programming
short_desc=Learn programming basics
type=free
level_tag=beginner
enrollment_type=auto_accept
status=draft
category_id=1
instructor_id=5
outcomes=["Understand basic programming", "Write simple programs"]
tags=["programming", "beginner"]
course_admins=[5, 8]
thumbnail=@/path/to/thumbnail.jpg
banner=@/path/to/banner.jpg
```

**Field Details:**
- `code`: string, required, unique, max 50 chars
- `title`: string, required, max 255 chars
- `short_desc`: string, nullable
- `type`: enum, required (`free`, `paid`)
- `level_tag`: enum, required (`beginner`, `intermediate`, `advanced`)
- `enrollment_type`: enum, required (`auto_accept`, `key_based`, `approval`)
- `status`: enum, optional (`draft`, `published`, `archived`)
- `category_id`: integer, nullable, exists in categories table
- `instructor_id`: integer, nullable, exists in users table
- `outcomes`: JSON array of strings, nullable
- `tags`: JSON array of strings, nullable
- `course_admins`: JSON array of user IDs, nullable
- `thumbnail`: file, nullable (jpg, jpeg, png, webp, max 2MB)
- `banner`: file, nullable (jpg, jpeg, png, webp, max 5MB)

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
    "created_at": "2024-03-03T14:00:00Z"
  }
}
```

### 10.2 Update Course
```http
PUT /api/v1/courses/{slug}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:** Same as Create, semua field optional

**Response:** Similar to Create

### 10.3 Delete Course
```http
DELETE /api/v1/courses/{slug}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Course deleted successfully",
  "data": []
}
```

### 10.4 Publish Course
```http
PUT /api/v1/courses/{slug}/publish
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Course published successfully",
  "data": {
    "id": 1,
    "slug": "introduction-to-programming",
    "status": "published",
    "published_at": "2024-03-03T14:30:00Z"
  }
}
```

### 10.5 Unpublish Course
```http
PUT /api/v1/courses/{slug}/unpublish
Authorization: Bearer {token}
```

### 10.6 Generate Enrollment Key
```http
POST /api/v1/courses/{slug}/enrollment-key/generate
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Enrollment key generated successfully",
  "data": {
    "course": { ... },
    "enrollment_key": "ABC123XYZ789"
  }
}
```

### 10.7 Update Enrollment Key
```http
PUT /api/v1/courses/{slug}/enrollment-key
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "enrollment_type": "key_based",
  "enrollment_key": "CUSTOM-KEY-2024"
}
```

### 10.8 Remove Enrollment Key
```http
DELETE /api/v1/courses/{slug}/enrollment-key
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Enrollment key removed successfully",
  "data": {
    "id": 1,
    "enrollment_type": "auto_accept"
  }
}
```



## 11. Unit Management

### 11.1 Create Unit
```http
POST /api/v1/courses/{course_slug}/units
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "code": "UNIT-01",
  "title": "Getting Started",
  "description": "Introduction to the course",
  "order": 1,
  "status": "draft"
}
```

**Field Details:**
- `code`: string, required, unique, max 50 chars
- `title`: string, required, max 255 chars
- `description`: string, nullable
- `order`: integer, nullable, min 1, unique per course
- `status`: enum, optional (`draft`, `published`)

**Response:**
```json
{
  "success": true,
  "message": "Unit created successfully",
  "data": {
    "id": 1,
    "course_id": 1,
    "code": "UNIT-01",
    "slug": "getting-started",
    "title": "Getting Started",
    "order": 1,
    "status": "draft",
    "created_at": "2024-03-03T14:00:00Z"
  }
}
```

### 11.2 Update Unit
```http
PUT /api/v1/courses/{course_slug}/units/{unit_slug}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:** Same as Create, semua field optional

### 11.3 Delete Unit
```http
DELETE /api/v1/courses/{course_slug}/units/{unit_slug}
Authorization: Bearer {token}
```

### 11.4 Publish Unit
```http
PUT /api/v1/courses/{course_slug}/units/{unit_slug}/publish
Authorization: Bearer {token}
```

### 11.5 Unpublish Unit
```http
PUT /api/v1/courses/{course_slug}/units/{unit_slug}/unpublish
Authorization: Bearer {token}
```

### 11.6 Reorder Units
```http
PUT /api/v1/courses/{course_slug}/units/reorder
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "units": [
    {"id": 2, "order": 1},
    {"id": 1, "order": 2},
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

### 11.7 Get Content Order
```http
GET /api/v1/courses/{course_slug}/units/{unit_slug}/content-order
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Content order retrieved successfully",
  "data": {
    "unit_id": 1,
    "contents": [
      {
        "id": 1,
        "type": "lesson",
        "title": "Welcome",
        "order": 1
      },
      {
        "id": 1,
        "type": "assignment",
        "title": "First Assignment",
        "order": 2
      },
      {
        "id": 1,
        "type": "quiz",
        "title": "Quiz 1",
        "order": 3
      }
    ]
  }
}
```

### 11.8 Reorder Unit Content
```http
PUT /api/v1/courses/{course_slug}/units/{unit_slug}/content-order
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "content": [
    {"type": "lesson", "id": 1, "order": 1},
    {"type": "assignment", "id": 1, "order": 2},
    {"type": "quiz", "id": 1, "order": 3},
    {"type": "lesson", "id": 2, "order": 4}
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Content reordered successfully",
  "data": { ... }
}
```



## 12. Lesson Management

### 12.1 Create Lesson
```http
POST /api/v1/courses/{course_slug}/units/{unit_slug}/lessons
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "code": "LESSON-01",
  "title": "Welcome to the Course",
  "content": "# Welcome\n\nThis is markdown content...",
  "order": 1,
  "status": "draft"
}
```

**Field Details:**
- `code`: string, required, unique, max 50 chars
- `title`: string, required, max 255 chars
- `content`: string (markdown), nullable
- `order`: integer, nullable, min 1
- `status`: enum, optional (`draft`, `published`)

**Response:**
```json
{
  "success": true,
  "message": "Lesson created successfully",
  "data": {
    "id": 1,
    "unit_id": 1,
    "code": "LESSON-01",
    "slug": "welcome-to-the-course",
    "title": "Welcome to the Course",
    "order": 1,
    "status": "draft",
    "created_at": "2024-03-03T14:00:00Z"
  }
}
```

### 12.2 Update Lesson
```http
PUT /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:** Same as Create, semua field optional

### 12.3 Delete Lesson
```http
DELETE /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}
Authorization: Bearer {token}
```

### 12.4 Publish Lesson
```http
PUT /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/publish
Authorization: Bearer {token}
```

### 12.5 Unpublish Lesson
```http
PUT /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/unpublish
Authorization: Bearer {token}
```

## 13. Lesson Block Management

### 13.1 Create Lesson Block
```http
POST /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```
type=text
content=This is a text block content
order=1
```

Atau untuk video/image:
```
type=video
order=2
media=@/path/to/video.mp4
```

**Field Details:**
- `type`: enum, required (`text`, `video`, `image`, `file`, `code`)
- `content`: string, nullable (untuk type text/code)
- `order`: integer, nullable, min 1
- `media`: file, nullable (untuk type video/image/file)

**Response:**
```json
{
  "success": true,
  "message": "Lesson block created successfully",
  "data": {
    "id": 1,
    "lesson_id": 1,
    "slug": "intro-text",
    "type": "text",
    "content": "This is a text block content",
    "order": 1,
    "media_url": null,
    "created_at": "2024-03-03T14:00:00Z"
  }
}
```

### 13.2 Update Lesson Block
```http
PUT /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### 13.3 Delete Lesson Block
```http
DELETE /api/v1/courses/{course_slug}/units/{unit_slug}/lessons/{lesson_slug}/blocks/{block_slug}
Authorization: Bearer {token}
```



---

# API LEARNING MODULE - MANAJEMEN

**Role Required:** Superadmin, Admin, atau Instructor

## 14. Assignment Management

### 14.1 Create Assignment
```http
POST /api/v1/assignments
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
type=assignment
title=First Assignment
description=Complete the following tasks...
unit_id=1
order=2
submission_type=file
max_score=100
passing_grade=60
status=draft
review_mode=manual
attachments[]=@/path/to/file1.pdf
attachments[]=@/path/to/file2.pdf
```

**Field Details:**
- `type`: enum, required (`assignment`, `quiz`)
- `title`: string, required, max 255 chars
- `description`: string, nullable
- `unit_id`: integer, required, exists in units table
- `order`: integer, nullable, min 1
- `submission_type`: enum, required (`text`, `file`, `mixed`)
- `max_score`: integer, nullable, min 1, max 1000
- `passing_grade`: numeric, nullable, min 0, max 100
- `status`: enum, optional (`draft`, `published`, `archived`)
- `time_limit_minutes`: integer, nullable, min 1
- `review_mode`: enum, nullable (`immediate`, `manual`, `deferred`, `hidden`)
- `randomization_type`: enum, nullable (`static`, `random_order`, `bank`) - HANYA untuk type=quiz
- `question_bank_count`: integer, nullable, min 0 - HANYA untuk type=quiz
- `attachments`: array of files, max 5 files, each max 10MB

**Validation Rules:**
- Jika `type=assignment`: `submission_type` harus `file` atau `mixed`, `review_mode` harus `manual`
- Jika `type=assignment`: TIDAK boleh ada `randomization_type` atau `question_bank_count`

**Response:**
```json
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "id": 1,
    "title": "First Assignment",
    "submission_type": "file",
    "max_score": 100,
    "status": "draft",
    "unit_slug": "getting-started",
    "course_slug": "introduction-to-programming",
    "created_at": "2024-03-03T14:00:00Z"
  }
}
```

### 14.2 Update Assignment
```http
PUT /api/v1/assignments/{assignment_id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:** Same as Create, semua field optional

### 14.3 Delete Assignment
```http
DELETE /api/v1/assignments/{assignment_id}
Authorization: Bearer {token}
```

### 14.4 Publish Assignment
```http
PUT /api/v1/assignments/{assignment_id}/publish
Authorization: Bearer {token}
```

### 14.5 Unpublish Assignment
```http
PUT /api/v1/assignments/{assignment_id}/unpublish
Authorization: Bearer {token}
```

### 14.6 Archive Assignment
```http
PUT /api/v1/assignments/{assignment_id}/archived
Authorization: Bearer {token}
```

### 14.7 Duplicate Assignment
```http
POST /api/v1/assignments/{assignment_id}/duplicate
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "First Assignment (Copy)",
  "unit_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Assignment duplicated successfully",
  "data": {
    "id": 5,
    "title": "First Assignment (Copy)",
    "unit_id": 2
  }
}
```



### 14.8 List All Submissions (Admin/Instructor View)
```http
GET /api/v1/assignments/{assignment_id}/submissions
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`, `per_page`
- `filter[status]`: string (`draft`, `submitted`, `graded`, `missing`)
- `filter[user_id]`: integer
- `filter[score_min]`: numeric
- `filter[score_max]`: numeric
- `sort`: string (contoh: `score`, `-submitted_at`)
- `include`: string (contoh: `user,answers`)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "assignment_id": 1,
      "user": {
        "id": 10,
        "name": "Student Name",
        "email": "student@example.com"
      },
      "attempt_number": 1,
      "status": "graded",
      "score": 85,
      "max_score": 100,
      "submitted_at": "2024-03-01T15:30:00Z",
      "graded_at": "2024-03-02T10:00:00Z",
      "graded_by": {
        "id": 5,
        "name": "Instructor Name"
      }
    }
  ],
  "meta": { ... }
}
```

### 14.9 Grade Submission
```http
POST /api/v1/submissions/{submission_id}/grade
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "score": 85,
  "feedback": "Good work! Minor improvements needed in section 2."
}
```

**Field Details:**
- `score`: numeric, required, min 0, max assignment.max_score
- `feedback`: string, nullable

**Response:**
```json
{
  "success": true,
  "message": "Submission graded successfully",
  "data": {
    "id": 101,
    "assignment_id": 1,
    "status": "graded",
    "score": 85,
    "max_score": 100,
    "feedback": "Good work! Minor improvements needed in section 2.",
    "graded_at": "2024-03-03T14:30:00Z",
    "graded_by": {
      "id": 5,
      "name": "Instructor Name"
    }
  }
}
```

### 14.10 Search Submissions (Global)
```http
GET /api/v1/submissions/search
Authorization: Bearer {token}
```

**Query Parameters:**
- `query`: string (search in student name, assignment title)
- `filters[course_id]`: integer
- `filters[assignment_id]`: integer
- `filters[status]`: string
- `filters[user_id]`: integer
- `page`, `per_page`

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [ ... ],
    "meta": {
      "total": 150,
      "per_page": 15,
      "current_page": 1,
      "last_page": 10
    }
  }
}
```

### 14.11 List All Assessments in Course
```http
GET /api/v1/courses/{course_slug}/assessments
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`, `per_page`
- `filter[type]`: string (`assignment`, `quiz`)
- `filter[status]`: string
- `filter[unit_id]`: integer

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "assignment",
      "title": "First Assignment",
      "unit_slug": "getting-started",
      "status": "published",
      "submissions_count": 45,
      "graded_count": 30,
      "pending_count": 15
    },
    {
      "id": 2,
      "type": "quiz",
      "title": "Quiz 1",
      "unit_slug": "getting-started",
      "status": "published",
      "submissions_count": 50,
      "graded_count": 50,
      "pending_count": 0
    }
  ],
  "meta": { ... }
}
```



## 15. Quiz Management

### 15.1 Create Quiz
```http
POST /api/v1/quizzes
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
unit_id=1
order=3
title=Quiz 1: Introduction
description=Test your knowledge...
passing_grade=70
auto_grading=true
max_score=100
time_limit_minutes=30
randomization_type=static
question_bank_count=0
review_mode=immediate
attachments[]=@/path/to/reference.pdf
```

**Field Details:**
- `unit_id`: integer, required, exists in units table
- `order`: integer, nullable, min 1
- `title`: string, required, max 255 chars
- `description`: string, nullable
- `passing_grade`: numeric, nullable, min 0, max 100
- `auto_grading`: boolean, nullable (default: true)
- `max_score`: numeric, nullable, min 1
- `time_limit_minutes`: integer, nullable, min 1
- `randomization_type`: enum, nullable (`static`, `random_order`, `bank`)
- `question_bank_count`: integer, nullable, min 1 (required jika randomization_type=bank)
- `review_mode`: enum, nullable (`immediate`, `after_deadline`, `never`)
- `attachments`: array of files, nullable

**Response:**
```json
{
  "success": true,
  "message": "Quiz created successfully",
  "data": {
    "id": 1,
    "title": "Quiz 1: Introduction",
    "passing_grade": 70,
    "auto_grading": true,
    "max_score": 100,
    "time_limit_minutes": 30,
    "randomization_type": "static",
    "review_mode": "immediate",
    "status": "draft",
    "created_at": "2024-03-03T14:00:00Z"
  }
}
```

### 15.2 Update Quiz
```http
PUT /api/v1/quizzes/{quiz_id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:** Same as Create, semua field optional

### 15.3 Delete Quiz
```http
DELETE /api/v1/quizzes/{quiz_id}
Authorization: Bearer {token}
```

### 15.4 Publish Quiz
```http
PUT /api/v1/quizzes/{quiz_id}/publish
Authorization: Bearer {token}
```

### 15.5 Unpublish Quiz
```http
PUT /api/v1/quizzes/{quiz_id}/unpublish
Authorization: Bearer {token}
```

### 15.6 Archive Quiz
```http
PUT /api/v1/quizzes/{quiz_id}/archived
Authorization: Bearer {token}
```



## 16. Quiz Question Management

### 16.1 List Questions (Admin/Instructor View)
```http
GET /api/v1/quizzes/{quiz_id}/questions
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`, `per_page`
- `filter[question_type]`: string (`multiple_choice`, `checkbox`, `essay`, `file_upload`)
- `sort`: string (contoh: `order`, `-points`)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "quiz_id": 1,
      "question_text": "What is the capital of France?",
      "question_type": "multiple_choice",
      "points": 10,
      "order": 1,
      "options": [
        {"key": "A", "text": "London"},
        {"key": "B", "text": "Paris"},
        {"key": "C", "text": "Berlin"},
        {"key": "D", "text": "Madrid"}
      ],
      "answer_key": "B",
      "explanation": "Paris is the capital of France.",
      "attachments": []
    }
  ],
  "meta": { ... }
}
```

### 16.2 Get Question Detail
```http
GET /api/v1/quizzes/{quiz_id}/questions/{question_id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "quiz_id": 1,
    "question_text": "What is the capital of France?",
    "question_type": "multiple_choice",
    "points": 10,
    "order": 1,
    "options": [ ... ],
    "answer_key": "B",
    "explanation": "Paris is the capital of France.",
    "attachments": []
  }
}
```

### 16.3 Add Question
```http
POST /api/v1/quizzes/{quiz_id}/questions
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Multiple Choice):**
```
question_text=What is the capital of France?
question_type=multiple_choice
points=10
order=1
options[0][key]=A
options[0][text]=London
options[1][key]=B
options[1][text]=Paris
options[2][key]=C
options[2][text]=Berlin
options[3][key]=D
options[3][text]=Madrid
answer_key=B
explanation=Paris is the capital of France.
```

**Request Body (Checkbox - multiple correct answers):**
```
question_text=Select all programming languages
question_type=checkbox
points=10
order=2
options[0][key]=A
options[0][text]=Python
options[1][key]=B
options[1][text]=HTML
options[2][key]=C
options[2][text]=Java
options[3][key]=D
options[3][text]=CSS
answer_keys[]=A
answer_keys[]=C
explanation=Python and Java are programming languages.
```

**Request Body (Essay):**
```
question_text=Explain the concept of object-oriented programming
question_type=essay
points=20
order=3
explanation=OOP is a programming paradigm...
```

**Field Details:**
- `question_text`: string, required
- `question_type`: enum, required (`multiple_choice`, `checkbox`, `essay`, `file_upload`)
- `points`: numeric, required, min 0
- `order`: integer, nullable, min 1
- `options`: array, required untuk multiple_choice/checkbox
- `answer_key`: string, required untuk multiple_choice
- `answer_keys`: array, required untuk checkbox
- `explanation`: string, nullable
- `attachments`: array of files, nullable

**Response:**
```json
{
  "success": true,
  "message": "Question created successfully",
  "data": {
    "id": 1,
    "quiz_id": 1,
    "question_text": "What is the capital of France?",
    "question_type": "multiple_choice",
    "points": 10,
    "order": 1
  }
}
```

### 16.4 Update Question
```http
PUT /api/v1/quizzes/{quiz_id}/questions/{question_id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:** Same as Add Question, semua field optional

### 16.5 Delete Question
```http
DELETE /api/v1/quizzes/{quiz_id}/questions/{question_id}
Authorization: Bearer {token}
```

### 16.6 Reorder Questions
```http
POST /api/v1/quizzes/{quiz_id}/questions/reorder
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "ids": [3, 1, 2, 5, 4]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Questions reordered successfully",
  "data": []
}
```



### 16.7 List Quiz Submissions (Admin/Instructor View)
```http
GET /api/v1/quizzes/{quiz_id}/submissions
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`, `per_page`
- `filter[status]`: string (`in_progress`, `submitted`, `graded`)
- `filter[user_id]`: integer
- `filter[score_min]`: numeric
- `filter[score_max]`: numeric
- `sort`: string (contoh: `final_score`, `-submitted_at`)
- `include`: string (contoh: `user,answers`)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 501,
      "quiz_id": 1,
      "user": {
        "id": 10,
        "name": "Student Name",
        "email": "student@example.com"
      },
      "attempt_number": 1,
      "status": "graded",
      "final_score": 90,
      "max_score": 100,
      "started_at": "2024-03-01T14:00:00Z",
      "submitted_at": "2024-03-01T14:25:00Z",
      "graded_at": "2024-03-01T14:25:00Z",
      "time_taken_minutes": 25
    }
  ],
  "meta": { ... }
}
```

---

## 17. Common Query Parameters

### Filter Parameters

**Status Filters:**
- `filter[status]=draft` - Draft items
- `filter[status]=published` - Published items
- `filter[status]=archived` - Archived items

**Search:**
- `filter[search]=keyword` - Search in title, code, description

**Date Range:**
- `filter[created_from]=2024-01-01` - Created after date
- `filter[created_to]=2024-12-31` - Created before date

**Numeric Range:**
- `filter[score_min]=70` - Minimum score
- `filter[score_max]=100` - Maximum score

### Sort Parameters

**Format:** `sort=field` (ascending) atau `sort=-field` (descending)

**Examples:**
- `sort=title` - Sort by title A-Z
- `sort=-created_at` - Sort by newest first
- `sort=order` - Sort by order ascending
- `sort=-score` - Sort by highest score first

### Include Parameters

**Format:** `include=relation1,relation2,relation3`

**Common Relations:**
- Courses: `category,instructor,tags,units,admins,enrollments`
- Units: `course,lessons`
- Lessons: `unit,blocks`
- Assignments: `unit,questions,submissions,prerequisites`
- Quizzes: `unit,questions,submissions`
- Submissions: `user,assignment,answers,graded_by`

**Examples:**
- `include=category,instructor` - Include category and instructor
- `include=units.lessons` - Include units with their lessons (nested)



---

## 18. Business Rules & Logic

### Prerequisite System

**Unit Prerequisites:**
- Unit 1 selalu terbuka (tidak ada prerequisite)
- Unit 2+ memerlukan unit sebelumnya 100% selesai
- 100% selesai = semua lessons completed + semua assignments/quizzes passed

**Passing Criteria:**
- Assignment: `score >= (max_score * 0.6)` atau `score >= passing_grade`
- Quiz: `final_score >= passing_grade`

**Locked Content:**
- `is_locked: true` = tidak bisa diakses
- `is_locked: false` = bisa diakses
- Student tidak bisa complete lesson, submit assignment, atau start quiz jika `is_locked: true`

### Unlimited Attempts System

**New System (Setelah Retake Removal):**
- Student bisa mengerjakan assignment/quiz unlimited times
- Setiap attempt disimpan dengan `attempt_number` yang increment
- Highest score yang digunakan untuk passing, bukan latest
- History semua attempts tetap tersimpan

**Validation Rules:**
- Tidak bisa start attempt baru jika ada draft submission (`status=draft`)
- Tidak bisa start attempt baru jika ada pending grading (`status=submitted`)
- Harus tunggu grading selesai sebelum bisa attempt lagi

**Example Flow:**
1. Student start assignment → `attempt_number=1`, `status=draft`
2. Student submit → `status=submitted` (pending grading)
3. Instructor grade → `status=graded`, `score=75`
4. Student bisa start lagi → `attempt_number=2`, `status=draft`
5. Student submit → `status=submitted`
6. Instructor grade → `status=graded`, `score=85`
7. Highest score (85) digunakan untuk passing

### Quiz Flow

**Student Flow:**
1. View quiz detail (tidak bisa lihat questions)
2. Start quiz → create submission with `status=in_progress`
3. Get questions one by one (pagination, 1 per page)
4. Save answers (dapat disimpan berkali-kali)
5. Submit quiz → auto-grading (jika `auto_grading=true`)
6. View results (tergantung `review_mode`)

**Time Limit:**
- Jika ada `time_limit_minutes`, quiz harus diselesaikan dalam waktu tersebut
- `expires_at` dihitung dari `started_at + time_limit_minutes`
- Jika waktu habis, quiz otomatis submit

**Review Mode:**
- `immediate`: Student langsung bisa lihat jawaban benar/salah setelah submit
- `manual`: Instruktur review manual (untuk essay/file_upload)
- `deferred`: Tampil setelah deadline
- `hidden`: Tidak tampil sama sekali

### Assignment Flow

**Student Flow:**
1. View assignment detail
2. Start submission → create submission with `status=draft`
3. View questions (bisa lihat semua questions)
4. Save answers (dapat disimpan berkali-kali)
5. Submit answers → `status=submitted` (menunggu grading)
6. Instructor grade → `status=graded`
7. View graded submission

**Submission Types:**
- `text`: Jawaban teks saja
- `file`: Upload file saja
- `mixed`: Kombinasi teks dan file

**Review Mode:**
- Assignment selalu `manual` (instruktur yang grade)

### Grading

**Auto-grading (Quiz):**
- Hanya untuk `multiple_choice` dan `checkbox`
- Langsung graded setelah submit
- Score dihitung otomatis berdasarkan `answer_key`

**Manual Grading (Assignment):**
- Instruktur harus grade manual
- Bisa kasih `score` dan `feedback`
- Status berubah dari `submitted` ke `graded`



---

## 19. Error Codes & Messages

### HTTP Status Codes

**Success:**
- `200 OK` - Request berhasil
- `201 Created` - Resource berhasil dibuat
- `204 No Content` - Request berhasil tanpa response body

**Client Errors:**
- `400 Bad Request` - Request tidak valid
- `401 Unauthorized` - Tidak ada token atau token invalid
- `403 Forbidden` - Tidak punya permission
- `404 Not Found` - Resource tidak ditemukan
- `422 Unprocessable Entity` - Validation error

**Server Errors:**
- `500 Internal Server Error` - Server error

### Common Error Responses

**Validation Error (422):**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "email": ["The email has already been taken."],
    "max_score": ["The max score must be at least 1."]
  }
}
```

**Unauthorized (401):**
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

**Forbidden (403):**
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```

**Not Found (404):**
```json
{
  "success": false,
  "message": "Resource not found."
}
```

**Business Logic Error (400):**
```json
{
  "success": false,
  "message": "Cannot start new submission",
  "errors": {
    "submission": ["You have a draft submission. Please complete or delete it first."]
  }
}
```

### Specific Error Messages

**Assignment/Quiz Errors:**
- `"Assignment is locked. Prerequisites not met."`
- `"You have a draft submission. Please complete or delete it first."`
- `"You have a pending submission awaiting grading."`
- `"Quiz time limit exceeded."`
- `"You must start the quiz first to view questions."`

**Prerequisite Errors:**
- `"Unit 1 must be 100% complete before accessing Unit 2."`
- `"You must complete all lessons in this unit."`
- `"You must pass all assignments in previous units."`

**Permission Errors:**
- `"You do not have permission to view this course."`
- `"Only instructors can grade submissions."`
- `"You can only view your own submissions."`



---

## 20. Complete Examples

### Example 1: Student Mengerjakan Assignment

**Step 1: Lihat assignment detail**
```http
GET /api/v1/assignments/1
Authorization: Bearer {token}
```

**Step 2: Check prerequisites**
```http
GET /api/v1/assignments/1/prerequisites/check
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "can_access": true,
    "is_locked": false
  }
}
```

**Step 3: Start submission**
```http
POST /api/v1/assignments/1/submissions
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 101,
    "status": "draft",
    "attempt_number": 1
  }
}
```

**Step 4: Save answer (bisa berkali-kali)**
```http
POST /api/v1/submissions/101/answers
Authorization: Bearer {token}
Content-Type: multipart/form-data

question_id=1
answer_file=@/path/to/answer.pdf
```

**Step 5: Submit final**
```http
POST /api/v1/submissions/101/submit
Authorization: Bearer {token}
Content-Type: application/json

{
  "answers": [
    {"question_id": 1, "answer": "Final answer"}
  ]
}
```

Response:
```json
{
  "success": true,
  "message": "Submission submitted successfully",
  "data": {
    "id": 101,
    "status": "submitted",
    "submitted_at": "2024-03-03T14:30:00Z"
  }
}
```

**Step 6: Tunggu grading dari instruktur**

**Step 7: Lihat hasil grading**
```http
GET /api/v1/assignments/1/submissions/101
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 101,
    "status": "graded",
    "score": 85,
    "max_score": 100,
    "feedback": "Good work!",
    "graded_at": "2024-03-03T16:00:00Z"
  }
}
```

**Step 8: Jika ingin retry (attempt ke-2)**
```http
POST /api/v1/assignments/1/submissions
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 105,
    "status": "draft",
    "attempt_number": 2
  }
}
```

### Example 2: Student Mengerjakan Quiz

**Step 1: Lihat quiz detail**
```http
GET /api/v1/quizzes/1
Authorization: Bearer {token}
```

**Step 2: Start quiz**
```http
POST /api/v1/quizzes/1/submissions/start
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 501,
    "status": "in_progress",
    "started_at": "2024-03-03T14:00:00Z",
    "expires_at": "2024-03-03T14:30:00Z",
    "time_limit_minutes": 30
  }
}
```

**Step 3: Get question (1 per page)**
```http
GET /api/v1/quiz-submissions/501/questions?page=1
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "question_text": "What is 2+2?",
    "question_type": "multiple_choice",
    "options": [
      {"key": "A", "text": "3"},
      {"key": "B", "text": "4"},
      {"key": "C", "text": "5"}
    ]
  },
  "meta": {
    "current_question": 1,
    "total_questions": 10,
    "has_next": true
  }
}
```

**Step 4: Save answer**
```http
POST /api/v1/quiz-submissions/501/answers
Authorization: Bearer {token}
Content-Type: application/json

{
  "quiz_question_id": 1,
  "selected_option": "B"
}
```

**Step 5: Next question**
```http
GET /api/v1/quiz-submissions/501/questions?page=2
Authorization: Bearer {token}
```

**Step 6: Ulangi step 4-5 untuk semua soal**

**Step 7: Submit quiz**
```http
POST /api/v1/quiz-submissions/501/submit
Authorization: Bearer {token}
```

Response (auto-grading):
```json
{
  "success": true,
  "data": {
    "id": 501,
    "status": "graded",
    "final_score": 90,
    "max_score": 100,
    "passed": true,
    "graded_at": "2024-03-03T14:28:00Z"
  }
}
```



### Example 3: Instructor Membuat Course dengan Units dan Lessons

**Step 1: Create course**
```http
POST /api/v1/courses
Authorization: Bearer {token}
Content-Type: multipart/form-data

code=CS101
title=Introduction to Programming
type=free
level_tag=beginner
enrollment_type=auto_accept
status=draft
thumbnail=@/path/to/thumbnail.jpg
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "slug": "introduction-to-programming"
  }
}
```

**Step 2: Create unit 1**
```http
POST /api/v1/courses/introduction-to-programming/units
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "UNIT-01",
  "title": "Getting Started",
  "order": 1,
  "status": "draft"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "slug": "getting-started"
  }
}
```

**Step 3: Create lesson in unit 1**
```http
POST /api/v1/courses/introduction-to-programming/units/getting-started/lessons
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "LESSON-01",
  "title": "Welcome",
  "content": "# Welcome\n\nThis is the first lesson...",
  "order": 1,
  "status": "draft"
}
```

**Step 4: Create assignment in unit 1**
```http
POST /api/v1/assignments
Authorization: Bearer {token}
Content-Type: multipart/form-data

type=assignment
title=First Assignment
unit_id=1
order=2
submission_type=file
max_score=100
passing_grade=60
status=draft
review_mode=manual
```

**Step 5: Create quiz in unit 1**
```http
POST /api/v1/quizzes
Authorization: Bearer {token}
Content-Type: application/json

{
  "unit_id": 1,
  "order": 3,
  "title": "Quiz 1",
  "passing_grade": 70,
  "auto_grading": true,
  "time_limit_minutes": 30,
  "randomization_type": "static",
  "review_mode": "immediate"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 1
  }
}
```

**Step 6: Add questions to quiz**
```http
POST /api/v1/quizzes/1/questions
Authorization: Bearer {token}
Content-Type: multipart/form-data

question_text=What is 2+2?
question_type=multiple_choice
points=10
order=1
options[0][key]=A
options[0][text]=3
options[1][key]=B
options[1][text]=4
options[2][key]=C
options[2][text]=5
answer_key=B
```

**Step 7: Publish everything**
```http
PUT /api/v1/courses/introduction-to-programming/units/getting-started/lessons/welcome/publish
PUT /api/v1/assignments/1/publish
PUT /api/v1/quizzes/1/publish
PUT /api/v1/courses/introduction-to-programming/units/getting-started/publish
PUT /api/v1/courses/introduction-to-programming/publish
```

### Example 4: Instructor Grading Submission

**Step 1: List submissions for assignment**
```http
GET /api/v1/assignments/1/submissions?filter[status]=submitted
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "user": {
        "id": 10,
        "name": "Student Name"
      },
      "status": "submitted",
      "submitted_at": "2024-03-03T14:30:00Z"
    }
  ]
}
```

**Step 2: View submission detail**
```http
GET /api/v1/assignments/1/submissions/101?include=answers
Authorization: Bearer {token}
```

**Step 3: Grade submission**
```http
POST /api/v1/submissions/101/grade
Authorization: Bearer {token}
Content-Type: application/json

{
  "score": 85,
  "feedback": "Good work! Minor improvements needed in section 2."
}
```

Response:
```json
{
  "success": true,
  "message": "Submission graded successfully",
  "data": {
    "id": 101,
    "status": "graded",
    "score": 85,
    "graded_at": "2024-03-03T16:00:00Z"
  }
}
```

