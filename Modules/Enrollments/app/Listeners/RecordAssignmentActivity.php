<?php

declare(strict_types=1);

namespace Modules\Enrollments\Listeners;

use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Models\EnrollmentActivity;
use Modules\Learning\Enums\SubmissionState;
use Modules\Learning\Events\SubmissionStateChanged;

class RecordAssignmentActivity
{
    public function handle(SubmissionStateChanged $event): void
    {
        $submission = $event->submission->fresh(['assignment.unit.course']);

        if (! $submission || ! $submission->assignment || ! $submission->assignment->unit || ! $submission->assignment->unit->course) {
            return;
        }

        if (! $submission->enrollment_id) {
            return;
        }

        $enrollment = Enrollment::query()->find($submission->enrollment_id);

        if (! $enrollment || $enrollment->user_id !== $submission->user_id) {
            return;
        }

        $assignment = $submission->assignment;
        $activityKey = 'submission:'.$submission->id;

        if ($event->newState === SubmissionState::PendingManualGrading) {
            EnrollmentActivity::query()->firstOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'event_type' => 'assignment_submitted',
                    'assignment_id' => $assignment->id,
                    'body' => $activityKey,
                ],
                [
                    'user_id' => $submission->user_id,
                    'course_id' => $assignment->unit->course_id,
                    'title' => 'Submitted Assignment: "'.$assignment->title.'"',
                    'metadata' => [
                        'assignment_id' => $assignment->id,
                        'submission_id' => $submission->id,
                        'state' => $event->newState->value,
                    ],
                    'lesson_id' => null,
                    'quiz_id' => null,
                    'occurred_at' => $submission->submitted_at ?? now(),
                ],
            );
        }

        if (! in_array($event->newState, [SubmissionState::AutoGraded, SubmissionState::Graded, SubmissionState::Released], true)) {
            return;
        }

        EnrollmentActivity::query()->firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'event_type' => 'assignment_graded',
                'assignment_id' => $assignment->id,
                'body' => $activityKey,
            ],
            [
                'user_id' => $submission->user_id,
                'course_id' => $assignment->unit->course_id,
                'title' => 'Assignment graded: "'.$assignment->title.'"',
                'metadata' => [
                    'assignment_id' => $assignment->id,
                    'submission_id' => $submission->id,
                    'state' => $event->newState->value,
                    'score' => $submission->score,
                ],
                'lesson_id' => null,
                'quiz_id' => null,
                'occurred_at' => $submission->submitted_at ?? now(),
            ],
        );
    }
}