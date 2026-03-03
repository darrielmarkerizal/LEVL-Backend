# Quiz Flow Requirements & Response Consistency

## Problem Statement
1. Response tidak konsisten antara list dan detail view
2. Quiz detail menampilkan questions sebelum student start quiz
3. Flow quiz tidak jelas: harus start → answer (draft) → submit → grading

## Required Quiz Flow

### 1. Student View Quiz Detail (Before Start)
**Endpoint:** `GET /quizzes/{id}`

**Response Should Include:**
```json
{
  "id": 1122,
  "title": "Quiz Title",
  "description": "Quiz description",
  "passing_grade": "79.00",
  "max_score": "100.00",
  "max_attempts": 3,
  "time_limit_minutes": 44,
  "retake_enabled": false,
  "auto_grading": true,
  "review_mode": "immediate",
  "is_locked": true,
  "lesson_slug": "lesson-slug",
  "unit_slug": "unit-slug",
  "questions_count": 4,
  "scope_type": "lesson",
  "created_at": "2026-03-02T08:48:30.000000Z"
}
```

**Should NOT Include:**
- ❌ `questions` array (belum start)
- ❌ `correct_answers` (belum start)

### 2. Student Start Quiz
**Endpoint:** `POST /quizzes/{id}/submissions/start`

**Response Should Include:**
```json
{
  "submission_id": 123,
  "quiz_id": 1122,
  "status": "in_progress",
  "started_at": "2026-03-02T10:00:00Z",
  "time_limit_minutes": 44,
  "deadline": "2026-03-02T10:44:00Z",
  "questions": [
    {
      "id": 4485,
      "type": "multiple_choice",
      "content": "Question text",
      "options": ["A", "B", "C", "D"],
      "max_score": "25.00",
      "order": 1
    }
  ]
}
```

**Notes:**
- Questions baru muncul setelah start
- Tidak ada correct_answer
- Submission dibuat dengan status "in_progress"

### 3. Student Answer Questions (Draft)
**Endpoint:** `PUT /quizzes/{id}/submissions/{submission_id}/answers`

**Request:**
```json
{
  "answers": [
    {
      "question_id": 4485,
      "content": "Answer text or selected option"
    }
  ]
}
```

**Response:**
```json
{
  "submission_id": 123,
  "status": "in_progress",
  "answers_saved": 1,
  "total_questions": 4
}
```

**Notes:**
- Jawaban tersimpan sebagai draft
- Bisa diupdate berkali-kali sebelum submit
- Tidak ada scoring

### 4. Student Submit Quiz
**Endpoint:** `POST /quizzes/{id}/submissions/{submission_id}/submit`

**Response (Auto Grading = true, No Essay):**
```json
{
  "submission_id": 123,
  "status": "graded",
  "grading_status": "final",
  "score": "75.00",
  "final_score": "75.00",
  "is_passed": false,
  "passing_grade": "79.00",
  "submitted_at": "2026-03-02T10:30:00Z",
  "answers": [
    {
      "question_id": 4485,
      "content": "Answer",
      "score": "25.00",
      "is_correct": true,
      "feedback": "Correct!"
    }
  ]
}
```

**Response (Auto Grading = false OR Has Essay):**
```json
{
  "submission_id": 123,
  "status": "submitted",
  "grading_status": "pending_manual",
  "score": null,
  "final_score": null,
  "is_passed": null,
  "submitted_at": "2026-03-02T10:30:00Z",
  "message": "Your submission is being reviewed by instructor"
}
```

## Assignment vs Quiz Consistency

### Assignment Detail Response
```json
{
  "id": 1,
  "title": "Assignment Title",
  "description": "Description",
  "submission_type": "file",
  "max_score": 100,
  "max_attempts": 5,
  "retake_enabled": true,
  "review_mode": "immediate",
  "is_locked": false,
  "lesson_slug": "lesson-slug",
  "unit_slug": "unit-slug",
  "course_slug": "course-slug",
  "attachments": [],
  "created_at": "2026-03-02T08:48:30.000000Z"
}
```

### Quiz Detail Response (Consistent with Assignment)
```json
{
  "id": 1122,
  "title": "Quiz Title",
  "description": "Description",
  "passing_grade": "79.00",
  "max_score": "100.00",
  "max_attempts": 3,
  "time_limit_minutes": 44,
  "retake_enabled": false,
  "auto_grading": true,
  "review_mode": "immediate",
  "is_locked": true,
  "lesson_slug": "lesson-slug",
  "unit_slug": "unit-slug",
  "questions_count": 4,
  "scope_type": "lesson",
  "created_at": "2026-03-02T08:48:30.000000Z"
}
```

## Field Consistency Rules

### Common Fields (Both Assignment & Quiz)
1. id, title, description
2. max_score, max_attempts
3. retake_enabled, review_mode
4. is_locked (for student)
5. lesson_slug, unit_slug
6. created_at

### Quiz-Specific Fields
- passing_grade
- time_limit_minutes
- auto_grading
- questions_count
- scope_type

### Assignment-Specific Fields
- submission_type
- course_slug
- attachments

## Implementation Tasks

### 1. Update QuizResource
- [ ] Add `is_locked` field for student
- [ ] Remove `questions` from detail view (student)
- [ ] Add `lesson_slug`, `unit_slug`
- [ ] Consistent field ordering

### 2. Update AssignmentResource
- [ ] Ensure `is_locked` exists
- [ ] Consistent field ordering with Quiz
- [ ] Match common fields

### 3. Quiz Flow Endpoints
- [ ] Ensure `/start` endpoint exists
- [ ] Ensure `/answers` endpoint for draft
- [ ] Ensure `/submit` endpoint
- [ ] Handle auto_grading logic

### 4. Response Consistency
- [ ] List view: minimal fields
- [ ] Detail view: full fields (no questions for quiz)
- [ ] After start: questions appear
- [ ] After submit: results based on auto_grading

## Testing Checklist
- [ ] Quiz list shows is_locked
- [ ] Quiz detail shows is_locked, no questions
- [ ] Start quiz returns questions
- [ ] Answer saves as draft
- [ ] Submit with auto_grading shows score immediately
- [ ] Submit with essay shows pending status
- [ ] Assignment detail has same common fields as quiz
