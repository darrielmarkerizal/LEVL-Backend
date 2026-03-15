<?php

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Traits\CachesUsers;
use Modules\Schemes\Events\LessonCompleted;

class AwardXpForLessonCompleted
{
    use CachesUsers; // FIX: Use cached user lookups

    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(LessonCompleted $event): void
    {
        // FIX: Remove unnecessary fresh() call - use event data directly
        $lesson = $event->lesson;
        $userId = $event->userId;

        if (! $lesson || ! $lesson->unit || ! $lesson->unit->course) {
            return;
        }

        // Get XP from xp_sources table
        $xpSource = \Modules\Gamification\Models\XpSource::where('code', 'lesson_completed')
            ->where('is_active', true)
            ->first();
        
        $xp = $xpSource ? $xpSource->xp_amount : 50;

        // 1. Award XP (sync)
        $this->gamification->awardXp(
            $userId,
            $xp,
            'lesson_completed',
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
        // FIX: Use cached user lookup
        $user = $this->getCachedUser($userId);
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
