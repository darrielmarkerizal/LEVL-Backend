<?php

declare(strict_types=1);

namespace Modules\Enrollments\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Models\EnrollmentActivity;
use Modules\Schemes\Events\LessonCompleted;

class RecordLessonCompletedActivity implements ShouldQueue
{
    public function handle(LessonCompleted $event): void
    {
        $lesson = $event->lesson;

        if (! $lesson || ! $lesson->unit || ! $lesson->unit->course) {
            return;
        }

        $enrollment = Enrollment::query()->find($event->enrollmentId);

        if (! $enrollment || $enrollment->user_id !== $event->userId) {
            return;
        }

        EnrollmentActivity::query()->firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'event_type' => 'lesson_completed',
                'lesson_id' => $lesson->id,
            ],
            [
                'user_id' => $event->userId,
                'course_id' => $lesson->unit->course_id,
                'title' => 'Completed Lesson: "'.$lesson->title.'"',
                'body' => null,
                'metadata' => [
                    'lesson_id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'course_id' => $lesson->unit->course_id,
                ],
                'quiz_id' => null,
                'assignment_id' => null,
                'occurred_at' => now(),
            ],
        );
    }
}