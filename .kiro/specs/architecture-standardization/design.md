# Design Document

## Overview

Dokumen ini menjelaskan desain teknis untuk standardisasi arsitektur backend LSP. Fokus utama adalah:

1. Standardisasi ApiResponse pattern ke trait-based approach
2. Membuat Contracts/Interfaces untuk semua Repository dan Service
3. Migrasi DTOs ke Spatie Laravel Data
4. Standardisasi authorization ke Policy pattern
5. Cleanup folder structure (hapus Interfaces redundan)

## Architecture

### Current State Analysis

```
┌─────────────────────────────────────────────────────────────────┐
│                    Current Inconsistencies                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ApiResponse Patterns:                                           │
│  ├── Trait-based: Schemes, Enrollments, Auth                    │
│  ├── Static class: Gamification, Forums, Content (partial)      │
│  └── Manual response()->json(): Content (AnnouncementController)│
│                                                                  │
│  Contracts/Interfaces:                                           │
│  ├── Complete: Schemes, Content, Auth                           │
│  ├── Partial: Enrollments, Forums                               │
│  └── Missing: Gamification, Learning, Grading                   │
│                                                                  │
│  DTOs:                                                           │
│  ├── Using BaseDTO: All modules                                 │
│  └── Missing Update DTOs: Enrollments, Gamification, Learning   │
│                                                                  │
│  Authorization:                                                  │
│  ├── Policy: Content                                            │
│  ├── Gate: Schemes                                              │
│  └── Manual hasRole(): Enrollments, Gamification                │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Target State

```
┌─────────────────────────────────────────────────────────────────┐
│                    Standardized Architecture                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Request ──► FormRequest ──► Controller ──► Service ──► Response│
│                   │              │             │                 │
│                   ▼              ▼             ▼                 │
│              Validation     ApiResponse    Repository            │
│              (Spatie Data)   (Trait)      (Interface)           │
│                   │              │             │                 │
│                   ▼              ▼             ▼                 │
│                 DTO          Policy         Model                │
│           (Spatie Data)                                          │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. ApiResponse Trait (Existing - Standard)

Location: `app/Support/ApiResponse.php`

```php
trait ApiResponse
{
    protected function success(mixed $data, string $message, int $status = 200): JsonResponse;
    protected function created(mixed $data, string $message): JsonResponse;
    protected function error(string $message, int $status, ?array $errors = null): JsonResponse;
    protected function paginateResponse(LengthAwarePaginator $paginator, string $message): JsonResponse;
    protected function validationError(array $errors, string $message): JsonResponse;
    protected function notFound(string $message): JsonResponse;
    protected function forbidden(string $message): JsonResponse;
}
```

### 2. Spatie Laravel Data DTO Pattern

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;

final class CreateChallengeDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        
        #[Required]
        public string $description,
        
        #[Required]
        public string $type,
        
        #[Required]
        public int $xpReward,
        
        public ?int $courseId = null,
        public ?string $criteriaType = null,
        public ?int $criteriaValue = null,
        public ?Carbon $startDate = null,
        public ?Carbon $endDate = null,
    ) {}
}
```

### 3. Repository Interface Pattern

```php
namespace Modules\Gamification\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Gamification\Models\Challenge;

interface ChallengeRepositoryInterface
{
    public function findById(int $id): ?Challenge;
    public function findActive(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Challenge;
    public function update(Challenge $challenge, array $data): Challenge;
    public function delete(Challenge $challenge): bool;
}
```

### 4. Service Interface Pattern

```php
namespace Modules\Gamification\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Gamification\DTOs\CreateChallengeDTO;
use Modules\Gamification\DTOs\UpdateChallengeDTO;
use Modules\Gamification\Models\Challenge;

interface ChallengeServiceInterface
{
    public function getUserChallenges(int $userId): Collection;
    public function getActiveChallenge(int $challengeId): ?Challenge;
    public function getCompletedChallenges(int $userId, int $limit = 15): Collection;
    public function assignDailyChallenges(): int;
    public function assignWeeklyChallenges(): int;
    public function checkAndUpdateProgress(int $userId, string $criteriaType, int $count = 1): void;
    public function completeChallenge(UserChallengeAssignment $assignment): void;
    public function claimReward(int $userId, int $challengeId): array;
    public function expireOverdueChallenges(): int;
}
```

### 5. Policy Pattern

```php
namespace Modules\Gamification\Policies;

use Modules\Auth\Models\User;
use Modules\Gamification\Models\Challenge;

class ChallengePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view challenges
    }

    public function view(User $user, Challenge $challenge): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin');
    }

    public function update(User $user, Challenge $challenge): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Admin');
    }

    public function delete(User $user, Challenge $challenge): bool
    {
        return $user->hasRole('Superadmin');
    }

    public function claim(User $user, Challenge $challenge): bool
    {
        return true; // Users can claim their own completed challenges
    }
}
```

## Data Models

### DTOs to Create/Migrate

| Module | DTO | Status | Action |
|--------|-----|--------|--------|
| Enrollments | `UpdateEnrollmentDTO` | Missing | Create |
| Gamification | `UpdateChallengeDTO` | Missing | Create |
| Gamification | `UpdateBadgeDTO` | Missing | Create |
| Learning | `UpdateAssignmentDTO` | Missing | Create |
| Learning | `UpdateSubmissionDTO` | Missing | Create |
| Grading | `CreateGradeDTO` | Missing | Create |
| Grading | `UpdateGradeDTO` | Missing | Create |
| All existing | `*DTO` | BaseDTO | Migrate to Spatie Data |

### Interfaces to Create

| Module | Repository Interfaces | Service Interfaces |
|--------|----------------------|-------------------|
| Gamification | `ChallengeRepositoryInterface`, `GamificationRepositoryInterface` | `ChallengeServiceInterface`, `GamificationServiceInterface`, `LeaderboardServiceInterface` |
| Learning | `AssignmentRepositoryInterface`, `SubmissionRepositoryInterface` | `AssignmentServiceInterface`, `SubmissionServiceInterface`, `LearningPageServiceInterface` |
| Grading | `GradingRepositoryInterface` | `GradingServiceInterface` |
| Forums | - | `ForumServiceInterface`, `ModerationServiceInterface` |
| Enrollments | - | `EnrollmentServiceInterface` |
| Content | - | `AnnouncementServiceInterface`, `NewsServiceInterface`, `ContentStatisticsServiceInterface` |

### Policies to Create

| Module | Policies |
|--------|----------|
| Gamification | `ChallengePolicy`, `BadgePolicy` |
| Learning | `AssignmentPolicy`, `SubmissionPolicy` |
| Grading | `GradePolicy` |
| Enrollments | `EnrollmentPolicy` |
| Schemes | `CoursePolicy`, `UnitPolicy`, `LessonPolicy` |

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: ApiResponse Trait Consistency
*For any* controller in the application, if it returns a JSON response, it SHALL use the `ApiResponse` trait methods (`success()`, `error()`, `paginateResponse()`, etc.) instead of manual `response()->json()` or static `ApiResponse::` calls.
**Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5**

### Property 2: Interface-Implementation Parity
*For any* repository or service class, if it has a corresponding interface, all public methods in the class SHALL be declared in the interface.
**Validates: Requirements 2.1, 2.2**

### Property 3: DTO Spatie Data Inheritance
*For any* DTO class in the application, the class SHALL extend `Spatie\LaravelData\Data` and use validation attributes for required fields.
**Validates: Requirements 3.1, 3.2**

### Property 4: Policy Authorization Consistency
*For any* controller action that requires authorization, the controller SHALL use `$this->authorize()` or `Gate::authorize()` with a Policy, not manual `hasRole()` checks.
**Validates: Requirements 4.1, 4.8**

### Property 5: No Redundant Interface Folders
*For any* module in the application, there SHALL NOT be both `Interfaces` and `Contracts` folders - only `Contracts` SHALL exist.
**Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5, 5.6**

### Property 6: ServiceProvider Binding Completeness
*For any* interface in `Contracts/` folder, there SHALL be a corresponding binding in the module's ServiceProvider.
**Validates: Requirements 2.8, 6.5**

## Error Handling

| Exception | Use Case | HTTP Code |
|-----------|----------|-----------|
| `ModelNotFoundException` | Resource not found | 404 |
| `ValidationException` | Input validation failed (Spatie Data) | 422 |
| `BusinessException` | Business rule violated | 422 |
| `AuthorizationException` | Policy authorization failed | 403 |

## Testing Strategy

### Unit Testing
- Test setiap DTO dapat di-instantiate dari array dan request
- Test setiap Policy method mengembalikan boolean yang benar
- Test ServiceProvider bindings resolve ke correct implementation

### Property-Based Testing
- Menggunakan PHPUnit dengan data providers
- Test bahwa semua controllers menggunakan ApiResponse trait
- Test bahwa semua interfaces memiliki binding di ServiceProvider
- Test bahwa semua DTOs extend Spatie Data

### Integration Testing
- Test full request flow dengan DTO validation
- Test authorization dengan Policy
- Test response format consistency

## Implementation Notes

### Installation Requirements

```bash
composer require spatie/laravel-data
```

### Migration Strategy

1. **Phase 1**: Install Spatie Laravel Data, create base infrastructure
2. **Phase 2**: Create all missing interfaces and bind them
3. **Phase 3**: Migrate DTOs to Spatie Data (one module at a time)
4. **Phase 4**: Standardize ApiResponse in all controllers
5. **Phase 5**: Create Policies and migrate authorization
6. **Phase 6**: Cleanup redundant folders and update documentation

### Files to Modify/Create

**New Files:**
- `Modules/Gamification/app/Contracts/Repositories/*.php`
- `Modules/Gamification/app/Contracts/Services/*.php`
- `Modules/Gamification/app/Policies/*.php`
- `Modules/Learning/app/Contracts/Repositories/*.php`
- `Modules/Learning/app/Contracts/Services/*.php`
- `Modules/Learning/app/Policies/*.php`
- `Modules/Grading/app/Contracts/*.php`
- `Modules/Grading/app/Policies/*.php`
- `Modules/Enrollments/app/Contracts/Services/*.php`
- `Modules/Enrollments/app/Policies/*.php`
- Various `*DTO.php` files

**Files to Modify:**
- All controllers using static `ApiResponse::` or manual `response()->json()`
- All ServiceProviders to add interface bindings
- All existing DTOs to extend Spatie Data
- `docs/ARCHITECTURE.md`

**Folders to Delete:**
- `Modules/Schemes/app/Interfaces/`
- `Modules/Enrollments/app/Interfaces/`
- `Modules/Learning/app/Interfaces/`
- `Modules/Gamification/app/Interfaces/`

### Verification

```bash
# Run tests
php artisan test

# Check for remaining static ApiResponse calls
grep -r "ApiResponse::" Modules/*/app/Http/Controllers/

# Check for remaining response()->json() calls
grep -r "response()->json" Modules/*/app/Http/Controllers/

# Check for remaining hasRole() in authorization context
grep -r "hasRole.*403" Modules/*/app/Http/Controllers/
```
