<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Grading\Events\GradesReleased;

class AwardXpForPerfectScore implements ShouldQueue
{
    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(GradesReleased $event): void
    {
        $xpAmount = (int) SystemSetting::get('gamification.points.perfect_score_assignment', 20);

        foreach ($event->submissions as $submission) {
            $grade = $submission->grade;

            if ($grade && $grade->score >= 100) {
                $userId = $grade->user_id;

                $this->gamification->awardXp(
                    $userId,
                    $xpAmount,
                    'perfect_score',
                    'grade',
                    $grade->id,
                    [
                        'description' => 'Perfect score achieved!',
                        'allow_multiple' => false,
                        'metadata' => [
                            'score' => $grade->score,
                            'source_type' => $grade->source_type->value,
                            'source_id' => $grade->source_id,
                        ],
                    ]
                );

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

                $this->counterService->increment($userId, 'perfect_score', 'global', null, 'lifetime');
                $this->counterService->increment($userId, 'perfect_score', 'global', null, 'daily');

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
