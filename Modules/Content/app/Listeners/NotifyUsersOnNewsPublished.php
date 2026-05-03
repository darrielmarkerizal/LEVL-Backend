<?php

namespace Modules\Content\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Content\Events\NewsPublished;
use Modules\Content\Services\ContentNotificationService;

class NotifyUsersOnNewsPublished implements ShouldQueue
{
    protected ContentNotificationService $notificationService;

    public function __construct(ContentNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(NewsPublished $event): void
    {
        $this->notificationService->notifyNewNews($event->news);
    }
}
