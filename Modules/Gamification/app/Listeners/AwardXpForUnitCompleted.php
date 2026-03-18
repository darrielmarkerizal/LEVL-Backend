<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;
use Modules\Gamification\Traits\CachesUsers;
use Modules\Schemes\Events\UnitCompleted;

class AwardXpForUnitCompleted implements ShouldQueue
{
    use CachesUsers;
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(UnitCompleted $event): void
    {
        $unit = $event->unit;
        $userId = $event->userId;

        if (! $unit || ! $unit->course) {
            return;
        }

        $xp = (int) SystemSetting::get('gamification.points.unit_complete', 20);

        $this->gamification->awardXp(
            $userId,
            $xp,
            'unit_completed',
            'unit',
            $unit->id,
            [
                'description' => sprintf('Completed unit: %s', $unit->title),
                'allow_multiple' => false,
            ]
        );

        $this->counterService->increment($userId, 'unit_completed', 'global', null, 'lifetime');
        $this->counterService->increment($userId, 'unit_completed', 'global', null, 'daily');
        $this->counterService->increment($userId, 'unit_completed', 'global', null, 'weekly');
        $this->counterService->increment($userId, 'unit_completed', 'course', $unit->course_id, 'lifetime');

        $user = $this->getCachedUser($userId);
        if ($user) {
            $payload = [
                'unit_id' => $unit->id,
                'course_id' => $unit->course_id,
                'is_weekend' => now()->isWeekend(),
            ];
            $this->evaluator->evaluate($user, 'unit_completed', $payload);
        }
    }
}
