# Course & Unit Authorization Summary

## Overview
Implementasi authorization untuk endpoint `/courses/:slug` dan `/courses/:slug/units` dengan akses bertingkat berdasarkan role dan status.

## Authorization Rules

### 1. Course Access (`/courses/:slug`)

**Policy**: `CoursePolicy::view()`

**Access Levels**:

1. **Public/Unauthenticated Users**:
   - ✅ Can view PUBLISHED courses only
   - ❌ Cannot view DRAFT courses

2. **Students**:
   - ✅ Can view PUBLISHED courses
   - ❌ Cannot view DRAFT courses (even if enrolled)

3. **Instructors**:
   - ✅ Can view PUBLISHED courses
   - ✅ Can view their OWN DRAFT courses (where `instructor_id` = user.id)
   - ❌ Cannot view other instructors' DRAFT courses

4. **Admins**:
   - ✅ Can view PUBLISHED courses
   - ✅ Can view DRAFT courses they are assigned to (via `course_admins` table)
   - ❌ Cannot view DRAFT courses they are not assigned to

5. **Superadmins**:
   - ✅ Can view ALL courses (published or draft)

### 2. Units List Access (`/courses/:slug/units`)

**Policy**: `CoursePolicy::viewUnits()`

**Access Levels**: Same as Course Access (delegates to `CoursePolicy::view()`)

### 3. Individual Unit Access (`/courses/:slug/units/:id`)

**Policy**: `UnitPolicy::view()`

**Access Levels**:

1. **Public/Unauthenticated Users**:
   - ✅ Can view PUBLISHED units in PUBLISHED courses
   - ❌ Cannot view DRAFT units
   - ❌ Cannot view units in DRAFT courses

2. **Students**:
   - ✅ Can view PUBLISHED units in PUBLISHED courses
   - ✅ Can view units in courses they are ENROLLED in (status: active/completed)
   - ❌ Cannot view DRAFT units (even if enrolled)
   - ❌ Cannot view units in courses they are NOT enrolled in

3. **Instructors**:
   - ✅ Can view ALL units (published or draft) in their OWN courses
   - ✅ Can view PUBLISHED units in other courses
   - ❌ Cannot view DRAFT units in other instructors' courses

4. **Admins**:
   - ✅ Can view ALL units (published or draft) in courses they are assigned to
   - ✅ Can view PUBLISHED units in other courses
   - ❌ Cannot view DRAFT units in courses they are not assigned to

5. **Superadmins**:
   - ✅ Can view ALL units in ALL courses (published or draft)

## Implementation Details

### Controller Authorization

**CourseController::show()**:
```php
$this->authorize('view', $course);
```

**UnitController::index()**:
```php
$this->authorize('viewUnits', $course);
```

**UnitController::show()**:
```php
$this->authorize('view', $unit);
```

### Policy Logic

**CoursePolicy::view()**:
- Checks if course is published → allow all
- If draft → check user role and ownership/assignment

**UnitPolicy::view()**:
- Checks if unit AND course are both published → allow all
- If either is draft → check user role and ownership/assignment/enrollment

## Resource Filtering

Resources (`CourseResource`, `UnitResource`, `LessonResource`) also implement role-based filtering:

1. **Management (Superadmin/Admin/Instructor)**:
   - See all data including management fields
   - Can access all includes

2. **Enrolled Students**:
   - See course content (units, lessons, quizzes, assignments)
   - Cannot see management data (enrollments, instructor_list)

3. **Non-enrolled Students/Public**:
   - See basic course info only
   - Cannot see course content

## Testing Scenarios

### Scenario 1: Public User
- ✅ GET `/courses/published-course` → 200 OK
- ❌ GET `/courses/draft-course` → 403 Forbidden
- ✅ GET `/courses/published-course/units` → 200 OK (only published units)
- ❌ GET `/courses/draft-course/units` → 403 Forbidden

### Scenario 2: Student (Not Enrolled)
- ✅ GET `/courses/published-course` → 200 OK
- ❌ GET `/courses/draft-course` → 403 Forbidden
- ✅ GET `/courses/published-course/units` → 200 OK (only published units)
- ❌ GET `/courses/published-course/units/draft-unit` → 403 Forbidden

### Scenario 3: Student (Enrolled)
- ✅ GET `/courses/enrolled-course` → 200 OK
- ✅ GET `/courses/enrolled-course/units` → 200 OK (all units)
- ✅ GET `/courses/enrolled-course/units/any-unit` → 200 OK
- ❌ GET `/courses/not-enrolled-course/units/unit` → 403 Forbidden

### Scenario 4: Instructor (Own Course)
- ✅ GET `/courses/own-draft-course` → 200 OK
- ✅ GET `/courses/own-draft-course/units` → 200 OK
- ✅ GET `/courses/own-draft-course/units/draft-unit` → 200 OK
- ❌ GET `/courses/other-instructor-draft-course` → 403 Forbidden

### Scenario 5: Admin (Assigned Course)
- ✅ GET `/courses/assigned-draft-course` → 200 OK
- ✅ GET `/courses/assigned-draft-course/units` → 200 OK
- ✅ GET `/courses/assigned-draft-course/units/draft-unit` → 200 OK
- ❌ GET `/courses/not-assigned-draft-course` → 403 Forbidden

### Scenario 6: Superadmin
- ✅ GET `/courses/any-course` → 200 OK
- ✅ GET `/courses/any-course/units` → 200 OK
- ✅ GET `/courses/any-course/units/any-unit` → 200 OK

## Security Notes

1. **Double Authorization**: Both Policy (controller) and Resource (response) implement authorization
2. **Status Checks**: Always check both unit AND course status for proper access control
3. **Enrollment Verification**: Students must have active/completed enrollment to access content
4. **Assignment Verification**: Admins must be explicitly assigned to course via `course_admins` table
5. **Ownership Verification**: Instructors must be the course owner (`instructor_id`)

## Related Files

- `Modules/Schemes/app/Policies/CoursePolicy.php`
- `Modules/Schemes/app/Policies/UnitPolicy.php`
- `Modules/Schemes/app/Http/Controllers/CourseController.php`
- `Modules/Schemes/app/Http/Controllers/UnitController.php`
- `Modules/Schemes/app/Http/Resources/CourseResource.php`
- `Modules/Schemes/app/Http/Resources/UnitResource.php`
- `Modules/Schemes/app/Http/Resources/LessonResource.php`
