<?php

namespace Modules\Schemes\Services;

use App\Exceptions\BusinessException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Schemes\Contracts\Repositories\CourseRepositoryInterface;
use Modules\Schemes\DTOs\CreateCourseDTO;
use Modules\Schemes\DTOs\UpdateCourseDTO;
use Modules\Schemes\Models\Course;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CourseService
{
    public function __construct(
        private readonly CourseRepositoryInterface $repository
    ) {}

    /**
     * Get paginated list of all courses.
     *
     * Supports:
     * - filter[search] (Scout/Meilisearch)
     * - filter[status], filter[level_tag], filter[type], filter[category_id], filter[tag]
     * - sort: id, code, title, created_at, updated_at, published_at (prefix with - for desc)
     * - include: tags, category, instructor, units
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);
        $query = $this->buildQuery();

        return $query->paginate($perPage);
    }

    /**
     * List all courses (paginated).
     *
     * Supports same filters/sorts/includes as paginate().
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->paginate($perPage);
    }

    /**
     * List public (published) courses with pagination.
     *
     * Supports same filters/sorts/includes as paginate(), automatically filters to published status.
     */
    public function listPublic(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);
        $query = $this->buildQuery()->where('status', 'published');

        return $query->paginate($perPage);
    }

    /**
     * Build query with Spatie Query Builder + Scout search + tag filtering.
     */
    private function buildQuery(): QueryBuilder
    {
        $searchQuery = request('filter.search') ?? request('search');

        $builder = QueryBuilder::for(Course::class);

        // Handle Scout search if search parameter is provided
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Course::search($searchQuery)->keys()->toArray();

            if (! empty($ids)) {
                $builder->whereIn('id', $ids);
            } else {
                // No results from search, return empty
                $builder->whereRaw('1 = 0');
            }
        }

        // Apply tag filters if provided
        $tagFilter = request('filter.tag');
        if ($tagFilter) {
            $tags = $this->parseArrayFilter($tagFilter);

            if (! empty($tags)) {
                foreach ($tags as $tagValue) {
                    $value = trim((string) $tagValue);
                    if ($value === '') {
                        continue;
                    }

                    $slug = Str::slug($value);

                    $builder->whereHas('tags', function (Builder $tagQuery) use ($value, $slug) {
                        $tagQuery->where(function (Builder $inner) use ($value, $slug) {
                            $inner->where('slug', $slug)
                                ->orWhere('slug', $value)
                                ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($value)]);
                        });
                    });
                }
            }
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('level_tag'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('category_id'),
            ])
            ->allowedIncludes(['tags', 'category', 'instructor', 'units'])
            ->allowedSorts(['id', 'code', 'title', 'created_at', 'updated_at', 'published_at'])
            ->defaultSort('title');
    }

    /**
     * Parse filter value that may be array or JSON string.
     */
    private function parseArrayFilter($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $trim = trim($value);

            if ($trim === '') {
                return [];
            }

            if ($trim[0] === '[' || str_starts_with($trim, '%5B')) {
                $decoded = json_decode($trim, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }

                $urldec = urldecode($trim);
                $decoded = json_decode($urldec, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            return [$trim];
        }

        return [];
    }

    /**
     * Find course by ID.
     */
    public function find(int $id): ?Course
    {
        return $this->repository->findById($id);
    }

    /**
     * Find course by ID or fail.
     */
    public function findOrFail(int $id): Course
    {
        $course = $this->repository->findById($id);
        if (! $course) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException;
        }

        return $course;
    }

    /**
     * Find course by slug.
     */
    public function findBySlug(string $slug): ?Course
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * Create a new course.
     * Note: Slug is auto-generated by HasSlug trait from title.
     */
    public function create(CreateCourseDTO|array $data): Course
    {
        $attributes = $data instanceof CreateCourseDTO ? $data->toArrayWithoutNull() : $data;

        // Remove slug from input - HasSlug trait handles it
        unset($attributes['slug']);

        // Generate code if not provided
        if (! isset($attributes['code'])) {
            $attributes['code'] = $this->generateCourseCode();
        }

        $tags = $attributes['tags'] ?? null;
        unset($attributes['tags']);

        $course = $this->repository->create($attributes);

        if ($tags) {
            $course->tags()->sync($tags);
        }

        return $course->fresh(['tags']);
    }

    /**
     * Update an existing course.
     * Note: Slug is NOT regenerated on update (doNotGenerateSlugsOnUpdate).
     */
    public function update(int $id, UpdateCourseDTO|array $data): Course
    {
        $course = $this->repository->findByIdOrFail($id);
        $attributes = $data instanceof UpdateCourseDTO ? $data->toArrayWithoutNull() : $data;

        // Remove slug from input - HasSlug handles it, and we don't regenerate on update
        unset($attributes['slug']);

        $tags = $attributes['tags'] ?? null;
        unset($attributes['tags']);

        $this->repository->update($course, $attributes);

        if ($tags !== null) {
            $course->tags()->sync($tags);
        }

        return $course->fresh(['tags']);
    }

    /**
     * Delete a course.
     */
    public function delete(int $id): bool
    {
        $course = $this->findOrFail($id);

        return $this->repository->delete($course);
    }

    /**
     * Publish a course.
     *
     * @throws BusinessException
     */
    public function publish(int $id): Course
    {
        $course = $this->findOrFail($id);

        // Business rule: course must have at least one unit with lessons
        if ($course->units()->count() === 0) {
            throw new BusinessException(
                'Kursus tidak dapat dipublikasikan karena belum memiliki unit.',
                ['units' => ['Kursus harus memiliki minimal satu unit.']]
            );
        }

        $hasLessons = $course->units()->whereHas('lessons')->exists();
        if (! $hasLessons) {
            throw new BusinessException(
                'Kursus tidak dapat dipublikasikan karena belum memiliki lesson.',
                ['lessons' => ['Kursus harus memiliki minimal satu lesson.']]
            );
        }

        $this->repository->update($course, [
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $course->fresh();
    }

    /**
     * Unpublish a course.
     */
    public function unpublish(int $id): Course
    {
        $course = $this->repository->findByIdOrFail($id);

        $this->repository->update($course, [
            'status' => 'draft',
            'published_at' => null,
        ]);

        return $course->fresh();
    }

    /**
     * Generate a unique course code.
     */
    private function generateCourseCode(): string
    {
        do {
            $code = 'CRS-'.strtoupper(Str::random(6));
        } while (Course::where('code', $code)->exists());

        return $code;
    }

    /**
     * Upload a thumbnail for the course.
     */
    public function uploadThumbnail(int $id, \Illuminate\Http\UploadedFile $file): Course
    {
        $course = $this->findOrFail($id);

        $course->clearMediaCollection('thumbnail');
        $course->addMedia($file)->toMediaCollection('thumbnail');

        return $course->fresh();
    }

    /**
     * Upload a banner for the course.
     */
    public function uploadBanner(int $id, \Illuminate\Http\UploadedFile $file): Course
    {
        $course = $this->findOrFail($id);

        $course->clearMediaCollection('banner');
        $course->addMedia($file)->toMediaCollection('banner');

        return $course->fresh();
    }

    /**
     * Delete the course thumbnail.
     */
    public function deleteThumbnail(int $id): Course
    {
        $course = $this->findOrFail($id);
        $course->clearMediaCollection('thumbnail');

        return $course->fresh();
    }

    /**
     * Delete the course banner.
     */
    public function deleteBanner(int $id): Course
    {
        $course = $this->findOrFail($id);
        $course->clearMediaCollection('banner');

        return $course->fresh();
    }
}
