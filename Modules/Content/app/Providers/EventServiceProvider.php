<?php

namespace Modules\Content\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    
    protected $listen = [
        \Modules\Content\Events\AnnouncementPublished::class => [
            \Modules\Content\Listeners\NotifyTargetAudienceOnAnnouncementPublished::class,
        ],
        \Modules\Content\Events\NewsPublished::class => [
            \Modules\Content\Listeners\NotifyUsersOnNewsPublished::class,
        ],
        \Modules\Content\Events\ContentSubmitted::class => [
            \Modules\Content\Listeners\NotifyReviewersOnContentSubmitted::class,
        ],
        \Modules\Content\Events\ContentApproved::class => [
            \Modules\Content\Listeners\NotifyAuthorOnContentApproved::class,
        ],
        \Modules\Content\Events\ContentRejected::class => [
            \Modules\Content\Listeners\NotifyAuthorOnContentRejected::class,
        ],
        \Modules\Content\Events\ContentScheduled::class => [
            \Modules\Content\Listeners\NotifyAuthorOnContentScheduled::class,
        ],
        \Modules\Content\Events\ContentPublished::class => [
            \Modules\Content\Listeners\NotifyAuthorOnContentPublished::class,
        ],
    ];

    
    protected static $shouldDiscoverEvents = false;

    
    protected function configureEmailVerification(): void {}
}
