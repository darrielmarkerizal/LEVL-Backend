<?php

declare(strict_types=1);

namespace Modules\Schemes\Listeners;

use Modules\Enrollments\Models\Enrollment;
use Modules\Schemes\Events\UnitCompleted;
use Modules\Schemes\Services\ProgressionService;

class UpdateProgressOnUnitCompleted
{
    public function __construct(private ProgressionService $progression) {}

    public function handle(UnitCompleted $event): void
    {
        $unit = $event->unit;
        $courseId = $unit->course_id ?? $unit->course()->value('id');

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

        $this->progression->markUnitCompleted($unit, $enrollment);
    }
}


