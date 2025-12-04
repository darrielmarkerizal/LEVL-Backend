<?php

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Enums\ChallengeCriteriaType;
use Modules\Gamification\Services\ChallengeService;
use Modules\Schemes\Events\LessonCompleted;

class UpdateChallengeProgressOnLessonCompleted
{
    public function __construct(private ChallengeService $challengeService) {}

    public function handle(LessonCompleted $event): void
    {
        $this->challengeService->checkAndUpdateProgress(
            $event->userId,
            ChallengeCriteriaType::LessonsCompleted->value,
            1
        );
    }
}
