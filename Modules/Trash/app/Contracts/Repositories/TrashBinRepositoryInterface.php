<?php

declare(strict_types=1);

namespace Modules\Trash\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Trash\Models\TrashBin;

interface TrashBinRepositoryInterface
{
    public function paginateForAccess(int $actorId, bool $isSuperadmin, array $accessibleCourseIds, array $params): LengthAwarePaginator;

    public function findByIdOrFail(int $id): TrashBin;

    public function findManyByIds(array $ids): Collection;

    public function getSourceTypes(): array;

    public function getSourceTypesForAccess(int $actorId, array $accessibleCourseIds): array;
}
