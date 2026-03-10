<?php

declare(strict_types=1);

namespace Modules\Trash\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;

interface TrashBinManagementServiceInterface
{
    public function paginate(User $actor, array $params): LengthAwarePaginator;

    public function restore(User $actor, int $trashBinId): void;

    public function restoreAll(User $actor, ?string $resourceType = null): int;

    public function bulkRestore(User $actor, array $ids): int;

    public function forceDelete(User $actor, int $trashBinId): void;

    public function forceDeleteAll(User $actor, ?string $resourceType = null): int;

    public function bulkForceDelete(User $actor, array $ids): int;

    public function getSourceTypes(User $actor): array;

    public function getMasterSourceTypes(): array;
}
