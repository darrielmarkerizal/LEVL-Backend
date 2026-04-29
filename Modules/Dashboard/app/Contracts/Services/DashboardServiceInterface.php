<?php

declare(strict_types=1);

namespace Modules\Dashboard\Contracts\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Auth\Models\User;

interface DashboardServiceInterface
{
    public function getDashboardData(User $user): array;

    public function getRecommendedCourseIds(int $userId, int $limit): Collection;

    public function getRecommendedCourses(int $userId, int $limit, ?Request $request = null): Collection;
}
