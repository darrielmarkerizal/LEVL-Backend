# SPATIE QUERY BUILDER MIGRATION - SEARCH MODULE
**Versi**: 1.0  
**Tanggal**: 15 Maret 2026  
**Status**: 🔄 Migration Required

---

## 📋 OVERVIEW

Search module perlu diupdate untuk menggunakan Spatie Query Builder secara konsisten dengan module lain (Auth, Schemes, Forums) dan mengimplementasikan authorization rules yang telah didefinisikan.

---

## 🎯 MIGRATION GOALS

1. ✅ Replace Scout search dengan Spatie Query Builder
2. ✅ Implement role-based authorization
3. ✅ Add filtering, sorting, pagination support
4. ✅ Maintain backward compatibility dengan API existing
5. ✅ Improve performance dengan proper indexing

---

## 📁 FILES TO UPDATE

### 1. SearchService.php
**Current**: Uses Scout search  
**Target**: Use Spatie Query Builder with authorization

### 2. SearchController.php
**Current**: Basic search endpoints  
**Target**: Add comprehensive search endpoints with filters

### 3. Routes (api.php)
**Current**: Limited endpoints  
**Target**: Add all documented endpoints

### 4. SearchHistoryRepository.php
**Current**: Basic CRUD  
**Target**: Add Spatie Query Builder support

---

## 🔧 IMPLEMENTATION PLAN

### Phase 1: Update SearchService

```php
<?php

namespace Modules\Search\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;

class SearchService
{
    /**
     * Search courses with Spatie Query Builder
     */
    public function searchCourses(string $query, ?User $user = null, ?Request $request = null): Collection
    {
        $baseQuery = QueryBuilder::for(Course::class, $request ?? new Request)
            ->allowedFilters([
                AllowedFilter::callback('q', function ($query, $value) {
                    $query->where(function($q) use ($value) {
                        $q->where('title', 'LIKE', "%{$value}%")
                          ->orWhere('description', 'LIKE', "%{$value}%")
                          ->orWhere('code', 'LIKE', "%{$value}%");
                    });
                }),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('level'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('instructor_id'),
            ])
            ->allowedSorts(['title', 'created_at', 'updated_at'])
            ->defaultSort('-created_at');

        // Apply authorization filters
        if (!$user || !$user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            $baseQuery->where('status', 'published');
        }

        return $baseQuery->get();
    }

    /**
     * Search users with authorization
     */
    public function searchUsers(string $query, User $authUser, ?Request $request = null): Collection
    {
        $baseQuery = QueryBuilder::for(User::class, $request ?? new Request)
            ->allowedFilters([
                AllowedFilter::callback('q', function ($query, $value) {
                    $query->where(function($q) use ($value) {
                        $q->where('name', 'LIKE', "%{$value}%")
                          ->orWhere('username', 'LIKE', "%{$value}%")
                          ->orWhere('email', 'LIKE', "%{$value}%");
                    });
                }),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->whereHas('roles', function($q) use ($value) {
                        $q->where('name', $value);
                    });
                }),
            ])
            ->allowedSorts(['name', 'created_at'])
            ->defaultSort('name');

        // Apply role-based filters
        if ($authUser->hasRole('Student')) {
            // Students can only see other students
            $baseQuery->whereHas('roles', function($q) {
                $q->where('name', 'Student');
            });
        } elseif ($authUser->hasRole('Instructor')) {
            // Instructors can see all students
            $baseQuery->whereHas('roles', function($q) {
                $q->where('name', 'Student');
            });
        }
        // Admin/SuperAdmin can see all

        return $baseQuery->get();
    }

    /**
     * Global search with authorization
     */
    public function globalSearch(string $query, ?User $user = null, ?Request $request = null): array
    {
        $request = $request ?? new Request(['filter' => ['q' => $query]]);

        return [
            'courses' => $this->searchCourses($query, $user, $request)->take(5),
            'users' => $user ? $this->searchUsers($query, $user, $request)->take(5) : collect([]),
            'forums' => $user ? $this->searchForums($query, $user, $request)->take(5) : collect([]),
        ];
    }
}
```

### Phase 2: Update SearchController

```php
<?php

namespace Modules\Search\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Search\Services\SearchService;

class SearchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SearchService $searchService
    ) {}

    /**
     * Global search
     * GET /api/v1/search
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'type' => 'nullable|in:all,courses,users,content,forums',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $user = auth()->user();

        $results = match($type) {
            'courses' => ['courses' => $this->searchService->searchCourses($query, $user, $request)],
            'users' => ['users' => $this->searchService->searchUsers($query, $user, $request)],
            'all' => $this->searchService->globalSearch($query, $user, $request),
            default => throw new \InvalidArgumentException("Invalid search type: {$type}"),
        };

        // Save to history if authenticated
        if ($user && !empty(trim($query))) {
            $totalResults = collect($results)->flatten()->count();
            $this->searchService->saveSearchHistory($user, $query, [], $totalResults);
        }

        return $this->success(data: $results);
    }

    /**
     * Autocomplete
     * GET /api/v1/search/autocomplete
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = $request->input('q');
        $limit = $request->input('limit', 10);

        $suggestions = $this->searchService->getSuggestions($query, $limit);

        return $this->success(data: ['suggestions' => $suggestions]);
    }

    /**
     * Search history
     * GET /api/v1/search/history
     */
    public function getSearchHistory(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $limit = $request->input('limit', 20);
        $history = $this->searchService->getSearchHistory(auth()->id(), $limit);

        return $this->success(data: $history);
    }

    /**
     * Clear search history
     * DELETE /api/v1/search/history
     */
    public function clearSearchHistory(Request $request): JsonResponse
    {
        // If specific ID provided
        if ($request->has('id')) {
            $this->searchService->deleteSearchHistoryItem(auth()->id(), $request->input('id'));
            return $this->success(message: 'Search history item deleted successfully');
        }

        // Clear all history
        $deletedCount = $this->searchService->clearSearchHistory(auth()->id());
        
        return $this->success(
            message: 'Search history cleared successfully',
            data: ['deleted_count' => $deletedCount]
        );
    }

    /**
     * Delete specific history item
     * DELETE /api/v1/search/history/{id}
     */
    public function deleteHistoryItem(Request $request, int $id): JsonResponse
    {
        $this->searchService->deleteSearchHistoryItem(auth()->id(), $id);
        
        return $this->success(message: 'Search history item deleted successfully');
    }
}
```

### Phase 3: Update Routes

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\SearchController;

Route::prefix('v1')->group(function () {
    // Public search endpoints
    Route::get('search', [SearchController::class, 'search'])
        ->name('search.global');
    
    Route::get('search/autocomplete', [SearchController::class, 'autocomplete'])
        ->name('search.autocomplete');

    // Protected endpoints (require authentication)
    Route::middleware(['auth:api'])->group(function () {
        Route::get('search/history', [SearchController::class, 'getSearchHistory'])
            ->name('search.history');
        
        Route::delete('search/history', [SearchController::class, 'clearSearchHistory'])
            ->name('search.history.clear');
        
        Route::delete('search/history/{id}', [SearchController::class, 'deleteHistoryItem'])
            ->name('search.history.delete');
    });
});
```

---

## 📊 QUERY BUILDER FEATURES

### Filtering

```php
// Example: Search courses with filters
GET /api/v1/search?q=programming&filter[category_id]=1&filter[level]=beginner&filter[status]=published

// Spatie Query Builder automatically handles:
QueryBuilder::for(Course::class)
    ->allowedFilters([
        AllowedFilter::callback('q', function ($query, $value) {
            $query->where('title', 'LIKE', "%{$value}%");
        }),
        AllowedFilter::exact('category_id'),
        AllowedFilter::exact('level'),
        AllowedFilter::exact('status'),
    ])
```

### Sorting

```php
// Example: Sort by title ascending
GET /api/v1/search?q=programming&sort=title

// Example: Sort by created_at descending
GET /api/v1/search?q=programming&sort=-created_at

// Spatie Query Builder automatically handles:
QueryBuilder::for(Course::class)
    ->allowedSorts(['title', 'created_at', 'updated_at'])
    ->defaultSort('-created_at')
```

### Pagination

```php
// Example: Get page 2 with 20 items per page
GET /api/v1/search?q=programming&page=2&per_page=20

// Spatie Query Builder automatically handles:
$results = QueryBuilder::for(Course::class)
    ->paginate($perPage);
```

### Including Relations

```php
// Example: Include instructor and category
GET /api/v1/search?q=programming&include=instructor,category

// Spatie Query Builder automatically handles:
QueryBuilder::for(Course::class)
    ->allowedIncludes(['instructor', 'category', 'units'])
```

---

## 🔒 AUTHORIZATION IMPLEMENTATION

### Student Authorization

```php
// Students can only search:
// - All courses (public)
// - Other students (users)
// - Content from enrolled courses only

if ($user->hasRole('Student')) {
    // For content search
    $enrolledCourseIds = DB::table('enrollments')
        ->where('user_id', $user->id)
        ->whereIn('status', ['active', 'completed'])
        ->pluck('course_id');

    $query->whereIn('course_id', $enrolledCourseIds);
}
```

### Instructor Authorization

```php
// Instructors can search:
// - All courses (public)
// - All students (users)
// - Content from managed courses only

if ($user->hasRole('Instructor')) {
    // For content search
    $managedCourseIds = Course::where('instructor_id', $user->id)
        ->pluck('id');

    $query->whereIn('course_id', $managedCourseIds);
}
```

### Admin Authorization

```php
// Admins can search everything
if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
    // No filters applied
}
```

---

## 📝 MIGRATION CHECKLIST

### Code Changes
- [ ] Update SearchService to use Spatie Query Builder
- [ ] Add authorization logic to SearchService
- [ ] Update SearchController with new endpoints
- [ ] Add validation rules to controller methods
- [ ] Update routes with all endpoints
- [ ] Add SearchHistoryRepository methods
- [ ] Create SearchRequest classes for validation

### Testing
- [ ] Test course search with filters
- [ ] Test user search with role-based access
- [ ] Test content search with enrollment check
- [ ] Test autocomplete functionality
- [ ] Test search history CRUD
- [ ] Test authorization for each role
- [ ] Test pagination and sorting
- [ ] Performance test with large datasets

### Documentation
- [ ] Update API_PENCARIAN_LENGKAP.md
- [ ] Add Spatie Query Builder examples
- [ ] Document all filter options
- [ ] Document authorization rules
- [ ] Add Postman collection examples

### Database
- [ ] Add indexes for search performance
- [ ] Optimize search_history table
- [ ] Add composite indexes for filters

---

## 🚀 PERFORMANCE OPTIMIZATIONS

### Database Indexes

```sql
-- Courses table
CREATE INDEX idx_courses_title ON courses(title);
CREATE INDEX idx_courses_status ON courses(status);
CREATE INDEX idx_courses_category_id ON courses(category_id);
CREATE INDEX idx_courses_instructor_id ON courses(instructor_id);

-- Users table
CREATE INDEX idx_users_name ON users(name);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_status ON users(status);

-- Enrollments table
CREATE INDEX idx_enrollments_user_status ON enrollments(user_id, status);
CREATE INDEX idx_enrollments_course_status ON enrollments(course_id, status);

-- Search history
CREATE INDEX idx_search_history_user_created ON search_history(user_id, created_at DESC);
```

### Caching Strategy

```php
// Cache search results for 5 minutes
$cacheKey = "search:{$query}:" . md5(json_encode($filters));

return Cache::remember($cacheKey, 300, function() use ($query, $filters) {
    return $this->performSearch($query, $filters);
});
```

---

## 📞 NEXT STEPS

1. **Review this migration plan** with team
2. **Create feature branch** for migration
3. **Implement changes** phase by phase
4. **Write tests** for each phase
5. **Update documentation** as you go
6. **Deploy to staging** for testing
7. **Collect feedback** from FE/Mobile team
8. **Deploy to production** after approval

---

**Status**: 🔄 Ready for Implementation  
**Priority**: High  
**Estimated Time**: 3-5 days  
**Maintainer**: Backend Team
