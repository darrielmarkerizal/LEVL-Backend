<?php

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Schemes\Events\LessonCompleted;

class AwardXpForLessonCompleted
{
    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
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

        // 1. Award XP (sync)
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

        // 2. Log event (sync, fast)
        $this->loggerService->log(
            $userId,
            'lesson_completed',
            'lesson',
            $lesson->id,
            [
                'lesson_id' => $lesson->id,
                'course_id' => $lesson->unit->course_id,
                'unit_id' => $lesson->unit_id,
                'is_weekend' => now()->isWeekend(),
            ]
        );

        // 3. Increment counters (sync, fast - no locking!)
        $this->counterService->increment($userId, 'lesson_completed', 'global', null, 'lifetime');
        $this->counterService->increment($userId, 'lesson_completed', 'global', null, 'daily');
        $this->counterService->increment($userId, 'lesson_completed', 'global', null, 'weekly');
        $this->counterService->increment($userId, 'lesson_completed', 'course', $lesson->unit->course_id, 'lifetime');

        // 4. Evaluate Dynamic Badge Rules
        $user = \Modules\Auth\Models\User::find($userId);
        if ($user) {
            $payload = [
                'lesson_id' => $lesson->id,
                'course_id' => $lesson->unit->course_id,
                'unit_id' => $lesson->unit_id,
                'scope_type' => 'course',
                'scope_id' => $lesson->unit->course_id,
                'is_weekend' => now()->isWeekend(),
            ];
            $this->evaluator->evaluate($user, 'lesson_completed', $payload);
        }
    }
}
