<?php

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Schemes\Events\LessonCompleted;
use Modules\Schemes\Events\LessonViewed;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Repositories\LessonRepository;

class LessonService
{
    public function __construct(private LessonRepository $repository) {}

    public function listByUnit(int $unitId, array $params): LengthAwarePaginator
    {
        return $this->repository->findByUnit($unitId, $params);
    }

    public function show(int $unitId, int $id): ?Lesson
    {
        return $this->repository->findByUnitAndId($unitId, $id);
    }

    public function create(int $unitId, array $data): Lesson
    {
        $data['unit_id'] = $unitId;

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($unitId, $data['title'] ?? Str::random(8));
        } else {
            $data['slug'] = $this->generateUniqueSlug($unitId, $data['slug']);
        }

        if (empty($data['order'])) {
            $maxOrder = $this->repository->getMaxOrderForUnit($unitId);
            $data['order'] = $maxOrder + 1;
        }

        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }

        return $this->repository->create($data);
    }

    public function update(int $unitId, int $id, array $data): ?Lesson
    {
        $lesson = $this->repository->findByUnitAndId($unitId, $id);
        if (! $lesson) {
            return null;
        }

        if (! empty($data['slug']) && $data['slug'] !== $lesson->slug) {
            $data['slug'] = $this->generateUniqueSlug($unitId, $data['slug'], $lesson->id);
        }

        return $this->repository->update($lesson, $data);
    }

    public function delete(int $unitId, int $id): bool
    {
        $lesson = $this->repository->findByUnitAndId($unitId, $id);
        if (! $lesson) {
            return false;
        }

        return $this->repository->delete($lesson);
    }

    public function publish(int $unitId, int $id): ?Lesson
    {
        $lesson = $this->repository->findByUnitAndId($unitId, $id);
        if (! $lesson) {
            return null;
        }

        $lesson->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $lesson->fresh();
    }

    public function unpublish(int $unitId, int $id): ?Lesson
    {
        $lesson = $this->repository->findByUnitAndId($unitId, $id);
        if (! $lesson) {
            return null;
        }

        $lesson->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return $lesson->fresh();
    }

    public function markViewed(Lesson $lesson, int $userId, int $enrollmentId): void
    {
        LessonViewed::dispatch($lesson, $userId, $enrollmentId);
    }

    public function markCompleted(Lesson $lesson, int $userId, int $enrollmentId): void
    {
        LessonCompleted::dispatch($lesson, $userId, $enrollmentId);
    }

    public function getRepository(): LessonRepository
    {
        return $this->repository;
    }

    private function generateUniqueSlug(int $unitId, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $slug = $base !== '' ? $base : Str::random(8);
        $suffix = 0;
        do {
            $candidate = $suffix > 0 ? $slug.'-'.$suffix : $slug;
            $existsQuery = Lesson::where('unit_id', $unitId)
                ->where('slug', $candidate);
            if ($ignoreId) {
                $existsQuery->where('id', '!=', $ignoreId);
            }
            $exists = $existsQuery->exists();
            if (! $exists) {
                return $candidate;
            }
            $suffix++;
        } while (true);
    }
}
