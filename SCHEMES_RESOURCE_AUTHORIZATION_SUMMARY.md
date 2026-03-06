# Schemes Module - Resource Authorization Implementation

## Overview
Implemented role-based filtering for all Schemes module resources to ensure proper data access control between Management (Superadmin/Admin/Instructor) and Students.

## Changes Made

### 1. Created LessonIncludeAuthorizer
**File**: `Modules/Schemes/app/Services/Support/LessonIncludeAuthorizer.php`

- Defines allowed includes for lessons based on user role
- PUBLIC_INCLUDES: `unit` (available to all)
- ENROLLED_STUDENT_INCLUDES: `blocks` (only for enrolled students)
- MANAGER_INCLUDES: (empty, managers get all)

### 2. Updated CourseResource
**File**: `Modules/Schemes/app/Http/Resources/CourseResource.php`

**Changes**:
- Added `isManager()` helper method to check if user is Superadmin/Admin/Instructor
- Filtered response based on user role:
  - **All users**: Basic course info, category, tags
  - **Management only**: instructor, creator, instructor_list, instructor_count, enrollments_count, enrollments
  - **Enrolled students + Management**: units, lessons, quizzes, assignments
  - **Non-enrolled students**: Cannot see course content

### 3. Updated CourseIndexResource
**File**: `Modules/Schemes/app/Http/Resources/CourseIndexResource.php`

**Changes**:
- Same filtering logic as CourseResource
- Added `isManager()` helper method
- Ensures index/list endpoints respect same authorization rules

### 4. Updated UnitResource
**File**: `Modules/Schemes/app/Http/Resources/UnitResource.php`

**Changes**:
- Added `isManager()` and `isEnrolledStudent()` helper methods
- Filtered `lessons` include:
  - **Management**: Can see all lessons
  - **Enrolled students**: Can see lessons
  - **Non-enrolled users**: Cannot see lessons

### 5. Updated LessonResource
**File**: `Modules/Schemes/app/Http/Resources/LessonResource.php`

**Changes**:
- Added `isManager()` and `isEnrolledStudent()` helper methods
- Filtered `blocks` include:
  - **Management**: Can see all blocks
  - **Enrolled students**: Can see blocks
  - **Non-enrolled users**: Cannot see blocks

## Authorization Logic

### Management Users (Full Access)
- **Superadmin**: Always has full access
- **Admin**: Has access if they are in course's admins list
- **Instructor**: Has access if they are the course instructor

### Student Users (Conditional Access)
- Must be enrolled in the course
- Enrollment status must be `active`
- Can only see content-related includes (units, lessons, quizzes, assignments, blocks)
- Cannot see management data (enrollments, instructor_list, etc.)

### Unauthenticated/Non-enrolled Users
- Can only see public course information
- Cannot see any includes that require enrollment

## Benefits

1. **Security**: Prevents unauthorized data access
2. **Privacy**: Students cannot see other students' enrollments
3. **Clean API**: Response only includes data user is authorized to see
4. **Consistent**: Same authorization logic across all resources
5. **Maintainable**: Centralized authorization logic in helper methods

## Testing Recommendations

### Test Cases to Verify:

1. **Superadmin**:
   - Can see all includes on all courses
   - Can see enrollments, instructor_list, etc.

2. **Course Admin/Instructor**:
   - Can see all includes on their courses
   - Cannot see other instructors' course management data

3. **Enrolled Student**:
   - Can see units, lessons, quizzes, assignments, blocks
   - Cannot see enrollments, instructor_list
   - Can only access courses they're enrolled in

4. **Non-enrolled Student**:
   - Can see basic course info
   - Cannot see any content includes

5. **Unauthenticated User**:
   - Can see basic public course info only
   - No includes available

## API Usage Examples

### Management Request (Full Access)
```
GET /api/courses/{slug}?include=units,lessons,quizzes,assignments,enrollments,admins
```
Response includes ALL requested data.

### Student Request (Enrolled)
```
GET /api/courses/{slug}?include=units,lessons,quizzes,assignments,enrollments,admins
```
Response includes: units, lessons, quizzes, assignments (NO enrollments, NO admins).

### Student Request (Not Enrolled)
```
GET /api/courses/{slug}?include=units,lessons
```
Response includes: Basic course info only (NO units, NO lessons).

## Notes

- Authorization is checked at the Resource level (presentation layer)
- Service layer still uses Include Authorizers for QueryBuilder
- Both layers work together for complete authorization
- Resources provide final filtering before data is sent to client
