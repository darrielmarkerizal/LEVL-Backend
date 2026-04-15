<?php

declare(strict_types=1);

namespace Modules\Dashboard\Contracts\Repositories;

use App\Contracts\BaseRepositoryInterface;
use Modules\Auth\Models\User;

interface DashboardRepositoryInterface extends BaseRepositoryInterface
{
    public function getPendingEnrollmentCount(User $user): int;

    public function getTotalUsersCount(User $user): int;

    public function getTotalSchemesCount(User $user): int;

    public function getRegistrationQueue(User $user, int $limit = 5): array;

    public function getContentStatistics(User $user): array;

    public function getTopLeaderboard(int $limit = 3): array;

    public function getStudentGamificationStats(User $user): array;

    public function getLatestLearningActivity(User $user): ?array;

    public function getRecentAchievements(User $user): array;

    public function getRecommendedCourses(User $user): array;

    public function getLatestPosts(int $limit = 5): array;
}
