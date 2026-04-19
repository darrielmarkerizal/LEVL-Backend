<?php

declare(strict_types=1);

namespace Modules\Trash\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;
use Modules\Trash\Contracts\Repositories\TrashBinRepositoryInterface;
use Modules\Trash\Contracts\Services\TrashBinManagementServiceInterface;
use Modules\Trash\Jobs\ForceDeleteTrashBinJob;
use Modules\Trash\Models\TrashBin;

class TrashBinManagementService implements TrashBinManagementServiceInterface
{
    public function __construct(
        private readonly TrashBinRepositoryInterface $repository,
        private readonly TrashBinService $trashService,
    ) {}

    public function paginate(User $actor, array $params): LengthAwarePaginator
    {
        [$isFullAccess, $courseIds] = $this->resolveAccessContext($actor);

        return $this->repository->paginateForAccess($actor->id, $isFullAccess, $courseIds, $params);
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
        $this->assertHasTrashRole($actor);

        [$isFullAccess, $courseIds] = $this->resolveAccessContext($actor);

        
        $bins = $this->repository->getAllForAccess(
            $isFullAccess ? null : $actor->id,
            $isFullAccess,
            $courseIds,
            $resourceType
        );

        
        foreach ($bins as $bin) {
            $this->trashService->restoreFromTrashBin($bin);
        }

        return [
            'queued' => false,
            'resource_type' => $resourceType,
            'count' => $bins->count(),
        ];
    }

    public function bulkRestore(User $actor, array $ids): array
    {
        $bins = $this->repository->findManyByIds($ids);

        foreach ($bins as $bin) {
            $this->assertCanAccessBin($actor, $bin);
        }

        
        foreach ($bins as $bin) {
            $this->trashService->restoreFromTrashBin($bin);
        }

        return [
            'queued' => false,
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
        $this->assertHasTrashRole($actor);

        [$isFullAccess, $courseIds] = $this->resolveAccessContext($actor);

        
        $bins = $this->repository->getAllForAccess(
            $isFullAccess ? null : $actor->id,
            $isFullAccess,
            $courseIds,
            $resourceType
        );

        
        foreach ($bins as $bin) {
            $this->trashService->forceDeleteFromTrashBin($bin);
        }

        return [
            'queued' => false,
            'resource_type' => $resourceType,
            'count' => $bins->count(),
        ];
    }

    public function bulkForceDelete(User $actor, array $ids): array
    {
        $bins = $this->repository->findManyByIds($ids);

        foreach ($bins as $bin) {
            $this->assertCanAccessBin($actor, $bin);
        }

        
        foreach ($bins as $bin) {
            $this->trashService->forceDeleteFromTrashBin($bin);
        }

        return [
            'queued' => false,
            'ids' => $bins->pluck('id')->values()->all(),
            'count' => $bins->count(),
        ];
    }

    public function getSourceTypes(User $actor): array
    {
        [$isFullAccess, $courseIds] = $this->resolveAccessContext($actor);

        if ($isFullAccess) {
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

    private function assertHasTrashRole(User $actor): void
    {
        if (! $this->hasTrashAccessRole($actor)) {
            throw new AuthorizationException(__('messages.forbidden'));
        }
    }

    private function assertCanAccessBin(User $actor, TrashBin $bin): void
    {
        if (! $this->hasTrashAccessRole($actor)) {
            throw new AuthorizationException(__('messages.forbidden'));
        }

        
        if ($actor->hasRole('Superadmin') || $actor->hasRole('Admin')) {
            return;
        }

        
        if (
            (int) $bin->deleted_by === (int) $actor->id
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
        
        if (! $actor->hasRole('Instructor')) {
            return false;
        }

        $courseId = $this->resolveCourseIdFromBin($bin);
        if ($courseId === null) {
            return false;
        }

        return Course::query()
            ->where('id', $courseId)
            ->where(function ($q) use ($actor): void {
                $q->where('instructor_id', $actor->id)
                    ->orWhereHas('managers', fn ($m) => $m->where('users.id', $actor->id));
            })
            ->exists();
    }

    private function resolveAccessContext(User $actor): array
    {
        
        if ($actor->hasRole('Superadmin') || $actor->hasRole('Admin')) {
            return [true, []];
        }

        
        $managedCourseIds = $actor->managedCourses()->pluck('courses.id')->toArray();
        $instructorCourseIds = Course::query()->where('instructor_id', $actor->id)->pluck('id')->toArray();
        $courseIds = array_values(array_unique(array_map('intval', array_merge($managedCourseIds, $instructorCourseIds))));

        return [false, $courseIds];
    }

    private function resolveCourseIdFromBin(TrashBin $bin): ?int
    {
        $metaCourseId = data_get($bin->metadata, 'course_id');
        if ($metaCourseId !== null && is_numeric($metaCourseId)) {
            return (int) $metaCourseId;
        }

        return null;
    }
}
