<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Traits\CachesUsers;
use Modules\Learning\Events\QuizCompleted;

class AwardXpForQuizPassed implements ShouldQueue
{
    use CachesUsers;
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

    public function handle(QuizCompleted $event): void
    {
        $submission = $event->submission;
        $userId = $submission->user_id;
        $quizId = $submission->quiz_id;

        if (! $submission || ! $submission->quiz) {
            return;
        }

        if (! $submission->isPassed()) {
            return;
        }

        $finalScore = $submission->final_score ?? $submission->score;

        $this->gamification->awardXp(
            $userId,
            0,
            'quiz_passed',
            'quiz',
            $quizId,
            [
                'description' => sprintf('Passed quiz: %s (Score: %.2f)', $submission->quiz->title, $finalScore),
                'metadata' => [
                    'score' => $finalScore,
                    'passing_grade' => $submission->quiz->passing_grade,
                    'time_spent_seconds' => $submission->time_spent_seconds,
                ],
            ]
        );

        if ($finalScore >= 100) {
            $this->gamification->awardXp(
                $userId,
                0,
                'perfect_score',
                'quiz',
                $quizId,
                [
                    'description' => 'Perfect score on quiz!',
                    'metadata' => [
                        'score' => $finalScore,
                    ],
                ]
            );
        }

        $this->loggerService->log(
            $userId,
            'quiz_passed',
            'quiz',
            $quizId,
            [
                'quiz_id' => $quizId,
                'submission_id' => $submission->id,
                'score' => $finalScore,
                'passing_grade' => $submission->quiz->passing_grade,
                'time_spent_seconds' => $submission->time_spent_seconds,
            ]
        );

        $this->counterService->increment($userId, 'quiz_passed', 'global', null, 'lifetime');
        $this->counterService->increment($userId, 'quiz_passed', 'global', null, 'daily');
        $this->counterService->increment($userId, 'quiz_passed', 'global', null, 'weekly');

        $user = $this->getCachedUser($userId);
        if ($user) {
            $payload = [
                'quiz_id' => $quizId,
                'submission_id' => $submission->id,
                'score' => $finalScore,
                'passing_grade' => $submission->quiz->passing_grade,
                'is_perfect' => $finalScore >= 100,
            ];
            $this->evaluator->evaluate($user, 'quiz_passed', $payload);
        }
    }
}
