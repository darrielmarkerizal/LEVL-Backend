# Requirements Document

## Introduction

Dokumen ini mendefinisikan requirements untuk standardisasi arsitektur backend LSP dengan fokus pada konsistensi pattern di seluruh module. Tujuannya adalah memastikan semua module mengikuti pattern yang sama untuk ApiResponse, Contracts/Interfaces, DTOs (menggunakan Spatie Laravel Data), Authorization (Policy), dan struktur folder yang bersih.

## Glossary

- **ApiResponse**: Trait atau class untuk standardisasi format response API
- **Contract/Interface**: PHP interface yang mendefinisikan kontrak untuk Repository dan Service
- **DTO (Data Transfer Object)**: Object untuk transfer data antar layer menggunakan Spatie Laravel Data
- **Policy**: Laravel authorization class untuk menentukan akses user terhadap resource
- **Repository**: Layer yang menangani data access logic
- **Service**: Layer yang berisi business logic
- **Spatie Laravel Data**: Package untuk membuat type-safe data objects dengan validasi dan transformasi

## Requirements

### Requirement 1: Standardisasi ApiResponse Pattern

**User Story:** Sebagai developer, saya ingin semua controller menggunakan pattern ApiResponse yang sama, sehingga format response API konsisten di seluruh aplikasi.

#### Acceptance Criteria

1. WHEN a controller returns a success response THEN the controller SHALL use the `ApiResponse` trait method `success()` or `created()`
2. WHEN a controller returns an error response THEN the controller SHALL use the `ApiResponse` trait method `error()` or `validationError()`
3. WHEN a controller returns paginated data THEN the controller SHALL use the `ApiResponse` trait method `paginateResponse()`
4. WHEN refactoring Content module controllers THEN the system SHALL replace manual `response()->json()` calls with `ApiResponse` trait methods
5. WHEN refactoring Gamification module controllers THEN the system SHALL replace static `ApiResponse::success()` calls with trait-based methods

### Requirement 2: Buat Contracts/Interfaces untuk Module yang Belum Ada

**User Story:** Sebagai developer, saya ingin semua repository dan service memiliki interface, sehingga dependency injection konsisten dan testable.

#### Acceptance Criteria

1. WHEN creating repository interface THEN the interface SHALL define all public methods dari repository class
2. WHEN creating service interface THEN the interface SHALL define all public methods dari service class
3. WHEN Gamification module is deployed THEN the module SHALL have `ChallengeRepositoryInterface`, `GamificationRepositoryInterface`, `ChallengeServiceInterface`, `GamificationServiceInterface`, dan `LeaderboardServiceInterface`
4. WHEN Learning module is deployed THEN the module SHALL have `AssignmentRepositoryInterface`, `SubmissionRepositoryInterface`, `AssignmentServiceInterface`, dan `SubmissionServiceInterface`
5. WHEN Grading module is deployed THEN the module SHALL have `GradingRepositoryInterface` dan `GradingServiceInterface`
6. WHEN Forums module is deployed THEN the module SHALL have `ForumServiceInterface` dan `ModerationServiceInterface`
7. WHEN Enrollments module is deployed THEN the module SHALL have `EnrollmentServiceInterface`
8. WHEN binding interfaces THEN the ServiceProvider SHALL bind interface to concrete implementation

### Requirement 3: Migrasi DTOs ke Spatie Laravel Data

**User Story:** Sebagai developer, saya ingin semua DTOs menggunakan Spatie Laravel Data, sehingga mendapat fitur validasi, transformasi, dan type-safety yang lebih baik.

#### Acceptance Criteria

1. WHEN creating a new DTO THEN the DTO class SHALL extend `Spatie\LaravelData\Data`
2. WHEN a DTO has validation rules THEN the DTO SHALL use Spatie Data validation attributes
3. WHEN converting request to DTO THEN the system SHALL use `DTO::from($request)` method
4. WHEN converting DTO to array THEN the system SHALL use `$dto->toArray()` method
5. WHEN Enrollments module is deployed THEN the module SHALL have `UpdateEnrollmentDTO` class
6. WHEN Gamification module is deployed THEN the module SHALL have `UpdateChallengeDTO` dan `UpdateBadgeDTO` classes
7. WHEN Learning module is deployed THEN the module SHALL have `UpdateAssignmentDTO` dan `UpdateSubmissionDTO` classes
8. WHEN Grading module is deployed THEN the module SHALL have `CreateGradeDTO` dan `UpdateGradeDTO` classes
9. WHEN existing DTOs are migrated THEN the DTOs SHALL maintain backward compatibility with existing code

### Requirement 4: Standardisasi Authorization ke Policy Pattern

**User Story:** Sebagai developer, saya ingin semua authorization menggunakan Policy pattern, sehingga logic authorization terpusat dan konsisten.

#### Acceptance Criteria

1. WHEN authorizing resource access THEN the controller SHALL use `$this->authorize()` method atau `Gate::authorize()`
2. WHEN creating a Policy THEN the Policy SHALL define methods untuk view, create, update, delete, dan custom actions
3. WHEN Enrollments module is deployed THEN the module SHALL have `EnrollmentPolicy` class
4. WHEN Gamification module is deployed THEN the module SHALL have `ChallengePolicy` dan `BadgePolicy` classes
5. WHEN Learning module is deployed THEN the module SHALL have `AssignmentPolicy` dan `SubmissionPolicy` classes
6. WHEN Grading module is deployed THEN the module SHALL have `GradePolicy` class
7. WHEN Schemes module is deployed THEN the module SHALL have `CoursePolicy`, `UnitPolicy`, dan `LessonPolicy` classes
8. WHEN refactoring controllers THEN the system SHALL replace manual role checks dengan Policy authorization

### Requirement 5: Hapus Folder Interfaces yang Redundan

**User Story:** Sebagai developer, saya ingin struktur folder yang bersih tanpa redundansi, sehingga codebase lebih mudah dipahami.

#### Acceptance Criteria

1. WHEN cleaning up module structure THEN the system SHALL remove empty `Interfaces` folders
2. WHEN a module has both `Interfaces` and `Contracts` folders THEN the system SHALL consolidate ke `Contracts` folder saja
3. WHEN Schemes module is cleaned THEN the `Interfaces` folder SHALL be removed
4. WHEN Enrollments module is cleaned THEN the `Interfaces` folder SHALL be removed
5. WHEN Learning module is cleaned THEN the `Interfaces` folder SHALL be removed
6. WHEN Gamification module is cleaned THEN the `Interfaces` folder SHALL be removed

### Requirement 6: Tambahkan Contracts untuk Module yang Belum Lengkap

**User Story:** Sebagai developer, saya ingin semua module memiliki struktur Contracts yang lengkap, sehingga arsitektur konsisten.

#### Acceptance Criteria

1. WHEN a module has repositories THEN the module SHALL have corresponding repository interfaces di `Contracts/Repositories/`
2. WHEN a module has services THEN the module SHALL have corresponding service interfaces di `Contracts/Services/`
3. WHEN Content module is deployed THEN the module SHALL have service interfaces untuk semua services
4. WHEN Auth module is deployed THEN the module SHALL have complete service interfaces untuk ProfileService, EmailVerificationService, dll
5. WHEN interfaces are created THEN the ServiceProvider SHALL register bindings untuk interface ke implementation

### Requirement 7: Update Architecture Documentation

**User Story:** Sebagai developer, saya ingin dokumentasi arsitektur yang up-to-date, sehingga onboarding developer baru lebih mudah.

#### Acceptance Criteria

1. WHEN architecture changes are made THEN the `docs/ARCHITECTURE.md` SHALL be updated
2. WHEN documenting DTO pattern THEN the documentation SHALL reference Spatie Laravel Data usage
3. WHEN documenting ApiResponse THEN the documentation SHALL show trait-based pattern
4. WHEN documenting authorization THEN the documentation SHALL show Policy pattern examples
