# Design Document

## Overview

Dokumen ini menjelaskan desain teknis untuk melakukan audit kesesuaian dokumentasi OpenAPI dengan implementasi API aktual di Laravel, serta refactoring spesifikasi OpenAPI agar lebih konsisten dan mudah dibaca di Scalar.

Proses audit akan membandingkan:
1. **Routes** (`Modules/*/routes/api.php`) dengan paths di `openapi.json`
2. **FormRequest classes** dengan request body schemas
3. **Resource classes** dengan response schemas
4. **Middleware** dengan security requirements
5. **Enums** dengan enum values di dokumentasi

## Architecture

### Current Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    OpenAPI Generation Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Laravel Routes ──► OpenApiGeneratorService ──► openapi.json    │
│       │                      │                       │          │
│       │                      ▼                       │          │
│       │              featureGroups                   │          │
│       │              (keyword matching)              │          │
│       │                      │                       │          │
│       ▼                      ▼                       ▼          │
│  FormRequest          summaryOverrides         Scalar UI        │
│  (validation)         endpointExamples         (/scalar)        │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Audit Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      Audit Process Flow                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. Extract Routes ──► Compare with openapi.json paths          │
│         │                                                        │
│         ▼                                                        │
│  2. Extract FormRequest rules ──► Compare with request schemas  │
│         │                                                        │
│         ▼                                                        │
│  3. Extract Resource fields ──► Compare with response schemas   │
│         │                                                        │
│         ▼                                                        │
│  4. Extract Middleware ──► Compare with security requirements   │
│         │                                                        │
│         ▼                                                        │
│  5. Extract Enums ──► Compare with enum values in docs          │
│         │                                                        │
│         ▼                                                        │
│  6. Generate Audit Report + Refactored OpenAPI Spec             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Module Structure Analysis

Berdasarkan analisis, proyek memiliki modul-modul berikut:

| Module | Routes File | Key Controllers |
|--------|-------------|-----------------|
| Auth | `Modules/Auth/routes/api.php` | AuthApiController, ProfileController, AdminProfileController |
| Schemes | `Modules/Schemes/routes/api.php` | CourseController, UnitController, LessonController |
| Enrollments | `Modules/Enrollments/routes/api.php` | EnrollmentsController, ReportController |
| Content | `Modules/Content/routes/api.php` | AnnouncementController, NewsController |
| Forums | `Modules/Forums/routes/api.php` | ThreadController, ReplyController |
| Gamification | `Modules/Gamification/routes/api.php` | ChallengeController, LeaderboardController |
| Learning | `Modules/Learning/routes/api.php` | AssignmentController, SubmissionController |
| Notifications | `Modules/Notifications/routes/api.php` | NotificationsController |
| Search | `Modules/Search/routes/api.php` | SearchController |
| Common | `Modules/Common/routes/api.php` | CategoriesController |

### 2. Response Structure (ApiResponse Trait)

Berdasarkan `app/Support/ApiResponse.php`, struktur response standar adalah:

```php
// Success Response
{
    "success": true,
    "message": "Berhasil",
    "data": mixed,
    "meta": {
        "pagination": { // optional, for paginated responses
            "current_page": int,
            "per_page": int,
            "total": int,
            "last_page": int,
            "from": int|null,
            "to": int|null,
            "has_next": bool,
            "has_prev": bool
        }
    },
    "errors": null
}

// Error Response
{
    "success": false,
    "message": "Error message",
    "data": null,
    "meta": null,
    "errors": {
        "field_name": ["Error message 1", "Error message 2"]
    }
}
```

### 3. Validation Rules Mapping

Mapping dari Laravel validation rules ke OpenAPI schema:

| Laravel Rule | OpenAPI Property |
|--------------|------------------|
| `required` | `required: true` |
| `string` | `type: string` |
| `integer` | `type: integer` |
| `boolean` | `type: boolean` |
| `email` | `type: string, format: email` |
| `min:X` (string) | `minLength: X` |
| `max:X` (string) | `maxLength: X` |
| `min:X` (number) | `minimum: X` |
| `max:X` (number) | `maximum: X` |
| `confirmed` | Add `{field}_confirmation` to required |
| `unique:table,column` | Description: "Must be unique" |
| `regex:/pattern/` | `pattern: pattern` |
| `in:a,b,c` | `enum: [a, b, c]` |
| `nullable` | `nullable: true` |
| `image` | `type: string, format: binary` |
| `mimes:jpg,png` | Description: "Allowed: jpg, png" |

### 4. Key FormRequest Classes

Berdasarkan analisis, berikut adalah FormRequest utama yang perlu diaudit:

**Auth Module:**
- `RegisterRequest`: name, username, email, password (dengan password_confirmation)
- `LoginRequest`: login, password
- `UpdateProfileRequest`: name, username, avatar (file upload)
- `ChangePasswordRequest`: current_password, password (dengan password_confirmation)

**Schemes Module:**
- `CourseRequest`: title, description, category_id, tags, thumbnail
- `UnitRequest`: title, description, order
- `LessonRequest`: title, description, content_type

**Enrollments Module:**
- Enrollment endpoints menggunakan route model binding

### 5. Naming Convention Standards

Untuk konsistensi, dokumentasi OpenAPI harus mengikuti standar berikut:

| Element | Convention | Example |
|---------|------------|---------|
| Path segments | kebab-case | `/v1/course-tags`, `/v1/enrollment-key` |
| Path parameters | snake_case | `{course_id}`, `{user_id}` |
| Query parameters | snake_case | `per_page`, `sort_by`, `created_at` |
| Request body fields | snake_case | `enrollment_key`, `password_confirmation` |
| Response fields | snake_case | `created_at`, `updated_at`, `email_verified_at` |
| Schema names | PascalCase | `UserResource`, `CourseResponse`, `EnrollmentStatus` |
| Tag names | Title Case (Indonesian) | `Autentikasi`, `Pendaftaran Kelas` |
| Operation IDs | camelCase | `loginUser`, `getCourses`, `createEnrollment` |

### 6. Request Headers

Headers yang perlu didokumentasikan:

```yaml
# Standard Headers
Accept: application/json
Content-Type: application/json  # atau multipart/form-data untuk file upload
Authorization: Bearer {token}   # untuk authenticated endpoints

# Custom Headers (jika ada)
X-Device-Id: string            # optional, untuk tracking
X-App-Version: string          # optional, untuk versioning client
```

### 7. API Versioning

Semua endpoint harus menggunakan prefix `/v1/`:

```
✓ /v1/auth/login
✓ /v1/courses
✓ /v1/enrollments

✗ /auth/login (tanpa version prefix)
✗ /api/v1/courses (double prefix)
```

### 8. Rate Limiting Documentation

Berdasarkan routes, ada beberapa rate limit groups:

| Group | Limit | Endpoints |
|-------|-------|-----------|
| `throttle:auth` | 10/minute | Login, Register, Password Reset |
| `throttle:enrollment` | 5/minute | Enroll, Cancel, Withdraw |
| `throttle:api` | 60/minute | General API endpoints |

Rate limit harus didokumentasikan di:
- Tag description
- Endpoint description
- Response headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

### 9. Enum Classes to Document

```php
// User Status
enum UserStatus: string {
    case Active = 'active';
    case Pending = 'pending';
    case Suspended = 'suspended';
    case Inactive = 'inactive';
}

// Enrollment Status
enum EnrollmentStatus: string {
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}

// Content Status
enum ContentStatus: string {
    case Draft = 'draft';
    case Published = 'published';
    case Scheduled = 'scheduled';
    case Archived = 'archived';
}
```

## Data Models

### Audit Finding Structure

```typescript
interface AuditFinding {
    endpoint: string;           // e.g., "POST /v1/auth/register"
    module: string;             // e.g., "Auth"
    issueType: IssueType;
    severity: 'critical' | 'major' | 'minor';
    description: string;
    expected: string;
    actual: string;
    recommendation: string;
}

enum IssueType {
    MISSING_ENDPOINT = 'missing_endpoint',
    EXTRA_ENDPOINT = 'extra_endpoint',
    WRONG_METHOD = 'wrong_method',
    MISSING_AUTH = 'missing_auth',
    EXTRA_AUTH = 'extra_auth',
    MISSING_FIELD = 'missing_field',
    WRONG_TYPE = 'wrong_type',
    MISSING_VALIDATION = 'missing_validation',
    WRONG_ENUM = 'wrong_enum',
    MISSING_EXAMPLE = 'missing_example',
    WRONG_STATUS_CODE = 'wrong_status_code',
    MISSING_QUERY_PARAM = 'missing_query_param',
    WRONG_CONTENT_TYPE = 'wrong_content_type'
}
```

### Audit Report Structure

```typescript
interface AuditReport {
    summary: {
        totalEndpoints: number;
        documentedEndpoints: number;
        missingEndpoints: number;
        totalFindings: number;
        criticalFindings: number;
        majorFindings: number;
        minorFindings: number;
        qualityScore: number;  // 0-100
    };
    findingsByModule: {
        [module: string]: AuditFinding[];
    };
    findingsByType: {
        [type: string]: AuditFinding[];
    };
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Route Coverage Completeness
*For any* registered Laravel API route with a named route and controller, the generated OpenAPI spec SHALL include a path item for that route with the correct HTTP method.
**Validates: Requirements 1.1, 1.2, 1.3, 1.4**

### Property 2: Authentication Consistency
*For any* endpoint with `auth:api` middleware, the OpenAPI spec SHALL include `security: [{"bearerAuth": []}]`, and for any public endpoint (no auth middleware), the spec SHALL NOT include security requirement.
**Validates: Requirements 2.1, 2.2, 2.3**

### Property 3: Path Parameter Type Accuracy
*For any* route with path parameters, the OpenAPI spec SHALL document each parameter with correct name and type (slug binding = string type, id binding = integer type).
**Validates: Requirements 3.1, 3.2, 3.3**

### Property 4: Request Body Field Coverage
*For any* POST/PUT/PATCH endpoint using FormRequest, the OpenAPI spec SHALL document all fields from the FormRequest rules with correct types, required status, and validation constraints (min/max/format).
**Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.6, 4.7**

### Property 5: Confirmation Field Inclusion
*For any* field with `confirmed` validation rule (e.g., password), the OpenAPI spec SHALL include the corresponding confirmation field (e.g., password_confirmation) as required.
**Validates: Requirements 4.5**

### Property 6: Response Schema Consistency
*For any* endpoint, the response schema SHALL match the ApiResponse trait structure: `{success: boolean, message: string, data: any, meta: object|null, errors: object|null}`.
**Validates: Requirements 5.1, 5.2, 5.3, 12.1, 12.2, 12.3, 12.4, 12.5, 12.6**

### Property 7: Status Code Coverage
*For any* authenticated endpoint, the spec SHALL document 401 response; for any endpoint with path parameters, the spec SHALL document 404 response; for any endpoint with request body, the spec SHALL document 422 response.
**Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5**

### Property 8: Query Parameter Documentation
*For any* GET list endpoint, the OpenAPI spec SHALL document pagination parameters (page, per_page) with correct types (integer) and default values.
**Validates: Requirements 7.1, 7.2, 7.3, 7.4, 7.5**

### Property 9: Enum Value Completeness
*For any* field accepting enum values (status, role, type), the OpenAPI spec SHALL list all valid values exactly matching the PHP Enum class cases.
**Validates: Requirements 8.1, 8.2, 8.3**

### Property 10: File Upload Content Type
*For any* endpoint accepting file upload (with `image`, `file`, or `mimes` validation rule), the OpenAPI spec SHALL use `multipart/form-data` content type and document file constraints.
**Validates: Requirements 9.1, 9.2, 9.3, 9.4**

### Property 11: Naming Convention Consistency
*For any* OpenAPI spec element, the naming SHALL follow the defined convention: snake_case for fields/params, PascalCase for schemas, kebab-case for paths.
**Validates: Requirements 10.2**

### Property 12: API Version Prefix Consistency
*For any* documented endpoint path, the path SHALL start with `/v1/` prefix to ensure consistent versioning.
**Validates: Requirements 10.1**

### Property 13: Rate Limit Documentation
*For any* endpoint with throttle middleware, the OpenAPI spec SHALL document the rate limit in the endpoint description or tag description.
**Validates: Requirements 2.1 (extended)**

### Property 14: Request Header Documentation
*For any* endpoint, the OpenAPI spec SHALL document required headers (Accept, Content-Type, Authorization for authenticated endpoints).
**Validates: Requirements 4.1 (extended)**

### Property 15: Audit Completeness Verification
*For any* refactored OpenAPI spec, re-running the audit SHALL produce zero critical or major findings, confirming all mismatches have been addressed.
**Validates: Requirements 15.2**

## Error Handling

### Audit Process Errors

1. **Route Parsing Error**: Log warning and skip route
2. **FormRequest Not Found**: Log warning, document endpoint without request body details
3. **Resource Not Found**: Log warning, use generic response schema
4. **Enum Not Found**: Log warning, document field without enum values

### Fallback Strategies

1. **Missing FormRequest**: Use controller method signature to infer parameters
2. **Missing Resource**: Use generic object schema with additionalProperties
3. **Ambiguous Route**: Flag for manual review

## Testing Strategy

### Manual Audit Process

Karena ini adalah proses audit manual, testing akan dilakukan dengan:

1. **Route Comparison**: Membandingkan output `php artisan route:list --json` dengan paths di openapi.json
2. **FormRequest Inspection**: Membaca setiap FormRequest dan membandingkan rules dengan schema
3. **Response Verification**: Membandingkan actual API response dengan documented schema
4. **Enum Verification**: Membandingkan PHP Enum values dengan documented enum values

### Verification Checklist

Per endpoint, verifikasi:
- [ ] Path matches route
- [ ] HTTP method matches
- [ ] Authentication requirement matches middleware
- [ ] Path parameters documented with correct types
- [ ] Query parameters documented (for GET list endpoints)
- [ ] Request body fields match FormRequest rules
- [ ] Required fields marked correctly
- [ ] Validation constraints documented (min, max, format)
- [ ] Confirmation fields included (password_confirmation)
- [ ] Enum values complete
- [ ] File upload uses multipart/form-data
- [ ] Response schema matches ApiResponse structure
- [ ] Status codes appropriate (200, 201, 401, 403, 404, 422, 500)
- [ ] Examples realistic and in Indonesian

## Implementation Notes

### Audit Output Format

Audit report akan dihasilkan dalam format Markdown dengan struktur:

```markdown
# OpenAPI Audit Report

## Summary
- Total Endpoints: X
- Documented: Y
- Missing: Z
- Quality Score: N%

## Findings by Module

### Auth Module
| Endpoint | Issue | Severity | Recommendation |
|----------|-------|----------|----------------|
| POST /v1/auth/register | Missing password_confirmation | Major | Add field |

### Schemes Module
...

## Refactored OpenAPI Spec

[Patch/diff atau full spec]
```

### Priority Order for Audit

1. **Auth Module** - Critical for security
2. **Enrollments Module** - Core business logic
3. **Schemes Module** - Course management
4. **Content Module** - Content management
5. **Forums Module** - Community features
6. **Gamification Module** - Engagement features
7. **Other Modules** - Supporting features

### Files to Analyze

1. `storage/api-docs/openapi.json` - Current OpenAPI spec
2. `app/Services/OpenApiGeneratorService.php` - Generator service
3. `Modules/*/routes/api.php` - Route definitions
4. `Modules/*/app/Http/Requests/*.php` - FormRequest classes
5. `Modules/*/app/Http/Resources/*.php` - Resource classes
6. `Modules/*/app/Enums/*.php` - Enum definitions
7. `app/Support/ApiResponse.php` - Response structure

