<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradingQueueItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'submission_id' => $this->id,
            'student_name' => $this->user?->name,
            'student_email' => $this->user?->email,
            'assignment_id' => $this->assignment_id,
            'assignment_title' => $this->assignment?->title,
            'submitted_at' => $this->submitted_at,
            'is_late' => $this->is_late,
            'questions_requiring_grading' => $this->getQuestionsRequiringGrading(),
            'total_questions' => $this->answers->count(),
            'graded_questions' => $this->answers->filter(fn ($a) => $a->score !== null)->count(),
        ];
    }

    private function getQuestionsRequiringGrading(): array
    {
        if (! $this->relationLoaded('answers')) {
            return [];
        }

        return $this->answers
            ->filter(fn ($answer) => $answer->score === null && ! $answer->question?->canAutoGrade())
            ->map(fn ($answer) => [
                'question_id' => $answer->question_id,
                'question_type' => $answer->question?->type?->value,
                'question_content' => $answer->question?->content,
            ])
            ->values()
            ->toArray();
    }
}
