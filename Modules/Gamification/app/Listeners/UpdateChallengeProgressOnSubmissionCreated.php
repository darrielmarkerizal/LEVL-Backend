<?php

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Enums\ChallengeCriteriaType;
use Modules\Gamification\Services\ChallengeService;
use Modules\Learning\Events\SubmissionCreated;

class UpdateChallengeProgressOnSubmissionCreated
{
    public function __construct(
        private ChallengeService $challengeService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

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

        $user = \Modules\Auth\Models\User::find($submission->user_id);
        if ($user) {
            $payload = [
                'assignment_id' => $submission->assignment_id,
                'is_first_submission' => $submission->attempt === 1,
                'time' => $submission->created_at->format('H:i:s'),
            ];
            $this->evaluator->evaluate($user, 'assignment_submitted', $payload);
        }
    }
}
