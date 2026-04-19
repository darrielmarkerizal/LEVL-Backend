<?php

declare(strict_types=1);

namespace Modules\Schemes\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Models\Unit;

interface UnitRepositoryInterface
{
    
    public function findByCourse(int $courseId, array $params = [], int $perPage = 15): LengthAwarePaginator;

    
    public function findByCourseAndId(int $courseId, int $id): ?Unit;

    
    public function getMaxOrderForCourse(int $courseId): int;

    
    public function reorderUnits(int $courseId, array $unitOrders): void;

    
    public function getAllByCourse(int $courseId): Collection;

    
    public function updateOrder(int $unitId, int $order): bool;
}
