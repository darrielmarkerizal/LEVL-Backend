<?php

namespace Modules\Content\Services;

use Illuminate\Support\Collection;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class ContentNotificationService
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function notifyTargetAudience(Announcement $announcement): void
    {
        $targetUsers = $this->getTargetUsers($announcement);

        if ($targetUsers->isEmpty()) {
            return;
        }

        foreach ($targetUsers as $user) {
            $this->notificationService->notifyByPreferences(
                $user,
                NotificationType::CourseUpdates->value,
                __('notifications.content.new_announcement_title'),
                $announcement->title,
                [
                    'announcement_id' => $announcement->id,
                    'target_type' => $announcement->target_type,
                    'target_value' => $announcement->target_value,
                ]
            );
        }
    }

    public function getTargetUsers(Announcement $announcement): Collection
    {
        if ($announcement->target_type === 'all') {
            return User::where('status', UserStatus::Active->value)->get();
        }

        if ($announcement->target_type === 'role') {
            return User::whereHas('roles', function ($q) use ($announcement) {
                $q->where('name', $announcement->target_value);
            })
                ->where('status', UserStatus::Active->value)
                ->get();
        }

        if ($announcement->target_type === 'course' && $announcement->course_id) {
            return $this->getCourseEnrolledUsers($announcement->course_id);
        }

        return collect();
    }

    protected function getCourseEnrolledUsers(int $courseId): Collection
    {
        return User::whereHas('enrollments', function ($q) use ($courseId) {
            $q->where('course_id', $courseId)
                ->where('status', \Modules\Enrollments\Enums\EnrollmentStatus::Active->value);
        })
            ->where('status', UserStatus::Active->value)
            ->get();
    }

    public function notifyNewNews(News $news): void
    {
        $users = User::where('status', UserStatus::Active->value)->get();

        foreach ($users as $user) {
            $this->notificationService->notifyByPreferences(
                $user,
                NotificationType::CourseUpdates->value,
                __('notifications.content.new_news_title'),
                $news->title,
                [
                    'news_id' => $news->id,
                ]
            );
        }
    }

    public function notifyScheduledPublication($content): void
    {
        if ($content instanceof Announcement) {
            $this->notifyTargetAudience($content);
        } elseif ($content instanceof News) {
            $this->notifyNewNews($content);
        }
    }
}
