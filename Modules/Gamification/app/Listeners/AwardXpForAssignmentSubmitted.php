<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Learning\Events\SubmissionCreated;
use Modules\Learning\Models\Submission;

class AwardXpForAssignmentSubmitted
{
    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(SubmissionCreated $event): void
    {
        $submission = $event->submission->fresh(['assignment', 'user']);
        $userId = $submission->user_id;
        $assignmentId = $submission->assignment_id;

        if (!$submission || !$submission->assignment) {
            return;
        }

        // 1. Award XP for assignment submission (100 XP from xp_sources)
        $this->gamification->awardXp(
            $userId,
            0, // Will use xp_sources config (assignment_submitted = 100 XP)
            'assignment_submitted',
            'assignment',
            $assignmentId,
            [
                'description' => sprintf('Submitted assignment: %s', $submission->assignment->title),
            ]
        );

        // 2. Check if first submission (First Blood badge)
        $isFirst = $this->checkIfFirstSubmission($assignmentId, $userId);
        if ($isFirst) {
            $this->gamification->awardXp(
                $userId,
                0, // Will use xp_sources config (first_submission = 30 XP)
                'first_submission',
                'assignment',
                $assignmentId,
                [
                    'description' => 'First to submit this assignment!',
                ]
            );
        }

        // 3. Log event
        $this->loggerService->log(
            $userId,
            'assignment_submitted',
            'assignment',
            $assignmentId,
            [
                'assignment_id' => $assignmentId,
                'submission_id' => $submission->id,
                'is_first' => $isFirst,
            ]
        );

        // 4. Increment counters
        $this->counterService->increment($userId, 'assignment_submitted', 'global', null, 'lifetime');
        $this->counterService->increment($userId, 'assignment_submitted', 'global', null, 'daily');
        $this->counterService->increment($userId, 'assignment_submitted', 'global', null, 'weekly');

        // 5. Evaluate Dynamic Badge Rules
        $user = \Modules\Auth\Models\User::find($userId);
        if ($user) {
            $payload = [
                'assignment_id' => $assignmentId,
                'submission_id' => $submission->id,
                'is_first' => $isFirst,
            ];
            $this->evaluator->evaluate($user, 'assignment_submitted', $payload);
        }
    }

    private function checkIfFirstSubmission(int $assignmentId, int $userId): bool
    {
        $firstSubmission = Submission::where('assignment_id', $assignmentId)
            ->orderBy('created_at')
            ->first();

        return $firstSubmission && $firstSubmission->user_id === $userId;
    }
}
