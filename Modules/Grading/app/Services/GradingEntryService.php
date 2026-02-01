<?php

declare(strict_types=1);

namespace Modules\Grading\Services;

use InvalidArgumentException;
use Modules\Grading\DTOs\SubmissionGradeDTO;
use Modules\Grading\Events\GradesReleased;
use Modules\Grading\Models\Grade;
use Modules\Grading\Services\Support\GradeCalculator;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Models\Submission;

class GradingEntryService
{
    public function __construct(
        private readonly GradeCalculator $calculator,
        private readonly \Modules\Learning\Contracts\Repositories\AssignmentRepositoryInterface $assignmentRepository,
        private readonly \Modules\Grading\Services\Support\GradingActionProcessor $actionProcessor,
        private readonly \Modules\Grading\Services\Support\AutoGradingProcessor $autoGradingProcessor
    ) {}

    public function autoGrade(int $submissionId): void
    {
        $this->autoGradingProcessor->execute($submissionId);
    }

    public function manualGrade(SubmissionGradeDTO $data): Grade
    {
        $submission = Submission::with(['answers.question', 'assignment'])->findOrFail($data->submissionId);
        
        $globalScoreOverride = $data->scoreOverride;

        if ($globalScoreOverride !== null && $submission->is_late) {
             $globalScoreOverride = $this->calculator->applyLatePenalty(
                 $globalScoreOverride, 
                 $submission->assignment->late_penalty_percent
             );
        }

        // Delegate answer processing
        $this->actionProcessor->processAnswers($submission, $data->answers);
        
        if ($globalScoreOverride === null) {
             $submission->refresh();
             if ($submission->answers->contains(fn($a) => $a->score === null)) {
                  throw \Modules\Learning\Exceptions\SubmissionException::notAllowed(
                    __('messages.grading.incomplete_grading')
                );
             }
             
             $score = $this->calculator->calculateSubmissionScore($submission);
        } else {
            $score = $globalScoreOverride;
        }

        $submission->update(['score' => $score]);

        // Delegate grade persistence
        $grade = $this->actionProcessor->persistGrade(
            $submission, 
            $score, 
            $data->graderId, 
            $data->feedback
        );

        $submission->transitionTo(SubmissionState::Graded, $data->graderId ?? auth('api')->id());

        return $grade;
    }

    public function releaseGrade(int $submissionId): void
    {
        $submission = Submission::with('grade')->findOrFail($submissionId);

        if (!$submission->grade) {
            throw new InvalidArgumentException(__('messages.grading.no_grade_exists'));
        }

        if ($submission->grade->is_draft) {
            throw new InvalidArgumentException(__('messages.grading.cannot_release_draft'));
        }

        if ($submission->state !== SubmissionState::Graded) {
             if ($submission->state !== SubmissionState::Graded && $submission->state !== SubmissionState::Released) {
                  throw new InvalidArgumentException(__('messages.grading.submission_not_graded'));
             }
        }

        $submission->grade->release();
        $submission->transitionTo(SubmissionState::Released, (int) auth('api')->id());

        GradesReleased::dispatch(collect([$submission]), auth('api')->id());
    }

    public function saveDraftGrade(SubmissionGradeDTO $data): void
    {
         $submission = Submission::with(['answers.question', 'grade'])->findOrFail($data->submissionId);
         $this->actionProcessor->saveDraft($submission, $data->answers, $data->graderId);
    }
    
    public function overrideGrade(int $submissionId, float $score, string $reason): void
    {
        $submission = Submission::findOrFail($submissionId);
        $this->actionProcessor->overrideGrade($submission, $score, $reason, (int) auth('api')->id());
    }

    public function returnToQueue(int $submissionId): void
    {
        $submission = Submission::findOrFail($submissionId);
        $this->actionProcessor->returnToQueue($submission);
    }

    public function recalculateCourseGrade(int $studentId, int $courseId): ?float
    {
         if (! $courseId) return null;
         
         $assignments = $this->assignmentRepository->getFlattenedForCourse($courseId);
         $courseGrade = $this->calculator->calculateCourseScore($assignments, $studentId);
         
         Grade::updateOrCreate(
            [
                'source_type' => 'course',
                'source_id' => $courseId,
                'user_id' => $studentId,
            ],
            [
                'score' => $courseGrade,
                'max_score' => 100,
                'is_draft' => false,
                'graded_at' => now(),
            ]
        );
        
        return $courseGrade;
    }

}
