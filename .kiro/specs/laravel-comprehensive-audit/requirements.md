# Requirements Document

## Introduction

Dokumen ini berisi hasil audit menyeluruh terhadap project Laravel LMS (Learning Management System) yang menggunakan arsitektur modular dengan nwidart/laravel-modules. Audit mencakup aspek arsitektur, konsistensi, database design, API routing, testing, dokumentasi, keamanan, dan production readiness.

## Glossary

- **LMS**: Learning Management System - Sistem manajemen pembelajaran
- **Module**: Unit terpisah dalam arsitektur modular Laravel menggunakan nwidart/laravel-modules
- **EARS**: Easy Approach to Requirements Syntax - Pola penulisan requirements
- **3NF**: Third Normal Form - Tingkat normalisasi database
- **N+1 Query**: Anti-pattern query database yang menyebabkan query berlebihan
- **JWT**: JSON Web Token - Mekanisme autentikasi stateless
- **OpenAPI/Scalar**: Standar dokumentasi API

## Requirements

### Requirement 1: Arsitektur & Struktur Folder

**User Story:** Sebagai developer, saya ingin struktur project yang konsisten dan terorganisir, sehingga mudah untuk maintenance dan onboarding developer baru.

#### Acceptance Criteria

1. THE project structure SHALL follow consistent modular architecture pattern across all modules
2. WHEN a new module is created THEN the module SHALL follow the established folder structure (Controllers, Services, Repositories, Models, etc.)
3. THE project SHALL NOT contain god-classes (classes with more than 500 lines or 10+ methods)
4. WHEN business logic is implemented THEN the logic SHALL be placed in Service classes, NOT in Controllers

### Requirement 2: Konsistensi Penamaan

**User Story:** Sebagai developer, saya ingin penamaan yang konsisten di seluruh codebase, sehingga mudah untuk memahami dan menavigasi kode.

#### Acceptance Criteria

1. THE naming convention SHALL be consistent: snake_case for database columns, camelCase for PHP methods, PascalCase for classes
2. THE route naming SHALL follow RESTful conventions with consistent prefix (e.g., `/api/v1/`)
3. THE model names SHALL be singular (User, Course) and table names SHALL be plural (users, courses)
4. WHEN enum values are used THEN the values SHALL be consistent across the codebase (lowercase or UPPERCASE, not mixed)

### Requirement 3: API Routing

**User Story:** Sebagai API consumer, saya ingin endpoint yang konsisten dan tidak ambigu, sehingga mudah untuk mengintegrasikan dengan frontend.

#### Acceptance Criteria

1. THE API routes SHALL use consistent versioning prefix (`/api/v1/`)
2. THE HTTP methods SHALL be used correctly: GET for read, POST for create, PUT/PATCH for update, DELETE for delete
3. THE route paths SHALL NOT have conflicts or ambiguities
4. WHEN nested resources are used THEN the nesting SHALL NOT exceed 3 levels deep
5. THE route naming SHALL follow RESTful conventions (plural nouns for collections)

### Requirement 4: Validasi & Error Handling

**User Story:** Sebagai API consumer, saya ingin response error yang konsisten dan informatif, sehingga mudah untuk debugging dan handling errors.

#### Acceptance Criteria

1. THE validation logic SHALL be placed in FormRequest classes, NOT in Controllers
2. THE error response format SHALL be consistent: `{ success, message, data, meta, errors }`
3. THE HTTP status codes SHALL be used correctly (200, 201, 400, 401, 403, 404, 422, 500)
4. WHEN validation fails THEN the response SHALL include field-specific error messages

### Requirement 5: Database Design & Normalisasi

**User Story:** Sebagai database administrator, saya ingin schema database yang optimal dan ternormalisasi, sehingga tidak ada redundansi data dan query efisien.

#### Acceptance Criteria

1. THE database schema SHALL be normalized to at least 3NF (Third Normal Form)
2. THE foreign keys SHALL be properly defined with appropriate ON DELETE actions
3. THE columns that are frequently used in WHERE, JOIN, ORDER BY SHALL have indexes
4. THE enum columns SHALL use PHP Enum classes for type safety
5. WHEN JSON columns are used THEN the usage SHALL be justified (not for data that should be normalized)

### Requirement 6: Query & Performa

**User Story:** Sebagai user, saya ingin aplikasi yang responsif, sehingga tidak ada delay yang mengganggu pengalaman.

#### Acceptance Criteria

1. THE queries SHALL NOT have N+1 problems (use eager loading with `with()`)
2. THE list endpoints SHALL implement pagination
3. WHEN heavy queries are needed THEN the results SHALL be cached appropriately
4. THE database indexes SHALL cover frequently queried columns

### Requirement 7: Keamanan

**User Story:** Sebagai security officer, saya ingin aplikasi yang aman dari common vulnerabilities, sehingga data user terlindungi.

#### Acceptance Criteria

1. THE sensitive endpoints SHALL be protected with authentication middleware
2. THE authorization SHALL be implemented using Gates/Policies for resource access
3. THE models SHALL have proper `$fillable` or `$guarded` to prevent mass assignment
4. THE sensitive data SHALL NOT be exposed in API responses (passwords, tokens, etc.)
5. WHEN file uploads are handled THEN the files SHALL be validated for type and size

### Requirement 8: Production Readiness

**User Story:** Sebagai DevOps engineer, saya ingin aplikasi yang siap untuk production deployment, sehingga tidak ada masalah saat go-live.

#### Acceptance Criteria

1. THE `.env.example` SHALL contain all required environment variables with safe defaults
2. THE `APP_DEBUG` SHALL be set to `false` in production
3. THE logging SHALL be configured appropriately for production (not debug level)
4. THE queue and cache drivers SHALL be configured for production (not sync/array)

### Requirement 9: Code Quality & Best Practice

**User Story:** Sebagai developer, saya ingin codebase yang mengikuti best practices, sehingga mudah untuk maintain dan extend.

#### Acceptance Criteria

1. THE code SHALL follow PSR-12 coding standard
2. THE code SHALL follow SOLID principles
3. THE code SHALL NOT have duplicate logic (DRY principle)
4. THE dependency injection SHALL be used instead of direct instantiation

### Requirement 10: Testing (Pest)

**User Story:** Sebagai QA engineer, saya ingin test coverage yang memadai, sehingga regresi dapat terdeteksi lebih awal.

#### Acceptance Criteria

1. THE test suite SHALL cover positive scenarios (happy path)
2. THE test suite SHALL cover negative scenarios (validation errors, unauthorized access, not found)
3. THE test suite SHALL cover edge cases (boundary values, empty inputs)
4. THE test names SHALL be descriptive using Pest conventions (`it_can_...`, `it_fails_when_...`)
5. WHEN a new feature is added THEN the feature SHALL have corresponding tests

### Requirement 11: Dokumentasi API (Scalar/OpenAPI)

**User Story:** Sebagai API consumer, saya ingin dokumentasi API yang akurat dan up-to-date, sehingga mudah untuk mengintegrasikan.

#### Acceptance Criteria

1. THE API documentation SHALL be synchronized with actual implementation
2. THE documented endpoints SHALL match the routes in code
3. THE request/response schemas SHALL match the actual data structures
4. WHEN routes are added or modified THEN the documentation SHALL be updated accordingly
