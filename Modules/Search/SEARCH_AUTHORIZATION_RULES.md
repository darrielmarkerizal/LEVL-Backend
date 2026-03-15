# SEARCH AUTHORIZATION RULES - LEVL API
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Module**: Search  
**Purpose**: Dokumentasi aturan authorization untuk search API

---

## 📋 RINGKASAN ATURAN

### Authorization Matrix

| Resource Type | Student | Instructor | Admin/SuperAdmin |
|--------------|---------|------------|------------------|
| **Courses** | ✅ All (Public) | ✅ All (Public) | ✅ All |
| **Units** | ✅ All (Public) | ✅ All (Public) | ✅ All |
| **Lessons (Elemen)** | ⚠️ Enrolled Only | ⚠️ Managed Courses | ✅ All |
| **Assignments** | ⚠️ Enrolled Only | ⚠️ Managed Courses | ✅ All |
| **Quizzes** | ⚠️ Enrolled Only | ⚠️ Managed Courses | ✅ All |
| **Users** | ⚠️ Students Only | ✅ All Students | ✅ All |
| **Forums** | ⚠️ Enrolled Courses | ⚠️ Managed Courses | ✅ All |

**Legend**:
- ✅ Full Access
- ⚠️ Conditional Access
- ❌ No Access

---

## 👥 ROLE-BASED ACCESS RULES

### 1. STUDENT ROLE

#### Courses & Units (PUBLIC)
```php
// Students can search ALL courses and units
// No enrollment check required
$courses = Course::search($query)->get();
$units = Unit::search($query)->get();
```

#### Lessons/Elemen (RESTRICTED)
```php
// Students can ONLY search lessons in courses they are enrolled in
// Enrollment status must be: active OR completed
$lessons = Lesson::search($query)
    ->whereIn('course_id', function($query) use ($userId) {
        $query->select('course_id')
            ->from('enrollments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed']);
    })
    ->get();
```

#### Assignments & Quizzes (RESTRICTED)
```php
// Same rule as lessons - only from enrolled courses
$assignments = Assignment::search($query)
    ->whereIn('course_id', function($query) use ($userId) {
        $query->select('course_id')
            ->from('enrollments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed']);
    })
    ->get();
```

#### Users (RESTRICTED)
```php
// Students can ONLY search other students
// Cannot see instructors or admins
$users = User::search($query)
    ->whereHas('roles', function($query) {
        $query->where('name', 'Student');
    })
    ->get();
```

#### Forums (RESTRICTED)
```php
// Students can only search forums in enrolled courses
$forums = Thread::search($query)
    ->whereIn('course_id', function($query) use ($userId) {
        $query->select('course_id')
            ->from('enrollments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed']);
    })
    ->get();
```

---

### 2. INSTRUCTOR ROLE

#### Courses & Units (PUBLIC)
```php
// Instructors can search ALL courses and units
$courses = Course::search($query)->get();
$units = Unit::search($query)->get();
```

#### Lessons/Elemen (MANAGED COURSES)
```php
// Instructors can search lessons in courses they manage
$lessons = Lesson::search($query)
    ->whereIn('course_id', function($query) use ($userId) {
        $query->select('id')
            ->from('courses')
            ->where('instructor_id', $userId);
    })
    ->get();
```

#### Assignments & Quizzes (MANAGED COURSES)
```php
// Same rule as lessons - only from managed courses
$assignments = Assignment::search($query)
    ->whereIn('course_id', function($query) use ($userId) {
        $query->select('id')
            ->from('courses')
            ->where('instructor_id', $userId);
    })
    ->get();
```

#### Users (ALL STUDENTS)
```php
// Instructors can search ALL students
// Cannot see other instructors or admins (unless needed for collaboration)
$users = User::search($query)
    ->whereHas('roles', function($query) {
        $query->where('name', 'Student');
    })
    ->get();
```

#### Forums (MANAGED COURSES)
```php
// Instructors can search forums in courses they manage
$forums = Thread::search($query)
    ->whereIn('course_id', function($query) use ($userId) {
        $query->select('id')
            ->from('courses')
            ->where('instructor_id', $userId);
    })
    ->get();
```

---

### 3. ADMIN & SUPERADMIN ROLE

#### All Resources (FULL ACCESS)
```php
// Admins and SuperAdmins can search ALL resources
// No restrictions applied
$courses = Course::search($query)->get();
$units = Unit::search($query)->get();
$lessons = Lesson::search($query)->get();
$assignments = Assignment::search($query)->get();
$quizzes = Quiz::search($query)->get();
$users = User::search($query)->get();
$forums = Thread::search($query)->get();
```

---

## 🔐 IMPLEMENTATION GUIDELINES

### 1. Search Service Authorization

```php
class SearchService
{
    public function search(string $query, string $type, User $user): array
    {
        return match($type) {
            'courses' => $this->searchCourses($query, $user),
            'units' => $this->searchUnits($query, $user),
            'lessons' => $this->searchLessons($query, $user),
            'assignments' => $this->searchAssignments($query, $user),
            'quizzes' => $this->searchQuizzes($query, $user),
            'users' => $this->searchUsers($query, $user),
            'forums' => $this->searchForums($query, $user),
            'all' => $this->globalSearch($query, $user),
        };
    }

    protected function searchLessons(string $query, User $user): Collection
    {
        $baseQuery = Lesson::search($query);

        // Apply role-based filters
        if ($user->hasRole('Student')) {
            return $this->filterLessonsForStudent($baseQuery, $user);
        }

        if ($user->hasRole('Instructor')) {
            return $this->filterLessonsForInstructor($baseQuery, $user);
        }

        // Admin/SuperAdmin - no filter
        return $baseQuery->get();
    }

    protected function filterLessonsForStudent($query, User $user): Collection
    {
        return $query->whereIn('course_id', function($q) use ($user) {
            $q->select('course_id')
                ->from('enrollments')
                ->where('user_id', $user->id)
                ->whereIn('status', ['active', 'completed']);
        })->get();
    }

    protected function filterLessonsForInstructor($query, User $user): Collection
    {
        return $query->whereIn('course_id', function($q) use ($user) {
            $q->select('id')
                ->from('courses')
                ->where('instructor_id', $user->id);
        })->get();
    }
}
```

### 2. Controller Authorization

```php
class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $user = auth()->user();

        // Validate user has permission to search
        if (!$user) {
            // Public search - only courses and units
            if (!in_array($type, ['courses', 'units', 'all'])) {
                return $this->error(
                    message: 'Authentication required for this search type',
                    code: 401
                );
            }
        }

        $results = $this->searchService->search($query, $type, $user);

        return $this->success(data: $results);
    }
}
```

---

## 📊 ENROLLMENT STATUS VALIDATION

### Valid Enrollment Statuses for Content Access

```php
const VALID_ENROLLMENT_STATUSES = ['active', 'completed'];
```

### Enrollment Status Definitions

| Status | Can Access Content | Description |
|--------|-------------------|-------------|
| `active` | ✅ Yes | Currently enrolled and active |
| `completed` | ✅ Yes | Completed the course |
| `suspended` | ❌ No | Enrollment suspended |
| `cancelled` | ❌ No | Enrollment cancelled |
| `pending` | ❌ No | Waiting for approval |

### Enrollment Check Query

```php
// Check if user has valid enrollment
$hasValidEnrollment = Enrollment::where('user_id', $userId)
    ->where('course_id', $courseId)
    ->whereIn('status', ['active', 'completed'])
    ->exists();
```

---

## 🎯 SEARCH SCOPE EXAMPLES

### Example 1: Student Searching Lessons

```php
// Student ID: 5
// Enrolled in courses: [1, 3, 5] with status 'active'

// Search query: "variables"
$results = Lesson::search('variables')
    ->whereIn('course_id', [1, 3, 5])  // Only enrolled courses
    ->get();

// Results will ONLY include lessons from courses 1, 3, 5
```

### Example 2: Instructor Searching Assignments

```php
// Instructor ID: 10
// Manages courses: [2, 4, 6]

// Search query: "final project"
$results = Assignment::search('final project')
    ->whereIn('course_id', [2, 4, 6])  // Only managed courses
    ->get();

// Results will ONLY include assignments from courses 2, 4, 6
```

### Example 3: Admin Searching All Content

```php
// Admin ID: 1
// No restrictions

// Search query: "programming"
$results = Lesson::search('programming')
    ->get();  // No filters applied

// Results include ALL lessons matching "programming"
```

---

## 🔍 TESTING AUTHORIZATION

### Test Cases

#### 1. Student Access Test
```php
public function test_student_can_only_search_enrolled_lessons()
{
    $student = User::factory()->create();
    $student->assignRole('Student');
    
    $course1 = Course::factory()->create();
    $course2 = Course::factory()->create();
    
    // Enroll in course1 only
    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course1->id,
        'status' => 'active'
    ]);
    
    $lesson1 = Lesson::factory()->create(['course_id' => $course1->id]);
    $lesson2 = Lesson::factory()->create(['course_id' => $course2->id]);
    
    $results = $this->searchService->searchLessons('test', $student);
    
    $this->assertTrue($results->contains($lesson1));
    $this->assertFalse($results->contains($lesson2));
}
```

#### 2. Instructor Access Test
```php
public function test_instructor_can_only_search_managed_course_content()
{
    $instructor = User::factory()->create();
    $instructor->assignRole('Instructor');
    
    $course1 = Course::factory()->create(['instructor_id' => $instructor->id]);
    $course2 = Course::factory()->create(); // Different instructor
    
    $lesson1 = Lesson::factory()->create(['course_id' => $course1->id]);
    $lesson2 = Lesson::factory()->create(['course_id' => $course2->id]);
    
    $results = $this->searchService->searchLessons('test', $instructor);
    
    $this->assertTrue($results->contains($lesson1));
    $this->assertFalse($results->contains($lesson2));
}
```

#### 3. Admin Access Test
```php
public function test_admin_can_search_all_content()
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    
    $lesson1 = Lesson::factory()->create();
    $lesson2 = Lesson::factory()->create();
    $lesson3 = Lesson::factory()->create();
    
    $results = $this->searchService->searchLessons('test', $admin);
    
    $this->assertCount(3, $results);
}
```

---

## ⚠️ SECURITY CONSIDERATIONS

### 1. Prevent Data Leakage
- Always apply role-based filters BEFORE returning results
- Never expose enrollment status in public search
- Sanitize search queries to prevent injection

### 2. Performance Optimization
- Index enrollment table for faster lookups
- Cache user's enrolled courses
- Use eager loading to prevent N+1 queries

### 3. Audit Logging
- Log all search queries with user context
- Track unauthorized access attempts
- Monitor search patterns for anomalies

---

## 📝 MIGRATION CHECKLIST

- [ ] Update SearchService with role-based filtering
- [ ] Add enrollment status validation
- [ ] Implement course ownership checks for instructors
- [ ] Add authorization tests
- [ ] Update API documentation
- [ ] Add audit logging
- [ ] Performance test with large datasets
- [ ] Security audit

---

**Dokumen ini adalah panduan implementasi authorization untuk Search API.**

**Versi**: 1.0  
**Terakhir Update**: 15 Maret 2026  
**Maintainer**: Backend Team
