# Course Archive/Unarchive API Implementation

## Overview
Implemented dedicated archive and unarchive endpoints for courses with full frontend integration.

## Backend Implementation

### Routes Added
**File**: `Levl-BE/Modules/Schemes/routes/api.php`

```php
Route::put('courses/{course:slug}/archive', [CourseController::class, 'archive'])
    ->middleware('can:update,course')
    ->name('courses.archive');
    
Route::put('courses/{course:slug}/unarchive', [CourseController::class, 'unarchive'])
    ->middleware('can:update,course')
    ->name('courses.unarchive');
```

### Controller Methods
**File**: `Levl-BE/Modules/Schemes/app/Http/Controllers/CourseController.php`

```php
public function archive(Course $course)
{
    $courseWithInstructors = $this->service->findWithInstructors($course->id);
    $this->authorize('update', $courseWithInstructors);
    
    $updated = $this->service->archive($course->id);
    
    return $this->success(new CourseResource($updated), __('messages.courses.archived'));
}

public function unarchive(Course $course)
{
    $courseWithInstructors = $this->service->findWithInstructors($course->id);
    $this->authorize('update', $courseWithInstructors);
    
    $updated = $this->service->unarchive($course->id);
    
    return $this->success(new CourseResource($updated), __('messages.courses.unarchived'));
}
```

### Service Methods
**File**: `Levl-BE/Modules/Schemes/app/Contracts/Services/CourseServiceInterface.php`

```php
public function archive(int $courseId): Course;
public function unarchive(int $courseId): Course;
```

**File**: `Levl-BE/Modules/Schemes/app/Services/CourseService.php`

```php
public function archive(int $courseId): Course
{
    return $this->processor->archive($courseId);
}

public function unarchive(int $courseId): Course
{
    return $this->processor->unarchive($courseId);
}
```

### Processor Implementation
**File**: `Levl-BE/Modules/Schemes/app/Services/Support/CoursePublicationProcessor.php`

```php
public function archive(int $courseId): Course
{
    $course = $this->repository->findOrFail($courseId);
    
    return DB::transaction(function () use ($course) {
        $this->repository->update($course, [
            'status' => CourseStatus::Archived->value,
        ]);
        
        return $this->repository->findOrFail($course->id);
    });
}

public function unarchive(int $courseId): Course
{
    $course = $this->repository->findOrFail($courseId);
    
    return DB::transaction(function () use ($course) {
        $this->repository->update($course, [
            'status' => CourseStatus::Draft->value,
        ]);
        
        return $this->repository->findOrFail($course->id);
    });
}
```

### Translations
**English** (`Levl-BE/lang/en/messages.php`):
```php
'courses' => [
    'archived'   => 'Course archived successfully.',
    'unarchived' => 'Course unarchived successfully.',
]
```

**Indonesian** (`Levl-BE/lang/id/messages.php`):
```php
'courses' => [
    'archived'   => 'Kursus berhasil diarsipkan.',
    'unarchived' => 'Kursus berhasil dibatalkan dari arsip.',
]
```

## Frontend Implementation

### API Hooks
**File**: `Levl-FE/hooks/api/schemes.ts`

```typescript
export function useArchiveCourse() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (courseSlug: string) => {
      const response = await apiClient.put<ApiResponse<{ course: Course }>>(
        `${COURSES_ENDPOINT}/${courseSlug}/archive`,
      );
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: QUERY_KEYS.courses });
    },
  });
}

export function useUnarchiveCourse() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (courseSlug: string) => {
      const response = await apiClient.put<ApiResponse<{ course: Course }>>(
        `${COURSES_ENDPOINT}/${courseSlug}/unarchive`,
      );
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: QUERY_KEYS.courses });
    },
  });
}
```

### Courses Table Integration
**File**: `Levl-FE/components/dashboard/skema/courses-table.tsx`

**Features**:
- Archive button shows for non-archived courses
- Unarchive button shows for archived courses
- Confirmation dialog for both actions
- Toast notifications on success/error
- Automatic table refresh after action

**Key Changes**:
1. Added `useUnarchiveCourse` hook
2. Updated dialog type to include "unarchive"
3. Modified archive button to toggle between archive/unarchive based on course status
4. Added unarchive case in `handleConfirmAction`
5. Added unarchive content in `getDialogContent`

### Translations
**English** (`Levl-FE/messages/en/page.json`):
```json
{
  "scheme": {
    "actions": {
      "archive": "Archive",
      "unarchive": "Unarchive"
    },
    "confirm": {
      "archive": {
        "title": "Archive Scheme",
        "description": "Are you sure you want to archive this scheme? It will no longer be accessible to users."
      },
      "unarchive": {
        "title": "Unarchive Scheme",
        "description": "Are you sure you want to unarchive this scheme? It will be changed to draft status."
      }
    },
    "messages": {
      "archived": "Scheme archived successfully",
      "unarchived": "Scheme unarchived successfully"
    }
  }
}
```

**Indonesian** (`Levl-FE/messages/id/page.json`):
```json
{
  "scheme": {
    "actions": {
      "archive": "Arsipkan",
      "unarchive": "Batalkan Arsip"
    },
    "confirm": {
      "archive": {
        "title": "Arsipkan Skema",
        "description": "Apakah Anda yakin ingin mengarsipkan skema ini? Skema tidak akan dapat diakses oleh pengguna."
      },
      "unarchive": {
        "title": "Batalkan Arsip Skema",
        "description": "Apakah Anda yakin ingin membatalkan pengarsipan skema ini? Status akan diubah menjadi draft."
      }
    },
    "messages": {
      "archived": "Skema berhasil diarsipkan",
      "unarchived": "Skema berhasil dibatalkan dari arsip"
    }
  }
}
```

## API Endpoints

### Archive Course
```
PUT /api/v1/courses/{slug}/archive
```

**Authorization**: Requires `update` permission on course

**Response**:
```json
{
  "success": true,
  "message": "Course archived successfully.",
  "data": {
    "course": {
      "id": 1,
      "slug": "course-slug",
      "status": "archived",
      ...
    }
  }
}
```

### Unarchive Course
```
PUT /api/v1/courses/{slug}/unarchive
```

**Authorization**: Requires `update` permission on course

**Response**:
```json
{
  "success": true,
  "message": "Course unarchived successfully.",
  "data": {
    "course": {
      "id": 1,
      "slug": "course-slug",
      "status": "draft",
      ...
    }
  }
}
```

## Business Logic

### Archive
- Changes course status to `archived`
- Course becomes inaccessible to students
- Only instructors/admins can view archived courses
- Preserves all course data and relationships

### Unarchive
- Changes course status to `draft`
- Course needs to be published again to be accessible to students
- Allows instructors/admins to restore archived courses

## Authorization
Both endpoints require:
- User must be authenticated (`auth:api` middleware)
- User must have `Superadmin`, `Admin`, or `Instructor` role
- User must have `update` permission on the course (via policy)

## Testing

### Manual Testing Steps
1. **Archive Course**:
   - Navigate to courses table
   - Click archive button on a published/draft course
   - Confirm action in dialog
   - Verify course status changes to "Archived"
   - Verify success toast appears

2. **Unarchive Course**:
   - Navigate to courses table
   - Find an archived course
   - Click unarchive button (same button, different icon/text)
   - Confirm action in dialog
   - Verify course status changes to "Draft"
   - Verify success toast appears

3. **Authorization**:
   - Test as student (should not see archive/unarchive buttons)
   - Test as instructor (should see buttons for own courses)
   - Test as admin (should see buttons for all courses)

### API Testing
```bash
# Archive course
curl -X PUT http://localhost:8000/api/v1/courses/course-slug/archive \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"

# Unarchive course
curl -X PUT http://localhost:8000/api/v1/courses/course-slug/unarchive \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

## Files Modified

### Backend
- `Levl-BE/Modules/Schemes/routes/api.php`
- `Levl-BE/Modules/Schemes/app/Http/Controllers/CourseController.php`
- `Levl-BE/Modules/Schemes/app/Contracts/Services/CourseServiceInterface.php`
- `Levl-BE/Modules/Schemes/app/Services/CourseService.php`
- `Levl-BE/Modules/Schemes/app/Services/Support/CoursePublicationProcessor.php`
- `Levl-BE/lang/en/messages.php` (translations already existed)
- `Levl-BE/lang/id/messages.php` (translations already existed)

### Frontend
- `Levl-FE/hooks/api/schemes.ts`
- `Levl-FE/components/dashboard/skema/courses-table.tsx`
- `Levl-FE/messages/en/page.json`
- `Levl-FE/messages/id/page.json`

## Status
✅ **COMPLETE** - All backend and frontend implementation finished with full integration.
