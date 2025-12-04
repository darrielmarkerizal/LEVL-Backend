<?php

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Enums\ChallengeCriteriaType;
use Modules\Gamification\Services\ChallengeService;
use Modules\Learning\Events\SubmissionCreated;

class UpdateChallengeProgressOnSubmissionCreated
{
    public function __construct(private ChallengeService $challengeService) {}

    public function handle(SubmissionCreated $event): void
    {
        $submission = $event->submission;

        if (! $submission) {
            return;
        }

        $this->challengeService->checkAndUpdateProgress(
            $submission->user_id,
            ChallengeCriteriaType::AssignmentsSubmitted->value,
            1
        );
    }
}
