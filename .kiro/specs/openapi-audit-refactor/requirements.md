# Requirements Document

## Introduction

Dokumen ini mendefinisikan requirements untuk melakukan audit kesesuaian antara dokumentasi OpenAPI dengan implementasi API aktual di Laravel, serta refactoring spesifikasi OpenAPI agar lebih konsisten dan mudah dibaca di Scalar. Audit mencakup pengecekan path, HTTP method, autentikasi, header, path/query params, request body, field required, aturan validasi, response schema, status code, dan format error.

## Glossary

- **OpenAPI**: Spesifikasi standar untuk dokumentasi REST API (sebelumnya Swagger)
- **Scalar**: Library untuk menampilkan dokumentasi API dengan UI modern
- **OpenApiGeneratorService**: Service Laravel yang generate OpenAPI spec dari routes
- **FormRequest**: Laravel class untuk validasi request dengan rules
- **Resource**: Laravel API Resource yang transform model ke JSON response
- **Path Parameter**: Parameter yang ada di URL path (e.g., `{id}`, `{slug}`)
- **Query Parameter**: Parameter yang ada di query string (e.g., `?page=1&per_page=15`)
- **Request Body**: Data yang dikirim dalam body request (JSON/form-data)
- **Validation Rules**: Aturan validasi Laravel (required, min, max, email, unique, confirmed, dll)
- **Mismatch**: Ketidaksesuaian antara dokumentasi dan implementasi aktual

## Requirements

### Requirement 1: Audit Path dan HTTP Method

**User Story:** As a developer, I want to verify that all documented API paths and HTTP methods match the actual Laravel routes, so that the documentation accurately reflects the available endpoints.

#### Acceptance Criteria

1. WHEN auditing API paths THEN the system SHALL compare all paths in openapi.json with registered Laravel routes
2. WHEN a path exists in documentation but not in routes THEN the system SHALL flag it as "documented but not implemented"
3. WHEN a path exists in routes but not in documentation THEN the system SHALL flag it as "implemented but not documented"
4. WHEN HTTP methods differ between documentation and routes THEN the system SHALL report the mismatch

### Requirement 2: Audit Authentication Requirements

**User Story:** As a developer, I want to verify that authentication requirements in documentation match the actual middleware configuration, so that API consumers know which endpoints require authentication.

#### Acceptance Criteria

1. WHEN an endpoint has `auth:api` middleware THEN the OpenAPI spec SHALL include `security: [{"bearerAuth": []}]`
2. WHEN an endpoint is public (no auth middleware) THEN the OpenAPI spec SHALL NOT include security requirement
3. WHEN authentication requirements differ THEN the system SHALL report the mismatch with actual middleware

### Requirement 3: Audit Path Parameters

**User Story:** As a developer, I want to verify that path parameters in documentation match the actual route parameters, so that API consumers can correctly construct URLs.

#### Acceptance Criteria

1. WHEN a route has path parameters (e.g., `{course}`, `{user}`) THEN the OpenAPI spec SHALL document each parameter with correct name and type
2. WHEN parameter binding uses slug (e.g., `{course:slug}`) THEN the OpenAPI spec SHALL indicate the parameter is a slug string
3. WHEN parameter binding uses id THEN the OpenAPI spec SHALL indicate the parameter is an integer

### Requirement 4: Audit Request Body and Validation Rules

**User Story:** As a developer, I want to verify that request body schemas and validation rules in documentation match the actual FormRequest classes, so that API consumers know exactly what data to send.

#### Acceptance Criteria

1. WHEN a controller method uses FormRequest THEN the OpenAPI spec SHALL document all fields from the FormRequest rules
2. WHEN a field has `required` rule THEN the OpenAPI spec SHALL mark the field as required
3. WHEN a field has `min:X` or `max:X` rule THEN the OpenAPI spec SHALL include minLength/maxLength or minimum/maximum
4. WHEN a field has `email` rule THEN the OpenAPI spec SHALL set format to "email"
5. WHEN a field has `confirmed` rule (e.g., password) THEN the OpenAPI spec SHALL document the confirmation field (e.g., password_confirmation)
6. WHEN a field has `unique` rule THEN the OpenAPI spec SHALL mention uniqueness in description
7. WHEN validation rules differ THEN the system SHALL report the mismatch

### Requirement 5: Audit Response Schema

**User Story:** As a developer, I want to verify that response schemas in documentation match the actual Resource/Transformer classes, so that API consumers know what data to expect.

#### Acceptance Criteria

1. WHEN a controller returns a Resource class THEN the OpenAPI spec SHALL document all fields from the Resource
2. WHEN response includes pagination THEN the OpenAPI spec SHALL include pagination meta structure
3. WHEN response fields differ from Resource THEN the system SHALL report the mismatch

### Requirement 6: Audit Status Codes

**User Story:** As a developer, I want to verify that documented status codes match the actual responses from controllers, so that API consumers can handle all possible responses.

#### Acceptance Criteria

1. WHEN a POST endpoint creates a resource THEN the OpenAPI spec SHALL document 201 status code
2. WHEN an endpoint can return validation errors THEN the OpenAPI spec SHALL document 422 status code
3. WHEN an endpoint requires authentication THEN the OpenAPI spec SHALL document 401 status code
4. WHEN an endpoint can return not found THEN the OpenAPI spec SHALL document 404 status code
5. WHEN status codes differ THEN the system SHALL report the mismatch

### Requirement 7: Audit Query Parameters

**User Story:** As a developer, I want to verify that query parameters in documentation match the actual controller/request handling, so that API consumers know what filters and pagination options are available.

#### Acceptance Criteria

1. WHEN an endpoint accepts query parameters (page, per_page, search, sort, filter) THEN the OpenAPI spec SHALL document each parameter
2. WHEN a query parameter has a default value THEN the OpenAPI spec SHALL specify the default value
3. WHEN a query parameter has type constraints THEN the OpenAPI spec SHALL specify the correct type (integer, string, boolean)
4. WHEN a query parameter is optional THEN the OpenAPI spec SHALL mark it as not required
5. WHEN query parameters differ from implementation THEN the system SHALL report the mismatch

### Requirement 8: Audit Enum and Constant Values

**User Story:** As a developer, I want to verify that enum/constant values in documentation match the actual PHP enums and constants, so that API consumers know all valid values.

#### Acceptance Criteria

1. WHEN a field accepts enum values (status, role, type) THEN the OpenAPI spec SHALL list all valid enum values
2. WHEN enum values are defined in PHP Enum class THEN the OpenAPI spec SHALL match those values exactly
3. WHEN enum values change THEN the system SHALL report the mismatch
4. WHEN documenting enum THEN the OpenAPI spec SHALL include description for each value if applicable

### Requirement 9: Audit File Upload Endpoints

**User Story:** As a developer, I want to verify that file upload endpoints are correctly documented with multipart/form-data content type, so that API consumers can properly upload files.

#### Acceptance Criteria

1. WHEN an endpoint accepts file upload THEN the OpenAPI spec SHALL use `multipart/form-data` content type
2. WHEN a file field has size limits THEN the OpenAPI spec SHALL document maximum file size
3. WHEN a file field has type restrictions (image, pdf, etc.) THEN the OpenAPI spec SHALL document allowed MIME types
4. WHEN file upload configuration differs THEN the system SHALL report the mismatch

### Requirement 10: Audit Naming Convention Consistency

**User Story:** As a developer, I want to verify that naming conventions are consistent throughout the OpenAPI spec, so that the documentation is predictable and easy to use.

#### Acceptance Criteria

1. WHEN documenting path segments THEN the OpenAPI spec SHALL use kebab-case (e.g., `/course-tags`, `/enrollment-key`)
2. WHEN documenting path/query parameters THEN the OpenAPI spec SHALL use snake_case (e.g., `per_page`, `course_id`)
3. WHEN documenting request/response fields THEN the OpenAPI spec SHALL use snake_case (e.g., `created_at`, `email_verified_at`)
4. WHEN naming schemas THEN the OpenAPI spec SHALL use PascalCase (e.g., `UserResource`, `CourseResponse`)
5. WHEN naming tags THEN the OpenAPI spec SHALL use Title Case in Indonesian (e.g., `Autentikasi`, `Pendaftaran Kelas`)
6. WHEN naming inconsistencies are found THEN the system SHALL report the mismatch

### Requirement 11: Audit Request Headers

**User Story:** As a developer, I want to verify that request headers are properly documented, so that API consumers know what headers to send.

#### Acceptance Criteria

1. WHEN an endpoint accepts JSON THEN the OpenAPI spec SHALL document `Accept: application/json` header
2. WHEN an endpoint sends JSON body THEN the OpenAPI spec SHALL document `Content-Type: application/json` header
3. WHEN an endpoint accepts file upload THEN the OpenAPI spec SHALL document `Content-Type: multipart/form-data` header
4. WHEN an endpoint requires authentication THEN the OpenAPI spec SHALL document `Authorization: Bearer {token}` header
5. WHEN custom headers are used THEN the OpenAPI spec SHALL document them with description

### Requirement 12: Audit API Versioning

**User Story:** As a developer, I want to verify that all endpoints use consistent API versioning, so that the API is predictable and maintainable.

#### Acceptance Criteria

1. WHEN documenting any endpoint THEN the OpenAPI spec SHALL use `/v1/` prefix consistently
2. WHEN an endpoint path doesn't have version prefix THEN the system SHALL flag it as inconsistent
3. WHEN version prefix differs from `/v1/` THEN the system SHALL report the inconsistency

### Requirement 13: Audit Rate Limiting

**User Story:** As a developer, I want to verify that rate limiting information is documented, so that API consumers know the request limits.

#### Acceptance Criteria

1. WHEN an endpoint has throttle middleware THEN the OpenAPI spec SHALL document the rate limit
2. WHEN documenting rate limit THEN the OpenAPI spec SHALL include requests per minute/hour
3. WHEN rate limit groups differ (auth, enrollment, api) THEN the OpenAPI spec SHALL document each group's limit
4. WHEN rate limit information is missing THEN the system SHALL flag it for documentation

### Requirement 14: Refactor Tags and Grouping

**User Story:** As a developer, I want the OpenAPI spec to have consistent tags organized by module/feature, so that the documentation is easy to navigate in Scalar.

#### Acceptance Criteria

1. WHEN organizing endpoints THEN the OpenAPI spec SHALL group endpoints by module (Auth, Schemes, Enrollments, etc.)
2. WHEN naming tags THEN the OpenAPI spec SHALL use consistent Indonesian naming convention
3. WHEN describing tags THEN the OpenAPI spec SHALL include clear, concise descriptions

### Requirement 15: Refactor Reusable Components

**User Story:** As a developer, I want the OpenAPI spec to use reusable components/schemas, so that the documentation is DRY and consistent.

#### Acceptance Criteria

1. WHEN multiple endpoints use the same response structure THEN the OpenAPI spec SHALL use $ref to shared schema
2. WHEN multiple endpoints use the same request body THEN the OpenAPI spec SHALL use $ref to shared schema
3. WHEN defining common structures (User, Course, Enrollment) THEN the OpenAPI spec SHALL define them in components/schemas

### Requirement 16: Standardize Response Structure

**User Story:** As a developer, I want all API responses to follow a consistent structure, so that API consumers can easily parse responses.

#### Acceptance Criteria

1. WHEN returning success response THEN the OpenAPI spec SHALL document structure: `{success, message, data, meta, errors}` matching actual ApiResponse trait
2. WHEN returning validation error THEN the OpenAPI spec SHALL document structure with errors object containing field-specific messages
3. WHEN returning unauthorized error THEN the OpenAPI spec SHALL document 401 response with appropriate message
4. WHEN returning not found error THEN the OpenAPI spec SHALL document 404 response with appropriate message
5. WHEN returning server error THEN the OpenAPI spec SHALL document 500 response with appropriate message
6. WHEN response structure differs from actual implementation THEN the system SHALL report the mismatch

### Requirement 17: Add Realistic Examples

**User Story:** As a developer, I want the OpenAPI spec to include realistic request/response examples, so that API consumers can understand the expected data format.

#### Acceptance Criteria

1. WHEN documenting request body THEN the OpenAPI spec SHALL include realistic example values (not just "string" or "example")
2. WHEN documenting response THEN the OpenAPI spec SHALL include realistic example matching actual API response
3. WHEN documenting error response THEN the OpenAPI spec SHALL include realistic error messages in Indonesian

### Requirement 18: Generate Audit Report

**User Story:** As a developer, I want a comprehensive audit report listing all mismatches and recommendations, so that I can systematically fix documentation issues.

#### Acceptance Criteria

1. WHEN audit is complete THEN the system SHALL generate a summary of documentation quality
2. WHEN mismatches are found THEN the system SHALL list each mismatch with endpoint, issue type, and recommendation
3. WHEN generating report THEN the system SHALL organize findings by module/endpoint

### Requirement 19: Generate Refactored OpenAPI Spec

**User Story:** As a developer, I want a refactored OpenAPI spec or patch/diff that I can apply to fix all issues, so that I can quickly update the documentation.

#### Acceptance Criteria

1. WHEN refactoring is complete THEN the system SHALL provide updated OpenAPI spec or patch
2. WHEN providing updates THEN the system SHALL ensure all mismatches are addressed
3. WHEN providing updates THEN the system SHALL maintain backward compatibility where possible
4. WHEN providing updates THEN the system SHALL be directly applicable to the openapi.json file

