<?php

namespace Modules\Content\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;

class ContentNotificationService
{
    public function notifyTargetAudience(Announcement $announcement): void
    {
        $targetUsers = $this->getTargetUsers($announcement);

        if ($targetUsers->isEmpty()) {
            return;
        }

        // Send notification to target users
        // This would integrate with the Notifications module
        // For now, we'll just log or queue the notification
        foreach ($targetUsers as $user) {
            // Notification::send($user, new AnnouncementPublishedNotification($announcement));
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
        // Get all active users or specific subscribers
        $users = User::where('status', UserStatus::Active->value)->get();

        foreach ($users as $user) {
            // Notification::send($user, new NewsPublishedNotification($news));
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
