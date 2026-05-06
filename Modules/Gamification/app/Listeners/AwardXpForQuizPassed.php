<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Learning\Events\QuizCompleted;

class AwardXpForQuizPassed extends GamificationListener
{

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

        $xpAmount = (int) SystemSetting::get('gamification.points.quiz_passed', 30);

        $this->gamification->awardXp(
            $userId,
            $xpAmount,
            'quiz_passed',
            'quiz',
            $quizId,
            [
                'description' => sprintf('Passed quiz: %s (Score: %.2f)', $submission->quiz->title, $finalScore),
                'allow_multiple' => false,
                'metadata' => [
                    'score' => $finalScore,
                    'passing_grade' => $submission->quiz->passing_grade,
                    'time_spent_seconds' => $submission->time_spent_seconds,
                ],
            ]
        );

        if ($finalScore >= 100) {
            $perfectScoreXp = (int) SystemSetting::get('gamification.points.perfect_score_quiz', 20);

            $this->gamification->awardXp(
                $userId,
                $perfectScoreXp,
                'perfect_score',
                'quiz',
                $quizId,
                [
                    'description' => 'Perfect score on quiz!',
                    'allow_multiple' => false,
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

        $this->counterService->incrementGlobal($userId, 'quiz_passed');

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
