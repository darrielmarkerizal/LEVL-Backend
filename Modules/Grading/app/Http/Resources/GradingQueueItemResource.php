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
        $statusValue = $this->status instanceof \BackedEnum ? $this->status->value : $this->status;
        $workflowValue = $this->state instanceof \BackedEnum ? $this->state->value : $this->state;
        $course = $this->assignment?->unit?->course;

        return [
            'type' => 'assignment',
            'submission_id' => $this->id,
            'student_name' => $this->user?->name,
            'student_email' => $this->user?->email,
            'assignment_id' => $this->assignment_id,
            'assignment_title' => $this->assignment?->title,
            'course' => $course ? [
                'id' => $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'code' => $course->code,
            ] : null,
            'course_slug' => $course?->slug,
            'sequence' => $this->sequence($this->assignment?->unit?->order, $this->assignment?->order),
            'submitted_at' => $this->submitted_at,
            'status' => $statusValue,
            'status_value' => $statusValue,
            'status_label' => $this->enumLabel($this->status),
            'workflow_state' => $workflowValue,
            'workflow_state_value' => $workflowValue,
            'workflow_state_label' => $this->enumLabel($this->state),
            'score' => $this->score,
        ];
    }

    private function toQuizArray(): array
    {
        $statusValue = $this->status instanceof \BackedEnum ? $this->status->value : $this->status;
        $workflowValue = $this->grading_status instanceof \BackedEnum ? $this->grading_status->value : $this->grading_status;
        $course = $this->quiz?->unit?->course;

        return [
            'type' => 'quiz',
            'submission_id' => $this->id,
            'student_name' => $this->user?->name,
            'student_email' => $this->user?->email,
            'quiz_id' => $this->quiz_id,
            'quiz_title' => $this->quiz?->title,
            'course' => $course ? [
                'id' => $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'code' => $course->code,
            ] : null,
            'course_slug' => $course?->slug,
            'sequence' => $this->sequence($this->quiz?->unit?->order, $this->quiz?->order),
            'submitted_at' => $this->submitted_at,
            'status' => $statusValue,
            'status_value' => $statusValue,
            'status_label' => $this->enumLabel($this->status),
            'grading_status' => $workflowValue,
            'grading_status_label' => $this->enumLabel($this->grading_status),
            'workflow_state' => $workflowValue,
            'workflow_state_value' => $workflowValue,
            'workflow_state_label' => $this->enumLabel($this->grading_status),
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

    private function enumLabel(mixed $enum): ?string
    {
        if (! is_object($enum) || ! method_exists($enum, 'label')) {
            return null;
        }

        return $enum->label();
    }

    private function sequence(?int $unitOrder, ?int $elementOrder): ?string
    {
        if ($unitOrder === null || $elementOrder === null) {
            return null;
        }

        return $unitOrder.'.'.$elementOrder;
    }
}
