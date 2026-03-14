<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Learning\Events\QuizCompleted;

class AwardXpForQuizPassed
{
    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(QuizCompleted $event): void
    {
        $submission = $event->submission->fresh(['quiz', 'user']);
        $userId = $submission->user_id;
        $quizId = $submission->quiz_id;

        if (!$submission || !$submission->quiz) {
            return;
        }

        // Only award XP if quiz is passed
        if (!$submission->isPassed()) {
            return;
        }

        $finalScore = $submission->final_score ?? $submission->score;

        // 1. Award XP for passing quiz (80 XP from xp_sources)
        $this->gamification->awardXp(
            $userId,
            0, // Will use xp_sources config (quiz_passed = 80 XP)
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

        // 2. Check if perfect score (100%)
        if ($finalScore >= 100) {
            $this->gamification->awardXp(
                $userId,
                0, // Will use xp_sources config (perfect_score = 50 XP)
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

        // 3. Log event
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

        // 4. Increment counters
        $this->counterService->increment($userId, 'quiz_passed', 'global', null, 'lifetime');
        $this->counterService->increment($userId, 'quiz_passed', 'global', null, 'daily');
        $this->counterService->increment($userId, 'quiz_passed', 'global', null, 'weekly');

        // 5. Evaluate Dynamic Badge Rules
        $user = \Modules\Auth\Models\User::find($userId);
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
