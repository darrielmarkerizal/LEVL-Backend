# Requirements Document

## Introduction

Dokumen ini mendefinisikan requirements untuk refactoring API project LMS agar konsisten mengikuti arsitektur berlapis (Controller → Service → Repository) yang sudah didefinisikan di `docs/ARCHITECTURE.md`. Refactoring ini bertujuan untuk menghilangkan inkonsistensi, meningkatkan maintainability, dan memastikan semua controller hanya memanggil service layer tanpa business logic.

## Glossary

- **Controller**: Layer yang menangani HTTP request/response, validasi input via FormRequest, dan memanggil Service layer
- **Service**: Layer yang berisi business logic, orchestration antar repository, dan transaction management
- **Repository**: Layer yang menangani data access (CRUD), query building, filtering, dan pagination
- **DTO (Data Transfer Object)**: Object untuk transfer data antar layer dengan type-safety menggunakan Spatie Laravel Data
- **ApiResponse Trait**: Trait standar untuk format response API yang konsisten
- **Interface**: Contract yang mendefinisikan method signature untuk Service dan Repository

## Requirements

### Requirement 1

**User Story:** As a developer, I want all controllers to follow the same pattern, so that the codebase is consistent and maintainable.

#### Acceptance Criteria

1. WHEN a controller method is called THEN the Controller SHALL only perform request validation, authorization, DTO conversion, and service method invocation without containing business logic
2. WHEN a controller returns a response THEN the Controller SHALL use ApiResponse trait methods (success, created, error, paginateResponse) for consistent response format
3. WHEN a controller needs to access data THEN the Controller SHALL call Service layer methods instead of directly querying Models or Repositories
4. WHEN a controller handles errors THEN the Controller SHALL delegate error handling to the exception handler or return standardized error responses via ApiResponse trait

### Requirement 2

**User Story:** As a developer, I want all services to have corresponding interfaces, so that dependencies can be properly injected and tested.

#### Acceptance Criteria

1. WHEN a new service is created THEN the Service SHALL implement a corresponding ServiceInterface defined in Contracts/Services directory
2. WHEN a service is injected into a controller THEN the Controller SHALL depend on the ServiceInterface rather than the concrete Service class
3. WHEN a service needs data access THEN the Service SHALL call Repository methods instead of directly querying Models
4. WHEN a service performs complex operations THEN the Service SHALL manage database transactions appropriately

### Requirement 3

**User Story:** As a developer, I want response formats to be consistent across all API endpoints, so that frontend developers can easily consume the API.

#### Acceptance Criteria

1. WHEN an API returns success response THEN the Response SHALL follow the format: `{"success": true, "message": "...", "data": {...}}`
2. WHEN an API returns paginated data THEN the Response SHALL include meta information with pagination details
3. WHEN an API returns error response THEN the Response SHALL follow the format: `{"success": false, "message": "...", "errors": {...}}`
4. WHEN an API returns created resource THEN the Response SHALL use HTTP status code 201 with created() method

### Requirement 4

**User Story:** As a developer, I want business logic to be centralized in services, so that it can be reused and tested independently.

#### Acceptance Criteria

1. WHEN business rules need to be applied THEN the Service layer SHALL contain all business logic validation and processing
2. WHEN multiple repositories need to be coordinated THEN the Service layer SHALL orchestrate the operations
3. WHEN data transformation is needed THEN the Service layer SHALL handle DTO to Model array conversion
4. WHEN authorization checks beyond Policy are needed THEN the Service layer SHALL implement the authorization logic

### Requirement 5

**User Story:** As a developer, I want all modules to follow the same directory structure, so that code organization is predictable.

#### Acceptance Criteria

1. WHEN a module has services THEN the Module SHALL have Contracts/Services directory with corresponding interfaces
2. WHEN a module has repositories THEN the Module SHALL have Contracts/Repositories directory with corresponding interfaces
3. WHEN interfaces are defined THEN the ServiceProvider SHALL bind interfaces to implementations
4. WHEN DTOs are used THEN the Module SHALL have DTOs directory with Spatie Laravel Data classes

### Requirement 6

**User Story:** As a developer, I want to eliminate duplicate authorization logic in controllers, so that authorization is handled consistently.

#### Acceptance Criteria

1. WHEN authorization is needed THEN the Controller SHALL use Policy-based authorization via $this->authorize() method
2. WHEN custom authorization logic exists in controller THEN the Logic SHALL be moved to Policy or Service layer
3. WHEN role-based checks are needed THEN the Controller SHALL delegate to Policy methods or Service layer

### Requirement 7

**User Story:** As a developer, I want statistics and reporting logic to be properly layered, so that complex queries are maintainable.

#### Acceptance Criteria

1. WHEN statistics are calculated THEN the Repository layer SHALL handle raw database queries and aggregations
2. WHEN statistics are processed THEN the Service layer SHALL handle business logic transformation of raw data
3. WHEN statistics service exists THEN the Service SHALL have a corresponding interface for dependency injection

### Requirement 8

**User Story:** As a developer, I want to eliminate duplicate code across controllers, so that maintenance is easier and bugs are fixed in one place.

#### Acceptance Criteria

1. WHEN authorization logic like `userCanManageCourse()` is needed in multiple controllers THEN the Logic SHALL be extracted to a shared Trait, Policy, or Service
2. WHEN similar helper methods exist in multiple controllers THEN the Methods SHALL be consolidated into a shared location (Trait or base class)
3. WHEN duplicate validation logic exists THEN the Logic SHALL be moved to FormRequest classes or shared validation rules
4. WHEN duplicate response formatting exists THEN the Formatting SHALL use ApiResponse trait consistently

### Requirement 9

**User Story:** As a developer, I want placeholder/incomplete controllers to be either implemented or removed, so that the codebase is clean.

#### Acceptance Criteria

1. WHEN a controller has empty or placeholder methods THEN the Controller SHALL be either fully implemented or removed from the codebase
2. WHEN a controller returns view responses in an API-only project THEN the Controller SHALL be refactored to return JSON responses or removed
3. WHEN backup files exist (like .bak files) THEN the Files SHALL be removed from the repository

### Requirement 10

**User Story:** As a developer, I want consistent error handling across all API endpoints, so that errors are predictable and debuggable.

#### Acceptance Criteria

1. WHEN an exception occurs in controller THEN the Controller SHALL let the global exception handler process it or use ApiResponse error methods
2. WHEN catching exceptions manually THEN the Controller SHALL use ApiResponse::error() method with appropriate HTTP status codes
3. WHEN business logic errors occur THEN the Service layer SHALL throw appropriate custom exceptions (BusinessException, ResourceNotFoundException, etc.)
4. WHEN validation fails THEN the System SHALL return 422 status with standardized error format via ValidationException

### Requirement 11

**User Story:** As a developer, I want proper logging and audit trails for important operations, so that we can track and debug issues.

#### Acceptance Criteria

1. WHEN user performs sensitive operations (login, password change, account deletion) THEN the System SHALL create audit log entries
2. WHEN audit logging is needed THEN the Service layer SHALL handle audit creation instead of Controller
3. WHEN logging is performed THEN the Log entries SHALL include user_id, action, ip_address, and relevant metadata

### Requirement 12

**User Story:** As a developer, I want API documentation to be accurate and consistent, so that frontend developers can integrate easily.

#### Acceptance Criteria

1. WHEN a controller method is documented THEN the Documentation SHALL accurately reflect the actual request/response format
2. WHEN response examples are provided THEN the Examples SHALL match the actual ApiResponse format (success, message, data, meta, errors)
3. WHEN authentication is required THEN the Documentation SHALL clearly indicate @authenticated annotation
4. WHEN role restrictions apply THEN the Documentation SHALL specify required roles via @role annotation

### Requirement 13

**User Story:** As a developer, I want all controllers to use ApiResponse trait, so that response format is consistent.

#### Acceptance Criteria

1. WHEN a controller handles API requests THEN the Controller SHALL use ApiResponse trait
2. WHEN returning success response THEN the Controller SHALL use $this->success() or $this->created() methods
3. WHEN returning error response THEN the Controller SHALL use $this->error() or specific error methods (notFound, forbidden, unauthorized)
4. WHEN returning paginated data THEN the Controller SHALL use $this->paginateResponse() method

### Requirement 14

**User Story:** As a developer, I want response format to use 'success' key instead of 'status', so that format is consistent with ApiResponse trait.

#### Acceptance Criteria

1. WHEN returning JSON response THEN the Response SHALL use 'success' boolean key instead of 'status' string key
2. WHEN migrating existing responses THEN the Controller SHALL replace `['status' => 'success']` with ApiResponse trait methods
3. WHEN error responses are returned THEN the Response SHALL use `success: false` instead of `status: 'error'`


### Requirement 15

**User Story:** As a developer, I want API endpoints to be performant, so that users have a good experience.

#### Acceptance Criteria

1. WHEN fetching related data THEN the Repository SHALL use eager loading to prevent N+1 query problems
2. WHEN returning large datasets THEN the API SHALL implement pagination with configurable page size
3. WHEN data is frequently accessed THEN the Service layer SHALL implement caching strategies where appropriate
4. WHEN complex queries are executed THEN the Repository SHALL use query optimization techniques (indexes, select specific columns)

### Requirement 16

**User Story:** As a developer, I want API endpoints to be secure, so that the application is protected from attacks.

#### Acceptance Criteria

1. WHEN API endpoints are accessed THEN the System SHALL enforce rate limiting to prevent abuse
2. WHEN user input is received THEN the FormRequest SHALL sanitize and validate all input data
3. WHEN sensitive data is returned THEN the Response SHALL exclude sensitive fields (passwords, tokens, internal IDs where appropriate)
4. WHEN authentication is required THEN the Middleware SHALL verify JWT tokens and reject invalid requests

### Requirement 17

**User Story:** As a developer, I want API versioning to be properly implemented, so that breaking changes don't affect existing clients.

#### Acceptance Criteria

1. WHEN API routes are defined THEN the Routes SHALL be prefixed with version number (e.g., /api/v1/)
2. WHEN breaking changes are introduced THEN the System SHALL create new version endpoints while maintaining backward compatibility
3. WHEN deprecated endpoints exist THEN the Documentation SHALL clearly mark them as deprecated with migration guidance
4. WHEN version-specific logic is needed THEN the Controller SHALL delegate to appropriate versioned service methods
