<?php

declare(strict_types=1);

namespace Modules\Schemes\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Modules\Schemes\Events\CourseCompleted::class => [
            \Modules\Schemes\Listeners\SendCourseCompletedEmail::class,
        ],
        \Modules\Learning\Events\QuizCompleted::class => [
            \Modules\Schemes\Listeners\UpdateProgressOnQuizCompleted::class,
        ],
        \Modules\Grading\Events\GradesReleased::class => [
            \Modules\Schemes\Listeners\UpdateProgressOnGradesReleased::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}
