<?php

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Schemes\Contracts\Services\UnitServiceInterface;
use Modules\Schemes\Events\UnitCompleted;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Repositories\UnitRepository;

class UnitService implements UnitServiceInterface
{
    public function __construct(private UnitRepository $repository) {}

    public function listByCourse(int $courseId, array $params): LengthAwarePaginator
    {
        return $this->repository->findByCourse($courseId, $params);
    }

    public function show(int $courseId, int $id): ?Unit
    {
        return $this->repository->findByCourseAndId($courseId, $id);
    }

    public function create(int $courseId, array $data): Unit
    {
        $data['course_id'] = $courseId;

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($courseId, $data['title'] ?? $data['code'] ?? Str::random(8));
        } else {
            $data['slug'] = $this->generateUniqueSlug($courseId, $data['slug']);
        }

        if (empty($data['order'])) {
            $maxOrder = $this->repository->getMaxOrderForCourse($courseId);
            $data['order'] = $maxOrder + 1;
        }

        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }

        return $this->repository->create($data);
    }

    public function update(int $courseId, int $id, array $data): ?Unit
    {
        $unit = $this->repository->findByCourseAndId($courseId, $id);
        if (! $unit) {
            return null;
        }

        if (! empty($data['slug']) && $data['slug'] !== $unit->slug) {
            $data['slug'] = $this->generateUniqueSlug($courseId, $data['slug'], $unit->id);
        }

        return $this->repository->update($unit, $data);
    }

    public function delete(int $courseId, int $id): bool
    {
        $unit = $this->repository->findByCourseAndId($courseId, $id);
        if (! $unit) {
            return false;
        }

        return $this->repository->delete($unit);
    }

    public function reorder(int $courseId, array $unitOrders): bool
    {
        $this->repository->reorderUnits($courseId, $unitOrders);

        return true;
    }

    public function markCompleted(Unit $unit, int $userId, int $enrollmentId): void
    {
        UnitCompleted::dispatch($unit, $userId, $enrollmentId);
    }

    public function publish(int $courseId, int $id): ?Unit
    {
        $unit = $this->repository->findByCourseAndId($courseId, $id);
        if (! $unit) {
            return null;
        }

        $unit->update([
            'status' => 'published',
        ]);

        return $unit->fresh();
    }

    public function unpublish(int $courseId, int $id): ?Unit
    {
        $unit = $this->repository->findByCourseAndId($courseId, $id);
        if (! $unit) {
            return null;
        }

        $unit->update([
            'status' => 'draft',
        ]);

        return $unit->fresh();
    }

    public function getRepository(): UnitRepository
    {
        return $this->repository;
    }

    private function generateUniqueSlug(int $courseId, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $slug = $base !== '' ? $base : Str::random(8);
        $suffix = 0;
        do {
            $candidate = $suffix > 0 ? $slug.'-'.$suffix : $slug;
            $existsQuery = Unit::where('course_id', $courseId)
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
