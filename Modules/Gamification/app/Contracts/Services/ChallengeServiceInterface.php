<?php

namespace Modules\Gamification\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Gamification\Models\Challenge;
use Modules\Gamification\Models\UserChallengeAssignment;

interface ChallengeServiceInterface
{
    public function getUserChallenges(int $userId): Collection;

    public function getActiveChallenge(int $challengeId): ?Challenge;

    public function getCompletedChallenges(int $userId, int $limit = 15): Collection;

    public function assignDailyChallenges(): int;

    public function assignWeeklyChallenges(): int;

    public function checkAndUpdateProgress(int $userId, string $criteriaType, int $count = 1): void;

    public function completeChallenge(UserChallengeAssignment $assignment): void;

    public function claimReward(int $userId, int $challengeId): array;

    public function expireOverdueChallenges(): int;
}
