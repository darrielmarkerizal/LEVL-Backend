<?php

namespace Modules\Content\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Content\Events\AnnouncementPublished;
use Modules\Content\Services\ContentNotificationService;

class NotifyTargetAudienceOnAnnouncementPublished implements ShouldQueue
{
    protected ContentNotificationService $notificationService;

    public function __construct(ContentNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(AnnouncementPublished $event): void
    {
        $this->notificationService->notifyTargetAudience($event->announcement);
    }
}
