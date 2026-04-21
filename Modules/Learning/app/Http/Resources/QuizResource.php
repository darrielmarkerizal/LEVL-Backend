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
            'order' => $this->order,
            'sequence' => $this->sequence(),
            'description' => $this->description,
            'passing_grade' => $this->passing_grade,
            'max_score' => $this->max_score,
            'time_limit_minutes' => $this->time_limit_minutes,
            'auto_grading' => $this->auto_grading,
            'review_mode' => $this->review_mode?->value ?? $this->review_mode,
            'randomization_type' => $this->randomization_type?->value ?? $this->randomization_type,
            'question_bank_count' => $this->question_bank_count,
            'status' => $this->when(isset($this->status_value), $this->status_value) ?: $this->status?->value,
            'status_label' => $this->when(isset($this->status_label), $this->status_label) ?: $this->status?->label(),
            'is_locked' => $this->when(isset($this->is_locked), $this->is_locked),
            'is_completed' => $this->when(isset($this->is_completed), $this->is_completed),
            'unit_slug' => $this->unit->slug ?? null,
            'course_slug' => $this->unit->course->slug ?? null,
            'unit' => $this->when(
                $this->relationLoaded('unit'),
                fn () => [
                    'id' => $this->unit->id,
                    'slug' => $this->unit->slug,
                    'title' => $this->unit->title,
                    'code' => $this->unit->code,
                    'course' => $this->when(
                        $this->unit && $this->unit->relationLoaded('course') && $this->unit->course,
                        fn () => [
                            'id' => $this->unit->course->id,
                            'slug' => $this->unit->course->slug,
                            'title' => $this->unit->course->title,
                            'code' => $this->unit->course->code,
                        ]
                    ),
                ]
            ),
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
            'submission_status' => $this->when(isset($this->submission_status), $this->submission_status),
            'submission_status_label' => $this->when(isset($this->submission_status_label), $this->submission_status_label),
            'score' => $this->when(isset($this->score), $this->score),
            'submitted_at' => $this->when(isset($this->submitted_at), $this->submitted_at),
            'is_submission_completed' => $this->when(isset($this->is_submission_completed), $this->is_submission_completed),
            'attempts_used' => $this->when(isset($this->attempts_used), $this->attempts_used),
            'xp_reward' => $this->when(isset($this->xp_reward), $this->xp_reward),
            'xp_perfect_bonus' => $this->when(isset($this->xp_perfect_bonus), $this->xp_perfect_bonus),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function toInstructorArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'order' => $this->order,
            'sequence' => $this->sequence(),
            'description' => $this->description,
            'passing_grade' => $this->passing_grade,
            'auto_grading' => $this->auto_grading,
            'max_score' => $this->max_score,
            'time_limit_minutes' => $this->time_limit_minutes,
            'review_mode' => $this->review_mode,
            'randomization_type' => $this->randomization_type?->value ?? $this->randomization_type,
            'question_bank_count' => $this->question_bank_count,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'available_from' => $this->available_from?->toISOString(),
            'tolerance_minutes' => $this->tolerance_minutes,
            'late_penalty_percent' => $this->late_penalty_percent,
            'scope_type' => $this->scope_type,
            'assignable_type' => $this->assignable_type,
            'assignable_id' => $this->assignable_id,
            'lesson_id' => $this->lesson_id,
            'unit_slug' => $this->unit->slug ?? null,
            'course_slug' => $this->unit->course->slug ?? null,
            'unit' => $this->when(
                $this->relationLoaded('unit'),
                fn () => [
                    'id' => $this->unit->id,
                    'slug' => $this->unit->slug,
                    'title' => $this->unit->title,
                    'code' => $this->unit->code,
                    'course' => $this->when(
                        $this->unit && $this->unit->relationLoaded('course') && $this->unit->course,
                        fn () => [
                            'id' => $this->unit->course->id,
                            'slug' => $this->unit->course->slug,
                            'title' => $this->unit->course->title,
                            'code' => $this->unit->course->code,
                        ]
                    ),
                ]
            ),
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

    private function sequence(): ?string
    {
        $unitOrder = $this->unit?->order;
        $elementOrder = $this->order;

        if ($unitOrder === null || $elementOrder === null) {
            return null;
        }

        return $unitOrder . '.' . $elementOrder;
    }
}
