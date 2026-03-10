<?php

declare(strict_types=1);

namespace Modules\Trash\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;

interface TrashBinManagementServiceInterface
{
    public function paginate(User $actor, array $params): LengthAwarePaginator;

    public function restore(User $actor, int $trashBinId): array;

    public function restoreAll(User $actor, ?string $resourceType = null): array;

    public function bulkRestore(User $actor, array $ids): array;

    public function forceDelete(User $actor, int $trashBinId): array;

    public function forceDeleteAll(User $actor, ?string $resourceType = null): array;

    public function bulkForceDelete(User $actor, array $ids): array;

    public function getSourceTypes(User $actor): array;

    public function getMasterSourceTypes(): array;
}
