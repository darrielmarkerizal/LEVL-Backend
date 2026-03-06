# Summary: Unified Content Creation API

## Tanggal
6 Maret 2026

## Overview

Implementasi API simplified untuk membuat konten (Lesson, Assignment, Quiz) dalam satu endpoint dengan **minimal fields** - hanya `type`, `title`, dan `order`. Detail konten diisi kemudian via UPDATE endpoint.

## Philosophy: Create First, Fill Details Later

API ini mengikuti workflow:
1. **Create** - Buat skeleton konten dengan minimal info
2. **Update** - Isi detail konten via endpoint spesifik
3. **Publish** - Publikasikan ketika siap

## Endpoint

```
POST /api/v1/courses/{course_slug}/units/{unit_slug}/contents
```

## Required Fields

### Semua Type
- `type`: lesson | assignment | quiz
- `title`: string (max 255)
- `order`: integer (optional, auto-generate)

### Assignment Only
- `submission_type`: file | mixed (REQUIRED karena tidak bisa diubah setelah ada submission)

## Implementasi

### 1. Request Validation
**File**: `Modules/Schemes/app/Http/Requests/StoreContentRequest.php`

- Validasi dinamis berdasarkan `type` field
- Rules berbeda untuk lesson/assignment/quiz
- Custom error messages dalam Bahasa Indonesia

```php
public function rules(): array
{
    $type = $this->input('type');
    
    if ($type === 'lesson') {
        return [...]; // Lesson-specific rules
    }
    if ($type === 'assignment') {
        return [...]; // Assignment-specific rules
    }
    if ($type === 'quiz') {
        return [...]; // Quiz-specific rules
    }
}
```

### 2. Content Service
**File**: `Modules/Schemes/app/Services/ContentService.php`

- Orchestrate creation berdasarkan type
- Delegate ke service yang sesuai (LessonService, AssignmentService, QuizService)
- Return unified response format

```php
public function createContent(Unit $unit, array $data, int $creatorId): array
{
    $type = $data['type'];
    
    return match ($type) {
        'lesson' => $this->createLesson($unit, $data),
        'assignment' => $this->createAssignment($unit, $data, $creatorId),
        'quiz' => $this->createQuiz($unit, $data, $creatorId),
    };
}
```

### 3. Controller Update
**File**: `Modules/Schemes/app/Http/Controllers/UnitController.php`

- Update method `storeContent` untuk menggunakan `ContentService`
- Thin controller (< 10 lines)
- Proper authorization check

```php
public function storeContent(StoreContentRequest $request, Course $course, Unit $unit)
{
    $this->service->validateHierarchy($course->id, $unit->id);
    $this->authorize('update', $unit);
    
    $contentService = app(ContentService::class);
    $createdContent = $contentService->createContent($unit, $request->validated(), auth('api')->id());
    
    return $this->created($createdContent, __('messages.content.created'));
}
```

### 4. Route
**File**: `Modules/Schemes/routes/api.php`

Route sudah ada sebelumnya:
```php
Route::post('courses/{course:slug}/units/{unit:slug}/contents', [UnitController::class, 'storeContent'])
    ->name('courses.units.contents.store');
```

## Request Examples

### Create Lesson (Minimal)
```json
{
  "type": "lesson",
  "title": "Struktur Dasar HTML",
  "order": 1
}
```

### Create Assignment (Minimal)
```json
{
  "type": "assignment",
  "title": "Tugas Membuat Website Portfolio",
  "submission_type": "file",
  "order": 2
}
```

**Note**: `submission_type` required untuk assignment.

### Create Quiz (Minimal)
```json
{
  "type": "quiz",
  "title": "Quiz HTML Dasar",
  "order": 3
}
```

## Complete Workflow Example

### Step 1: Create Skeleton
```bash
POST /api/v1/courses/web-dev/units/html-basics/contents
{
  "type": "lesson",
  "title": "Struktur Dasar HTML"
}

# Response
{
  "success": true,
  "data": {
    "type": "lesson",
    "id": 123,
    "slug": "struktur-dasar-html",
    "title": "Struktur Dasar HTML",
    "order": 1,
    "status": "draft"
  }
}
```

### Step 2: Fill Details
```bash
PUT /api/v1/units/html-basics/lessons/struktur-dasar-html
{
  "description": "Mempelajari struktur dasar dokumen HTML",
  "markdown_content": "# Struktur HTML\n\n...",
  "duration_minutes": 30
}
```

### Step 3: Publish
```bash
PUT /api/v1/units/html-basics/lessons/struktur-dasar-html
{
  "status": "published"
}
```

## Response Format

Unified response untuk semua type:

```json
{
  "success": true,
  "message": "Content created successfully",
  "data": {
    "type": "lesson",
    "id": 123,
    "slug": "struktur-dasar-html",
    "title": "Struktur Dasar HTML",
    "order": 1,
    "status": "draft",
    "data": {
      // Full object dari konten yang dibuat
    }
  }
}
```

## Validation Rules

### Common Fields (All Types)
- `type`: required, in:lesson,assignment,quiz
- `title`: required, string, max:255
- `order`: nullable, integer, min:1

### Assignment Only
- `submission_type`: **required**, in:file,mixed

**That's it!** Semua field lain diisi via UPDATE endpoint.

## Benefits

### For Frontend
✅ **Quick Content Creation** - Buat konten cepat tanpa form panjang  
✅ **Flexible Workflow** - Create first, fill details later  
✅ **Easy Reordering** - Buat semua konten, atur order, baru isi detail  
✅ **Single Endpoint** - Tidak perlu switch endpoint  
✅ **Consistent Response** - Format response sama untuk semua type  

### For Backend
✅ **Minimal Validation** - Hanya validate essential fields  
✅ **Centralized Logic** - Create logic di satu tempat  
✅ **Easy to Extend** - Mudah tambah type baru  
✅ **Reuse Services** - Delegate ke existing services  

## Use Cases

### 1. Quick Content Scaffolding
```javascript
// User quickly adds content structure
const contents = [
  { type: 'lesson', title: 'Intro to HTML' },
  { type: 'lesson', title: 'HTML Tags' },
  { type: 'assignment', title: 'Build a Page', submission_type: 'file' },
  { type: 'quiz', title: 'HTML Quiz' }
];

// Create all skeletons
contents.forEach((content, index) => {
  createContent({ ...content, order: index + 1 });
});

// Fill details later when ready
```

### 2. Drag-and-Drop Content Builder
```javascript
// User drags content type to canvas
onDrop(contentType) {
  // Create skeleton immediately
  const content = await createContent({
    type: contentType,
    title: `New ${contentType}`
  });
  
  // Show detail form for editing
  showDetailForm(content);
}
```

### 3. Bulk Import
```javascript
// Import content structure from CSV/JSON
importedData.forEach(item => {
  createContent({
    type: item.type,
    title: item.title,
    order: item.order
  });
});
```

## Backward Compatibility

✅ **Fully Backward Compatible**

Endpoint lama masih berfungsi:
- `POST /api/v1/units/{slug}/lessons`
- `POST /api/v1/assignments`
- `POST /api/v1/quizzes`

Developer bisa pilih:
- **Unified API**: Untuk form builder, CMS, dynamic UI
- **Separated API**: Untuk form spesifik, RESTful approach

## Testing

### Manual Testing
```bash
# Test Lesson Creation
curl -X POST /api/v1/courses/web-dev/units/html-basics/contents \
  -H "Authorization: Bearer {token}" \
  -d '{"type":"lesson","title":"Test Lesson"}'

# Test Assignment Creation
curl -X POST /api/v1/courses/web-dev/units/html-basics/contents \
  -H "Authorization: Bearer {token}" \
  -d '{"type":"assignment","title":"Test Assignment","submission_type":"file"}'

# Test Quiz Creation
curl -X POST /api/v1/courses/web-dev/units/html-basics/contents \
  -H "Authorization: Bearer {token}" \
  -d '{"type":"quiz","title":"Test Quiz"}'
```

### Automated Testing
```bash
vendor/bin/pest Modules/Schemes/tests/Feature/UnifiedContentTest.php
```

## Documentation

Updated files:
1. `PANDUAN_FORM_MANAGEMENT_LENGKAP.md` - Added Section 8: Unified Content Creation
2. `UNIFIED_CONTENT_API_SUMMARY.md` - This file

## Files Created/Modified

### Created
1. `Modules/Schemes/app/Http/Requests/StoreContentRequest.php`
2. `Modules/Schemes/app/Services/ContentService.php`
3. `UNIFIED_CONTENT_API_SUMMARY.md`

### Modified
1. `Modules/Schemes/app/Http/Controllers/UnitController.php`
2. `PANDUAN_FORM_MANAGEMENT_LENGKAP.md`

## Next Steps

1. ✅ Implementation completed
2. ⏳ Add automated tests
3. ⏳ Update Postman collection
4. ⏳ Inform frontend team
5. ⏳ Create example implementation in frontend
6. ⏳ Monitor usage and gather feedback

## Notes

- Unified API adalah **tambahan**, bukan pengganti
- Kedua approach (unified vs separated) valid dan bisa digunakan
- Pilih approach yang sesuai dengan use case
- Validation rules sama dengan endpoint terpisah
- Authorization sama dengan endpoint terpisah

---

**Status**: ✅ Completed  
**Author**: Backend Team  
**Reviewed by**: -  
**Approved by**: -
