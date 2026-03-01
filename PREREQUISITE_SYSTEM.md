# Prerequisite System Documentation (Order-Based)

## Overview

Sistem prerequisite otomatis berbasis urutan (`order` field) untuk kontrol akses berurutan konten pembelajaran. Tidak ada tabel prerequisite manual - semua prerequisite ditentukan otomatis berdasarkan urutan konten.

## Key Features

- **Automatic Order-Based**: Prerequisites ditentukan otomatis dari field `order`
- **No Manual Configuration**: Tidak perlu set prerequisite saat membuat konten
- **Sequential Access**: Content dengan `order=2` otomatis membutuhkan `order=1` selesai
- **Unit Sequential**: Unit harus diselesaikan berurutan (Unit 2 butuh Unit 1 selesai)
- **Mixed Content Support**: Lesson, Assignment, Quiz bisa dicampur dalam urutan apapun

## How It Works

### Lesson Prerequisites
- Lesson dengan `order=2` membutuhkan Lesson `order=1` selesai
- Lesson dengan `order=3` membutuhkan Lesson `order=1` dan `order=2` selesai
- Hanya berlaku dalam satu Unit yang sama

### Assignment/Quiz Prerequisites
- Assignment/Quiz dengan ID lebih tinggi membutuhkan semua content sebelumnya selesai
- "Content sebelumnya" = semua Lesson + Assignment/Quiz dengan ID lebih kecil dalam Unit yang sama
- Contoh: Assignment ID=5 membutuhkan semua Lesson + Assignment/Quiz ID<5 selesai

### Unit Prerequisites
- Unit dengan `order=2` membutuhkan Unit `order=1` selesai 100%
- Unit selesai = semua Lesson completed + semua Assignment/Quiz passed

## Prerequisite Variants Supported

Karena berbasis order, sistem mendukung urutan apapun:

```
Varian 1: L1(order=1) → A1(id=1) → L2(order=2) → Q1(id=1) → L3(order=3)
Varian 2: L1(order=1) → Q1(id=1) → A1(id=2) → L2(order=2) → Q2(id=3)
Varian 3: L1(order=1) → L2(order=2) → A1(id=1) → Q1(id=2) → A2(id=3)
... dan seterusnya
```

Urutan ditentukan oleh:
- Lesson: field `order` dalam tabel `lessons`
- Assignment/Quiz: field `id` (auto-increment)

## Database Schema

### lesson_completions
```sql
- id
- lesson_id (FK to lessons)
- user_id (FK to users)
- completed_at (timestamp)
- created_at, updated_at
- UNIQUE(lesson_id, user_id)
```

### Removed Tables
- `content_prerequisites` (DROPPED - tidak digunakan lagi)
- `lesson_prerequisites` (DROPPED)
- `assignment_prerequisites` (DROPPED)
- `quiz_prerequisites` (DROPPED)

## Model Relations

### Lesson Model
```php
public function completions(): HasMany
public function isCompletedBy(int $userId): bool
```

### Assignment Model
```php
public function submissions(): HasMany
public static function forUnit(int $unitId): Builder
```

### Quiz Model
```php
public function submissions(): HasMany
public static function forUnit(int $unitId): Builder
```

## Services

### PrerequisiteService

**Location**: `Modules/Schemes/app/Services/PrerequisiteService.php`

**Methods**:
- `checkLessonAccess(Lesson $lesson, int $userId): array` - Check lesson access by order
- `checkAssignmentAccess(Assignment $assignment, int $userId): array` - Check assignment access
- `checkQuizAccess(Quiz $quiz, int $userId): array` - Check quiz access
- `checkUnitAccess(Unit $unit, int $userId): array` - Check unit access
- `isUnitCompleted(Unit $unit, int $userId): bool` - Check unit completion
- `getUnitProgress(Unit $unit, int $userId): array` - Get progress percentage
- `getUnitContentOrder(Unit $unit): array` - Get content in order

**Response Format**:
```php
[
    'accessible' => true|false,
    'missing' => [
        ['type' => 'lesson|assignment|quiz', 'id' => 1, 'title' => '...', 'order' => 1],
        ...
    ]
]
```

### LessonCompletionService

**Location**: `Modules/Schemes/app/Services/LessonCompletionService.php`

**Methods**:
- `markAsCompleted(Lesson $lesson, int $userId): LessonCompletion`
- `unmarkAsCompleted(Lesson $lesson, int $userId): bool`
- `isCompleted(Lesson $lesson, int $userId): bool`
- `getUserCompletions(int $userId, int $unitId): array`

## API Endpoints

### Lesson Completion

**Mark Lesson as Complete**
```
POST /api/v1/lessons/{lesson:slug}/complete
Auth: Required
Response: LessonCompletion object
```

**Mark Lesson as Incomplete**
```
DELETE /api/v1/lessons/{lesson:slug}/complete
Auth: Required
Response: Success message
```

## Business Logic

### Completion Requirements

**Lesson**: Must be marked as completed
**Assignment**: Must pass with score >= 60% of max_score
**Quiz**: Must pass with final_score >= passing_grade

### Unit Completion

Unit dianggap selesai jika:
1. Semua Lesson completed
2. Semua Assignment passed (>= 60%)
3. Semua Quiz passed (>= passing_grade)

### Sequential Access Logic

**Lesson Access**:
```php
// Lesson order=3 requires order=1 and order=2 completed
$previousLessons = Lesson::where('unit_id', $lesson->unit_id)
    ->where('order', '<', $lesson->order)
    ->get();
```

**Assignment Access**:
```php
// Assignment requires all lessons + assignments/quizzes with lower ID
$lessons = Lesson::where('unit_id', $unitId)->get();
$assignments = Assignment::forUnit($unitId)->where('id', '<', $assignment->id)->get();
$quizzes = Quiz::forUnit($unitId)->where('id', '<', $assignment->id)->get();
```

**Unit Access**:
```php
// Unit order=2 requires Unit order=1 completed
$previousUnit = Unit::where('course_id', $unit->course_id)
    ->where('order', '<', $unit->order)
    ->first();
```

## Usage Examples

### Check Lesson Access
```php
$prerequisiteService = app(PrerequisiteService::class);

$result = $prerequisiteService->checkLessonAccess($lesson, $userId);

if (!$result['accessible']) {
    foreach ($result['missing'] as $missing) {
        echo "Complete Lesson {$missing['order']}: {$missing['title']}";
    }
}
```

### Check Assignment Access
```php
$result = $prerequisiteService->checkAssignmentAccess($assignment, $userId);

if (!$result['accessible']) {
    echo "Complete these first:";
    foreach ($result['missing'] as $missing) {
        echo "{$missing['type']}: {$missing['title']}";
    }
}
```

### Check Unit Access
```php
$result = $prerequisiteService->checkUnitAccess($unit, $userId);

if (!$result['accessible']) {
    echo "Complete previous unit first";
}
```

### Get Unit Progress
```php
$progress = $prerequisiteService->getUnitProgress($unit, $userId);

echo "Progress: {$progress['percentage']}%";
echo "Completed: {$progress['completed']}/{$progress['total']}";
```

### Get Content Order
```php
$content = $prerequisiteService->getUnitContentOrder($unit);

foreach ($content as $item) {
    echo "{$item['type']}: {$item['title']} (order: {$item['order']})";
}
```

## Seeder Implementation

### ComprehensiveAssessmentSeeder
- Creates 1-3 assignments per lesson
- Creates 1-3 quizzes per lesson
- NO manual prerequisite chains created
- Prerequisites determined automatically by ID

### LearningContentSeeder
- Creates 2-6 lessons per unit with sequential `order` field
- NO manual prerequisite chains created
- Prerequisites determined automatically by `order` field

## Migration Notes

### Dropped Tables
```sql
DROP TABLE IF EXISTS content_prerequisites;
DROP TABLE IF EXISTS lesson_prerequisites;
DROP TABLE IF EXISTS assignment_prerequisites;
DROP TABLE IF EXISTS quiz_prerequisites;
```

### Added Tables
```sql
CREATE TABLE lesson_completions (
    id, lesson_id, user_id, completed_at, created_at, updated_at
);
```

## Frontend Integration

### Checking Access
```javascript
const response = await api.get(`/lessons/${lesson.slug}/access-check`);
if (!response.accessible) {
    showPrerequisiteModal(response.missing);
}
```

### Marking Complete
```javascript
await api.post(`/lessons/${lesson.slug}/complete`);
```

### Progress Display
```javascript
const progress = await api.get(`/units/${unit.id}/progress`);
showProgressBar(progress.percentage);
```

## Testing Scenarios

1. **Sequential Lessons**: L1→L2→L3 by order field
2. **Mixed Content**: L1→A1→L2→Q1 by order/ID
3. **Unit Sequential**: Unit 2 blocked until Unit 1 complete
4. **Lesson Completion**: Mark/unmark lesson complete
5. **Assignment Passing**: Submit with passing score
6. **Quiz Passing**: Complete quiz with passing score
7. **Progress Calculation**: Accurate percentage for mixed content

## Performance Considerations

- Use eager loading: `$unit->load('lessons')`
- Cache unit completion status
- Index `order` field on lessons table
- Index `unit_id` + `order` composite for faster queries

## Advantages of Order-Based System

1. **No Manual Configuration**: Tidak perlu set prerequisite saat create content
2. **Automatic**: Prerequisites otomatis berdasarkan urutan
3. **Simple**: Tidak ada tabel prerequisite yang kompleks
4. **Flexible**: Urutan bisa diubah dengan update field `order`
5. **Predictable**: Logic jelas dan mudah dipahami
6. **Maintainable**: Tidak ada data prerequisite yang perlu di-sync

## Future Enhancements

- [ ] Reorder content via drag-and-drop (update `order` field)
- [ ] Skip prerequisites for specific users (override)
- [ ] Prerequisite bypass for instructors
- [ ] Visual progress timeline
- [ ] Bulk reorder content
- [ ] Export/import content order
