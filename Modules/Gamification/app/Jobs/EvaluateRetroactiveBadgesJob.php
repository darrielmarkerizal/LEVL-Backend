<?php

declare(strict_types=1);

namespace Modules\Gamification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Gamification\Models\BadgeRule;
use Modules\Gamification\Services\EventCounterService;
use Modules\Gamification\Services\Support\BadgeManager;

class EvaluateRetroactiveBadgesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $userId,
        private readonly ?string $eventTrigger = null,
        private readonly ?int $badgeId = null
    ) {}

    public function handle(BadgeManager $badgeManager, EventCounterService $counterService): void
    {
        $query = BadgeRule::with('badge')
            ->whereRaw('rule_enabled IS TRUE');

        if ($this->eventTrigger) {
            $query->where('event_trigger', $this->eventTrigger);
        }

        if ($this->badgeId) {
            $query->where('badge_id', $this->badgeId);
        }

        $rules = $query->get();

        foreach ($rules as $rule) {
            if (! $rule->badge || ! $rule->badge->active || ! $rule->badge->threshold) {
                continue;
            }

            // Retroactive evaluation is generally safe for rules that only depend on the event counter
            // For rules with complex conditions (like score >= 80), it requires payload context which we don't have.
            // We'll skip rules that have condition requirements that can't be resolved without a payload context.
            if (! empty($rule->conditions)) {
                $hasPayloadConditions = false;
                $payloadKeys = ['min_score', 'max_attempts', 'is_passed', 'max_duration_days', 'is_first_submission', 'is_weekend', 'time_before', 'time_after', 'level', 'course_slug'];
                
                foreach ($payloadKeys as $key) {
                    if (isset($rule->conditions[$key])) {
                        $hasPayloadConditions = true;
                        break;
                    }
                }

                if ($hasPayloadConditions) {
                    continue; // Skip because we cannot evaluate payload-dependent rules retroactively
                }
            }

            $counter = $counterService->getCounter(
                $this->userId,
                $rule->event_trigger,
                null,
                null,
                $rule->progress_window ?? 'lifetime'
            );

            if ($counter >= $rule->badge->threshold) {
                // Award Badge (idempotency is handled inside the badgeManager)
                $badgeManager->awardBadge(
                    $this->userId,
                    $rule->badge->code,
                    $rule->badge->name,
                    $rule->badge->description ?? __('gamification.badge_earned_description', ['name' => $rule->badge->name])
                );
            }
        }
    }
}
