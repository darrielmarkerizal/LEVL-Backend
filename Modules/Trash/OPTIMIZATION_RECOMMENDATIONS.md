# Trash Module - Optimization Recommendations

## Critical Issues Found

### 1. N+1 Query Problems

#### Issue: `cascadeDeleteChildren()` Method
**Location:** `TrashBinService.php` lines 380-404

**Problem:**
```php
$model->units()->get()->each(function ($unit): void {
    if (! $unit->trashed()) {
        $unit->delete(); // Each delete triggers queries
    }
});
```

**Solution:**
```php
private function cascadeDeleteChildren(Model $model): void
{
    if ($model instanceof \Modules\Schemes\Models\Course) {
        // Eager load to avoid N+1
        $units = $model->units()->whereNull('deleted_at')->get();
        foreach ($units as $unit) {
            $unit->delete();
        }
    }

    if ($model instanceof \Modules\Schemes\Models\Unit) {
        // Batch load all children
        $lessons = $model->lessons()->whereNull('deleted_at')->get();
        $quizzes = $model->quizzes()->whereNull('deleted_at')->get();
        $assignments = $model->assignments()->whereNull('deleted_at')->get();
        
        foreach ($lessons as $lesson) {
            $lesson->delete();
        }
        foreach ($quizzes as $quiz) {
            $quiz->delete();
        }
        foreach ($assignments as $assignment) {
            $assignment->delete();
        }
    }
}
```

#### Issue: `deleteModelMedia()` Method
**Location:** `TrashBinService.php` lines 372-378

**Problem:**
```php
$model->media()->get()->each(function ($media): void {
    $media->delete(); // N+1 on media deletion
});
```

**Solution:**
```php
private function deleteModelMedia(Model $model): void
{
    if (! $model instanceof HasMedia) {
        return;
    }

    // Batch delete media files
    $mediaItems = $model->media()->get();
    $mediaIds = $mediaItems->pluck('id')->toArray();
    
    // Delete files first
    foreach ($mediaItems as $media) {
        $media->delete();
    }
    
    // Or use bulk delete if Spatie supports it
    // \Spatie\MediaLibrary\MediaCollections\Models\Media::whereIn('id', $mediaIds)->delete();
}
```

---

### 2. Memory Leak with Octane/Swoole

#### Issue: Static Cache in `hasStatusColumn()`
**Location:** `TrashBinService.php` lines 406-415

**Problem:**
```php
private function hasStatusColumn(Model $model): bool
{
    static $cache = []; // Memory leak in Octane!
    
    $table = $model->getTable();
    if (! array_key_exists($table, $cache)) {
        $cache[$table] = Schema::hasColumn($table, 'status');
    }
    
    return $cache[$table];
}
```

**Solution - Use Laravel Cache:**
```php
private function hasStatusColumn(Model $model): bool
{
    $table = $model->getTable();
    $cacheKey = "schema:has_status_column:{$table}";
    
    return \Illuminate\Support\Facades\Cache::remember(
        $cacheKey,
        now()->addHours(24),
        fn () => Schema::hasColumn($table, 'status')
    );
}
```

**Or use Octane-safe approach:**
```php
private function hasStatusColumn(Model $model): bool
{
    $table = $model->getTable();
    
    // Use Octane table to store cache
    if (extension_loaded('swoole')) {
        $cache = \Laravel\Octane\Facades\Octane::table('schema_cache');
        
        if ($cache->exists($table)) {
            return (bool) $cache->get($table);
        }
        
        $hasColumn = Schema::hasColumn($table, 'status');
        $cache->set($table, $hasColumn);
        
        return $hasColumn;
    }
    
    // Fallback for non-Octane
    static $cache = [];
    if (! array_key_exists($table, $cache)) {
        $cache[$table] = Schema::hasColumn($table, 'status');
    }
    
    return $cache[$table];
}
```

---

### 3. Inefficient Bulk Operations

#### Issue: Sequential Processing in Chunks
**Location:** `TrashBinService.php` lines 180-195, 197-218, 220-234, 236-250

**Problem:**
```php
$query->chunkById(100, function ($bins) use (&$count): void {
    foreach ($bins as $bin) {
        if ($this->forceDeleteFromTrashBin($bin)) { // Each in separate transaction
            $count++;
        }
    }
});
```

**Solution - Batch Processing:**
```php
public function forceDeleteAll(?string $resourceType = null, ?int $actorId = null, array $accessibleCourseIds = []): int
{
    $count = 0;

    $query = TrashBin::query()->orderBy('id');
    if ($resourceType !== null) {
        $query->where('resource_type', $resourceType);
    }

    if ($actorId !== null) {
        $query->where(function ($sub) use ($actorId, $accessibleCourseIds): void {
            $sub->where('deleted_by', $actorId);

            if (! empty($accessibleCourseIds)) {
                $sub->orWhereIn(DB::raw("(metadata->>'course_id')::bigint"), $accessibleCourseIds);
            }
        });
    }

    // Process in batches with single transaction per batch
    $query->chunkById(100, function ($bins) use (&$count): void {
        DB::transaction(function () use ($bins, &$count): void {
            foreach ($bins as $bin) {
                if ($this->forceDeleteSingle($bin)) {
                    $count++;
                }
            }
        });
    });

    return $count;
}
```

**Better Solution - Use Jobs for Large Operations:**
Already implemented in `TrashBinManagementService` - Good! ✅

---

### 4. Context Management Race Conditions

#### Issue: Instance Properties for Context
**Location:** `TrashDeleteContext.php` and `TrashBinService.php`

**Problem:**
In Octane, service instances are reused across requests. If `TrashDeleteContext` is a singleton, concurrent requests could interfere with each other.

**Solution:**
Ensure `TrashDeleteContext` is NOT a singleton:

```php
// In TrashServiceProvider.php
$this->app->bind(TrashDeleteContext::class, function ($app) {
    return new TrashDeleteContext(); // New instance per request
});

// NOT this:
// $this->app->singleton(TrashDeleteContext::class, ...);
```

---

### 5. Missing Eager Loading

#### Issue: Repository Pagination
**Location:** `TrashBinRepository.php` line 56

**Current:**
```php
return $query
    ->with(['deletedByUser:id,name,username']) // Good!
    ->paginate($perPage)
    ->appends($params);
```

**Recommendation:**
Add more eager loading if needed:
```php
return $query
    ->with([
        'deletedByUser:id,name,username,email',
        // Add other relationships if accessed in views
    ])
    ->paginate($perPage)
    ->appends($params);
```

---

## Performance Optimizations

### 1. Add Database Indexes

**Recommended indexes for `trash_bins` table:**
```php
// In migration
$table->index(['deleted_by', 'deleted_at']);
$table->index(['group_uuid']);
$table->index(['expires_at']);
$table->index(['resource_type', 'deleted_at']);
$table->index("((metadata->>'course_id'))", 'trash_bins_metadata_course_id_index');
```

### 2. Optimize Search Query

**Location:** `TrashBinRepository.php` lines 42-52

**Current issue:** Multiple similarity checks are expensive

**Solution:**
```php
if ($search) {
    // Use full-text search if available
    $query->where(function ($subQuery) use ($search): void {
        $subQuery->whereRaw("COALESCE(metadata->>'title', '') ILIKE ?", ["%{$search}%"])
            ->orWhereHas('deletedByUser', function ($userQuery) use ($search) {
                $userQuery->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('username', 'ILIKE', "%{$search}%");
            });
    });
    
    // Add similarity as secondary sort, not filter
    $query->orderByRaw("similarity(COALESCE(metadata->>'title', ''), ?) DESC", [$search]);
}
```

### 3. Cache Frequently Accessed Data

**Add caching for source types:**
```php
public function getSourceTypes(): array
{
    return Cache::remember('trash_bins:source_types', 3600, function () {
        return TrashBin::query()
            ->select('resource_type')
            ->distinct()
            ->orderBy('resource_type')
            ->pluck('resource_type')
            ->values()
            ->toArray();
    });
}
```

---

## Octane-Specific Recommendations

### 1. Clear Static Caches on Request End

**Add to `TrashServiceProvider.php`:**
```php
use Laravel\Octane\Events\RequestTerminated;

public function boot(): void
{
    // Clear static caches after each request in Octane
    if (config('octane.server')) {
        Event::listen(RequestTerminated::class, function () {
            // Reset any static caches here if needed
        });
    }
}
```

### 2. Use Octane Tables for Shared State

For schema caching across workers:
```php
// In config/octane.php
'tables' => [
    'schema_cache' => 1000, // Max 1000 entries
],
```

### 3. Avoid Singleton Services with Mutable State

Ensure services are stateless or use request-scoped binding:
```php
// In TrashServiceProvider
$this->app->scoped(TrashBinService::class);
$this->app->scoped(TrashDeleteContext::class);
```

---

## Testing Recommendations

### 1. Add Performance Tests

```php
public function test_bulk_delete_performance()
{
    // Create 1000 trash bins
    TrashBin::factory()->count(1000)->create();
    
    $startTime = microtime(true);
    $this->trashService->forceDeleteAll();
    $duration = microtime(true) - $startTime;
    
    $this->assertLessThan(10, $duration, 'Bulk delete should complete in under 10 seconds');
}
```

### 2. Add Octane-Specific Tests

```php
public function test_concurrent_requests_dont_interfere()
{
    // Test that concurrent deletes don't share context
}
```

---

## Priority Fixes

1. **HIGH**: Fix static cache memory leak (Issue #2)
2. **HIGH**: Fix N+1 in cascadeDeleteChildren (Issue #1)
3. **MEDIUM**: Add database indexes
4. **MEDIUM**: Verify TrashDeleteContext is not singleton
5. **LOW**: Optimize search query
6. **LOW**: Add caching for source types

---

## Summary

The Trash module has good architecture with job-based async processing, but needs:
- N+1 query fixes in cascade operations
- Octane-safe caching strategy
- Better batch processing
- Additional database indexes
- Context isolation verification

Most issues are fixable with the solutions provided above.
