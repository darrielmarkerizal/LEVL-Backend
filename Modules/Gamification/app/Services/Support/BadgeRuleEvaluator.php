<?php

namespace Modules\Gamification\Services\Support;

use Modules\Auth\Models\User;
use Modules\Gamification\Models\BadgeRule;

class BadgeRuleEvaluator
{
    public function __construct(
        private readonly BadgeManager $badgeManager,
        private readonly PointManager $pointManager
    ) {}

    public function evaluate(User $user, string $triggerAction, array $payload = []): void
    {
        // 1. Get all rules relevant to this trigger
        // Optimization: Cache rules to avoid repeated DB calls on every event (Octane friendly)
        $rules = \Illuminate\Support\Facades\Cache::remember('gamification.badge_rules', 3600, function () {
            return BadgeRule::with('badge')->get();
        });

        $relevantRules = $rules->where('event_trigger', $triggerAction);

        if ($relevantRules->isEmpty()) {
            return;
        }

        // 2. Evaluate each rule
        foreach ($relevantRules as $rule) {
            if (empty($rule->conditions) || $this->isConditionMet($rule->conditions, $payload, $user)) {
                $this->badgeManager->awardBadge(
                    $user->id,
                    $rule->badge->code,
                    $rule->badge->name,
                    $rule->badge->description ?? __('gamification.badge_earned_description', ['name' => $rule->badge->name])
                );
            }
        }
    }

    private function isConditionMet(array $conditions, array $payload, User $user): bool
    {
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
