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
│  - Request → DTO (via Spatie Data::from())                      │
│  - Model → DTO (via Spatie Data::from())                        │
│  - DTO → Response (via ApiResponse trait)                       │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Layer Responsibilities

### Controller Layer
- Validate input via FormRequest
- Convert request to DTO using Spatie Laravel Data
- Call Service methods
- Return Response via ApiResponse trait
- Authorize using Policy
- NO business logic
- NO direct database queries

### Service Layer
- Business logic
- Business rule validation
- Orchestration between repositories
- Transaction management
- Return DTO or Model objects
- Implements ServiceInterface

### Repository Layer
- Data access (CRUD)
- Query building
- Filtering & Sorting
- Pagination
- NO business logic
- Implements RepositoryInterface

### DTO Layer (Spatie Laravel Data)
- Type-safe data transfer
- Validation via attributes
- Factory methods (from())
- toArray() for serialization

## Directory Structure

```
Modules/{ModuleName}/
├── app/
│   ├── Contracts/
│   │   ├── Repositories/
│   │   │   └── {Model}RepositoryInterface.php
│   │   └── Services/
│   │       └── {Model}ServiceInterface.php
│   ├── DTOs/
│   │   ├── Create{Model}DTO.php
│   │   └── Update{Model}DTO.php
│   ├── Policies/
│   │   └── {Model}Policy.php
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

### DTO Example (Spatie Laravel Data)

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateCourseDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,
        
        public ?string $description = null,
        
        #[MapInputName('category_id')]
        public ?int $categoryId = null,
    ) {}
    
    public function toModelArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
        ];
    }
}

// Usage in controller
$dto = CreateCourseDTO::from($request);
$course = $this->service->create($dto);
```

### Update DTO Example (with Optional fields)

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateCourseDTO extends Data
{
    public function __construct(
        public string|Optional|null $title,
        public string|Optional|null $description,
    ) {}
    
    public function toModelArray(): array
    {
        $data = [];
        
        if (!$this->title instanceof Optional) {
            $data['title'] = $this->title;
        }
        if (!$this->description instanceof Optional) {
            $data['description'] = $this->description;
        }
        
        return $data;
    }
}
```

### Repository Interface Example

```php
interface CourseRepositoryInterface
{
    public function findById(int $id): ?Course;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Course;
    public function update(Course $course, array $data): Course;
    public function delete(Course $course): bool;
}
```

### Repository Example

```php
class CourseRepository implements CourseRepositoryInterface
{
    public function findById(int $id): ?Course
    {
        return Course::find($id);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Course::paginate($perPage);
    }
    
    // ... other methods
}
```

### Service Interface Example

```php
interface CourseServiceInterface
{
    public function list(int $perPage = 15): LengthAwarePaginator;
    public function create(CreateCourseDTO $dto): Course;
    public function update(int $id, UpdateCourseDTO $dto): Course;
    public function delete(int $id): bool;
    public function publish(int $id): Course;
}
```

### Service Example

```php
class CourseService implements CourseServiceInterface
{
    public function __construct(
        private CourseRepositoryInterface $repository
    ) {}

    public function create(CreateCourseDTO $dto): Course
    {
        return $this->repository->create($dto->toArray());
    }
}
```

### Policy Example

```php
class CoursePolicy
{
    public function view(?User $user, Course $course): bool
    {
        if ($course->status === 'published') {
            return true;
        }
        
        return $user && $user->hasRole('Superadmin');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->hasRole('Superadmin') 
            || (int) $course->instructor_id === (int) $user->id;
    }
}
```

### Controller Example with ApiResponse Trait

```php
class CourseController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CourseServiceInterface $service
    ) {}

    public function index(Request $request)
    {
        $courses = $this->service->list($request->input('per_page', 15));
        
        return $this->paginateResponse($courses);
    }

    public function store(CreateCourseRequest $request)
    {
        $this->authorize('create', Course::class);
        
        $dto = CreateCourseDTO::from($request);
        $course = $this->service->create($dto);
        
        return $this->created(['course' => $course], 'Course berhasil dibuat.');
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $this->authorize('update', $course);
        
        $dto = UpdateCourseDTO::from($request);
        $updated = $this->service->update($course->id, $dto);
        
        return $this->success(['course' => $updated], 'Course berhasil diperbarui.');
    }
}
```

## ApiResponse Trait Methods

| Method | Description | HTTP Code |
|--------|-------------|-----------|
| `success($data, $message)` | Success response | 200 |
| `created($data, $message)` | Resource created | 201 |
| `error($message, $status)` | Error response | Variable |
| `paginateResponse($paginator)` | Paginated response | 200 |
| `validationError($errors)` | Validation failed | 422 |
| `notFound($message)` | Resource not found | 404 |
| `forbidden($message)` | Access denied | 403 |

## Exception Handling

| Exception | Use Case | HTTP Code |
|-----------|----------|-----------|
| ModelNotFoundException | Resource not found | 404 |
| ValidationException | Input validation failed | 422 |
| BusinessException | Business rule violated | 422 |
| AuthorizationException | Policy authorization failed | 403 |

## ServiceProvider Bindings

Each module's ServiceProvider should bind interfaces to implementations:

```php
public function register(): void
{
    // Repository bindings
    $this->app->bind(
        CourseRepositoryInterface::class,
        CourseRepository::class
    );

    // Service bindings
    $this->app->singleton(
        CourseServiceInterface::class,
        CourseService::class
    );
}
```
