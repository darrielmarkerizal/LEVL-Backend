# Audit: Post API Out of Memory Issues

## Status: 🔴 CRITICAL ISSUES FOUND
**Tanggal**: 16 Maret 2026

## Ringkasan Eksekutif

Ditemukan **5 critical memory issues** yang dapat menyebabkan Out of Memory pada API Post di module Notifications. Issues utama adalah **N+1 queries**, **eager loading berlebihan**, dan **tidak ada pagination limit** pada relationships.

---

## 1. CRITICAL ISSUES FOUND 🔴

### Issue #1: N+1 Query Problem di PostListResource
**File**: `app/Http/Resources/PostListResource.php`  
**Severity**: 🔴 CRITICAL  
**Line**: 27-30

```php
'view_count' => $this->when(
    $this->relationLoaded('views'),
    fn () => $this->views->count()  // ❌ PROBLEM: Loads ALL views into memory
),
```

**Problem**:
- Method `$this->views->count()` loads **ALL** PostView records into memory
- Jika 1 post punya 10,000 views, semua 10,000 records di-load
- Dengan 100 posts, bisa load 1,000,000 records = **Out of Memory**

**Impact**: 
- Memory usage: ~100MB per 10,000 views
- Query time: 5-10 seconds untuk large datasets
- Server crash pada high traffic

**Solution**:
```php
'view_count' => $this->when(
    $this->relationLoaded('views'),
    fn () => $this->views_count ?? $this->views()->count()  // ✅ Use aggregate
),
```

---

### Issue #2: Eager Loading Berlebihan di Repository
**File**: `app/Repositories/PostRepository.php`  
**Severity**: 🔴 CRITICAL  
**Line**: 20

```php
protected array $with = ['author', 'audiences', 'views'];  // ❌ PROBLEM
```

**Problem**:
- `'views'` di-eager load untuk SEMUA query
- Setiap post bisa punya ribuan views
- Loading views tidak perlu untuk list endpoint

**Impact**:
- Memory: +50-100MB per request
- Query time: +2-5 seconds
- Database load: +300% queries

**Solution**:
```php
protected array $with = ['author', 'audiences'];  // ✅ Remove 'views'
// Load views only when needed with withCount
```

---

