# Design Document: API Consistency Refactor

## Overview

Dokumen ini menjelaskan desain teknis untuk refactoring API project LMS agar konsisten mengikuti arsitektur berlapis (Controller → Service → Repository). Refactoring ini mencakup standardisasi response format, eliminasi duplicate code, penambahan interfaces untuk dependency injection, dan peningkatan error handling, security, dan performance.

## Architecture

### Current State Issues

```
┌─────────────────────────────────────────────────────────────────┐
│                    CURRENT INCONSISTENCIES                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ❌ Some controllers use response()->json() directly            │
│  ❌ Some controllers use 'status' key instead of 'success'      │
│  ❌ Some services don't have interfaces                         │
│  ❌ Duplicate userCanManageCourse() in multiple controllers     │
│  ❌ Direct Model queries in controllers                         │
│  ❌ Placeholder/incomplete controllers exist                    │
│  ❌ Inconsistent error handling                                 │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Target State Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      TARGET ARCHITECTURE                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Request ──► FormRequest ──► Controller ──► Service ──► Response│
│                    │              │             │                │
│                    │              │             ▼                │
│              Validation      ApiResponse   Repository           │
│                    │         Trait              │                │
│                    │              │             ▼                │
│                    ▼              │           Model              │
│              Sanitized           │                              │
│              Input               │                              │
│                                  │                              │
│  All responses use:              │                              │
│  {success, message, data, meta, errors}                         │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Shared Authorization Trait

Extract duplicate authorization logic to a shared trait:

```php
// app/Traits/ManagesCourse.php (existing, to be enhanced)
namespace App\Traits;

use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;

trait ManagesCourse
{
    protected function userCanManageCourse(User $user, Course $course): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($user->hasRole('Admin') || $user->hasRole('Instructor')) {
            if ((int) $course->instructor_id === (int) $user->id) {
                return true;
            }

            if (method_exists($course, 'hasAdmin') && $course->hasAdmin($user)) {
                return true;
            }
        }

        return false;
    }

    protected function canModifyEnrollment(User $user, $enrollment): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return (int) $enrollment->user_id === (int) $user->id;
    }
}
```

### 2. Service Interfaces to Create

#### ContentStatisticsServiceInterface

```php
// Modules/Content/app/Contracts/Services/ContentStatisticsServiceInterface.php
namespace Modules\Content\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;

interface ContentStatisticsServiceInterface
{
    public function getAnnouncementStatistics(Announcement $announcement): array;
    public function getNewsStatistics(News $news): array;
    public function getAllAnnouncementStatistics(array $filters = []): Collection;
    public function getAllNewsStatistics(array $filters = []): Collection;
    public function getTrendingNews(int $limit = 10): Collection;
    public function getMostViewedNews(int $days = 30, int $limit = 10): Collection;
    public function getDashboardStatistics(): array;
}
```

#### NotificationPreferenceServiceInterface (if not exists)

```php
// Modules/Notifications/app/Contracts/Services/NotificationPreferenceServiceInterface.php
namespace Modules\Notifications\Contracts\Services;

interface NotificationPreferenceServiceInterface
{
    public function getPreferences($user): array;
    public function updatePreferences($user, array $preferences): bool;
    public function resetToDefaults($user): bool;
}
```

### 3. Controller Refactoring Pattern

#### Before (Inconsistent)

```php
// ContentStatisticsController - BEFORE
public function index(Request $request): JsonResponse
{
    // ... logic ...
    return response()->json([
        'status' => 'success',  // ❌ Wrong key
        'data' => $data,
    ]);
}
```

#### After (Consistent)

```php
// ContentStatisticsController - AFTER
class ContentStatisticsController extends Controller
{
    use ApiResponse;  // ✅ Add trait

    public function __construct(
        private ContentStatisticsServiceInterface $statisticsService  // ✅ Interface
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewStatistics', [Announcement::class]);
        
        $filters = $this->buildFilters($request);
        $data = $this->statisticsService->getStatistics($filters);
        
        return $this->success($data);  // ✅ Use trait method
    }
}
```

### 4. Response Format Standardization

All API responses must follow this structure:

```json
{
    "success": true,
    "message": "Berhasil",
    "data": { ... },
    "meta": {
        "pagination": { ... }
    },
    "errors": null
}
```

Error responses:

```json
{
    "success": false,
    "message": "Error message",
    "data": null,
    "meta": null,
    "errors": {
        "field": ["Error detail"]
    }
}
```

## Data Models

### Audit Log Structure

```php
// Existing Audit model structure
[
    'action' => 'update|create|delete|login|logout',
    'user_id' => int,
    'module' => 'Auth|Content|...',
    'target_table' => 'users|announcements|...',
    'target_id' => int,
    'ip_address' => string,
    'user_agent' => string,
    'meta' => [
        'action' => 'specific.action.name',
        'changes' => [...],
    ],
    'logged_at' => datetime,
]
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Success Response Format Consistency

*For any* API endpoint that returns a successful response, the JSON response SHALL contain the keys: `success` (boolean true), `message` (string), `data` (any), `meta` (object or null), and `errors` (null).

**Validates: Requirements 1.2, 3.1, 13.1, 13.2, 14.1**

### Property 2: Error Response Format Consistency

*For any* API endpoint that returns an error response, the JSON response SHALL contain `success` (boolean false), `message` (string), and appropriate HTTP status code (4xx or 5xx).

**Validates: Requirements 1.4, 3.3, 10.1, 10.2, 13.3, 14.3**

### Property 3: Paginated Response Structure

*For any* API endpoint that returns paginated data, the response SHALL include `meta.pagination` object with keys: `current_page`, `per_page`, `total`, `last_page`, `from`, `to`, `has_next`, `has_prev`.

**Validates: Requirements 3.2, 13.4, 15.2**

### Property 4: Created Resource Status Code

*For any* API endpoint that creates a new resource successfully, the HTTP response status code SHALL be 201.

**Validates: Requirements 3.4**

### Property 5: Authorization Enforcement

*For any* protected API endpoint, requests without valid authentication SHALL receive 401 status, and requests without proper authorization SHALL receive 403 status.

**Validates: Requirements 6.1, 16.4**

### Property 6: Validation Error Format

*For any* API endpoint that receives invalid input, the response SHALL have status 422 and include `errors` object with field-specific error messages.

**Validates: Requirements 10.4, 16.2**

### Property 7: Rate Limiting Enforcement

*For any* API endpoint with rate limiting, exceeding the rate limit SHALL result in 429 status response.

**Validates: Requirements 16.1**

### Property 8: Sensitive Data Exclusion

*For any* API response containing user data, the response SHALL NOT include sensitive fields such as `password`, `remember_token`, or internal security tokens.

**Validates: Requirements 16.3**

### Property 9: Audit Trail for Sensitive Operations

*For any* sensitive operation (login, password change, account deletion, role change), an audit log entry SHALL be created with user_id, action, ip_address, and timestamp.

**Validates: Requirements 11.1, 11.3**

### Property 10: Transaction Integrity

*For any* complex operation involving multiple database writes, either all writes SHALL succeed or all SHALL be rolled back (atomicity).

**Validates: Requirements 2.4**

## Error Handling

### Exception Hierarchy

```
Throwable
├── Exception
│   ├── BusinessException (422)
│   ├── ResourceNotFoundException (404)
│   ├── DuplicateResourceException (409)
│   ├── ForbiddenException (403)
│   ├── UnauthorizedException (401)
│   └── ValidationException (422)
```

### Global Exception Handler

The existing `app/Exceptions/Handler.php` already handles API exceptions properly. Controllers should:

1. Let exceptions bubble up to the handler for standard cases
2. Use `$this->error()` for custom error responses
3. Never catch exceptions just to re-throw with different format

## Testing Strategy

### Dual Testing Approach

Testing akan menggunakan kombinasi unit tests dan property-based tests:

1. **Unit Tests**: Verify specific examples and edge cases
2. **Property-Based Tests**: Verify universal properties across all inputs

### Property-Based Testing Library

**Library**: [Pest PHP](https://pestphp.com/) with [pest-plugin-faker](https://github.com/pestphp/pest-plugin-faker) for data generation

Pest sudah digunakan di project ini (lihat `tests/Pest.php`).

### Test Structure

```
tests/
├── Feature/
│   └── Api/
│       ├── ResponseFormatTest.php      # Property 1, 2, 3, 4
│       ├── AuthorizationTest.php       # Property 5
│       ├── ValidationTest.php          # Property 6
│       ├── RateLimitingTest.php        # Property 7
│       ├── SecurityTest.php            # Property 8
│       └── AuditTrailTest.php          # Property 9
└── Unit/
    └── Services/
        └── TransactionIntegrityTest.php # Property 10
```

### Property Test Example

```php
// tests/Feature/Api/ResponseFormatTest.php

/**
 * **Feature: api-consistency-refactor, Property 1: Success Response Format Consistency**
 */
it('returns consistent success response format for all endpoints', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');
    
    $endpoints = [
        ['GET', '/api/v1/announcements'],
        ['GET', '/api/v1/news'],
        ['GET', '/api/v1/courses'],
        // ... more endpoints
    ];
    
    foreach ($endpoints as [$method, $url]) {
        $response = $this->json($method, $url);
        
        if ($response->status() >= 200 && $response->status() < 300) {
            $response->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta',
                'errors',
            ]);
            
            expect($response->json('success'))->toBeTrue();
            expect($response->json('errors'))->toBeNull();
        }
    }
});
```

### Test Annotations

Each property-based test MUST include:
- Comment with format: `**Feature: {feature_name}, Property {number}: {property_text}**`
- Reference to the requirement it validates

### Minimum Test Iterations

Property-based tests should run minimum 100 iterations to ensure coverage of edge cases.
