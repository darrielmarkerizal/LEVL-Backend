<?php

namespace Modules\Schemes\Listeners;

use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Events\LessonCompleted;
use Modules\Schemes\Services\ProgressionService;

class UpdateProgressOnLessonCompleted
{
    public function __construct(private ProgressionService $progression) {}

    public function handle(LessonCompleted $event): void
    {
        $lesson = $event->lesson;
        $unit = $lesson->unit;
        $courseId = $unit?->course_id ?? $lesson->unit()->value('course_id');

        if (! $courseId) {
            return;
        }

        $enrollment = Enrollment::query()
            ->where('id', $event->enrollmentId)
            ->where('user_id', $event->userId)
            ->where('course_id', $courseId)
            ->first();

        if (! $enrollment) {
            return;
        }

        $this->progression->markLessonCompleted($lesson, $enrollment);
    }
}


