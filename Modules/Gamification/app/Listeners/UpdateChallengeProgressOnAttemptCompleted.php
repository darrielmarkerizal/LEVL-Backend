<?php

namespace Modules\Gamification\Listeners;

use Modules\Assessments\Events\AttemptCompleted;
use Modules\Gamification\Enums\ChallengeCriteriaType;
use Modules\Gamification\Services\ChallengeService;

class UpdateChallengeProgressOnAttemptCompleted
{
    public function __construct(private ChallengeService $challengeService) {}

    public function handle(AttemptCompleted $event): void
    {
        $attempt = $event->attempt;

        if (! $attempt) {
            return;
        }

        $this->challengeService->checkAndUpdateProgress(
            $attempt->user_id,
            ChallengeCriteriaType::ExercisesCompleted->value,
            1
        );
    }
}
