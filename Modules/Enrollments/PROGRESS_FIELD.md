# Enrollment Progress Field

## Overview

Field `progress` telah ditambahkan ke API response `/enrollments`. Field ini menampilkan persentase progress course dalam bentuk angka (0-100).

## Logic

- **Status pending**: Progress = 0%
- **Status lainnya**: Progress diambil dari `course_progress.progress_percent`
- Progress di-round ke 2 desimal

## API Response Example

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "status": "approved",
      "progress": 45.50,
      "enrolled_at": "2026-03-03T10:00:00+00:00",
      "completed_at": null,
      "created_at": "2026-03-03T10:00:00+00:00",
      "updated_at": "2026-03-03T10:00:00+00:00",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": "https://..."
      },
      "course": {
        "id": 1,
        "title": "Introduction to Programming",
        "slug": "intro-programming",
        "code": "CS101"
      }
    },
    {
      "id": 2,
      "status": "pending",
      "progress": 0,
      "enrolled_at": null,
      "completed_at": null,
      "created_at": "2026-03-03T11:00:00+00:00",
      "updated_at": "2026-03-03T11:00:00+00:00",
      "user": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "avatar_url": "https://..."
      },
      "course": {
        "id": 2,
        "title": "Advanced JavaScript",
        "slug": "advanced-js",
        "code": "JS201"
      }
    }
  ]
}
```

## Implementation Details

### Files Modified

1. **EnrollmentResource.php**
   - Added progress calculation logic
   - Returns 0 for pending status
   - Returns progress_percent from courseProgress for other statuses

2. **EnrollmentFinder.php**
   - Added `courseProgress` to eager loading in `buildQuery()`
   - Added `courseProgress` to eager loading in `buildQueryForIndex()`

3. **EnrollmentsController.php**
   - Added `courseProgress` to eager loading in `show()` method

## Database Relationship

```
enrollments
  └─ courseProgress (hasOne)
       └─ progress_percent (float)
```

## Notes

- Progress is automatically calculated based on course completion
- Pending enrollments always show 0% progress
- Progress is cached along with enrollment data (300 seconds TTL)
