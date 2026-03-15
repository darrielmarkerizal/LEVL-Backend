# CORRECT USAGE EXAMPLES - SEARCH API

**Date**: 15 Maret 2026  
**Module**: Search

---

## ❌ WRONG vs ✅ CORRECT

### Example 1: Search with Type Parameter

**❌ WRONG**:
```
GET /api/v1/search?q=darriel&filter[type]=courses
```
**Error**: `type` is NOT a Spatie filter, it's a query parameter

**✅ CORRECT**:
```
GET /api/v1/search?q=darriel&type=courses
```

---

### Example 2: Search Courses with Filters

**❌ WRONG**:
```
GET /api/v1/search?q=programming&status=published&level_tag=beginner
```
**Error**: Filters must use `filter[]` format

**✅ CORRECT**:
```
GET /api/v1/search?q=programming&type=courses&filter[status]=published&filter[level_tag]=beginner
```

---

### Example 3: Search with Pagination

**❌ WRONG**:
```
GET /api/v1/search?q=programming&filter[per_page]=20
```
**Error**: `per_page` is NOT a filter, it's a query parameter

**✅ CORRECT**:
```
GET /api/v1/search?q=programming&type=courses&per_page=20&page=1
```

---

### Example 4: Search with Sorting

**❌ WRONG**:
```
GET /api/v1/search?q=programming&filter[sort]=-created_at
```
**Error**: `sort` is NOT a filter, it's a query parameter

**✅ CORRECT**:
```
GET /api/v1/search?q=programming&type=courses&sort=-created_at
```

---

## 📝 PARAMETER TYPES

### Query Parameters (NO filter[] prefix)
```
q           - Search query (required)
type        - Resource type (courses, units, lessons, users)
sort        - Sort field (prefix - for desc)
per_page    - Items per page
page        - Page number
```

### Spatie Filters (WITH filter[] prefix)
```
filter[status]          - For courses
filter[level_tag]       - For courses
filter[category_id]     - For courses
filter[instructor_id]   - For courses
filter[type]            - For courses (course type, NOT search type)
filter[course_id]       - For units/lessons
filter[unit_id]         - For lessons
filter[role]            - For users
```

---

## ✅ COMPLETE EXAMPLES

### 1. Search All Courses
```
GET /api/v1/search?q=programming&type=courses
```

### 2. Search Courses with Filters
```
GET /api/v1/search?q=programming&type=courses&filter[status]=published&filter[level_tag]=beginner&filter[category_id]=1
```

### 3. Search Courses with Sorting
```
GET /api/v1/search?q=programming&type=courses&sort=-created_at&per_page=20
```

### 4. Search Units in a Course
```
GET /api/v1/search?q=basic&type=units&filter[course_id]=1
```

### 5. Search Lessons (Requires Auth)
```
GET /api/v1/search?q=variables&type=lessons&filter[unit_id]=3
Authorization: Bearer {token}
```

### 6. Search Users (Requires Auth)
```
GET /api/v1/search?q=jane&type=users&filter[role]=Student&filter[status]=active
Authorization: Bearer {token}
```

### 7. Global Search (Quick)
```
GET /api/v1/search/global?q=programming&type=all
```

### 8. Autocomplete
```
GET /api/v1/search/autocomplete?q=prog&limit=10
```

---

## 🔍 DEBUGGING TIPS

### If you get "filter not allowed" error:

1. Check if you're using `filter[]` for actual Spatie filters
2. Make sure `type`, `sort`, `per_page`, `page` are NOT in `filter[]`
3. Verify the filter name matches allowed filters for that resource type

### Allowed Filters by Type:

**Courses**:
- `filter[status]`
- `filter[level_tag]`
- `filter[type]` (course type)
- `filter[category_id]`
- `filter[instructor_id]`

**Units**:
- `filter[course_id]`

**Lessons**:
- `filter[unit_id]`

**Users**:
- `filter[status]`
- `filter[role]`

---

## 🚀 QUICK REFERENCE

```bash
# Basic search
curl "http://localhost:8000/api/v1/search?q=programming&type=courses"

# With filters
curl "http://localhost:8000/api/v1/search?q=programming&type=courses&filter[status]=published&filter[level_tag]=beginner"

# With pagination
curl "http://localhost:8000/api/v1/search?q=programming&type=courses&per_page=20&page=1"

# With sorting
curl "http://localhost:8000/api/v1/search?q=programming&type=courses&sort=-created_at"

# Global search
curl "http://localhost:8000/api/v1/search/global?q=programming"

# Autocomplete
curl "http://localhost:8000/api/v1/search/autocomplete?q=prog&limit=10"
```

---

**Remember**: 
- `type`, `sort`, `per_page`, `page` are query parameters (NO `filter[]`)
- Actual filters use `filter[name]` format
- Check allowed filters for each resource type
