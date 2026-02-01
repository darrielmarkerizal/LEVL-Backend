<?php

declare(strict_types=1);

namespace Modules\Grading\Services\Support;

use Modules\Learning\Models\Submission;
use Modules\Schemes\Models\Course;
use Modules\Learning\Enums\SubmissionState;
use Illuminate\Support\Collection;

class GradeCalculator
{
    public function calculateSubmissionScore(Submission $submission): float
    {
        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($submission->answers as $answer) {
            $question = $answer->question;

            if (! $question || $answer->score === null) {
                continue;
            }

            $weight = $question->weight ?? 1;
            $maxScore = $question->max_score ?? 100;
            $normalizedScore = ($answer->score / $maxScore) * 100;

            $totalWeightedScore += $normalizedScore * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight === 0) {
            return 0.0;
        }

        return round($totalWeightedScore / $totalWeight, 2);
    }
    
    public function applyLatePenalty(float $score, ?float $penaltyPercent): float
    {
        if ($penaltyPercent === null) {
            $penaltyPercent = (float) \Modules\Common\Models\SystemSetting::get('learning.late_penalty_percent', 0);
        }
        
        if ($penaltyPercent > 0) {
            $penalty = ($score * $penaltyPercent) / 100;
            return max(0, $score - $penalty);
        }
        
        return $score;
    }

    public function calculateCourseScore(Collection $assignments, int $studentId): float
    {
        if ($assignments->isEmpty()) {
            return 0.0;
        }

        $totalWeightedScore = 0.0;
        $totalWeight = 0.0;

        foreach ($assignments as $assignment) {
            $highestSubmission = Submission::query()
                ->where('assignment_id', $assignment->id)
                ->where('user_id', $studentId)
                ->whereNotNull('score')
                ->whereNotIn('state', [SubmissionState::InProgress->value])
                ->whereHas('grade', fn ($q) => $q->where('is_draft', false))
                ->orderByDesc('score')
                ->first();

            if ($highestSubmission && $highestSubmission->score !== null) {
                $weight = $assignment->max_score ?? 100;
                $normalizedScore = ($highestSubmission->score / 100) * $weight;
                $totalWeightedScore += $normalizedScore;
                $totalWeight += $weight;
            }
        }

        if ($totalWeight === 0.0) {
            return 0.0;
        }

        return round(($totalWeightedScore / $totalWeight) * 100, 2);
    }
}
