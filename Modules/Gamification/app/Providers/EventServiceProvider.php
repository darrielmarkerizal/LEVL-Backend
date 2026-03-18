<?php

namespace Modules\Gamification\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Schemes Module Integration
        \Modules\Schemes\Events\LessonCompleted::class => [
            \Modules\Gamification\Listeners\AwardXpForLessonCompleted::class,
        ],
        \Modules\Schemes\Events\UnitCompleted::class => [
            \Modules\Gamification\Listeners\AwardXpForUnitCompleted::class,
        ],
        \Modules\Schemes\Events\CourseCompleted::class => [
            \Modules\Gamification\Listeners\AwardBadgeForCourseCompleted::class,
        ],

        // Learning Module Integration
        \Modules\Learning\Events\SubmissionStateChanged::class => [
            \Modules\Gamification\Listeners\AwardXpForAssignmentSubmitted::class,
        ],
        \Modules\Learning\Events\QuizCompleted::class => [
            \Modules\Gamification\Listeners\AwardXpForQuizPassed::class,
        ],

        // Grading Module Integration
        \Modules\Grading\Events\GradesReleased::class => [
            \Modules\Gamification\Listeners\AwardXpForGradeReleased::class,
            \Modules\Gamification\Listeners\AwardXpForPerfectScore::class,
        ],

        // Forums Module Integration
        \Modules\Forums\Events\ThreadCreated::class => [
            \Modules\Gamification\Listeners\AwardXpForThreadCreated::class,
        ],
        \Modules\Forums\Events\ReplyCreated::class => [
            \Modules\Gamification\Listeners\AwardXpForReplyCreated::class,
        ],
        \Modules\Forums\Events\ReactionAdded::class => [
            \Modules\Gamification\Listeners\AwardXpForReactionReceived::class,
        ],

        // Gamification Events
        \Modules\Gamification\Events\UserLeveledUp::class => [
            \Modules\Gamification\Listeners\HandleLevelUp::class,
        ],
        \Modules\Gamification\Events\UserLoggedIn::class => [
            \Modules\Gamification\Listeners\AwardXpForDailyLogin::class,
        ],
        \Modules\Gamification\Events\BadgeEarned::class => [
            \Modules\Gamification\Listeners\SendBadgeEarnedNotification::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}
