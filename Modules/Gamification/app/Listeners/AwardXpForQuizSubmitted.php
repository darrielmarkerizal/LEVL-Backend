<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;
use Modules\Learning\Events\QuizSubmitted;

class AwardXpForQuizSubmitted extends GamificationListener
{

    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(QuizSubmitted $event): void
    {
        $submission = $event->submission->fresh(['quiz']);
        $userId = $submission->user_id;
        $quizId = $submission->quiz_id;

        if (! $submission || ! $submission->quiz) {
            return;
        }

        if ((int) ($submission->attempt_number ?? 0) !== 1) {
            return;
        }

        $existingXp = Point::where('user_id', $userId)
            ->where('reason', 'quiz_submitted')
            ->where('source_type', 'quiz')
            ->where('source_id', $quizId)
            ->exists();

        if ($existingXp) {
            \Log::info("User {$userId} already received XP for quiz {$quizId} submission, skipping");

            return;
        }

        $xpAmount = (int) SystemSetting::get('gamification.points.quiz_submitted', 10);

        $this->gamification->awardXp(
            $userId,
            $xpAmount,
            'quiz_submitted',
            'quiz',
            $quizId,
            [
                'description' => sprintf('Submitted quiz: %s', $submission->quiz->title),
                'metadata' => [
                    'attempt_number' => $submission->attempt_number,
                    'time_spent_seconds' => $submission->time_spent_seconds,
                ],
            ]
        );

        $this->loggerService->log(
            $userId,
            'quiz_submitted',
            'quiz',
            $quizId,
            [
                'quiz_id' => $quizId,
                'submission_id' => $submission->id,
                'attempt_number' => $submission->attempt_number,
            ]
        );

        $this->counterService->incrementGlobal($userId, 'quiz_submitted');

        $user = $this->getCachedUser($userId);
        if ($user) {
            $payload = [
                'quiz_id' => $quizId,
                'submission_id' => $submission->id,
                'attempt_number' => $submission->attempt_number,
            ];
            $this->evaluator->evaluate($user, 'quiz_submitted', $payload);
        }
    }
}
