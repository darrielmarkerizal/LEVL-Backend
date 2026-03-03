<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'passing_grade' => $this->passing_grade,
            'max_score' => $this->max_score,
            'max_attempts' => $this->max_attempts,
            'time_limit_minutes' => $this->time_limit_minutes,
            'retake_enabled' => $this->retake_enabled,
            'auto_grading' => $this->auto_grading,
            'review_mode' => $this->review_mode?->value ?? $this->review_mode,
            'is_locked' => $this->when(isset($this->is_locked), $this->is_locked),
            'unit_slug' => $this->unit->slug ?? null,
            'questions_count' => $this->when(
                $this->relationLoaded('questions'),
                fn () => $this->questions->count()
            ),
            'scope_type' => $this->when(isset($this->scope_type), $this->scope_type),
            'attachments' => $this->when(
                $this->relationLoaded('media'),
                fn () => $this->getMedia('attachments')->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'size' => $m->size,
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function toInstructorArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'passing_grade' => $this->passing_grade,
            'auto_grading' => $this->auto_grading,
            'max_score' => $this->max_score,
            'max_attempts' => $this->max_attempts,
            'cooldown_minutes' => $this->cooldown_minutes,
            'time_limit_minutes' => $this->time_limit_minutes,
            'retake_enabled' => $this->retake_enabled,
            'randomization_type' => $this->randomization_type,
            'question_bank_count' => $this->question_bank_count,
            'review_mode' => $this->review_mode,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'available_from' => $this->available_from?->toISOString(),
            'deadline_at' => $this->deadline_at?->toISOString(),
            'tolerance_minutes' => $this->tolerance_minutes,
            'late_penalty_percent' => $this->late_penalty_percent,
            'scope_type' => $this->scope_type,
            'assignable_type' => $this->assignable_type,
            'assignable_id' => $this->assignable_id,
            'lesson_id' => $this->lesson_id,
            'created_by' => $this->created_by,
            'creator' => $this->when(
                $this->relationLoaded('creator'),
                fn () => ['id' => $this->creator?->id, 'name' => $this->creator?->name]
            ),
            'questions_count' => $this->when(
                $this->relationLoaded('questions'),
                fn () => $this->questions->count()
            ),
            'questions' => $this->when(
                $this->relationLoaded('questions'),
                fn () => QuizQuestionResource::collection($this->questions)
            ),
            'attachments' => $this->when(
                $this->relationLoaded('media'),
                fn () => $this->getMedia('attachments')->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'size' => $m->size,
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
