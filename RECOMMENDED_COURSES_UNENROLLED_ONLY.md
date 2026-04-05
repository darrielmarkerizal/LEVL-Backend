# Recommended Courses - Unenrolled Only Implementation

## Overview
Memastikan endpoint `/api/v1/dashboard/recommended-courses` hanya mengembalikan course yang belum di-enroll oleh user.

## Endpoint
```
GET /api/v1/dashboard/recommended-courses
```

### Query Parameters
- `limit` (optional): Jumlah course yang dikembalikan (default: 2, max: 10)

### Authentication
Required: Bearer token

## Implementation

### File Modified
`Levl-BE/Modules/Dashboard/app/Services/DashboardService.php`

### Method: `getRecommendedCourses()`

**Logic**:
1. Ambil semua course IDs yang sudah di-enroll oleh user (semua status: pending, active, completed, rejected)
2. Exclude semua enrolled courses dari hasil rekomendasi
3. Prioritaskan course dengan kategori yang sama dengan enrolled courses
4. Urutkan berdasarkan popularity (enrollments_count)
5. Jika tidak cukup, tambahkan popular courses (tetap exclude enrolled)

**Key Changes**:
```php
// Get ALL enrolled courses (not just active)
$enrolledCourseIds = Enrollment::where('user_id', $userId)
    ->pluck('course_id')
    ->unique()  // Added unique() to ensure no duplicates
    ->toArray();

// Exclude ALL enrolled courses
$query = Course::query()
    ->whereNotIn('id', $enrolledCourseIds) // This ensures only unenrolled courses
    ->where('status', 'published');
```

## Response Example

### Success Response
```json
{
  "success": true,
  "message": "Recommended courses retrieved successfully",
  "data": [
    {
      "id": 5,
      "title": "Advanced PHP Programming",
      "slug": "advanced-php-programming",
      "description": "Learn advanced PHP concepts",
      "category": "Programming",
      "thumbnail": "https://example.com/thumbnail.jpg",
      "instructor": {
        "id": 2,
        "name": "John Doe"
      },
      "enrollments_count": 150
    },
    {
      "id": 8,
      "title": "Database Design Fundamentals",
      "slug": "database-design-fundamentals",
      "description": "Master database design principles",
      "category": "Database",
      "thumbnail": "https://example.com/thumbnail2.jpg",
      "instructor": {
        "id": 3,
        "name": "Jane Smith"
      },
      "enrollments_count": 120
    }
  ]
}
```

## Recommendation Algorithm

### Priority 1: Similar Category Courses
- Ambil kategori dari enrolled courses
- Cari course dengan kategori yang sama
- Exclude enrolled courses
- Sort by popularity

### Priority 2: Popular Courses
- Jika tidak cukup dari kategori yang sama
- Ambil popular courses (highest enrollments_count)
- Exclude enrolled courses
- Fill remaining slots

### Special Case: No Enrollments
- Jika user belum enroll course apapun
- Return popular courses (highest enrollments_count)
- Limit sesuai parameter

## Enrollment Status Handling

Method ini exclude SEMUA enrollment status:
- `pending` - Menunggu approval
- `active` - Sedang aktif
- `completed` - Sudah selesai
- `rejected` - Ditolak

Ini memastikan user tidak mendapat rekomendasi untuk course yang:
1. Sudah mereka enroll (active/completed)
2. Sedang menunggu approval (pending)
3. Pernah ditolak (rejected)

## Testing Scenarios

### Test Case 1: User Belum Enroll Apapun
```bash
GET /api/v1/dashboard/recommended-courses?limit=3
Authorization: Bearer <token>
```
**Expected**: Return 3 popular courses (highest enrollments_count)

### Test Case 2: User Sudah Enroll Beberapa Course
```bash
GET /api/v1/dashboard/recommended-courses?limit=5
Authorization: Bearer <token>
```
**Expected**: 
- Return 5 courses yang BELUM di-enroll
- Prioritas: course dengan kategori sama dengan enrolled courses
- Tidak ada course yang sudah di-enroll (any status)

### Test Case 3: User Enroll Hampir Semua Course
```bash
GET /api/v1/dashboard/recommended-courses?limit=10
Authorization: Bearer <token>
```
**Expected**: 
- Return course yang tersisa (belum di-enroll)
- Jumlah bisa kurang dari 10 jika tidak cukup course tersedia

### Test Case 4: Verify Enrolled Course Not Returned
1. User enroll course A (status: active)
2. Call recommended-courses endpoint
3. **Expected**: Course A tidak ada di hasil rekomendasi

### Test Case 5: Verify Pending Enrollment Not Returned
1. User enroll course B (status: pending)
2. Call recommended-courses endpoint
3. **Expected**: Course B tidak ada di hasil rekomendasi

## Benefits

1. **No Duplicate Recommendations**: User tidak akan melihat course yang sudah mereka enroll
2. **Better UX**: Rekomendasi lebih relevan karena hanya menampilkan course baru
3. **Accurate**: Exclude semua status enrollment, bukan hanya active
4. **Smart Algorithm**: Prioritaskan course dengan kategori yang sama

## Related Endpoints

- `GET /api/v1/dashboard` - Dashboard overview (includes enrolled courses count)
- `GET /api/v1/dashboard/recent-learning` - Recent learning activities (only enrolled courses)
- `GET /api/v1/courses/my-enrolled` - List of enrolled courses

## Notes

- Endpoint ini hanya untuk Student role
- Requires authentication
- Course yang dikembalikan hanya yang berstatus 'published'
- Sorting berdasarkan popularity (enrollments_count DESC)
