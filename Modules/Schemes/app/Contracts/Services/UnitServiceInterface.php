<?php

declare(strict_types=1);

namespace Modules\Schemes\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Repositories\UnitRepository;

interface UnitServiceInterface
{
    
    public function listByCourse(int $courseId, array $params): LengthAwarePaginator;

    
    public function show(int $courseId, int $id): ?Unit;

    
    public function create(int $courseId, array $data): Unit;

    
    public function update(int $courseId, int $id, array $data): ?Unit;

    
    public function delete(int $courseId, int $id): bool;

    
    public function reorder(int $courseId, array $unitOrders): bool;

    
    public function markCompleted(Unit $unit, int $userId, int $enrollmentId): void;

    
    public function publish(int $courseId, int $id): ?Unit;

    
    public function unpublish(int $courseId, int $id): ?Unit;

    
    public function getRepository(): UnitRepository;
}
