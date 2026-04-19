<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Events\SubmissionStateChanged;
use Modules\Learning\Models\Submission;

class AwardXpForAssignmentSubmitted implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $maxExceptions = 2;

    public array $backoff = [5, 30, 120];

    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(SubmissionStateChanged $event): void
    {
        
        if ($event->newState !== SubmissionState::Graded) {
            return;
        }

        $submission = $event->submission->fresh(['assignment', 'user', 'grade']);
        $userId = $submission->user_id;
        $assignmentId = $submission->assignment_id;

        if (! $submission || ! $submission->assignment) {
            return;
        }

        
        $passingGrade = $submission->assignment->passing_grade;
        $score = $submission->score ?? 0;

        if ($score < $passingGrade) {
            
            return;
        }

        
        $existingXp = \Modules\Gamification\Models\Point::where('user_id', $userId)
            ->where('reason', 'assignment_submitted')
            ->where('source_type', 'assignment')
            ->where('source_id', $assignmentId)
            ->exists();

        if ($existingXp) {
            
            \Log::info("User {$userId} already received XP for assignment {$assignmentId}, skipping XP award");

            return;
        }

        
        $this->gamification->awardXp(
            $userId,
            0, 
            'assignment_submitted',
            'assignment',
            $assignmentId,
            [
                'description' => sprintf('Submitted assignment: %s', $submission->assignment->title),
            ]
        );

        
        $isFirst = $this->checkIfFirstSubmission($assignmentId, $userId);
        if ($isFirst) {
            $this->gamification->awardXp(
                $userId,
                0, 
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
                'score' => $score,
                'passing_grade' => $passingGrade,
            ]
        );

        
        $this->counterService->increment($userId, 'assignment_submitted', 'global', null, 'lifetime');
        $this->counterService->increment($userId, 'assignment_submitted', 'global', null, 'daily');
        $this->counterService->increment($userId, 'assignment_submitted', 'global', null, 'weekly');

        
        $user = \Modules\Auth\Models\User::find($userId);
        if ($user) {
            $payload = [
                'assignment_id' => $assignmentId,
                'submission_id' => $submission->id,
                'is_first' => $isFirst,
                'is_first_submission' => $isFirst,
                'score' => $score,
                'time' => now()->format('H:i:s'),
            ];
            $this->evaluator->evaluate($user, 'assignment_submitted', $payload);
        }
    }

    private function checkIfFirstSubmission(int $assignmentId, int $userId): bool
    {
        $firstSubmission = Submission::where('assignment_id', $assignmentId)
            ->where('status', 'graded')
            ->orderBy('created_at')
            ->first();

        return $firstSubmission && $firstSubmission->user_id === $userId;
    }
}
