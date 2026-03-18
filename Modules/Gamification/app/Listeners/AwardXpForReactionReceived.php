<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Common\Models\SystemSetting;
use Modules\Forums\Events\ReactionAdded;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;

class AwardXpForReactionReceived implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function __construct(
        private readonly GamificationService $gamification,
        private readonly EventCounterService $counterService,
        private readonly BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(ReactionAdded $event): void
    {
        $reaction = $event->reaction;

        // Award XP to the CONTENT OWNER, not the reactor
        $contentOwnerId = $reaction->reactable->user_id ?? null;

        if (! $contentOwnerId) {
            return;
        }

        $xp = (int) SystemSetting::get('gamification.points.reaction_received', 1);

        // 1. Award XP to content owner
        $this->gamification->awardXp(
            $contentOwnerId,
            $xp,
            'engagement',
            'reaction',
            $reaction->id,
            [
                'description' => __('gamification::gamification.reaction_received_xp'),
                'allow_multiple' => true,
            ]
        );

        // 2. Increment counters
        $this->counterService->increment($contentOwnerId, 'reaction_received', 'global', null, 'lifetime');
        $this->counterService->increment($contentOwnerId, 'reaction_received', 'global', null, 'daily');

        // 3. Evaluate badge rules
        $user = \Modules\Auth\Models\User::find($contentOwnerId);
        if ($user) {
            $payload = [
                'reaction_id' => $reaction->id,
                'reactable_type' => $reaction->reactable_type,
            ];
            $this->evaluator->evaluate($user, 'reaction_received', $payload);
        }
    }
}
