<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Forums\Events\ReplyCreated;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\EventLoggerService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;

class AwardXpForReplyCreated extends GamificationListener
{
    public function __construct(
        private readonly GamificationService $gamification,
        private readonly EventCounterService $counterService,
        private readonly EventLoggerService $loggerService,
        private readonly BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(ReplyCreated $event): void
    {
        $reply = $event->reply;
        $userId = (int) ($reply->author_id ?? 0);

        if ($userId <= 0) {
            return;
        }

        $xp = (int) SystemSetting::get('gamification.points.reply_created', 3);

        $this->gamification->awardXp(
            $userId,
            $xp,
            'forum_reply_created',
            'reply',
            $reply->id,
            [
                'description' => __('gamification::gamification.reply_created_xp'),
                'allow_multiple' => true,
            ]
        );

        $this->loggerService->log(
            $userId,
            'reply_created',
            'reply',
            $reply->id,
            [
                'reply_id' => $reply->id,
                'thread_id' => $reply->thread_id,
            ]
        );

        $this->counterService->incrementGlobal($userId, 'reply_created');

        $user = $this->getCachedUser($userId);
        if ($user) {
            $payload = [
                'reply_id' => $reply->id,
                'thread_id' => $reply->thread_id,
                'scope_type' => 'global',
                'scope_id' => null,
            ];
            $this->evaluator->evaluate($user, 'reply_created', $payload);
        }
    }
}
