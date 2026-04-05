# Course API Enrollment Logic Implementation Summary

## Overview
Berhasil mengimplementasikan perubahan logika API `/api/v1/courses/:courseSlug` agar tidak mengembalikan error 403 ketika user belum enroll. API sekarang mengembalikan response sukses dengan field yang dibatasi berdasarkan status enrollment.

## Changes Made

### 1. CourseController.php
**File**: `Levl-BE/Modules/Schemes/app/Http/Controllers/CourseController.php`

**Changes**:
- Menghapus check `canAccessProtectedIncludes()` yang mengembalikan 403 error
- Menambahkan `filterIncludesByEnrollment()` untuk filter includes berdasarkan enrollment
- Menggunakan `findBySlugWithFilteredIncludes()` untuk load course dengan filtered includes

**Before**:
```php
if (!$this->service->canAccessProtectedIncludes($userId, $course, $requestedIncludes)) {
    if ($user) {
        return $this->forbidden(__('messages.courses.enrollment_required'));
    }
    return $this->unauthorized(__('messages.courses.authentication_required'));
}
```

**After**:
```php
// Filter includes based on enrollment status
$allowedIncludes = $this->service->filterIncludesByEnrollment($userId, $course, $requestedIncludes);

// Load course with filtered includes
$courseWithIncludes = $this->service->findBySlugWithFilteredIncludes($course->slug, $allowedIncludes);
```

### 2. CourseService.php
**File**: `Levl-BE/Modules/Schemes/app/Services/CourseService.php`

**New Methods**:

#### `filterIncludesByEnrollment()`
Memfilter requested includes berdasarkan enrollment status user.

```php
public function filterIncludesByEnrollment(?int $userId, Course $course, array $requestedIncludes): array
{
    // Define public and restricted includes
    $publicIncludes = ['category', 'tags', 'instructor', 'outcomes'];
    $restrictedIncludes = ['units', 'elements', 'lessons', 'quizzes', 'assignments', 'progress', 'enrollments'];

    // Check if user can access restricted content
    $canAccessRestricted = false;
    
    if ($userId) {
        $user = \Modules\Auth\Models\User::find($userId);
        
        if ($user) {
            // Managers (Superadmin, Admin, Instructor) can access all
            if ($user->hasRole('Superadmin') || $user->hasRole('Admin')) {
                $canAccessRestricted = true;
            } elseif ($user->hasRole('Instructor') && $course->instructors()->where('user_id', $user->id)->exists()) {
                $canAccessRestricted = true;
            } elseif ($user->hasRole('Student')) {
                // Students can only access if enrolled
                $canAccessRestricted = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->whereIn('status', ['active', 'completed'])
                    ->exists();
            }
        }
    }

    // Filter requested includes
    $allowedIncludes = [];
    foreach ($requestedIncludes as $include) {
        $include = trim($include);
        
        // Always allow public includes
        if (in_array($include, $publicIncludes)) {
            $allowedIncludes[] = $include;
            continue;
        }
        
        // Only allow restricted includes if user has access
        if (in_array($include, $restrictedIncludes) && $canAccessRestricted) {
            $allowedIncludes[] = $include;
        }
    }

    return $allowedIncludes;
}
```

#### `findBySlugWithFilteredIncludes()`
Delegate method ke CourseFinder untuk load course dengan filtered includes.

```php
public function findBySlugWithFilteredIncludes(string $slug, array $includes): ?Course
{
    return $this->finder->findBySlugWithFilteredIncludes($slug, $includes);
}
```

### 3. CourseServiceInterface.php
**File**: `Levl-BE/Modules/Schemes/app/Contracts/Services/CourseServiceInterface.php`

**New Method Signatures**:
```php
public function filterIncludesByEnrollment(?int $userId, Course $course, array $requestedIncludes): array;

public function findBySlugWithFilteredIncludes(string $slug, array $includes): ?Course;
```

### 4. CourseFinder.php
**File**: `Levl-BE/Modules/Schemes/app/Services/Support/CourseFinder.php`

**New Method**:
```php
public function findBySlugWithFilteredIncludes(string $slug, array $includes): ?Course
{
    $user = auth('api')->user();

    // Build base query
    $query = Course::where('slug', $slug);

    // Load filtered includes
    if (!empty($includes)) {
        $query->with($includes);
    }

    // Always load enrollments for authenticated users
    if ($user) {
        $query->with(['enrollments' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }]);
    }

    return $query->first();
}
```

### 5. CourseResource.php
**File**: `Levl-BE/Modules/Schemes/app/Http/Resources/CourseResource.php`

**New Field**:
```php
'is_enrolled' => $isStudent && $enrollment && in_array($enrollment->status->value, ['active', 'completed']),
```

Field ini menunjukkan apakah user sudah enrolled di course atau belum.

## Include Categories

### Public Includes (Selalu Tersedia)
- `category` - Kategori course
- `tags` - Tags course
- `instructor` - Informasi instruktur
- `outcomes` - Learning outcomes

### Restricted Includes (Hanya untuk Enrolled Users atau Managers)
- `units` - Unit kompetensi
- `elements` - Elemen kompetensi
- `lessons` - Materi pembelajaran
- `quizzes` - Quiz/latihan soal
- `assignments` - Tugas-tugas
- `progress` - Progress user
- `enrollments` - Data enrollment (untuk managers)

## API Response Examples

### Example 1: User Belum Login
```bash
GET /api/v1/courses/course-slug?include=units,category,tags,elements
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Course Title",
    "slug": "course-slug",
    "description": "Course description",
    "thumbnail": "...",
    "category": { ... },
    "tags": [ ... ],
    "is_enrolled": false
    // Tidak ada 'units' dan 'elements'
  }
}
```

### Example 2: User Login Tapi Belum Enroll
```bash
GET /api/v1/courses/course-slug?include=units,category,tags,elements
Authorization: Bearer <token>
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Course Title",
    "slug": "course-slug",
    "description": "Course description",
    "thumbnail": "...",
    "category": { ... },
    "tags": [ ... ],
    "is_enrolled": false
    // Tidak ada 'units' dan 'elements'
  }
}
```

### Example 3: User Sudah Enroll
```bash
GET /api/v1/courses/course-slug?include=units,category,tags,elements
Authorization: Bearer <token>
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Course Title",
    "slug": "course-slug",
    "description": "Course description",
    "thumbnail": "...",
    "category": { ... },
    "tags": [ ... ],
    "units": [ ... ],
    "elements": [ ... ],
    "is_enrolled": true
  }
}
```

## Access Control Logic

### Managers (Full Access)
- Superadmin: Akses ke semua course dan semua includes
- Admin: Akses ke semua course dan semua includes
- Instructor: Akses ke course yang mereka ajar dan semua includes

### Students (Conditional Access)
- Public includes: Selalu bisa diakses
- Restricted includes: Hanya jika enrolled dengan status 'active' atau 'completed'

### Guest Users (Limited Access)
- Hanya bisa akses public includes
- Tidak bisa akses restricted includes

## Benefits

1. **Better UX**: User dapat melihat informasi course tanpa harus enroll dulu
2. **No More 403 Errors**: Tidak ada lagi error yang membingungkan untuk unenrolled users
3. **Flexible**: Frontend dapat menampilkan preview course dengan informasi terbatas
4. **Consistent**: Response selalu sukses, field yang dikembalikan tergantung enrollment
5. **Secure**: Restricted content tetap terlindungi, hanya accessible untuk enrolled users

## Testing Checklist

- [ ] Test API dengan user belum login
- [ ] Test API dengan user login tapi belum enroll
- [ ] Test API dengan user sudah enroll (active)
- [ ] Test API dengan user sudah enroll (completed)
- [ ] Test API dengan Instructor
- [ ] Test API dengan Admin
- [ ] Test API dengan Superadmin
- [ ] Test berbagai kombinasi include parameters
- [ ] Verify restricted includes tidak bocor ke unenrolled users
- [ ] Verify public includes selalu tersedia

## Next Steps

1. **Frontend Update**: Update hooks dan components untuk handle response baru
2. **Testing**: Test semua scenarios di atas
3. **Documentation**: Update API documentation
4. **Monitoring**: Monitor untuk memastikan tidak ada data leakage

## Files Modified

1. `Levl-BE/Modules/Schemes/app/Http/Controllers/CourseController.php`
2. `Levl-BE/Modules/Schemes/app/Services/CourseService.php`
3. `Levl-BE/Modules/Schemes/app/Contracts/Services/CourseServiceInterface.php`
4. `Levl-BE/Modules/Schemes/app/Services/Support/CourseFinder.php`
5. `Levl-BE/Modules/Schemes/app/Http/Resources/CourseResource.php`

## Breaking Changes

### Backend
- Response structure berubah dari error 403 menjadi success dengan limited fields
- Field `is_enrolled` ditambahkan ke response

### Frontend (Perlu Update)
- Update error handling (tidak ada lagi 403 untuk unenrolled users)
- Handle `is_enrolled` field untuk conditional rendering
- Update components yang consume course data
