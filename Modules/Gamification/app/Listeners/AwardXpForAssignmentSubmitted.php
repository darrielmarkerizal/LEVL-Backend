<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Events\SubmissionStateChanged;
use Modules\Learning\Models\Submission;

class AwardXpForAssignmentSubmitted extends GamificationListener
{

    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(SubmissionStateChanged $event): void
    {
        $submissionStates = [SubmissionState::AutoGraded, SubmissionState::PendingManualGrading];
        if (! in_array($event->newState, $submissionStates, true)) {
            return;
        }

        $submission = $event->submission->fresh(['assignment', 'user']);
        $userId = $submission->user_id;
        $assignmentId = $submission->assignment_id;

        if (! $submission || ! $submission->assignment) {
            return;
        }

        if ((int) ($submission->attempt_number ?? 0) !== 1) {
            return;
        }

        $existingXp = Point::where('user_id', $userId)
            ->where('reason', 'assignment_submitted')
            ->where('source_type', 'assignment')
            ->where('source_id', $assignmentId)
            ->exists();

        if (! $existingXp) {
            $xpSubmitted = (int) SystemSetting::get('gamification.points.assignment_submitted', 5);

            $this->gamification->awardXp(
                $userId,
                $xpSubmitted,
                'assignment_submitted',
                'assignment',
                $assignmentId,
                [
                    'description' => sprintf('Submitted assignment: %s', $submission->assignment->title),
                ]
            );

            $isFirst = $this->checkIfFirstSubmission($assignmentId, $userId);
            if ($isFirst) {
                $xpFirst = (int) SystemSetting::get('gamification.points.first_submission', 10);

                $this->gamification->awardXp(
                    $userId,
                    $xpFirst,
                    'first_submission',
                    'assignment',
                    $assignmentId,
                    [
                        'description' => 'First to submit this assignment!',
                    ]
                );
            }

            $this->loggerService->log(
                $userId,
                'assignment_submitted',
                'assignment',
                $assignmentId,
                [
                    'assignment_id' => $assignmentId,
                    'submission_id' => $submission->id,
                    'is_first' => $isFirst,
                    'score' => $submission->score,
                ]
            );

            $this->counterService->incrementGlobal($userId, 'assignment_submitted');

            $user = $this->getCachedUser($userId);
            if ($user) {
                $payload = [
                    'assignment_id' => $assignmentId,
                    'submission_id' => $submission->id,
                    'is_first' => $isFirst,
                    'is_first_submission' => $isFirst,
                    'score' => $submission->score,
                    'time' => now()->format('H:i:s'),
                ];
                $this->evaluator->evaluate($user, 'assignment_submitted', $payload);
            }
        }

        if ($event->newState === SubmissionState::AutoGraded) {
            $score = (float) ($submission->score ?? 0);
            $passingGrade = (float) $submission->assignment->passing_grade;

            if ($score >= $passingGrade) {
                $xpCompleted = (int) SystemSetting::get('gamification.points.assignment_completed', 20);

                $this->gamification->awardXp(
                    $userId,
                    $xpCompleted,
                    'assignment_completed',
                    'assignment',
                    $assignmentId,
                    [
                        'description' => sprintf('Passed auto-graded assignment: %s', $submission->assignment->title),
                        'allow_multiple' => false,
                    ]
                );
            }
        }
    }

    private function checkIfFirstSubmission(int $assignmentId, int $userId): bool
    {
        $firstSubmission = Submission::where('assignment_id', $assignmentId)
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at')
            ->first();

        return $firstSubmission && $firstSubmission->user_id === $userId;
    }
}
