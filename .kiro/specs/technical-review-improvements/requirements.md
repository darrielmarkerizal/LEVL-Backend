# Requirements Document

## Introduction

Dokumen ini mendefinisikan requirements untuk perbaikan teknis pada project Laravel backend LSP (Lembaga Sertifikasi Profesi) berdasarkan hasil technical review. Perbaikan mencakup aspek keamanan, konsistensi arsitektur, dokumentasi API, dan production readiness.

## Glossary

- **LSP System**: Sistem backend Laravel untuk platform Learning Management System (LMS) sertifikasi profesi
- **Module**: Unit kode terpisah menggunakan nwidart/laravel-modules yang menangani domain bisnis spesifik
- **Service Layer**: Layer yang berisi business logic dan orchestration
- **Repository Layer**: Layer yang menangani data access dan persistence
- **Interface**: Contract yang mendefinisikan method signatures untuk dependency injection
- **JWT**: JSON Web Token untuk authentication
- **OpenAPI**: Spesifikasi standar untuk dokumentasi REST API
- **Rate Limiting**: Mekanisme pembatasan jumlah request dalam periode waktu tertentu
- **Enrollment Key**: Kode akses untuk mendaftar ke course dengan mode key-based

## Requirements

### Requirement 1: Security Hardening

**User Story:** As a system administrator, I want the application to follow security best practices, so that user data and system integrity are protected from vulnerabilities.

#### Acceptance Criteria

1. WHEN the application is deployed to production THEN the LSP System SHALL have APP_DEBUG set to false in environment configuration
2. WHEN storing enrollment keys THEN the LSP System SHALL hash the enrollment_key value before persisting to database
3. WHEN validating enrollment keys THEN the LSP System SHALL compare using secure hash verification
4. WHEN the .env.example file is reviewed THEN the LSP System SHALL contain placeholder values without actual secrets
5. WHEN API endpoints receive requests THEN the LSP System SHALL apply rate limiting with configurable thresholds
6. WHEN cross-origin requests are received THEN the LSP System SHALL validate against configured CORS whitelist

### Requirement 2: Architecture Consistency

**User Story:** As a developer, I want consistent architecture patterns across all modules, so that the codebase is maintainable and predictable.

#### Acceptance Criteria

1. WHEN a module requires abstraction THEN the LSP System SHALL use "Contracts" folder naming for interface definitions
2. WHEN a service class is created THEN the LSP System SHALL have a corresponding interface in the Contracts folder
3. WHEN a repository class is created THEN the LSP System SHALL have a corresponding interface in the Contracts folder
4. WHEN interfaces are defined THEN the LSP System SHALL bind them to implementations in the module's ServiceProvider
5. WHEN a controller requires business logic THEN the LSP System SHALL inject only the service interface, not repository directly

### Requirement 3: API Route Consistency

**User Story:** As an API consumer, I want consistent and RESTful API endpoints, so that integration is predictable and follows industry standards.

#### Acceptance Criteria

1. WHEN approving an enrollment THEN the LSP System SHALL use PATCH method instead of POST for the state change operation
2. WHEN cancelling an enrollment THEN the LSP System SHALL use PATCH method instead of POST for the state change operation
3. WHEN declining an enrollment THEN the LSP System SHALL use PATCH method instead of POST for the state change operation
4. WHEN defining assessment routes THEN the LSP System SHALL remove redundant "assessments/" prefix from nested resources
5. WHEN all API responses are returned THEN the LSP System SHALL follow the standardized response format with success, message, data, meta, and errors fields

### Requirement 4: API Documentation

**User Story:** As an API consumer, I want complete and accurate API documentation, so that I can integrate with the system efficiently.

#### Acceptance Criteria

1. WHEN the Auth module endpoints are accessed THEN the LSP System SHALL have corresponding OpenAPI specification
2. WHEN the Schemes module endpoints are accessed THEN the LSP System SHALL have corresponding OpenAPI specification
3. WHEN the Enrollments module endpoints are accessed THEN the LSP System SHALL have corresponding OpenAPI specification
4. WHEN the Assessments module endpoints are accessed THEN the LSP System SHALL have corresponding OpenAPI specification
5. WHEN the Learning module endpoints are accessed THEN the LSP System SHALL have corresponding OpenAPI specification
6. WHEN OpenAPI specs define response schemas THEN the LSP System SHALL match the actual implementation response format

### Requirement 5: Database Integrity

**User Story:** As a database administrator, I want proper data integrity constraints, so that orphan records and data inconsistencies are prevented.

#### Acceptance Criteria

1. WHEN exercises reference scope entities THEN the LSP System SHALL validate scope_id exists in the corresponding table based on scope_type
2. WHEN a scope entity (course/unit/lesson) is deleted THEN the LSP System SHALL handle related exercises appropriately
3. WHEN database migrations are executed THEN the LSP System SHALL not contain duplicate table existence checks

### Requirement 6: Error Handling Consolidation

**User Story:** As a developer, I want centralized and consistent error handling, so that debugging is easier and user experience is uniform.

#### Acceptance Criteria

1. WHEN exceptions are handled THEN the LSP System SHALL use only bootstrap/app.php for API exception rendering
2. WHEN the Handler.php file exists THEN the LSP System SHALL delegate to bootstrap/app.php configuration or be removed
3. WHEN auth route exceptions return null THEN the LSP System SHALL provide explicit fallback behavior

### Requirement 7: Testing Infrastructure

**User Story:** As a developer, I want reliable testing infrastructure, so that I can write and run tests consistently across environments.

#### Acceptance Criteria

1. WHEN test database configuration is defined THEN the LSP System SHALL use environment variables for port configuration
2. WHEN tests are executed THEN the LSP System SHALL have test coverage for critical business logic in each module
3. WHEN module tests are run THEN the LSP System SHALL properly isolate test data using RefreshDatabase trait

