# Design Document

## Overview

Dokumen ini menjelaskan desain teknis untuk refactoring arsitektur backend LSP dengan mengimplementasikan pola DTO (Data Transfer Object), Repository Pattern, dan Service Layer yang konsisten. Tujuannya adalah:

1. Menghilangkan array acak dengan DTO yang type-safe
2. Memusatkan query dan data access di Repository
3. Memusatkan business logic di Service
4. Menyederhanakan Controller menjadi thin layer

## Architecture

### Current Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Current Flow (Inconsistent)                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Request ──► Controller ──► Model (direct query) ──► Response   │
│                  │                                               │
│                  ├──► Service (some modules)                     │
│                  │                                               │
│                  └──► Repository (some modules)                  │
│                                                                  │
│  Problems:                                                       │
│  - Array acak untuk data transfer                               │
│  - Query logic tersebar di Controller dan Service               │
│  - Business logic kadang di Controller                          │
│  - Tidak semua module menggunakan Repository                    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Target Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Target Flow (Consistent)                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Request ──► FormRequest ──► Controller ──► Service ──► Response│
│                                    │                             │
│                                    ▼                             │
│                              Repository                          │
│                                    │                             │
│                                    ▼                             │
│                                 Model                            │
│                                                                  │
│  Data Flow:                                                      │
│  - Request → DTO (via fromRequest)                              │
│  - Model → DTO (via fromModel)                                  │
│  - DTO → Response (via Resource)                                │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Layer Responsibilities

```
┌─────────────────────────────────────────────────────────────────┐
│ Layer          │ Responsibility                                  │
├─────────────────────────────────────────────────────────────────┤
│ Controller     │ - Validate input (FormRequest)                 │
│                │ - Call Service                                  │
│                │ - Return Response (Resource)                    │
│                │ - NO business logic                             │
│                │ - NO direct database queries                    │
├─────────────────────────────────────────────────────────────────┤
│ Service        │ - Business logic                                │
│                │ - Validation lanjutan (business rules)          │
│                │ - Orchestration antar Repository/Service        │
│                │ - Transaction management                        │
│                │ - Return DTO atau Model                         │
├─────────────────────────────────────────────────────────────────┤
│ Repository     │ - Data access (CRUD)                            │
│                │ - Query building                                │
│                │ - Filtering & Sorting                           │
│                │ - Pagination                                    │
│                │ - NO business logic                             │
├─────────────────────────────────────────────────────────────────┤
│ DTO            │ - Type-safe data transfer                       │
│                │ - Immutable data structure                      │
│                │ - Factory methods (fromRequest, fromModel)      │
│                │ - toArray() for serialization                   │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Base DTO Class

```php
<?php

namespace App\Support;

abstract class BaseDTO
{
    /**
     * Create DTO from request data.
     */
    abstract public static function fromRequest(array $data): static;

    /**
     * Convert DTO to array.
     */
    abstract public function toArray(): array;

    /**
     * Create DTO from model.
     */
    public static function fromModel($model): static
    {
        return static::fromRequest($model->toArray());
    }

    /**
     * Get only non-null values as array.
     */
    public function toArrayWithoutNull(): array
    {
        return array_filter($this->toArray(), fn ($value) => $value !== null);
    }
}
```

### 2. Base Repository Interface

```php
<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get query builder instance.
     */
    public function query(): Builder;

    /**
     * Find by ID.
     */
    public function findById(int $id): ?Model;

    /**
     * Find by ID or fail.
     */
    public function findByIdOrFail(int $id): Model;

    /**
     * Create new record.
     */
    public function create(array $attributes): Model;

    /**
     * Update existing record.
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * Delete record.
     */
    public function delete(Model $model): bool;

    /**
     * Get paginated list.
     */
    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all records matching params.
     */
    public function list(array $params): Collection;
}
```

### 3. Abstract Base Repository

```php
<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use App\Support\FilterableRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    use FilterableRepository;

    /**
     * Model class name.
     */
    abstract protected function model(): string;

    /**
     * Allowed filter keys.
     */
    protected array $allowedFilters = [];

    /**
     * Allowed sort fields.
     */
    protected array $allowedSorts = ['id', 'created_at', 'updated_at'];

    /**
     * Default sort field.
     */
    protected string $defaultSort = 'id';

    /**
     * Default relations to load.
     */
    protected array $with = [];

    public function query(): Builder
    {
        return $this->model()::query()->with($this->with);
    }

    public function findById(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): Model
    {
        return $this->model()::create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filteredPaginate(
            $this->query(),
            $params,
            $this->allowedFilters,
            $this->allowedSorts,
            $this->defaultSort,
            $perPage
        );
    }

    public function list(array $params): Collection
    {
        $query = $this->query();
        $this->applyFiltering($query, $params, $this->allowedFilters, $this->allowedSorts, $this->defaultSort);
        return $query->get();
    }
}
```

### 4. Base Service Class

```php
<?php

namespace App\Services;

use App\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    public function __construct(
        protected readonly BaseRepositoryInterface $repository
    ) {}

    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($params, max(1, $perPage));
    }

    public function list(array $params): Collection
    {
        return $this->repository->list($params);
    }

    public function find(int $id): ?Model
    {
        return $this->repository->findById($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->repository->findByIdOrFail($id);
        return $this->repository->update($model, $data);
    }

    public function delete(int $id): bool
    {
        $model = $this->repository->findByIdOrFail($id);
        return $this->repository->delete($model);
    }
}
```

### 5. Custom Exceptions

```php
<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected array $errors = [];

    public function __construct(string $message, array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

## Data Models

### DTO Structure Example

```php
<?php

namespace Modules\Schemes\DTOs;

use App\Support\BaseDTO;

final class CreateCourseDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?int $categoryId,
        public readonly ?string $levelTag,
        public readonly ?string $type,
        public readonly ?array $tags,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            title: $data['title'],
            description: $data['description'] ?? null,
            categoryId: $data['category_id'] ?? null,
            levelTag: $data['level_tag'] ?? null,
            type: $data['type'] ?? null,
            tags: $data['tags'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'level_tag' => $this->levelTag,
            'type' => $this->type,
            'tags' => $this->tags,
        ];
    }
}
```

### Module Directory Structure

```
Modules/{ModuleName}/
├── app/
│   ├── Contracts/
│   │   └── Repositories/
│   │       └── {Model}RepositoryInterface.php
│   ├── DTOs/
│   │   ├── Create{Model}DTO.php
│   │   ├── Update{Model}DTO.php
│   │   └── {Model}FilterDTO.php
│   ├── Repositories/
│   │   └── {Model}Repository.php
│   ├── Services/
│   │   └── {Model}Service.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── {Model}Controller.php
│   │   ├── Requests/
│   │   │   ├── Create{Model}Request.php
│   │   │   └── Update{Model}Request.php
│   │   └── Resources/
│   │       └── {Model}Resource.php
│   └── Providers/
│       └── {ModuleName}ServiceProvider.php
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: DTO Round-Trip Consistency
*For any* valid input data array, creating a DTO via fromRequest() and then calling toArray() SHALL preserve all non-null values from the original data.
**Validates: Requirements 1.4, 1.6**

### Property 2: DTO Required Field Validation
*For any* DTO class with required properties, instantiating with missing required fields SHALL throw an appropriate exception.
**Validates: Requirements 1.3**

### Property 3: Repository Filter Application
*For any* Repository paginate() or list() call with filter parameters, the returned results SHALL only contain records matching the specified filters.
**Validates: Requirements 2.3**

### Property 4: Repository Sort Application
*For any* Repository paginate() or list() call with sort parameters, the returned results SHALL be ordered according to the specified sort field and direction.
**Validates: Requirements 2.4**

### Property 5: Repository Pagination Structure
*For any* Repository paginate() call, the result SHALL be a LengthAwarePaginator with correct current_page, per_page, total, and last_page values.
**Validates: Requirements 2.5**

### Property 6: Repository CRUD Operations
*For any* Repository implementing BaseRepositoryInterface, create() SHALL return a persisted model, update() SHALL modify the model, and delete() SHALL remove the model.
**Validates: Requirements 2.6**

### Property 7: Service Business Exception
*For any* business rule violation in Service layer, the service SHALL throw BusinessException with appropriate message and error details.
**Validates: Requirements 5.3**

### Property 8: Repository Not Found Exception
*For any* Repository findByIdOrFail() call with non-existent ID, the repository SHALL throw ModelNotFoundException.
**Validates: Requirements 5.2**

### Property 9: Exception Response Consistency
*For any* thrown BusinessException or ValidationException, the exception handler SHALL return JSON response with structure: {success: false, message: string, data: null, meta: null, errors: object}.
**Validates: Requirements 5.4**

## Error Handling

### Exception Types

| Exception | Use Case | HTTP Code |
|-----------|----------|-----------|
| `ModelNotFoundException` | Resource not found | 404 |
| `ValidationException` | Input validation failed | 422 |
| `BusinessException` | Business rule violated | 422 |
| `AuthorizationException` | Permission denied | 403 |
| `AuthenticationException` | Not authenticated | 401 |

### Exception Handler Updates

```php
// app/Exceptions/Handler.php
public function render($request, Throwable $e)
{
    if ($e instanceof BusinessException) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'data' => null,
            'meta' => null,
            'errors' => $e->getErrors(),
        ], $e->getCode());
    }

    // ... existing handlers
}
```

## Testing Strategy

### Unit Testing

- Test DTO fromRequest() creates correct instance
- Test DTO toArray() returns correct structure
- Test Repository methods return correct types
- Test Service business logic with mocked Repository

### Property-Based Testing

Menggunakan PHPUnit dengan data providers:

```php
/**
 * @dataProvider validDTODataProvider
 */
public function test_dto_round_trip(array $data): void
{
    $dto = CreateCourseDTO::fromRequest($data);
    $array = $dto->toArray();
    
    $this->assertEquals($data['title'], $array['title']);
}
```

### Integration Testing

- Test full flow: Request → Controller → Service → Repository → Response
- Test exception handling produces correct response structure

## Implementation Notes

### Files to Create

1. `app/Support/BaseDTO.php` - Abstract base DTO class
2. `app/Contracts/BaseRepositoryInterface.php` - Base repository interface
3. `app/Repositories/BaseRepository.php` - Abstract base repository
4. `app/Services/BaseService.php` - Abstract base service
5. `app/Exceptions/BusinessException.php` - Custom business exception

### Migration Strategy

1. **Phase 1**: Create base classes (DTO, Repository, Service)
2. **Phase 2**: Refactor Auth module sebagai pilot
3. **Phase 3**: Refactor Schemes module (Course, Unit, Lesson)
4. **Phase 4**: Refactor remaining modules

### Existing Code to Leverage

Project sudah memiliki beberapa pattern yang bisa digunakan:

1. `FilterableRepository` trait di `app/Support/FilterableRepository.php`
2. `QueryFilter` class untuk filtering
3. Repository interfaces di beberapa module (Auth, Schemes)
4. Service classes di beberapa module (Common, Gamification)

### Verification

- Run `php artisan test` untuk memastikan semua test pass
- Check code coverage untuk DTO, Repository, dan Service
- Review controller methods untuk memastikan tidak ada query langsung
