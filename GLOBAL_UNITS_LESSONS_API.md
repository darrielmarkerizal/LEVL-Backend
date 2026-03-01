# Global Units & Lessons API - Implementation Summary

## ✅ Changes Completed

### 1. Fixed HasFactory Import Error
**File:** `Modules/Schemes/app/Models/Unit.php`
- Removed duplicate `use Illuminate\Database\Eloquent\Factories\HasFactory;` import
- Fixed fatal error that was blocking seeder execution

### 2. Added Global Units API

#### Routes Added
- `GET /units` - List all units (authenticated)
- `GET /units/{unit_slug}` - Show specific unit (authenticated)

#### Authorization Logic
- **Superadmin**: Can see ALL units from all courses
- **Admin/Instructor**: Only units from courses they manage (instructor_id match)
- **Student**: All published units (no restriction)

#### Filter Parameters (Slug-Based)
- `search`: PostgreSQL Full Text Search across title, description, slug (direct query param)
- `filter[status]`: Filter by status (published, draft)
- `filter[course_slug]`: Filter by course slug (NOT course_id)
- `sort`: Sort by order, title, created_at (prefix with - for desc)
- `include`: Relations (course, lessons)

#### Files Modified
- `Modules/Schemes/routes/api.php` - Added global routes
- `Modules/Schemes/app/Http/Controllers/UnitController.php` - Added `indexAll()` and `showGlobal()`
- `Modules/Schemes/app/Services/UnitService.php` - Added `paginateAll()` with authorization

### 3. Added Global Lessons API

#### Routes Added
- `GET /lessons` - List all lessons (authenticated)
- `GET /lessons/{lesson_slug}` - Show specific lesson (authenticated)

#### Authorization Logic
- **Superadmin**: Can see ALL lessons from all courses
- **Admin/Instructor**: Only lessons from courses they manage (via unit.course.instructor_id)
- **Student**: All published lessons (no restriction)

#### Filter Parameters (Slug-Based)
- `search`: PostgreSQL Full Text Search across title, description, markdown_content, slug (direct query param)
- `filter[status]`: Filter by status (published, draft)
- `filter[content_type]`: Filter by type (markdown, video, link)
- `filter[unit_slug]`: Filter by unit slug (NOT unit_id)
- `filter[course_slug]`: Filter by course slug (NOT course_id)
- `sort`: Sort by order, title, created_at (prefix with - for desc)
- `include`: Relations (unit, unit.course, blocks)

#### Files Modified
- `Modules/Schemes/routes/api.php` - Added global routes
- `Modules/Schemes/app/Http/Controllers/LessonController.php` - Added `indexAll()` and `showGlobal()`
- `Modules/Schemes/app/Services/LessonService.php` - Updated `paginateAll()` signature
- `Modules/Schemes/app/Services/Support/LessonFinder.php` - Added `paginateAll()` with authorization

### 4. Updated API Documentation
**File:** `API_COMPLETE_DOCUMENTATION.md`
- Added global units endpoints documentation
- Added global lessons endpoints documentation
- Updated filter parameters to use slug instead of ID
- Added authorization rules for each role

## 🔒 Authorization Rules

### Superadmin
```php
// Can see everything - no filters applied
if ($user->hasRole('Superadmin')) {
    // No query restrictions
}
```

### Admin/Instructor
```php
// Only courses they manage
if ($user->hasRole(['Admin', 'Instructor'])) {
    $query->whereHas('course', function ($q) use ($user) {
        $q->where('instructor_id', $user->id);
    });
}
```

### Student
```php
// All published content (handled by policies)
// No additional query restrictions
```

## 📝 API Usage Examples

### List All Units (Superadmin)
```bash
GET /api/v1/units?include=course&sort=-created_at
Authorization: Bearer {superadmin_token}
```

### List Units by Course (Admin/Instructor)
```bash
GET /api/v1/units?filter[course_slug]=web-development&include=lessons
Authorization: Bearer {instructor_token}
```

### Search Units (PostgreSQL FTS)
```bash
GET /api/v1/units?search=advanced&include=course
Authorization: Bearer {token}
```

### List All Lessons (Superadmin)
```bash
GET /api/v1/lessons?include=unit.course&sort=title
Authorization: Bearer {superadmin_token}
```

### List Lessons by Unit (Student)
```bash
GET /api/v1/lessons?filter[unit_slug]=getting-started&filter[status]=published
Authorization: Bearer {student_token}
```

### Search Lessons (PostgreSQL FTS)
```bash
GET /api/v1/lessons?search=introduction&include=unit.course
Authorization: Bearer {token}
```

### Combined Filters
```bash
GET /api/v1/lessons?search=html&filter[status]=published&filter[course_slug]=web-dev
Authorization: Bearer {token}
```

### Show Specific Unit
```bash
GET /api/v1/units/advanced-topics?include=course,lessons
Authorization: Bearer {token}
```

### Show Specific Lesson
```bash
GET /api/v1/lessons/introduction-to-html?include=unit.course,blocks
Authorization: Bearer {token}
```

## 🎯 Key Features

1. **Slug-Based Filtering**: All filters use slug instead of ID for better API design
2. **Role-Based Authorization**: Different access levels for Superadmin, Admin/Instructor, Student
3. **Flexible Includes**: Support for eager loading relations
4. **Pagination**: Standard Laravel pagination with configurable per_page
5. **Sorting**: Multiple sort fields with asc/desc order
6. **Search**: Partial text search in title field

## ⚠️ Important Notes

1. **No ID Filters**: All filters use slug (course_slug, unit_slug) instead of ID
2. **Authorization**: Automatically applied based on user role
3. **Policies**: Existing policies still apply for individual resource access
4. **Performance**: Uses eager loading to prevent N+1 queries
5. **Caching**: No caching applied to global endpoints (real-time data)

## 🔧 Technical Details

### Query Builder Filters
```php
// Units
AllowedFilter::exact('status')
AllowedFilter::partial('title')
// Custom: course_slug (via whereHas)

// Lessons
AllowedFilter::exact('content_type')
AllowedFilter::exact('status')
AllowedFilter::partial('title')
// Custom: unit_slug, course_slug (via whereHas)
```

### Includes
```php
// Units
->allowedIncludes(['course', 'lessons'])

// Lessons
->allowedIncludes(['unit', 'unit.course', 'blocks'])
```

### Sorts
```php
// Both Units and Lessons
->allowedSorts(['order', 'title', 'created_at'])
->defaultSort('-created_at') // Descending by default
```

## ✅ Code Quality

- All code formatted with Laravel Pint (PSR-12)
- Follows controller discipline (thin controllers)
- Service layer handles business logic
- Proper authorization checks
- Type hints on all parameters
- No comments (descriptive naming)

## 🚀 Ready for Testing

All changes are complete and ready for testing. The seeder error has been fixed and the API is ready to use.
