# Learning Module Resource Refactoring Plan

## Objectives
1. Standardize field ordering across all resources
2. Remove unnecessary fields for student role
3. Improve readability and consistency

## Field Ordering Standard

### Basic Info (Always First)
- id
- title
- description (if applicable)

### Core Data
- Type/status fields
- Score/grade fields
- Attempt/submission fields

### Metadata
- Timestamps (created_at, updated_at)
- Relations (user, creator, etc)

### Student vs Instructor Fields

**Fields to REMOVE for Students:**
- Internal IDs (user_id, enrollment_id, created_by, assignable_id, assignable_type)
- Admin fields (status for published content, is_available)
- Technical fields (question_set, state)

**Fields to KEEP for Students:**
- Content data (title, description, questions)
- Progress data (score, status, attempts)
- Access control (is_locked, can_retake)
- Metadata (created_at, slugs for navigation)

## Resources to Refactor

### High Priority (Student-facing)
1. ✅ AssignmentEnrichmentService (already done)
2. ✅ QuizEnrichmentService (already done)
3. SubmissionResource
4. SubmissionDetailResource
5. QuizSubmissionResource
6. AssignmentResource (detail view)
7. QuizResource (detail view)

### Medium Priority
8. AnswerResource
9. AnswerDetailResource
10. QuestionResource
11. QuizQuestionResource

### Low Priority (Admin-only)
12. OverrideResource
13. AssignmentIndexResource (not used directly)
14. QuizIndexResource (not used directly)

## Implementation Strategy

1. Create separate methods for student vs instructor in resources
2. Use `$request->user()->hasRole('Student')` to determine which fields to return
3. Maintain backward compatibility for instructor/admin views
4. Update enrichment services if needed

## Next Steps
1. Refactor SubmissionResource & SubmissionDetailResource
2. Refactor QuizSubmissionResource
3. Refactor AssignmentResource & QuizResource detail views
4. Test all endpoints with student and instructor roles
