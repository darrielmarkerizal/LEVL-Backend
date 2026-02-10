<?php

declare(strict_types=1);

namespace Modules\Forums\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Auth\Models\User;
use Modules\Forums\Contracts\Repositories\ForumStatisticsRepositoryInterface;
use Modules\Forums\Models\ForumStatistic;
use Carbon\Carbon;

class ForumStatisticsService
{
    public function __construct(
        private readonly ForumStatisticsRepositoryInterface $repository,
    ) {}

    public function getStatistics(int $courseId, ?int $userId, Carbon $periodStart, Carbon $periodEnd): ForumStatistic
    {
        $cacheKey = $this->cacheKey($courseId, $userId, $periodStart, $periodEnd);
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($courseId, $userId, $periodStart, $periodEnd) {
            if ($userId) {
                $statistics = $this->repository->getUserStatistics($courseId, $userId, $periodStart, $periodEnd);
                return $statistics ?: $this->repository->updateUserStatistics($courseId, $userId, $periodStart, $periodEnd);
            }

            $statistics = $this->repository->getSchemeStatistics($courseId, $periodStart, $periodEnd);
            return $statistics ?: $this->repository->updateSchemeStatistics($courseId, $periodStart, $periodEnd);
        });
    }

    public function getUserStatistics(int $courseId, User $user, Carbon $periodStart, Carbon $periodEnd): ForumStatistic
    {
        $cacheKey = $this->cacheKey($courseId, $user->id, $periodStart, $periodEnd);
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($courseId, $user, $periodStart, $periodEnd) {
            $statistics = $this->repository->getUserStatistics($courseId, $user->id, $periodStart, $periodEnd);
            return $statistics ?: $this->repository->updateUserStatistics($courseId, $user->id, $periodStart, $periodEnd);
        });
    }

    public function parsePeriodFilters(array $filters): array
    {
        $periodStart = $filters['period_start'] ?? null;
        $periodEnd = $filters['period_end'] ?? null;

        $start = $periodStart ? Carbon::parse($periodStart) : Carbon::now()->startOfMonth();
        $end = $periodEnd ? Carbon::parse($periodEnd) : Carbon::now()->endOfMonth();

        return [$start, $end];
    }

    private function cacheKey(int $courseId, ?int $userId, Carbon $periodStart, Carbon $periodEnd): string
    {
        $userPart = $userId ? (string) $userId : 'all';
        return "forums:stats:course:{$courseId}:user:{$userPart}:{$periodStart->toDateString()}:{$periodEnd->toDateString()}";
    }
}
