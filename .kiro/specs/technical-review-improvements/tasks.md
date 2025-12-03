# Implementation Plan

## Phase 1: Security Hardening

- [x] 1. Implement Enrollment Key Hashing
  - [x] 1.1 Create EnrollmentKeyHasher service
    - Create `app/Support/EnrollmentKeyHasher.php` with hash, verify, and generate methods
    - Create `app/Contracts/EnrollmentKeyHasherInterface.php` interface
    - Register binding in `AppServiceProvider`
    - _Requirements: 1.2, 1.3_
  - [ ] 1.2 Write property test for enrollment key hashing
    - **Property 1: Enrollment Key Hash Integrity**
    - **Validates: Requirements 1.2, 1.3**
  - [x] 1.3 Create database migration for enrollment_key_hash
    - Add `enrollment_key_hash` column to courses table
    - Migrate existing plain text keys to hashed values
    - Remove `enrollment_key` column
    - _Requirements: 1.2_
  - [x] 1.4 Update Course model and CourseService
    - Update `$fillable` and `$hidden` arrays in Course model
    - Update CourseService to use EnrollmentKeyHasher for storing/validating keys
    - Update CourseController enrollment key methods
    - _Requirements: 1.2, 1.3_
  - [x] 1.5 Update EnrollmentsController to use hash verification
    - Modify enroll method to verify enrollment key using hash
    - _Requirements: 1.3_

- [-] 2. Implement Rate Limiting
  - [x] 2.1 Create rate limiting configuration
    - Create `config/rate-limiting.php` with configurable thresholds
    - Define limits for: default API, auth endpoints, enrollment endpoints
    - _Requirements: 1.5_
  - [x] 2.2 Configure rate limiting in RouteServiceProvider
    - Register rate limiters using RateLimiter facade
    - Apply different limits based on endpoint type
    - _Requirements: 1.5_
  - [x] 2.3 Apply rate limiting middleware to routes
    - Update Auth module routes with auth rate limiter
    - Update Enrollments module routes with enrollment rate limiter
    - Apply default rate limiter to all API routes
    - _Requirements: 1.5_
  - [ ]* 2.4 Write property test for rate limiting
    - **Property 2: Rate Limiting Enforcement**
    - **Validates: Requirements 1.5**

- [x] 3. Implement CORS Configuration
  - [x] 3.1 Create CORS configuration file
    - Create `config/cors.php` with allowed origins, methods, headers
    - Use environment variable for allowed origins
    - _Requirements: 1.6_
  - [x] 3.2 Register CORS middleware
    - Add CORS middleware to API middleware group in bootstrap/app.php
    - _Requirements: 1.6_
  - [ ]* 3.3 Write property test for CORS validation
    - **Property 3: CORS Header Validation**
    - **Validates: Requirements 1.6**

- [x] 4. Clean up environment configuration
  - [x] 4.1 Update .env.example with secure defaults
    - Set APP_DEBUG=false
    - Remove any actual secrets, use placeholders
    - Add CORS_ALLOWED_ORIGINS placeholder
    - _Requirements: 1.1, 1.4_

- [ ] 5. Checkpoint - Security hardening complete
  - Ensure all tests pass, ask the user if questions arise.

## Phase 2: Architecture Consistency

- [x] 6. Standardize interface folder naming
  - [x] 6.1 Rename Auth module Interfaces to Contracts
    - Rename `Modules/Auth/app/Interfaces/` to `Modules/Auth/app/Contracts/`
    - Update namespace references in all files
    - Update AuthServiceProvider bindings
    - _Requirements: 2.1_
  - [x] 6.2 Create Contracts folders for modules without interfaces
    - Create `Contracts/Services/` and `Contracts/Repositories/` in Schemes module
    - Create `Contracts/Services/` and `Contracts/Repositories/` in Learning module
    - Create `Contracts/Services/` and `Contracts/Repositories/` in Enrollments module
    - Create `Contracts/Services/` and `Contracts/Repositories/` in Assessments module
    - Create `Contracts/Services/` and `Contracts/Repositories/` in Gamification module
    - _Requirements: 2.1_

- [x] 7. Create service interfaces for Schemes module
  - [x] 7.1 Create CourseServiceInterface
    - Define interface in `Modules/Schemes/app/Contracts/Services/CourseServiceInterface.php`
    - Update CourseService to implement interface
    - Bind interface in SchemesServiceProvider
    - _Requirements: 2.2, 2.4_
  - [x] 7.2 Create UnitServiceInterface
    - Define interface in `Modules/Schemes/app/Contracts/Services/UnitServiceInterface.php`
    - Update UnitService to implement interface
    - Bind interface in SchemesServiceProvider
    - _Requirements: 2.2, 2.4_
  - [x] 7.3 Create LessonServiceInterface
    - Define interface in `Modules/Schemes/app/Contracts/Services/LessonServiceInterface.php`
    - Update LessonService to implement interface
    - Bind interface in SchemesServiceProvider
    - _Requirements: 2.2, 2.4_

- [x] 8. Create repository interfaces for Schemes module
  - [x] 8.1 Create CourseRepositoryInterface
    - Define interface in `Modules/Schemes/app/Contracts/Repositories/CourseRepositoryInterface.php`
    - Update CourseRepository to implement interface
    - Bind interface in SchemesServiceProvider
    - _Requirements: 2.3, 2.4_
  - [x] 8.2 Create UnitRepositoryInterface
    - Define interface in `Modules/Schemes/app/Contracts/Repositories/UnitRepositoryInterface.php`
    - Update UnitRepository to implement interface
    - Bind interface in SchemesServiceProvider
    - _Requirements: 2.3, 2.4_
  - [x] 8.3 Create LessonRepositoryInterface
    - Define interface in `Modules/Schemes/app/Contracts/Repositories/LessonRepositoryInterface.php`
    - Update LessonRepository to implement interface
    - Bind interface in SchemesServiceProvider
    - _Requirements: 2.3, 2.4_

- [ ] 9. Refactor controllers to use only service interfaces
  - [ ] 9.1 Update CourseController
    - Remove direct repository injection
    - Inject only CourseServiceInterface
    - _Requirements: 2.5_
  - [ ] 9.2 Update UnitController
    - Remove direct repository injection if present
    - Inject only UnitServiceInterface
    - _Requirements: 2.5_
  - [ ] 9.3 Update LessonController
    - Remove direct repository injection if present
    - Inject only LessonServiceInterface
    - _Requirements: 2.5_

- [ ]* 10. Write property test for service interface binding
  - **Property 4: Service Interface Binding Resolution**
  - **Validates: Requirements 2.4**

- [ ] 11. Checkpoint - Architecture consistency complete
  - Ensure all tests pass, ask the user if questions arise.

## Phase 3: API Route Consistency

- [-] 12. Fix HTTP method inconsistencies in Enrollments module
  - [ ] 12.1 Change enrollment state change routes to PATCH
    - Update `POST enrollments/{enrollment}/approve` to `PATCH`
    - Update `POST enrollments/{enrollment}/decline` to `PATCH`
    - Update `POST enrollments/{enrollment}/remove` to `PATCH`
    - Update `POST courses/{course}/cancel` to `PATCH`
    - Update `POST courses/{course}/withdraw` to `PATCH`
    - _Requirements: 3.1, 3.2, 3.3_

- [ ] 13. Clean up Assessments module route prefixes
  - [ ] 13.1 Remove redundant assessments prefix
    - Change `/assessments/exercises` to `/exercises`
    - Change `/assessments/questions` to `/questions`
    - Change `/assessments/attempts` to `/attempts`
    - Change `/assessments/answers` to `/answers`
    - Change `/assessments/options` to `/options`
    - Update all route names accordingly
    - _Requirements: 3.4_

- [ ]* 14. Write property test for API response format
  - **Property 5: API Response Format Consistency**
  - **Validates: Requirements 3.5**

- [ ] 15. Checkpoint - API route consistency complete
  - Ensure all tests pass, ask the user if questions arise.

## Phase 4: Database Integrity

- [ ] 16. Implement polymorphic scope validation for exercises
  - [ ] 16.1 Create ExerciseScopeValidator
    - Create validation rule class for scope_type and scope_id
    - Validate scope_id exists in corresponding table based on scope_type
    - _Requirements: 5.1_
  - [ ] 16.2 Update ExerciseRequest validation
    - Add custom validation rule for scope validation
    - _Requirements: 5.1_
  - [ ]* 16.3 Write property test for polymorphic scope validation
    - **Property 6: Polymorphic Scope Validation**
    - **Validates: Requirements 5.1**

- [ ] 17. Add cascade handling for scope entity deletion
  - [ ] 17.1 Create observer for scope entity deletion
    - Create ExerciseScopeObserver to handle course/unit/lesson deletion
    - Soft delete or archive related exercises when scope is deleted
    - _Requirements: 5.2_

- [ ] 18. Clean up migration duplicate checks
  - [ ] 18.1 Review and fix users table migration
    - Remove `if (!Schema::hasTable('users'))` check
    - Ensure migration runs cleanly on fresh database
    - _Requirements: 5.3_

- [ ] 19. Checkpoint - Database integrity complete
  - Ensure all tests pass, ask the user if questions arise.

## Phase 5: Error Handling Consolidation

- [ ] 20. Consolidate exception handling
  - [ ] 20.1 Add rate limiting exception handler to bootstrap/app.php
    - Handle ThrottleRequestsException with proper JSON response
    - Include retry_after in meta
    - _Requirements: 6.1_
  - [ ] 20.2 Simplify Handler.php
    - Remove duplicate exception handling logic
    - Keep only base class extension
    - _Requirements: 6.2_
  - [ ] 20.3 Fix null return in auth route exception handlers
    - Replace null returns with explicit response or re-throw
    - _Requirements: 6.3_

- [ ] 21. Checkpoint - Error handling complete
  - Ensure all tests pass, ask the user if questions arise.

## Phase 6: API Documentation

- [ ] 22. Create OpenAPI specification for Auth module
  - [ ] 22.1 Create Auth module openapi.yaml
    - Document all auth endpoints (register, login, logout, refresh, etc.)
    - Document user management endpoints
    - Document profile endpoints
    - Use standardized response schema
    - _Requirements: 4.1, 4.6_

- [ ] 23. Create OpenAPI specification for Schemes module
  - [ ] 23.1 Create Schemes module openapi.yaml
    - Document course CRUD endpoints
    - Document unit CRUD endpoints
    - Document lesson CRUD endpoints
    - Document tag endpoints
    - Document progress endpoints
    - Use standardized response schema
    - _Requirements: 4.2, 4.6_

- [ ] 24. Create OpenAPI specification for Enrollments module
  - [ ] 24.1 Create Enrollments module openapi.yaml
    - Document enrollment endpoints
    - Document enrollment status endpoints
    - Document reporting endpoints
    - Use standardized response schema
    - _Requirements: 4.3, 4.6_

- [ ] 25. Create OpenAPI specification for Assessments module
  - [ ] 25.1 Create Assessments module openapi.yaml
    - Document exercise CRUD endpoints
    - Document question CRUD endpoints
    - Document attempt endpoints
    - Document grading endpoints
    - Use standardized response schema
    - _Requirements: 4.4, 4.6_

- [ ] 26. Create OpenAPI specification for Learning module
  - [ ] 26.1 Create Learning module openapi.yaml
    - Document assignment CRUD endpoints
    - Document submission endpoints
    - Use standardized response schema
    - _Requirements: 4.5, 4.6_

- [ ] 27. Checkpoint - API documentation complete
  - Ensure all tests pass, ask the user if questions arise.

## Phase 7: Testing Infrastructure

- [ ] 28. Fix test database configuration
  - [ ] 28.1 Update TestCase.php to use environment variables
    - Replace hardcoded port with env('DB_PORT')
    - Add phpunit.xml configuration for test database
    - _Requirements: 7.1_

- [ ] 29. Add RefreshDatabase trait to test classes
  - [ ] 29.1 Update existing test files
    - Add RefreshDatabase trait to all feature test classes
    - Ensure proper test isolation
    - _Requirements: 7.3_
  - [ ]* 29.2 Write property test for test database isolation
    - **Property 7: Test Database Isolation**
    - **Validates: Requirements 7.3**

- [ ] 30. Final Checkpoint - All improvements complete
  - Ensure all tests pass, ask the user if questions arise.
