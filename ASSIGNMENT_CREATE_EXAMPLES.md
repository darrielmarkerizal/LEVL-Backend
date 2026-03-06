# Assignment Creation Examples

## Endpoint
```
POST /api/v1/courses/{course_slug}/units/{unit_slug}/assignments
```

## Authentication
Required: `Bearer Token` (Admin, Instructor, or Superadmin)

---

## Example 1: Simple Assignment (File Upload)

### JSON (application/json)
```json
{
  "type": "assignment",
  "title": "Project Proposal Document",
  "description": "Create a comprehensive project proposal document including problem statement, objectives, methodology, and timeline.",
  "unit_id": 1,
  "submission_type": "file",
  "max_score": 100,
  "passing_grade": 70,
  "status": "draft",
  "review_mode": "manual"
}
```

### Form Data (multipart/form-data)
```
type: assignment
title: Project Proposal Document
description: Create a comprehensive project proposal document including problem statement, objectives, methodology, and timeline.
unit_id: 1
submission_type: file
max_score: 100
passing_grade: 70
status: draft
review_mode: manual
```

---

## Example 2: Assignment with File Attachments

### Form Data (multipart/form-data)
```
type: assignment
title: Database Design Assignment
description: Design a normalized database schema for an e-commerce system. Include ER diagram and SQL scripts.
unit_id: 2
submission_type: file
max_score: 100
passing_grade: 75
status: published
review_mode: manual
time_limit_minutes: 120
order: 1
attachments[0]: [FILE] requirements.pdf
attachments[1]: [FILE] template.docx
attachments[2]: [FILE] example_schema.png
```

### cURL Example
```bash
curl -X POST "https://api.example.com/api/v1/courses/web-dev-101/units/database-basics/assignments" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: multipart/form-data" \
  -F "type=assignment" \
  -F "title=Database Design Assignment" \
  -F "description=Design a normalized database schema for an e-commerce system." \
  -F "unit_id=2" \
  -F "submission_type=file" \
  -F "max_score=100" \
  -F "passing_grade=75" \
  -F "status=published" \
  -F "review_mode=manual" \
  -F "time_limit_minutes=120" \
  -F "order=1" \
  -F "attachments[0]=@/path/to/requirements.pdf" \
  -F "attachments[1]=@/path/to/template.docx" \
  -F "attachments[2]=@/path/to/example_schema.png"
```

---

## Example 3: Quiz-type Assignment (with Questions)

### JSON (application/json)
```json
{
  "type": "quiz",
  "title": "JavaScript Fundamentals Quiz",
  "description": "Test your knowledge of JavaScript basics including variables, functions, and control structures.",
  "unit_id": 3,
  "submission_type": "text",
  "max_score": 100,
  "passing_grade": 80,
  "status": "draft",
  "review_mode": "auto",
  "time_limit_minutes": 30,
  "randomization_type": "questions",
  "question_bank_count": 10,
  "order": 2
}
```

**Note**: After creating a quiz-type assignment, you need to add questions separately using the questions endpoint.

---

## Example 4: Mixed Submission Type Assignment

### JSON (application/json)
```json
{
  "type": "assignment",
  "title": "Web Application Development Project",
  "description": "Build a full-stack web application with authentication, CRUD operations, and responsive design. Submit both code repository link and documentation.",
  "unit_id": 4,
  "submission_type": "mixed",
  "max_score": 200,
  "passing_grade": 140,
  "status": "published",
  "review_mode": "manual",
  "time_limit_minutes": 10080,
  "order": 3
}
```

---

## Field Descriptions

### Required Fields

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `type` | enum | Assignment type | `assignment` or `quiz` |
| `title` | string | Assignment title (max 255 chars) | "Project Proposal" |
| `unit_id` | integer | ID of the unit this assignment belongs to | `1` |
| `submission_type` | enum | How students submit | `file`, `text`, `link`, `mixed` |

### Optional Fields

| Field | Type | Description | Default | Example |
|-------|------|-------------|---------|---------|
| `description` | string | Detailed instructions | null | "Create a comprehensive..." |
| `order` | integer | Display order in unit | Auto-generated | `1` |
| `max_score` | integer | Maximum score (1-1000) | 100 | `100` |
| `passing_grade` | decimal | Minimum score to pass (0-100) | 60 | `70.5` |
| `status` | enum | Publication status | `draft` | `draft`, `published` |
| `time_limit_minutes` | integer | Time limit in minutes | null | `120` |
| `review_mode` | enum | Grading method | `manual` | `manual`, `auto` |
| `randomization_type` | enum | Question randomization (quiz only) | null | `none`, `questions`, `answers`, `both` |
| `question_bank_count` | integer | Number of questions to show (quiz only) | null | `10` |
| `attachments` | array | Files to attach (max 5 files) | [] | See form-data example |

---

## Enum Values

### type
- `assignment` - File upload assignment
- `quiz` - Question-based quiz

### submission_type
- `file` - File upload only
- `text` - Text input only
- `link` - URL/link submission
- `mixed` - Combination of file and text

### status
- `draft` - Not visible to students
- `published` - Visible to students

### review_mode
- `manual` - Instructor grades manually
- `auto` - System grades automatically (quiz only)

### randomization_type (quiz only)
- `none` - No randomization
- `questions` - Randomize question order
- `answers` - Randomize answer order
- `both` - Randomize both questions and answers

---

## Validation Rules

### Assignment Type Constraints
When `type = "assignment"`:
- `submission_type` must be `file` or `mixed`
- `review_mode` must be `manual`
- Cannot have `randomization_type`
- Cannot have `question_bank_count`

### Quiz Type Constraints
When `type = "quiz"`:
- Can use any `submission_type`
- Can use `auto` or `manual` review_mode
- Can have `randomization_type`
- Can have `question_bank_count`

### File Attachments
- Maximum 5 files
- Allowed types: pdf, doc, docx, xls, xlsx, ppt, pptx, zip, jpg, jpeg, png, webp
- Maximum size per file: 10MB (10240 KB)

---

## Response Example

### Success (201 Created)
```json
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "id": 42,
    "type": "assignment",
    "title": "Project Proposal Document",
    "description": "Create a comprehensive project proposal...",
    "unit_id": 1,
    "order": 1,
    "submission_type": "file",
    "max_score": 100,
    "passing_grade": 70,
    "status": "draft",
    "review_mode": "manual",
    "time_limit_minutes": null,
    "randomization_type": null,
    "question_bank_count": null,
    "created_by": 5,
    "created_at": "2026-03-06T10:30:00.000000Z",
    "updated_at": "2026-03-06T10:30:00.000000Z",
    "attachments": []
  },
  "meta": null,
  "errors": null
}
```

### Error (422 Validation Error)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": null,
  "meta": null,
  "errors": {
    "title": ["The title field is required."],
    "unit_id": ["The selected unit id is invalid."],
    "submission_type": ["Assignment type must use file or mixed submission type."]
  }
}
```

---

## Postman Collection Example

### Request Setup
1. Method: `POST`
2. URL: `{{base_url}}/api/v1/courses/{{course_slug}}/units/{{unit_slug}}/assignments`
3. Headers:
   - `Authorization: Bearer {{token}}`
   - `Content-Type: application/json` (for JSON)
   - OR `Content-Type: multipart/form-data` (for file uploads)

### Body (JSON)
```json
{
  "type": "assignment",
  "title": "{{$randomLoremWords}}",
  "description": "{{$randomLoremParagraph}}",
  "unit_id": 1,
  "submission_type": "file",
  "max_score": 100,
  "passing_grade": 70,
  "status": "draft",
  "review_mode": "manual"
}
```

### Body (Form-data for file uploads)
| Key | Type | Value |
|-----|------|-------|
| type | text | assignment |
| title | text | Database Design Assignment |
| description | text | Design a normalized database schema... |
| unit_id | text | 2 |
| submission_type | text | file |
| max_score | text | 100 |
| passing_grade | text | 75 |
| status | text | published |
| review_mode | text | manual |
| attachments[0] | file | [Select File] |
| attachments[1] | file | [Select File] |

---

## Common Errors

### 1. Invalid Unit ID
```json
{
  "errors": {
    "unit_id": ["The selected unit id is invalid."]
  }
}
```
**Solution**: Ensure the unit exists and belongs to the specified course.

### 2. Invalid Submission Type for Assignment
```json
{
  "errors": {
    "submission_type": ["Assignment type must use file or mixed submission type."]
  }
}
```
**Solution**: Use `file` or `mixed` for assignment type.

### 3. File Too Large
```json
{
  "errors": {
    "attachments.0": ["The attachments.0 must not be greater than 10240 kilobytes."]
  }
}
```
**Solution**: Reduce file size to under 10MB.

### 4. Too Many Attachments
```json
{
  "errors": {
    "attachments": ["The attachments must not have more than 5 items."]
  }
}
```
**Solution**: Upload maximum 5 files.

### 5. Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized",
  "errors": null
}
```
**Solution**: Ensure you have Admin, Instructor, or Superadmin role and are assigned to the course.

---

## Tips

1. **Start with Draft**: Create assignments with `status: "draft"` first, then publish after review
2. **Use Order**: Set `order` to control the sequence of assignments in the unit
3. **Time Limits**: Use `time_limit_minutes` for timed assessments (e.g., 120 for 2 hours)
4. **Passing Grade**: Set realistic passing grades (typically 60-80%)
5. **Attachments**: Include reference materials, templates, or examples as attachments
6. **Quiz Setup**: For quiz-type assignments, create the assignment first, then add questions
7. **Mixed Submissions**: Use `mixed` type when students need to submit both files and text/links
