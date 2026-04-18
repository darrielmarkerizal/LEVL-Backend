<?php

namespace Modules\Gamification\Services\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Auth\Models\User;
use Modules\Gamification\Models\BadgeRule;
use Modules\Gamification\Models\BadgeRuleCooldown;
use Modules\Gamification\Services\EventCounterService;

class BadgeRuleEvaluator
{
    public function __construct(
        private readonly BadgeManager $badgeManager,
        private readonly PointManager $pointManager,
        private readonly EventCounterService $counterService
    ) {}

    public function evaluate(User $user, string $triggerAction, array $payload = []): void
    {
        // 1. Get rules indexed by event (90% faster!)
        $rules = $this->getRulesByEvent($triggerAction);

        if ($rules->isEmpty()) {
            return;
        }

        // 2. Evaluate each rule (ordered by priority)
        foreach ($rules as $rule) {
            // Check cooldown
            if ($rule->cooldown_seconds && ! $this->canEvaluate($user->id, $rule->id)) {
                continue;
            }

            // Check conditions
            if (! empty($rule->conditions) && ! $this->isConditionMet($rule->conditions, $payload, $user)) {
                continue;
            }

            // Get counter (READ only, no write!)
            $counter = $this->counterService->getCounter(
                $user->id,
                $triggerAction,
                $payload['scope_type'] ?? null,
                $payload['scope_id'] ?? null,
                $rule->progress_window ?? 'lifetime'
            );

            // Check threshold
            if ($counter >= $rule->badge->threshold) {
                $awarded = $this->badgeManager->awardBadge(
                    $user->id,
                    $rule->badge->code,
                    $rule->badge->name,
                    $rule->badge->description ?? __('gamification.badge_earned_description', ['name' => $rule->badge->name])
                );

                // Update cooldown if badge awarded
                if ($awarded && $rule->cooldown_seconds) {
                    $this->updateCooldown($user->id, $rule->id, $rule->cooldown_seconds);
                }
            }
        }
    }

    private function getRulesByEvent(string $event): Collection
    {
        return Cache::tags(['gamification', 'rules'])->remember(
            "gamification.rules_by_event.{$event}",
            3600,
            function () use ($event) {
                return BadgeRule::with('badge')
                    ->where('event_trigger', $event)
                    ->whereRaw('rule_enabled IS TRUE')
                    ->orderBy('priority', 'desc')
                    ->get();
            }
        );
    }

    private function canEvaluate(int $userId, int $ruleId): bool
    {
        $cooldown = BadgeRuleCooldown::where('user_id', $userId)
            ->where('badge_rule_id', $ruleId)
            ->first();

        if (! $cooldown) {
            return true;
        }

        return $cooldown->canEvaluate();
    }

    private function updateCooldown(int $userId, int $ruleId, int $seconds): void
    {
        BadgeRuleCooldown::updateOrCreate(
            ['user_id' => $userId, 'badge_rule_id' => $ruleId],
            [
                'last_evaluated_at' => now(),
                'can_evaluate_after' => now()->addSeconds($seconds),
            ]
        );
    }

    private function isConditionMet(array $conditions, array $payload, User $user): bool
    {
        // Level Matching
        if (isset($conditions['level'])) {
            if (! isset($payload['level']) || $payload['level'] < $conditions['level']) {
                return false;
            }
        }

        // Target Matching (Course Slug)
        if (isset($conditions['course_slug'])) {
            if (! isset($payload['course_slug']) || $payload['course_slug'] !== $conditions['course_slug']) {
                return false;
            }
        }

        // Quality Scoring
        if (isset($conditions['min_score'])) {
            if (! isset($payload['score']) || $payload['score'] < $conditions['min_score']) {
                return false;
            }
        }

        if (isset($conditions['max_attempts'])) {
            if (! isset($payload['attempts']) || $payload['attempts'] > $conditions['max_attempts']) {
                return false;
            }
        }

        if (isset($conditions['is_passed'])) {
            if (! isset($payload['is_passed']) || $payload['is_passed'] !== $conditions['is_passed']) {
                return false;
            }
        }

        // Speed Validation
        if (isset($conditions['max_duration_days'])) {
            if (! isset($payload['duration_days']) || $payload['duration_days'] > $conditions['max_duration_days']) {
                return false;
            }
        }

        if (isset($conditions['is_first_submission'])) {
            if (! isset($payload['is_first_submission']) || $payload['is_first_submission'] !== $conditions['is_first_submission']) {
                return false;
            }
        }

        // Habit Validation
        if (isset($conditions['is_weekend'])) {
            if (! isset($payload['is_weekend']) || $payload['is_weekend'] !== $conditions['is_weekend']) {
                return false;
            }
        }

        if (isset($conditions['min_streak_days'])) {
            $stats = $this->pointManager->getOrCreateStats($user->id);
            if ($stats->current_streak < $conditions['min_streak_days']) {
                return false;
            }
        }

        if (isset($conditions['time_before'])) {
            if (! isset($payload['time']) || $payload['time'] > $conditions['time_before']) {
                return false;
            }
        }

        if (isset($conditions['time_after'])) {
            if (! isset($payload['time']) || $payload['time'] < $conditions['time_after']) {
                return false;
            }
        }

        return true;
    }
}
