<?php

declare(strict_types=1);

namespace Modules\Schemes\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Schemes\Models\Lesson;

interface LessonRepositoryInterface
{
    
    public function findByUnit(int $unitId, array $params = [], int $perPage = 15): LengthAwarePaginator;

    
    public function findByUnitAndId(int $unitId, int $id): ?Lesson;

    
    public function getMaxOrderForUnit(int $unitId): int;

    
    public function getAllByUnit(int $unitId): Collection;
}
