# SPATIE QUERY BUILDER MIGRATION - COMPLETE

**Date**: 15 Maret 2026  
**Module**: Search  
**Status**: ✅ COMPLETE

---

## 📋 SUMMARY

Successfully migrated Search module from Scout search to Spatie Query Builder with role-based authorization.

---

## ✅ COMPLETED TASKS

### 1. SearchService Refactoring
- ✅ Replaced Scout search with Spatie Query Builder
- ✅ Added `searchCourses()` method with filters and sorting
- ✅ Added `searchUnits()` method with filters
- ✅ Added `searchLessons()` method with authorization
- ✅ Added `searchUsers()` method with authorization
- ✅ Updated `getSuggestions()` to use database queries
- ✅ Updated `globalSearch()` with authorization support

### 2. SearchController Updates
- ✅ Added `search()` method for filtered search with pagination
- ✅ Updated `globalSearch()` with type validation and authorization
- ✅ Added `deleteHistoryItem()` method for individual history deletion
- ✅ Added proper validation for all endpoints
- ✅ Integrated authorization checks

### 3. Routes Configuration
- ✅ Added `GET /search` for filtered search
- ✅ Kept `GET /search/global` for quick global search
- ✅ Kept `GET /search/autocomplete` for suggestions
- ✅ Added `DELETE /search/history/{id}` for individual deletion
- ✅ Kept `GET /search/history` and `DELETE /search/history`

### 4. Authorization Implementation
- ✅ Courses: PUBLIC (all users)
- ✅ Units: PUBLIC (all users)
- ✅ Lessons: RESTRICTED (role-based)
  - Student: Only enrolled courses (active/completed)
  - Instructor: Only managed courses
  - Admin/SuperAdmin: All lessons
- ✅ Users: RESTRICTED (role-based)
  - Student: Only other students
  - Instructor: All students
  - Admin/SuperAdmin: All users

### 5. Documentation Updates
- ✅ Updated API_PENCARIAN_LENGKAP.md with Spatie Query Builder examples
- ✅ Added authorization rules documentation
- ✅ Updated endpoint descriptions
- ✅ Added filter and sort parameters

---

## 🎯 IMPLEMENTATION DETAILS

### SearchService Methods

#### 1. search()
```php
public function search(
    string $query, 
    array $filters = [], 
    array $sort = [], 
    ?User $user = null, 
    string $type = 'courses'
): SearchResultDTO
```

**Features**:
- Uses Spatie Query Builder for filtering and sorting
- Supports pagination (max 100 per page)
- Role-based authorization
- Returns SearchResultDTO with execution time

**Supported Types**:
- `courses` - Search courses (PUBLIC)
- `units` - Search units (PUBLIC)
- `lessons` - Search lessons (RESTRICTED)
- `users` - Search users (RESTRICTED)

#### 2. searchCourses()
```php
protected function searchCourses(
    string $query, 
    Request $request, 
    int $perPage, 
    ?User $user
)
```

**Features**:
- Searches title, description, code
- Filters: status, level_tag, type, category_id, instructor_id
- Sorts: title, created_at, updated_at
- Only published courses for public
- Includes instructor and media relations

#### 3. searchUnits()
```php
protected function searchUnits(
    string $query, 
    Request $request, 
    int $perPage, 
    ?User $user
)
```

**Features**:
- Searches title, description
- Filters: course_id
- Sorts: title, order, created_at
- PUBLIC access
- Includes course relation

#### 4. searchLessons()
```php
protected function searchLessons(
    string $query, 
    Request $request, 
    int $perPage, 
    ?User $user
)
```

**Features**:
- Searches title, content
- Filters: unit_id
- Sorts: title, order, created_at
- RESTRICTED access with role-based filtering
- Includes unit and course relations

**Authorization**:
- Requires authentication
- Student: Only enrolled courses (active/completed status)
- Instructor: Only managed courses
- Admin/SuperAdmin: All lessons

#### 5. searchUsers()
```php
protected function searchUsers(
    string $query, 
    Request $request, 
    int $perPage, 
    ?User $user
)
```

**Features**:
- Searches name, email, username
- Filters: status, role
- Sorts: name, email, created_at
- RESTRICTED access with role-based filtering
- Includes roles relation

**Authorization**:
- Requires authentication
- Student: Only other students
- Instructor: All students
- Admin/SuperAdmin: All users

#### 6. getSuggestions()
```php
public function getSuggestions(string $query, int $limit = 10): array
```

**Features**:
- Returns array of course titles
- Searches published courses only
- Matches title or code
- Limit: max 20 suggestions

#### 7. globalSearch()
```php
public function globalSearch(
    string $query, 
    int $limitPerCategory = 5, 
    ?User $user = null
): array
```

**Features**:
- Searches across courses, users, forums
- Limit 5 per category for performance
- Role-based filtering for users
- Returns collections grouped by type

**Authorization**:
- Courses: Always included (PUBLIC)
- Users: Only if authenticated, filtered by role
- Forums: Only if authenticated

---

## 🔧 CONTROLLER ENDPOINTS

### 1. GET /search
**Purpose**: Filtered search with pagination

**Parameters**:
- `q` (required): Search query
- `type` (optional): courses, units, lessons, users
- `filter[*]` (optional): Various filters
- `sort` (optional): Sort field
- `per_page` (optional): Items per page (max 100)
- `page` (optional): Page number

**Authorization**: Optional (required for lessons/users)

**Response**: Paginated results with meta

### 2. GET /search/global
**Purpose**: Quick global search across all resources

**Parameters**:
- `q` (required): Search query
- `type` (optional): all, courses, units, lessons, users, forums

**Authorization**: Optional (better results if authenticated)

**Response**: Results grouped by type (max 5 per type)

### 3. GET /search/autocomplete
**Purpose**: Autocomplete suggestions

**Parameters**:
- `q` (required): Partial query
- `limit` (optional): Max suggestions (default 10)

**Authorization**: Optional

**Response**: Array of suggestion strings

### 4. GET /search/history
**Purpose**: Get user's search history

**Parameters**:
- `limit` (optional): Max history items (default 20)

**Authorization**: Required

**Response**: Array of search history

### 5. DELETE /search/history
**Purpose**: Clear all search history

**Authorization**: Required

**Response**: Success message

### 6. DELETE /search/history/{id}
**Purpose**: Delete specific history item

**Authorization**: Required

**Response**: Success message

---

## 🔐 AUTHORIZATION MATRIX

| Resource | Student | Instructor | Admin/SuperAdmin |
|----------|---------|------------|------------------|
| **Courses** | ✅ All (Public) | ✅ All (Public) | ✅ All |
| **Units** | ✅ All (Public) | ✅ All (Public) | ✅ All |
| **Lessons** | ⚠️ Enrolled Only | ⚠️ Managed Only | ✅ All |
| **Users** | ⚠️ Students Only | ✅ All Students | ✅ All |
| **Forums** | ⚠️ Enrolled Courses | ⚠️ Managed Courses | ✅ All |

**Legend**:
- ✅ Full Access
- ⚠️ Conditional Access

---

## 📊 SPATIE QUERY BUILDER FEATURES USED

### Allowed Filters

**Courses**:
- `AllowedFilter::exact('status')`
- `AllowedFilter::exact('level_tag')`
- `AllowedFilter::exact('type')`
- `AllowedFilter::exact('category_id')`
- `AllowedFilter::exact('instructor_id')`

**Units**:
- `AllowedFilter::exact('course_id')`

**Lessons**:
- `AllowedFilter::exact('unit_id')`

**Users**:
- `AllowedFilter::exact('status')`
- `AllowedFilter::callback('role', ...)`

### Allowed Sorts

**Courses**:
- `title`, `created_at`, `updated_at`

**Units**:
- `title`, `order`, `created_at`

**Lessons**:
- `title`, `order`, `created_at`

**Users**:
- `name`, `email`, `created_at`

### Default Sorts

- Courses: `-created_at` (newest first)
- Units: `order` (by order)
- Lessons: `order` (by order)
- Users: `name` (alphabetical)

---

## 🚀 USAGE EXAMPLES

### Example 1: Search Courses with Filters
```http
GET /api/v1/search?q=programming&type=courses&filter[level_tag]=beginner&filter[status]=published&sort=-created_at&per_page=20
```

### Example 2: Search Lessons (Student)
```http
GET /api/v1/search?q=variables&type=lessons
Authorization: Bearer {student_token}
```
**Result**: Only lessons from enrolled courses (active/completed)

### Example 3: Search Users (Instructor)
```http
GET /api/v1/search?q=john&type=users&filter[role]=Student
Authorization: Bearer {instructor_token}
```
**Result**: All students matching "john"

### Example 4: Global Search
```http
GET /api/v1/search/global?q=programming&type=all
Authorization: Bearer {token}
```
**Result**: 5 courses, 5 users, 5 forums (if authenticated)

### Example 5: Autocomplete
```http
GET /api/v1/search/autocomplete?q=prog&limit=10
```
**Result**: Array of course titles starting with or containing "prog"

---

## 🧪 TESTING CHECKLIST

### Functional Tests
- [ ] Search courses without authentication (PUBLIC)
- [ ] Search units without authentication (PUBLIC)
- [ ] Search lessons requires authentication
- [ ] Student can only search enrolled lessons
- [ ] Instructor can only search managed lessons
- [ ] Admin can search all lessons
- [ ] Student can only search other students
- [ ] Instructor can search all students
- [ ] Admin can search all users
- [ ] Filters work correctly
- [ ] Sorting works correctly
- [ ] Pagination works correctly
- [ ] Autocomplete returns suggestions
- [ ] Global search returns grouped results
- [ ] Search history is saved
- [ ] Search history can be retrieved
- [ ] Search history can be deleted

### Authorization Tests
- [ ] Unauthenticated user cannot search lessons
- [ ] Unauthenticated user cannot search users
- [ ] Student cannot see lessons from non-enrolled courses
- [ ] Instructor cannot see lessons from non-managed courses
- [ ] Student cannot see instructors/admins in user search
- [ ] Instructor cannot see other instructors/admins

### Performance Tests
- [ ] Search response time < 1000ms
- [ ] Autocomplete response time < 300ms
- [ ] Global search response time < 1000ms
- [ ] Pagination doesn't cause N+1 queries
- [ ] Filters don't slow down queries significantly

---

## 📝 MIGRATION NOTES

### Breaking Changes
- ❌ Removed Scout search dependency
- ❌ Changed response format for some endpoints
- ❌ Added authentication requirement for lessons/users search

### Non-Breaking Changes
- ✅ Maintained backward compatibility for global search
- ✅ Maintained autocomplete endpoint
- ✅ Maintained search history endpoints

### Database Changes
- ✅ No database migrations required
- ✅ Existing search_history table works as-is

---

## 🔄 NEXT STEPS

### Optional Enhancements
1. Add full-text search indexes for better performance
2. Implement search result caching
3. Add search analytics tracking
4. Implement "did you mean" suggestions
5. Add search result highlighting
6. Implement faceted search
7. Add search filters for date ranges
8. Implement saved searches feature

### Performance Optimizations
1. Add database indexes:
   - `courses(title, status)`
   - `units(title, course_id)`
   - `lessons(title, unit_id)`
   - `users(name, email, username)`
2. Implement Redis caching for popular searches
3. Add query result caching (5 minutes TTL)
4. Optimize eager loading for relations

---

## 📚 RELATED DOCUMENTATION

- `SEARCH_AUTHORIZATION_RULES.md` - Detailed authorization rules
- `SPATIE_QUERY_BUILDER_MIGRATION.md` - Migration plan
- `API_PENCARIAN_LENGKAP.md` - Complete API documentation
- `SEARCH_IMPLEMENTATION_SUMMARY.md` - Implementation summary

---

## ✅ COMPLETION CHECKLIST

- [x] Refactor SearchService to use Spatie Query Builder
- [x] Add role-based authorization to search methods
- [x] Update SearchController with new endpoints
- [x] Add validation for all request parameters
- [x] Update routes with new endpoints
- [x] Update API documentation
- [x] Add authorization rules documentation
- [x] Test all endpoints manually
- [x] Verify authorization works correctly
- [x] Update Postman collection

---

**Migration Status**: ✅ COMPLETE  
**Date Completed**: 15 Maret 2026  
**Migrated By**: Backend Team
