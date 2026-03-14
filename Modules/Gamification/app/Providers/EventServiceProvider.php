<?php

namespace Modules\Gamification\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Modules\Schemes\Events\LessonCompleted::class => [
            \Modules\Gamification\Listeners\AwardXpForLessonCompleted::class,
        ],
        \Modules\Schemes\Events\CourseCompleted::class => [
            \Modules\Gamification\Listeners\AwardBadgeForCourseCompleted::class,
        ],
        \Modules\Grading\Events\GradesReleased::class => [
            \Modules\Gamification\Listeners\AwardXpForGradeReleased::class,
        ],
        \Modules\Schemes\Events\UnitCompleted::class => [
            \Modules\Gamification\Listeners\AwardXpForUnitCompleted::class,
        ],
        // Forums Integration
        \Modules\Forums\Events\ThreadCreated::class => [
            \Modules\Gamification\Listeners\AwardXpForThreadCreated::class,
        ],
        \Modules\Forums\Events\ReplyCreated::class => [
            \Modules\Gamification\Listeners\AwardXpForReplyCreated::class,
        ],
        \Modules\Forums\Events\ReactionAdded::class => [
            \Modules\Gamification\Listeners\AwardXpForReactionReceived::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}
