<?php

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Services\GamificationService;
use Modules\Schemes\Events\LessonCompleted;

class AwardXpForLessonCompleted
{
    public function __construct(
        private GamificationService $gamification,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(LessonCompleted $event): void
    {
        $lesson = $event->lesson->fresh(['unit.course']);
        $userId = $event->userId;

        if (! $lesson || ! $lesson->unit || ! $lesson->unit->course) {
            return;
        }

        $xp = (int) SystemSetting::get('gamification.points.lesson_complete', 10);

        $this->gamification->awardXp(
            $userId,
            $xp,
            'completion',
            'lesson',
            $lesson->id,
            [
                'description' => sprintf('Completed lesson: %s', $lesson->title),
                'allow_multiple' => false,
            ]
        );

        // Evaluate Dynamic Badge Rules
        $user = \Modules\Auth\Models\User::find($userId);
        if ($user) {
            $this->evaluator->evaluate($user, 'lesson_completed');
        }
    }
}

