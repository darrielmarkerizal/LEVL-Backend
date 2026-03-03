# Assignment & Quiz Refactoring - Unit Level Content

## Overview

Refactoring Assignment dan Quiz dari polymorphic assignable menjadi konten independen yang setara dengan Lesson di bawah Unit.

## Struktur Lama (Polymorphic)

```
Course
  └── Unit
       └── Lesson
            ├── Assignment (via assignable_type/assignable_id)
            └── Quiz (via assignable_type/assignable_id)
```

**Masalah:**
- Assignment/Quiz bisa di-assign ke Course, Unit, atau Lesson (polymorphic)
- Sulit menentukan urutan konten untuk prerequisite checking
- Tidak konsisten dengan PrerequisiteService yang mengharapkan order-based

## Struktur Baru (Unit Level)

```
Course
  └── Unit
       ├── Lesson (order: 1, 2, 3...)
       ├── Assignment (order: 4, 5...)
       └── Quiz (order: 6, 7...)
```

**Keuntungan:**
- Semua konten (Lesson, Assignment, Quiz) setara di level Unit
- Order-based prerequisite checking yang konsisten
- Lebih mudah untuk menentukan urutan penyelesaian
- Sinkron dengan PrerequisiteService

## Database Changes

### Migration: `2026_03_03_000001_refactor_assignments_quizzes_to_unit_level.php`

**Assignments Table:**
- ✅ Rename `lesson_id` → `unit_id`
- ✅ Add `order` field (integer, default 0)
- ✅ Remove `assignable_type` and `assignable_id`
- ✅ Add foreign key `unit_id` → `units.id`
- ✅ Add index on `(unit_id, order)`

**Quizzes Table:**
- ✅ Rename `lesson_id` → `unit_id`
- ✅ Add `order` field (integer, default 0)
- ✅ Remove `assignable_type` and `assignable_id`
- ✅ Add foreign key `unit_id` → `units.id`
- ✅ Add index on `(unit_id, order)`

## Model Changes Required

### Assignment Model
- [ ] Update `$fillable` - remove assignable fields, add unit_id and order
- [ ] Remove `assignable()` morphTo relation
- [ ] Add `unit()` belongsTo relation
- [ ] Update `getScopeTypeAttribute()` - always return 'unit'
- [ ] Remove `getUnitSlug()` - use `unit.slug` directly
- [ ] Update `getCourseId()` - use `unit.course_id`
- [ ] Update scopes: `forUnit()`, `forCourse()`
- [ ] Add scope: `ordered()` for order by

### Quiz Model
- [ ] Update `$fillable` - remove assignable fields, add unit_id and order
- [ ] Remove `assignable()` morphTo relation
- [ ] Add `unit()` belongsTo relation
- [ ] Update `getScopeTypeAttribute()` - always return 'unit'
- [ ] Remove `getUnitSlug()` - use `unit.slug` directly
- [ ] Update `getCourseId()` - use `unit.course_id`
- [ ] Update scopes: `forUnit()`, `forCourse()`
- [ ] Add scope: `ordered()` for order by

## Service Changes Required

### PrerequisiteService
- [ ] Simplify `getAssignmentUnitId()` - just return `assignment->unit_id`
- [ ] Simplify `getQuizUnitId()` - just return `quiz->unit_id`
- [ ] Update `getUnitContentBeforeAssignment()` - use order-based filtering
- [ ] Update `getUnitContentBeforeQuiz()` - use order-based filtering
- [ ] Update `getUnitContentOrder()` - merge all content by order

### AssignmentService
- [ ] Update `create()` - require `unit_id` and `order`
- [ ] Remove polymorphic scope handling
- [ ] Update validation for unit_id

### QuizService
- [ ] Update `create()` - require `unit_id` and `order`
- [ ] Remove polymorphic scope handling
- [ ] Update validation for unit_id

## Repository Changes Required

### AssignmentRepository
- [ ] Update `forUnit()` scope - simpler query
- [ ] Update `forCourse()` scope - join through units
- [ ] Remove `forLesson()` scope (no longer needed)

### QuizRepository
- [ ] Update `forUnit()` scope - simpler query
- [ ] Update `forCourse()` scope - join through units
- [ ] Remove `forLesson()` scope (no longer needed)

## Resource Changes Required

### AssignmentResource
- [ ] Update `unit_slug` - use `$this->unit->slug`
- [ ] Remove conditional logic for assignable

### QuizResource
- [ ] Update `unit_slug` - use `$this->unit->slug`
- [ ] Remove conditional logic for assignable

## Enrichment Service Changes

### AssignmentEnrichmentService
- [ ] Update to load `unit` relation instead of `lesson.unit`
- [ ] Simplify `unit_slug` retrieval

### QuizEnrichmentService
- [ ] Update to load `unit` relation instead of `lesson.unit`
- [ ] Simplify `unit_slug` retrieval

## Request Validation Changes

### StoreAssignmentRequest / UpdateAssignmentRequest
- [ ] Remove `assignable_type` and `assignable_id` validation
- [ ] Add `unit_id` validation (required, exists:units,id)
- [ ] Add `order` validation (required, integer, min:1)

### StoreQuizRequest / UpdateQuizRequest
- [ ] Remove `assignable_type` and `assignable_id` validation
- [ ] Add `unit_id` validation (required, exists:units,id)
- [ ] Add `order` validation (required, integer, min:1)

## Seeder Changes Required

### LearningContentSeeder
- [ ] Update to create assignments with `unit_id` and `order`
- [ ] Update to create quizzes with `unit_id` and `order`
- [ ] Ensure proper ordering: lessons first, then assignments/quizzes

### SequentialProgressSeeder
- [ ] Update to handle unit-level content ordering
- [ ] Process content by order field

## Controller Changes

### AssignmentController
- [ ] Update `index()` - filter by unit_id from course
- [ ] Update `store()` - validate unit_id

### QuizController
- [ ] Update `index()` - filter by unit_id from course
- [ ] Update `store()` - validate unit_id

## API Response Changes

**Before:**
```json
{
  "lesson_slug": "lesson-1",
  "unit_slug": "unit-1",
  "scope_type": "lesson"
}
```

**After:**
```json
{
  "unit_slug": "unit-1",
  "order": 5,
  "scope_type": "unit"
}
```

## Testing Checklist

- [ ] Test assignment creation with unit_id
- [ ] Test quiz creation with unit_id
- [ ] Test prerequisite checking with order-based logic
- [ ] Test unit content listing (lessons + assignments + quizzes ordered)
- [ ] Test progress tracking
- [ ] Test API responses

## Migration Path

1. ✅ Run migration to update database structure
2. [ ] Update all models
3. [ ] Update all services
4. [ ] Update all repositories
5. [ ] Update all resources
6. [ ] Update all requests
7. [ ] Update all seeders
8. [ ] Run `php artisan migrate:fresh --seed`
9. [ ] Test all endpoints
10. [ ] Update documentation

## Benefits

1. **Simpler Architecture** - No more polymorphic complexity
2. **Consistent Ordering** - All content uses same order field
3. **Better Prerequisites** - Order-based checking is straightforward
4. **Easier to Understand** - Clear hierarchy: Course → Unit → Content
5. **Sync with PrerequisiteService** - Already expects this structure

## Next Steps

1. Update Assignment model
2. Update Quiz model
3. Update PrerequisiteService
4. Update all related services and repositories
5. Update seeders
6. Test thoroughly
