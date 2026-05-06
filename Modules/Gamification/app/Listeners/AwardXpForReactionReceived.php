<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Forums\Events\ReactionAdded;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;

class AwardXpForReactionReceived extends GamificationListener
{

    public function __construct(
        private readonly GamificationService $gamification,
        private readonly EventCounterService $counterService,
        private readonly BadgeRuleEvaluator $evaluator
    ) {}

    public function handle(ReactionAdded $event): void
    {
        $reaction = $event->reaction;

        
        $contentOwnerId = $reaction->reactable->user_id ?? null;

        if (! $contentOwnerId) {
            return;
        }

        $xp = (int) SystemSetting::get('gamification.points.reaction_received', 1);

        
        $this->gamification->awardXp(
            $contentOwnerId,
            $xp,
            'forum_liked',
            'reaction',
            $reaction->id,
            [
                'description' => __('gamification::gamification.reaction_received_xp'),
                'allow_multiple' => true,
            ]
        );

        
        $this->counterService->increment($contentOwnerId, 'reaction_received', 'global', null, 'lifetime');
        $this->counterService->increment($contentOwnerId, 'reaction_received', 'global', null, 'daily');

        
        $user = $this->getCachedUser($contentOwnerId);
        if ($user) {
            $payload = [
                'reaction_id' => $reaction->id,
                'reactable_type' => $reaction->reactable_type,
            ];
            $this->evaluator->evaluate($user, 'reaction_received', $payload);
        }
    }
}
