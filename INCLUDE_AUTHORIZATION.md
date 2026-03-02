# Include Authorization System

## Overview

Sistem authorization untuk dynamic includes berdasarkan role user dan enrollment status. Sistem ini memastikan bahwa user hanya bisa mengakses data yang sesuai dengan hak akses mereka.

---

## Course API - `/courses/{slug}`

### Public Access (Tanpa Token)
User yang tidak login hanya bisa mengakses informasi dasar course:

**Allowed Includes:**
- `tags` - Tags yang terkait dengan course
- `category` - Kategori course
- `instructor` - Informasi instructor (public profile)
- `units` - Daftar unit (metadata saja, tanpa konten)

**Example:**
```
GET /courses/web-development-101?include=tags,category,instructor,units
```

---

### Authenticated - Enrolled Student (Active Enrollment Required)
Student yang sudah enroll dan status enrollment `active` bisa mengakses konten pembelajaran:

**Allowed Includes (Public + Enrolled):**
- `tags`, `category`, `instructor`, `units` (dari public)
- `lessons` - Daftar lesson dalam course
- `quizzes` - Daftar quiz dalam course
- `assignments` - Daftar assignment dalam course
- `units.lessons` - Lessons dalam setiap unit
- `units.lessons.blocks` - Blocks dalam setiap lesson

**Example:**
```
GET /courses/web-development-101?include=units.lessons,quizzes,assignments
Authorization: Bearer {student_token}
```

**Validation:**
- User harus memiliki enrollment dengan `status = 'active'`
- Jika enrollment tidak active atau tidak ada, hanya mendapat public includes

---

### Authenticated - Admin/Instructor (Course Manager)
Admin yang assigned ke course atau Instructor yang mengajar course bisa mengakses semua data termasuk management data:

**Allowed Includes (Public + Enrolled + Manager):**
- Semua includes dari Public dan Enrolled Student
- `enrollments` - Daftar semua enrollment di course
- `enrollments.user` - Data user yang enroll
- `admins` - Daftar admin yang assigned ke course

**Example:**
```
GET /courses/web-development-101?include=enrollments.user,admins
Authorization: Bearer {admin_token}
```

**Validation:**
- Superadmin: Full access ke semua course
- Admin: Harus ada di `course_admins` table untuk course tersebut
- Instructor: Harus `instructor_id` sama dengan user id

---

## Unit API - `/courses/{slug}/units/{slug}`

### Public Access (Tanpa Token)
**Allowed Includes:**
- `course` - Informasi course parent

### Authenticated - Enrolled Student (Active Enrollment Required)
**Allowed Includes (Public + Enrolled):**
- `course` (dari public)
- `lessons` - Daftar lesson dalam unit
- `lessons.blocks` - Blocks dalam setiap lesson

**Example:**
```
GET /courses/web-dev/units/getting-started?include=lessons.blocks
Authorization: Bearer {student_token}
```

**Validation:**
- User harus enrolled active di course parent dari unit

---

## Quiz Submission API - `/quizzes/{id}/submissions/{id}`

### Owner Access (Submission Owner)
Student yang membuat submission bisa melihat data submission mereka:

**Allowed Includes:**
- `answers` - Jawaban yang sudah disubmit
- `quiz` - Informasi quiz
- `user` - Informasi user (diri sendiri)

**Example:**
```
GET /quizzes/123/submissions/456?include=answers,quiz
Authorization: Bearer {student_token}
```

**Validation:**
- `submission.user_id` harus sama dengan authenticated user id

---

### Manager Access (Course Manager)
Admin/Instructor yang manage course bisa melihat semua submission:

**Allowed Includes:**
- `answers` - Jawaban student
- `quiz` - Informasi quiz
- `user` - Informasi student yang submit

**Example:**
```
GET /quizzes/123/submissions/456?include=answers,user
Authorization: Bearer {instructor_token}
```

**Validation:**
- Superadmin: Full access
- Admin: Harus assigned ke course yang terkait
- Instructor: Harus instructor dari course yang terkait

---

## Implementation Details

### Architecture

```
Controller
    ↓
IncludeAuthorizer (checks user role & enrollment)
    ↓
QueryBuilder (with filtered allowedIncludes)
    ↓
Resource (with whenLoaded)
```

### Authorizer Classes

1. **CourseIncludeAuthorizer** - `Modules/Schemes/app/Services/Support/CourseIncludeAuthorizer.php`
   - Handles course includes authorization
   - Checks enrollment status for student access
   - Validates course manager permissions

2. **UnitIncludeAuthorizer** - `Modules/Schemes/app/Services/Support/UnitIncludeAuthorizer.php`
   - Handles unit includes authorization
   - Checks parent course enrollment

3. **QuizSubmissionIncludeAuthorizer** - `Modules/Learning/app/Services/Support/QuizSubmissionIncludeAuthorizer.php`
   - Handles quiz submission includes authorization
   - Validates ownership or manager access

### Key Methods

```php
public function getAllowedIncludesForQueryBuilder(?User $user, Model $model): array
```
- Returns array of allowed include strings based on user permissions
- Used by QueryBuilder's `allowedIncludes()`

```php
private function isManager(User $user, Course $course): bool
```
- Checks if user is Superadmin, Admin (assigned), or Instructor (owner)

```php
private function isEnrolledStudent(User $user, Course $course): bool
```
- Checks if user has active enrollment in course

---

## Security Considerations

1. **Default Deny**: Jika user tidak memenuhi kriteria, include tidak akan di-load
2. **No Error Messages**: Tidak ada error jika request include yang tidak diizinkan, simply ignored
3. **Enrollment Validation**: Selalu check status enrollment = 'active'
4. **Manager Validation**: Selalu check relationship ke course (admins table atau instructor_id)

---

## Testing Scenarios

### Test Case 1: Public User Request Restricted Include
```
GET /courses/web-dev?include=enrollments
Response: enrollments tidak di-load (ignored)
```

### Test Case 2: Student Request Lesson Without Enrollment
```
GET /courses/web-dev?include=lessons
Authorization: Bearer {student_token_not_enrolled}
Response: lessons tidak di-load (ignored)
```

### Test Case 3: Enrolled Student Request Lessons
```
GET /courses/web-dev?include=lessons,quizzes
Authorization: Bearer {enrolled_student_token}
Response: lessons dan quizzes di-load
```

### Test Case 4: Admin Request Enrollments
```
GET /courses/web-dev?include=enrollments.user
Authorization: Bearer {admin_token}
Response: enrollments dengan user di-load (jika admin assigned ke course)
```

### Test Case 5: Student Request Other Student's Submission
```
GET /quizzes/123/submissions/456?include=answers
Authorization: Bearer {other_student_token}
Response: 403 Forbidden (dari policy, bukan include authorization)
```

---

## Migration Guide

### Before (Hardcoded)
```php
public function show(Course $course)
{
    return $this->success(
        new CourseResource($course->load(['tags', 'units', 'lessons']))
    );
}
```

### After (Dynamic with Authorization)
```php
public function show(Course $course)
{
    $courseWithIncludes = $this->service->findBySlugWithIncludes($course->slug);
    return $this->success(new CourseResource($courseWithIncludes));
}
```

Service handles authorization automatically based on authenticated user and requested includes.

---

## Future Enhancements

1. **Assignment Includes Authorization** - Similar to Quiz Submission
2. **Lesson Includes Authorization** - For lesson detail endpoint
3. **Grading Includes Authorization** - For submission grading data
4. **Cache Includes by User Role** - Optimize repeated requests
5. **Include Authorization Logging** - Track unauthorized include attempts

---

## Related Files

- `Modules/Schemes/app/Services/Support/CourseIncludeAuthorizer.php`
- `Modules/Schemes/app/Services/Support/UnitIncludeAuthorizer.php`
- `Modules/Learning/app/Services/Support/QuizSubmissionIncludeAuthorizer.php`
- `Modules/Schemes/app/Services/Support/CourseFinder.php`
- `Modules/Schemes/app/Services/UnitService.php`
- `Modules/Learning/app/Http/Controllers/QuizSubmissionController.php`
