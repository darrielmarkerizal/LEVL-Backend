<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Traits\CachesUsers;
use Modules\Schemes\Events\LessonCompleted;

class AwardXpForLessonCompleted implements ShouldQueue
{
    use CachesUsers;

    public function __construct(
        private GamificationService $gamification,
        private EventCounterService $counterService,
        private EventLoggerService $loggerService,
        private \Modules\Gamification\Services\Support\BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(LessonCompleted $event): void
    {
        $lesson = $event->lesson;
        $userId = $event->userId;

        if (! $lesson || ! $lesson->unit || ! $lesson->unit->course) {
            return;
        }

        $xpSource = \Modules\Gamification\Models\XpSource::where('code', 'lesson_completed')
            ->active()
            ->first();

        $xp = $xpSource ? $xpSource->xp_amount : 50;

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

        $this->counterService->increment($userId, 'lesson_completed', 'global', null, 'lifetime');
        $this->counterService->increment($userId, 'lesson_completed', 'global', null, 'daily');
        $this->counterService->increment($userId, 'lesson_completed', 'global', null, 'weekly');
        $this->counterService->increment($userId, 'lesson_completed', 'course', $lesson->unit->course_id, 'lifetime');

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
