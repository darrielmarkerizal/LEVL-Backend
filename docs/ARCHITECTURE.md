# Architecture Documentation

## Overview

Aplikasi ini menggunakan arsitektur berlapis dengan pola DTO (Data Transfer Object), Repository Pattern, dan Service Layer yang konsisten.

## Layer Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Request Flow                             │
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

## Layer Responsibilities

### Controller Layer
- Validate input via FormRequest
- Convert request to DTO
- Call Service methods
- Return Response via Resource
- NO business logic
- NO direct database queries

### Service Layer
- Business logic
- Business rule validation
- Orchestration between repositories
- Transaction management
- Return DTO or Model objects

### Repository Layer
- Data access (CRUD)
- Query building
- Filtering & Sorting
- Pagination
- NO business logic

### DTO Layer
- Type-safe data transfer
- Immutable data structure
- Factory methods (fromRequest, fromModel)
- toArray() for serialization

## Directory Structure

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
│   │   ├── Requests/
│   │   └── Resources/
│   └── Providers/
│       └── {ModuleName}ServiceProvider.php
```

## Examples

### DTO Example

```php
final class CreateCourseDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            title: $data['title'],
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
```

### Repository Example

```php
class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    protected function model(): string
    {
        return Course::class;
    }

    protected array $allowedFilters = ['status', 'level_tag'];
    protected array $allowedSorts = ['title', 'created_at'];
}
```

### Service Example

```php
class CourseService
{
    public function __construct(
        private CourseRepositoryInterface $repository
    ) {}

    public function create(CreateCourseDTO $dto, User $author): Course
    {
        return $this->repository->create(array_merge(
            $dto->toArray(),
            ['author_id' => $author->id]
        ));
    }
}
```

## Exception Handling

| Exception | Use Case | HTTP Code |
|-----------|----------|-----------|
| ModelNotFoundException | Resource not found | 404 |
| ValidationException | Input validation failed | 422 |
| BusinessException | Business rule violated | 422 |
| AuthorizationException | Permission denied | 403 |
