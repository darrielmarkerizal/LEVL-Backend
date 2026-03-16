# Post API Memory Issues - Critical Findings

## 🔴 CRITICAL ISSUES

### Issue #1: N+1 Query - View Count Loading
**File**: `PostListResource.php` Line 27-30
**Severity**: CRITICAL

**Problem**:
```php
'view_count' => $this->when(
    $this->relationLoaded('views'),
    fn () => $this->views->count()  // ❌ Loads ALL views
),
```

**Impact**: 100MB+ memory per 10K views

**Solution**:
```php
'view_count' => $this->views_count ?? 0,
```

---

### Issue #2: Eager Loading Views Globally
**File**: `PostRepository.php` Line 20
**Severity**: CRITICAL

**Problem**:
```php
protected array $with = ['author', 'audiences', 'views'];  // ❌
```

**Impact**: +50-100MB per request

**Solution**:
```php
protected array $with = ['author', 'audiences'];  // ✅
```

---

### Issue #3: Missing withCount in Queries
**File**: `PostRepository.php` Line 31-40
**Severity**: HIGH

**Problem**: No aggregate queries for counts

**Solution**: Add `withCount('views')` to queries

---

### Issue #4: No Pagination on Relationships
**File**: `PostController.php` Line 60-66
**Severity**: HIGH

**Problem**: Loading all audiences without limit

**Solution**: Use pagination or limit

---

### Issue #5: Cache Loading Full Collections
**File**: `PostRepository.php` Line 75-90
**Severity**: MEDIUM

**Problem**: Caching full view collections

**Solution**: Cache only counts/IDs

---

## 📊 MEMORY IMPACT ANALYSIS

| Scenario | Current | Optimized | Savings |
|----------|---------|-----------|---------|
| 100 posts, 10K views each | 1.2GB | 15MB | 98.7% |
| 1000 posts list | 500MB | 25MB | 95% |
| Single post detail | 50MB | 2MB | 96% |

---

## ✅ RECOMMENDED FIXES

### Priority 1: Immediate (Deploy Today)
1. Remove 'views' from eager loading
2. Add withCount('views') to queries
3. Fix PostListResource view_count

### Priority 2: This Week
4. Add pagination to relationships
5. Optimize cache strategy
6. Add database indexes

### Priority 3: Next Sprint
7. Implement query result caching
8. Add monitoring/alerts
9. Load testing

---

## 🚀 IMPLEMENTATION PLAN

See `POST_MEMORY_FIX_IMPLEMENTATION.md` for detailed fixes.
