<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizSubmissionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'user_id' => $this->user_id,
            'enrollment_id' => $this->enrollment_id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'grading_status' => $this->grading_status?->value,
            'grading_status_label' => $this->grading_status?->label(),
            'score' => $this->score,
            'final_score' => $this->final_score,
            'attempt_number' => $this->attempt_number,
            'is_late' => $this->is_late,
            'is_resubmission' => $this->is_resubmission,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'time_spent_seconds' => $this->time_spent_seconds,
            'duration' => $this->duration,
            'is_passed' => $this->when(
                $this->grading_status?->isFinal(),
                fn() => $this->isPassed()
            ),
            'user' => $this->when(
                $this->relationLoaded('user'),
                fn() => ['id' => $this->user?->id, 'name' => $this->user?->name, 'email' => $this->user?->email]
            ),
            'answers' => $this->when(
                $this->relationLoaded('answers'),
                fn() => $this->answers->map(fn($a) => [
                    'id' => $a->id,
                    'quiz_question_id' => $a->quiz_question_id,
                    'content' => $a->content,
                    'selected_options' => $a->selected_options,
                    'score' => $a->score,
                    'is_auto_graded' => $a->is_auto_graded,
                    'feedback' => $a->feedback,
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
