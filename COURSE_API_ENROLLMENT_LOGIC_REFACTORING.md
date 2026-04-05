# Course API Enrollment Logic Refactoring Plan

## Overview
Mengubah logika API `/api/v1/courses/:courseSlug` agar tidak mengembalikan error 403 ketika user belum enroll. Sebaliknya, API akan tetap mengembalikan response sukses tetapi membatasi field yang dikembalikan berdasarkan status enrollment user.

## Current Behavior
```json
// Request: GET /api/v1/courses/:courseSlug?include=units,category,tags,elements
// Response (jika belum enroll):
{
  "success": false,
  "message": "Anda harus terdaftar di kursus ini untuk mengakses konten tersebut.",
  "errors": null
}
```

## New Behavior
```json
// Request: GET /api/v1/courses/:courseSlug?include=units,category,tags,elements
// Response (jika belum enroll):
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
    // Field 'units' dan 'elements' tidak ada karena belum enroll
    "is_enrolled": false
  }
}

// Response (jika sudah enroll):
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
    "units": [ ... ],      // Ada karena sudah enroll
    "elements": [ ... ],   // Ada karena sudah enroll
    "is_enrolled": true
  }
}
```

## Implementation Plan

### 1. Identifikasi File yang Perlu Diubah
- [ ] `Levl-BE/Modules/Schemes/app/Http/Controllers/CourseController.php` - Controller utama
- [ ] `Levl-BE/Modules/Schemes/app/Services/CourseService.php` - Service layer (jika ada)
- [ ] `Levl-BE/Modules/Schemes/app/Models/Course.php` - Model untuk relationship logic
- [ ] `Levl-BE/Modules/Schemes/app/Http/Resources/CourseResource.php` - Resource untuk response formatting

### 2. Logika Perubahan

#### A. Hapus Enrollment Check yang Mengembalikan 403
```php
// BEFORE (di Controller):
if (!$user->isEnrolledIn($course)) {
    return response()->json([
        'success' => false,
        'message' => 'Anda harus terdaftar di kursus ini untuk mengakses konten tersebut.'
    ], 403);
}

// AFTER:
// Hapus check ini, biarkan request lanjut
```

#### B. Conditional Include Berdasarkan Enrollment
```php
// Di Controller atau Service:
$allowedIncludes = ['category', 'tags', 'instructor'];
$restrictedIncludes = ['units', 'elements', 'assignments', 'progress'];

$isEnrolled = $user ? $user->isEnrolledIn($course) : false;

// Parse requested includes
$requestedIncludes = explode(',', $request->query('include', ''));

// Filter includes berdasarkan enrollment status
$includes = array_filter($requestedIncludes, function($include) use ($isEnrolled, $allowedIncludes, $restrictedIncludes) {
    $include = trim($include);
    
    // Always allow public includes
    if (in_array($include, $allowedIncludes)) {
        return true;
    }
    
    // Only allow restricted includes if enrolled
    if (in_array($include, $restrictedIncludes)) {
        return $isEnrolled;
    }
    
    return false;
});

// Load relationships
$course->load($includes);
```

#### C. Tambahkan Field `is_enrolled` di Response
```php
// Di CourseResource:
public function toArray($request)
{
    $user = $request->user();
    $isEnrolled = $user ? $user->isEnrolledIn($this->resource) : false;
    
    return [
        'id' => $this->id,
        'title' => $this->title,
        'slug' => $this->slug,
        'description' => $this->description,
        'thumbnail' => $this->thumbnail,
        'is_enrolled' => $isEnrolled,
        
        // Conditional includes
        'category' => $this->whenLoaded('category'),
        'tags' => $this->whenLoaded('tags'),
        'units' => $this->whenLoaded('units'),
        'elements' => $this->whenLoaded('elements'),
        // ... other fields
    ];
}
```

### 3. Kategori Include Fields

#### Public Fields (Selalu Tersedia)
- `category` - Kategori course
- `tags` - Tags course
- `instructor` - Informasi instruktur
- `thumbnail` - Gambar thumbnail
- `description` - Deskripsi course
- `duration` - Durasi course
- `level` - Level kesulitan

#### Restricted Fields (Hanya untuk Enrolled Users)
- `units` - Unit kompetensi
- `elements` - Elemen kompetensi
- `assignments` - Tugas-tugas
- `progress` - Progress user
- `materials` - Materi pembelajaran
- `quizzes` - Quiz/latihan soal
- `forums` - Forum diskusi

### 4. Testing Scenarios

#### Test Case 1: User Belum Login
```bash
# Request
GET /api/v1/courses/course-slug?include=units,category,tags,elements

# Expected Response
{
  "success": true,
  "data": {
    "id": 1,
    "title": "...",
    "category": { ... },
    "tags": [ ... ],
    "is_enrolled": false
    // Tidak ada 'units' dan 'elements'
  }
}
```

#### Test Case 2: User Login Tapi Belum Enroll
```bash
# Request (dengan auth token)
GET /api/v1/courses/course-slug?include=units,category,tags,elements

# Expected Response
{
  "success": true,
  "data": {
    "id": 1,
    "title": "...",
    "category": { ... },
    "tags": [ ... ],
    "is_enrolled": false
    // Tidak ada 'units' dan 'elements'
  }
}
```

#### Test Case 3: User Sudah Enroll
```bash
# Request (dengan auth token)
GET /api/v1/courses/course-slug?include=units,category,tags,elements

# Expected Response
{
  "success": true,
  "data": {
    "id": 1,
    "title": "...",
    "category": { ... },
    "tags": [ ... ],
    "units": [ ... ],
    "elements": [ ... ],
    "is_enrolled": true
  }
}
```

### 5. Implementation Steps

1. **Analisis Kode Existing**
   - Baca `CourseController.php` untuk menemukan enrollment check
   - Identifikasi semua endpoint yang terpengaruh
   - Cek apakah ada middleware yang handle enrollment

2. **Refactor Controller**
   - Hapus enrollment check yang return 403
   - Implementasi conditional include logic
   - Tambahkan helper method untuk check enrollment

3. **Update Resource**
   - Tambahkan field `is_enrolled`
   - Pastikan `whenLoaded()` bekerja dengan baik
   - Handle nested relationships

4. **Update Model**
   - Pastikan relationship methods sudah ada
   - Tambahkan scope atau helper method jika perlu

5. **Testing**
   - Test semua scenario di atas
   - Test dengan berbagai kombinasi include
   - Test performance dengan banyak data

6. **Update Frontend**
   - Update hooks/api untuk handle response baru
   - Tambahkan conditional rendering berdasarkan `is_enrolled`
   - Update error handling

### 6. Breaking Changes & Migration

#### Backend Changes
- Response structure berubah dari error 403 menjadi success dengan limited fields
- Frontend perlu update untuk handle response baru

#### Frontend Changes Needed
- Update `hooks/api/courses.ts` untuk handle `is_enrolled` field
- Update components yang consume course data
- Tambahkan conditional rendering untuk restricted content
- Update error handling (tidak ada lagi 403 untuk unenrolled users)

### 7. Rollout Strategy

1. **Phase 1: Backend Implementation**
   - Implement conditional include logic
   - Add `is_enrolled` field
   - Test thoroughly

2. **Phase 2: Frontend Update**
   - Update API hooks
   - Update components
   - Test integration

3. **Phase 3: Deployment**
   - Deploy backend first
   - Monitor for issues
   - Deploy frontend
   - Monitor user experience

## Benefits

1. **Better UX**: User dapat melihat informasi course tanpa harus enroll dulu
2. **Flexible**: Frontend dapat menampilkan preview course dengan informasi terbatas
3. **Consistent**: Tidak ada lagi error 403 yang membingungkan
4. **Scalable**: Mudah menambahkan field baru ke kategori public/restricted

## Risks & Mitigation

### Risk 1: Data Leakage
- **Mitigation**: Carefully categorize fields sebagai public/restricted
- **Mitigation**: Add tests untuk memastikan restricted fields tidak bocor

### Risk 2: Performance
- **Mitigation**: Use eager loading untuk relationships
- **Mitigation**: Add caching untuk course data

### Risk 3: Breaking Changes
- **Mitigation**: Update frontend bersamaan dengan backend
- **Mitigation**: Add backward compatibility jika perlu

## Next Steps

1. Review dan approve planning ini
2. Identifikasi file-file yang perlu diubah
3. Implementasi perubahan di backend
4. Update frontend untuk handle response baru
5. Testing menyeluruh
6. Deployment
