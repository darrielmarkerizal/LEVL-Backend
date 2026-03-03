# Controller Refactoring Plan - Service/Repository Pattern Enforcement

## Tanggal: 2026-03-03

## Pelanggaran yang Ditemukan

### 1. CourseController - VIOLATIONS

#### ❌ Line 64-65: `$course->load('admins')`
```php
public function update(CourseRequest $request, Course $course)
{
    $course->load('admins');  // ❌ Model manipulation in controller
    $this->authorize('update', $course);
```
**Fix:** Move to service or use eager loading in route model binding

#### ❌ Line 78, 88, 98, 108, 118, 128: Repeated `$course->load('admins')`
All methods that need admins should load via service or route binding

#### ❌ Line 143-144: Type casting and query parameter extraction
```php
$userId = auth('api')->id();
$perPage = (int) $request->query('per_page', 15);
```
**Fix:** Move parameter extraction to service

---

### 2. UnitController - VIOLATIONS

#### ❌ Line 27-29: Creating dummy model for authorization
```php
$dummyUnit = new Unit;
$dummyUnit->course_id = $course->id;
$dummyUnit->setRelation('course', $course);
$this->authorize('view', $dummyUnit);
```
**Fix:** Create dedicated authorization method in service or policy

#### ❌ Line 32-35: Query parameter extraction and type casting
```php
$paginator = $this->service->paginate(
    $course->id,
    $request->query('filter', []),
    (int) $request->query('per_page', 15)
);
```
**Fix:** Pass entire request to service, let service handle extraction

---

### 3. LessonController - VIOLATIONS

#### ❌ Line 36-38: Query parameter extraction
```php
$paginator = $this->service->paginate(
    $unit->id,
    $request->query('filter', []),
    (int) $request->query('per_page', 15)
);
```
**Fix:** Pass request to service

#### ❌ Line 107-112: Filter manipulation
```php
$filters = $request->query('filter', []);
if ($request->has('search')) {
    $filters['search'] = $request->query('search');
}
```
**Fix:** Move to service layer

---

### 4. ProgressController - VIOLATIONS

#### ❌ Line 23-27: Complex authorization logic
```php
$targetId = (int) ($request->query('user_id') ?? auth('api')->id());
if ($targetId !== auth('api')->id()) {
    $this->authorize('viewAny', [\Modules\Enrollments\Models\Enrollment::class, $course]);
}
```
**Fix:** Move to service method `getProgressForUser($course, $targetId, $requestingUserId)`

---

### 5. QuizSubmissionController - VIOLATIONS

#### ❌ Line 40-48: Database query in controller
```php
$existingDraft = QuizSubmission::where('quiz_id', $quiz->id)
    ->where('user_id', $userId)
    ->where('status', \Modules\Learning\Enums\QuizSubmissionStatus::Draft)
    ->first();

if ($existingDraft) {
    return $this->error(__('messages.quiz_submissions.already_started'), [
        'submission_id' => $existingDraft->id,
    ], 422);
}
```
**Fix:** Move to service method `startQuiz()` - service should handle this check

#### ❌ Line 62-70: Include parameter handling
```php
$includeParam = request()->get('include', '');
if (! empty($includeParam)) {
    $user = auth('api')->user();
    $allowedIncludes = $this->includeAuthorizer->getAllowedIncludesForQueryBuilder($user, $submission);

    $submission = \Spatie\QueryBuilder\QueryBuilder::for(\Modules\Learning\Models\QuizSubmission::class)
        ->where('id', $submission->id)
        ->allowedIncludes($allowedIncludes)
        ->firstOrFail();
}
```
**Fix:** Move to service method `getSubmissionWithIncludes($submission, $user)`

#### ❌ Line 79-95: Pagination logic for students
```php
if ($user && $user->hasRole('Student')) {
    $questions = $this->submissionService->listQuestions($submission, $user->id);
    $total = $questions->count();

    if ($page < 1 || $page > $total) {
        return $this->error(__('messages.quiz_submissions.invalid_page'), [], 404);
    }

    $question = $questions->get($page - 1);

    return $this->success([
        'data' => new \Modules\Learning\Http\Resources\QuizQuestionResource($question),
        'meta' => [
            'pagination' => [
                'current_page' => $page,
                'total' => $total,
                'has_next' => $page < $total,
                'has_prev' => $page > 1,
            ],
        ],
    ]);
}
```
**Fix:** Move to service method `getQuestionsForStudent($submission, $userId, $page)`

---

### 6. SubmissionController - VIOLATIONS

#### ❌ Line 115-133: Pagination logic for students (duplicate of quiz)
Same issue as QuizSubmissionController - move to service

#### ❌ Line 137-139: Conditional pagination
```php
if ($request->hasAny(['page', 'per_page'])) {
    return $this->paginateResponse($this->service->getSubmissionQuestionsPaginated($submission, (int) $request->query('per_page', 1))->through(fn ($i) => new QuestionResource($i)));
}
```
**Fix:** Service should handle this decision

#### ❌ Line 157-159: Validation logic
```php
if ($submission->assignment_id !== $assignment->id) {
    return $this->error(__('messages.submissions.not_found'), [], 404);
}
```
**Fix:** Move to service validation method

---

### 7. QuizController - VIOLATIONS

#### ❌ Line 67-73: Role-based enrichment logic
```php
if ($user && $user->hasRole('Student')) {
    $enriched = $this->enrichmentService->enrichDetailForStudent($quizWithRelations, $user->id);
    return $this->success(QuizResource::make($enriched));
}

return $this->success(QuizResource::make($quizWithRelations));
```
**Fix:** Service should return enriched data based on user role

#### ❌ Line 119-121: Role check for blocking access
```php
if ($user && $user->hasRole('Student')) {
    return $this->error(__('messages.quizzes.must_start_first'), [], 403);
}
```
**Fix:** Move to policy or service

#### ❌ Line 133-135: Validation logic
```php
if ($question->quiz_id !== $quiz->id) {
    return $this->error(__('messages.questions.not_found'), [], 404);
}
```
**Fix:** Move to service validation

#### ❌ Line 157-158: Request validation in controller
```php
$ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
```
**Fix:** Create FormRequest class

---

## Refactoring Strategy

### Phase 1: Move Query Logic to Services
1. Create service methods for all database queries
2. Remove all Model::where() calls from controllers
3. Move all query parameter extraction to services

### Phase 2: Move Validation Logic to Services
1. Create validation methods in services
2. Remove if/else validation from controllers
3. Services should throw exceptions for invalid states

### Phase 3: Move Authorization Logic
1. Complex authorization should be in policies
2. Simple checks can stay in controller
3. Authorization that requires data fetching should be in service

### Phase 4: Standardize Request Handling
1. All query parameter extraction in services
2. All type casting in services
3. Controllers only pass Request object to service

### Phase 5: Remove Model Manipulation
1. No `$model->load()` in controllers
2. No `$model->setRelation()` in controllers
3. All eager loading via service or route binding

---

## Target Controller Structure

```php
public function index(Request $request, Model $parent): JsonResponse
{
    $this->authorize('viewAny', [ChildModel::class, $parent]);
    $result = $this->service->listForIndex($parent, $request, auth('api')->user());
    
    return $this->paginateResponse($result, 'messages.success');
}

public function store(StoreRequest $request, Model $parent): JsonResponse
{
    $this->authorize('create', [ChildModel::class, $parent]);
    $created = $this->service->create($parent, $request->validated(), auth('api')->user());
    
    return $this->created(new Resource($created), __('messages.created'));
}

public function show(Model $parent, Model $child): JsonResponse
{
    $this->authorize('view', $child);
    $result = $this->service->getForUser($child, auth('api')->user());
    
    return $this->success(new Resource($result));
}

public function update(UpdateRequest $request, Model $parent, Model $child): JsonResponse
{
    $this->authorize('update', $child);
    $updated = $this->service->update($child, $request->validated(), auth('api')->user());
    
    return $this->success(new Resource($updated), __('messages.updated'));
}

public function destroy(Model $parent, Model $child): JsonResponse
{
    $this->authorize('delete', $child);
    $this->service->delete($child);
    
    return $this->success([], __('messages.deleted'));
}
```

**Maximum 5-7 lines per method!**

---

## Priority Order

### High Priority (Breaking Service Pattern)
1. ✅ QuizSubmissionController::start() - Database query in controller
2. ✅ QuizSubmissionController::show() - QueryBuilder in controller
3. ✅ QuizSubmissionController::listQuestions() - Pagination logic
4. ✅ SubmissionController::listQuestions() - Pagination logic
5. ✅ CourseController - Repeated `$course->load('admins')`

### Medium Priority (Logic in Controller)
6. ProgressController::show() - Authorization logic
7. UnitController::index() - Dummy model creation
8. QuizController::listQuestions() - Role check
9. QuizController::show() - Enrichment logic

### Low Priority (Parameter Handling)
10. All query parameter extraction
11. All type casting
12. All filter manipulation

---

## Implementation Plan

1. Create new service methods for each violation
2. Update controllers to use new service methods
3. Run tests to ensure no breaking changes
4. Run Pint to format code
5. Reload Octane

---

## Success Criteria

- ✅ No database queries in controllers
- ✅ No Model manipulation in controllers
- ✅ No business logic in controllers
- ✅ No if/else for domain rules in controllers
- ✅ Maximum 5-7 lines per controller method
- ✅ All logic testable via service layer
- ✅ Controllers are pure coordinators
