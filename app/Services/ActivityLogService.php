<?php

namespace App\Services;

use App\Repositories\ActivityLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function __construct(
        private ActivityLogRepository $repository
    ) {}

    /**
     * Get paginated activity logs.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get single activity log by ID.
     */
    public function find(int $id): ?Activity
    {
        return $this->repository->find($id);
    }
}
