<?php

namespace Modules\Forums\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
     
    protected $listen = [
        \Modules\Forums\Events\ThreadCreated::class => [
            \Modules\Forums\Listeners\NotifyInstructorOnThreadCreated::class,
            \Modules\Forums\Listeners\UpdateForumStatistics::class,
        ],
        \Modules\Forums\Events\ReplyCreated::class => [
            \Modules\Forums\Listeners\NotifyAuthorOnReply::class,
            \Modules\Forums\Listeners\UpdateForumStatistics::class,
        ],
        \Modules\Forums\Events\ThreadPinned::class => [
            \Modules\Forums\Listeners\NotifyUsersOnThreadPinned::class,
        ],
        \Modules\Forums\Events\ReactionAdded::class => [
            \Modules\Forums\Listeners\NotifyAuthorOnReaction::class,
        ],
    ];

     
    protected static $shouldDiscoverEvents = false;

     
    protected function configureEmailVerification(): void {}
}
