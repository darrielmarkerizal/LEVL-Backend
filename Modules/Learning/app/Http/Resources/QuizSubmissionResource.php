<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizSubmissionResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $request->user();
        $isStudent = $user && $user->hasRole('Student');

        if ($isStudent) {
            return $this->toStudentArray();
        }

        return $this->toInstructorArray();
    }

    private function toStudentArray(): array
    {
        return [
            'id' => $this->id,
            'attempt_number' => $this->attempt_number,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'grading_status' => $this->grading_status?->value,
            'grading_status_label' => $this->grading_status?->label(),
            'score' => $this->score,
            'final_score' => $this->final_score,
            'is_passed' => $this->when(
                $this->grading_status?->isFinal(),
                fn () => $this->isPassed()
            ),
            'started_at' => $this->started_at?->toISOString(),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'time_spent_seconds' => $this->time_spent_seconds,
            'duration' => $this->duration,
            'session_token' => $this->when(
                $this->status === \Modules\Learning\Enums\QuizSubmissionStatus::Draft,
                fn () => $this->session_token
            ),
            'quiz' => $this->when(
                $this->relationLoaded('quiz'),
                fn () => new \Modules\Learning\Http\Resources\QuizResource($this->quiz)
            ),
            'answers' => $this->when(
                $this->relationLoaded('answers'),
                fn () => \Modules\Learning\Http\Resources\QuizAnswerResource::collection($this->answers)
            ),
        ];
    }

    private function toInstructorArray(): array
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
                fn () => $this->isPassed()
            ),
            'quiz' => $this->when(
                $this->relationLoaded('quiz'),
                fn () => new \Modules\Learning\Http\Resources\QuizResource($this->quiz)
            ),
            'user' => $this->when(
                $this->relationLoaded('user'),
                fn () => ['id' => $this->user?->id, 'name' => $this->user?->name, 'email' => $this->user?->email]
            ),
            'answers' => $this->when(
                $this->relationLoaded('answers'),
                fn () => \Modules\Learning\Http\Resources\QuizAnswerResource::collection($this->answers)
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
