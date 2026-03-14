<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Grading\Events\GradesReleased;

class AwardXpForPerfectScore
{
    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(GradesReleased $event): void
    {
        foreach ($event->grades as $grade) {
            // Only award for perfect scores (100%)
            if ($grade->score >= 100) {
                $userId = $grade->user_id;

                // Award XP for perfect score (50 XP from xp_sources)
                $this->gamification->awardXp(
                    $userId,
                    0, // Will use xp_sources config (perfect_score = 50 XP)
                    'perfect_score',
                    'grade',
                    $grade->id,
                    [
                        'description' => 'Perfect score achieved!',
                        'metadata' => [
                            'score' => $grade->score,
                            'source_type' => $grade->source_type->value,
                            'source_id' => $grade->source_id,
                        ],
                    ]
                );

                // Log event
                $this->loggerService->log(
                    $userId,
                    'perfect_score',
                    'grade',
                    $grade->id,
                    [
                        'grade_id' => $grade->id,
                        'score' => $grade->score,
                        'source_type' => $grade->source_type->value,
                        'source_id' => $grade->source_id,
                    ]
                );

                // Increment counters
                $this->counterService->increment($userId, 'perfect_score', 'global', null, 'lifetime');
                $this->counterService->increment($userId, 'perfect_score', 'global', null, 'daily');

                // Evaluate Dynamic Badge Rules
                $user = \Modules\Auth\Models\User::find($userId);
                if ($user) {
                    $payload = [
                        'grade_id' => $grade->id,
                        'score' => $grade->score,
                        'source_type' => $grade->source_type->value,
                        'source_id' => $grade->source_id,
                    ];
                    $this->evaluator->evaluate($user, 'perfect_score', $payload);
                }
            }
        }
    }
}
