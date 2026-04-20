<?php

declare(strict_types=1);

namespace Modules\Grading\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Learning\Enums\QuizQuestionType;
use Modules\Learning\Models\QuizSubmission;

class GradingQueueItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (is_array($this->resource) && isset($this->resource['quiz_submission'], $this->resource['essay_answer'])) {
            return $this->toQuizEssayRowArray($this->resource['quiz_submission'], $this->resource['essay_answer']);
        }

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
            'submission_type' => $this->assignment?->submission_type?->value ?? $this->assignment?->submission_type,
            'submission_type_label' => $this->enumLabel($this->assignment?->submission_type),
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
            'essay_questions' => $this->getEssayQuestions(),
            'questions_requiring_grading' => $this->getQuestionsRequiringGrading(),
        ];
    }

    private function toQuizEssayRowArray($submission, $essayAnswer): array
    {
        $statusValue = $submission->status instanceof \BackedEnum ? $submission->status->value : $submission->status;
        $workflowValue = $submission->grading_status instanceof \BackedEnum ? $submission->grading_status->value : $submission->grading_status;
        $course = $submission->quiz?->unit?->course;

        return [
            'type' => 'quiz',
            'row_type' => 'essay_question',
            'submission_id' => $submission->id,
            'quiz_answer_id' => $essayAnswer->id,
            'student_name' => $submission->user?->name,
            'student_email' => $submission->user?->email,
            'quiz_id' => $submission->quiz_id,
            'quiz_title' => $submission->quiz?->title,
            'course' => $course ? [
                'id' => $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'code' => $course->code,
            ] : null,
            'course_slug' => $course?->slug,
            'sequence' => $this->sequence($submission->quiz?->unit?->order, $submission->quiz?->order),
            'submitted_at' => $submission->submitted_at,
            'status' => $statusValue,
            'status_value' => $statusValue,
            'status_label' => $this->enumLabel($submission->status),
            'grading_status' => $workflowValue,
            'grading_status_label' => $this->enumLabel($submission->grading_status),
            'workflow_state' => $workflowValue,
            'workflow_state_value' => $workflowValue,
            'workflow_state_label' => $this->enumLabel($submission->grading_status),
            'score' => $submission->score,
            'final_score' => $submission->final_score,
            'total_questions' => $submission->relationLoaded('answers') ? $submission->answers->count() : 0,
            'graded_questions' => $submission->relationLoaded('answers') ? $submission->answers->filter(fn ($a) => $a->score !== null)->count() : 0,
            'question_id' => $essayAnswer->quiz_question_id,
            'question_order' => $essayAnswer->question?->order,
            'question_type' => $essayAnswer->question?->type?->value,
            'question_type_label' => $this->enumLabel($essayAnswer->question?->type),
            'question_content' => $essayAnswer->question?->content,
            'question_max_score' => $essayAnswer->question?->max_score,
            'student_answer' => $essayAnswer->content,
            'question_score' => $essayAnswer->score,
            'is_graded' => $essayAnswer->score !== null,
        ];
    }

    private function getEssayQuestions(): array
    {
        if (! $this->relationLoaded('answers')) {
            return [];
        }

        return $this->answers
            ->filter(fn ($answer) => $answer->question?->type?->value === QuizQuestionType::Essay->value)
            ->map(fn ($answer) => [
                'answer_id' => $answer->id,
                'question_id' => $answer->quiz_question_id,
                'question_order' => $answer->question?->order,
                'question_type' => $answer->question?->type?->value,
                'question_type_label' => $this->enumLabel($answer->question?->type),
                'question_content' => $answer->question?->content,
                'question_max_score' => $answer->question?->max_score,
                'student_answer' => $answer->content,
                'score' => $answer->score,
                'is_graded' => $answer->score !== null,
            ])
            ->values()
            ->toArray();
    }

    private function getQuestionsRequiringGrading(): array
    {
        return collect($this->getEssayQuestions())
            ->filter(fn (array $question) => ($question['is_graded'] ?? false) === false)
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
