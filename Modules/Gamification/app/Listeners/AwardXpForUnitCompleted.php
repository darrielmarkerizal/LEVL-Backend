<?php

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Services\GamificationService;
use Modules\Schemes\Events\UnitCompleted;

class AwardXpForUnitCompleted
{
    public function __construct(private GamificationService $gamification) {}

    public function handle(UnitCompleted $event): void
    {
        $unit = $event->unit->fresh(['course']);
        $userId = $event->userId;

        if (! $unit || ! $unit->course) {
            return;
        }

        $xp = (int) SystemSetting::get('gamification.points.unit_complete', 20);

        $this->gamification->awardXp(
            $userId,
            $xp,
            'completion',
            'unit',
            $unit->id,
            [
                'description' => sprintf('Completed unit: %s', $unit->title),
                'allow_multiple' => false,
            ]
        );
    }
}
