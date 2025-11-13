<?php

namespace Modules\Learning\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Common\Models\SystemSetting;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Models\LessonProgress;
use Modules\Learning\Events\SubmissionCreated;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Models\Lesson;

class SubmissionService
{
    public function create(Assignment $assignment, int $userId, array $data): Submission
    {
        return DB::transaction(function () use ($assignment, $userId, $data) {
            // Get enrollment via lesson
            $lesson = $assignment->lesson;
            if (! $lesson) {
                throw ValidationException::withMessages([
                    'assignment' => 'Assignment tidak memiliki lesson yang valid.',
                ]);
            }

            $enrollment = $this->getEnrollmentForLesson($lesson, $userId);
            if (! $enrollment) {
                throw ValidationException::withMessages([
                    'enrollment' => 'Anda belum terdaftar pada course ini.',
                ]);
            }

            // Check if assignment is available
            if (! $assignment->isAvailable()) {
                throw ValidationException::withMessages([
                    'assignment' => 'Assignment belum tersedia atau belum dipublish.',
                ]);
            }

            // Check for existing submission (only non-draft submissions count)
            $existingSubmission = Submission::query()
                ->where('assignment_id', $assignment->id)
                ->where('user_id', $userId)
                ->whereIn('status', ['submitted', 'late', 'graded'])
                ->latest('id')
                ->first();

            $allowResubmit = $assignment->allow_resubmit !== null
                ? (bool) $assignment->allow_resubmit
                : SystemSetting::get('learning.allow_resubmit', true);
            $isResubmission = $existingSubmission !== null;

            if ($isResubmission && ! $allowResubmit) {
                throw ValidationException::withMessages([
                    'submission' => 'Resubmission tidak diizinkan untuk assignment ini.',
                ]);
            }

            // Determine attempt number
            $attemptNumber = $isResubmission
                ? ($existingSubmission->attempt_number + 1)
                : 1;

            // Check deadline and determine if late
            $isLate = $assignment->isPastDeadline();

            // Create submission
            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $userId,
                'enrollment_id' => $enrollment->id,
                'answer_text' => $data['answer_text'] ?? null,
                'status' => $isLate ? 'late' : 'submitted',
                'attempt_number' => $attemptNumber,
                'is_late' => $isLate,
                'is_resubmission' => $isResubmission,
                'previous_submission_id' => $isResubmission ? $existingSubmission->id : null,
                'submitted_at' => Carbon::now(),
            ]);

            // Increment attempt_count in lesson_progress
            $this->incrementLessonProgressAttempt($enrollment->id, $lesson->id);

            // Dispatch event
            SubmissionCreated::dispatch($submission);

            return $submission->fresh(['assignment', 'user', 'enrollment', 'files']);
        });
    }

    public function update(Submission $submission, array $data): Submission
    {
        if ($submission->status === 'graded') {
            throw ValidationException::withMessages([
                'submission' => 'Submission yang sudah dinilai tidak dapat diubah.',
            ]);
        }

        $submission->update([
            'answer_text' => $data['answer_text'] ?? $submission->answer_text,
        ]);

        return $submission->fresh(['assignment', 'user', 'enrollment', 'files']);
    }

    public function grade(Submission $submission, int $score, ?string $feedback = null): Submission
    {
        $assignment = $submission->assignment;
        $maxScore = $assignment->max_score;

        if ($score < 0 || $score > $maxScore) {
            throw ValidationException::withMessages([
                'score' => "Score harus antara 0 dan {$maxScore}.",
            ]);
        }

        // Apply late penalty if applicable
        if ($submission->is_late) {
            $assignmentPenalty = $assignment->late_penalty_percent;
            $latePenaltyPercent = $assignmentPenalty !== null
                ? (int) $assignmentPenalty
                : (int) SystemSetting::get('learning.late_penalty_percent', 0);
            if ($latePenaltyPercent > 0) {
                $penalty = ($score * $latePenaltyPercent) / 100;
                $score = max(0, $score - $penalty);
            }
        }

        $submission->update([
            'status' => 'graded',
            'score' => $score,
            'feedback' => $feedback,
            'graded_at' => Carbon::now(),
        ]);

        return $submission->fresh(['assignment', 'user', 'enrollment', 'files']);
    }

    private function getEnrollmentForLesson(Lesson $lesson, int $userId): ?Enrollment
    {
        $lesson->loadMissing('unit.course');

        if (! $lesson->unit || ! $lesson->unit->course) {
            return null;
        }

        return Enrollment::query()
            ->where('course_id', $lesson->unit->course->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed'])
            ->first();
    }

    private function incrementLessonProgressAttempt(int $enrollmentId, int $lessonId): void
    {
        $progress = LessonProgress::query()
            ->where('enrollment_id', $enrollmentId)
            ->where('lesson_id', $lessonId)
            ->first();

        if ($progress) {
            $progress->increment('attempt_count');
        } else {
            // Create if doesn't exist
            LessonProgress::create([
                'enrollment_id' => $enrollmentId,
                'lesson_id' => $lessonId,
                'status' => 'not_started',
                'progress_percent' => 0,
                'attempt_count' => 1,
            ]);
        }
    }
}

