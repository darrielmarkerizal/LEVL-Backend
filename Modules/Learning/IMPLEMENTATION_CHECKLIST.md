# Assignment vs Quiz Implementation Checklist

## ✅ Completed

### 1. Database
- [x] Migration: Add `type` enum field to `assignments` table
- [x] Migration executed successfully

### 2. Model
- [x] Enum: `AssignmentType` created with `Assignment` and `Quiz` cases
- [x] Model: Added `type` cast to `AssignmentType` enum
- [x] Model: Added helper methods (`isAssignment()`, `isQuiz()`)
- [x] Model: Added query scopes (`assignments()`, `quizzes()`, `ofType()`)

### 3. Translations
- [x] English: `assignment_type` enum labels
- [x] Indonesian: `assignment_type` enum labels

### 4. Request Validation
- [x] `StoreAssignmentRequest`: Added `type` field validation
- [x] `StoreAssignmentRequest`: Added conditional validation (assignment must use file/mixed)
- [x] `UpdateAssignmentRequest`: Added `type` field validation

### 5. Documentation
- [x] Created `ASSIGNMENT_VS_QUIZ.md` with usage examples
- [x] Created this checklist

## 🔄 Optional/Future Updates

### DTOs (Optional - if you want strict typing)
- [ ] `CreateAssignmentDTO`: Add `type` property
- [ ] `UpdateAssignmentDTO`: Add `type` property

### Service Layer (Optional - for business logic)
- [ ] `AssignmentService`: Add validation for quiz must have questions
- [ ] `AssignmentService`: Add validation for assignment must not have questions
- [ ] `QuestionService`: Validate questions can only be added to quiz type

### Repository (Optional - for filtering)
- [ ] `AssignmentRepository`: Add methods to filter by type
  ```php
  public function getAssignments($filters);
  public function getQuizzes($filters);
  ```

### API Resources (Optional - for response formatting)
- [ ] `AssignmentResource`: Include `type` in response
- [ ] Add `type` to API documentation

### Frontend (Required when implementing UI)
- [ ] Create separate forms for Assignment vs Quiz
- [ ] Add type selector in create assignment page
- [ ] Filter assignments by type in list view
- [ ] Show different UI based on type

## 📝 Notes

### Current Behavior
- Default type is `assignment` for backward compatibility
- Existing assignments will be treated as `assignment` type
- No breaking changes to existing API

### Validation Rules

**For Assignment (`type='assignment'`):**
- `submission_type` must be `file` or `mixed`
- Should NOT have questions (not enforced yet, optional)
- Students upload files as submission

**For Quiz (`type='quiz'`):**
- Must have questions (not enforced yet, optional)
- `submission_type` is not relevant
- Students answer questions

### API Usage

**Create Assignment:**
```json
POST /api/v1/assignments
{
  "type": "assignment",
  "title": "Weekly Report",
  "description": "Submit your report",
  "submission_type": "file",
  "assignable_type": "Lesson",
  "assignable_slug": "lesson-1"
}
```

**Create Quiz:**
```json
POST /api/v1/assignments
{
  "type": "quiz",
  "title": "Chapter 1 Quiz",
  "description": "Answer all questions",
  "max_attempts": 3,
  "time_limit_minutes": 60,
  "assignable_type": "Lesson",
  "assignable_slug": "lesson-1"
}
```

**Filter by Type:**
```
GET /api/v1/courses/{slug}/assignments?filter[type]=quiz
GET /api/v1/courses/{slug}/assignments?filter[type]=assignment
```

## 🚀 Next Steps

1. **Test the API** - Create assignments and quizzes via API
2. **Update Frontend** - Add type selector and different forms
3. **Add Service Validation** (Optional) - Enforce business rules
4. **Update API Documentation** - Document the new `type` field

## 🔍 Testing Commands

```bash
# Run migrations
php artisan migrate

# Test creating assignment
curl -X POST /api/v1/assignments \
  -H "Content-Type: application/json" \
  -d '{"type":"assignment","title":"Test Assignment",...}'

# Test creating quiz
curl -X POST /api/v1/assignments \
  -H "Content-Type: application/json" \
  -d '{"type":"quiz","title":"Test Quiz",...}'

# Test filtering
curl /api/v1/courses/course-1/assignments?filter[type]=quiz
```
