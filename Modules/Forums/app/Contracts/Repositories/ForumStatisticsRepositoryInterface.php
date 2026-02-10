<?php

declare(strict_types=1);

namespace Modules\Forums\Contracts\Repositories;

use Carbon\Carbon;
use Modules\Forums\Models\ForumStatistic;

interface ForumStatisticsRepositoryInterface
{
    public function getSchemeStatistics(int $schemeId, Carbon $periodStart, Carbon $periodEnd): ?ForumStatistic;

    public function getUserStatistics(int $schemeId, int $userId, Carbon $periodStart, Carbon $periodEnd): ?ForumStatistic;

    public function updateSchemeStatistics(int $schemeId, Carbon $periodStart, Carbon $periodEnd): ForumStatistic;

    public function updateUserStatistics(int $schemeId, int $userId, Carbon $periodStart, Carbon $periodEnd): ForumStatistic;
}
