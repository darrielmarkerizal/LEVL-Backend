<?php

declare(strict_types=1);


namespace Modules\Auth\Contracts\Services;

interface UserBulkServiceInterface
{
    public function export(\Modules\Auth\Models\User $authUser, array $data): void;

    public function bulkActivate(array $userIds, int $changedBy): int;

    public function bulkDeactivate(array $userIds, int $changedBy, int $currentUserId): int;

    public function bulkDelete(array $userIds, int $currentUserId): int;
}
