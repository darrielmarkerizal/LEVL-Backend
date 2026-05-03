<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Models\SystemSetting;
use Modules\Forums\Events\ThreadCreated;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;
use Modules\Gamification\Traits\CachesUsers;

class AwardXpForThreadCreated implements ShouldQueue
{
    use CachesUsers;

    public function __construct(
        private readonly GamificationService $gamification,
        private readonly EventCounterService $counterService,
        private readonly EventLoggerService $loggerService,
        private readonly BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(ThreadCreated $event): void
    {
        $thread = $event->thread;
        $userId = (int) ($thread->author_id ?? 0);

        if ($userId <= 0) {
            return;
        }

        $xp = (int) SystemSetting::get('gamification.points.thread_created', 5);

        $this->gamification->awardXp(
            $userId,
            $xp,
            'engagement',
            'thread',
            $thread->id,
            [
                'description' => __('gamification::gamification.thread_created_xp'),
                'allow_multiple' => true,
            ]
        );

        $this->loggerService->log(
            $userId,
            'thread_created',
            'thread',
            $thread->id,
            [
                'thread_id' => $thread->id,
                'forum_id' => $thread->forum_id,
            ]
        );

        $this->counterService->increment($userId, 'thread_created', 'global', null, 'lifetime');
        $this->counterService->increment($userId, 'thread_created', 'global', null, 'daily');
        $this->counterService->increment($userId, 'thread_created', 'global', null, 'weekly');

        $user = $this->getCachedUser($userId);
        if ($user) {
            $payload = [
                'thread_id' => $thread->id,
                'forum_id' => $thread->forum_id,
                'scope_type' => 'global',
                'scope_id' => null,
            ];
            $this->evaluator->evaluate($user, 'thread_created', $payload);
        }
    }
}
