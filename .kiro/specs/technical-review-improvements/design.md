# Design Document: Technical Review Improvements

## Overview

Dokumen ini menjelaskan desain teknis untuk perbaikan yang diidentifikasi dari technical review project Laravel backend LSP. Perbaikan difokuskan pada security hardening, konsistensi arsitektur, standardisasi API, dokumentasi, dan testing infrastructure.

## Architecture

### Current State
```
ta-prep-lsp-be/
├── app/                          # Core application code
│   ├── Exceptions/               # Exception handlers (redundant with bootstrap)
│   ├── Http/Middleware/          # Global middlewares
│   ├── Policies/                 # Authorization policies
│   └── Support/                  # Helpers and traits
├── Modules/                      # Domain modules
│   ├── Auth/app/Interfaces/      # ❌ Inconsistent naming
│   ├── Content/app/Contracts/    # ✅ Preferred naming
│   └── [Other modules]/          # Mixed or missing interfaces
└── bootstrap/app.php             # Exception handling
```

### Target State
```
ta-prep-lsp-be/
├── app/
│   ├── Exceptions/               # Removed or minimal (delegate to bootstrap)
│   ├── Http/Middleware/          # + RateLimiting, CORS
│   └── Support/                  # + HashHelper for enrollment keys
├── Modules/
│   └── [Each Module]/
│       └── app/
│           ├── Contracts/        # ✅ Standardized interface folder
│           │   ├── Services/     # Service interfaces
│           │   └── Repositories/ # Repository interfaces
│           ├── Services/         # Implementations
│           └── Repositories/     # Implementations
└── config/
    ├── cors.php                  # CORS configuration
    └── rate-limiting.php         # Rate limiting rules
```

## Components and Interfaces

### 1. Security Components

#### 1.1 Enrollment Key Hashing Service
```php
// app/Support/EnrollmentKeyHasher.php
interface EnrollmentKeyHasherInterface
{
    public function hash(string $plainKey): string;
    public function verify(string $plainKey, string $hashedKey): bool;
    public function generate(int $length = 12): string;
}

class EnrollmentKeyHasher implements EnrollmentKeyHasherInterface
{
    public function hash(string $plainKey): string;
    public function verify(string $plainKey, string $hashedKey): bool;
    public function generate(int $length = 12): string;
}
```

#### 1.2 Rate Limiting Configuration
```php
// config/rate-limiting.php
return [
    'api' => [
        'default' => ['max' => 60, 'decay' => 1],      // 60 req/min
        'auth' => ['max' => 10, 'decay' => 1],         // 10 req/min
        'enrollment' => ['max' => 5, 'decay' => 1],    // 5 req/min
    ],
];
```

#### 1.3 CORS Configuration
```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000'),
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    'max_age' => 86400,
];
```

### 2. Architecture Standardization

#### 2.1 Interface Naming Convention
All modules will use `Contracts/` folder with sub-folders:
- `Contracts/Services/` - Service interfaces
- `Contracts/Repositories/` - Repository interfaces

#### 2.2 Service Provider Bindings
```php
// Example: Modules/Schemes/Providers/SchemesServiceProvider.php
public function register(): void
{
    $this->app->bind(
        \Modules\Schemes\Contracts\Services\CourseServiceInterface::class,
        \Modules\Schemes\Services\CourseService::class
    );
    $this->app->bind(
        \Modules\Schemes\Contracts\Repositories\CourseRepositoryInterface::class,
        \Modules\Schemes\Repositories\CourseRepository::class
    );
}
```

### 3. API Route Standardization

#### 3.1 HTTP Method Corrections
| Current | Target | Endpoint |
|---------|--------|----------|
| POST | PATCH | /enrollments/{id}/approve |
| POST | PATCH | /enrollments/{id}/decline |
| POST | PATCH | /courses/{slug}/cancel |
| POST | PATCH | /courses/{slug}/withdraw |

#### 3.2 Route Prefix Cleanup
```php
// Before: /api/v1/assessments/exercises/{id}
// After:  /api/v1/exercises/{id}

// Before: /api/v1/assessments/attempts/{id}
// After:  /api/v1/attempts/{id}
```

### 4. OpenAPI Documentation Structure

Each module will have:
```
Modules/{Module}/
├── openapi.yaml           # OpenAPI 3.0 specification
└── docs/
    └── api-examples.md    # Usage examples
```

Standard response schema:
```yaml
components:
  schemas:
    ApiResponse:
      type: object
      properties:
        success:
          type: boolean
        message:
          type: string
        data:
          type: object
          nullable: true
        meta:
          type: object
          nullable: true
        errors:
          type: object
          nullable: true
```

## Data Models

### 1. Course Model Changes
```php
// Modules/Schemes/Models/Course.php
protected $fillable = [
    // ... existing fields
    'enrollment_key_hash',  // NEW: Hashed enrollment key
];

protected $hidden = [
    'enrollment_key_hash',  // Hide from JSON
    // Remove 'enrollment_key' from hidden (no longer stored plain)
];
```

### 2. Migration for Enrollment Key Hash
```php
Schema::table('courses', function (Blueprint $table) {
    $table->string('enrollment_key_hash', 255)->nullable()->after('enrollment_key');
});

// Data migration: hash existing keys
Course::whereNotNull('enrollment_key')->each(function ($course) {
    $course->update([
        'enrollment_key_hash' => Hash::make($course->enrollment_key),
        'enrollment_key' => null,
    ]);
});

Schema::table('courses', function (Blueprint $table) {
    $table->dropColumn('enrollment_key');
});
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Enrollment Key Hash Integrity
*For any* enrollment key string, when stored via the EnrollmentKeyHasher, the stored hash SHALL NOT equal the plain text AND Hash::check with the original plain text SHALL return true.
**Validates: Requirements 1.2, 1.3**

### Property 2: Rate Limiting Enforcement
*For any* API endpoint with rate limiting configured, when requests exceed the configured threshold within the decay period, the system SHALL return HTTP 429 status code.
**Validates: Requirements 1.5**

### Property 3: CORS Header Validation
*For any* cross-origin request with an Origin header, the response SHALL include Access-Control-Allow-Origin header only if the origin is in the configured whitelist.
**Validates: Requirements 1.6**

### Property 4: Service Interface Binding Resolution
*For any* service interface registered in a module's ServiceProvider, resolving the interface from the container SHALL return an instance of the bound implementation.
**Validates: Requirements 2.4**

### Property 5: API Response Format Consistency
*For any* API endpoint response, the JSON structure SHALL contain the keys: success (boolean), message (string), data (object|null), meta (object|null), errors (object|null).
**Validates: Requirements 3.5**

### Property 6: Polymorphic Scope Validation
*For any* exercise creation with scope_type and scope_id, the system SHALL validate that scope_id exists in the table corresponding to scope_type (courses, units, or lessons).
**Validates: Requirements 5.1**

### Property 7: Test Database Isolation
*For any* test class using RefreshDatabase trait, database state after test execution SHALL be identical to the state before test execution.
**Validates: Requirements 7.3**

## Error Handling

### Consolidated Exception Handling
All API exceptions will be handled in `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions): void {
    // Authentication exceptions
    $exceptions->render(function (AuthenticationException $e, Request $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak terotorisasi.',
                'data' => null,
                'meta' => null,
                'errors' => null,
            ], 401);
        }
    });
    
    // Rate limiting exceptions
    $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
        return response()->json([
            'success' => false,
            'message' => 'Terlalu banyak request. Coba lagi nanti.',
            'data' => null,
            'meta' => ['retry_after' => $e->getHeaders()['Retry-After'] ?? 60],
            'errors' => null,
        ], 429);
    });
    
    // ... other handlers
});
```

### Handler.php Removal
The `app/Exceptions/Handler.php` will be simplified to only extend base handler without custom logic, as all API handling is in bootstrap.

## Testing Strategy

### Dual Testing Approach

#### Unit Tests
- Test individual components in isolation
- Mock external dependencies
- Focus on edge cases and error conditions

#### Property-Based Tests
- Use **Pest PHP** with **pest-plugin-faker** for property-based testing
- Generate random inputs to verify properties hold across all valid inputs
- Minimum 100 iterations per property test

### Test Structure
```
tests/
├── Feature/
│   ├── Security/
│   │   ├── EnrollmentKeyHashingTest.php
│   │   ├── RateLimitingTest.php
│   │   └── CorsTest.php
│   └── Api/
│       └── ResponseFormatTest.php
├── Unit/
│   └── Support/
│       └── EnrollmentKeyHasherTest.php
└── Property/
    ├── EnrollmentKeyHashPropertyTest.php
    ├── RateLimitingPropertyTest.php
    └── ApiResponsePropertyTest.php
```

### Property Test Example
```php
// tests/Property/EnrollmentKeyHashPropertyTest.php
use function Pest\Faker\fake;

it('hashes enrollment keys securely', function () {
    $hasher = app(EnrollmentKeyHasherInterface::class);
    
    // Property: hash should never equal plain text
    // Property: verify should return true for correct key
    for ($i = 0; $i < 100; $i++) {
        $plainKey = fake()->regexify('[A-Z0-9]{12}');
        $hash = $hasher->hash($plainKey);
        
        expect($hash)->not->toBe($plainKey);
        expect($hasher->verify($plainKey, $hash))->toBeTrue();
        expect($hasher->verify('wrong-key', $hash))->toBeFalse();
    }
});
```

### Test Annotations
Each property-based test MUST include:
```php
/**
 * **Feature: technical-review-improvements, Property 1: Enrollment Key Hash Integrity**
 * **Validates: Requirements 1.2, 1.3**
 */
```
