<?php

declare(strict_types=1);

namespace Modules\Dashboard\Services;

use Modules\Auth\Models\User;
use Modules\Dashboard\Contracts\Repositories\DashboardRepositoryInterface;
use Modules\Dashboard\Contracts\Services\DashboardServiceInterface;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repository
    ) {}

    public function getDashboardData(User $user): array
    {
        if ($user->hasRole('Student')) {
            return [
                'gamification_stats' => $this->repository->getStudentGamificationStats($user),
                'latest_learning_activity' => $this->repository->getLatestLearningActivity($user),
                'recent_achievements' => $this->repository->getRecentAchievements($user),
            ];
        }

        return [
            'pending_enrollment' => $this->repository->getPendingEnrollmentCount($user),
            'total_users' => $this->repository->getTotalUsersCount($user),
            'total_schemes' => $this->repository->getTotalSchemesCount($user),
            'registration_and_class_queue' => $this->repository->getRegistrationQueue($user),
            'learning_content_statistic' => $this->repository->getContentStatistics($user),
            'global_top_leaderboard' => $this->repository->getTopLeaderboard(),
        ];
    }
}
