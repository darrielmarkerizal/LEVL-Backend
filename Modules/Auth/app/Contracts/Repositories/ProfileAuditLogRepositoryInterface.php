<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\ProfileAuditLog;

interface ProfileAuditLogRepositoryInterface
{
    
    public function create(array $data): ProfileAuditLog;

    
    public function findByUserId(int $userId, int $perPage = 20): LengthAwarePaginator;
}
