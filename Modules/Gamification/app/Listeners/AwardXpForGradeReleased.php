<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use Modules\Common\Models\SystemSetting;
use Modules\Gamification\Services\GamificationService;
use Modules\Grading\Events\GradesReleased;

class AwardXpForGradeReleased
{
    public function __construct(
        private readonly GamificationService $gamification
    ) {}

    public function handle(GradesReleased $event): void
    {
        $passingThreshold = (float) SystemSetting::get('grading.passing_score_percent', 70);
        $xpAmount = (int) SystemSetting::get('gamification.points.assignment_completion', 50);

        foreach ($event->submissions as $submission) {
            $grade = $submission->grade;
            $assignment = $submission->assignment;

            if (! $grade || ! $grade->isReleased()) {
                continue;
            }

            if ($grade->effective_score < $passingThreshold) {
                continue;
            }

            $this->gamification->awardXp(
                $submission->user_id,
                $xpAmount,
                'achievement',
                'assignment',
                $assignment->id,
                [
                    'description' => 'Assignment completion XP',
                    'allow_multiple' => false,
                ]
            );
        }
    }
}
