<?php

namespace Modules\Schemes\Services;

use App\Exceptions\BusinessException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Modules\Schemes\Contracts\Repositories\CourseRepositoryInterface;
use Modules\Schemes\DTOs\CourseFilterDTO;
use Modules\Schemes\DTOs\CreateCourseDTO;
use Modules\Schemes\DTOs\UpdateCourseDTO;
use Modules\Schemes\Models\Course;

class CourseService
{
    public function __construct(
        private readonly CourseRepositoryInterface $repository
    ) {}

    /**
     * Get paginated list of courses.
     */
    public function paginate(CourseFilterDTO|array $params, int $perPage = 15): LengthAwarePaginator
    {
        $params = $params instanceof CourseFilterDTO ? $params->toArray() : $params;

        return $this->repository->paginate($params, max(1, $perPage));
    }

    /**
     * Get all courses matching params.
     */
    public function list(CourseFilterDTO|array $params): Collection
    {
        $params = $params instanceof CourseFilterDTO ? $params->toArray() : $params;

        return $this->repository->list($params);
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
        return $this->repository->findByIdOrFail($id);
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
     */
    public function create(CreateCourseDTO|array $data): Course
    {
        $attributes = $data instanceof CreateCourseDTO ? $data->toArrayWithoutNull() : $data;

        // Generate slug from title
        if (! isset($attributes['slug']) && isset($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

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
     */
    public function update(int $id, UpdateCourseDTO|array $data): Course
    {
        $course = $this->repository->findByIdOrFail($id);
        $attributes = $data instanceof UpdateCourseDTO ? $data->toArrayWithoutNull() : $data;

        // Update slug if title changed
        if (isset($attributes['title']) && $attributes['title'] !== $course->title) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

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
        $course = $this->repository->findByIdOrFail($id);

        return $this->repository->delete($course);
    }

    /**
     * Publish a course.
     *
     * @throws BusinessException
     */
    public function publish(int $id): Course
    {
        $course = $this->repository->findByIdOrFail($id);

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
}
