<?php

declare(strict_types=1);

namespace Modules\Grading\Services\Support;

use Illuminate\Support\Collection;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Models\Submission;

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

    public function assertValidScore(float $score, float $maxScore): void
    {
        if ($score < 0.0 || $score > $maxScore) {
            throw new \InvalidArgumentException(__('messages.grading.invalid_score'));
        }
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
