<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Learning\Models\QuizSubmission;

class GradingQueueItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof QuizSubmission) {
            return $this->toQuizArray();
        }

        return $this->toAssignmentArray();
    }

    private function toAssignmentArray(): array
    {
        return [
            'type' => 'assignment',
            'submission_id' => $this->id,
            'student_name' => $this->user?->name,
            'student_email' => $this->user?->email,
            'assignment_id' => $this->assignment_id,
            'assignment_title' => $this->assignment?->title,
            'submitted_at' => $this->submitted_at,
            'state' => $this->state instanceof \BackedEnum ? $this->state->value : $this->state,
            'score' => $this->score,
        ];
    }

    private function toQuizArray(): array
    {
        return [
            'type' => 'quiz',
            'submission_id' => $this->id,
            'student_name' => $this->user?->name,
            'student_email' => $this->user?->email,
            'quiz_id' => $this->quiz_id,
            'quiz_title' => $this->quiz?->title,
            'submitted_at' => $this->submitted_at,
            'status' => $this->status?->value,
            'grading_status' => $this->grading_status?->value,
            'score' => $this->score,
            'final_score' => $this->final_score,
            'total_questions' => $this->relationLoaded('answers') ? $this->answers->count() : 0,
            'graded_questions' => $this->relationLoaded('answers') ? $this->answers->filter(fn ($a) => $a->score !== null)->count() : 0,
            'questions_requiring_grading' => $this->getQuestionsRequiringGrading(),
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
                'answer_id' => $answer->id,
                'question_id' => $answer->quiz_question_id,
                'question_type' => $answer->question?->type?->value,
                'question_content' => $answer->question?->content,
                'question_max_score' => $answer->question?->max_score,
                'student_answer' => $answer->content,
            ])
            ->values()
            ->toArray();
    }
}
