<?php

declare(strict_types=1);

namespace Modules\Enrollments\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Modules\Enrollments\Events\EnrollmentCreated::class => [
            \Modules\Enrollments\Listeners\InitializeProgressForEnrollment::class,
        ],
        \Modules\Schemes\Events\LessonCompleted::class => [
            \Modules\Enrollments\Listeners\RecordLessonCompletedActivity::class,
        ],
        \Modules\Learning\Events\SubmissionStateChanged::class => [
            \Modules\Enrollments\Listeners\RecordAssignmentActivity::class,
        ],
        \Modules\Learning\Events\QuizCompleted::class => [
            \Modules\Enrollments\Listeners\RecordQuizCompletedActivity::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}
