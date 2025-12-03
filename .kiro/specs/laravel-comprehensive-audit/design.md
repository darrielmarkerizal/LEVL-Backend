# Design Document - Laravel Comprehensive Audit

## Overview

Dokumen ini berisi hasil audit detail dan rekomendasi perbaikan untuk project Laravel LMS. Audit dilakukan berdasarkan requirements yang telah didefinisikan, mencakup 11 area utama: arsitektur, penamaan, routing, validasi, database, performa, keamanan, production readiness, code quality, testing, dan dokumentasi API.

## Architecture

### Current State Analysis

Project menggunakan arsitektur modular dengan **nwidart/laravel-modules v12** yang membagi aplikasi menjadi 13 modules:
- Auth, Assessments, Common, Content, Enrollments, Forums, Gamification, Grading, Learning, Notifications, Operations, Schemes, Search

**Kelebihan:**
- Separation of concerns yang baik per domain
- Setiap module memiliki struktur folder yang konsisten (Controllers, Services, Repositories, Models)
- Autoloading terorganisir dengan baik di `composer.json`

**Kekurangan yang Ditemukan:**
- Beberapa module menggunakan `auth:sanctum` (Operations) sementara yang lain menggunakan `auth:api` (JWT) - inkonsistensi middleware
- File test debugging di Operations module (`FileTestController`) yang seharusnya tidak ada di production

### Recommended Architecture

```
Modules/
├── {ModuleName}/
│   ├── app/
│   │   ├── Contracts/          # Interfaces
│   │   ├── Enums/              # PHP Enums
│   │   ├── Events/             # Domain Events
│   │   ├── Exceptions/         # Custom Exceptions
│   │   ├── Http/
│   │   │   ├── Controllers/    # Thin Controllers
│   │   │   ├── Middleware/     # Module-specific Middleware
│   │   │   ├── Requests/       # Form Requests
│   │   │   └── Resources/      # API Resources
│   │   ├── Jobs/               # Queue Jobs
│   │   ├── Listeners/          # Event Listeners
│   │   ├── Models/             # Eloquent Models
│   │   ├── Policies/           # Authorization Policies
│   │   ├── Repositories/       # Data Access Layer
│   │   └── Services/           # Business Logic
│   ├── config/
│   ├── database/
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   └── tests/
```

## Components and Interfaces

### API Response Structure (Sudah Baik ✅)

```php
// app/Support/ApiResponse.php - Struktur sudah konsisten
{
    "success": true|false,
    "message": "string",
    "data": mixed,
    "meta": {
        "pagination": {...}  // untuk list endpoints
    },
    "errors": null|{...}
}
```

### Exception Handler (Sudah Baik ✅)

```php
// app/Exceptions/Handler.php
// Sudah handle: ValidationException, NotFoundHttpException, 
// UnauthorizedHttpException, AccessDeniedHttpException
```

## Data Models

### Database Schema Analysis

#### Users Table (Auth Module) ✅
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(100),
    username VARCHAR(50) UNIQUE,
    email VARCHAR(191) UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255),
    avatar_path VARCHAR(255) NULL,
    status ENUM('pending', 'active', 'inactive', 'banned') DEFAULT 'pending',
    -- Profile fields added later
    bio TEXT NULL,
    phone VARCHAR(20) NULL,
    account_status VARCHAR(20) NULL,  -- ⚠️ Redundant dengan status?
    last_profile_update TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX (status, created_at)
);
```

**Issues Found:**
- `account_status` dan `status` terlihat redundant - perlu klarifikasi perbedaan
- Tidak ada index pada `email_verified_at` untuk query user yang belum verifikasi

#### Courses Table (Schemes Module) ✅
```sql
CREATE TABLE courses (
    id BIGINT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    slug VARCHAR(100) UNIQUE,
    title VARCHAR(255),
    short_desc TEXT NULL,
    type ENUM('okupasi', 'kluster') DEFAULT 'okupasi',
    level_tag ENUM('dasar', 'menengah', 'mahir') DEFAULT 'dasar',
    category VARCHAR(100) NULL,  -- ⚠️ Seharusnya FK ke categories?
    tags_json JSON NULL,         -- ⚠️ Sudah ada pivot table course_tag
    outcomes_json JSON NULL,
    prereq_text TEXT NULL,
    thumbnail_path VARCHAR(255) NULL,
    banner_path VARCHAR(255) NULL,
    enrollment_type ENUM('auto_accept', 'key_based', 'approval') DEFAULT 'auto_accept',
    enrollment_key VARCHAR(100) NULL,
    progression_mode ENUM('sequential', 'free') DEFAULT 'sequential',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX (type, status),
    INDEX (enrollment_type)
);
```

**Issues Found:**
- `category` sebagai VARCHAR padahal ada tabel `categories` - seharusnya FK
- `tags_json` redundant karena sudah ada pivot table `course_tag`
- Tidak ada `instructor_id` FK (ditambahkan di migration terpisah)

#### Enrollments Table ✅
```sql
CREATE TABLE enrollments (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    course_id BIGINT REFERENCES courses(id) ON DELETE CASCADE,
    status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'active',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    progress_percent FLOAT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (user_id, course_id),
    INDEX (status, progress_percent)
);
```

**Good:** Proper FK constraints, unique constraint, indexes

### Normalization Issues (3NF Violations)

1. **courses.category** - Seharusnya FK ke categories table
2. **courses.tags_json** - Redundant dengan course_tag pivot table
3. **users.account_status** - Mungkin redundant dengan users.status

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Route Consistency
*For any* API route in the application, the route path SHALL start with `/v1/` prefix, SHALL NOT conflict with other routes, and nested resources SHALL NOT exceed 4 levels deep.
**Validates: Requirements 2.2, 3.1, 3.3, 3.4**

### Property 2: API Response Format
*For any* API endpoint response (success or error), the JSON structure SHALL contain the keys `success`, `message`, `data`, `meta`, and `errors`, with appropriate HTTP status codes (200/201 for success, 400/401/403/404/422/500 for errors).
**Validates: Requirements 4.2, 4.3, 4.4**

### Property 3: Authentication Protection
*For any* endpoint that requires authentication, calling without a valid JWT token SHALL return HTTP 401, and calling with a valid token but insufficient permissions SHALL return HTTP 403.
**Validates: Requirements 7.1, 7.2**

### Property 4: Sensitive Data Protection
*For any* API response containing user data, the response SHALL NOT include password hashes, remember tokens, or other sensitive credentials.
**Validates: Requirements 7.4**

### Property 5: Pagination Implementation
*For any* list endpoint that returns collections, the response SHALL include pagination metadata (`current_page`, `per_page`, `total`, `last_page`) when the collection exceeds the page size.
**Validates: Requirements 6.2**

### Property 6: N+1 Query Prevention
*For any* endpoint that returns data with relationships, the number of database queries SHALL NOT increase linearly with the number of related records (eager loading must be used).
**Validates: Requirements 6.1**

### Property 7: Documentation Synchronization
*For any* API route defined in the codebase, there SHALL exist a corresponding endpoint definition in the OpenAPI specification with matching HTTP method, path, and response schema.
**Validates: Requirements 11.1, 11.2, 11.3**

### Property 8: Validation Error Response
*For any* endpoint that receives invalid input data, the response SHALL return HTTP 422 with field-specific error messages in the `errors` object.
**Validates: Requirements 4.4**

## Error Handling

### Current Implementation (Good ✅)

```php
// app/Exceptions/Handler.php
protected function handleApiException(Request $request, Throwable $e): JsonResponse
{
    if ($e instanceof ValidationException) {
        return $this->validationError($e->errors());  // 422
    }
    if ($e instanceof NotFoundHttpException) {
        return $this->notFound("Resource tidak ditemukan");  // 404
    }
    if ($e instanceof UnauthorizedHttpException) {
        return $this->unauthorized($e->getMessage() ?: "Tidak terotorisasi");  // 401
    }
    if ($e instanceof AccessDeniedHttpException) {
        return $this->forbidden($e->getMessage() ?: "Akses ditolak");  // 403
    }
    // ... 500 for others
}
```

### Custom Exceptions (Good ✅)
- `BusinessException`
- `DuplicateResourceException`
- `ForbiddenException`
- `InvalidPasswordException`
- `ResourceNotFoundException`
- `UnauthorizedException`
- `ValidationException`

## Testing Strategy

### Current State

- **Total Test Cases:** 261 test cases
- **Feature Tests:** 13 files
- **Unit Tests:** 19 files
- **Estimated Coverage:** 70-75%

### Testing Framework
- **Pest PHP v3.8** - Modern testing framework ✅
- **PHPUnit v11.5.3** - Base framework ✅

### Gaps Identified

1. **Auth Module:**
   - ❌ Login throttling/rate limiting tests
   - ❌ Refresh token expiry tests
   - ❌ Password validation edge cases

2. **Assessments Module:**
   - ❌ Attempt time limit enforcement tests
   - ❌ Multiple attempts limit tests
   - ❌ Auto-grading tests

3. **General:**
   - ❌ Pagination tests for all list endpoints
   - ❌ Cross-resource authorization tests
   - ❌ Concurrent operation tests

### Recommended Test Structure

```php
// Pest test naming convention
it('can create user with valid data')           // Positive
it('fails when email is invalid')               // Validation
it('returns 401 when not authenticated')        // Auth
it('returns 403 when user lacks permission')    // Authorization
it('returns 404 when resource not found')       // Not Found
it('handles empty input gracefully')            // Edge Case
```

### Property-Based Testing

Menggunakan **Pest** dengan plugin untuk property-based testing:

```php
// Example property test
it('always returns consistent response format', function () {
    $response = $this->getJson('/api/v1/courses');
    
    expect($response->json())
        ->toHaveKeys(['success', 'message', 'data', 'meta', 'errors']);
});
```


## Audit Findings Summary

### 1. Arsitektur & Struktur Folder

| Aspek | Status | Catatan |
|-------|--------|---------|
| Modular Architecture | ✅ Good | 13 modules terorganisir dengan baik |
| Folder Structure | ✅ Good | Konsisten di semua modules |
| Service Layer | ✅ Good | Business logic di Services |
| Repository Pattern | ✅ Good | Data access terpisah |
| God Classes | ⚠️ Warning | Perlu review beberapa controller |

### 2. Konsistensi Penamaan

| Aspek | Status | Catatan |
|-------|--------|---------|
| Database Columns | ✅ Good | snake_case konsisten |
| PHP Methods | ✅ Good | camelCase konsisten |
| Class Names | ✅ Good | PascalCase konsisten |
| Route Names | ✅ Good | dot notation konsisten |
| Enum Values | ⚠️ Warning | Mix lowercase di beberapa tempat |

### 3. API Routing

| Aspek | Status | Catatan |
|-------|--------|---------|
| Versioning | ✅ Good | `/v1/` prefix konsisten |
| HTTP Methods | ✅ Good | RESTful conventions |
| Route Conflicts | ✅ Good | Tidak ada konflik terdeteksi |
| Nesting Depth | ✅ Good | Max 4 levels (courses/units/lessons/blocks) |
| Middleware | ❌ Issue | Mix `auth:api` dan `auth:sanctum` |

**Route Conflict Analysis:**
- Tidak ditemukan route yang bentrok
- Semua modules menggunakan prefix `/v1/`
- Route naming konsisten dengan dot notation

### 4. Validasi & Error Handling

| Aspek | Status | Catatan |
|-------|--------|---------|
| FormRequest Usage | ✅ Good | Validasi di FormRequest classes |
| Response Format | ✅ Good | Konsisten dengan ApiResponse trait |
| HTTP Status Codes | ✅ Good | Proper usage |
| Error Messages | ✅ Good | Bahasa Indonesia, informatif |

### 5. Database Design & Normalisasi

| Aspek | Status | Catatan |
|-------|--------|---------|
| 3NF Compliance | ⚠️ Warning | Beberapa violations ditemukan |
| Foreign Keys | ✅ Good | Proper constraints |
| Indexes | ⚠️ Warning | Beberapa kolom perlu index |
| Enum Usage | ✅ Good | PHP Enum classes digunakan |
| JSON Columns | ⚠️ Warning | Beberapa redundant |

**3NF Violations:**
1. `courses.category` - VARCHAR instead of FK
2. `courses.tags_json` - Redundant dengan pivot table
3. `users.account_status` vs `users.status` - Possible redundancy

### 6. Query & Performa

| Aspek | Status | Catatan |
|-------|--------|---------|
| N+1 Prevention | ⚠️ Warning | Perlu audit lebih lanjut |
| Pagination | ✅ Good | Implemented di list endpoints |
| Caching | ⚠️ Warning | Belum optimal |
| Index Coverage | ⚠️ Warning | Beberapa kolom perlu index |

### 7. Keamanan

| Aspek | Status | Catatan |
|-------|--------|---------|
| Auth Middleware | ✅ Good | Protected endpoints |
| Authorization | ✅ Good | Policies & Gates |
| Mass Assignment | ✅ Good | $fillable defined |
| Sensitive Data | ✅ Good | Hidden in models |
| File Upload | ✅ Good | Validation exists |
| Debug Endpoints | ❌ Issue | FileTestController di production |

### 8. Production Readiness

| Aspek | Status | Catatan |
|-------|--------|---------|
| .env.example | ✅ Good | Lengkap |
| APP_DEBUG | ✅ Good | Default false |
| Logging | ✅ Good | Configurable |
| Queue Driver | ⚠️ Warning | Default database, consider Redis |
| Cache Driver | ⚠️ Warning | Default database, consider Redis |

### 9. Code Quality

| Aspek | Status | Catatan |
|-------|--------|---------|
| PSR-12 | ✅ Good | Laravel Pint configured |
| SOLID Principles | ✅ Good | Generally followed |
| DRY | ⚠️ Warning | Some duplication found |
| Dependency Injection | ✅ Good | Used consistently |

### 10. Testing (Pest)

| Aspek | Status | Catatan |
|-------|--------|---------|
| Positive Tests | ✅ Good | 261 test cases |
| Negative Tests | ⚠️ Warning | Perlu ditambah |
| Edge Cases | ⚠️ Warning | Perlu ditambah |
| Test Naming | ✅ Good | Descriptive |
| Coverage | ⚠️ Warning | ~70-75%, target 85%+ |

### 11. Dokumentasi API

| Aspek | Status | Catatan |
|-------|--------|---------|
| OpenAPI Spec | ✅ Good | Comprehensive |
| Sync with Code | ⚠️ Warning | Beberapa endpoint tidak sinkron |
| Response Examples | ✅ Good | Included |
| Error Responses | ✅ Good | Documented |

## Detailed Findings

### Critical Issues (HIGH Priority)

1. **Mixed Authentication Middleware**
   - File: `Modules/Operations/routes/api.php`
   - Issue: Menggunakan `auth:sanctum` sementara module lain menggunakan `auth:api`
   - Impact: Inkonsistensi autentikasi, potential security issue
   - Fix: Ubah ke `auth:api` untuk konsistensi

2. **Debug Endpoints in Production**
   - File: `Modules/Operations/routes/api.php`
   - Issue: `FileTestController` endpoints tidak dilindungi dan seharusnya tidak ada di production
   - Impact: Security vulnerability, information disclosure
   - Fix: Hapus atau lindungi dengan environment check

3. **Database Normalization Issues**
   - File: `Modules/Schemes/database/migrations/2025_11_02_115520_create_courses_table.php`
   - Issue: `category` sebagai VARCHAR, `tags_json` redundant
   - Impact: Data inconsistency, query inefficiency
   - Fix: Migrate ke proper FK relationships

### Medium Priority Issues

4. **Missing Database Indexes**
   - Tables: users, courses, enrollments
   - Columns: `email_verified_at`, `published_at`, `enrolled_at`
   - Impact: Slow queries on large datasets
   - Fix: Add indexes via migration

5. **Test Coverage Gaps**
   - Areas: Login throttling, token expiry, edge cases
   - Impact: Potential bugs not caught
   - Fix: Add missing test cases

6. **Cache/Queue Configuration**
   - Current: Database driver
   - Recommended: Redis for production
   - Impact: Performance under load
   - Fix: Configure Redis in production

### Low Priority Issues

7. **Enum Value Consistency**
   - Some enums use lowercase, some use UPPERCASE
   - Impact: Minor inconsistency
   - Fix: Standardize to lowercase

8. **Documentation Sync**
   - Some new endpoints not documented
   - Impact: API consumer confusion
   - Fix: Update OpenAPI spec

## Recommended Response Format

```json
{
    "success": true,
    "message": "Berhasil mengambil data",
    "data": {
        // actual data
    },
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 100,
            "last_page": 7,
            "from": 1,
            "to": 15,
            "has_next": true,
            "has_prev": false
        }
    },
    "errors": null
}
```

## Refactoring Examples

### Before: Mixed Middleware
```php
// Modules/Operations/routes/api.php
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('operations', OperationsController::class);
});
```

### After: Consistent Middleware
```php
// Modules/Operations/routes/api.php
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('operations', OperationsController::class);
});
```

### Before: Debug Endpoints Exposed
```php
// Modules/Operations/routes/api.php
Route::prefix('v1/file-test')->group(function () {
    Route::get('config', [FileTestController::class, 'config']);
    // ... more debug endpoints
});
```

### After: Protected or Removed
```php
// Modules/Operations/routes/api.php
if (app()->environment('local', 'testing')) {
    Route::prefix('v1/file-test')->middleware(['auth:api', 'role:Superadmin'])->group(function () {
        Route::get('config', [FileTestController::class, 'config']);
        // ... more debug endpoints
    });
}
```

### Database Migration: Add Missing Indexes
```php
// database/migrations/xxxx_add_missing_indexes.php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->index('email_verified_at');
    });
    
    Schema::table('courses', function (Blueprint $table) {
        $table->index('published_at');
        $table->index('category');
    });
    
    Schema::table('enrollments', function (Blueprint $table) {
        $table->index('enrolled_at');
    });
}
```

### Test Example: Adding Negative Cases
```php
// Before: Only positive test
it('can create course with valid data', function () {
    $response = $this->postJson('/api/v1/courses', $validData);
    $response->assertStatus(201);
});

// After: With negative cases
it('can create course with valid data', function () {
    $response = $this->postJson('/api/v1/courses', $validData);
    $response->assertStatus(201);
});

it('fails when title is missing', function () {
    $data = [...$validData];
    unset($data['title']);
    
    $response = $this->postJson('/api/v1/courses', $data);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('returns 401 when not authenticated', function () {
    $response = $this->postJson('/api/v1/courses', $validData);
    $response->assertStatus(401);
});

it('returns 403 when user is not admin', function () {
    $this->actingAs($studentUser);
    $response = $this->postJson('/api/v1/courses', $validData);
    $response->assertStatus(403);
});
```
