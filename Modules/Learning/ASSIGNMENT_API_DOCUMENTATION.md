# Assignment API Documentation

## Overview
Assignment adalah tugas berbasis file upload yang memerlukan grading manual oleh instructor. Berbeda dengan Quiz yang berbasis pertanyaan objektif.

---

## Endpoints

### 1. List Assignments in Course
```
GET /api/v1/courses/{course_slug}/assignments
```

**Authorization**: Authenticated users with `viewAssignments` permission on course

**Query Parameters**:
- `per_page` (integer, optional): Items per page (default: 15, max: 100)
- `page` (integer, optional): Page number
- `filter[status]` (string, optional): Filter by status (`draft`, `published`, `archived`)
- `sort` (string, optional): Sort field (`order`, `title`, `created_at`)

**Response**:
```json
{
  "success": true,
  "message": "Assignments retrieved successfully",
  "data": [
    {
      "id": 1,
      "type": "assignment",
      "title": "Project Proposal",
      "description": "Create a comprehensive project proposal...",
      "unit_id": 5,
      "order": 1,
      "submission_type": "file",
      "max_score": 100,
      "passing_grade": 70,
      "status": "published",
      "review_mode": "manual",
      "time_limit_minutes": null,
      "created_at": "2026-03-06T10:00:00Z",
      "updated_at": "2026-03-06T10:00:00Z"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 25,
      "last_page": 2
    }
  }
}
```

---

### 2. List Incomplete Assignments
```
GET /api/v1/courses/{course_slug}/assignments/incomplete
```

**Authorization**: Authenticated users with `viewAssignments` permission

**Description**: Returns assignments that the authenticated user hasn't completed yet.

**Response**: Same structure as list assignments

---

### 3. Show Assignment Detail
```
GET /api/v1/assignments/{assignment_id}
```

**Authorization**: User must have `view` permission on assignment

**Response**:
```json
{
  "success": true,
  "message": "Assignment retrieved successfully",
  "data": {
    "id": 1,
    "type": "assignment",
    "title": "Project Proposal",
    "description": "Create a comprehensive project proposal document...",
    "unit_id": 5,
    "order": 1,
    "submission_type": "file",
    "max_score": 100,
    "passing_grade": 70,
    "status": "published",
    "review_mode": "manual",
    "time_limit_minutes": 120,
    "created_by": 3,
    "created_at": "2026-03-06T10:00:00Z",
    "updated_at": "2026-03-06T10:00:00Z",
    "attachments": [
      {
        "id": 1,
        "file_name": "requirements.pdf",
        "mime_type": "application/pdf",
        "size": 245678,
        "url": "https://storage.example.com/assignments/1/requirements.pdf"
      }
    ],
    "unit": {
      "id": 5,
      "title": "Database Design",
      "slug": "database-design"
    }
  }
}
```

---

### 4. Create Assignment
```
POST /api/v1/assignments
```

**Authorization**: Admin, Instructor, or Superadmin

**Content-Type**: `multipart/form-data` (if uploading attachments) or `application/json`

**Request Body**:
```json
{
  "type": "assignment",
  "title": "Database Design Project",
  "description": "Design a normalized database schema for an e-commerce system",
  "unit_id": 5,
  "submission_type": "file",
  "max_score": 100,
  "passing_grade": 70,
  "status": "draft",
  "review_mode": "manual",
  "time_limit_minutes": 120,
  "order": 1
}
```

**With Attachments (Form Data)**:
```
type: assignment
title: Database Design Project
description: Design a normalized database schema...
unit_id: 5
submission_type: file
max_score: 100
passing_grade: 70
status: draft
review_mode: manual
time_limit_minutes: 120
order: 1
attachments[]: [FILE]
attachments[]: [FILE]
```

**Response**: Same as Show Assignment Detail

---

### 5. Update Assignment
```
PUT /api/v1/assignments/{assignment_id}
```

**Authorization**: User must have `update` permission on assignment

**Request Body**: Same as Create Assignment (all fields optional)

**Response**: Same as Show Assignment Detail

---

### 6. Delete Assignment
```
DELETE /api/v1/assignments/{assignment_id}
```

**Authorization**: User must have `delete` permission on assignment

**Response**:
```json
{
  "success": true,
  "message": "Assignment deleted successfully",
  "data": null
}
```

---

### 7. Publish Assignment
```
PUT /api/v1/assignments/{assignment_id}/publish
```

**Authorization**: User must have `update` permission on assignment

**Response**: Same as Show Assignment Detail with `status: "published"`

---

### 8. Unpublish Assignment
```
PUT /api/v1/assignments/{assignment_id}/unpublish
```

**Authorization**: User must have `update` permission on assignment

**Response**: Same as Show Assignment Detail with `status: "draft"`

---

### 9. Archive Assignment
```
PUT /api/v1/assignments/{assignment_id}/archived
```

**Authorization**: User must have `update` permission on assignment

**Response**: Same as Show Assignment Detail with `status: "archived"`

---

### 10. Duplicate Assignment
```
POST /api/v1/assignments/{assignment_id}/duplicate
```

**Authorization**: User must have `duplicate` permission on assignment

**Request Body**:
```json
{
  "title": "Database Design Project (Copy)",
  "unit_id": 6
}
```

**Response**: Same as Show Assignment Detail (new assignment)

---

### 11. Check Prerequisites
```
GET /api/v1/assignments/{assignment_id}/prerequisites/check
```

**Authorization**: Authenticated user

**Response**:
```json
{
  "success": true,
  "message": "Prerequisites checked",
  "data": {
    "can_access": true,
    "missing_prerequisites": [],
    "locked": false
  }
}
```

---

## Submission Endpoints

### 12. List Submissions for Assignment
```
GET /api/v1/assignments/{assignment_id}/submissions
```

**Authorization**: Authenticated user

**Query Parameters**:
- `per_page` (integer, optional)
- `filter[status]` (string, optional): `draft`, `submitted`, `graded`, `missing`
- `filter[user_id]` (integer, optional): Filter by user (instructors only)

**Response**:
```json
{
  "success": true,
  "message": "Submissions retrieved successfully",
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "user_id": 10,
      "attempt_number": 1,
      "status": "graded",
      "score": 85,
      "feedback": "Good work, but needs improvement in...",
      "submitted_at": "2026-03-06T15:30:00Z",
      "graded_at": "2026-03-07T10:00:00Z",
      "graded_by": 3,
      "user": {
        "id": 10,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "meta": {
    "pagination": {...}
  }
}
```

---

### 13. Get Highest Submission
```
GET /api/v1/assignments/{assignment_id}/submissions/highest
```

**Authorization**: Authenticated user

**Description**: Returns the submission with the highest score for the authenticated user

**Response**: Single submission object

---

### 14. Show Submission Detail
```
GET /api/v1/assignments/{assignment_id}/submissions/{submission_id}
```

**Authorization**: Authenticated user

**Response**:
```json
{
  "success": true,
  "message": "Submission retrieved successfully",
  "data": {
    "id": 1,
    "assignment_id": 1,
    "user_id": 10,
    "attempt_number": 1,
    "status": "graded",
    "score": 85,
    "feedback": "Good work overall...",
    "submitted_at": "2026-03-06T15:30:00Z",
    "graded_at": "2026-03-07T10:00:00Z",
    "graded_by": 3,
    "files": [
      {
        "id": 1,
        "file_name": "proposal.pdf",
        "mime_type": "application/pdf",
        "size": 1234567,
        "url": "https://storage.example.com/submissions/1/proposal.pdf"
      }
    ],
    "assignment": {
      "id": 1,
      "title": "Project Proposal",
      "max_score": 100,
      "passing_grade": 70
    }
  }
}
```

---

### 15. Create Submission
```
POST /api/v1/assignments/{assignment_id}/submissions
```

**Authorization**: Authenticated user (Student)

**Content-Type**: `multipart/form-data`

**Request Body**:
```
files[0]: [FILE]
files[1]: [FILE]
text_content: "Optional text submission"
link_url: "https://github.com/user/project"
```

**Response**: Same as Show Submission Detail with `status: "draft"`

---

### 16. Update Submission
```
PUT /api/v1/submissions/{submission_id}
```

**Authorization**: User must have `update` permission on submission

**Content-Type**: `multipart/form-data`

**Request Body**: Same as Create Submission

**Response**: Same as Show Submission Detail

---

### 17. Submit Assignment
```
POST /api/v1/submissions/{submission_id}/submit
```

**Authorization**: User must have `submit` permission on submission

**Description**: Finalizes the submission (changes status from `draft` to `submitted`)

**Response**: Same as Show Submission Detail with `status: "submitted"`

---

### 18. Grade Submission
```
POST /api/v1/submissions/{submission_id}/grade
```

**Authorization**: Admin, Instructor, or Superadmin with `grade` permission

**Request Body**:
```json
{
  "score": 85,
  "feedback": "Good work overall. The database schema is well-designed, but you need to add more indexes for performance optimization."
}
```

**Response**: Same as Show Submission Detail with `status: "graded"`

---

### 19. Search Submissions
```
GET /api/v1/submissions/search
```

**Authorization**: Admin, Instructor, or Superadmin

**Query Parameters**:
- `q` (string, required): Search query
- `assignment_id` (integer, optional)
- `user_id` (integer, optional)
- `status` (string, optional)
- `per_page` (integer, optional)

**Response**: Paginated list of submissions

---

## Field Descriptions

### Assignment Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `type` | enum | Yes | Must be `"assignment"` for file-based assignments |
| `title` | string | Yes | Assignment title (max 255 chars) |
| `description` | string | No | Detailed instructions |
| `unit_id` | integer | Yes | ID of the unit |
| `order` | integer | No | Display order (auto-generated if not provided) |
| `submission_type` | enum | Yes | `file`, `text`, `link`, `mixed` (assignment must use `file` or `mixed`) |
| `max_score` | integer | No | Maximum score (default: 100, max: 1000) |
| `passing_grade` | decimal | No | Minimum score to pass (0-100, default: 60) |
| `status` | enum | No | `draft`, `published`, `archived` (default: `draft`) |
| `review_mode` | enum | No | Must be `manual` for assignments |
| `time_limit_minutes` | integer | No | Time limit in minutes (min: 1) |
| `attachments` | array | No | Files to attach (max 5 files, 10MB each) |

### Submission Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Submission ID |
| `assignment_id` | integer | Assignment ID |
| `user_id` | integer | Student ID |
| `attempt_number` | integer | Attempt number (1, 2, 3, ...) |
| `status` | enum | `draft`, `submitted`, `graded`, `missing` |
| `score` | decimal | Score given by instructor |
| `feedback` | string | Instructor feedback |
| `submitted_at` | datetime | When student submitted |
| `graded_at` | datetime | When instructor graded |
| `graded_by` | integer | Instructor ID who graded |

---

## Enum Values

### submission_type
- `file` - File upload only
- `text` - Text input only
- `link` - URL/link submission
- `mixed` - Combination of file, text, and link

**Note**: Assignments (type=assignment) must use `file` or `mixed` submission type.

### status (Assignment)
- `draft` - Not visible to students
- `published` - Visible to students
- `archived` - Hidden but not deleted

### status (Submission)
- `draft` - Student is still working on it
- `submitted` - Student has submitted, waiting for grading
- `graded` - Instructor has graded
- `missing` - Student didn't submit (auto-marked)

---

## Authorization Rules

### View Assignment
- **Published**: All enrolled students + instructors + admins
- **Draft**: Only instructors and admins assigned to the course

### Create/Update/Delete Assignment
- Superadmin: All assignments
- Admin: Assignments in courses they're assigned to
- Instructor: Assignments in their own courses

### Submit Assignment
- Students enrolled in the course
- Assignment must be published
- Prerequisites must be met

### Grade Submission
- Superadmin: All submissions
- Admin: Submissions in courses they're assigned to
- Instructor: Submissions in their own courses

---

## Common Errors

### 403 Forbidden
```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Assignment not found.",
  "errors": null
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "unit_id": ["The selected unit id is invalid."]
  }
}
```

---

## Notes

1. **Unlimited Attempts**: Students can submit multiple times. Each submission creates a new attempt.
2. **File Uploads**: Use `multipart/form-data` when uploading files
3. **Grading**: All assignments require manual grading by instructors
4. **Prerequisites**: Check prerequisites before allowing submission
5. **Time Limits**: Optional time limit for completing the assignment
6. **Attachments**: Instructors can attach reference materials (max 5 files, 10MB each)
