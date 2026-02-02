<?php

namespace Modules\Gamification\Services\Support;

use Modules\Auth\Models\User;
use Modules\Gamification\Models\BadgeRule;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Models\UserScopeStat;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;

class BadgeRuleEvaluator
{
    public function __construct(
        private readonly BadgeManager $badgeManager,
        private readonly PointManager $pointManager
    ) {}

    public function evaluate(User $user, string $triggerAction): void
    {
        // 1. Get all rules relevant to this trigger
        // Optimization: Cache rules to avoid repeated DB calls on every event (Octane friendly)
        $rules = \Illuminate\Support\Facades\Cache::remember('gamification.badge_rules', 3600, function () {
             return BadgeRule::with('badge')->get();
        });

        // Mapping Triggers to Criteria
        $relevantCriteria = match ($triggerAction) {
            'lesson_completed' => ['lesson_count'],
            'course_completed' => ['course_count'],
            'login' => ['login_streak'],
            default => []
        };

        if (empty($relevantCriteria)) {
            return;
        }

        $relevantRules = $rules->whereIn('criterion', $relevantCriteria);

        if ($relevantRules->isEmpty()) {
            return;
        }

        // 2. Evaluate each rule
        foreach ($relevantRules as $rule) {
            if ($this->checkRule($user, $rule)) {
                $this->badgeManager->awardBadge(
                    $user->id,
                    $rule->badge->code,
                    $rule->badge->name,
                    $rule->badge->description ?? __('gamification.badge_earned_description', ['name' => $rule->badge->name])
                );
            }
        }
    }

    private function checkRule(User $user, BadgeRule $rule): bool
    {
        $currentValue = 0;

        switch ($rule->criterion) {
            case 'lesson_count':
                // Count completed lessons (from UserScopeStat or direct DB count)
                // For performance, let's use UserSameStat or direct count if not exists
                // Assuming we can count completed lessons via DB
                $currentValue = \Modules\Schemes\Models\LessonUser::where('user_id', $user->id)
                    ->whereNotNull('completed_at')
                    ->count();
                break;

            case 'course_count':
                $currentValue = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->count();
                break;
            
            case 'login_streak':
                $stats = $this->pointManager->getOrCreateStats($user->id);
                $currentValue = $stats->current_streak;
                break;
        }

        return match ($rule->operator) {
            '>=' => $currentValue >= $rule->value,
            '>' => $currentValue > $rule->value,
            '=' => $currentValue == $rule->value,
            default => false
        };
    }
}
