<?php

declare(strict_types=1);


namespace Modules\Auth\Contracts\Repositories;

use Illuminate\Support\Collection;

interface UserBulkRepositoryInterface
{
    public function findByIds(array $userIds): Collection;

    public function bulkDeactivate(array $userIds, int $changedBy, int $currentUserId): int;

    public function findById(int $userId): ?\Modules\Auth\Models\User;

    public function bulkDelete(array $userIds, int $currentUserId): int;
}
