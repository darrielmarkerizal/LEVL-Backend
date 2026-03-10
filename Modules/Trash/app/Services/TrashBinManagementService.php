<?php

declare(strict_types=1);

namespace Modules\Trash\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;
use Modules\Trash\Jobs\BulkForceDeleteTrashBinsJob;
use Modules\Trash\Jobs\BulkRestoreTrashBinsJob;
use Modules\Trash\Jobs\ForceDeleteAllTrashBinsJob;
use Modules\Trash\Jobs\ForceDeleteTrashBinJob;
use Modules\Trash\Jobs\RestoreAllTrashBinsJob;
use Modules\Trash\Contracts\Repositories\TrashBinRepositoryInterface;
use Modules\Trash\Contracts\Services\TrashBinManagementServiceInterface;
use Modules\Trash\Models\TrashBin;

class TrashBinManagementService implements TrashBinManagementServiceInterface
{
    public function __construct(
        private readonly TrashBinRepositoryInterface $repository,
        private readonly TrashBinService $trashService,
    ) {}

    public function paginate(User $actor, array $params): LengthAwarePaginator
    {
        [$isSuperadmin, $courseIds] = $this->resolveAccessContext($actor);

        return $this->repository->paginateForAccess($actor->id, $isSuperadmin, $courseIds, $params);
    }

    public function restore(User $actor, int $trashBinId): array
    {
        $bin = $this->repository->findByIdOrFail($trashBinId);
        $this->assertCanAccessBin($actor, $bin);

        $this->trashService->restoreFromTrashBin($bin);

        return [
            'queued' => false,
        ];
    }

    public function restoreAll(User $actor, ?string $resourceType = null): array
    {
        $this->assertSuperadmin($actor);

        RestoreAllTrashBinsJob::dispatch($resourceType, $actor->id);

        return [
            'queued' => true,
            'resource_type' => $resourceType,
        ];
    }

    public function bulkRestore(User $actor, array $ids): array
    {
        $bins = $this->repository->findManyByIds($ids);

        foreach ($bins as $bin) {
            $this->assertCanAccessBin($actor, $bin);
        }

        BulkRestoreTrashBinsJob::dispatch($bins->pluck('id')->values()->all(), $actor->id);

        return [
            'queued' => true,
            'ids' => $bins->pluck('id')->values()->all(),
            'count' => $bins->count(),
        ];
    }

    public function forceDelete(User $actor, int $trashBinId): array
    {
        $bin = $this->repository->findByIdOrFail($trashBinId);
        $this->assertCanAccessBin($actor, $bin);

        $groupCount = $this->repository->countByGroupUuid($bin->group_uuid);
        if ($this->trashService->shouldRunAsyncCascade($bin, $groupCount)) {

            ForceDeleteTrashBinJob::dispatch($bin->id, $actor->id);

            return [
                'queued' => true,
                'trash_bin_id' => $bin->id,
                'group_uuid' => $bin->group_uuid,
                'group_items' => $groupCount,
                'resource_type' => $bin->resource_type,
            ];
        }

        $this->trashService->forceDeleteFromTrashBin($bin);

        return [
            'queued' => false,
        ];
    }

    public function forceDeleteAll(User $actor, ?string $resourceType = null): array
    {
        $this->assertSuperadmin($actor);

        ForceDeleteAllTrashBinsJob::dispatch($resourceType, $actor->id);

        return [
            'queued' => true,
            'resource_type' => $resourceType,
        ];
    }

    public function bulkForceDelete(User $actor, array $ids): array
    {
        $bins = $this->repository->findManyByIds($ids);

        foreach ($bins as $bin) {
            $this->assertCanAccessBin($actor, $bin);
        }

        BulkForceDeleteTrashBinsJob::dispatch($bins->pluck('id')->values()->all(), $actor->id);

        return [
            'queued' => true,
            'ids' => $bins->pluck('id')->values()->all(),
            'count' => $bins->count(),
        ];
    }

    public function getSourceTypes(User $actor): array
    {
        [$isSuperadmin, $courseIds] = $this->resolveAccessContext($actor);

        if ($isSuperadmin) {
            return $this->trashService->toSourceTypeOptions($this->repository->getSourceTypes());
        }

        return $this->trashService->toSourceTypeOptions(
            $this->repository->getSourceTypesForAccess($actor->id, $courseIds)
        );
    }

    public function getMasterSourceTypes(): array
    {
        return $this->trashService->getSupportedResourceTypeOptions();
    }

    private function assertSuperadmin(User $actor): void
    {
        if (! $actor->hasRole('Superadmin')) {
            throw new AuthorizationException(__('messages.forbidden'));
        }
    }

    private function assertCanAccessBin(User $actor, TrashBin $bin): void
    {
        if (! $this->hasTrashAccessRole($actor)) {
            throw new AuthorizationException(__('messages.forbidden'));
        }

        if (
            $actor->hasRole('Superadmin')
            || (int) $bin->deleted_by === (int) $actor->id
            || $this->canManageBinCourse($actor, $bin)
        ) {
            return;
        }

        throw new AuthorizationException(__('messages.forbidden'));
    }

    private function hasTrashAccessRole(User $actor): bool
    {
        return $actor->hasRole('Superadmin')
            || $actor->hasRole('Admin')
            || $actor->hasRole('Instructor');
    }

    private function canManageBinCourse(User $actor, TrashBin $bin): bool
    {
        if (! ($actor->hasRole('Admin') || $actor->hasRole('Instructor'))) {
            return false;
        }

        $courseId = $this->resolveCourseId($bin);
        if ($courseId === null) {
            return false;
        }

        $isManaged = $actor->managedCourses()->where('courses.id', $courseId)->exists();
        $isInstructor = Course::query()->where('id', $courseId)->where('instructor_id', $actor->id)->exists();

        return $isManaged || $isInstructor;
    }

    private function resolveAccessContext(User $actor): array
    {
        $isSuperadmin = $actor->hasRole('Superadmin');
        if ($isSuperadmin) {
            return [true, []];
        }

        $managedCourseIds = $actor->managedCourses()->pluck('courses.id')->toArray();
        $instructorCourseIds = Course::query()->where('instructor_id', $actor->id)->pluck('id')->toArray();
        $courseIds = array_values(array_unique(array_map('intval', array_merge($managedCourseIds, $instructorCourseIds))));

        return [false, $courseIds];
    }

    private function resolveCourseId(TrashBin $bin): ?int
    {
        $metaCourseId = data_get($bin->metadata, 'course_id');
        if ($metaCourseId !== null && is_numeric($metaCourseId)) {
            return (int) $metaCourseId;
        }

        $class = $bin->trashable_type;
        if (! class_exists($class)) {
            return null;
        }

        $query = method_exists($class, 'withTrashed') ? $class::withTrashed() : $class::query();
        $model = $query->find($bin->trashable_id);

        if (! $model) {
            return null;
        }

        if ($model instanceof \Modules\Schemes\Models\Course) {
            return (int) $model->id;
        }

        if ($model instanceof \Modules\Schemes\Models\Unit) {
            return (int) $model->course_id;
        }

        if ($model instanceof \Modules\Schemes\Models\Lesson) {
            return $model->unit ? (int) $model->unit->course_id : null;
        }

        if (method_exists($model, 'getCourseId')) {
            $courseId = $model->getCourseId();

            return $courseId !== null ? (int) $courseId : null;
        }

        return null;
    }
}
