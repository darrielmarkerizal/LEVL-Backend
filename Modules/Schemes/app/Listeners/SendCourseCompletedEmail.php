<?php

declare(strict_types=1);

namespace Modules\Schemes\Listeners;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Mail\Mail\Schemes\CourseCompletedMail;
use Modules\Schemes\Events\CourseCompleted;

class SendCourseCompletedEmail implements ShouldQueue
{
    use Queueable;

    public $queue = 'emails-transactional';

    public int $tries = 3;

    public int $timeout = 60;

    public function handle(CourseCompleted $event): void
    {
        $enrollment = $event->enrollment->fresh(['user', 'course']);

        if (! $enrollment->user || ! $enrollment->user->email) {
            return;
        }

        $course = $event->course;
        $user = $enrollment->user;

        $courseUrl = $this->getCourseUrl($course);

        Mail::to($user->email)
            ->queue((new CourseCompletedMail($user, $course, $enrollment, $courseUrl))->onQueue('emails-transactional'));
    }

    private function getCourseUrl($course): string
    {
        $frontendUrl = config('app.frontend_url');

        return rtrim($frontendUrl, '/').'/courses/'.$course->slug;
    }
}
