<?php

namespace Modules\Notifications\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Grading\Events\GradeRecalculated;
use Modules\Grading\Events\GradesReleased;
use Modules\Learning\Events\SubmissionStateChanged;
use Modules\Notifications\Listeners\NotifyOnGradeRecalculated;
use Modules\Notifications\Listeners\NotifyOnGradesReleased;
use Modules\Notifications\Listeners\NotifyOnSubmissionStateChanged;
use Modules\Notifications\Listeners\NotifyUserOnCourseCompleted;
use Modules\Schemes\Events\CourseCompleted;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CourseCompleted::class => [
            NotifyUserOnCourseCompleted::class,
        ],

        SubmissionStateChanged::class => [
            NotifyOnSubmissionStateChanged::class,
        ],

        GradesReleased::class => [
            NotifyOnGradesReleased::class,
        ],

        GradeRecalculated::class => [
            NotifyOnGradeRecalculated::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}
