<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Contracts\Services\CourseServiceInterface;
use Modules\Schemes\DTOs\CreateCourseDTO;
use Modules\Schemes\DTOs\UpdateCourseDTO;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Services\Support\CourseFinder;
use Modules\Schemes\Services\Support\CourseLifecycleProcessor;
use Modules\Schemes\Services\Support\CoursePublicationProcessor;

class CourseService implements CourseServiceInterface
{
    public function __construct(
        private readonly CourseFinder $finder,
        private readonly CourseLifecycleProcessor $lifecycleProcessor,
        private readonly CoursePublicationProcessor $publicationProcessor
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->finder->paginate($filters, $perPage);
    }

    public function paginateForIndex(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->finder->paginateForIndex($filters, $perPage);
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->finder->list($filters, $perPage);
    }

    public function listForIndex(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->finder->listForIndex($filters, $perPage);
    }

    public function listPublic(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listPublic($perPage, $filters);
    }

    public function listPublicForIndex(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->finder->listPublicForIndex($perPage, $filters);
    }

    public function find(int $id): ?Course
    {
        return $this->finder->find($id);
    }

    public function findOrFail(int $id): Course
    {
        return $this->finder->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Course
    {
        return $this->finder->findBySlug($slug);
    }

    public function findBySlugWithIncludes(string $slug): ?Course
    {
        return $this->finder->findBySlugWithIncludes($slug);
    }

    public function create(CreateCourseDTO|array $data, ?\Modules\Auth\Models\User $actor = null, array $files = []): Course
    {
        return $this->lifecycleProcessor->create($data, $actor, $files);
    }

    public function update(int $id, UpdateCourseDTO|array $data, array $files = []): Course
    {
        $course = $this->findOrFail($id);

        return $this->lifecycleProcessor->update($course, $data, $files);
    }

    public function delete(int $id): bool
    {
        $course = $this->findOrFail($id);

        return $this->lifecycleProcessor->delete($course);
    }

    public function publish(int $id): Course
    {
        $course = $this->findOrFail($id);

        return $this->publicationProcessor->publish($course);
    }

    public function unpublish(int $id): Course
    {
        $course = $this->findOrFail($id);

        return $this->publicationProcessor->unpublish($course);
    }

    public function updateEnrollmentSettings(int $id, array $data): array
    {
        $course = $this->findOrFail($id);

        return $this->lifecycleProcessor->updateEnrollmentSettings($course, $data);
    }

    public function uploadThumbnail(int $id, \Illuminate\Http\UploadedFile $file): Course
    {
        $course = $this->findOrFail($id);

        return $this->lifecycleProcessor->uploadThumbnail($course, $file);
    }

    public function uploadBanner(int $id, \Illuminate\Http\UploadedFile $file): Course
    {
        $course = $this->findOrFail($id);

        return $this->lifecycleProcessor->uploadBanner($course, $file);
    }

    public function deleteThumbnail(int $id): Course
    {
        $course = $this->findOrFail($id);

        return $this->lifecycleProcessor->deleteThumbnail($course);
    }

    public function deleteBanner(int $id): Course
    {
        $course = $this->findOrFail($id);

        return $this->lifecycleProcessor->deleteBanner($course);
    }

    public function verifyEnrollmentKey(Course $course, string $plainKey): bool
    {
        if (empty($course->enrollment_key_hash)) {
            return false;
        }

        $hasher = app(\App\Contracts\EnrollmentKeyHasherInterface::class);

        return $hasher->verify($plainKey, $course->enrollment_key_hash);
    }

    public function generateEnrollmentKey(int $length = 12): string
    {
        return $this->lifecycleProcessor->generateEnrollmentKey($length);
    }

    public function hasEnrollmentKey(Course $course): bool
    {
        return ! empty($course->enrollment_key_hash);
    }

    public function searchGlobal(string $query, int $limit = 5): \Illuminate\Support\Collection
    {
        return $this->finder->searchGlobal($query, $limit);
    }

    public function listEnrolledCourses(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->finder->listEnrolledCourses($userId, $filters, $perPage);
    }

    public function findWithInstructors(int $id): ?Course
    {
        $course = $this->finder->find($id);

        if ($course) {
            $course->load('instructors');
        }

        return $course;
    }

    public function generateUniqueSlug(string $title): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Course::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
